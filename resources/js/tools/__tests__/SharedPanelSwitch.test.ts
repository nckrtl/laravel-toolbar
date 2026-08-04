import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import SharedPanel from '@/components/SharedPanel.vue';
import { activeToolId, usePinnedPanel } from '@/composables/usePinnedPanel';

const STORAGE_KEY = 'toolbar-pinned-tool';

const applyPayload = () => {
    window.dispatchEvent(
        new CustomEvent('laravel-toolbar:update', {
            detail: {
                data: {
                    ...window.__LARAVEL_TOOLBAR_DATA__,
                    request_id: 'default-request-id',
                    selected_request_id: 'default-request-id',
                    request_history: window.__LARAVEL_TOOLBAR_DATA__.request_history,
                    animations: false,
                    queries: {
                        totalTime: 12,
                        totalTimeFilteredQueries: 12,
                        databases: [{ name: 'db', tablePlusConnectionUrl: null }],
                        connections: [],
                        drivers: [],
                        queries: Array.from({ length: 5 }, (_, i) => ({
                            hash: `h${i}`,
                            sql: `select ${i}`,
                            bindings: [],
                            duration: 1,
                            connection: 'mysql',
                            driver: 'mysql',
                            is_duplicate: false,
                            is_slow: false,
                            percentage: 0.2,
                            offset: i * 0.2,
                            file: '/app/a.php',
                            line: i,
                            editor_url: null,
                            type: null,
                            memory_used: null,
                        })),
                    },
                    models: {
                        'App\\Models\\User': {
                            action: 'retrieved',
                            model: 'App\\Models\\User',
                            count: 3,
                            memory_used: null,
                            sources: {
                                a: { file: '/app/x.php', line: 1, count: 3, editor_url: '#' },
                            },
                        },
                    },
                },
            },
        }),
    );
};

const unpinAll = () => {
    localStorage.removeItem(STORAGE_KEY);
    for (const id of ['models', 'database', 'request']) {
        const panel = usePinnedPanel(id, { size: 'full', align: 'left', index: 0 });
        if (activeToolId.value === id) {
            panel.togglePin();
        }
    }
};

const pinTool = (id: string) => {
    const panel = usePinnedPanel(id, { size: 'full', align: 'left', index: 0 });
    if (activeToolId.value && activeToolId.value !== id) {
        usePinnedPanel(activeToolId.value).togglePin();
    }
    if (activeToolId.value !== id) {
        panel.togglePin();
    }
};

describe('SharedPanel tool switch (jsdom smoke)', () => {
    beforeEach(() => {
        unpinAll();
        applyPayload();
    });

    afterEach(() => {
        unpinAll();
    });

    it('keeps nested tables when switching Models → Database', async () => {
        usePinnedPanel('models', { size: 'full', align: 'left', index: 0 });
        usePinnedPanel('database', { size: 'full', align: 'left', index: 1 });

        pinTool('models');
        const wrapper = mount(SharedPanel, { attachTo: document.body });
        await nextTick();
        await nextTick();

        expect(wrapper.find('.scrollable-table').exists()).toBe(true);
        expect(wrapper.text()).toContain('Models');

        pinTool('database');
        await nextTick();
        await nextTick();

        expect(wrapper.text()).toContain('Queries');
        expect(wrapper.find('.scrollable-table').exists()).toBe(true);
        expect(wrapper.findAll('tbody tr').length).toBe(5);

        wrapper.unmount();
    });
});
