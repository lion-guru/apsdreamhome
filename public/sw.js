
// Service Worker for PWA
const CACHE_NAME = 'apsdreamhome-v1';
const urlsToCache = [
    '/',
    '/index.php',
    '/public/assets/css/style.css',
    '/public/assets/js/main.js',
    '/public/assets/images/logo.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Cache hit - return response
                if (response) {
                    return response;
                }
                return fetch(event.request);
            })
    );
});

// Background sync for offline functionality
self.addEventListener('sync', (event) => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

function doBackgroundSync() {
    // Implement background sync logic
    return Promise.resolve();
}

// Push notifications
self.addEventListener('push', (event) => {
    const options = {
        body: event.data.text(),
        icon: '/public/assets/images/logo.png',
        badge: '/public/assets/images/badge.png'
    };
    
    event.waitUntil(
        self.registration.showNotification('APS Dream Home', options)
    );
});
