import { describe, expect, it, vi } from 'vitest';
import { useRequestHistory } from '@/composables/useRequestHistory';
import type { RequestHistoryRow, ToolbarData } from '@/types';

const flush = async (): Promise<void> => {
    await Promise.resolve();
    await Promise.resolve();
};

const makeRequest = (overrides = {}): NckRtl.Toolbar.Data.RequestData => ({
    route_name: 'home',
    editor_url: null,
    route_editor_url: null,
    method: 'GET',
    uri: '/',
    ip_address: '127.0.0.1',
    controller_action: 'App\\Http\\Controllers\\HomeController@index',
    middleware: ['web'],
    is_inertia: false,
    uuid: '1234567890abcdef',
    memory: null,
    duration: null,
    ...overrides,
});

const makeResponse = (statusCode: number, formattedValue = '1 kB'): NckRtl.Toolbar.Data.ResponseData => ({
    status_code: statusCode,
    headers: [],
    size: {
        formattedValue,
    },
});

const makeHistoryRow = (
    id: string,
    overrides: Partial<RequestHistoryRow> = {},
): RequestHistoryRow => ({
    id,
    is_xhr: false,
    method: 'GET',
    uri: '/',
    name: 'home',
    middleware_count: 1,
    status_code: 200,
    size: '1 kB',
    duration: '12 ms',
    ...overrides,
});

const makeToolbarData = (overrides: Partial<ToolbarData> = {}): ToolbarData => ({
    request_id: 'request-1',
    selected_request_id: 'request-1',
    request_history: [makeHistoryRow('request-1')],
    request: makeRequest(),
    response: makeResponse(200),
    profiler: {
        total_wall_time: {
            formattedValue: '12 ms',
        },
    } as NckRtl.Toolbar.Data.ProfilerData,
    ...overrides,
});

const setToolbarData = (data: ToolbarData): void => {
    window.dispatchEvent(
        new CustomEvent('laravel-toolbar:update', {
            detail: {
                data,
            },
        }),
    );
};

