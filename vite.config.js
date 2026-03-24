import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path'; // Добавляем эту строку

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        origin: 'http://new-bank.loc',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'new-bank.loc',
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    alpine: ['alpinejs'],
                },
            },
        },
    },
    resolve: {
        alias: {
            '@': '/resources/js',
            'bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
        },
    },
});
