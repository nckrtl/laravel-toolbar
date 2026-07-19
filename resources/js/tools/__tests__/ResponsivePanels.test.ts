import { mount } from "@vue/test-utils";
import { nextTick } from "vue";
import { describe, expect, it } from "vitest";
import SharedPanel from "@/components/SharedPanel.vue";
import ScrollableTable from "@/components/ScrollableTable.vue";
import { activeToolId, usePinnedPanel } from "@/composables/usePinnedPanel";
import DatabasePanel from "@/tools/panels/database.vue";
import MemoryPanel from "@/tools/panels/memory.vue";
import ModelsPanel from "@/tools/panels/models.vue";
import RequestPanel from "@/tools/panels/request.vue";
import RequestsPanel from "@/tools/panels/requests.vue";
import TimingsPanel from "@/tools/panels/timings.vue";

const setProfilerData = () => {
    window.dispatchEvent(
        new CustomEvent("laravel-toolbar:update", {
            detail: {
                data: {
                    ...window.__LARAVEL_TOOLBAR_DATA__,
                    selected_request_id: "default-request-id",
                    request_history: window.__LARAVEL_TOOLBAR_DATA__.request_history,
                    profiler: {
                        total_wall_time: { formattedValue: "12 ms" },
                        total_real_memory: null,
                        total_allocated_memory: null,
                        stages: [
                            {
                                label: "Bootstrapping",
                                color: "#ef4444",
                                recordedStart: true,
                                recordedEnd: true,
                                wall_time: {
                                    percentage: 100,
                                    measurement: { value: 12, formattedValue: "12 ms" },
                                },
                                memory_real_delta: {
                                    percentage: 100,
                                    measurement: { value: 1024, formattedValue: "1 KB" },
                                },
                            },
                        ],
                    } as NckRtl.Toolbar.Data.ProfilerData,
                },
            },
        }),
    );
};

const setModelsData = () => {
    window.dispatchEvent(
        new CustomEvent("laravel-toolbar:update", {
            detail: {
                data: {
                    ...window.__LARAVEL_TOOLBAR_DATA__,
                    profiler: {
                        total_wall_time: { formattedValue: "0 ms" },
                        total_real_memory: null,
                        total_allocated_memory: null,
                        stages: [],
                    } as NckRtl.Toolbar.Data.ProfilerData,
                    models: {
                        "App\\Models\\Demo\\Store": {
                            count: 1,
                            action: "retrieved",
                            sources: {
                                source: {
                                    count: 1,
                                    file: "RunToolbarDemoWorkload.php",
                                    line: 28,
                                    editor_url: "#",
                                },
                                controller: {
                                    count: 1,
                                    file: "StoreController.php",
                                    line: 42,
                                    editor_url: "#",
                                },
                            },
                        },
                    },
                },
            },
        }),
    );
};

