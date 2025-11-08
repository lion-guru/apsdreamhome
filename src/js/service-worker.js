/**
 * Service Worker for APS Dream Homes Website
 * Handles caching and offline functionality
 */

const CACHE_NAME = 'aps-dream-homes-cache-v1';
const ASSETS_TO_CACHE = [
  '/march2025apssite/',
  '/march2025apssite/index.php',
  '/march2025apssite/css/optimized.css',
  '/march2025apssite/assets/css/bootstrap.min.css',
  '/march2025apssite/assets/css/font-awesome.min.css',
  '/march2025apssite/assets/css/style.css',
  '/march2025apssite/assets/js/jquery.min.js',
  '/march2025apssite/assets/js/bootstrap.min.js',
  '/march2025apssite/assets/js/custom.js',
  '/march2025apssite/assets/images/logo/aps.png',
  '/march2025apssite/assets/images/logo/apsico.ico'
];

// Install event - cache assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(ASSETS_TO_CACHE);
      })
      .then(() => self.skipWaiting())
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch event - serve from cache or network
self.addEventListener('fetch', event => {
  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }
  
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }
  
  // Skip PHP API requests
  if (event.request.url.includes('.php?') || event.request.url.includes('/api/')) {
    return;
  }
  
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Return cached response if found
        if (response) {
          return response;
        }
        
        // Clone the request
        const fetchRequest = event.request.clone();
        
        // Make network request
        return fetch(fetchRequest).then(response => {
          // Check if valid response
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }
          
          // Clone the response
          const responseToCache = response.clone();
          
          // Cache the response
          caches.open(CACHE_NAME)
            .then(cache => {
              cache.put(event.request, responseToCache);
            });
          
          return response;
        });
      })
      .catch(() => {
        // Fallback for offline pages
        if (event.request.url.includes('.html') || event.request.url.endsWith('/')) {
          return caches.match('/march2025apssite/index.php');
        }
      })
  );
});