import { computed, ref, shallowRef } from 'vue';
import type { RequestHistoryRow, ToolbarData, ToolbarUpdateEvent } from '@/types';

type RequestDataResponse = {
    raw?: ToolbarData;
    summary?: unknown;
};

const IGNORED_HISTORY_URIS = ['/_boost/browser-logs'];
const IGNORED_HISTORY_PREFIXES = ['/_toolbar/requests/'];

const baseToolbarData = window.__LARAVEL_TOOLBAR_DATA__;
export const requestHistory = ref<RequestHistoryRow[]>([]);
export const selectedRequestId = ref<string | null>(null);
export const previewRequestId = ref<string | null>(null);
const payloadCache = shallowRef(new Map<string, ToolbarData>());
const pendingPayloads = new Map<string, Promise<ToolbarData>>();

const getPayloadRequestId = (payload: ToolbarData | null | undefined): string | null => {
    return payload?.request_id ?? payload?.metadata?.request_id ?? null;
};

const buildFallbackHistory = (payload: ToolbarData): RequestHistoryRow[] => {
    const requestId = getPayloadRequestId(payload);

    if (!requestId || !payload.request || !payload.response) {
        return [];
    }

    return [
        {
            id: requestId,
            is_xhr: false,
            method: payload.request.method ?? '',
            uri: payload.request.uri ?? '',
            name: payload.request.route_name ?? null,
            action: payload.request.controller_action ?? null,
            middleware_count: Array.isArray(payload.request.middleware)
                ? payload.request.middleware.length
                : null,
            status_code: payload.response.status_code ?? null,
            size: payload.response.size?.formattedValue ?? null,
            duration:
                payload.profiler?.total_wall_time?.formattedValue ??
                payload.request.duration?.formattedValue ??
                payload.request.duration ??
                null,
        },
    ];
};

const shouldIgnoreHistoryUri = (uri: string | null | undefined): boolean => {
    if (!uri) {
        return false;
    }

    if (IGNORED_HISTORY_URIS.includes(uri)) {
        return true;
    }

    return IGNORED_HISTORY_PREFIXES.some((prefix) => uri.startsWith(prefix));
};

const filterHistoryRows = (history: RequestHistoryRow[]): RequestHistoryRow[] => {
    return history.filter((row) => !shouldIgnoreHistoryUri(row.uri));
};

const normalizeHistory = (payload: ToolbarData): RequestHistoryRow[] => {
    if (Array.isArray(payload.request_history) && payload.request_history.length > 0) {
        return filterHistoryRows(payload.request_history.filter((row) => Boolean(row?.id)));
    }

    return filterHistoryRows(buildFallbackHistory(payload));
};

const resolveSelectedRequestId = (
    payload: ToolbarData,
    history: RequestHistoryRow[],
): string | null => {
    if (
        payload.selected_request_id &&
        (history.length === 0 || history.some((row) => row.id === payload.selected_request_id))
    ) {
        return payload.selected_request_id;
    }

    if (payload.request_id && (history.length === 0 || history.some((row) => row.id === payload.request_id))) {
        return payload.request_id;
    }

    const finalPageRequest = [...history].reverse().find((row) => !row.is_xhr);
    const lastHistoryRow = history[history.length - 1] ?? null;

    return finalPageRequest?.id ?? lastHistoryRow?.id ?? getPayloadRequestId(payload);
};

const decoratePayload = (payload: ToolbarData, requestId: string | null): ToolbarData => {
    return {
        ...baseToolbarData,
        ...payload,
        layout: payload.layout ?? baseToolbarData.layout,
        request_id: requestId ?? getPayloadRequestId(payload) ?? getPayloadRequestId(baseToolbarData),
        selected_request_id: selectedRequestId.value,
        request_history: requestHistory.value,
    };
};

const setCachedPayload = (requestId: string, payload: ToolbarData): ToolbarData => {
    const nextCache = new Map(payloadCache.value);
    const normalizedPayload = {
        ...payload,
        request_id: requestId,
    };

    nextCache.set(requestId, normalizedPayload);
    payloadCache.value = nextCache;

    return normalizedPayload;
};

const replaceToolbarState = (payload: ToolbarData): void => {
    const history = normalizeHistory(payload);
    const resolvedSelectedRequestId = resolveSelectedRequestId(payload, history);
    const nextCache = new Map<string, ToolbarData>();

    requestHistory.value = history;
    selectedRequestId.value = resolvedSelectedRequestId;
    previewRequestId.value = null;
    pendingPayloads.clear();

    if (resolvedSelectedRequestId) {
        nextCache.set(resolvedSelectedRequestId, {
            ...payload,
            request_id: resolvedSelectedRequestId,
        });
    }

    const payloadRequestId = getPayloadRequestId(payload);

    if (payloadRequestId && payloadRequestId !== resolvedSelectedRequestId) {
        nextCache.set(payloadRequestId, {
            ...payload,
            request_id: payloadRequestId,
        });
    }

    payloadCache.value = nextCache;
};

