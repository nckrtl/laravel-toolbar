import { computed, ref, type ComputedRef, type Ref } from "vue";
import {
    createOrbitGatewayClient,
    subscribeProcessStream,
    type OrbitGatewayClient,
    type ProcessRuntimeStatus,
    type ProcessStreamProcess,
    type ProcessStreamSubscription,
    type ProcessStreamUpdate,
} from "@hardimpactdev/orbit-sdk-typescript";

export type OrbitProcessStatus = ProcessRuntimeStatus;

export type OrbitProcess = {
    key: string;
    label: string;
    status: OrbitProcessStatus;
    node?: string | null;
    command?: string | null;
    runtime_unit?: string | null;
};

export type OrbitLifecycleAction = "start" | "restart" | "stop";

const DEFAULT_GATEWAY_URL = "https://gateway.orbit";
const CLIENT_HEADER = "laravel-toolbar";

const processes: Ref<OrbitProcess[]> = ref([]);
const error: Ref<string | null> = ref(null);
const loading: Ref<boolean> = ref(false);
const pendingByKey: Ref<Record<string, OrbitLifecycleAction | undefined>> = ref({});

let client: OrbitGatewayClient | null = null;
let activeGatewayUrl = DEFAULT_GATEWAY_URL;
let streamSubscription: ProcessStreamSubscription | null = null;
let subscriberCount = 0;

function resolveAppHostname(): string {
    return window.location.hostname;
}

function normalizeGatewayUrl(gatewayUrl?: string | null): string {
    const trimmed = gatewayUrl?.trim();
    return trimmed && trimmed.length > 0 ? trimmed.replace(/\/+$/, "") : DEFAULT_GATEWAY_URL;
}

function ensureClient(gatewayUrl?: string | null): OrbitGatewayClient {
    const nextUrl = normalizeGatewayUrl(gatewayUrl);

    // Compare against the currently active URL before any assignment so a
    // pre-updated activeGatewayUrl cannot mask a stale client instance.
    if (!client || activeGatewayUrl !== nextUrl) {
        client = createOrbitGatewayClient({
            baseUrl: nextUrl,
            headers: {
                "X-Orbit-Client": CLIENT_HEADER,
            },
        });
        activeGatewayUrl = nextUrl;
    }

    return client;
}

function formatApiError(apiError: unknown): string {
    if (apiError == null) {
        return "Orbit gateway request failed";
    }

    if (typeof apiError === "string") {
        return apiError;
    }

    if (apiError instanceof Event) {
        return "Unable to reach Orbit process stream";
    }

    if (typeof apiError === "object") {
        const record = apiError as {
            error?: { message?: string; code?: string };
            message?: string;
            code?: string;
        };

        if (typeof record.message === "string" && record.message.length > 0) {
            return record.message;
        }

        if (record.error?.message) {
            return record.error.message;
        }
    }

    return "Orbit gateway request failed";
}

function mapStreamProcess(process: ProcessStreamProcess): OrbitProcess {
    return {
        key: process.key,
        label: process.label,
        status: process.status,
        node: process.node,
        command: process.command,
        runtime_unit: process.runtime_unit,
    };
}

function applySnapshot(list: ProcessStreamProcess[]): void {
    processes.value = list.map(mapStreamProcess);
    error.value = null;
    loading.value = false;
}

function applyUpdate(update: ProcessStreamUpdate): void {
    const index = processes.value.findIndex((process) => process.key === update.key);

    if (index === -1) {
        processes.value = [
            ...processes.value,
            {
                key: update.key,
                label: update.label,
                status: update.status,
                node: update.node,
                command: null,
                runtime_unit: update.unit_name,
            },
        ];
        return;
    }

    const current = processes.value[index];
    const next = [...processes.value];
    next[index] = {
        ...current,
        label: update.label || current.label,
        status: update.status,
        node: update.node ?? current.node,
        runtime_unit: update.unit_name ?? current.runtime_unit,
    };
    processes.value = next;
}

function closeStream(): void {
    streamSubscription?.close();
    streamSubscription = null;
}

/**
 * Open (or replace) the single shared SSE subscription.
 * Lifecycle client is rebuilt first via ensureClient so stream base URL and
 * POST client always share the same normalized gateway URL.
 */
