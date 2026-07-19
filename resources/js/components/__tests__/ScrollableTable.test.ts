import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import ScrollableTable from "@/components/ScrollableTable.vue";

const horizontalMasks = {
    left: "linear-gradient(to right, transparent 0px, black 40px, black 100%)",
    both: "linear-gradient(to right, transparent 0px, black 40px, black calc(100% - 40px), transparent 100%)",
    right: "linear-gradient(to right, black 0px, black calc(100% - 40px), transparent 100%)",
};

const verticalMasks = {
    top: "linear-gradient(to bottom, transparent 0px, black 32px, black 100%)",
    both: "linear-gradient(to bottom, transparent 0px, black 32px, black calc(100% - 32px), transparent 100%)",
    bottom: "linear-gradient(to bottom, black 0px, black calc(100% - 32px), transparent 100%)",
};

type ScrollMetric =
    | "clientWidth"
    | "scrollWidth"
    | "clientHeight"
    | "scrollHeight"
    | "scrollLeft"
    | "scrollTop";

const setScrollMetrics = (element: HTMLElement, metrics: Partial<Record<ScrollMetric, number>>) => {
    for (const [property, value] of Object.entries(metrics)) {
        Object.defineProperty(element, property, {
            configurable: true,
            writable: true,
            value,
        });
    }
};

const mountTable = () =>
    mount(ScrollableTable, {
        props: {
            minWidth: "48rem",
            bodyClass: "max-h-[220px] pb-3",
        },
        slots: {
            header: "<table data-test='header'><thead><tr><th>Query</th></tr></thead></table>",
            default: "<table data-test='body'><tbody><tr><td>select 1</td></tr></tbody></table>",
        },
    });

describe("ScrollableTable", () => {
    it("keeps endpoint padding inside a horizontal-only scroller with the real header fixed", () => {
        const wrapper = mountTable();
        const scroller = wrapper.find(".scrollable-table-horizontal-scroller");
        const track = wrapper.find(".scrollable-table-track");
        const content = wrapper.find(".scrollable-table-content");
        const header = wrapper.find(".scrollable-table-header");
        const body = wrapper.find(".scrollable-table-body");

        expect(wrapper.classes()).toContain("-mx-2");
        expect(scroller.classes()).toContain("overflow-x-auto");
        expect(scroller.classes()).toContain("overflow-y-hidden");
        expect(track.classes()).toContain("h-full");
        expect(track.classes()).toContain("px-2");
        expect(track.classes()).not.toContain("w-max");
        expect(track.attributes("style")).toContain("min-width: calc(48rem + 1rem)");
        expect(content.classes()).toContain("h-full");
        expect(content.classes()).toContain("min-h-0");
        expect(content.classes()).toContain("flex");
        expect(content.classes()).toContain("flex-col");
        expect(content.attributes("style")).toContain("min-width: 48rem");
        expect(header.classes()).toContain("shrink-0");
        expect(body.classes()).toContain("max-h-[220px]");
        expect(body.classes()).toContain("min-h-0");
        expect(body.classes()).toContain("pb-3");
        expect(header.classes()).toContain("z-40");
        expect(header.element.parentElement).toBe(content.element);
        expect(body.element.parentElement).toBe(content.element);
        expect(wrapper.find(".scrollable-table-header-top-rule").exists()).toBe(false);
        expect(wrapper.find(".scrollable-table-left-fade").exists()).toBe(false);
        expect(wrapper.find(".scrollable-table-right-fade").exists()).toBe(false);
    });

    it("masks the full table horizontally only while content remains in each direction", async () => {
        const wrapper = mountTable();
        const scroller = wrapper.find(".scrollable-table-horizontal-scroller");
        const element = scroller.element as HTMLElement;

        setScrollMetrics(element, { clientWidth: 100, scrollWidth: 300, scrollLeft: 0 });
        await scroller.trigger("scroll");

        expect(element.style.maskImage).toBe(horizontalMasks.right);

        element.scrollLeft = 100;
        await scroller.trigger("scroll");

        expect(element.style.maskImage).toBe(horizontalMasks.both);

        element.scrollLeft = 200;
        await scroller.trigger("scroll");

        expect(element.style.maskImage).toBe(horizontalMasks.left);
    });

    it("uses the same mask technique vertically while rows remain in each direction", async () => {
        const wrapper = mountTable();
        const body = wrapper.find(".scrollable-table-body");
        const element = body.element as HTMLElement;

        setScrollMetrics(element, { clientHeight: 100, scrollHeight: 300, scrollTop: 0 });
        await body.trigger("scroll");

        expect(element.style.maskImage).toBe(verticalMasks.bottom);

        element.scrollTop = 100;
        await body.trigger("scroll");

        expect(element.style.maskImage).toBe(verticalMasks.both);

        element.scrollTop = 200;
        await body.trigger("scroll");

        expect(element.style.maskImage).toBe(verticalMasks.top);
    });

    it("removes both masks when neither axis overflows", async () => {
        const wrapper = mountTable();
        const horizontalScroller = wrapper.find(".scrollable-table-horizontal-scroller");
        const body = wrapper.find(".scrollable-table-body");

        setScrollMetrics(horizontalScroller.element as HTMLElement, {
            clientWidth: 100,
            scrollWidth: 100,
            scrollLeft: 0,
        });
        setScrollMetrics(body.element as HTMLElement, {
            clientHeight: 100,
            scrollHeight: 100,
            scrollTop: 0,
        });

        await horizontalScroller.trigger("scroll");
        await body.trigger("scroll");

        expect((horizontalScroller.element as HTMLElement).style.maskImage).toBe("none");
        expect((body.element as HTMLElement).style.maskImage).toBe("none");
    });
});
