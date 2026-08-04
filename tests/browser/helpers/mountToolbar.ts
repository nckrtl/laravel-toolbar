import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import type { ConsoleMessage, Page } from '@playwright/test';
import {
    buildCompactBootstrapPayload,
    buildRequestDataEndpointBody,
    buildSyntheticToolbarPayload,
    QUERY_COUNT,
    REQUEST_ID,
} from '../fixtures/toolbarPayload';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../../..');

/** Stable center-group tool order from the synthetic layout fixture. */
export const DEFAULT_TOOL_ORDER = [
    'requests',
    'request',
    'timings',
    'memory',
    'database',
    'models',
] as const;

export type DefaultToolId = (typeof DEFAULT_TOOL_ORDER)[number];

export type HostVueVariant = 'none' | 'package';

export type ErrorCapture = {
    errors: string[];
    assertClean: () => void;
};

export function resolveProductionToolbarAsset(): string {
    const manifestPath = path.join(ROOT, 'build/manifest.json');
    if (!fs.existsSync(manifestPath)) {
        throw new Error('Missing build/manifest.json. Run `npm run build` before browser tests.');
    }

    const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8')) as Record<
        string,
        { file?: string }
    >;
    const entry = manifest['resources/js/toolbar.prod.ts'];
    if (!entry?.file) {
        throw new Error('toolbar.prod.ts entry missing from build/manifest.json');
    }

    const assetPath = path.join(ROOT, 'build', entry.file);
    if (!fs.existsSync(assetPath)) {
        throw new Error(`Built toolbar asset not found: ${assetPath}`);
    }

    return assetPath;
}

/**
 * Capture pageerror + console.error for the page lifetime.
 * Call before scripts load; assertClean() after scenarios.
 */
export function installErrorCapture(page: Page): ErrorCapture {
    const errors: string[] = [];

    page.on('pageerror', (err) => {
        errors.push(`pageerror: ${err.message}`);
    });

    page.on('console', (msg: ConsoleMessage) => {
        if (msg.type() !== 'error') {
            return;
        }
        errors.push(`console.error: ${msg.text()}`);
    });

    return {
        errors,
        assertClean: () => {
            if (errors.length > 0) {
                throw new Error(`Unexpected page errors:\n${errors.join('\n')}`);
            }
        },
    };
}

async function mountHostVueApp(page: Page): Promise<void> {
    const hostScript = path.join(ROOT, 'node_modules/vue/dist/vue.global.prod.js');
    if (!fs.existsSync(hostScript)) {
        throw new Error(`Host Vue global missing at ${hostScript}`);
    }

    await page.addScriptTag({ path: hostScript });
    await page.evaluate(() => {
        const Vue = (window as any).Vue;
        if (!Vue?.createApp || !Vue?.reactive) {
            throw new Error('Host Vue global not available after script load');
        }

        const hostState = Vue.reactive({ ticks: 0, label: 'host-vue-app' });
        Vue.createApp({
            setup() {
                return { hostState };
            },
            template:
                "<div class='host-root'>" +
                "<span class='host-label'>{{ hostState.label }}</span>" +
                " <span class='host-tick'>{{ hostState.ticks }}</span>" +
                '</div>',
        }).mount('#app');

        (window as any).__HOST_VUE_VERSION__ = Vue.version;
        (window as any).__HOST_STATE__ = hostState;
    });
}

export async function assertHostRootMounted(page: Page): Promise<void> {
    const host = await page.evaluate(() => {
        const root = document.querySelector('#app .host-root') as HTMLElement | null;
        return {
            present: Boolean(root),
            text: root?.innerText ?? '',
            tick: document.querySelector('#app .host-tick')?.textContent ?? null,
        };
    });

    if (!host.present) {
        throw new Error('Expected host .host-root to remain mounted after toolbar injection');
    }
}

