import { defineConfig } from 'vite';
import { resolve } from 'node:path';

// Bundle Vue with the esm-bundler runtime so the host registers
// __VUE_INSTANCE_SETTERS__ like a real Vite/Inertia app (not vue.global.prod).
export default defineConfig({
    build: {
        lib: {
            entry: resolve(__dirname, 'main.ts'),
            name: 'HostVueBundled',
            formats: ['iife'],
            fileName: () => 'host-vue-bundled.js',
        },
        outDir: resolve(__dirname, '../dist-host'),
        emptyOutDir: true,
        minify: true,
        // Keep Vue internals so INSTANCE_SETTERS stay live.
        rollupOptions: {
            // Do not externalize vue — must be bundled like a real host app.
            external: [],
        },
    },
    define: {
        'process.env.NODE_ENV': JSON.stringify('production'),
    },
});
