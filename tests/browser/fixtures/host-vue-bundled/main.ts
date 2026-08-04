import { createApp, h, reactive, version } from 'vue';

/**
 * Parser-time ES module host (type=module), mirrors Vite/Inertia app bundles.
 *
 * Also overwrites window._ with a Lodash-like wrapper function — the real
 * Servauto failure mode. Unwrapped classic toolbar scripts minify Vue withCtx
 * to top-level `function _` (window._); Lodash then clobbers it.
 */
function lodashLike(value: unknown) {
    // Lodash `_(fn)` returns a wrapper object, not a callable slot function.
    return {
        __wrapped__: value,
        __actions__: [] as unknown[],
        __chain__: false,
        __index__: 0,
        __values__: undefined as unknown,
    };
}

const order = ((window as any).__SCRIPT_ORDER__ ??= []) as string[];
if (!order.includes('host')) {
    order.push('host');
}

// Clobber any top-level classic-script `function _` that leaked onto window.
(window as any)._ = lodashLike;
(window as any).__HOST_LODASH_OVERWRITE__ = true;

const hostState = reactive({ ticks: 0, label: 'host-vue-bundled' });

createApp({
    setup() {
        const id = window.setInterval(() => {
            hostState.ticks = (hostState.ticks + 1) % 100000;
        }, 16);
        (window as any).__HOST_TICK_TIMER__ = id;

        return () =>
            h('div', { class: 'host-root' }, [
                h('span', { class: 'host-label' }, hostState.label),
                ' ',
                h('span', { class: 'host-tick' }, String(hostState.ticks)),
            ]);
    },
}).mount('#app');

(window as any).__HOST_VUE_VERSION__ = version;
(window as any).__HOST_STATE__ = hostState;
(window as any).__HOST_HAS_INSTANCE_SETTERS__ =
    typeof (globalThis as any).__VUE_INSTANCE_SETTERS__ !== 'undefined';
(window as any).__HOST_MOUNTED__ = true;
