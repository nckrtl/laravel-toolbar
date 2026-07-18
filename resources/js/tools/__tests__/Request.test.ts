import { mount } from "@vue/test-utils";
import { nextTick } from "vue";
import { afterEach, describe, expect, it } from "vitest";
import SharedPanel from "@/components/SharedPanel.vue";
import ToolbarItem from "@/components/ToolbarItem.vue";
import Pill from "@/components/Pill.vue";
import { activeToolId, usePinnedPanel } from "@/composables/usePinnedPanel";
import Request from "@/tools/Request.vue";
import type { RequestHistoryRow, ToolbarData } from "@/types";

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
    status_code: 200,
    size: "1 kB",
    duration: "12 ms",
    ...overrides,
});

const makeToolbarData = (overrides: Partial<ToolbarData> = {}): ToolbarData => ({
    request_id: "request-1",
    selected_request_id: "request-1",
    request_history: [makeHistoryRow("request-1")],
    request: makeRequest(),
    response: makeResponse(200),
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
        Request,
        SharedPanel,
    },
    template: "<div><Request /><SharedPanel /></div>",
};

const openPanel = async (wrapper): Promise<void> => {
    if (activeToolId.value !== "request") {
        await wrapper.findComponent(Request).findComponent(ToolbarItem).trigger("click");
    }
    await nextTick();
};

afterEach(() => {
    if (activeToolId.value === "request") {
        usePinnedPanel("request").togglePin();
    }
});

describe("Request tool", () => {
    it("renders the active request payload", async () => {
        setToolbarData(
            makeToolbarData({
                request_id: "request-posts",
                selected_request_id: "request-posts",
                request_history: [
                    makeHistoryRow("request-posts", {
                        method: "POST",
                        uri: "/posts",
                        status_code: 204,
                        size: "0 B",
                    }),
                ],
                request: makeRequest({
                    method: "POST",
                    uri: "/posts",
                }),
                response: makeResponse(204, "0 B"),
            }),
        );

        const wrapper = mount(Harness);
        await openPanel(wrapper);

        const panel = wrapper.findComponent(SharedPanel);
        const text = panel.text();

        expect(text).toContain("/posts");
        expect(text).toContain("204 No Content");
        expect(text.match(/Method/g)).toHaveLength(1);
        expect(text.match(/Status/g)).toHaveLength(1);
        expect(wrapper.findComponent(Request).findComponent(Pill).text()).toBe("204");
    });

    it("ignores redirect_chain and renders only the active request payload", async () => {
        setToolbarData(
            makeToolbarData({
                request_id: "request-final",
                selected_request_id: "request-final",
                request_history: [
                    makeHistoryRow("request-final", {
                        uri: "/final",
                        status_code: 200,
                        size: "2 kB",
                    }),
                ],
                request: makeRequest({
                    uri: "/final",
                    method: "GET",
                }),
                response: makeResponse(200, "2 kB"),
                redirect_chain: [
                    {
                        request: makeRequest({
                            uri: "/first",
                            method: "POST",
                        }),
                        response: makeResponse(301, "512 B"),
                    },
                    {
                        request: makeRequest({
                            uri: "/second",
                            method: "GET",
                        }),
                        response: makeResponse(302, "768 B"),
                    },
                ],
            }),
        );

        const wrapper = mount(Harness);
        await openPanel(wrapper);

        const panel = wrapper.findComponent(SharedPanel);
        const text = panel.text();

        expect(text).toContain("/final");
        expect(text).not.toContain("/first");
        expect(text).not.toContain("/second");
        expect(text).toContain("200 OK");
        expect(text.match(/Method/g)).toHaveLength(1);
        expect(text.match(/Status/g)).toHaveLength(1);
        expect(wrapper.findComponent(Request).findComponent(Pill).text()).toBe("200");
    });

    it("reacts to selected payload updates", async () => {
        const wrapper = mount(Harness);

        setToolbarData(
            makeToolbarData({
                request_id: "request-initial",
                selected_request_id: "request-initial",
                request_history: [
                    makeHistoryRow("request-initial", {
                        uri: "/initial",
                        status_code: 200,
                    }),
                ],
                request: makeRequest({
                    uri: "/initial",
                    method: "GET",
                }),
                response: makeResponse(200, "1 kB"),
            }),
        );
        await openPanel(wrapper);

        expect(wrapper.findComponent(SharedPanel).text()).toContain("/initial");

        setToolbarData(
            makeToolbarData({
                request_id: "request-updated",
                selected_request_id: "request-updated",
                request_history: [
                    makeHistoryRow("request-updated", {
                        uri: "/updated",
                        status_code: 201,
                        size: "3 kB",
                    }),
                ],
                request: makeRequest({
                    uri: "/updated",
                    method: "PUT",
                }),
                response: makeResponse(201, "3 kB"),
            }),
        );
        await nextTick();

        const panel = wrapper.findComponent(SharedPanel);
        const text = panel.text();

        expect(text).toContain("/updated");
        expect(text).toContain("201 Created");
        expect(wrapper.findComponent(Request).findComponent(Pill).text()).toBe("201");
    });
});
