import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import Pill from "@/components/Pill.vue";

describe("Pill", () => {
    it("renders the success variant with a semantic green background and dark text", () => {
        const wrapper = mount(Pill, {
            props: {
                color: "green",
            },
            slots: {
                default: "200",
            },
        });

        expect(wrapper.classes()).toContain("bg-emerald-400");
        expect(wrapper.classes()).toContain("text-emerald-950");
        expect(wrapper.attributes("style")).toContain("text-shadow: none");
    });

    it("uses a custom active color without the status glow", () => {
        const wrapper = mount(Pill, {
            props: {
                color: "red",
                customColor: "#a3e635",
            },
            slots: {
                default: "500",
            },
        });

        expect(wrapper.attributes("style")).toContain("background-color: rgb(163, 230, 53)");
        expect(wrapper.attributes("style")).toContain("text-shadow: none");
    });
});
