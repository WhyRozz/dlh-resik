import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/notifications.js',
            ],
            refresh: true,
        }),
    ],

    server: {
        host: true,
        port: 5173,
        strictPort: true,

        cors: true,

        hmr: {
            host: '192.168.0.34',
            protocol: 'ws',
            port: 5173,
        },
    },
});