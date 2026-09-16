import { mount } from "@vue/test-utils";
import { nextTick } from "vue";
import { afterEach, describe, expect, it, vi } from "vitest";
import BreakpointIndicator from "@/tools/BreakpointIndicator.vue";

function mockHostBreakpoints(entries: Record<string, string>) {
    const props = Object.keys(entries).map((name) => `--breakpoint-${name}`);
    const styleDecl: Record<string | number | symbol, unknown> = {
        length: props.length,
        item: (index: number) => props[index] ?? "",
        getPropertyValue: (name: string) => {
            if (name.startsWith("--breakpoint-")) {
                const key = name.slice("--breakpoint-".length);
                return entries[key] ?? "";
            }
            return "";
        },
    };

    props.forEach((prop, index) => {
        styleDecl[index] = prop;
    });

    return vi
        .spyOn(globalThis, "getComputedStyle")
        .mockReturnValue(styleDecl as unknown as CSSStyleDeclaration);
}

describe("BreakpointIndicator", () => {
    afterEach(() => {
        vi.restoreAllMocks();
    });

    it("mounts without throwing when the host has no CSS breakpoint variables", async () => {
        mockHostBreakpoints({});

        expect(() =>
            mount(BreakpointIndicator, {
                props: {
                    config: { show_pixels: false },
                },
            }),
        ).not.toThrow();

        const wrapper = mount(BreakpointIndicator, {
            props: {
                config: { show_pixels: false },
            },
        });

        await nextTick();

        expect(wrapper.text()).toContain("xs");
        wrapper.unmount();
    });

    it("selects the active breakpoint when CSS variables are present", async () => {
        mockHostBreakpoints({
            sm: "640px",
            md: "768px",
            lg: "1024px",
        });

        vi.spyOn(globalThis, "matchMedia").mockImplementation((query: string) => {
            const match = /min-width:\s*(\d+)px/.exec(query);
            const minWidth = match ? Number(match[1]) : 0;
            return {
                matches: 900 >= minWidth,
                media: query,
                onchange: null,
                addListener: () => {},
                removeListener: () => {},
                addEventListener: () => {},
                removeEventListener: () => {},
                dispatchEvent: () => false,
            } as MediaQueryList;
        });

        Object.defineProperty(document.documentElement, "clientWidth", {
            configurable: true,
            get: () => 900,
        });

        const wrapper = mount(BreakpointIndicator, {
            props: {
                config: { show_pixels: false },
            },
        });

        await nextTick();

        expect(wrapper.text()).toContain("md");
        wrapper.unmount();
    });
});
