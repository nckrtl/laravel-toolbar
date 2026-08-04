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

/**
 * - none: no host Vue
 * - package: vue.global.prod.js (no INSTANCE_SETTERS) — weaker dual-runtime; host
 *   mounts after toolbar (post-load inject)
 * - bundled: parser-time `<script type="module">` host (esm-bundler Vue + Lodash-like
 *   `window._`) with classic toolbar at body end — matches Servauto
 */
export type HostVueVariant = 'none' | 'package' | 'bundled';

export type ErrorCapture = {
    errors: string[];
    assertClean: () => void;
};

type ManifestEntry = { file?: string; css?: string[] };

function readProductionManifest(): Record<string, ManifestEntry> {
    const manifestPath = path.join(ROOT, 'build/manifest.json');
    if (!fs.existsSync(manifestPath)) {
        throw new Error('Missing build/manifest.json. Run `npm run build` before browser tests.');
    }

    return JSON.parse(fs.readFileSync(manifestPath, 'utf8')) as Record<string, ManifestEntry>;
}

function resolveToolbarProdEntry(): ManifestEntry {
    const entry = readProductionManifest()['resources/js/toolbar.prod.ts'];
    if (!entry?.file) {
        throw new Error('toolbar.prod.ts entry missing from build/manifest.json');
    }
    return entry;
}

/** Resolve the hashed production toolbar.prod JS path from the Vite manifest. */
export function resolveProductionToolbarAsset(): string {
    const entry = resolveToolbarProdEntry();
    const assetPath = path.join(ROOT, 'build', entry.file!);
    if (!fs.existsSync(assetPath)) {
        throw new Error(`Built toolbar asset not found: ${assetPath}`);
    }
    return assetPath;
}

/** Production CSS companion to toolbar.prod (delayed-CSS race fixtures). */
export function resolveProductionToolbarCssAsset(): string {
    const entry = resolveToolbarProdEntry();
    const cssRel = entry.css?.[0];
    if (!cssRel) {
        throw new Error('toolbar.prod.css missing from build/manifest.json');
    }
    const assetPath = path.join(ROOT, 'build', cssRel);
    if (!fs.existsSync(assetPath)) {
        throw new Error(`Built toolbar CSS not found: ${assetPath}`);
    }
    return assetPath;
}

