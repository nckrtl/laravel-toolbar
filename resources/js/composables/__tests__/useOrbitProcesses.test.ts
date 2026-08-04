import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

const subscribeProcessStream = vi.fn();
const createOrbitGatewayClient = vi.fn();

vi.mock("@hardimpactdev/orbit-sdk-typescript", () => ({
    subscribeProcessStream: (...args: unknown[]) => subscribeProcessStream(...args),
    createOrbitGatewayClient: (...args: unknown[]) => createOrbitGatewayClient(...args),
}));

type StreamHandlers = {
    onSnapshot?: (snapshot: unknown, event?: MessageEvent) => void;
    onUpdate?: (update: unknown, event?: MessageEvent) => void;
    onError?: (error: unknown, event?: MessageEvent) => void;
    onReconnect?: () => void;
};

function makeProcess(
    key: string,
    status: string,
    overrides: Record<string, unknown> = {},
) {
    const { label: labelOverride, name: _name, key: _key, ...rest } = overrides;
    const label =
        typeof labelOverride === "string" && labelOverride.length > 0
            ? labelOverride
            : key;

    return {
        node: "local",
        project: null,
        instance: null,
        workspace: null,
        key,
        label,
        name: key,
        command: null,
        restart_policy: "always",
        crash_notification: "none",
        runtime: "node",
        tool: null,
        service: null,
        runtime_unit: `${key}.service`,
        status,
        last_event: null,
        ...rest,
    };
}

function makeUpdate(
    key: string,
    status: string,
    overrides: Record<string, unknown> = {},
) {
    const { label: labelOverride, name: _name, key: _key, ...rest } = overrides;
    const label =
        typeof labelOverride === "string" && labelOverride.length > 0
            ? labelOverride
            : key;

    return {
        id: 1,
        event: status,
        status,
        key,
        name: key,
        label,
        node: "local",
        project: null,
        instance: null,
        workspace: null,
        unit_name: `${key}.service`,
        occurred_at: null,
        exit_code: null,
        exit_status: null,
        ...rest,
    };
}

