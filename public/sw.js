/**
 * APS Dream Home — Service Worker
 * Handles Web Push notifications (VAPID) + click -> open URL.
 */

self.addEventListener('install', function(event) {
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    event.waitUntil(clients.claim());
});

self.addEventListener('push', function(event) {
    if (!event.data) return;

    var data = {};
    try {
        data = event.data.json();
    } catch (e) {
        data = { title: event.data.text() };
    }

    var title = (data.title || 'APS Dream Home').toString().slice(0, 200);
    var options = {
        body: (data.body || data.message || 'New notification').toString().slice(0, 1000),
        icon: data.icon || '/assets/img/favicon.png',
        badge: data.badge || '/assets/img/favicon.png',
        image: data.image || undefined,
        tag: data.tag || ('aps-' + Date.now()),
        renotify: data.renotify === true,
        requireInteraction: data.requireInteraction === true,
        data: {
            url: data.url || data.action_url || '/',
            notification_id: data.notification_id || null,
            event_type: data.event_type || null
        },
        actions: Array.isArray(data.actions) ? data.actions.slice(0, 2) : [
            { action: 'open', title: 'Open' },
            { action: 'dismiss', title: 'Dismiss' }
        ],
        vibrate: data.vibrate || [100, 50, 100],
        timestamp: Date.now()
    };

    event.waitUntil(
        self.registration.showNotification(title, options).catch(function(err) {
            console.error('[sw] showNotification failed:', err);
        })
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    if (event.action === 'dismiss') return;

    var targetUrl = '/';
    if (event.notification && event.notification.data && event.notification.data.url) {
        targetUrl = event.notification.data.url;
    }

    if (event.action && event.notification && event.notification.data && event.notification.data.actions) {
        var found = event.notification.data.actions.find(function(a) { return a.action === event.action; });
        if (found && found.url) targetUrl = found.url;
    }

    if (!/^https?:\/\//i.test(targetUrl)) {
        targetUrl = new URL(targetUrl, self.location.origin).href;
    }

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(windowClients) {
            for (var i = 0; i < windowClients.length; i++) {
                var client = windowClients[i];
                if ('focus' in client) {
                    try {
                        var clientUrl = new URL(client.url);
                        var target = new URL(targetUrl);
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

self.addEventListener('notificationclose', function(event) {
    // Analytics hook — no-op for now
});

self.addEventListener('message', function(event) {
    if (event.data && event.data.type === 'PING') {
        if (event.source && event.source.postMessage) {
            event.source.postMessage({ type: 'PONG', at: Date.now() });
        }
    }
});