/**
 * Bump reactive host state and assert the host app re-renders.
 * Proves dual-runtime coexistence after toolbar panel work.
 */
export async function assertHostRemainsReactive(page: Page): Promise<void> {
    await assertHostRootMounted(page);

    const before = await page.evaluate(() => {
        return document.querySelector('#app .host-tick')?.textContent ?? null;
    });

    await page.evaluate(() => {
        const state = (window as any).__HOST_STATE__;
        if (!state) {
            throw new Error('__HOST_STATE__ missing — host Vue app not mounted');
        }
        state.ticks = Number(state.ticks ?? 0) + 1;
        state.label = 'host-still-alive';
    });

    await page.waitForFunction(
        (prev) => {
            const tick = document.querySelector('#app .host-tick')?.textContent;
            const label = document.querySelector('#app .host-label')?.textContent;
            return tick !== prev && label === 'host-still-alive';
        },
        before,
        { timeout: 5_000 },
    );

    await assertHostRootMounted(page);
}

/**
 * Mount the production toolbar bundle in a real browser page.
 * Optionally mounts a host Vue app first (Inertia/Vue dual-runtime shape).
 *
 * Uses an http origin so localStorage works for the pinned-panel bootstrap path.
 */
export async function mountProductionToolbar(
    page: Page,
    options?: {
        hostVue?: HostVueVariant;
        pin?: string | null;
        animations?: boolean;
        /** full = complete inline payload; compact = layout/history only + async hydrate */
        bootstrap?: 'full' | 'compact';
    },
): Promise<void> {
    const hostVue = options?.hostVue ?? 'none';
    const pin = options?.pin ?? 'models';
    const bootstrap = options?.bootstrap ?? 'full';
    const fullPayload = buildSyntheticToolbarPayload({
        animations: options?.animations ?? true,
    });
    const bootstrapPayload =
        bootstrap === 'compact'
            ? buildCompactBootstrapPayload({ animations: options?.animations ?? true })
            : fullPayload;
    const toolbarAsset = resolveProductionToolbarAsset();

    const html = `<!doctype html>
<html>
  <head><meta charset="utf-8" /><title>toolbar browser test</title></head>
  <body style="margin:0;background:#111;min-height:100vh;color:#fff">
    <div id="app"><p>host app</p></div>
    <div id="laravel-toolbar-shadow-host"></div>
  </body>
</html>`;

    await page.route('**/toolbar-browser-fixture', async (route) => {
        await route.fulfill({
            status: 200,
            contentType: 'text/html',
            body: html,
        });
    });

    if (bootstrap === 'compact') {
        const endpointBody = buildRequestDataEndpointBody(fullPayload);
        await page.route(`**/_toolbar/requests/${REQUEST_ID}`, async (route) => {
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify(endpointBody),
            });
        });
    }

    await page.goto('http://127.0.0.1/toolbar-browser-fixture');

    if (pin) {
        await page.evaluate((toolId) => {
            localStorage.setItem('toolbar-pinned-tool', toolId);
        }, pin);
    } else {
        await page.evaluate(() => {
            localStorage.removeItem('toolbar-pinned-tool');
        });
    }

    await page.evaluate((data) => {
        (window as any).__LARAVEL_TOOLBAR_DATA__ = data;
        (window as any).__LARAVEL_TOOLBAR_CSS_URL__ = '';
        (window as any).__LARAVEL_TOOLBAR_ASSET_VERSION__ = 'browser-test';
    }, bootstrapPayload);

    if (hostVue === 'package') {
        await mountHostVueApp(page);
        await assertHostRootMounted(page);
    }

    await page.addScriptTag({ path: toolbarAsset });

    await page.waitForFunction(() => {
        const host = document.getElementById('laravel-toolbar-shadow-host');
        const root = host?.shadowRoot;
        return Boolean(root?.getElementById('toolbar'));
    }, null, { timeout: 15_000 });

    if (hostVue === 'package') {
        await assertHostRootMounted(page);
    }

    if (bootstrap === 'compact') {
        await waitForHydratedQueries(page, QUERY_COUNT);
    }
}

