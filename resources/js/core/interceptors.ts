import { log, logData } from '@/core/utils/logger';
import type { ToolbarData } from '@/types';

type ResponseLike = Response | XMLHttpRequest;
type ToolbarSummaryUpdate = Pick<ToolbarData, 'history_row' | 'request_id'>;

export const setupInterceptors = (): void => {
    log('Setting up interceptors BEFORE Inertia loads');

    setupFetchInterceptor();
    setupXHRInterceptor();

    log('Interceptors installed successfully');
};

const setupFetchInterceptor = (): void => {
    const originalFetch = window.fetch;

    window.fetch = async function (...args: Parameters<typeof fetch>): Promise<Response> {
        const [input, init] = args;
        const response = await originalFetch.apply(this, args);

        if (!isInternalToolbarRequest(input, init)) {
            handleToolbarHeader(response);
        }

        return response;
    };
};

const setupXHRInterceptor = (): void => {
    const originalSend = XMLHttpRequest.prototype.send;

    XMLHttpRequest.prototype.send = function (
        this: XMLHttpRequest,
        ...args: Parameters<XMLHttpRequest['send']>
    ): void {
        this.addEventListener('load', function (this: XMLHttpRequest) {
            handleToolbarHeader(this);
        });

        return originalSend.apply(this, args);
    };
};

const handleToolbarHeader = (responseObject: ResponseLike): void => {
    const toolbarHeader =
        responseObject instanceof Response
            ? responseObject.headers.get('x-toolbar')
            : responseObject.getResponseHeader('x-toolbar');
    const toolbarSummaryHeader =
        responseObject instanceof Response
            ? responseObject.headers.get('x-toolbar-summary')
            : responseObject.getResponseHeader('x-toolbar-summary');

    if (toolbarHeader) {
        try {
            const decoded = atob(toolbarHeader);
            const update = JSON.parse(decoded) as ToolbarData;

            log('🟢 Toolbar data updated from response');

            window.dispatchEvent(
                new CustomEvent('laravel-toolbar:update', {
                    detail: { data: update },
                }),
            );

            logData(update);
        } catch (e) {
            log('🔴 Failed to parse toolbar data', e);
        }
    }

    if (toolbarSummaryHeader) {
        try {
            const decoded = atob(toolbarSummaryHeader);
            const summary = JSON.parse(decoded) as ToolbarData;
            const update: ToolbarSummaryUpdate = {
                request_id: summary.request_id,
                history_row: summary.history_row,
            };

            if (!update.request_id || !update.history_row?.id) {
                return;
            }

            log('🟢 Toolbar summary updated from response');

            window.dispatchEvent(
                new CustomEvent('laravel-toolbar:update', {
                    detail: { data: update },
                }),
            );

            logData(update);
        } catch (e) {
            log('🔴 Failed to parse toolbar summary', e);
        }
    }
};

const isInternalToolbarRequest = (
    input: RequestInfo | URL,
    init?: RequestInit,
): boolean => {
    const url =
        typeof input === 'string'
            ? input
            : input instanceof URL
              ? input.toString()
              : input.url;

    if (url.includes('/_toolbar/requests/')) {
        return true;
    }

    const headers = new Headers(
        init?.headers ??
            (input instanceof Request ? input.headers : undefined),
    );

    return headers.get('X-Laravel-Toolbar-Internal') === 'true';
};