/**
 * Capture pageerror + console.error for the page lifetime.
 * Call before scripts load; assertClean() after green scenarios only.
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

function resolveBundledHostAsset(): string {
    return path.join(ROOT, 'tests/browser/fixtures/dist-host/host-vue-bundled.js');
}

/** Host vue.global.prod dual-runtime (weaker; no INSTANCE_SETTERS). */
async function mountHostVueGlobal(page: Page): Promise<void> {
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
        (window as any).__HOST_HAS_INSTANCE_SETTERS__ =
            typeof (globalThis as any).__VUE_INSTANCE_SETTERS__ !== 'undefined';
        (window as any).__HOST_MOUNTED__ = true;
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
 * Mount the production toolbar in a real browser page.
 *
 * For hostVue=bundled (parser-faithful Servauto layout):
 * ```html
 * <head><script type="module" src="/host-vue-module.js"></script></head>
 * <body>
 *   <div id="app">…</div>
 *   <div id="laravel-toolbar-shadow-host"></div>
 *   <script>/* toolbar data * /</script>
 *   <script src="/toolbar.prod.js"></script>  <!-- classic, body end -->
 * </body>
 * ```
 *
 * Module scripts are deferred → classic toolbar runs first (may await CSS), then
 * host module. Exercise ToolbarItem/ScrollableTable slot mounts AFTER host is live.
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
        /**
         * Hold toolbar CSS until host module has mounted (production race).
         * Defaults to true when hostVue === 'bundled'.
         */
        interleaveHostDuringCss?: boolean;
        /**
         * Override the served toolbar.prod.js body (test-only).
         * Used to evaluate an unwrapped production copy for RED regression proof.
         */
        toolbarScriptBody?: string;
    },
): Promise<void> {
    const hostVue = options?.hostVue ?? 'none';
    // Default pin is models only when omitted; explicit null means unpinned.
    const pin = options?.pin === undefined ? 'models' : options.pin;
    const bootstrap = options?.bootstrap ?? 'full';
    const interleaveHostDuringCss =
        options?.interleaveHostDuringCss ?? hostVue === 'bundled';
    const fullPayload = buildSyntheticToolbarPayload({
        animations: options?.animations ?? true,
    });
    const bootstrapPayload =
        bootstrap === 'compact'
            ? buildCompactBootstrapPayload({ animations: options?.animations ?? true })
            : fullPayload;
    const toolbarJs =
        options?.toolbarScriptBody ?? fs.readFileSync(resolveProductionToolbarAsset(), 'utf8');
    const toolbarCss = fs.readFileSync(resolveProductionToolbarCssAsset(), 'utf8');

    const useParserTimeHost = hostVue === 'bundled';
    if (useParserTimeHost && !fs.existsSync(resolveBundledHostAsset())) {
        throw new Error(
            `Bundled host missing at ${resolveBundledHostAsset()}. Run: node tests/browser/build-host-vue.mjs`,
        );
    }
    const hostModuleJs = useParserTimeHost
        ? fs.readFileSync(resolveBundledHostAsset(), 'utf8')
        : null;

    // Delayed CSS gate: classic toolbar parks on fetch while deferred host module runs.
    let releaseCss: (() => void) | null = null;
    const cssGate =
        interleaveHostDuringCss && useParserTimeHost
            ? new Promise<void>((resolve) => {
                  releaseCss = resolve;
              })
            : null;

    const cssUrl = cssGate ? 'http://127.0.0.1/toolbar-css-fixture.css' : '';

    const hostModuleTag = useParserTimeHost
        ? '<script type="module" src="http://127.0.0.1/host-vue-module.js"></script>'
        : '';

    // Bootstrap data + classic toolbar in the document (parser order).
    const inlineBootstrap = `
    <script>
      window.__LARAVEL_TOOLBAR_DATA__ = ${JSON.stringify(bootstrapPayload)};
      window.__LARAVEL_TOOLBAR_CSS_URL__ = ${JSON.stringify(cssUrl)};
      window.__LARAVEL_TOOLBAR_ASSET_VERSION__ = 'browser-test';
      window.__SCRIPT_ORDER__ = [];
      window.__HOST_MOUNTED__ = false;
    </script>
    <script src="http://127.0.0.1/toolbar.prod.js"></script>
    <script>
      (window.__SCRIPT_ORDER__ = window.__SCRIPT_ORDER__ || []).push('toolbar-script');
    </script>`;

    const html = `<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>toolbar browser test</title>
    ${hostModuleTag}
  </head>
  <body style="margin:0;background:#111;min-height:100vh;color:#fff">
    <div id="app"><p>host app</p></div>
    <div id="laravel-toolbar-shadow-host"></div>
    ${inlineBootstrap}
  </body>
</html>`;

    await page.route('**/toolbar-browser-fixture', async (route) => {
        await route.fulfill({
            status: 200,
            contentType: 'text/html',
            body: html,
        });
    });

    await page.route('**/toolbar.prod.js', async (route) => {
        await route.fulfill({
            status: 200,
            contentType: 'application/javascript',
            body: toolbarJs,
        });
    });

    if (useParserTimeHost) {
        await page.route('**/host-vue-module.js', async (route) => {
            await route.fulfill({
                status: 200,
                contentType: 'text/javascript',
                headers: { 'Content-Type': 'text/javascript' },
                body: hostModuleJs!,
            });
        });
    }

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

    if (cssGate) {
        await page.route('**/toolbar-css-fixture.css', async (route) => {
            await cssGate;
            await route.fulfill({
                status: 200,
                contentType: 'text/css',
                body: toolbarCss,
            });
        });
    }

    // Pin must be set before the classic toolbar script runs.
    if (pin) {
        await page.addInitScript((toolId) => {
            localStorage.setItem('toolbar-pinned-tool', toolId);
        }, pin);
    } else {
        await page.addInitScript(() => {
            localStorage.removeItem('toolbar-pinned-tool');
        });
    }

    if (cssGate && useParserTimeHost) {
        // Classic toolbar requests CSS and parks; deferred host module runs mid-await.
        const cssRequestPromise = page.waitForRequest('**/toolbar-css-fixture.css', {
            timeout: 15_000,
        });

        await page.goto('http://127.0.0.1/toolbar-browser-fixture', {
            waitUntil: 'domcontentloaded',
        });

        await cssRequestPromise;

        await page.waitForFunction(() => Boolean((window as any).__HOST_MOUNTED__), null, {
            timeout: 15_000,
        });
        await assertHostRootMounted(page);

        // Invariant: while CSS is still gated, Vue app must not have mounted yet.
        const mountedBeforeCss = await page.evaluate(() => {
            const root = document.getElementById('laravel-toolbar-shadow-host')?.shadowRoot;
            return Boolean(root?.getElementById('toolbar'));
        });
        if (mountedBeforeCss) {
            throw new Error(
                'Toolbar #toolbar mounted before delayed CSS release — race fixture invalid',
            );
        }

        releaseCss?.();

        await page.waitForFunction(() => {
            const root = document.getElementById('laravel-toolbar-shadow-host')?.shadowRoot;
            return Boolean(root?.getElementById('toolbar'));
        }, null, { timeout: 15_000 });

        await page.evaluate(() => {
            const order = ((window as any).__SCRIPT_ORDER__ ??= []) as string[];
            if (!order.includes('toolbar-mounted')) {
                order.push('toolbar-mounted');
            }
        });
    } else {
        await page.goto('http://127.0.0.1/toolbar-browser-fixture', {
            waitUntil: 'domcontentloaded',
        });

        if (hostVue === 'package') {
            await mountHostVueGlobal(page);
            await assertHostRootMounted(page);
        }

        if (useParserTimeHost) {
            await page.waitForFunction(() => Boolean((window as any).__HOST_MOUNTED__), null, {
                timeout: 15_000,
            });
            await assertHostRootMounted(page);
        }

        await page.waitForFunction(() => {
            const root = document.getElementById('laravel-toolbar-shadow-host')?.shadowRoot;
            return Boolean(root?.getElementById('toolbar'));
        }, null, { timeout: 15_000 });

        await page.evaluate(() => {
            const order = ((window as any).__SCRIPT_ORDER__ ??= []) as string[];
            if (!order.includes('toolbar-mounted')) {
                order.push('toolbar-mounted');
            }
        });
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
        const toolbar = root?.getElementById('toolbar');
        // Tool roots sit after the absolute chrome background in each group.
        const toolRoots = toolbar
            ? [...(toolbar.querySelectorAll('.relative.flex.items-center.overflow-hidden') ?? [])]
                  .flatMap((group) => [...group.children].slice(1))
            : [];
        const blankToolCount = toolRoots.filter((el) => {
            const text = (el.textContent ?? '').replace(/\s+/g, '');
            // Vue failed components collapse to comment nodes → empty text.
            return text.length === 0;
        }).length;

        return {
            pin: localStorage.getItem('toolbar-pinned-tool'),
            hostVueVersion: (window as any).__HOST_VUE_VERSION__ ?? null,
            hostHasInstanceSetters: Boolean((window as any).__HOST_HAS_INSTANCE_SETTERS__),
            hostMounted: Boolean((window as any).__HOST_MOUNTED__),
            hostRootPresent: Boolean(hostRoot),
            hostRootText: hostRoot?.innerText ?? '',
            hostTick: document.querySelector('#app .host-tick')?.textContent ?? null,
            scriptOrder: ((window as any).__SCRIPT_ORDER__ as string[] | undefined) ?? [],
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
            toolRootCount: toolRoots.length,
            blankToolCount,
            toolbarText: (toolbar?.innerText ?? '').slice(0, 400),
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
