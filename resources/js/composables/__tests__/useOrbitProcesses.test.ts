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
    name: string,
    status: string,
    overrides: Record<string, unknown> = {},
) {
    return {
        node: "local",
        project: null,
        instance: null,
        workspace: null,
        name,
        command: null,
        restart_policy: "always",
        crash_notification: "none",
        runtime: "node",
        tool: null,
        service: null,
        runtime_unit: `${name}.service`,
        status,
        last_event: null,
        ...overrides,
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
        const { subscribe, processes, error, pendingByName, loading, runningCount, totalCount, runAction } =
            useOrbitProcesses();
        const unsubA = subscribe();
        const unsubB = subscribe();
        unsubscribes.push(unsubA, unsubB);

        streamOptions[0].onSnapshot?.({
            app: "app.example.test",
            context: { node: "local", project: null, instance: null, workspace: null },
            processes: [makeProcess("vite", "running"), makeProcess("queue", "stopped")],
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
        expect(pendingByName.value.vite).toBe("restart");

        // Stream error after action starts so both pending and error are present.
        streamOptions[0].onError?.({ message: "stream dropped" });
        expect(error.value).toBe("stream dropped");

        // Intermediate unsubscribe must keep shared state while stream stays open.
        unsubA();
        expect(closeFns[0]).not.toHaveBeenCalled();
        expect(processes.value).toHaveLength(2);
        expect(error.value).toBe("stream dropped");
        expect(pendingByName.value.vite).toBe("restart");

        unsubB();
        unsubscribes = [];

        expect(closeFns[0]).toHaveBeenCalledTimes(1);
        expect(processes.value).toEqual([]);
        expect(error.value).toBeNull();
        expect(pendingByName.value).toEqual({});
        expect(loading.value).toBe(false);
        expect(runningCount.value).toBe(0);
        expect(totalCount.value).toBe(0);

        resolvePost({ data: {}, error: undefined });
        await actionPromise;
        // Settled action must not reintroduce pending after full teardown.
        expect(pendingByName.value).toEqual({});
    });

    it("drives process state and counts from snapshot and update frames", () => {
        const { subscribe, processes, runningCount, totalCount } = useOrbitProcesses();
        unsubscribes.push(subscribe());

        const handlers = streamOptions[0];
        handlers.onSnapshot?.({
            app: "app.example.test",
            context: { node: "local", project: null, instance: null, workspace: null },
            processes: [
                makeProcess("vite", "running"),
                makeProcess("queue", "stopped"),
            ],
            cursor: { high_water_mark: 1 },
        });

        expect(processes.value).toHaveLength(2);
        expect(runningCount.value).toBe(1);
        expect(totalCount.value).toBe(2);

        handlers.onUpdate?.({
            id: 2,
            event: "starting",
            status: "starting",
            name: "queue",
            node: "local",
            project: null,
            instance: null,
            workspace: null,
            unit_name: "queue.service",
            occurred_at: null,
            exit_code: null,
            exit_status: null,
        });

        expect(processes.value.find((p) => p.name === "queue")?.status).toBe("starting");
        expect(runningCount.value).toBe(1);
        expect(totalCount.value).toBe(2);

        handlers.onUpdate?.({
            id: 3,
            event: "started",
            status: "running",
            name: "horizon",
            node: "local",
            project: null,
            instance: null,
            workspace: null,
            unit_name: "horizon.service",
            occurred_at: null,
            exit_code: null,
            exit_status: null,
        });

        expect(processes.value.map((p) => p.name)).toContain("horizon");
        expect(totalCount.value).toBe(3);
        expect(runningCount.value).toBe(2);
    });

    it("runs start, stop, and restart via typed SDK POSTs with hostname app and name", async () => {
        const { subscribe, runAction, pendingByName, processes, error } = useOrbitProcesses();
        unsubscribes.push(subscribe());

        streamOptions[0].onSnapshot?.({
            app: "app.example.test",
            context: { node: "local", project: null, instance: null, workspace: null },
            processes: [makeProcess("vite", "stopped")],
            cursor: { high_water_mark: 1 },
        });

        let resolvePost!: (value: unknown) => void;
        postMock.mockImplementationOnce(
            () =>
                new Promise((resolve) => {
                    resolvePost = resolve;
                }),
        );

        const startPromise = runAction("start", "vite");
        expect(pendingByName.value.vite).toBe("start");
        // No optimistic status change while POST is in flight.
        expect(processes.value.find((p) => p.name === "vite")?.status).toBe("stopped");

        resolvePost({ data: { success: { data: {} } }, error: undefined });
        await startPromise;
        expect(pendingByName.value.vite).toBeUndefined();
        expect(processes.value.find((p) => p.name === "vite")?.status).toBe("stopped");

        expect(postMock).toHaveBeenCalledWith("/processes/start", {
            body: { app: "app.example.test", name: "vite" },
        });

        postMock.mockResolvedValueOnce({ data: {}, error: undefined });
        await runAction("stop", "vite");
        expect(postMock).toHaveBeenCalledWith("/processes/stop", {
            body: { app: "app.example.test", name: "vite" },
        });

        postMock.mockResolvedValueOnce({ data: {}, error: undefined });
        await runAction("restart", "vite");
        expect(postMock).toHaveBeenCalledWith("/processes/restart", {
            body: { app: "app.example.test", name: "vite" },
        });

        expect(error.value).toBeNull();
    });

    it("surfaces lifecycle POST errors without inventing status", async () => {
        const { subscribe, runAction, processes, error } = useOrbitProcesses();
        unsubscribes.push(subscribe());

        streamOptions[0].onSnapshot?.({
            app: "app.example.test",
            context: { node: "local", project: null, instance: null, workspace: null },
            processes: [makeProcess("vite", "stopped")],
            cursor: { high_water_mark: 1 },
        });

        postMock.mockResolvedValueOnce({
            data: undefined,
            error: { error: { message: "Peer identity unknown." } },
        });

        await runAction("start", "vite");

        expect(error.value).toBe("Peer identity unknown.");
        expect(processes.value.find((p) => p.name === "vite")?.status).toBe("stopped");
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
