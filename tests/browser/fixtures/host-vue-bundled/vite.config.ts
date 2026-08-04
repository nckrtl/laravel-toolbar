import { defineConfig } from 'vite';
import { resolve } from 'node:path';

// ES module host (not IIFE): mirrors Vite/Inertia <script type="module" src="/build/assets/app-*.js">.
// Bundles esm-bundler Vue so __VUE_INSTANCE_SETTERS__ registers like a real host app.
export default defineConfig({
    build: {
        lib: {
            entry: resolve(__dirname, 'main.ts'),
            name: 'HostVueBundled',
            formats: ['es'],
            fileName: () => 'host-vue-bundled.js',
        },
        outDir: resolve(__dirname, '../dist-host'),
        emptyOutDir: true,
        minify: true,
        rollupOptions: {
            external: [],
        },
    },
    define: {
        'process.env.NODE_ENV': JSON.stringify('production'),
    },
});
