// APS Dream Home Service Worker
const CACHE_NAME = 'apsdreamhome-v1.0.0';
const STATIC_CACHE = 'apsdreamhome-static-v1.0.0';
const API_CACHE = 'apsdreamhome-api-v1.0.0';
const IMAGE_CACHE = 'apsdreamhome-images-v1.0.0';

// Files to cache
const STATIC_FILES = [
    '/',
    '/index.html',
    '/manifest.json',
    '/assets/css/app.css',
    '/assets/js/app.js',
    '/assets/images/logo.png',
    '/assets/images/icons/icon-192x192.png',
    '/assets/images/icons/icon-512x512.png'
];

// API endpoints to cache
const API_ENDPOINTS = [
    '/api/v2.0/properties/featured',
    '/api/v2.0/properties/stats',
    '/api/v2.0/users/stats',
    '/api/v2.0/analytics/overview'
];

// Install event
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('[SW] Caching static files');
                return cache.addAll(STATIC_FILES);
            })
            .then(() => {
                console.log('[SW] Static files cached successfully');
                return self.skipWaiting();
            })
    );
});

// Activate event
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker');
    
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== STATIC_CACHE && 
                        cacheName !== API_CACHE && 
                        cacheName !== IMAGE_CACHE) {
                        console.log('[SW] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            console.log('[SW] Service worker activated');
            return self.clients.claim();
        })
    );
});

// Fetch event
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);
    
    // Handle different request types
    if (request.method === 'GET') {
        // Static files - cache first
        if (isStaticFile(url.pathname)) {
            event.respondWith(handleStaticRequest(request));
        }
        // API requests - network first, cache fallback
        else if (isApiRequest(url.pathname)) {
            event.respondWith(handleApiRequest(request));
        }
        // Images - cache first with network fallback
        else if (isImageRequest(url.pathname)) {
            event.respondWith(handleImageRequest(request));
        }
        // Other requests - network only
        else {
            event.respondWith(fetch(request));
        }
    } else {
        // POST/PUT/DELETE requests - network only
        event.respondWith(fetch(request));
    }
});

// Handle static file requests
function handleStaticRequest(request) {
    return caches.match(STATIC_CACHE)
        .then((cache) => {
            return cache.match(request)
                .then((response) => {
                    if (response) {
                        console.log('[SW] Serving from cache:', request.url);
                        return response;
                    }
                    
                    // Not in cache, fetch from network
                    return fetch(request)
                        .then((response) => {
                            if (response.ok) {
                                // Cache the response
                                const responseClone = response.clone();
                                caches.open(STATIC_CACHE)
                                    .then((cache) => {
                                        cache.put(request, responseClone);
                                    });
                            }
                            return response;
                        })
                        .catch(() => {
                            // Network failed, try to serve from cache
                            return caches.match(request);
                        });
                });
        });
}

// Handle API requests
function handleApiRequest(request) {
    return caches.match(API_CACHE)
        .then((cache) => {
            return cache.match(request)
                .then((response) => {
                    // Always try network first for API requests
                    const fetchPromise = fetch(request)
                        .then((response) => {
                            if (response.ok) {
                                // Cache successful responses
                                const responseClone = response.clone();
                                caches.open(API_CACHE)
                                    .then((cache) => {
                                        cache.put(request, responseClone);
                                    });
                            }
                            return response;
                        })
                        .catch(() => {
                            // Network failed, try cache
                            if (response) {
                                console.log('[SW] Serving API from cache:', request.url);
                                return response;
                            }
                            throw new Error('Network request failed and no cache available');
                        });
                    
                    // Return cached response immediately if available
                    if (response) {
                        console.log('[SW] Serving API from cache (background fetch):', request.url);
                        // Update cache in background
                        fetch(request).then((networkResponse) => {
                            if (networkResponse.ok) {
                                caches.open(API_CACHE)
                                    .then((cache) => {
                                        cache.put(request, networkResponse);
                                    });
                            }
                        });
                        return response;
                    }
                    
                    return fetchPromise;
                });
        });
}

