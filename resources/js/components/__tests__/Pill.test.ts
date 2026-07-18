import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import Pill from "@/components/Pill.vue";

describe("Pill", () => {
    it("renders the success variant with a lime background and dark text", () => {
        const wrapper = mount(Pill, {
            props: {
                color: "green",
            },
            slots: {
                default: "200",
            },
        });

        expect(wrapper.classes()).toContain("bg-lime-400");
        expect(wrapper.classes()).toContain("text-[#132000]");
        expect(wrapper.attributes("style")).toContain("text-shadow: none");
    });
});
