var PushNotifications = {
    swRegistration: null,
    isSubscribed: false,

    init: function() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.log('[Push] Not supported');
            return;
        }

        navigator.serviceWorker.register('/sw.js')
            .then(function(reg) {
                PushNotifications.swRegistration = reg;
                PushNotifications.checkSubscription();
            })
            .catch(function(err) {
                console.error('[Push] SW registration failed:', err);
            });
    },

    checkSubscription: function() {
        if (!PushNotifications.swRegistration) return;
        PushNotifications.swRegistration.pushManager.getSubscription()
            .then(function(subscription) {
                PushNotifications.isSubscribed = subscription !== null;
                PushNotifications.updateUI();
            });
    },

    subscribe: function() {
        if (!PushNotifications.swRegistration) {
            console.log('[Push] Service worker not ready');
            return;
        }

        Notification.requestPermission().then(function(permission) {
            if (permission !== 'granted') return;

            fetch('/api/push/vapid-key')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.publicKey) {
                        console.log('[Push] VAPID key not configured');
                        return;
                    }
                    var key = PushNotifications.urlBase64ToUint8Array(data.publicKey);
                    return PushNotifications.swRegistration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: key
                    });
                })
                .then(function(subscription) {
                    if (!subscription) return;
                    var body = {
                        endpoint: subscription.endpoint,
                        keys: {
                            p256dh: PushNotifications.arrayBufferToBase64(subscription.getKey('p256dh')),
                            auth: PushNotifications.arrayBufferToBase64(subscription.getKey('auth'))
                        }
                    };
                    return fetch('/api/push/subscribe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': PushNotifications.getCsrfToken()
                        },
                        body: JSON.stringify(body)
                    });
                })
                .then(function(r) { return r ? r.json() : null; })
                .then(function(data) {
                    if (data && data.success) {
                        PushNotifications.isSubscribed = true;
                        PushNotifications.updateUI();
                    }
                })
                .catch(function(err) {
                    console.error('[Push] Subscribe error:', err);
                });
        });
    },

    unsubscribe: function() {
        PushNotifications.swRegistration.pushManager.getSubscription()
            .then(function(subscription) {
                if (!subscription) return;
                var endpoint = subscription.endpoint;
                return subscription.unsubscribe().then(function() {
                    return fetch('/api/push/unsubscribe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': PushNotifications.getCsrfToken()
                        },
                        body: JSON.stringify({ endpoint: endpoint })
                    });
                });
            })
            .then(function() {
                PushNotifications.isSubscribed = false;
                PushNotifications.updateUI();
            });
    },

    toggle: function() {
        if (PushNotifications.isSubscribed) {
            PushNotifications.unsubscribe();
        } else {
            PushNotifications.subscribe();
        }
    },

    updateUI: function() {
        var btn = document.getElementById('push-toggle');
        if (btn) {
            if (PushNotifications.isSubscribed) {
                btn.textContent = 'Disable Notifications';
                btn.className = 'btn btn-sm btn-outline-danger w-100';
                btn.onclick = function() { PushNotifications.unsubscribe(); };
            } else {
                btn.textContent = 'Enable Notifications';
                btn.className = 'btn btn-sm btn-primary w-100';
                btn.onclick = function() { PushNotifications.subscribe(); };
            }
        }
    },

    getCsrfToken: function() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        var input = document.querySelector('input[name="csrf_token"]');
        if (input) return input.value;
        return '';
    },

    urlBase64ToUint8Array: function(base64String) {
        var padding = '='.repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var rawData = window.atob(base64);
        var outputArray = new Uint8Array(rawData.length);
        for (var i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    },

    arrayBufferToBase64: function(buffer) {
        var bytes = new Uint8Array(buffer);
        var binary = '';
        for (var i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    PushNotifications.init();
});
