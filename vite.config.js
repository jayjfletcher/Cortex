import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    base: '',
    publicDir: false,
    build: {
        outDir: 'public',
        emptyOutDir: false,
        assetsInlineLimit: 16384,
        rollupOptions: {
            input: 'resources/js/app.js',
            output: {
                entryFileNames: 'app.js',
                assetFileNames: 'app.[ext]',
                inlineDynamicImports: true,
            },
        },
    },
});
