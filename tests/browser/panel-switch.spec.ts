import { expect, test } from '@playwright/test';
import {
    DATABASE_SUMMARY_MS,
    QUERY_COUNT,
    TOTAL_MODEL_HYDRATIONS,
} from './fixtures/toolbarPayload';
import {
    assertHostRemainsReactive,
    assertHostRootMounted,
    clickToolbarTool,
    installErrorCapture,
    inspectPanel,
    mountProductionToolbar,
    type HostVueVariant,
} from './helpers/mountToolbar';

/**
 * Production-bundle browser regression for SharedPanel tool switches at large scale.
 * Covers Models→Database nested table survival, full six-tool cycle, host dual-runtime,
 * and compact bootstrap + async request payload hydration.
 */
const hostMatrix: HostVueVariant[] = ['none', 'package'];

for (const hostVue of hostMatrix) {
    test.describe(`production bundle (hostVue=${hostVue})`, () => {
        test('Models pin shows table; Database switch keeps ScrollableTable at scale', async ({
            page,
        }) => {
            test.setTimeout(60_000);
            const errors = installErrorCapture(page);

            await mountProductionToolbar(page, {
                hostVue,
                pin: 'models',
                animations: true,
                bootstrap: 'full',
            });

            if (hostVue === 'package') {
                await assertHostRootMounted(page);
            }

            const models = await inspectPanel(page);
            expect(models.pin).toBe('models');
            expect(models.hasModelsHeader).toBe(true);
            expect(models.hasScrollable).toBe(true);
            expect(models.rowCount).toBeGreaterThan(0);
            expect(models.text).toContain(String(TOTAL_MODEL_HYDRATIONS));
            expect(models.text).toContain('Entity0');

            await clickToolbarTool(page, 'database');

            const database = await inspectPanel(page);
            expect(database.pin).toBe('database');
            expect(database.hasQueriesHeader).toBe(true);
            expect(
                database.hasScrollable,
                `ScrollableTable missing after Models→Database (hostVue=${hostVue}, host=${database.hostVueVersion})`,
            ).toBe(true);
            expect(database.rowCount).toBe(QUERY_COUNT);
            expect(database.text).toContain('select * from "tasks" where "id" = 0');
            expect(database.text).toContain(String(QUERY_COUNT));
            expect(database.text).toContain(`${DATABASE_SUMMARY_MS}`);

            if (hostVue === 'package') {
                await assertHostRemainsReactive(page);
            }

            errors.assertClean();
        });

        test('cycles all six default tools with meaningful panel body content', async ({
            page,
        }) => {
            test.setTimeout(90_000);
            const errors = installErrorCapture(page);

            await mountProductionToolbar(page, {
                hostVue,
                pin: null,
                animations: true,
                bootstrap: 'full',
            });

            if (hostVue === 'package') {
                await assertHostRootMounted(page);
            }

            const cases: Array<{
                tool: Parameters<typeof clickToolbarTool>[1];
                assert: (state: Awaited<ReturnType<typeof inspectPanel>>) => void;
            }> = [
                {
                    tool: 'requests',
                    assert: (state) => {
                        expect(state.hasPanel).toBe(true);
                        expect(state.hasRequestsTable).toBe(true);
                        expect(state.requestsDataRowCount).toBeGreaterThanOrEqual(1);
                        expect(state.text).toContain('/tasks');
                        expect(state.text).toMatch(/GET/);
                    },
                },
                {
                    tool: 'request',
                    assert: (state) => {
                        expect(state.hasPanel).toBe(true);
                        expect(state.text).toContain('/tasks');
                        expect(state.text).toContain('GET');
                        expect(state.text).toMatch(/TaskController@index/);
                    },
                },
                {
                    tool: 'timings',
                    assert: (state) => {
                        expect(state.hasPanel).toBe(true);
                        expect(state.hasTimingsBar).toBe(true);
                        expect(state.text).toContain('Bootstrapping');
                        expect(state.text).toContain('Routing');
                    },
                },
                {
                    tool: 'memory',
                    assert: (state) => {
                        expect(state.hasPanel).toBe(true);
                        expect(state.hasMemoryBar).toBe(true);
                        expect(state.text).toContain('Bootstrapping');
                        expect(state.text).toContain('Routing');
                    },
                },
                {
                    tool: 'database',
                    assert: (state) => {
                        expect(state.hasPanel).toBe(true);
                        expect(state.hasScrollable).toBe(true);
                        expect(state.rowCount).toBe(QUERY_COUNT);
                        expect(state.text).toContain('Queries');
                        expect(state.text).toContain('select * from "tasks"');
                    },
                },
                {
                    tool: 'models',
                    assert: (state) => {
                        expect(state.hasPanel).toBe(true);
                        expect(state.hasScrollable).toBe(true);
                        expect(state.rowCount).toBeGreaterThan(0);
                        expect(state.text).toContain('Models');
                        expect(state.text).toContain(String(TOTAL_MODEL_HYDRATIONS));
                        expect(state.text).toContain('Entity0');
                    },
                },
            ];

            for (const item of cases) {
                await clickToolbarTool(page, item.tool);
                const state = await inspectPanel(page);
                item.assert(state);
            }

            // Cross-size switch: Models (full) → Request (xl) → Database (full)
            await clickToolbarTool(page, 'models');
            await clickToolbarTool(page, 'request');
            let state = await inspectPanel(page);
            expect(state.text).toContain('/tasks');
            expect(state.text).toMatch(/TaskController@index/);

            await clickToolbarTool(page, 'database');
            state = await inspectPanel(page);
            expect(state.hasScrollable).toBe(true);
            expect(state.rowCount).toBe(QUERY_COUNT);

            if (hostVue === 'package') {
                await assertHostRemainsReactive(page);
            }

            errors.assertClean();
        });

        test('compact bootstrap hydrates async payload then Models→Database nested body', async ({
            page,
        }) => {
            test.setTimeout(60_000);
            const errors = installErrorCapture(page);

            await mountProductionToolbar(page, {
                hostVue,
                pin: 'models',
                animations: true,
                bootstrap: 'compact',
            });

            if (hostVue === 'package') {
                await assertHostRootMounted(page);
            }

            // After hydration, Models panel should have full nested table.
            const models = await inspectPanel(page);
            expect(models.pin).toBe('models');
            expect(models.hasModelsHeader).toBe(true);
            expect(models.hasScrollable).toBe(true);
            expect(models.rowCount).toBeGreaterThan(0);
            expect(models.text).toContain(String(TOTAL_MODEL_HYDRATIONS));
            expect(models.text).toContain('Entity0');

            await clickToolbarTool(page, 'database');

            const database = await inspectPanel(page);
            expect(database.pin).toBe('database');
            expect(database.hasQueriesHeader).toBe(true);
            expect(database.hasScrollable).toBe(true);
            expect(database.rowCount).toBe(QUERY_COUNT);
            expect(database.text).toContain('select * from "tasks" where "id" = 0');
            expect(database.text).toContain(String(QUERY_COUNT));

            if (hostVue === 'package') {
                await assertHostRemainsReactive(page);
            }

            errors.assertClean();
        });
    });
}
