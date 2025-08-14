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
                        // Core Vue ecosystem
                        if (id.includes('vue') && !id.includes('vue-router')) return 'vendor-vue'
                        if (id.includes('vue-router')) return 'vendor-router'

                        // HTTP and data fetching
                        if (id.includes('axios')) return 'vendor-http'

                        // UI libraries
                        if (id.includes('sweetalert2')) return 'vendor-ui'
                        if (id.includes('nprogress')) return 'vendor-ui'
                        if (id.includes('@headlessui') || id.includes('primevue')) return 'vendor-ui'

                        // Text editing
                        if (id.includes('tinymce') || id.includes('quill')) return 'vendor-editor'

                        // Utilities
                        if (id.includes('date-fns') || id.includes('select2') || id.includes('jquery')) return 'vendor-utils'

                        // Everything else
                        return 'vendor'
                    }

                    // Split pages into separate chunks
                    if (id.includes('/pages/')) {
                        const match = id.match(/\/pages\/([^/]+)\.vue$/)
                        if (match) return `page-${match[1].toLowerCase()}`
                    }

                    // Split composables
                    if (id.includes('/composables/')) return 'composables'
                },
            },
        },
        chunkSizeWarningLimit: 900,
        // Enable compression
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true,
                passes: 2,
                pure_funcs: ['console.info', 'console.debug'],
            },
            mangle: true
        },
        // Asset optimization
        assetsInlineLimit: 2048,
    },
});
