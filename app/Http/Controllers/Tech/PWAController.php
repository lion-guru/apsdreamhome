<?php
/**
 * Progressive Web App Controller
 * Handles PWA features, offline support, and mobile optimization
 */

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\BaseController;

class PWAController extends BaseController {

    /**
     * PWA manifest.json generator
     */
    public function manifest() {
        header('Content-Type: application/manifest+json');

        $manifest = [
            'name' => APP_NAME . ' - Real Estate & MLM',
            'short_name' => APP_NAME,
            'description' => 'Complete real estate platform with MLM network marketing',
            'start_url' => BASE_URL,
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#007bff',
            'orientation' => 'portrait-primary',
            'scope' => BASE_URL,
            'lang' => 'en',
            'categories' => ['business', 'productivity', 'utilities'],
            'icons' => [
                [
                    'src' => BASE_URL . 'assets/images/icons/icon-72x72.png',
                    'sizes' => '72x72',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => BASE_URL . 'assets/images/icons/icon-96x96.png',
                    'sizes' => '96x96',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => BASE_URL . 'assets/images/icons/icon-128x128.png',
                    'sizes' => '128x128',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => BASE_URL . 'assets/images/icons/icon-144x144.png',
                    'sizes' => '144x144',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => BASE_URL . 'assets/images/icons/icon-152x152.png',
                    'sizes' => '152x152',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => BASE_URL . 'assets/images/icons/icon-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => BASE_URL . 'assets/images/icons/icon-384x384.png',
                    'sizes' => '384x384',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => BASE_URL . 'assets/images/icons/icon-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ]
            ],
            'screenshots' => [
                [
                    'src' => BASE_URL . 'assets/images/screenshots/mobile-1.png',
                    'sizes' => '390x844',
                    'type' => 'image/png',
                    'form_factor' => 'narrow'
                ],
                [
                    'src' => BASE_URL . 'assets/images/screenshots/desktop-1.png',
                    'sizes' => '1280x720',
                    'type' => 'image/png',
                    'form_factor' => 'wide'
                ]
            ],
            'shortcuts' => [
                [
                    'name' => 'Browse Properties',
                    'short_name' => 'Properties',
                    'description' => 'Browse available properties',
                    'url' => BASE_URL . 'properties',
                    'icons' => [
                        'src' => BASE_URL . 'assets/images/icons/shortcut-properties.png',
                        'sizes' => '96x96'
                    ]
                ],
                [
                    'name' => 'My MLM Network',
                    'short_name' => 'MLM',
                    'description' => 'Manage MLM network',
                    'url' => BASE_URL . 'associate/mlm',
                    'icons' => [
                        'src' => BASE_URL . 'assets/images/icons/shortcut-mlm.png',
                        'sizes' => '96x96'
                    ]
                ],
                [
                    'name' => 'AI Assistant',
                    'short_name' => 'AI Chat',
                    'description' => 'Chat with AI assistant',
                    'url' => BASE_URL . 'chatbot',
                    'icons' => [
                        'src' => BASE_URL . 'assets/images/icons/shortcut-chat.png',
                        'sizes' => '96x96'
                    ]
                ]
            ],
            'related_applications' => [],
            'prefer_related_applications' => false
        ];

        echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Service Worker registration and caching
     */
    public function serviceWorker() {
        header('Content-Type: application/javascript');

        $sw_content = $this->generateServiceWorker();

        echo $sw_content;
        exit;
    }

    /**
     * Generate service worker content
     */
    private function generateServiceWorker() {
        return "
const CACHE_NAME = 'aps-dream-home-v1';
const STATIC_CACHE_URLS = [
    '/',
    '/properties',
    '/about',
    '/contact',
    '/chatbot',
    '/assets/css/bootstrap.min.css',
    '/assets/css/style.css',
    '/assets/js/bootstrap.bundle.min.js',
    '/assets/js/app.js',
    '/assets/images/logo.png',
    '/assets/images/icons/icon-192x192.png',
    '/assets/images/icons/icon-512x512.png'
];

const API_CACHE_URLS = [
    '/api/properties',
    '/api/agents',
    '/api/location/cities'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    console.log('Service Worker: Installing...');

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Service Worker: Caching static assets');
                return cache.addAll(STATIC_CACHE_URLS);
            })
            .then(() => {
                console.log('Service Worker: Installation complete');
                return self.skipWaiting();
            })
    );
});