describe("responsive toolbar panels", () => {
    it("lets the request panel shrink to the viewport and stacks its sections on mobile", async () => {
        const requestPanel = usePinnedPanel("request", { size: "xl" });

        if (activeToolId.value === "request") {
            requestPanel.togglePin();
        }

        requestPanel.togglePin();

        const sharedPanel = mount(SharedPanel);
        await nextTick();

        const panel = sharedPanel.find(".fixed.inset-x-0");
        const content = mount(RequestPanel);

        expect(panel.classes()).toContain("min-w-0");
        expect(panel.classes()).toContain("md:min-w-4xl");
        expect(content.classes()).toContain("grid-cols-1");
        expect(content.classes()).toContain("md:grid-cols-2");

        requestPanel.togglePin();
    });

    it("lets medium panels fill mobile viewports while restoring their fixed desktop width", async () => {
        setProfilerData();
        const timingsPanel = usePinnedPanel("timings", { size: "md" });

        if (activeToolId.value === "timings") {
            timingsPanel.togglePin();
        }

        timingsPanel.togglePin();

        const sharedPanel = mount(SharedPanel);
        await nextTick();

        const panel = sharedPanel.find(".fixed.inset-x-0");

        expect(panel.classes()).toContain("min-w-[calc(100vw-10px)]");
        expect(panel.classes()).toContain("max-w-[calc(100vw-10px)]");
        expect(panel.classes()).toContain("md:min-w-md");
        expect(panel.classes()).toContain("md:max-w-md");

        timingsPanel.togglePin();
    });

    it("keeps the requests table wide inside its own horizontal scroller", () => {
        const wrapper = mount(RequestsPanel);
        const tableBody = wrapper.find(".requests-table");
        const tableScroller = tableBody.element.parentElement;

        expect(tableBody.classes()).toContain("min-w-[64rem]");
        expect(tableScroller?.classList.contains("overflow-x-auto")).toBe(true);
        expect(tableScroller?.classList.contains("scrollbar-none")).toBe(true);
    });

    it("keeps the queries table wide inside its own horizontal scroller", () => {
        const wrapper = mount(DatabasePanel);
        const header = wrapper.find(".queries-header");
        const metrics = wrapper.find(".queries-stats");
        const databaseMetric = metrics
            .findAll("div")
            .find((element) => element.text().trim().startsWith("Database"));
        const labels = metrics
            .findAll("span")
            .filter((element) =>
                ["Queries", "Duration", "Database"].includes(element.text().trim()),
            );
        const tableBody = wrapper
            .findAll("div")
            .find((element) => element.classes().includes("max-h-[220px]"));
        const scrollableTable = wrapper.findComponent(ScrollableTable);

        expect(wrapper.classes()).not.toContain("overflow-hidden");
        expect(header.classes()).toContain("shrink-0");
        expect(header.classes()).toContain("items-center");
        expect(header.classes()).toContain("justify-between");
        expect(header.classes()).not.toContain("flex-col");
        expect(metrics.classes()).toContain("shrink-0");
        expect(metrics.classes()).toContain("ml-auto");
        expect(metrics.classes()).toContain("w-auto");
        expect(metrics.classes()).toContain("justify-end");
        expect(metrics.classes()).toContain("pr-0");
        expect(databaseMetric?.classes()).toContain("hidden");
        expect(databaseMetric?.classes()).toContain("sm:flex");
        expect(labels).toHaveLength(3);
        expect(labels.every((label) => label.classes().includes("whitespace-nowrap"))).toBe(true);
        expect(scrollableTable.exists()).toBe(true);
        expect(scrollableTable.props("minWidth")).toBe("48rem");
        expect(scrollableTable.classes()).toContain("-mx-2");
        expect(scrollableTable.find(".scrollable-table-track").classes()).toContain("px-2");
        expect(scrollableTable.find(".scrollable-table-header").element.nextElementSibling).toBe(
            scrollableTable.find(".scrollable-table-body").element,
        );
        expect(wrapper.find(".mobile-table-right-fade").exists()).toBe(false);
        expect(tableBody?.classes()).toContain("scrollable-table-body");
        expect(
            scrollableTable
                .find(".scrollable-table-horizontal-scroller")
                .classes()
                .includes("overflow-x-auto"),
        ).toBe(true);
    });

    it("matches the memory bar spacing above the first stage", () => {
        setProfilerData();
        const wrapper = mount(TimingsPanel);
        const section = wrapper.findComponent({ name: "Section" });
        const bar = section.find(".timings-bar");

        expect(section.classes()).toContain("gap-0!");
        expect(bar.classes()).toContain("pt-3.5");
        expect(bar.classes()).toContain("pb-1.5");
    });

    it("balances the memory bar spacing above the first stage", () => {
        setProfilerData();
        const wrapper = mount(MemoryPanel);
        const section = wrapper.findComponent({ name: "Section" });
        const bar = section.find(".memory-bar");

        expect(section.classes()).toContain("gap-0!");
        expect(bar.classes()).toContain("pt-3.5");
        expect(bar.classes()).toContain("pb-1.5");
    });

    it("keeps model metrics on one line and distributes every table row with the same percentage grid", async () => {
        setModelsData();
        const wrapper = mount(ModelsPanel);
        const metrics = wrapper.find(".models-stats");
        const labels = metrics
            .findAll("span")
            .filter((element) => ["Models", "Distinct Models"].includes(element.text().trim()));
        const tableBody = wrapper
            .findAll("div")
            .find((element) => element.classes().includes("max-h-[220px]"));
        const scrollableTable = wrapper.findComponent(ScrollableTable);
        const headerColumns = wrapper.findAll("thead th");
        const firstModelRowColumns = wrapper.findAll("tbody tr")[0]?.findAll("td") ?? [];
        const columnWidths = ["w-[35%]", "w-[10%]", "w-[15%]", "w-[40%]"];

        expect(wrapper.classes()).not.toContain("overflow-hidden");
        expect(metrics.classes()).toContain("pr-2");
        expect(labels).toHaveLength(2);
        expect(labels.every((label) => label.classes().includes("whitespace-nowrap"))).toBe(true);
        expect(scrollableTable.exists()).toBe(true);
        expect(scrollableTable.props("minWidth")).toBe("52rem");
        expect(scrollableTable.classes()).toContain("-mx-2");
        expect(scrollableTable.find(".scrollable-table-track").classes()).toContain("px-2");
        expect(scrollableTable.find(".scrollable-table-header").element.nextElementSibling).toBe(
            scrollableTable.find(".scrollable-table-body").element,
        );
        expect(wrapper.find(".mobile-table-right-fade").exists()).toBe(false);
        expect(tableBody?.classes()).toContain("scrollable-table-body");
        expect(wrapper.attributes("style")).toBeUndefined();
        expect(
            headerColumns.map((column) =>
                columnWidths.find((width) => column.classes().includes(width)),
            ),
        ).toEqual(columnWidths);
        expect(
            firstModelRowColumns.map((column) =>
                columnWidths.find((width) => column.classes().includes(width)),
            ),
        ).toEqual(columnWidths);

        await wrapper.find("tbody tr.group").trigger("click");

        expect(wrapper.findAll("colgroup col").map((column) => column.attributes("style"))).toEqual(
            ["width: 35%;", "width: 10%;", "width: 15%;", "width: 40%;"],
        );
        expect(
            scrollableTable
                .find(".scrollable-table-horizontal-scroller")
                .classes()
                .includes("overflow-x-auto"),
        ).toBe(true);
    });
});
