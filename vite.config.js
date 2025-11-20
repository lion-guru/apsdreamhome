import { defineConfig } from 'vite';

export default defineConfig({
  root: '.',
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        app: 'src/js/app.js',           // main JS entry
        style: 'src/css/style.css'      // main CSS entry
      }
    }
  },
  server: {
    host: 'localhost',
    port: 3000,
    open: false
  },
  publicDir: 'public'                  // static files (images, fonts)
});