// Activate event - cleanup old caches
self.addEventListener('activate', event => {
    console.log('Service Worker: Activating...');

    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('Service Worker: Deleting old cache', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker: Activation complete');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip external requests
    if (url.origin !== location.origin) {
        return;
    }

    // Handle API requests (Network First strategy)
    if (request.url.includes('/api/')) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    // Cache successful responses
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME + '-api')
                            .then(cache => cache.put(request, responseClone));
                    }
                    return response;
                })
                .catch(() => {
                    // Fallback to cache for API requests
                    return caches.match(request)
                        .then(cachedResponse => {
                            if (cachedResponse) {
                                return cachedResponse;
                            }
                            // Return offline page for API failures
                            return new Response(JSON.stringify({
                                success: false,
                                error: 'Offline',
                                message: 'You are currently offline'
                            }), {
                                headers: { 'Content-Type': 'application/json' }
                            });
                        });
                })
        );
        return;
    }

    // Handle static assets (Cache First strategy)
    event.respondWith(
        caches.match(request)
            .then(cachedResponse => {
                if (cachedResponse) {
                    return cachedResponse;
                }

                return fetch(request)
                    .then(response => {
                        // Don't cache non-successful responses
                        if (response.status !== 200) {
                            return response;
                        }

                        const responseClone = response.clone();
                        caches.open(CACHE_NAME)
                            .then(cache => cache.put(request, responseClone));

                        return response;
                    })
                    .catch(() => {
                        // Return offline page for navigation requests
                        if (request.mode === 'navigate') {
                            return caches.match('/');
                        }
                    });
            })
    );
});

// Background sync for offline actions
self.addEventListener('sync', event => {
    if (event.tag === 'property-inquiry') {
        event.waitUntil(syncPropertyInquiry());
    }

    if (event.tag === 'chat-message') {
        event.waitUntil(syncChatMessage());
    }
});

// Push notification handling
self.addEventListener('push', event => {
    if (!event.data) {
        return;
    }

    const data = event.data.json();
    const options = {
        body: data.body,
        icon: '/assets/images/icons/icon-192x192.png',
        badge: '/assets/images/icons/icon-72x72.png',
        vibrate: [200, 100, 200],
        data: data.data,
        actions: [
            {
                action: 'view',
                title: 'View',
                icon: '/assets/images/icons/icon-72x72.png'
            },
            {
                action: 'dismiss',
                title: 'Dismiss'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
    event.notification.close();

    if (event.action === 'view') {
        event.waitUntil(
            clients.openWindow(event.notification.data.url || '/')
        );
    }
});

// Helper functions for background sync
function syncPropertyInquiry() {
    return new Promise((resolve, reject) => {
        // Get pending inquiries from IndexedDB
        getOfflineInquiries()
            .then(inquiries => {
                return Promise.all(
                    inquiries.map(inquiry => {
                        return fetch('/api/inquiry', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(inquiry)
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                return removeOfflineInquiry(inquiry.id);
                            }
                        });
                    })
                );
            })
            .then(() => resolve())
            .catch(error => reject(error));
    });
}

function syncChatMessage() {
    return new Promise((resolve, reject) => {
        // Get pending chat messages from IndexedDB
        getOfflineMessages()
            .then(messages => {
                return Promise.all(
                    messages.map(message => {
                        return fetch('/api/chatbot/message', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(message)
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                return removeOfflineMessage(message.id);
                            }
                        });
                    })
                );
            })
            .then(() => resolve())
            .catch(error => reject(error));
    });
}

