const CACHE_NAME = 'apsdreamhome-v3';
const STATIC_ASSETS = [
    '/apsdreamhome/',
    '/apsdreamhome/assets/css/bootstrap.min.css',
    '/apsdreamhome/assets/js/bootstrap.bundle.min.js',
    '/apsdreamhome/assets/fonts/fontawesome/css/all.min.css'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Fetch event — cache ONLY static assets. Documents and API responses
// always go to network: caching them serves stale HTML/JSON (and stale
// CSP headers), which silently undeploys server-side fixes for visitors.
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    let isStatic = false;
    try {
        const url = new URL(event.request.url);
        isStatic = /\.(css|js|png|jpe?g|gif|svg|webp|ico|woff2?|ttf|eot|otf)$/i.test(url.pathname)
            || STATIC_ASSETS.indexOf(url.pathname) !== -1;
    } catch (e) {
        return;
    }
    if (!isStatic) return;

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                // Return cached version, but also update cache in background
                event.waitUntil(
                    fetch(event.request).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(event.request, networkResponse.clone());
                            });
                        }
                    })
                );
                return cachedResponse;
            }

            // Not in cache - fetch from network
            return fetch(event.request).then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200) {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return networkResponse;
            }).catch(() => {
                // Offline fallback
                if (event.request.destination === 'document') {
                    return caches.match('/apsdreamhome/');
                }
            });
        })
    );
});
