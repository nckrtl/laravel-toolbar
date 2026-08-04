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

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');

/**
 * Regression for dual Vite/Inertia Vue runtimes sharing
 * globalThis.__VUE_INSTANCE_SETTERS__ (blank tools / slot TypeErrors on Servauto).
 *
 * Live race (reproduced for hostVue=bundled + delayed CSS):
 * 1. Classic toolbar script runs — Vue runtime registers, starts loadProductionCSS
 * 2. CSS response held pending (no Vue app mount yet)
 * 3. Host Vite module mounts and registers INSTANCE_SETTERS
 * 4. CSS resolves → toolbar mountVueApp runs after host registration
 * 5. Compact hydration / panel switches while both runtimes are live
 *
 * Isolation is a Vite renderChunk rewrite of setter keys so the transformed
 * content participates in the asset content hash / manifest entry.
 */
test.describe('Vue runtime isolation vs bundled host', () => {
    test('production toolbar bundle rewrites shared Vue instance setter keys', () => {
        const asset = resolveProductionToolbarAsset();
        const source = fs.readFileSync(asset, 'utf8');

        // renderChunk plugin must rewrite before hash — no host-shared keys remain.
        expect(
            source.includes('__VUE_INSTANCE_SETTERS__'),
            'toolbar.prod must not register on the host-shared __VUE_INSTANCE_SETTERS__ key',
        ).toBe(false);
        expect(source.includes('__VUE_SSR_SETTERS__')).toBe(false);
        // Isolated keys prove Vue is still the multi-setter runtime, just namespaced.
        expect(source.includes('__LARAVEL_TOOLBAR_VUE_INSTANCE_SETTERS__')).toBe(true);
        expect(source.includes('__LARAVEL_TOOLBAR_VUE_SSR_SETTERS__')).toBe(true);
    });

    test('bundled host fixture itself is an esm-bundler Vue (registers INSTANCE_SETTERS)', () => {
        const hostPath = path.join(
            ROOT,
            'tests/browser/fixtures/dist-host/host-vue-bundled.js',
        );
        expect(fs.existsSync(hostPath)).toBe(true);
        const source = fs.readFileSync(hostPath, 'utf8');
        expect(source.includes('__VUE_INSTANCE_SETTERS__')).toBe(true);
    });

    test('delayed CSS race: toolbar script → host mid-await → CSS release → tools work', async ({
        page,
    }) => {
        test.setTimeout(90_000);
        const errors = installErrorCapture(page);

        // compact + delayed CSS (default for bundled) mirrors Servauto interleaving.
        await mountProductionToolbar(page, {
            hostVue: 'bundled',
            pin: 'models',
            animations: true,
            bootstrap: 'compact',
            interleaveHostDuringCss: true,
        });

        await assertHostRootMounted(page);

        const orderMeta = await page.evaluate(() => ({
            scriptOrder: (window as any).__SCRIPT_ORDER__ as string[] | undefined,
            hasSetters: Boolean((window as any).__HOST_HAS_INSTANCE_SETTERS__),
            setterCount: ((globalThis as any).__VUE_INSTANCE_SETTERS__ || []).length,
            hasToolbarPrivateSetters: typeof (globalThis as any)
                .__LARAVEL_TOOLBAR_VUE_INSTANCE_SETTERS__ !== 'undefined',
        }));

        // toolbar-script (CSS pending) → host → toolbar-mounted (after CSS)
        expect(orderMeta.scriptOrder).toEqual([
            'toolbar-script',
            'host',
            'toolbar-mounted',
        ]);
        expect(orderMeta.hasSetters).toBe(true);
        // Host alone on the shared key; isolated toolbar uses private keys.
        expect(orderMeta.setterCount).toBe(1);
        expect(orderMeta.hasToolbarPrivateSetters).toBe(true);

        const chrome = await inspectPanel(page);
        expect(chrome.toolRootCount).toBe(6);
        expect(chrome.blankToolCount).toBe(0);
        expect(chrome.toolbarText.length).toBeGreaterThan(0);
        expect(chrome.toolbarText).toContain(`${QUERY_COUNT}:`);
        expect(chrome.hasScrollable).toBe(true);
        expect(chrome.rowCount).toBeGreaterThan(0);
        expect(chrome.text).toContain(String(TOTAL_MODEL_HYDRATIONS));

        await clickToolbarTool(page, 'database');
        const database = await inspectPanel(page);
        expect(database.blankToolCount).toBe(0);
        expect(database.hasScrollable).toBe(true);
        expect(database.rowCount).toBe(QUERY_COUNT);
        expect(database.text).toContain('select * from "tasks" where "id" = 0');

        await assertHostRemainsReactive(page);

        const after = await page.evaluate(() => ({
            hostSetters: ((globalThis as any).__VUE_INSTANCE_SETTERS__ || []).length,
            toolbarSetters: (
                (globalThis as any).__LARAVEL_TOOLBAR_VUE_INSTANCE_SETTERS__ || []
            ).length,
        }));
        expect(after.hostSetters).toBe(1);
        expect(after.toolbarSetters).toBeGreaterThanOrEqual(1);

        errors.assertClean();
    });

    test('bundled host (full payload + delayed CSS) keeps six tools and nested panels', async ({
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

        const order = await page.evaluate(
            () => (window as any).__SCRIPT_ORDER__ as string[] | undefined,
        );
        expect(order).toEqual(['toolbar-script', 'host', 'toolbar-mounted']);

        const hostMeta = await page.evaluate(() => ({
            hasSetters: Boolean((window as any).__HOST_HAS_INSTANCE_SETTERS__),
            setterCount: ((globalThis as any).__VUE_INSTANCE_SETTERS__ || []).length,
        }));
        expect(hostMeta.hasSetters).toBe(true);
        expect(hostMeta.setterCount).toBe(1);

        const chrome = await inspectPanel(page);
        expect(chrome.toolRootCount).toBe(6);
        expect(chrome.blankToolCount).toBe(0);
        expect(chrome.toolbarText.length).toBeGreaterThan(0);
        expect(chrome.hasScrollable).toBe(true);
        expect(chrome.rowCount).toBeGreaterThan(0);
        expect(chrome.text).toContain(String(TOTAL_MODEL_HYDRATIONS));

        await clickToolbarTool(page, 'database');
        const database = await inspectPanel(page);
        expect(database.hasScrollable).toBe(true);
        expect(database.rowCount).toBe(QUERY_COUNT);
        expect(database.text).toContain('select * from "tasks" where "id" = 0');

        await assertHostRemainsReactive(page);

        const after = await page.evaluate(
            () => ((globalThis as any).__VUE_INSTANCE_SETTERS__ || []).length,
        );
        expect(after).toBe(1);

        errors.assertClean();
    });
});