const upsertHistoryRow = (historyRow: RequestHistoryRow): void => {
    if (shouldIgnoreHistoryUri(historyRow.uri)) {
        return;
    }

    const rowIndex = requestHistory.value.findIndex((row) => row.id === historyRow.id);

    if (rowIndex === -1) {
        requestHistory.value = [...requestHistory.value, historyRow];

        return;
    }

    const nextHistory = [...requestHistory.value];
    nextHistory[rowIndex] = historyRow;
    requestHistory.value = nextHistory;
};

const isStateReplacementPayload = (payload: ToolbarData): boolean => {
    return (
        Array.isArray(payload.request_history) ||
        payload.selected_request_id !== undefined ||
        payload.layout !== undefined
    );
};

const handleToolbarUpdate = (payload: ToolbarData): void => {
    if (isStateReplacementPayload(payload)) {
        replaceToolbarState(payload);

        return;
    }

    if (payload.history_row?.id) {
        upsertHistoryRow(payload.history_row);
    }

    if (payload.request_id && payload.request && payload.response) {
        setCachedPayload(payload.request_id, payload);
    }
};

const loadRequestPayload = async (requestId: string): Promise<ToolbarData> => {
    const response = await window.fetch(`/_toolbar/requests/${encodeURIComponent(requestId)}`, {
        headers: {
            Accept: 'application/json',
            'X-Laravel-Toolbar-Internal': 'true',
        },
    });

    if (!response.ok) {
        throw new Error(`Failed to load toolbar payload for request ${requestId}`);
    }

    const payloadResponse = (await response.json()) as RequestDataResponse | ToolbarData;
    const payload = 'raw' in payloadResponse ? payloadResponse.raw : payloadResponse;

    if (!payload || typeof payload !== 'object') {
        throw new Error(`Invalid toolbar payload for request ${requestId}`);
    }

    return setCachedPayload(requestId, {
        ...payload,
        request_id: getPayloadRequestId(payload) ?? requestId,
    });
};

export const ensureRequestPayload = (requestId: string): Promise<ToolbarData> => {
    const cachedPayload = payloadCache.value.get(requestId);

    if (cachedPayload) {
        return Promise.resolve(cachedPayload);
    }

    const pendingPayload = pendingPayloads.get(requestId);

    if (pendingPayload) {
        return pendingPayload;
    }

    const request = loadRequestPayload(requestId).finally(() => {
        pendingPayloads.delete(requestId);
    });

    pendingPayloads.set(requestId, request);

    return request;
};

export const activeRequestId = computed(() => {
    return previewRequestId.value ?? selectedRequestId.value;
});

export const activeToolbarData = computed(() => {
    const currentActiveRequestId = activeRequestId.value;
    const currentSelectedRequestId = selectedRequestId.value;

    if (currentActiveRequestId) {
        const activePayload = payloadCache.value.get(currentActiveRequestId);

        if (activePayload) {
            return decoratePayload(activePayload, currentActiveRequestId);
        }
    }

    if (currentSelectedRequestId) {
        const selectedPayload = payloadCache.value.get(currentSelectedRequestId);

        if (selectedPayload) {
            return decoratePayload(selectedPayload, currentSelectedRequestId);
        }
    }

    return decoratePayload(baseToolbarData, currentSelectedRequestId);
});

export const requestCount = computed(() => requestHistory.value.length);

export const previewRequest = (requestId: string | null | undefined): Promise<ToolbarData | null> => {
    if (!requestId) {
        previewRequestId.value = null;

        return Promise.resolve(null);
    }

    previewRequestId.value = requestId;

    return ensureRequestPayload(requestId).catch(() => {
        if (previewRequestId.value === requestId) {
            previewRequestId.value = null;
        }

        return null;
    });
};

export const clearRequestPreview = (): void => {
    previewRequestId.value = null;
};

export const selectRequest = async (requestId: string | null | undefined): Promise<void> => {
    if (!requestId) {
        return;
    }

    await ensureRequestPayload(requestId);

    selectedRequestId.value = requestId;
    previewRequestId.value = null;
};

replaceToolbarState(baseToolbarData);

window.addEventListener('laravel-toolbar:update', (event: ToolbarUpdateEvent) => {
    if (event.detail?.data) {
        handleToolbarUpdate(event.detail.data);
    }
});