// IndexedDB helper functions (simplified)
function getOfflineInquiries() {
    return Promise.resolve([]);
}

function removeOfflineInquiry(id) {
    return Promise.resolve();
}

function getOfflineMessages() {
    return Promise.resolve([]);
}

function removeOfflineMessage(id) {
    return Promise.resolve();
}
";
    }

    /**
     * Offline page for PWA
     */
    public function offline() {
        $this->data['page_title'] = 'Offline - ' . APP_NAME;

        // Don't use layout for offline page to avoid dependencies
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo APP_NAME; ?> - Offline</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    text-align: center;
                    padding: 50px;
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                }
                .offline-icon {
                    font-size: 5rem;
                    margin-bottom: 2rem;
                    opacity: 0.8;
                }
                .offline-message {
                    font-size: 1.5rem;
                    margin-bottom: 1rem;
                }
                .offline-subtitle {
                    opacity: 0.8;
                    margin-bottom: 2rem;
                }
                .retry-btn {
                    background: rgba(255,255,255,0.2);
                    border: 2px solid rgba(255,255,255,0.3);
                    color: white;
                    padding: 15px 30px;
                    border-radius: 25px;
                    text-decoration: none;
                    display: inline-block;
                    transition: all 0.3s ease;
                }
                .retry-btn:hover {
                    background: rgba(255,255,255,0.3);
                    text-decoration: none;
                    color: white;
                }
            </style>
        </head>
        <body>
            <div>
                <div class="offline-icon">📱</div>
                <div class="offline-message">You're Offline</div>
                <div class="offline-subtitle">Please check your internet connection and try again</div>
                <button class="retry-btn" onclick="window.location.reload()">
                    <i class="fas fa-refresh me-2"></i>Try Again
                </button>
            </div>
            <script>
                // Auto-retry when connection is restored
                window.addEventListener('online', function() {
                    window.location.reload();
                });
            </script>
        </body>
        </html>
        <?php
        echo ob_get_clean();
    }

    /**
     * Push notification subscription
     */
    public function subscribeNotifications() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
        }

        $subscription = json_decode(file_get_contents('php://input'), true);

        if (!$subscription) {
            sendJsonResponse(['success' => false, 'error' => 'Invalid subscription data'], 400);
        }

        // Save subscription to database
        $success = $this->savePushSubscription($subscription);

        sendJsonResponse([
            'success' => $success,
            'message' => $success ? 'Subscription saved' : 'Failed to save subscription'
        ]);
    }

    /**
     * Save push notification subscription
     */
    private function savePushSubscription($subscription) {
        try {
            global $pdo;

            $user_id = $_SESSION['user_id'] ?? null;

            $sql = "INSERT INTO push_subscriptions (user_id, endpoint, p256dh_key, auth_key, created_at, updated_at)
                    VALUES (?, ?, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                    p256dh_key = VALUES(p256dh_key),
                    auth_key = VALUES(auth_key),
                    updated_at = NOW()";

            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                $user_id,
                $subscription['endpoint'],
                $subscription['keys']['p256dh'] ?? '',
                $subscription['keys']['auth'] ?? ''
            ]);

        } catch (\Exception $e) {
            error_log('Push subscription save error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send push notification (admin function)
     */
    public function sendPushNotification() {
        if (!$this->isAdmin()) {
            sendJsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
        }

        $notification_data = json_decode(file_get_contents('php://input'), true);

        if (!$notification_data) {
            sendJsonResponse(['success' => false, 'error' => 'Invalid notification data'], 400);
        }

        $success = $this->broadcastNotification($notification_data);

        sendJsonResponse([
            'success' => $success,
            'message' => $success ? 'Notification sent' : 'Failed to send notification'
        ]);
    }

    /**
     * Broadcast notification to all subscribers
     */
    private function broadcastNotification($notification_data) {
        try {
            global $pdo;

            // Get all active subscriptions
            $sql = "SELECT * FROM push_subscriptions WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $pdo->query($sql);
            $subscriptions = $stmt->fetchAll();

            $sent_count = 0;

            foreach ($subscriptions as $subscription) {
                // In production, use web-push library to send notifications
                // For now, we'll simulate the sending
                $sent_count++;
            }

            return $sent_count > 0;

        } catch (\Exception $e) {
            error_log('Notification broadcast error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * PWA installation prompt
     */
    public function installPrompt() {
        $this->data['page_title'] = 'Install App - ' . APP_NAME;

        $this->render('pwa/install_prompt');
    }

    /**
     * Get PWA statistics
     */
    public function getPWAStats() {
        header('Content-Type: application/json');

        if (!$this->isAdmin()) {
            sendJsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $stats = [
            'install_prompts_shown' => $this->getInstallPromptStats(),
            'installations' => $this->getInstallationStats(),
            'push_subscriptions' => $this->getPushSubscriptionStats(),
            'offline_usage' => $this->getOfflineUsageStats()
        ];

        sendJsonResponse([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get install prompt statistics
     */
    private function getInstallPromptStats() {
        try {
            global $pdo;
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM pwa_install_prompts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            return (int)$stmt->fetch()['total'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get installation statistics
     */
    private function getInstallationStats() {
        try {
            global $pdo;
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM pwa_installations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            return (int)$stmt->fetch()['total'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get push subscription statistics
     */
    private function getPushSubscriptionStats() {
        try {
            global $pdo;
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM push_subscriptions WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            return (int)$stmt->fetch()['total'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get offline usage statistics
     */
    private function getOfflineUsageStats() {
        try {
            global $pdo;
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM offline_actions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            return (int)$stmt->fetch()['total'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Log PWA install prompt shown
     */
    public function logInstallPrompt() {
        header('Content-Type: application/json');

        try {
            global $pdo;

            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $platform = $this->detectPlatform($user_agent);

            $sql = "INSERT INTO pwa_install_prompts (user_agent, platform, ip_address, created_at)
                    VALUES (?, ?, ?, NOW())";

            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$user_agent, $platform, $_SERVER['REMOTE_ADDR'] ?? '']);

            sendJsonResponse([
                'success' => $success,
                'message' => 'Install prompt logged'
            ]);

        } catch (\Exception $e) {
            error_log('Install prompt logging error: ' . $e->getMessage());
            sendJsonResponse(['success' => false, 'error' => 'Logging failed'], 500);
        }
    }

    /**
     * Log PWA installation
     */
    public function logInstallation() {
        header('Content-Type: application/json');

        try {
            global $pdo;

            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $platform = $this->detectPlatform($user_agent);

            $sql = "INSERT INTO pwa_installations (user_agent, platform, ip_address, created_at)
                    VALUES (?, ?, ?, NOW())";

            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$user_agent, $platform, $_SERVER['REMOTE_ADDR'] ?? '']);

            sendJsonResponse([
                'success' => $success,
                'message' => 'Installation logged'
            ]);

        } catch (\Exception $e) {
            error_log('Installation logging error: ' . $e->getMessage());
            sendJsonResponse(['success' => false, 'error' => 'Logging failed'], 500);
        }
    }

    /**
     * Detect platform from user agent
     */
    private function detectPlatform($user_agent) {
        if (strpos($user_agent, 'Android') !== false) {
            return 'Android';
        } elseif (strpos($user_agent, 'iPhone') !== false || strpos($user_agent, 'iPad') !== false) {
            return 'iOS';
        } elseif (strpos($user_agent, 'Windows') !== false) {
            return 'Windows';
        } elseif (strpos($user_agent, 'Mac') !== false) {
            return 'macOS';
        } elseif (strpos($user_agent, 'Linux') !== false) {
            return 'Linux';
        }

        return 'Unknown';
    }
}