function openStream(gatewayUrl?: string | null): void {
    const baseUrl = normalizeGatewayUrl(gatewayUrl);
    // Must not assign activeGatewayUrl before ensureClient — that comparison
    // decides whether the HTTP client is rebuilt for the new base URL.
    ensureClient(baseUrl);

    closeStream();

    loading.value = processes.value.length === 0;
    error.value = null;

    streamSubscription = subscribeProcessStream({
        baseUrl: activeGatewayUrl,
        app: resolveAppHostname(),
        onSnapshot: (snapshot) => {
            applySnapshot(snapshot.processes);
        },
        onUpdate: (update) => {
            applyUpdate(update);
            error.value = null;
        },
        onError: (streamError) => {
            error.value = formatApiError(streamError);
            loading.value = false;
        },
        onReconnect: () => {
            // Native EventSource will deliver a fresh snapshot; surface no invented state.
            loading.value = processes.value.length === 0;
        },
    });
}

/** Keep the one stream and lifecycle client on the same gateway URL. */
function alignGateway(gatewayUrl?: string | null): void {
    const nextUrl = normalizeGatewayUrl(gatewayUrl);

    if (nextUrl === activeGatewayUrl && client && streamSubscription) {
        return;
    }

    if (subscriberCount > 0) {
        // Close/reopen the single shared stream — never open a second one.
        openStream(nextUrl);
        return;
    }

    ensureClient(nextUrl);
}

export function subscribeOrbitProcesses(gatewayUrl?: string | null): () => void {
    subscriberCount += 1;

    if (subscriberCount === 1) {
        openStream(gatewayUrl);
    } else {
        // Subsequent subscriber with a different gateway realigns stream + client.
        alignGateway(gatewayUrl);
    }

    return () => {
        subscriberCount = Math.max(0, subscriberCount - 1);
        if (subscriberCount === 0) {
            closeStream();
            processes.value = [];
            error.value = null;
            pendingByKey.value = {};
            loading.value = false;
        }
    };
}

/**
 * Run a lifecycle action for a durable Orbit process key.
 * Gateway request body still uses the server parameter `name` (= process key).
 */
export async function runOrbitLifecycleAction(
    action: OrbitLifecycleAction,
    key: string,
    gatewayUrl?: string | null,
): Promise<void> {
    if (!key || pendingByKey.value[key]) {
        return;
    }

    pendingByKey.value = {
        ...pendingByKey.value,
        [key]: action,
    };
    // Clear a prior action/stream error for this new request; do not touch status.
    error.value = null;

    try {
        // POSTs must target the same gateway as the active stream.
        alignGateway(gatewayUrl);
        const orbit = ensureClient(activeGatewayUrl);
        const body = {
            app: resolveAppHostname(),
            name: key,
        };

        let apiError: unknown;

        if (action === "start") {
            ({ error: apiError } = await orbit.POST("/processes/start", { body }));
        } else if (action === "restart") {
            ({ error: apiError } = await orbit.POST("/processes/restart", { body }));
        } else {
            ({ error: apiError } = await orbit.POST("/processes/stop", { body }));
        }

        if (apiError) {
            error.value = formatApiError(apiError);
        }
        // Status changes arrive only via SSE; no post-action GET/refetch.
    } catch (caught) {
        const message =
            caught instanceof Error && caught.message
                ? caught.message
                : `Failed to ${action} ${key}`;
        error.value = message;
    } finally {
        const next = { ...pendingByKey.value };
        delete next[key];
        pendingByKey.value = next;
    }
}

export function useOrbitProcesses(): {
    processes: Ref<OrbitProcess[]>;
    error: Ref<string | null>;
    loading: Ref<boolean>;
    pendingByKey: Ref<Record<string, OrbitLifecycleAction | undefined>>;
    runningCount: ComputedRef<number>;
    totalCount: ComputedRef<number>;
    subscribe: (gatewayUrl?: string | null) => () => void;
    runAction: (
        action: OrbitLifecycleAction,
        key: string,
        gatewayUrl?: string | null,
    ) => Promise<void>;
} {
    const runningCount = computed(
        () => processes.value.filter((process) => process.status === "running").length,
    );
    const totalCount = computed(() => processes.value.length);

    return {
        processes,
        error,
        loading,
        pendingByKey,
        runningCount,
        totalCount,
        subscribe: subscribeOrbitProcesses,
        runAction: runOrbitLifecycleAction,
    };
}
