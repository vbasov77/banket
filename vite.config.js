import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

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
        host: '0.0.0.0', // Слушаем все интерфейсы
        origin: 'http://new-bank.loc', // Ваш домен
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'new-bank.loc',
        },
    },
    build: {
        rollupOptions: {
            // Опциональная настройка для больших проектов
            // Позволяет разделить код на чанки
            output: {
                manualChunks: {
                    alpine: ['alpinejs'],
                },
            },
        },
    },
    resolve: {
        // Алиасы для удобства импорта
        alias: {
            '@': '/resources/js',
        },
    },
});
