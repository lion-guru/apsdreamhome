/**
 * APS Dream Home — Service Worker
 * Handles Web Push notifications (VAPID) + click → open URL.
 * Registered from /public/sw.js so it's served from the site root (required for
 * maximum scope; the page must call navigator.serviceWorker.register('/sw.js')).
 */

self.addEventListener('push', function (event) {
    let data = {};
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = { title: event.data.text() };
        }
    }

    const title = (data.title || 'APS Dream Home').toString().slice(0, 200);
    const options = {
        body: (data.body || data.message || 'New notification').toString().slice(0, 1000),
        icon: data.icon || '/assets/images/icons/icon-192x192.png',
        badge: data.badge || '/assets/images/icons/badge-72x72.png',
        image: data.image || undefined,
        tag: data.tag || ('aps-' + Date.now()),
        renotify: data.renotify === true,
        requireInteraction: data.requireInteraction === true,
        data: {
            url: data.url || data.action_url || '/',
            notification_id: data.notification_id || null,
            event_type: data.event_type || null,
        },
        actions: Array.isArray(data.actions) ? data.actions.slice(0, 2) : [],
        vibrate: data.vibrate || [100, 50, 100],
        timestamp: Date.now(),
    };

    event.waitUntil(
        self.registration.showNotification(title, options).catch(function (err) {
            console.error('[sw] showNotification failed:', err);
        })
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    let targetUrl = '/';
    if (event.notification && event.notification.data && event.notification.data.url) {
        targetUrl = event.notification.data.url;
    }

    if (event.action) {
        if (event.notification.data && event.notification.data.actions) {
            const found = event.notification.data.actions.find(function (a) { return a.action === event.action; });
            if (found && found.url) targetUrl = found.url;
        }
    }

    if (!/^https?:\/\//i.test(targetUrl)) {
        targetUrl = new URL(targetUrl, self.location.origin).href;
    }

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (windowClients) {
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if ('focus' in client) {
                    try {
                        const clientUrl = new URL(client.url);
                        const target = new URL(targetUrl);
                        if (clientUrl.origin === target.origin) {
                            client.navigate(targetUrl);
                            return client.focus();
                        }
                    } catch (e) { /* ignore */ }
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

self.addEventListener('notificationclose', function (event) {
    // Hook for analytics — no-op for now
});

self.addEventListener('install', function (event) {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        Promise.all([
            clients.claim(),
            // Clean up old caches if any (none yet, but defensive)
            caches.keys().then(function (names) {
                return Promise.all(
                    names.filter(function (n) { return n.indexOf('aps-v') !== 0; })
                        .map(function (n) { return caches.delete(n); })
                );
            })
        ])
    );
});

// Optional: handle simple message-based test push from page
self.addEventListener('message', function (event) {
    if (event.data && event.data.type === 'PING') {
        if (event.source && event.source.postMessage) {
            event.source.postMessage({ type: 'PONG', at: Date.now() });
        }
    }
});
