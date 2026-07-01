<?php
/**
 * Push Notification Subscribe Button (Web Push API + VAPID)
 *
 * Usage in any view (header.php or a layout):
 *   <?php if (!defined('PUSH_SUBSCRIBE_BTN_LOADED')) { define('PUSH_SUBSCRIBE_BTN_LOADED', true); include __DIR__ . '/push_subscribe_button.php'; } ?>
 *
 * Renders a small floating button. On click:
 *   1. Requests notification permission
 *   2. Subscribes via PushManager.subscribe()
 *   3. POSTs the subscription to /push/subscribe
 *
 * The button self-checks Notification.permission and existing
 * subscriptions on load — it shows "Notifications Enabled" when the
 * user has already granted permission + an active subscription.
 *
 * Requires the SW to be registered (header.php registers /sw.js).
 */
if (defined('PUSH_SUBSCRIBE_BTN_LOADED')) {
    return;
}
define('PUSH_SUBSCRIBE_BTN_LOADED', true);
?>
<!-- Web Push Subscribe Button -->
<div id="push-subscribe-wrap" class="push-subscribe-wrap" hidden>
    <button id="push-subscribe-btn"
            type="button"
            class="push-subscribe-btn push-state-default"
            aria-label="htmlspecialchars(__('component_enable_browser_notifications', 'Enable browser notifications'))">
        <i class="fas fa-bell" aria-hidden="true"></i>
        <span class="push-subscribe-label">__('component_enable_browser_notifications_btn', 'Enable Browser Notifications')</span>
    </button>
</div>

