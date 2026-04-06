import type { ToolbarData } from '@/types';

const defaultToolbarData: ToolbarData = {
    request_id: 'default-request-id',
    selected_request_id: 'default-request-id',
    request_history: [
        {
            id: 'default-request-id',
            is_xhr: false,
            method: 'GET',
            uri: '/',
            name: 'home',
            middleware_count: 0,
            status_code: 200,
            size: '0 B',
            duration: '0 ms',
        },
    ],
    request: {
        route_name: 'home',
        editor_url: null,
        route_editor_url: null,
        method: 'GET',
        uri: '/',
        ip_address: '127.0.0.1',
        controller_action: 'App\\Http\\Controllers\\HomeController@index',
        middleware: [],
        is_inertia: false,
        uuid: 'default-request-uuid',
        memory: null,
        duration: null,
    },
    response: {
        status_code: 200,
        headers: [],
        size: {
            formattedValue: '0 B',
        },
    },
    profiler: {
        total_wall_time: {
            formattedValue: '0 ms',
        },
    } as NckRtl.Toolbar.Data.ProfilerData,
};

window.__LARAVEL_TOOLBAR_DATA__ = defaultToolbarData;
window.__LARAVEL_TOOLBAR_CSS_URL__ = '/toolbar.css';
