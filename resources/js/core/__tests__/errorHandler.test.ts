import { createApp, defineComponent, h, nextTick } from 'vue';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import {
    attachToolbarErrorHandler,
    cleanupFailedMount,
    mountVueApp,
    setupShadowDOM,
} from '@/core/mount.base';

const seedToolbarLayout = () => {
    window.dispatchEvent(
        new CustomEvent('laravel-toolbar:update', {
            detail: {
                data: {
                    ...window.__LARAVEL_TOOLBAR_DATA__,
                    request_id: 'default-request-id',
                    selected_request_id: 'default-request-id',
                    request_history: window.__LARAVEL_TOOLBAR_DATA__.request_history,
                    animations: false,
                    layout: {
                        sections: {
                            left: [],
                            center: [],
                            right: [],
                        },
                    },
                },
            },
        }),
    );
};

describe('production Vue errorHandler observability', () => {
    beforeEach(() => {
        seedToolbarLayout();
        document.body.innerHTML = '<div id="laravel-toolbar-shadow-host"></div>';
    });

    afterEach(() => {
        vi.restoreAllMocks();
        cleanupFailedMount();
        document.body.innerHTML = '';
    });

    it('mountVueApp installs production errorHandler that reports via console.error', async () => {
        const consoleError = vi.spyOn(console, 'error').mockImplementation(() => {});

        const { appContainer } = setupShadowDOM();
        const app = mountVueApp(appContainer);
        await nextTick();

        expect(typeof app.config.errorHandler).toBe('function');

        consoleError.mockClear();
        const boom = new Error('panel render boom');
        app.config.errorHandler?.(boom, null, 'render function');

        expect(consoleError).toHaveBeenCalled();
        const [prefix, err, info] = consoleError.mock.calls[0] ?? [];
        expect(prefix).toBe('[Laravel Toolbar] Vue error:');
        expect(err).toBe(boom);
        expect(info).toBe('render function');
    });

    it('attachToolbarErrorHandler surfaces child render exceptions through console.error', async () => {
        const consoleError = vi.spyOn(console, 'error').mockImplementation(() => {});

        const Broken = defineComponent({
            setup() {
                throw new Error('child setup boom');
            },
            render() {
                return h('div', 'unreachable');
            },
        });

        const host = document.createElement('div');
        document.body.appendChild(host);

        const app = createApp({
            render: () => h(Broken),
        });
        attachToolbarErrorHandler(app);
        app.mount(host);
        await nextTick();

        expect(consoleError).toHaveBeenCalled();
        const [prefix, err] = consoleError.mock.calls[0] ?? [];
        expect(prefix).toBe('[Laravel Toolbar] Vue error:');
        expect(String(err)).toContain('child setup boom');

        app.unmount();
        host.remove();
    });
});
