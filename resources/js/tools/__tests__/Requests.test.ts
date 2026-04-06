import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { describe, expect, it, vi } from 'vitest';
import ToolbarItem from '@/components/ToolbarItem.vue';
import Request from '@/tools/Request.vue';
import Requests from '@/tools/Requests.vue';
import type { RequestHistoryRow, ToolbarData } from '@/types';

const flush = async (): Promise<void> => {
    await Promise.resolve();
    await Promise.resolve();
    await nextTick();
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
    response_type: 'HTML',
    status_code: 200,
    size: '1 kB',
    duration: '12 ms',
    ...overrides,
});

const makeToolbarData = (overrides: Partial<ToolbarData> = {}): ToolbarData => ({
    request_id: 'request-2',
    selected_request_id: 'request-2',
    request_history: [
        makeHistoryRow('request-1', {
            method: 'POST',
            uri: '/posts',
            name: 'posts.store',
            middleware_count: 2,
            response_type: 'JSON',
            status_code: 302,
            size: '512 B',
            duration: '4 ms',
            is_xhr: true,
        }),
        makeHistoryRow('request-2', {
            uri: '/dashboard',
            name: 'dashboard',
            response_type: 'HTML',
            status_code: 200,
            size: '2 kB',
            duration: '12 ms',
        }),
    ],
    request: makeRequest({
        uri: '/dashboard',
        method: 'GET',
        route_name: 'dashboard',
    }),
    response: makeResponse(200, '2 kB'),
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

const Harness = {
    components: {
        Request,
        Requests,
    },
    template: '<div><Requests /><Request /></div>',
};

describe('Requests tool', () => {
    it('previews on hover and persists selection on click', async () => {
        setToolbarData(makeToolbarData());

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

        const wrapper = mount(Harness);
        const requestsTool = wrapper.findComponent(Requests);
        const requestTool = wrapper.findComponent(Request);

        await requestsTool.findComponent(ToolbarItem).trigger('mouseenter');
        await requestTool.findComponent(ToolbarItem).trigger('mouseenter');
        await nextTick();

        expect(requestsTool.text()).toContain('Type');
        expect(requestsTool.text()).toContain('Response');
        expect(requestsTool.text()).toContain('Async');
        expect(requestsTool.text()).toContain('Page');
        expect(requestsTool.text()).toContain('JSON');
        expect(requestsTool.text()).toContain('HTML');
        expect(requestsTool.text()).toContain('2');
        expect(requestTool.text()).toContain('/dashboard');

        await requestsTool.find('[data-request-id="request-1"]').trigger('mouseenter');
        await flush();

        expect(global.fetch).toHaveBeenCalledTimes(1);
        expect(requestTool.text()).toContain('/posts');
        expect(requestTool.text()).toContain('302 - Found');

        await requestsTool.find('.requests-table').trigger('mouseleave');
        await flush();

        expect(requestTool.text()).toContain('/dashboard');
        expect(requestTool.text()).toContain('200 - OK');

        await requestsTool.find('[data-request-id="request-1"]').trigger('click');
        await flush();

        expect(global.fetch).toHaveBeenCalledTimes(1);

        await requestsTool.find('.requests-table').trigger('mouseleave');
        await flush();

        expect(requestTool.text()).toContain('/posts');
        expect(requestTool.text()).toContain('302 - Found');
    });
});
