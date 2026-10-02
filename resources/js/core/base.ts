import { setupInterceptors } from '@/core/interceptors';
import { log } from '@/core/utils/logger';

export type MountFunction = () => Promise<void>;

// Setup interceptors first (shared between dev and prod)
setupInterceptors();

let toolbarPageUpdateListenerBound = false;

/**
 * Shared mount orchestration
 * Import the appropriate mount function (dev or prod) and call this
 */
export async function initToolbar(mountFn: MountFunction): Promise<void> {
    // Hosted mode: an embedding app (such as T3 Code's browser) sets this before the page
    // loads and draws the toolbar itself. The interceptors above still report every request
    // through `laravel-toolbar:update`; only the toolbar UI stays unmounted.
    if (window.__LARAVEL_TOOLBAR_HOST__) {
        log(`Hosted by ${window.__LARAVEL_TOOLBAR_HOST__}; not mounting the toolbar UI`);
        return;
    }

    if (!toolbarPageUpdateListenerBound) {
        window.addEventListener('laravel-toolbar:html-updated', () => {
            void mountFn();
        });

        toolbarPageUpdateListenerBound = true;
    }

    try {
        await mountFn();
    } catch (error) {
        console.error('[Laravel Toolbar] Failed to mount:', error);
        throw error;
    }
}