describe('useRequestHistory', () => {
    it('appends xhr rows without changing the selected request', () => {
        setToolbarData(makeToolbarData());

        const { activeRequestId, requestHistory, selectedRequestId } = useRequestHistory();

        window.dispatchEvent(
            new CustomEvent('laravel-toolbar:update', {
                detail: {
                    data: {
                        request_id: 'request-2',
                        history_row: makeHistoryRow('request-2', {
                            is_xhr: true,
                            method: 'POST',
                            uri: '/api/users',
                            name: 'api.users.store',
                            status_code: 201,
                        }),
                    },
                },
            }),
        );

        expect(requestHistory.value.map((row) => row.id)).toEqual(['request-1', 'request-2']);
        expect(selectedRequestId.value).toBe('request-1');
        expect(activeRequestId.value).toBe('request-1');
    });

    it('replaces history when a page-like inertia payload arrives', () => {
        setToolbarData(
            makeToolbarData({
                request_id: 'request-1',
                selected_request_id: 'request-1',
                request_history: [
                    makeHistoryRow('request-1', {
                        uri: '/test',
                    }),
                    makeHistoryRow('request-legacy', {
                        is_xhr: true,
                        uri: '/api/legacy',
                    }),
                ],
                request: makeRequest({
                    uri: '/test',
                    route_name: 'home.test',
                }),
            }),
        );

        const { activeRequestId, data, requestHistory, selectedRequestId } = useRequestHistory();

        window.dispatchEvent(
            new CustomEvent('laravel-toolbar:update', {
                detail: {
                    data: makeToolbarData({
                        request_id: 'request-2',
                        selected_request_id: 'request-2',
                        request_history: [
                            makeHistoryRow('request-2', {
                                is_xhr: false,
                                uri: '/second',
                                name: 'home.second',
                                response_type: 'Inertia',
                            }),
                        ],
                        request: makeRequest({
                            uri: '/second',
                            route_name: 'home.second',
                            is_inertia: true,
                        }),
                        response: makeResponse(200, '2 kB'),
                    }),
                },
            }),
        );

        expect(requestHistory.value.map((row) => row.id)).toEqual(['request-2']);
        expect(selectedRequestId.value).toBe('request-2');
        expect(activeRequestId.value).toBe('request-2');
        expect(data.value.request?.uri).toBe('/second');
    });

    it('filters explicit internal and debug rows from request history', () => {
        setToolbarData(
            makeToolbarData({
                request_id: 'request-visible',
                selected_request_id: 'request-visible',
                request_history: [
                    makeHistoryRow('request-visible', {
                        uri: '/dashboard',
                    }),
                    makeHistoryRow('request-browser-logs', {
                        uri: '/_boost/browser-logs',
                    }),
                    makeHistoryRow('request-toolbar', {
                        uri: '/_toolbar/requests/request-toolbar',
                    }),
                ],
            }),
        );

        const { requestCount, requestHistory } = useRequestHistory();

        expect(requestCount.value).toBe(1);
        expect(requestHistory.value.map((row) => row.id)).toEqual(['request-visible']);

        window.dispatchEvent(
            new CustomEvent('laravel-toolbar:update', {
                detail: {
                    data: {
                        request_id: 'request-browser-logs-live',
                        history_row: makeHistoryRow('request-browser-logs-live', {
                            uri: '/_boost/browser-logs',
                        }),
                    },
                },
            }),
        );

        window.dispatchEvent(
            new CustomEvent('laravel-toolbar:update', {
                detail: {
                    data: {
                        request_id: 'request-toolbar-live',
                        history_row: makeHistoryRow('request-toolbar-live', {
                            uri: '/_toolbar/requests/request-toolbar-live',
                        }),
                    },
                },
            }),
        );

        expect(requestCount.value).toBe(1);
        expect(requestHistory.value.map((row) => row.id)).toEqual(['request-visible']);
    });

    it('loads uncached payloads once and reuses the client cache', async () => {
        setToolbarData(
            makeToolbarData({
                request_id: 'request-2',
                selected_request_id: 'request-2',
                request_history: [
                    makeHistoryRow('request-1', {
                        method: 'POST',
                        uri: '/posts',
                        status_code: 302,
                    }),
                    makeHistoryRow('request-2', {
                        uri: '/dashboard',
                        status_code: 200,
                    }),
                ],
                request: makeRequest({
                    uri: '/dashboard',
                    route_name: 'dashboard',
                }),
                response: makeResponse(200, '2 kB'),
            }),
        );

        global.fetch = vi.fn(async () => {
            return new Response(
                JSON.stringify(
                    {
                        summary: {
                            request: {
                                route_name: 'posts.store',
                            },
                        },
                        raw: makeToolbarData({
                            request_id: 'request-1',
                            selected_request_id: 'request-1',
                            request: makeRequest({
                                method: 'POST',
                                uri: '/posts',
                                route_name: 'posts.store',
                            }),
                            response: makeResponse(302, '512 B'),
                        }),
                    },
                ),
                {
                    status: 200,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                },
            );
        }) as typeof fetch;

        const { data, previewRequest, selectRequest, selectedRequestId } = useRequestHistory();

        await previewRequest('request-1');
        await flush();

        expect(global.fetch).toHaveBeenCalledTimes(1);
        expect(global.fetch).toHaveBeenCalledWith('/_toolbar/requests/request-1', {
            headers: {
                Accept: 'application/json',
                'X-Laravel-Toolbar-Internal': 'true',
            },
        });
        expect(data.value.request?.uri).toBe('/posts');

        await selectRequest('request-1');
        await flush();

        expect(global.fetch).toHaveBeenCalledTimes(1);
        expect(selectedRequestId.value).toBe('request-1');
        expect(data.value.response?.status_code).toBe(302);
    });

    it('clears preview state when a preview fetch fails', async () => {
        setToolbarData(
            makeToolbarData({
                request_id: 'request-2',
                selected_request_id: 'request-2',
                request_history: [
                    makeHistoryRow('request-1', {
                        method: 'POST',
                        uri: '/posts',
                        status_code: 302,
                    }),
                    makeHistoryRow('request-2', {
                        uri: '/dashboard',
                        status_code: 200,
                    }),
                ],
                request: makeRequest({
                    uri: '/dashboard',
                    route_name: 'dashboard',
                }),
                response: makeResponse(200, '2 kB'),
            }),
        );

        global.fetch = vi.fn(async () => {
            return new Response(null, {
                status: 404,
            });
        }) as typeof fetch;

        const { activeRequestId, data, previewRequest, previewRequestId, selectedRequestId } =
            useRequestHistory();

        await previewRequest('request-1');
        await flush();

        expect(global.fetch).toHaveBeenCalledTimes(1);
        expect(previewRequestId.value).toBeNull();
        expect(activeRequestId.value).toBe('request-2');
        expect(selectedRequestId.value).toBe('request-2');
        expect(data.value.request?.uri).toBe('/dashboard');
    });
});
