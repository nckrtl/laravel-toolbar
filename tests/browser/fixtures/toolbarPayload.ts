/**
 * Synthetic large-request toolbar payload for production-bundle browser tests.
 * Scale matches a reported heavy Servauto-style profile (no real app SQL/session data).
 */
export const QUERY_COUNT = 261;
export const TOTAL_MODEL_HYDRATIONS = 2978;
/** Distinct model classes in the synthetic models map. */
export const MODEL_COUNT = 24;
export const REQUEST_ID = 'browser-test-request';

export function buildSyntheticToolbarPayload(options?: { animations?: boolean }) {
    const animations = options?.animations ?? true;

    const queries = Array.from({ length: QUERY_COUNT }, (_, i) => ({
        hash: `hash-${i}`,
        sql: `select * from "tasks" where "id" = ${i}`,
        bindings: [i],
        duration: 0.4 + (i % 20) * 0.05,
        connection: 'mysql',
        driver: 'mysql',
        is_duplicate: i % 17 === 0,
        is_slow: i % 29 === 0,
        percentage: 1 / QUERY_COUNT,
        offset: (i % QUERY_COUNT) / QUERY_COUNT,
        file: '/app/Http/Controllers/TaskController.php',
        line: 10 + (i % 80),
        editor_url: '#',
        type: null,
        memory_used: null,
    }));

    // Spread hydrations across MODEL_COUNT classes so the total is exact.
    const base = Math.floor(TOTAL_MODEL_HYDRATIONS / MODEL_COUNT);
    let remainder = TOTAL_MODEL_HYDRATIONS - base * MODEL_COUNT;
    const models: Record<string, unknown> = {};

    for (let i = 0; i < MODEL_COUNT; i++) {
        const count = base + (remainder > 0 ? 1 : 0);
        if (remainder > 0) {
            remainder -= 1;
        }

        const name = `App\\Models\\Entity${i}`;
        models[name] = {
            action: 'retrieved',
            model: name,
            count,
            memory_used: null,
            sources: {
                a: {
                    file: `/app/Http/Controllers/C${i}.php`,
                    line: 12,
                    count,
                    editor_url: '#',
                },
            },
        };
    }

    const totalTime = 128.4;

    return {
        request_id: REQUEST_ID,
        selected_request_id: REQUEST_ID,
        animations,
        layout: {
            sections: {
                center: [
                    {
                        priority: 10,
                        section: 'center',
                        tools: {
                            Requests: {},
                            Request: {},
                            Timings: {},
                            MemoryUsage: {},
                            Database: {},
                            Models: {},
                        },
                    },
                ],
                left: [],
                right: [],
            },
        },
        request_history: [
            {
                id: REQUEST_ID,
                is_xhr: false,
                method: 'GET',
                uri: '/tasks',
                name: 'tasks.index',
                middleware_count: 3,
                status_code: 200,
                size: '126 KB',
                duration: '180 ms',
            },
        ],
        queries: {
            totalTime,
            totalTimeFilteredQueries: totalTime,
            databases: [{ name: 'app', tablePlusConnectionUrl: null }],
            connections: ['mysql'],
            drivers: ['mysql'],
            queries,
        },
        models,
        request: {
            route_name: 'tasks.index',
            editor_url: null,
            route_editor_url: null,
            method: 'GET',
            uri: '/tasks',
            ip_address: '127.0.0.1',
            controller_action: 'App\\Http\\Controllers\\TaskController@index',
            middleware: [],
            is_inertia: true,
            uuid: REQUEST_ID,
            memory: null,
            duration: null,
        },
        response: {
            status_code: 200,
            headers: [],
            size: { formattedValue: '126 KB' },
        },
        profiler: {
            total_wall_time: { formattedValue: '180 ms', value: 180 },
            total_real_memory: null,
            total_allocated_memory: { formattedValue: '24 MB', value: 24 },
            stages: [
                {
                    label: 'Bootstrapping',
                    color: '#ef4444',
                    recordedStart: true,
                    recordedEnd: true,
                    wall_time: {
                        percentage: 35,
                        measurement: { value: 63, formattedValue: '63 ms' },
                    },
                    memory_real_delta: {
                        percentage: 35,
                        measurement: { value: 4096, formattedValue: '4 KB' },
                    },
                },
                {
                    label: 'Routing',
                    color: '#22c55e',
                    recordedStart: true,
                    recordedEnd: true,
                    wall_time: {
                        percentage: 65,
                        measurement: { value: 117, formattedValue: '117 ms' },
                    },
                    memory_real_delta: {
                        percentage: 65,
                        measurement: { value: 8192, formattedValue: '8 KB' },
                    },
                },
            ],
        },
        php: {
            version: '8.4.0',
            memory_limit: '512M',
            max_execution_time: '30',
        },
        laravel: {
            version: '12.0.0',
            version_editor_url: null,
            environment: 'local',
            environment_editor_url: null,
            timezone: 'UTC',
            timezone_editor_url: null,
            locale: 'en',
            locale_editor_url: null,
            debug: 'true',
            debug_editor_url: null,
        },
        vue: { version: '3.5.0' },
        tailwind: { version: '4.0.0' },
        inertia: { version: '2.0.0' },
        metadata: { debug: false, request_id: REQUEST_ID },
    };
}

/**
 * v0.3.2 compact bootstrap shape: layout + history + ids only.
 * Missing request/response forces async fetch via /_toolbar/requests/{id}.
 */
export function buildCompactBootstrapPayload(options?: { animations?: boolean }) {
    const full = buildSyntheticToolbarPayload(options);

    return {
        request_id: full.request_id,
        selected_request_id: full.selected_request_id,
        animations: full.animations,
        layout: full.layout,
        request_history: full.request_history,
        metadata: full.metadata,
    };
}

/**
 * RequestDataController JSON shape: { summary, raw }.
 * Frontend prefers `raw` when present.
 */
export function buildRequestDataEndpointBody(
    fullPayload: ReturnType<typeof buildSyntheticToolbarPayload> = buildSyntheticToolbarPayload(),
) {
    const queries = fullPayload.queries?.queries ?? [];

    return {
        summary: {
            auth_mode: 'guest',
            request: {
                route_name: fullPayload.request?.route_name ?? '-',
                controller_action: fullPayload.request?.controller_action ?? '-',
            },
            profiler: {
                total_wall_time: fullPayload.profiler?.total_wall_time?.formattedValue ?? '0ms',
                total_real_memory: '0B',
                total_allocated_memory:
                    fullPayload.profiler?.total_allocated_memory?.formattedValue ?? '0B',
                stages: (fullPayload.profiler?.stages ?? []).map((stage: any) => ({
                    label: stage.label,
                    duration: stage.wall_time?.measurement?.formattedValue ?? '0ms',
                })),
            },
            queries: {
                count: queries.length,
                slow_count: queries.filter((q: any) => q.is_slow).length,
                duplicate_count: queries.filter((q: any) => q.is_duplicate).length,
            },
            timing_anchors: null,
        },
        raw: fullPayload,
    };
}

/** Database chrome summary uses Math.round(totalTime). */
export const DATABASE_SUMMARY_MS = Math.round(128.4);
