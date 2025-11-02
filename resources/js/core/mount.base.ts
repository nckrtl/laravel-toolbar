import { createApp, type App } from 'vue';
import Toolbar from '@/Toolbar.vue';
import { log, logData } from '@/core/utils/logger';

let isToolbarMounted = false;
let shadowRootRef: ShadowRoot | null = null;

export interface ShadowDOMSetupResult {
    shadowRoot: ShadowRoot;
    appContainer: HTMLElement;
}

export function setShadowRoot(shadowRoot: ShadowRoot): void {
    log('Storing shadowRoot reference');
    shadowRootRef = shadowRoot;
}

export function getShadowRoot(): ShadowRoot | null {
    return shadowRootRef;
}

export function setupShadowDOM(): ShadowDOMSetupResult {
    log('📦 Setting up Shadow DOM');

    const shadowHost = document.getElementById('laravel-toolbar-shadow-host');
    if (!shadowHost) {
        throw new Error('Shadow host not found - toolbar HTML not injected?');
    }

    // Check if shadow was already created (from cache or template)
    let shadowRoot = shadowHost.shadowRoot;

    if (!shadowRoot) {
        // No cache, no template - create fresh
        log('Creating fresh Shadow DOM (first visit)');
        shadowRoot = shadowHost.attachShadow({ mode: 'open' });

        // Create the root container
        const rootDiv = document.createElement('div');
        rootDiv.id = 'laravel-toolbar-root';
        shadowRoot.appendChild(rootDiv);
    } else {
        log('Shadow DOM already exists (from cache)');
    }

    setShadowRoot(shadowRoot);

    const appContainer = shadowRoot.getElementById('laravel-toolbar-root');
    if (!appContainer) {
        throw new Error('Toolbar root not found in shadow DOM');
    }

    log('✅ Shadow DOM ready');
    return { shadowRoot, appContainer };
}

export function mountVueApp(appContainer: HTMLElement): App<Element> {
    log('🎯 Mounting Vue app');

    const app = createApp({
        ...Toolbar,
        devtools: {
            hide: true,
        },
    });

    app.config.errorHandler = (err, instance, info) => {
        log('❌ Vue error:', err);
        log('Component:', instance);
        log('Error info:', info);
    };

    app.mount(appContainer);
    log('✅ Vue app mounted in Shadow DOM');

    return app;
}

export function cleanupFailedMount(): void {
    log('🧹 Cleaning up after failed mount');

    const shadowHost = document.getElementById('laravel-toolbar-shadow-host');
    if (shadowHost) {
        shadowHost.remove();
        log('Removed shadow host from DOM');
    }

    isToolbarMounted = false;
}

export function guardMount(): boolean {
    if (isToolbarMounted) {
        log('⚠️ Toolbar already mounted, skipping');
        return false;
    }
    isToolbarMounted = true;
    return true;
}

export function mountSuccess(): void {
    log('✅ Toolbar mounted successfully');
    logData(window.__LARAVEL_TOOLBAR_DATA__);
}
