import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { expect, test } from '@playwright/test';
import {
    assertHostRemainsReactive,
    assertHostRootMounted,
    clickToolbarTool,
    installErrorCapture,
    inspectPanel,
    mountProductionToolbar,
    resolveProductionToolbarAsset,
} from './helpers/mountToolbar';
import { QUERY_COUNT, TOTAL_MODEL_HYDRATIONS } from './fixtures/toolbarPayload';
import {
    CLASSIC_SCRIPT_IIFE_BANNER,
    isWrappedClassicScript,
    unwrapClassicScriptBundle,
} from '../../vite.plugins/wrapClassicScriptBundle';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');

/**
 * Root cause (Servauto): unwrapped classic toolbar bundle exposes minified Vue
 * withCtx as top-level `function _` → `window._`. Host ES module assigns Lodash
 * to `window._`, so later slot creation returns Lodash wrappers and renderSlot
 * throws `r is not a function` (blank tools / nested panels).
 *
 * Fix: IIFE-wrap the production toolbar classic script (lexical top-level bindings).
 *
 * Fixture: parser-time `<script type=module src=host>` (sets Lodash-like `_`)
 * + classic toolbar at body end. Decisive work: switch ToolbarItem/ScrollableTable
 * slots AFTER host has overwritten `window._`.
 */
