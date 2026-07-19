import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import Group from "@/components/Group.vue";
import Toolbar from "@/Toolbar.vue";
import Models from "@/tools/Models.vue";
import Request from "@/tools/Request.vue";

describe("responsive toolbar chrome", () => {
    it("keeps toolbar groups fully rounded and floating above the screen edge", () => {
        window.dispatchEvent(
            new CustomEvent("laravel-toolbar:update", {
                detail: {
                    data: {
                        ...window.__LARAVEL_TOOLBAR_DATA__,
                        layout: {
                            sections: {
                                center: [{ tools: {} }],
                            },
                        },
                    },
                },
            }),
        );

        const toolbar = mount(Toolbar, {
            global: {
                stubs: {
                    Group: true,
                    SharedPanel: true,
                },
            },
        });
        const centerSection = toolbar.find("#toolbar > div");
        const group = mount(Group, {
            props: {
                config: { tools: {} },
            },
        });
        const groupBackground = group.find(".absolute");

        expect(centerSection.classes()).toContain("p-[5px]");
        expect(centerSection.classes()).not.toContain("pb-0");
        expect(group.classes()).toContain("rounded-full");
        expect(group.classes()).toContain("items-center");
        expect(groupBackground.classes()).toContain("rounded-full");
    });

    it("keeps the final tool clearance aligned with the group height", () => {
        const group = mount(Group, {
            props: {
                config: {
                    tools: {
                        Request: {},
                        Models: {},
                    },
                },
            },
        });
        const firstClasses = group.findComponent(Request).props("itemClasses");
        const lastClasses = group.findComponent(Models).props("itemClasses");

        expect(firstClasses).toMatchObject({
            "pl-[3px]": true,
        });
        expect(lastClasses).toMatchObject({
            "pr-[3px]": true,
        });
        expect(lastClasses).not.toHaveProperty("pr-[5px] md:pr-[3px]");
    });
});