/** Wait until async request payload hydration populates query count into toolbar chrome. */
export async function waitForHydratedQueries(
    page: Page,
    expectedQueryCount: number = QUERY_COUNT,
): Promise<void> {
    await page.waitForFunction(
        (expectedQueries) => {
            const root = document.getElementById('laravel-toolbar-shadow-host')?.shadowRoot;
            if (!root) {
                return false;
            }
            // Database tool summary is "261:128ms" once queries hydrate.
            const toolbarText = root.getElementById('toolbar')?.innerText ?? '';
            return toolbarText.includes(`${expectedQueries}:`);
        },
        expectedQueryCount,
        { timeout: 15_000 },
    );
}

export async function inspectPanel(page: Page) {
    return page.evaluate(() => {
        const host = document.getElementById('laravel-toolbar-shadow-host');
        const root = host?.shadowRoot;
        const panel = root?.querySelector('.fixed.inset-x-0') as HTMLElement | null;
        const text = panel?.innerText ?? '';
        const requestsTable = root?.querySelector('.requests-table') as HTMLElement | null;
        const requestsRowCount = requestsTable?.querySelectorAll('tbody tr').length ?? 0;
        const requestsDataRowCount =
            requestsTable?.querySelectorAll('tbody tr[data-request-id]').length ?? 0;
        const hostRoot = document.querySelector('#app .host-root') as HTMLElement | null;

        return {
            pin: localStorage.getItem('toolbar-pinned-tool'),
            hostVueVersion: (window as any).__HOST_VUE_VERSION__ ?? null,
            hostRootPresent: Boolean(hostRoot),
            hostRootText: hostRoot?.innerText ?? '',
            hostTick: document.querySelector('#app .host-tick')?.textContent ?? null,
            hasPanel: Boolean(panel),
            hasScrollable: Boolean(root?.querySelector('.scrollable-table')),
            rowCount: root?.querySelectorAll('.scrollable-table tbody tr').length ?? 0,
            text: text.slice(0, 1200),
            hasModelsHeader: text.includes('Models'),
            hasQueriesHeader: text.includes('Queries'),
            hasRequestsTable: Boolean(requestsTable),
            requestsRowCount,
            requestsDataRowCount,
            hasTimingsBar: Boolean(root?.querySelector('.timings-bar')),
            hasMemoryBar: Boolean(root?.querySelector('.memory-bar')),
        };
    });
}

/**
 * Click a default center-group tool by stable layout order (not production test hooks).
 * Group DOM: first child is the absolute chrome background; tools follow in layout order.
 */
export async function clickToolbarTool(page: Page, toolId: DefaultToolId) {
    const index = DEFAULT_TOOL_ORDER.indexOf(toolId);
    if (index < 0) {
        throw new Error(`Unknown tool id: ${toolId}`);
    }

    const clicked = await page.evaluate((toolIndex) => {
        const root = document.getElementById('laravel-toolbar-shadow-host')?.shadowRoot;
        if (!root) {
            return false;
        }

        const group = root.querySelector('#toolbar .relative.flex.items-center.overflow-hidden');
        if (!group) {
            return false;
        }

        // Skip the absolute background layer (index 0).
        const toolRoots = [...group.children].slice(1);
        const toolRoot = toolRoots[toolIndex] as HTMLElement | undefined;
        if (!toolRoot) {
            return false;
        }

        const clickTarget =
            (toolRoot.querySelector('.cursor-default, .relative.cursor-default') as HTMLElement | null) ??
            toolRoot;
        clickTarget.click();
        return true;
    }, index);

    if (!clicked) {
        throw new Error(`Could not click toolbar tool index ${index} (${toolId})`);
    }

    await page.waitForTimeout(300);
}