describe("useOrbitProcesses", () => {
    let streamOptions: Array<Record<string, unknown> & StreamHandlers> = [];
    let closeFns: Array<ReturnType<typeof vi.fn>> = [];
    let postMock: ReturnType<typeof vi.fn>;
    let useOrbitProcesses: typeof import("../useOrbitProcesses").useOrbitProcesses;
    let unsubscribes: Array<() => void> = [];

    async function loadComposable() {
        vi.resetModules();
        const mod = await import("../useOrbitProcesses");
        useOrbitProcesses = mod.useOrbitProcesses;
    }

    beforeEach(async () => {
        streamOptions = [];
        closeFns = [];
        unsubscribes = [];
        postMock = vi.fn();

        Object.defineProperty(window, "location", {
            configurable: true,
            value: {
                ...window.location,
                hostname: "app.example.test",
            },
        });

        subscribeProcessStream.mockReset();
        createOrbitGatewayClient.mockReset();

        subscribeProcessStream.mockImplementation((options: StreamHandlers) => {
            streamOptions.push(options as Record<string, unknown> & StreamHandlers);
            const close = vi.fn();
            closeFns.push(close);
            return {
                close,
                eventSource: {} as EventSource,
            };
        });

        createOrbitGatewayClient.mockImplementation(() => ({
            POST: postMock,
            GET: vi.fn(),
        }));

        postMock.mockResolvedValue({ data: { success: { data: {} } }, error: undefined });

        await loadComposable();
    });

    afterEach(() => {
        while (unsubscribes.length > 0) {
            unsubscribes.pop()?.();
        }
    });

    it("opens exactly one SSE stream on first subscribe with gateway root and hostname", () => {
        const { subscribe } = useOrbitProcesses();
        const unsub = subscribe("https://gateway.orbit");
        unsubscribes.push(unsub);

        expect(subscribeProcessStream).toHaveBeenCalledTimes(1);
        expect(subscribeProcessStream).toHaveBeenCalledWith(
            expect.objectContaining({
                baseUrl: "https://gateway.orbit",
                app: "app.example.test",
            }),
        );
        expect(createOrbitGatewayClient).toHaveBeenCalledWith(
            expect.objectContaining({
                baseUrl: "https://gateway.orbit",
                headers: { "X-Orbit-Client": "laravel-toolbar" },
            }),
        );
    });

    it("shares one stream between toolbar and panel subscribers and closes only after last unsubscribe", () => {
        const { subscribe } = useOrbitProcesses();
        const unsubToolbar = subscribe();
        const unsubPanel = subscribe();
        unsubscribes.push(unsubToolbar, unsubPanel);

        expect(subscribeProcessStream).toHaveBeenCalledTimes(1);
        expect(closeFns[0]).not.toHaveBeenCalled();

        unsubToolbar();
        expect(closeFns[0]).not.toHaveBeenCalled();

        unsubPanel();
        expect(closeFns[0]).toHaveBeenCalledTimes(1);
        unsubscribes = [];
    });

    it("clears processes, error, pending, and loading on the last unsubscribe", async () => {
        const { subscribe, processes, error, pendingByKey, loading, runningCount, totalCount, runAction } =
            useOrbitProcesses();
        const unsubA = subscribe();
        const unsubB = subscribe();
        unsubscribes.push(unsubA, unsubB);

        streamOptions[0].onSnapshot?.({
            app: "app.example.test",
            context: { node: "local", project: null, instance: null, workspace: null },
            processes: [
                makeProcess("vite", "running", { label: "Vite" }),
                makeProcess("queue", "stopped", { label: "Queue" }),
            ],
            cursor: { high_water_mark: 1 },
        });

        expect(processes.value).toHaveLength(2);
        expect(runningCount.value).toBe(1);
        expect(totalCount.value).toBe(2);

        let resolvePost!: (value: unknown) => void;
        postMock.mockImplementationOnce(
            () =>
                new Promise((resolve) => {
                    resolvePost = resolve;
                }),
        );
        const actionPromise = runAction("restart", "vite");
        expect(pendingByKey.value.vite).toBe("restart");

        // Stream error after action starts so both pending and error are present.
        streamOptions[0].onError?.({ message: "stream dropped" });
        expect(error.value).toBe("stream dropped");

        // Intermediate unsubscribe must keep shared state while stream stays open.
        unsubA();
        expect(closeFns[0]).not.toHaveBeenCalled();
        expect(processes.value).toHaveLength(2);
        expect(error.value).toBe("stream dropped");
        expect(pendingByKey.value.vite).toBe("restart");

        unsubB();
        unsubscribes = [];

        expect(closeFns[0]).toHaveBeenCalledTimes(1);
        expect(processes.value).toEqual([]);
        expect(error.value).toBeNull();
        expect(pendingByKey.value).toEqual({});
        expect(loading.value).toBe(false);
        expect(runningCount.value).toBe(0);
        expect(totalCount.value).toBe(0);

        resolvePost({ data: {}, error: undefined });
        await actionPromise;
        // Settled action must not reintroduce pending after full teardown.
        expect(pendingByKey.value).toEqual({});
    });

    it("maps key/label from snapshots and matches updates by key", () => {
        const { subscribe, processes, runningCount, totalCount } = useOrbitProcesses();
        unsubscribes.push(subscribe());

        const handlers = streamOptions[0];
        handlers.onSnapshot?.({
            app: "app.example.test",
            context: { node: "local", project: null, instance: null, workspace: null },
            processes: [
                makeProcess("frankenphp-hauzer", "running", { label: "FrankenPHP" }),
                makeProcess("queue-worker", "stopped", { label: "Queue" }),
            ],
            cursor: { high_water_mark: 1 },
        });

        expect(processes.value).toEqual([
            expect.objectContaining({
                key: "frankenphp-hauzer",
                label: "FrankenPHP",
                status: "running",
            }),
            expect.objectContaining({
                key: "queue-worker",
                label: "Queue",
                status: "stopped",
            }),
        ]);
        expect(runningCount.value).toBe(1);
        expect(totalCount.value).toBe(2);

        handlers.onUpdate?.(
            makeUpdate("queue-worker", "starting", {
                id: 2,
                event: "starting",
                label: "Queue",
            }),
        );

        expect(processes.value.find((p) => p.key === "queue-worker")?.status).toBe("starting");
        expect(processes.value.find((p) => p.key === "queue-worker")?.label).toBe("Queue");
        expect(runningCount.value).toBe(1);
        expect(totalCount.value).toBe(2);

        handlers.onUpdate?.(
            makeUpdate("horizon-hauzer", "running", {
                id: 3,
                event: "started",
                label: "Horizon",
            }),
        );

        expect(processes.value.map((p) => p.key)).toContain("horizon-hauzer");
        expect(processes.value.find((p) => p.key === "horizon-hauzer")?.label).toBe("Horizon");
        expect(totalCount.value).toBe(3);
        expect(runningCount.value).toBe(2);
    });

    it("treats labels as display data and keeps actions targeted by key", async () => {
        const { subscribe, runAction, pendingByKey, processes, error } = useOrbitProcesses();
        unsubscribes.push(subscribe());

        streamOptions[0].onSnapshot?.({
            app: "app.example.test",
            context: { node: "local", project: null, instance: null, workspace: null },
            processes: [makeProcess("horizon-hauzer", "stopped", { label: "Horizon" })],
            cursor: { high_water_mark: 1 },
        });

        expect(processes.value[0]).toEqual(
            expect.objectContaining({
                key: "horizon-hauzer",
                label: "Horizon",
                status: "stopped",
            }),
        );

        let resolvePost!: (value: unknown) => void;
        postMock.mockImplementationOnce(
            () =>
                new Promise((resolve) => {
                    resolvePost = resolve;
                }),
        );

        const startPromise = runAction("start", "horizon-hauzer");
        expect(pendingByKey.value["horizon-hauzer"]).toBe("start");
        // Pending is keyed by durable process key, not display label.
        expect(pendingByKey.value.Horizon).toBeUndefined();
        // No optimistic status change while POST is in flight.
        expect(processes.value.find((p) => p.key === "horizon-hauzer")?.status).toBe("stopped");
        expect(processes.value.find((p) => p.key === "horizon-hauzer")?.label).toBe("Horizon");

        resolvePost({ data: { success: { data: {} } }, error: undefined });
        await startPromise;
        expect(pendingByKey.value["horizon-hauzer"]).toBeUndefined();
        expect(processes.value.find((p) => p.key === "horizon-hauzer")?.status).toBe("stopped");

        // Lifecycle request body still uses server parameter `name` set to the process key.
        expect(postMock).toHaveBeenCalledWith("/processes/start", {
            body: { app: "app.example.test", name: "horizon-hauzer" },
        });

        postMock.mockResolvedValueOnce({ data: {}, error: undefined });
        await runAction("stop", "horizon-hauzer");
        expect(postMock).toHaveBeenCalledWith("/processes/stop", {
            body: { app: "app.example.test", name: "horizon-hauzer" },
        });

        postMock.mockResolvedValueOnce({ data: {}, error: undefined });
        await runAction("restart", "horizon-hauzer");
        expect(postMock).toHaveBeenCalledWith("/processes/restart", {
            body: { app: "app.example.test", name: "horizon-hauzer" },
        });

        expect(error.value).toBeNull();
    });

    it("surfaces lifecycle POST errors without inventing status", async () => {
        const { subscribe, runAction, processes, error } = useOrbitProcesses();
        unsubscribes.push(subscribe());

        streamOptions[0].onSnapshot?.({
            app: "app.example.test",
            context: { node: "local", project: null, instance: null, workspace: null },
            processes: [makeProcess("vite", "stopped", { label: "Vite" })],
            cursor: { high_water_mark: 1 },
        });

        postMock.mockResolvedValueOnce({
            data: undefined,
            error: { error: { message: "Peer identity unknown." } },
        });

        await runAction("start", "vite");

        expect(error.value).toBe("Peer identity unknown.");
        expect(processes.value.find((p) => p.key === "vite")?.status).toBe("stopped");
        expect(processes.value.find((p) => p.key === "vite")?.label).toBe("Vite");
        expect(subscribeProcessStream).toHaveBeenCalledTimes(1);
    });

    it("realigns stream and HTTP client when gateway_url changes without duplicate streams", () => {
        const { subscribe } = useOrbitProcesses();
        const unsubA = subscribe("https://gateway.orbit");
        const unsubB = subscribe("https://gateway.orbit.other");
        unsubscribes.push(unsubA, unsubB);

        expect(subscribeProcessStream).toHaveBeenCalledTimes(2);
        expect(closeFns[0]).toHaveBeenCalledTimes(1);
        expect(streamOptions[1].baseUrl).toBe("https://gateway.orbit.other");
        expect(createOrbitGatewayClient).toHaveBeenCalledWith(
            expect.objectContaining({ baseUrl: "https://gateway.orbit.other" }),
        );
        // Still only one live stream (second subscription replaced the first).
        expect(closeFns[1]).not.toHaveBeenCalled();

        unsubA();
        expect(closeFns[1]).not.toHaveBeenCalled();
        unsubB();
        expect(closeFns[1]).toHaveBeenCalledTimes(1);
        unsubscribes = [];
    });

    it("defaults gateway base URL to https://gateway.orbit", () => {
        const { subscribe } = useOrbitProcesses();
        unsubscribes.push(subscribe());

        expect(subscribeProcessStream).toHaveBeenCalledWith(
            expect.objectContaining({
                baseUrl: "https://gateway.orbit",
                app: "app.example.test",
            }),
        );
    });
});
