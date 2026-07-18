import { mount } from "@vue/test-utils";
import { nextTick } from "vue";
import { afterEach, describe, expect, it, vi } from "vitest";
import SharedPanel from "@/components/SharedPanel.vue";
import ToolbarItem from "@/components/ToolbarItem.vue";
import { activeToolId, usePinnedPanel } from "@/composables/usePinnedPanel";
import { activeToolbarData } from "@/core/request-history";
import Requests from "@/tools/Requests.vue";
import type { RequestHistoryRow, ToolbarData } from "@/types";

const flush = async (): Promise<void> => {
    await Promise.resolve();
    await Promise.resolve();
    await nextTick();
};

const makeRequest = (overrides = {}): NckRtl.Toolbar.Data.RequestData => ({
    route_name: "home",
    editor_url: null,
    route_editor_url: null,
    method: "GET",
    uri: "/",
    ip_address: "127.0.0.1",
    controller_action: "App\\Http\\Controllers\\HomeController@index",
    middleware: ["web"],
    is_inertia: false,
    uuid: "1234567890abcdef",
    memory: null,
    duration: null,
    ...overrides,
});

const makeResponse = (
    statusCode: number,
    formattedValue = "1 kB",
): NckRtl.Toolbar.Data.ResponseData => ({
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
    method: "GET",
    uri: "/",
    name: "home",
    middleware_count: 1,
    response_type: "HTML",
    status_code: 200,
    size: "1 kB",
    duration: "12 ms",
    ...overrides,
});

const makeToolbarData = (overrides: Partial<ToolbarData> = {}): ToolbarData => ({
    request_id: "request-2",
    selected_request_id: "request-2",
    request_history: [
        makeHistoryRow("request-1", {
            method: "POST",
            uri: "/posts",
            name: "posts.store",
            middleware_count: 2,
            response_type: "JSON",
            status_code: 302,
            size: "512 B",
            duration: "4 ms",
            is_xhr: true,
        }),
        makeHistoryRow("request-2", {
            uri: "/dashboard",
            name: "dashboard",
            response_type: "HTML",
            status_code: 200,
            size: "2 kB",
            duration: "12 ms",
        }),
    ],
    request: makeRequest({
        uri: "/dashboard",
        method: "GET",
        route_name: "dashboard",
    }),
    response: makeResponse(200, "2 kB"),
    profiler: {
        total_wall_time: {
            formattedValue: "12 ms",
        },
    } as NckRtl.Toolbar.Data.ProfilerData,
    ...overrides,
});

const setToolbarData = (data: ToolbarData): void => {
    window.dispatchEvent(
        new CustomEvent("laravel-toolbar:update", {
            detail: {
                data,
            },
        }),
    );
};

const Harness = {
    components: {
        Requests,
        SharedPanel,
    },
    template: "<div><Requests /><SharedPanel /></div>",
};

const showTool = async (wrapper, tool): Promise<void> => {
    if (activeToolId.value !== tool) {
        await wrapper.findComponent(Requests).findComponent(ToolbarItem).trigger("click");
        await nextTick();
    }
};

afterEach(() => {
    if (activeToolId.value !== null) {
        usePinnedPanel(activeToolId.value).togglePin();
    }
});

describe("Requests tool", () => {
    it("previews on hover and persists selection on click", async () => {
        setToolbarData(makeToolbarData());

        global.fetch = vi.fn(async () => {
            return new Response(
                JSON.stringify({
                    summary: {
                        request: {
                            route_name: "posts.store",
                        },
                    },
                    raw: makeToolbarData({
                        request_id: "request-1",
                        selected_request_id: "request-1",
                        request: makeRequest({
                            method: "POST",
                            uri: "/posts",
                            route_name: "posts.store",
                        }),
                        response: makeResponse(302, "512 B"),
                    }),
                }),
                {
                    status: 200,
                    headers: {
                        "Content-Type": "application/json",
                    },
                },
            );
        }) as typeof fetch;

        const wrapper = mount(Harness);

        await showTool(wrapper, "requests");

        let panel = wrapper.findComponent(SharedPanel);

        expect(panel.text()).toContain("Type");
        expect(panel.text()).toContain("Response");
        expect(panel.text()).toContain("Async");
        expect(panel.text()).toContain("Page");
        expect(panel.text()).toContain("JSON");
        expect(panel.text()).toContain("HTML");
        expect(panel.text()).toContain("2");

        await panel.find('[data-request-id="request-1"]').trigger("mouseenter");
        await flush();

        expect(global.fetch).toHaveBeenCalledTimes(1);
        expect(activeToolbarData.value.request?.uri).toBe("/posts");
        expect(activeToolbarData.value.response?.status_code).toBe(302);

        await panel.find(".requests-table").trigger("mouseleave");
        await flush();

        expect(activeToolbarData.value.request?.uri).toBe("/dashboard");
        expect(activeToolbarData.value.response?.status_code).toBe(200);

        await panel.find('[data-request-id="request-1"]').trigger("click");
        await flush();

        expect(global.fetch).toHaveBeenCalledTimes(1);

        await panel.find(".requests-table").trigger("mouseleave");
        await flush();

        expect(activeToolbarData.value.request?.uri).toBe("/posts");
        expect(activeToolbarData.value.response?.status_code).toBe(302);
    });
});
