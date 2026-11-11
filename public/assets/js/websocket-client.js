/**
 * WebSocket Client for Real-time HR Dashboard Updates
 * Connects to the WebSocket server for real-time notifications and updates
 * 
 * Include in layouts where real-time updates are needed
 * 
 * Usage:
 *   <script src="<?= BASE_URL ?>/assets/js/websocket-client.js"></script>
 *   <script>
 *     HRWebSocket.init({
 *       onNotification: function(notification) { ... },
 *       onUnreadCount: function(count) { ... }
 *     });
 *   </script>
 */

(function() {
    'use strict';

    // Configuration
    var WS_HOST = window.WS_HOST || (window.location.protocol === 'https:' ? 'wss://' : 'ws://') + window.location.host.replace(/:\d+/, '') + ':8080';
    var WS_PATH = '/hr';
    var RECONNECT_INTERVAL = 5000; // 5 seconds
    var MAX_RECONNECT_ATTEMPTS = 10;
    var PING_INTERVAL = 30000; // 30 seconds

    var ws = null;
    var reconnectAttempts = 0;
    var reconnectTimer = null;
    var pingTimer = null;
    var isAuthenticated = false;
    var currentUserId = null;
    var callbacks = {
        onNotification: null,
        onUnreadCount: null,
        onConnected: null,
        onDisconnected: null,
        onError: null,
        onNotificationRead: null
    };

    // Authentication token
    var authToken = null;
    var userType = 'employee'; // employee, admin, associate, customer, agent

    // State
    var isConnected = false;
    var isAuthenticated = false;
    var reconnectAttempts = 0;

    var HRWebSocket = {
        init: function(options) {
            options = options || {};
            
            // Set callbacks
            if (options.onNotification) callbacks.onNotification = options.onNotification;
            if (options.onUnreadCount) callbacks.onUnreadCount = options.onUnreadCount;
            if (options.onConnected) callbacks.onConnected = options.onConnected;
            if (options.onDisconnected) callbacks.onDisconnected = options.onDisconnected;
            if (options.onError) callbacks.onError = options.onError;
            if (options.onNotificationRead) callbacks.onNotificationRead = options.onNotificationRead;

            // Get auth token from session
            if (typeof BASE_URL !== 'undefined') {
                // Try to get token from meta tag or session
                var tokenMeta = document.querySelector('meta[name="csrf-token"]');
                if (tokenMeta) {
                    authToken = tokenMeta.getAttribute('content');
                }
            }

            // Also check for session-based auth
            if (typeof USER_TOKEN !== 'undefined') {
                authToken = USER_TOKEN;
            }

            // Determine user type from page context
            if (document.body.classList.contains('admin-layout')) {
                userType = 'admin';
            } else if (document.body.classList.contains('associate-layout')) {
                userType = 'associate';
            } else if (document.body.classList.contains('customer-layout') || document.body.classList.contains('customer-dashboard')) {
                userType = 'customer';
            } else if (document.body.classList.contains('agent-layout')) {
                userType = 'agent';
            } else if (document.body.classList.contains('employee-layout')) {
                userType = 'employee';
            }

            // Connect
            connect();

            // Return public API
            return {
                send: send,
                subscribe: subscribe,
                unsubscribe: unsubscribe,
                getNotifications: getNotifications,
                markRead: markNotificationRead,
                markAllRead: markAllRead,
                disconnect: disconnect,
                isConnected: function() { return isConnected && isAuthenticated; },
                setToken: function(token, type) {
                    authToken = token;
                    userType = type || 'employee';
                    if (isConnected) {
                        authenticate();
                    }
                }
            };
        },

        send: function(data) {
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify(data));
                return true;
            }
            return false;
        },

        subscribe: function(channel) {
            send({ type: 'subscribe', channel: channel });
        },

        unsubscribe: function(channel) {
            send({ type: 'unsubscribe', channel: channel });
        },

        getNotifications: function(limit, offset) {
            send({
                type: 'get_notifications',
                limit: limit || 20,
                offset: offset || 0
            });
        },

        markRead: function(notificationId) {
            send({
                type: 'mark_read',
                notification_id: notificationId
            });
        },

        markAllRead: function() {
            send({ type: 'mark_read' });
        },

        disconnect: function() {
            if (ws) {
                ws.close();
                ws = null;
            }
            clearTimers();
        }
    };

    // Private functions
    function connect() {
        if (ws && (ws.readyState === WebSocket.OPEN || ws.readyState === WebSocket.CONNECTING)) {
            return;
        }

        var url = WS_HOST + WS_PATH;
        console.log('[HRWebSocket] Connecting to ' + url);

        try {
            ws = new WebSocket(url);
            ws.binaryType = 'arraybuffer';

            ws.onopen = onOpen;
            ws.onmessage = onMessage;
            ws.onclose = onClose;
            ws.onerror = onError;
        } catch (e) {
            console.error('[HRWebSocket] Connection error:', e);
            scheduleReconnect();
        }
    }

    function onOpen(event) {
        console.log('[HRWebSocket] Connected');
        isConnected = true;
        reconnectAttempts = 0;
        clearTimeout(reconnectTimer);

        // Send authentication
        authenticate();

        // Start ping interval
        startPingInterval();

        // Fire connected callback
        if (callbacks.onConnected) {
            callbacks.onConnected();
        }
    }

    function onMessage(event) {
        try {
            var data = JSON.parse(event.data);
            handleMessage(data);
        } catch (e) {
            console.error('[HRWebSocket] Failed to parse message:', e);
        }
    }

    function onClose(event) {
        console.log('[HRWebSocket] Disconnected:', event.code, event.reason);
        isConnected = false;
        isAuthenticated = false;
        clearTimers();

        if (callbacks.onDisconnected) {
            callbacks.onDisconnected();
        }

        // Schedule reconnect
        scheduleReconnect();
    }

    function onError(event) {
        console.error('[HRWebSocket] Error:', event);
        if (callbacks.onError) {
            callbacks.onError(event);
        }
    }

    function handleMessage(data) {
        switch (data.type) {
            case 'welcome':
                console.log('[HRWebSocket] Welcome:', data.message);
                break;

            case 'auth_result':
                if (data.success) {
                    isAuthenticated = true;
                    console.log('[HRWebSocket] Authenticated:', data.user);
                    // Request initial notifications and unread count
                    send({ type: 'get_notifications', limit: 20 });
                } else {
                    console.error('[HRWebSocket] Authentication failed:', data.message);
                    if (callbacks.onError) callbacks.onError({ message: data.message });
                }
                break;

            case 'notification':
                console.log('[HRWebSocket] Notification received:', data.notification);
                if (callbacks.onNotification) {
                    callbacks.onNotification(data.notification);
                }
                // Update unread count
                if (callbacks.onUnreadCount) {
                    // Request updated count
                    send({ type: 'get_unread_count' });
                }
                break;

            case 'unread_count':
                if (callbacks.onUnreadCount) {
                    callbacks.onUnreadCount(data.count);
                }
                break;

            case 'notifications':
                console.log('[HRWebSocket] Notifications received:', data.notifications.length);
                if (callbacks.onNotification) {
                    data.notifications.forEach(function(n) {
                        callbacks.onNotification(n);
                    });
                }
                if (callbacks.onUnreadCount) {
                    callbacks.onUnreadCount(data.unread_count);
                }
                break;

            case 'notifications_read':
                if (callbacks.onNotificationRead) {
                    callbacks.onNotificationRead(data.notification_id);
                }
                break;

            case 'marked_read':
                console.log('[HRWebSocket] Marked read:', data.notification_id);
                break;

            case 'notifications_read':
                // Response to get_notifications
                break;

            case 'subscribed':
                console.log('[HRWebSocket] Subscribed to channel:', data.channel);
                break;

            case 'unsubscribed':
                console.log('[HRWebSocket] Unsubscribed from channel:', data.channel);
                break;

            case 'pong':
                // Ping response
                break;

            case 'error':
                console.error('[HRWebSocket] Server error:', data.message);
                if (callbacks.onError) callbacks.onError({ message: data.message });
                break;

            default:
                console.log('[HRWebSocket] Unknown message type:', data.type, data);
        }
    }

    function authenticate() {
        if (!authToken) {
            console.warn('[HRWebSocket] No auth token available');
            // Try to get from cookie or session
            var token = getCookie('csrf_token') || getCookie('auth_token');
            if (token) {
                authToken = token;
            }
        }

        if (authToken) {
            send({
                type: 'authenticate',
                token: authToken,
                user_type: userType
            });
        } else {
            console.warn('[HRWebSocket] No auth token available for authentication');
        }
    }

    function send(data) {
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(data));
            return true;
        }
        return false;
    }

    function sendNotificationRead(notificationId) {
        send({
            type: 'mark_read',
            notification_id: notificationId
        });
    }

    function markNotificationRead(notificationId) {
        send({
            type: 'mark_read',
            notification_id: notificationId
        });
    }

    function markAllRead() {
        send({ type: 'mark_read' });
    }

    function getNotifications(limit, offset) {
        send({
            type: 'get_notifications',
            limit: limit || 20,
            offset: offset || 0
        });
    }

    function subscribe(channel) {
        send({ type: 'subscribe', channel: channel });
    }

    function unsubscribe(channel) {
        send({ type: 'unsubscribe', channel: channel });
    }

    function disconnect() {
        if (ws) {
            ws.close();
            ws = null;
        }
        clearTimers();
    }

    function startPingInterval() {
        clearInterval(pingTimer);
        pingTimer = setInterval(function() {
            if (ws && ws.readyState === WebSocket.OPEN) {
                send({ type: 'ping' });
            }
        }, 30000); // 30 seconds
    }

    function clearTimers() {
        clearInterval(pingTimer);
        clearTimeout(reconnectTimer);
        pingTimer = null;
        reconnectTimer = null;
    }

    function scheduleReconnect() {
        if (reconnectAttempts >= 10) {
            console.error('[HRWebSocket] Max reconnect attempts reached');
            return;
        }

        clearTimeout(reconnectTimer);
        reconnectAttempts++;
        var delay = Math.min(1000 * Math.pow(2, reconnectAttempts), 30000); // Exponential backoff, max 30s
        
        console.log('[HRWebSocket] Reconnecting in ' + delay + 'ms (attempt ' + reconnectAttempts + ')');
        
        reconnectTimer = setTimeout(function() {
            connect();
        }, delay);
    }

    function clearTimers() {
        clearInterval(pingTimer);
        clearTimeout(reconnectTimer);
        pingTimer = null;
        reconnectTimer = null;
    }

    // Helper functions
    function getCookie(name) {
        var value = '; ' + document.cookie;
        var parts = value.split('; ' + name + '=');
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    // Expose globally
    window.HRWebSocket = HRWebSocket;

    // Auto-initialize if data-auto-init attribute is present
    document.addEventListener('DOMContentLoaded', function() {
        var autoInit = document.querySelector('[data-hr-websocket-auto-init]');
        if (autoInit) {
            var options = {};
            try {
                var config = autoInit.getAttribute('data-hr-websocket-config');
                if (config) {
                    options = JSON.parse(config);
                }
            } catch (e) {
                console.warn('[HRWebSocket] Invalid config:', e);
            }
            HRWebSocket.init(options);
        }
    });

})();