import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import DatabasePanel from "@/tools/panels/database.vue";

describe("Database panel", () => {
    it("renders every query when source locations are unavailable", () => {
        window.dispatchEvent(
            new CustomEvent("laravel-toolbar:update", {
                detail: {
                    data: {
                        ...window.__LARAVEL_TOOLBAR_DATA__,
                        selected_request_id: "default-request-id",
                        request_history: window.__LARAVEL_TOOLBAR_DATA__.request_history,
                        queries: {
                            totalTime: 1.23,
                            totalTimeFilteredQueries: 1.23,
                            databases: [{ name: "database.sqlite", tablePlusConnectionUrl: null }],
                            queries: [
                                {
                                    sql: 'select * from "users"',
                                    file: null,
                                    line: null,
                                    editor_url: null,
                                    duration: 0.5,
                                    percentage: 0.4,
                                    offset: 0,
                                    isDuplicate: false,
                                    isSlow: false,
                                },
                                {
                                    sql: 'select * from "orders"',
                                    file: null,
                                    line: null,
                                    editor_url: null,
                                    duration: 0.73,
                                    percentage: 0.6,
                                    offset: 0.4,
                                    isDuplicate: false,
                                    isSlow: false,
                                },
                            ],
                        } as NckRtl.Toolbar.Data.QueriesData,
                    },
                },
            }),
        );

        const wrapper = mount(DatabasePanel);

        expect(wrapper.findAll("tbody tr")).toHaveLength(2);
        expect(wrapper.text()).toContain('select * from "users"');
        expect(wrapper.text()).toContain('select * from "orders"');
        expect(wrapper.text()).toContain("—");
    });
});
