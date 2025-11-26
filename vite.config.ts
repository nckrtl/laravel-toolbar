import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { run } from 'vite-plugin-run';

export default defineConfig({
  plugins: [
    tailwindcss(),
    vue(),
    laravel({
        input: process.env.NODE_ENV === 'production'
          ? 'resources/js/toolbar.prod.js'
          : 'resources/js/toolbar.dev.js',
        publicDirectory: './',
        detectTls: true,
    }),
    run({
      name: 'typescript-transformer',
      run: ['php', 'vendor/bin/testbench', 'typescript:transform'],
      pattern: ['src/Data/**/*.php'],
    }),
  ],
})
