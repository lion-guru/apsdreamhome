import { defineConfig } from 'vite';

export default defineConfig({
  root: '.',
  build: {
    outDir: 'public/dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        main: 'assets/js/main.js',
        style: 'assets/css/style.css'
      }
    }
  },
  server: {
    host: 'localhost',
    port: 3000,
    open: false
  },
  publicDir: false
});
