import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { setupInterceptors } from '@/core/interceptors';

const encode = (value: unknown): string => {
    return btoa(JSON.stringify(value));
};

describe('interceptors', () => {
    const originalFetch = window.fetch;

    beforeEach(() => {
        vi.restoreAllMocks();
        window.fetch = originalFetch;
    });

    afterEach(() => {
        window.fetch = originalFetch;
    });

    it('dispatches request history updates from x-toolbar-summary headers', async () => {
        const fetchMock = vi.fn(async () => {
            return new Response(null, {
                status: 200,
                headers: {
                    'x-toolbar-summary': encode({
                        request_id: 'request-2',
                        history_row: {
                            id: 'request-2',
                            is_xhr: true,
                            method: 'POST',
                            uri: '/api/users',
                            name: 'api.users.store',
                            middleware_count: 2,
                            status_code: 201,
                            size: '512 B',
                            duration: '4.20ms',
                        },
                    }),
                },
            });
        });

        window.fetch = fetchMock as typeof fetch;

        const eventHandler = vi.fn();
        window.addEventListener('laravel-toolbar:update', eventHandler);

        setupInterceptors();
        await window.fetch('/api/users', {
            headers: {
                Accept: 'application/json',
            },
        });

        expect(eventHandler).toHaveBeenCalledTimes(1);
        expect(eventHandler.mock.calls[0][0].detail.data).toEqual({
            request_id: 'request-2',
            history_row: {
                id: 'request-2',
                is_xhr: true,
                method: 'POST',
                uri: '/api/users',
                name: 'api.users.store',
                middleware_count: 2,
                status_code: 201,
                size: '512 B',
                duration: '4.20ms',
            },
        });
    });

    it('ignores internal toolbar snapshot fetches', async () => {
        const fetchMock = vi.fn(async () => {
            return new Response(null, {
                status: 200,
                headers: {
                    'x-toolbar': encode({
                        request_id: 'request-internal',
                        history_row: {
                            id: 'request-internal',
                        },
                    }),
                },
            });
        });

        window.fetch = fetchMock as typeof fetch;

        const eventHandler = vi.fn();
        window.addEventListener('laravel-toolbar:update', eventHandler);

        setupInterceptors();
        await window.fetch('/_toolbar/requests/request-internal', {
            headers: {
                'X-Laravel-Toolbar-Internal': 'true',
            },
        });

        expect(eventHandler).not.toHaveBeenCalled();
    });
});
