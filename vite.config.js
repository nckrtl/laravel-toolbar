import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    vue(),
    laravel({
        input: 'resources/js/toolbar.js',
        publicDirectory: './',
        detectTls: true,
    })
  ],
})