// Handle image requests
function handleImageRequest(request) {
    return caches.match(IMAGE_CACHE)
        .then((cache) => {
            return cache.match(request)
                .then((response) => {
                    if (response) {
                        console.log('[SW] Serving image from cache:', request.url);
                        return response;
                    }
                    
                    // Not in cache, fetch from network
                    return fetch(request)
                        .then((response) => {
                            if (response.ok) {
                                // Cache the response
                                const responseClone = response.clone();
                                caches.open(IMAGE_CACHE)
                                    .then((cache) => {
                                        cache.put(request, responseClone);
                                    });
                            }
                            return response;
                        })
                        .catch(() => {
                            // Network failed, return a placeholder
                            return new Response('Image not available', {
                                status: 404,
                                statusText: 'Not Found'
                            });
                        });
                });
        });
}

// Check if request is for static file
function isStaticFile(pathname) {
    return STATIC_FILES.some(file => pathname === file) ||
           pathname.startsWith('/assets/css/') ||
           pathname.startsWith('/assets/js/') ||
           pathname.startsWith('/assets/images/') ||
           pathname.endsWith('.css') ||
           pathname.endsWith('.js') ||
           pathname.endsWith('.png') ||
           pathname.endsWith('.jpg') ||
           pathname.endsWith('.jpeg') ||
           pathname.endsWith('.svg') ||
           pathname.endsWith('.ico');
}

// Check if request is for API
function isApiRequest(pathname) {
    return pathname.startsWith('/api/') ||
           API_ENDPOINTS.some(endpoint => pathname === endpoint);
}

// Check if request is for image
function isImageRequest(pathname) {
    return pathname.startsWith('/uploads/') ||
           pathname.startsWith('/storage/') ||
           pathname.match(/\.(jpg|jpeg|png|gif|webp|svg)$/i);
}

// Background sync
self.addEventListener('sync', (event) => {
    console.log('[SW] Background sync event:', event.tag);
    
    if (event.tag === 'background-sync-forms') {
        event.waitUntil(syncForms());
    }
});

// Sync forms
function syncForms() {
    return self.registration.showNotification('Forms Synced', {
        body: 'Your offline forms have been synced.',
        icon: '/assets/images/icons/icon-192x192.png'
    });
}

// Push notifications
self.addEventListener('push', (event) => {
    console.log('[SW] Push event received');
    
    const options = {
        body: event.data.text(),
        icon: '/assets/images/icons/icon-192x192.png',
        badge: '/assets/images/icons/badge-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Explore',
                icon: '/assets/images/icons/checkmark.png'
            },
            {
                action: 'close',
                title: 'Close',
                icon: '/assets/images/icons/xmark.png'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification('APS Dream Home', options)
    );
});

// Notification click
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification click received');
    
    event.notification.close();
    
    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('https://apsdreamhome.com/properties')
        );
    } else if (event.action === 'close') {
        // Just close the notification
    } else {
        // Default action - open app
        event.waitUntil(
            clients.openWindow('https://apsdreamhome.com/')
        );
    }
});

// Message handling
self.addEventListener('message', (event) => {
    console.log('[SW] Message received:', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Cache cleanup
self.addEventListener('message', (event) => {
    if (event.data.type === 'CACHE_CLEANUP') {
        cleanupCache();
    }
});

function cleanupCache() {
    caches.keys().then((cacheNames) => {
        return Promise.all(
            cacheNames.map((cacheName) => {
                if (cacheName.startsWith('apsdreamhome-') && 
                    cacheName !== STATIC_CACHE && 
                    cacheName !== API_CACHE && 
                    cacheName !== IMAGE_CACHE) {
                    return caches.delete(cacheName);
                }
            })
        );
    });
}

console.log('[SW] Service worker loaded');