<style>
.push-subscribe-wrap {
    position: fixed;
    bottom: 96px;
    right: 24px;
    z-index: 1050;
    max-width: 320px;
}
.push-subscribe-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border: 0;
    border-radius: 999px;
    background: linear-gradient(135deg, #0d9488, #0f766e);
    color: #fff;
    font-weight: 600;
    font-size: 13px;
    box-shadow: 0 8px 24px rgba(79, 70, 229, 0.35);
    cursor: pointer;
    transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
    line-height: 1;
}
.push-subscribe-btn:hover { transform: translateY(-1px); box-shadow: 0 10px 30px rgba(79, 70, 229, 0.45); }
.push-subscribe-btn:active { transform: translateY(0); }
.push-subscribe-btn[disabled] { cursor: progress; opacity: .7; }
.push-subscribe-btn.push-state-granted {
    background: linear-gradient(135deg, #16a34a, #22c55e);
    box-shadow: 0 8px 24px rgba(34, 197, 94, 0.35);
}
.push-subscribe-btn.push-state-denied {
    background: linear-gradient(135deg, #b91c1c, #ef4444);
    box-shadow: 0 8px 24px rgba(239, 68, 68, 0.35);
    cursor: not-allowed;
}
.push-subscribe-btn.push-state-unsupported {
    background: #94a3b8;
    box-shadow: none;
    cursor: not-allowed;
}
.push-subscribe-btn .fa-bell { font-size: 14px; }
@media (max-width: 575.98px) {
    .push-subscribe-wrap { right: 12px; left: 12px; max-width: none; }
    .push-subscribe-btn { width: 100%; justify-content: center; }
}
</style>

<script>
(function () {
    'use strict';

    var wrap = document.getElementById('push-subscribe-wrap');
    var btn  = document.getElementById('push-subscribe-btn');
    if (!wrap || !btn) return;

    var baseUrl = (window.BASE_URL || '').replace(/\/$/, '');

    // Feature detection — don't show on browsers without Push API / SW
    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        wrap.hidden = false;
        btn.classList.add('push-state-unsupported');
        btn.querySelector('.push-subscribe-label').textContent = 'Notifications not supported';
        btn.disabled = true;
        return;
    }

    function setState(state, label) {
        btn.classList.remove(
            'push-state-default', 'push-state-granted', 'push-state-denied', 'push-state-unsupported'
        );
        btn.classList.add('push-state-' + state);
        if (label) btn.querySelector('.push-subscribe-label').textContent = label;
    }

    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var raw = window.atob(base64);
        var out = new Uint8Array(raw.length);
        for (var i = 0; i < raw.length; ++i) out[i] = raw.charCodeAt(i);
        return out;
    }

    function postJSON(path, body) {
        return fetch(baseUrl + path, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(body || {})
        }).then(function (r) {
            var ct = r.headers.get('content-type') || '';
            if (ct.indexOf('application/json') === -1) {
                return { success: false, error: 'Non-JSON response (HTTP ' + r.status + ')' };
            }
            return r.json();
        });
    }

    function getVapidKey() {
        return fetch(baseUrl + '/push/vapid-key', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d && d.success && d.vapid_key) return d.vapid_key;
                throw new Error((d && d.error) || 'No VAPID key');
            });
    }

    function postSubscribe(sub) {
        var endpoint = sub.endpoint;
        var p256dh = btoa(String.fromCharCode.apply(null, new Uint8Array(sub.getKey('p256dh'))));
        var auth   = btoa(String.fromCharCode.apply(null, new Uint8Array(sub.getKey('auth'))));
        // re-encode as base64url (the PHP side decodes both forms, but keep it strict)
        p256dh = p256dh.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        auth   = auth.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        return postJSON('/push/subscribe', { endpoint: endpoint, keys: { p256dh: p256dh, auth: auth } });
    }

    function postUnsubscribe(endpoint) {
        return postJSON('/push/unsubscribe', { endpoint: endpoint });
    }

    function showInitialState(perm) {
        if (perm === 'granted') {
            setState('granted', 'Notifications Enabled');
        } else if (perm === 'denied') {
            setState('denied', 'Notifications Blocked');
        } else {
            setState('default', 'Enable Browser Notifications');
        }
        wrap.hidden = false;
    }

    function enable() {
        btn.disabled = true;
        setState('default', 'Requesting…');

        Notification.requestPermission().then(function (perm) {
            if (perm !== 'granted') {
                showInitialState(perm);
                return null;
            }
            return navigator.serviceWorker.ready;
        }).then(function (reg) {
            if (!reg) return null;
            return getVapidKey().then(function (key) {
                return reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(key)
                });
            });
        }).then(function (sub) {
            if (!sub) return null;
            return postSubscribe(sub).then(function (res) {
                if (res && res.success) {
                    setState('granted', 'Notifications Enabled');
                } else {
                    setState('default', 'Enable Browser Notifications');
                    console.warn('[push] subscribe failed:', res);
                }
            });
        }).catch(function (err) {
            console.error('[push] enable error:', err);
            setState('default', 'Enable Browser Notifications');
        }).then(function () {
            btn.disabled = false;
        });
    }

    btn.addEventListener('click', function () {
        if (Notification.permission === 'granted') {
            // Already granted — send a test push via server
            btn.disabled = true;
            setState('default', 'Sending test…');
            postJSON('/push/test', {
                title: 'APS Dream Home',
                body: 'Browser notifications are working!',
                url: '/'
            }).then(function (res) {
                if (res && res.success) {
                    setState('granted', 'Test sent!');
                    setTimeout(function () { setState('granted', 'Notifications Enabled'); }, 2500);
                } else {
                    setState('granted', 'Notifications Enabled');
                    console.warn('[push] test failed:', res);
                }
            }).catch(function (err) {
                console.error('[push] test error:', err);
                setState('granted', 'Notifications Enabled');
            }).then(function () { btn.disabled = false; });
        } else if (Notification.permission === 'denied') {
            setState('denied', 'Notifications Blocked — change in browser settings');
        } else {
            enable();
        }
    });

    // Show after a tick so it doesn't fight with above-the-fold content
    setTimeout(function () { showInitialState(Notification.permission); }, 600);
})();
</script>
