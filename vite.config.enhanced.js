/**
 * APS Dream Home - Enhanced Build Configuration
 * Advanced performance optimizations and build settings
 */

import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';
import { resolve } from 'path';

export default defineConfig(({ mode }) => ({
  // Base configuration
  root: __dirname,
  publicDir: 'public',
  base: mode === 'development' ? '/' : '/',

  // Enhanced build configuration
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,

    // Rollup options for advanced bundling
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'index.php'),
        admin: resolve(__dirname, 'admin/admin_panel.php'),
        properties: resolve(__dirname, 'properties.php'),
        about: resolve(__dirname, 'about.php'),
        contact: resolve(__dirname, 'contact.php'),
      },
      output: {
        // JavaScript files
        entryFileNames: (chunkInfo) => {
          if (chunkInfo.name === 'main') return 'js/index.[hash].js';
          if (chunkInfo.name === 'admin') return 'js/admin.[hash].js';
          return 'js/[name].[hash].js';
        },
        chunkFileNames: 'js/chunks/[name].[hash].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name?.endsWith('.css')) {
            return 'css/[name].[hash][extname]';
          }
          if (assetInfo.name?.match(/\.(png|jpg|jpeg|svg|gif|webp)$/)) {
            return 'images/[name].[hash][extname]';
          }
          if (assetInfo.name?.match(/\.(woff|woff2|ttf|eot)$/)) {
            return 'fonts/[name].[hash][extname]';
          }
          return 'assets/[name].[hash][extname]';
        },
      },
    },

    // Advanced optimizations
    cssCodeSplit: true,
    minify: mode === 'production' ? 'terser' : false,
    sourcemap: mode === 'development',
    target: 'es2015',

    // Enable tree shaking
    rollupOptions: {
      ...this.rollupOptions,
      treeshake: {
        preset: 'recommended',
        manualPureFunctions: ['console.log', 'console.info', 'console.debug'],
      },
    },
  },

  // Enhanced plugins
  plugins: [
    // PWA with advanced caching
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['favicon.ico', 'robots.txt', 'apple-touch-icon.png'],
      manifest: {
        name: 'APS Dream Home - Real Estate Platform',
        short_name: 'APSHomes',
        description: 'Find your dream home with APS Dream Home - Professional real estate platform',
        theme_color: '#4f46e5',
        background_color: '#ffffff',
        display: 'standalone',
        start_url: '/',
        icons: [
          {
            src: 'pwa-192x192.png',
            sizes: '192x192',
            type: 'image/png',
          },
          {
            src: 'pwa-512x512.png',
            sizes: '512x512',
            type: 'image/png',
            purpose: 'any maskable',
          },
        ],
      },
      workbox: {
        globPatterns: ['**/*.{js,css,html,php,png,jpg,jpeg,svg,woff,woff2}'],
        runtimeCaching: [
          {
            urlPattern: /^https:\/\/api\./,
            handler: 'NetworkFirst',
            options: {
              cacheName: 'api-cache',
              expiration: {
                maxEntries: 100,
                maxAgeSeconds: 60 * 60 * 24 * 7,
              },
              cacheKeyWillBeUsed: async ({ request }) => {
                return `${request.url}?${new Date().getDate()}`;
              },
            },
          },
          {
            urlPattern: /^https:\/\/fonts\.googleapis\.com/,
            handler: 'CacheFirst',
            options: {
              cacheName: 'google-fonts-cache',
              expiration: {
                maxEntries: 10,
                maxAgeSeconds: 60 * 60 * 24 * 365,
              },
            },
          },
          {
            urlPattern: /^https:\/\/cdn\.jsdelivr\.net/,
            handler: 'CacheFirst',
            options: {
              cacheName: 'cdn-cache',
              expiration: {
                maxEntries: 50,
                maxAgeSeconds: 60 * 60 * 24 * 30,
              },
            },
          },
          {
            urlPattern: /\.(?:png|jpg|jpeg|svg|gif|webp)$/,
            handler: 'CacheFirst',
            options: {
              cacheName: 'images-cache',
              expiration: {
                maxEntries: 200,
                maxAgeSeconds: 60 * 60 * 24 * 30,
              },
            },
          },
        ],
        skipWaiting: true,
        clientsClaim: true,
      },
    }),
  ],

  // Development server with advanced features
  server: {
    port: 3000,
    strictPort: true,
    host: '0.0.0.0',
    open: true,
    hmr: {
      protocol: 'ws',
      host: 'localhost',
    },
    // Enhanced proxy for PHP development
    proxy: {
      '^/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, ''),
        configure: (proxy, _options) => {
          proxy.on('error', (err, _req, _res) => {
            console.log('proxy error', err);
          });
        },
      },
      '^/admin': 'http://localhost:8000',
      '^/uploads': 'http://localhost:8000',
      '^/includes': 'http://localhost:8000',
    },
    // Watch for PHP file changes and reload
    watch: {
      usePolling: true,
      interval: 100,
    },
    // Enable CORS for development
    cors: true,
  },

  // Enhanced resolve configuration
  resolve: {
    alias: {
      '@': resolve(__dirname, 'assets/js'),
      '~': resolve(__dirname, 'assets'),
      '$': resolve(__dirname, 'includes'),
      'bootstrap': resolve(__dirname, 'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js'),
    },
  },

  // Advanced CSS configuration
  css: {
    devSourcemap: mode === 'development',
    preprocessorOptions: {
      scss: {
        additionalData: `@import "./assets/css/variables.scss";`,
      },
      css: {
        additionalData: `:root { --app-version: "${process.env.npm_package_version || '1.0.0'}"; }`,
      },
    },
    postcss: {
      plugins: [
        // Add PostCSS plugins for advanced CSS optimization
      ],
    },
  },

  // Enhanced dependency optimization
  optimizeDeps: {
    include: [
      'bootstrap',
      'jquery',
      'aos',
      'swiper',
      'owl.carousel',
      '@tailwindcss/forms',
    ],
    exclude: [
      // Exclude PHP files from pre-bundling
      '**/*.php',
    ],
  },

  // Advanced asset handling
  assetsInclude: ['**/*.php', '**/*.html'],

  // Define global constants
  define: {
    __APP_VERSION__: JSON.stringify(process.env.npm_package_version || '1.0.0'),
    __BUILD_TIME__: JSON.stringify(new Date().toISOString()),
  },

  // Performance optimizations
  esbuild: {
    drop: mode === 'production' ? ['console', 'debugger'] : [],
    legalComments: 'none',
  },
}));
