import { mount } from "@vue/test-utils";
import { nextTick } from "vue";
import { describe, expect, it } from "vitest";
import SharedPanel from "@/components/SharedPanel.vue";
import { activeToolId, usePinnedPanel } from "@/composables/usePinnedPanel";
import DatabasePanel from "@/tools/panels/database.vue";
import ModelsPanel from "@/tools/panels/models.vue";
import RequestPanel from "@/tools/panels/request.vue";
import RequestsPanel from "@/tools/panels/requests.vue";

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

    it("keeps the requests table wide inside its own horizontal scroller", () => {
        const wrapper = mount(RequestsPanel);
        const tableBody = wrapper.find(".requests-table");
        const tableScroller = tableBody.element.parentElement;

        expect(tableBody.classes()).toContain("min-w-[64rem]");
        expect(tableScroller?.classList.contains("overflow-x-auto")).toBe(true);
    });

    it("keeps the queries table wide inside its own horizontal scroller", () => {
        const wrapper = mount(DatabasePanel);
        const header = wrapper.find(".queries-header");
        const metrics = wrapper.find(".queries-stats");
        const labels = metrics
            .findAll("span")
            .filter((element) =>
                ["Queries", "Duration", "Database"].includes(element.text().trim()),
            );
        const tableBody = wrapper
            .findAll("div")
            .find((element) => element.classes().includes("max-h-[220px]"));
        const tableScroller = tableBody?.element.parentElement;

        expect(header.classes()).toContain("shrink-0");
        expect(header.classes()).toContain("flex-col");
        expect(header.classes()).toContain("sm:flex-row");
        expect(metrics.classes()).toContain("shrink-0");
        expect(metrics.classes()).toContain("w-full");
        expect(metrics.classes()).toContain("sm:w-auto");
        expect(metrics.classes()).toContain("pr-2");
        expect(labels).toHaveLength(3);
        expect(labels.every((label) => label.classes().includes("whitespace-nowrap"))).toBe(true);
        expect(tableBody?.classes()).toContain("min-w-[48rem]");
        expect(tableScroller?.classList.contains("overflow-x-auto")).toBe(true);
    });

    it("keeps model metrics on one line and the table inside its own horizontal scroller", () => {
        const wrapper = mount(ModelsPanel);
        const metrics = wrapper.find(".models-stats");
        const labels = metrics
            .findAll("span")
            .filter((element) => ["Models", "Distinct Models"].includes(element.text().trim()));
        const tableBody = wrapper
            .findAll("div")
            .find((element) => element.classes().includes("max-h-[220px]"));
        const tableScroller = tableBody?.element.parentElement;

        expect(metrics.classes()).toContain("pr-2");
        expect(labels).toHaveLength(2);
        expect(labels.every((label) => label.classes().includes("whitespace-nowrap"))).toBe(true);
        expect(tableBody?.classes()).toContain("min-w-[48rem]");
        expect(tableScroller?.classList.contains("overflow-x-auto")).toBe(true);
    });
});
