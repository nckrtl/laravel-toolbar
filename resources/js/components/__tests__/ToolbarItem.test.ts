import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import ToolbarItem from "@/components/ToolbarItem.vue";

describe("ToolbarItem", () => {
    it("uses the configured primary color only while active", async () => {
        window.dispatchEvent(
            new CustomEvent("laravel-toolbar:update", {
                detail: {
                    data: {
                        ...window.__LARAVEL_TOOLBAR_DATA__,
                        primary_color: "#a3e635",
                        selected_request_id: "default-request-id",
                        request_history: window.__LARAVEL_TOOLBAR_DATA__.request_history,
                    },
                },
            }),
        );

        const wrapper = mount(ToolbarItem, {
            props: {
                isActive: false,
            },
        });

        expect(wrapper.find(".cursor-default").attributes("style")).toBeUndefined();
        expect(wrapper.find(".active-top-border").exists()).toBe(false);

        await wrapper.setProps({ isActive: true });

        expect(wrapper.find(".cursor-default").attributes("style")).toContain(
            "background-color: color-mix(in srgb, rgb(163, 230, 53) 20%, transparent)",
        );
        expect(wrapper.find(".cursor-default > div").attributes("style")).toContain(
            "background-color: rgb(163, 230, 53)",
        );
        expect(wrapper.find(".cursor-default > div").attributes("style")).toContain(
            "color: rgb(0, 0, 0)",
        );
        expect(wrapper.find(".cursor-default > div").classes()).not.toContain("bg-[#101010]/30");
        expect(wrapper.find(".text-xxs").classes()).toContain("py-0.5");
        expect(wrapper.find(".cursor-default").classes()).toContain("p-[3px]");
        expect(wrapper.find(".cursor-default > div").classes()).not.toContain("p-0.5");
        expect(wrapper.find(".cursor-default > div").classes()).toContain("py-px");
        expect(wrapper.find(".cursor-default > div").classes()).toContain("relative");
        expect(wrapper.find(".active-top-border").classes()).toContain("border-t");
        expect(wrapper.find(".active-top-border").classes()).toContain("border-white/20");
    });
});