test.describe('classic-script IIFE vs host Lodash window._', () => {
    test('production toolbar bundle is IIFE-wrapped before content hash', () => {
        const asset = resolveProductionToolbarAsset();
        const source = fs.readFileSync(asset, 'utf8');

        expect(
            isWrappedClassicScript(source),
            'toolbar.prod must carry classic-iife banner (wrap before content hash)',
        ).toBe(true);
        expect(source.startsWith(CLASSIC_SCRIPT_IIFE_BANNER)).toBe(true);
        expect(source).toContain('(function(){');
        expect(source.trimEnd().endsWith('})();')).toBe(true);
    });

    test('bundled host fixture overwrites window._ like Lodash', () => {
        const hostPath = path.join(
            ROOT,
            'tests/browser/fixtures/dist-host/host-vue-bundled.js',
        );
        expect(fs.existsSync(hostPath)).toBe(true);
        const source = fs.readFileSync(hostPath, 'utf8');
        expect(source.includes('__wrapped__') || source.includes('lodashLike')).toBe(true);
        expect(source.includes('__VUE_INSTANCE_SETTERS__')).toBe(true);
    });

    test('RED without IIFE: unwrapped production copy fails post-host slot switch', async ({
        page,
    }) => {
        test.setTimeout(90_000);
        const errors = installErrorCapture(page);

        const wrapped = fs.readFileSync(resolveProductionToolbarAsset(), 'utf8');
        expect(isWrappedClassicScript(wrapped)).toBe(true);
        const unwrapped = unwrapClassicScriptBundle(wrapped);
        expect(isWrappedClassicScript(unwrapped)).toBe(false);

        await mountProductionToolbar(page, {
            hostVue: 'bundled',
            pin: 'models',
            animations: true,
            bootstrap: 'full',
            interleaveHostDuringCss: true,
            // Exact production body minus IIFE — proves the wrap is load-bearing.
            toolbarScriptBody: unwrapped,
        });

        await assertHostRootMounted(page);

        // Post-host slot mount: this is where Servauto blanks nested panels.
        await clickToolbarTool(page, 'database');
        const state = await inspectPanel(page);

        const slotFailure =
            state.blankToolCount > 0 ||
            !state.hasScrollable ||
            state.rowCount !== QUERY_COUNT ||
            errors.errors.some(
                (e) =>
                    e.includes('is not a function') ||
                    e.includes('renderSlot') ||
                    e.includes('TypeError'),
            );

        expect(
            slotFailure,
            `Expected unwrapped classic script to fail after host Lodash window._ ` +
                `(blankToolCount=${state.blankToolCount}, hasScrollable=${state.hasScrollable}, ` +
                `rowCount=${state.rowCount}, errors=${errors.errors.join(' | ') || 'none'})`,
        ).toBe(true);

        // Do NOT call errors.assertClean() — this scenario is expected to error.
    });

    test('post-host Lodash _ overwrite: Models→Database slot mounts stay healthy', async ({
        page,
    }) => {
        test.setTimeout(90_000);
        const errors = installErrorCapture(page);

        await mountProductionToolbar(page, {
            hostVue: 'bundled',
            pin: 'models',
            animations: true,
            bootstrap: 'full',
            interleaveHostDuringCss: true,
        });

        await assertHostRootMounted(page);

        const meta = await page.evaluate(() => {
            const underscore = (window as any)._;
            return {
                hostMounted: Boolean((window as any).__HOST_MOUNTED__),
                lodashOverwrite: Boolean((window as any).__HOST_LODASH_OVERWRITE__),
                windowUnderscoreIsFunction: typeof underscore === 'function',
                wrappedShape: (() => {
                    if (typeof underscore !== 'function') {
                        return null;
                    }
                    const w = underscore(() => 'slot');
                    return w && typeof w === 'object' ? Object.keys(w).sort() : typeof w;
                })(),
                windowUnderscoreIsWithCtx: (() => {
                    if (typeof underscore !== 'function') {
                        return false;
                    }
                    // Vue withCtx returns a function; Lodash-like returns a wrapper object.
                    return typeof underscore(() => 'x') === 'function';
                })(),
                scriptOrder: (window as any).__SCRIPT_ORDER__ as string[],
            };
        });

        expect(meta.hostMounted).toBe(true);
        expect(meta.lodashOverwrite).toBe(true);
        expect(meta.windowUnderscoreIsFunction).toBe(true);
        expect(meta.wrappedShape).toEqual(
            expect.arrayContaining([
                '__actions__',
                '__chain__',
                '__index__',
                '__values__',
                '__wrapped__',
            ]),
        );
        expect(meta.windowUnderscoreIsWithCtx).toBe(false);
        expect(meta.scriptOrder).toContain('host');
        expect(meta.scriptOrder).toContain('toolbar-mounted');

        let state = await inspectPanel(page);
        expect(state.toolRootCount).toBe(6);
        expect(state.blankToolCount).toBe(0);

        await clickToolbarTool(page, 'database');
        state = await inspectPanel(page);
        expect(state.blankToolCount, 'chrome blank after post-host Database switch').toBe(0);
        expect(state.hasScrollable).toBe(true);
        expect(state.rowCount).toBe(QUERY_COUNT);
        expect(state.text).toContain('select * from "tasks" where "id" = 0');

        await clickToolbarTool(page, 'models');
        state = await inspectPanel(page);
        expect(state.blankToolCount).toBe(0);
        expect(state.hasScrollable).toBe(true);
        expect(state.rowCount).toBeGreaterThan(0);
        expect(state.text).toContain(String(TOTAL_MODEL_HYDRATIONS));

        await assertHostRemainsReactive(page);
        errors.assertClean();
    });

    test('post-host compact hydrate + all six tools with tool-specific content', async ({
        page,
    }) => {
        test.setTimeout(90_000);
        const errors = installErrorCapture(page);

        await mountProductionToolbar(page, {
            hostVue: 'bundled',
            pin: null, // explicit null must stay unpinned (not coerced to models)
            animations: true,
            bootstrap: 'compact',
            interleaveHostDuringCss: true,
        });

        await assertHostRootMounted(page);

        const chrome = await inspectPanel(page);
        expect(chrome.pin).toBeNull();
        expect(chrome.toolRootCount).toBe(6);
        expect(chrome.blankToolCount).toBe(0);
        expect(chrome.toolbarText).toContain(`${QUERY_COUNT}:`);

        const cases: Array<{
            tool: Parameters<typeof clickToolbarTool>[1];
            assert: (state: Awaited<ReturnType<typeof inspectPanel>>) => void;
        }> = [
            {
                tool: 'requests',
                assert: (state) => {
                    expect(state.blankToolCount).toBe(0);
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
                    expect(state.blankToolCount).toBe(0);
                    expect(state.hasPanel).toBe(true);
                    expect(state.text).toContain('/tasks');
                    expect(state.text).toContain('GET');
                    expect(state.text).toMatch(/TaskController@index/);
                },
            },
            {
                tool: 'timings',
                assert: (state) => {
                    expect(state.blankToolCount).toBe(0);
                    expect(state.hasPanel).toBe(true);
                    expect(state.hasTimingsBar).toBe(true);
                    expect(state.text).toMatch(/Bootstrapping/i);
                    expect(state.text).toMatch(/Routing/i);
                },
            },
            {
                tool: 'memory',
                assert: (state) => {
                    expect(state.blankToolCount).toBe(0);
                    expect(state.hasPanel).toBe(true);
                    expect(state.hasMemoryBar).toBe(true);
                    expect(state.text).toMatch(/Bootstrapping/i);
                    expect(state.text).toMatch(/Routing/i);
                },
            },
            {
                tool: 'database',
                assert: (state) => {
                    expect(state.blankToolCount).toBe(0);
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
                    expect(state.blankToolCount).toBe(0);
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
            item.assert(await inspectPanel(page));
        }

        await assertHostRemainsReactive(page);
        errors.assertClean();
    });
});
