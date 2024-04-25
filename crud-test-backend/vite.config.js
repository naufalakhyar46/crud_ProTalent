import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue'; 
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: 'localhost'
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        // vue({ 
        //     template: {
        //         transformAssetUrls: {
        //             base: null,
        //             includeAbsolute: false,
        //         },
        //     },
        // })
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    build: {
        // target: 'es2015',
        rollupOptions: {
          output: {
            manualChunks(id) {
              if (id.includes('node_modules')) {
                return 'vendor'
              }
            }
          }
        }
    }
});
