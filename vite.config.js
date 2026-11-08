import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // หน้าบ้าน (public site) — Bootstrap 5 เท่านั้น
                'resources/sass/web.scss',
                'resources/js/web.js',
                // หลังบ้าน (admin) — AdminLTE v4 (Bootstrap 5), คนละ bundle จากหน้าบ้าน
                'resources/sass/admin.scss',
                'resources/js/admin.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});