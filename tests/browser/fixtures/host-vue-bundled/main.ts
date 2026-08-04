import { createApp, h, reactive, version } from 'vue';

/**
 * Vite-bundled host app using esm-bundler Vue (registers __VUE_INSTANCE_SETTERS__).
 * Continuously re-renders like a live Inertia/Vue SPA so dual-runtime races surface.
 */
const hostState = reactive({ ticks: 0, label: 'host-vue-bundled' });

createApp({
    setup() {
        // Keep the host renderer busy so shared instance setters are exercised
        // while the toolbar mounts and switches panels.
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
