import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/css/client.css',
                'resources/css/admin.css',
                'resources/js/client/realizations/create.js',
                'resources/js/client/remainders/create.js',
                'resources/js/admin/orders/index.js'
            ],
            refresh: true,
        }),
    ],
});
