import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('vue')) return 'vendor-vue'
                        if (id.includes('axios')) return 'vendor-axios'
                        if (id.includes('nprogress')) return 'vendor-nprogress'
                        if (id.includes('sweetalert2')) return 'vendor-swal'
                        return 'vendor'
                    }
                },
            },
        },
        chunkSizeWarningLimit: 1000,
    },
});
