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
                'resources/css/public-display.css',
                'resources/js/client/realizations/create.js',
                'resources/js/client/remainders/create.js',
                'resources/js/client/markdowns/create.js',
                'resources/js/admin/orders/index.js',
                'resources/js/admin/ingredient_movements/create.js',
                'resources/js/admin/ingredient_movements/show.js',
                'resources/js/client/feedback/create.js',
                'resources/js/public/orders/display.js'
            ],
            refresh: true,
        }),
    ],
});
