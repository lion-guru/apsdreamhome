<?php
// CSP headers now set centrally in BaseController::setSecurityHeaders()
// SecurityHelper is still used for CSRF token generation via SecurityHelper::generateCsrfToken()
// Load site settings from DB (same cache as header/footer)
if (!isset($GLOBALS['_site_settings_cache'])) {
    $GLOBALS['_site_settings_cache'] = [];
    try {
        $scPdo = \App\Core\Database\Database::getInstance()->getPdo();
        $scRows = $scPdo->query("SELECT content_key, content_value FROM site_content WHERE section = 'settings' AND is_active = 1")->fetchAll(PDO::FETCH_KEY_PAIR);
        $GLOBALS['_site_settings_cache'] = $scRows;
    } catch (\Exception $e) {}
}
$sc = function($key, $default = '') { return $GLOBALS['_site_settings_cache'][$key] ?? $default; };
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!(isset($seo) && is_array($seo))): ?>
    <title><?php echo $page_title ?? $sc('seo_title', 'APS Dream Home - Premium Real Estate in Uttar Pradesh'); ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description ?? $sc('seo_description', 'APS Dream Home offers premium residential plots, houses, and commercial properties in Gorakhpur, Lucknow, Kushinagar and across Uttar Pradesh.')) ?>">
    <?php endif; ?>
    <meta name="keywords" content="<?= htmlspecialchars($sc('seo_keywords', 'real estate, plots, homes, Gorakhpur, Lucknow, Kushinagar, Varanasi, Uttar Pradesh, property, residential, commercial')) ?>">
    <meta name="author" content="APS Dream Home">
    <meta name="robots" content="index, follow">
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/manifest.json">
    <meta name="theme-color" content="#6B4EE6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="APS Dream">
    <link rel="apple-touch-icon" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/images/logo-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/images/logo-192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/images/favicon-32.png">
    <?php if (isset($_SESSION['user_id'])): ?>
    <meta name="user-id" content="<?= (int)$_SESSION['user_id'] ?>">
    <?php endif; ?>
    <!-- Geo Tags -->
    <meta name="geo.region" content="IN-UP">
    <meta name="geo.placename" content="Gorakhpur">
    <meta name="geo.position" content="26.7606;83.3732">
    <meta name="ICBM" content="26.7606, 83.3732">
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_IN">
    <meta property="og:title" content="<?= htmlspecialchars($page_title ?? $sc('seo_title', 'APS Dream Home - Premium Real Estate')) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_description ?? $sc('seo_description', 'Premium Real Estate in Uttar Pradesh')) ?>">
    <?php if ($sc('seo_og_image')): ?>
    <meta property="og:image" content="<?= htmlspecialchars($sc('seo_og_image')) ?>">
    <?php else: ?>
    <meta property="og:image" content="<?php echo BASE_URL; ?>/assets/images/logo/apslogonew.jpg">
    <?php endif; ?>
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="APS Dream Home">
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title ?? $sc('seo_title', 'APS Dream Home')) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_description ?? $sc('seo_description', 'Premium Real Estate in Uttar Pradesh')) ?>">
    <meta name="twitter:image" content="<?php echo BASE_URL; ?>/assets/images/logo/apslogonew.jpg">

    <!-- Bootstrap JS (early load for modals) -->
    <script defer src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>

    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "RealEstateAgent",
        "name": <?= json_encode($sc('company_name', 'APS Dream Home')) ?>,
        "image": "<?= defined('BASE_URL') ? BASE_URL : '' ?>/<?= $sc('company_logo', 'assets/images/logo/apslogonew.jpg') ?>",
        "url": "<?= defined('BASE_URL') ? BASE_URL : '' ?>",
        "telephone": <?= json_encode(preg_replace('/[^0-9+]/', '', $sc('contact_phone', '+91-9277121112'))) ?>,
        "address": {
            "@type": "PostalAddress",
            "streetAddress": <?= json_encode($sc('contact_address', '1st floor, Singhariya Chauraha, Kunraghat, Deoria Road')) ?>,
            "addressLocality": <?= json_encode($sc('contact_city', 'Gorakhpur')) ?>,
            "addressRegion": <?= json_encode($sc('contact_state', 'Uttar Pradesh')) ?>,
            "postalCode": <?= json_encode($sc('contact_pincode', '273008')) ?>,
            "addressCountry": "IN"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "reviewCount": "200"
        },
        "openingHoursSpecification": [
            {"@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"], "opens": "09:00", "closes": "19:00"}
        ],
        "sameAs": [
            <?= $sc('social_facebook') ? json_encode($sc('social_facebook')) . ',' : '' ?>
            <?= $sc('social_instagram') ? json_encode($sc('social_instagram')) . ',' : '' ?>
            <?= $sc('social_youtube') ? json_encode($sc('social_youtube')) . ',' : '' ?>
            <?= $sc('social_linkedin') ? json_encode($sc('social_linkedin')) : '' ?>
        ]
    }
    </script>
    
    <!-- CSRF Token -->
    <?php if (isset($_SESSION['csrf_token'])): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES); ?>">
    <?php endif; ?>
    
    <!-- User ID for WebSocket auth -->
    <?php if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])): ?>
    <meta name="user-id" content="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['admin_id']; ?>">
    <?php endif; ?>

    <?php if (isset($seo) && is_array($seo)): ?>
    <!-- SEO Auto-Injected Meta Tags (BaseController::generateSEO) -->
    <title><?= htmlspecialchars($seo['title'] ?? 'APS Dream Home') ?></title>
    <meta name="description" content="<?= htmlspecialchars($seo['description'] ?? '') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($seo['keywords'] ?? '') ?>">

    <!-- Open Graph (dynamic) -->
    <meta property="og:title" content="<?= htmlspecialchars($seo['og_title'] ?? '') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seo['og_description'] ?? '') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($seo['og_image'] ?? '') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($seo['og_url'] ?? '') ?>">
    <meta property="og:type" content="<?= htmlspecialchars($seo['og_type'] ?? 'website') ?>">

    <!-- Twitter Card (dynamic) -->
    <meta name="twitter:card" content="<?= htmlspecialchars($seo['twitter_card'] ?? 'summary_large_image') ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($seo['twitter_title'] ?? '') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seo['twitter_description'] ?? '') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($seo['twitter_image'] ?? '') ?>">

    <!-- Canonical (dynamic — overrides static one above for accuracy) -->
    <link rel="canonical" href="<?= htmlspecialchars($seo['canonical'] ?? '') ?>">

    <!-- JSON-LD Structured Data -->
    <?php if (!empty($seo['json_ld'])): ?>
    <script type="application/ld+json">
    <?= json_encode($seo['json_ld'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>
    </script>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="<?php echo BASE_URL; ?>/assets/images/logo/apslogonew.jpg">

    <!-- PWA Manifest (dynamic URL) -->
    <meta name="theme-color" content="#2c3e50">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="APS Dream Home">
    <link rel="manifest" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/pwa/manifest">
    <link rel="apple-touch-icon" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/images/icons/icon-192x192.png">

    <!-- Preconnect to CDN origins for faster resource loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google Fonts (preloaded) -->
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <!-- Leaflet CSS for map picker -->
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <?php
    $isAdminPage = isset($admin_layout) && $admin_layout === true;
    $isPremiumPage = isset($premium_layout) && $premium_layout === true;
    ?>
    <?php if (!$isAdminPage): ?>
    <!-- CSS: base → components → header → premium theme (ALL public pages) → homepage extras -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css?v=6" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/frontend.css?v=6" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/header.css?v=7" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/premium-theme.css?v=8" rel="stylesheet">
    <!-- Homepage + public page extras (hero, projects, animations) -->
    <link href="<?php echo BASE_URL; ?>/assets/css/homepage.css?v=10" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/modern-animations.css?v=1" rel="stylesheet">
    <!-- Core layout, typography, dark-section helpers -->
    <link href="<?php echo BASE_URL; ?>/assets/css/consolidated/aps-core.css?v=2" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/consolidated/aps-pages.css?v=2" rel="stylesheet">
    <!-- Customer portal components (wizard, hero, cards, checklist) -->
    <link href="<?php echo BASE_URL; ?>/assets/css/consolidated/aps-components.css?v=1" rel="stylesheet">
    <?php endif; ?>
    <link href="<?php echo BASE_URL; ?>/assets/css/notification-system.css?v=6" rel="stylesheet">
    <!-- Live Chat Widget CSS -->
    <link href="<?php echo BASE_URL; ?>/assets/css/live-chat-widget.css" rel="stylesheet">
    <!-- Notification Toast Widget CSS -->
    <link href="<?php echo BASE_URL; ?>/assets/css/notification-widget.css" rel="stylesheet">
    <!-- Universal mobile-first responsive overrides -->
    <link href="<?php echo BASE_URL; ?>/assets/css/mobile-responsive.css?v=2" rel="stylesheet">

    <!-- Scroll fix (Cleaned) -->
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        header.premium-header { overflow: visible; }
        header.premium-header .navbar-collapse { overflow: visible; }
    </style>

    <!-- Extra head content from views -->
    <?php if (!empty($extraHead)) echo $extraHead; ?>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        // Dynamically set header height for page offset (immediately + on resize)
        (function() {
            function setHeaderHeight() {
                var hdr = document.querySelector('header.premium-header');
                var h = hdr ? hdr.offsetHeight : 80;
                document.documentElement.style.setProperty('--header-height', h + 'px');
            }
            // Run immediately after CSS paints
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', setHeaderHeight);
            } else {
                setHeaderHeight();
            }
            window.addEventListener('load', setHeaderHeight);
            window.addEventListener('resize', setHeaderHeight);
        })();
    </script>
</head>

<body>
    <?php
    // Mark document as started so header.php doesn't emit duplicate DOCTYPE/head/body
    $GLOBALS['_html_doc_started'] = true;

    if (!$isAdminPage) {
        if ($isPremiumPage) {
            include __DIR__ . '/active/header.php';
        } else {
            include __DIR__ . '/header.php';
        }
    }
    ?>

    <?php if (!$isAdminPage): ?>
    <div class="container-fluid px-0">
        <?= (new \App\Services\AdManagerService())->renderSlot('header_banner') ?>
    </div>
    <?php endif; ?>

    <main>
        <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['flash_success'] ?? ''); unset($_SESSION['flash_success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['flash_error'] ?? ''); unset($_SESSION['flash_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>
        <?php echo $content ?? ''; ?>
    </main>

    <?php if (!$isAdminPage): ?>
    <div class="container-fluid px-0">
        <?= (new \App\Services\AdManagerService())->renderSlot('footer_banner') ?>
    </div>
    <?php endif; ?>

    <?php
    if (!$isAdminPage) {
        // Tell footer.php NOT to close the document — base.php handles it
        $GLOBALS['_layout_handles_close'] = true;
        if ($isPremiumPage) {
            include __DIR__ . '/active/footer.php';
        } else {
            include __DIR__ . '/footer.php';
        }
    }
    ?>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    // Lazy load images that are below the fold
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('img:not([loading])').forEach(function(img) {
            var rect = img.getBoundingClientRect();
            if (rect.top > 600) img.setAttribute('loading', 'lazy');
        });
    });
    </script>

    <!-- Main AI Chatbot Integration -->
    <?php include __DIR__ . '/chat_widget.php'; ?>

    <!-- WhatsApp button is now inside chat_widget.php (combined toggle) -->

    <!-- Mobile Sticky Bottom Navigation -->
    <?php if (!$isAdminPage): ?>
    <nav class="mobile-bottom-sticky-nav" aria-label="Mobile bottom navigation">
      <a href="/" class="mobile-nav-item" data-label="Home">
        <i class="fas fa-home"></i>
      </a>
      <a href="/properties" class="mobile-nav-item" data-label="Properties">
        <i class="fas fa-building"></i>
      </a>
      <a href="/search" class="mobile-nav-item" data-label="Search">
        <i class="fas fa-search"></i>
      </a>
      <?php if (isset($_SESSION['user_id'])): ?>
      <a href="/user/dashboard" class="mobile-nav-item" data-label="Dashboard">
        <i class="fas fa-tachometer-alt"></i>
      </a>
      <a href="/user/profile" class="mobile-nav-item" data-label="Profile">
        <i class="fas fa-user"></i>
      </a>
      <?php else: ?>
      <a href="/login" class="mobile-nav-item" data-label="Login">
        <i class="fas fa-sign-in-alt"></i>
      </a>
      <a href="/about" class="mobile-nav-item" data-label="About">
        <i class="fas fa-info-circle"></i>
      </a>
      <?php endif; ?>
    </nav>
    <?php endif; ?>

    <!-- Real-time WebSocket Notifications -->
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        window.NOTIFY_USER = {
            id: <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 'null'); ?>,
            role: '<?php echo isset($_SESSION['admin_id']) ? 'admin' : (isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role'] ?? '', ENT_QUOTES) : (isset($_SESSION['user_id']) ? 'customer' : 'guest')); ?>'
        };
    </script>
    <script defer src="<?php echo BASE_URL; ?>/assets/js/notification-system.js?v=4"></script>
    <!-- WebSocket Notification Widget (complements notification-system.js) -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <script defer src="<?php echo BASE_URL; ?>/assets/js/notification-widget.js"></script>
    <?php endif; ?>
    <!-- Live Chat Widget (Removed to prevent overlap with chat_widget.php) -->
    <!-- <?php // include __DIR__ . '/../components/live_chat_widget.php'; ?> -->
    <!-- <script defer src="<?php // echo BASE_URL; ?>/assets/js/live-chat-widget.js"></script> -->
    <!-- Toast Notifications -->
    <script defer src="<?php echo BASE_URL; ?>/assets/js/toast-notifications.js"></script>
    <!-- Image Gallery Lightbox -->
    <script defer src="<?php echo BASE_URL; ?>/assets/js/image-gallery.js"></script>

    <!-- Custom JS -->
    <script defer src="<?php echo BASE_URL; ?>/assets/js/main.js?v=5"></script>
    <script defer src="<?php echo BASE_URL; ?>/assets/js/modern-effects.js?v=1"></script>
    <script defer src="<?php echo BASE_URL; ?>/assets/js/aps-location-autofill.js"></script>
    <script defer src="<?php echo BASE_URL; ?>/assets/js/aps-map-picker.js"></script>
    <!-- Leaflet JS for map picker -->
    <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- premium-header.js removed - functionality merged into header.php inline -->

    <!-- Utility JS -->
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            if (anchor.hasAttribute('data-bs-toggle')) return;
            if (anchor.getAttribute('role') === 'tab') return;
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        var counters = document.querySelectorAll('.stat-number');
        counters.forEach(function(counter) {
            function animate() {
                var value = +counter.getAttribute('data-target');
                var data = +counter.innerText;
                if (data < value) {
                    counter.innerText = Math.ceil(data + value / 200);
                    setTimeout(animate, 1);
                } else {
                    counter.innerText = value;
                }
            }
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) { animate(); observer.unobserve(entry.target); }
                });
            });
            observer.observe(counter);
        });

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/sw.js')
                    .then(function(reg) { console.log('SW registered:', reg.scope); })
                    .catch(function(err) { console.log('SW registration failed:', err); });
            });
        }
    </script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    // Polyfill or fallback mapping if needed, toast is handled by toast-notifications.js
    function showToast(message, type) {
        if (typeof window.APS !== 'undefined' && window.APS.toast) {
            window.APS.toast(message, type || 'info');
        }
    }

    // Mobile bottom nav active state
    (function() {
        var bottomNav = document.querySelector('.mobile-bottom-sticky-nav');
        if (!bottomNav) return;
        var path = window.location.pathname;
        var items = bottomNav.querySelectorAll('.mobile-nav-item');
        items.forEach(function(item) {
            var href = item.getAttribute('href') || '';
            if (href === '/') {
                if (path === '/') item.classList.add('active');
            } else if (path.startsWith(href)) {
                item.classList.add('active');
            }
        });

        // Haptic feedback on mobile nav taps
        if ('vibrate' in navigator) {
            items.forEach(function(item) {
                item.addEventListener('click', function() {
                    try { navigator.vibrate([5]); } catch (e) {}
                });
            });
        }
    })();
    </script>
    
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        if (typeof AOS !== 'undefined') {
            // Automatically add data-aos attributes to elements with premium-reveal
            document.querySelectorAll('.premium-reveal').forEach(function(el) {
                if (!el.hasAttribute('data-aos')) {
                    el.setAttribute('data-aos', 'fade-up');
                }
            });
            AOS.init({
                duration: 800,
                once: true,
                offset: 50,
                easing: 'ease-out-cubic'
            });
        }
      });
    </script>
</body>

</html>
