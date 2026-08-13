<?php
require_once __DIR__ . '/../../Helpers/TranslationHelper.php';

// Load site settings from DB (cached in-process)
if (!isset($GLOBALS['_site_settings_cache'])) {
    $GLOBALS['_site_settings_cache'] = [];
    try {
        $scPdo = \App\Core\Database\Database::getInstance()->getPdo();
        $scRows = $scPdo->query("SELECT content_key, content_value FROM site_content WHERE section = 'settings' AND is_active = 1")->fetchAll(PDO::FETCH_KEY_PAIR);
        $GLOBALS['_site_settings_cache'] = $scRows;
    } catch (\Exception $e) { /* graceful fallback */
    }
}
$sc = function ($key, $default = '') {
    return $GLOBALS['_site_settings_cache'][$key] ?? $default;
};

// Google Analytics 4 (gtag.js)
$ga4_id = $_ENV['GA4_MEASUREMENT_ID'] ?? getenv('GA4_MEASUREMENT_ID') ?: 'G-PLACEHOLDER';
$ga4_id = is_string($ga4_id) ? trim($ga4_id) : 'G-PLACEHOLDER';
$ga4_enabled = ($ga4_id !== '');

if ($ga4_enabled && !isset($GLOBALS['_ga4_loader_emitted'])) {
    $GLOBALS['_ga4_loader_emitted'] = true;
}

// NavigationHelper instance (replaces ~200 lines of inline nav logic)
$nav = \App\Helpers\NavigationHelper::getInstance();

// Ensure proper HTML document structure
if (!isset($GLOBALS['_html_doc_started'])) {
    $GLOBALS['_html_doc_started'] = true;
    $page_title = $page_title ?? 'APS Dream Home';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>/assets/css/style.css?v=6" rel="stylesheet">
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>/assets/css/frontend.css?v=6" rel="stylesheet">
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>/assets/css/header.css?v=6" rel="stylesheet">
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>/assets/css/premium-theme.css?v=6" rel="stylesheet">
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>/assets/css/mobile-responsive.css" rel="stylesheet">

    <?php if ($ga4_enabled): ?>
    <!-- Google Analytics 4 -->
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>" async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga4_id) ?>"></script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', '<?= htmlspecialchars($ga4_id) ?>', {
        'anonymize_ip': true
    });
    </script>
    <?php endif; ?>

    <?php if (isset($seo) && is_array($seo)): ?>
    <!-- SEO Auto-Injected Meta Tags -->
    <title><?= htmlspecialchars($seo['title'] ?? 'APS Dream Home') ?></title>
    <meta name="description" content="<?= htmlspecialchars($seo['description'] ?? '') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($seo['keywords'] ?? '') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($seo['canonical'] ?? '') ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($seo['og_title'] ?? '') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seo['og_description'] ?? '') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($seo['og_image'] ?? '') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($seo['og_url'] ?? '') ?>">
    <meta property="og:type" content="<?= htmlspecialchars($seo['og_type'] ?? 'website') ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="<?= htmlspecialchars($seo['twitter_card'] ?? 'summary_large_image') ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($seo['twitter_title'] ?? '') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seo['twitter_description'] ?? '') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($seo['twitter_image'] ?? '') ?>">

    <!-- JSON-LD Structured Data -->
    <?php if (!empty($seo['json_ld'])): ?>
    <script type="application/ld+json">
    <?= json_encode($seo['json_ld'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>
    </script>
    <?php endif; ?>
    <?php endif; ?>
</head>

<body>
<?php
} elseif ($ga4_enabled && !isset($GLOBALS['_ga4_loader_emitted_secondary'])) {
    $GLOBALS['_ga4_loader_emitted_secondary'] = true;
    ?>
    <!-- Google Analytics 4 -->
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>" async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga4_id) ?>"></script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', '<?= htmlspecialchars($ga4_id) ?>', {
        'anonymize_ip': true
    });
    </script>
    <?php
}
if (!defined('BASE_URL')) {
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $protocol = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    $basePath = preg_replace('#/public$#', '', $scriptDir);
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}

// Preserve $current_path / $GLOBALS['current_path'] for any legacy refs
$current_path = $nav->currentPath();
$GLOBALS['current_path'] = $current_path;
$is_home = $nav->isHome();
$header_class = $nav->headerClass();
$isLoggedIn = $nav->isLoggedIn();
$userName = $nav->isLoggedIn() ? $nav->userName() : '';
$userRole = $nav->isLoggedIn() ? $nav->userRole() : '';
$userIcon = $nav->isLoggedIn() ? $nav->userIconClass() : '';
$dashboardUrl = $nav->dashboardUrl();
$logoutUrl  = $nav->logoutUrl();
?>

<?php require __DIR__ . '/../components/navigation/mobile_top_bar.php'; ?>

<header class="<?= $header_class ?>" id="mainHeader">

    <!-- Desktop Navbar (lg+) -->
    <div class="d-none d-xl-block">
        <?php require __DIR__ . '/../components/navigation/desktop_navbar.php'; ?>
    </div>

    <!-- Mobile Drawer -->
    <?php require __DIR__ . '/../components/navigation/mobile_drawer.php'; ?>

</header>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
window.BASE_URL = '<?php echo BASE_URL; ?>';

/**
 * Unified drawer toggle — replaces openMenu/closeMenu pattern.
 *   toggleDrawer(event)      → toggles open state
 *   toggleDrawer(null,'close') → closes
 *   toggleDrawer(null,'open')  → opens
 */
function toggleDrawer(event, action) {
    var drawer = document.getElementById('mobileDrawer');
    var overlay = document.getElementById('mobileDrawerOverlay');
    var toggler = event ? event.currentTarget : document.getElementById('mobileMenuToggle');
    var header = document.getElementById('mainHeader');
    if (!drawer) return;

    var isOpen = drawer.classList.contains('active');
    var doOpen = (action === 'open') || (!action && !isOpen);

    if (doOpen) {
        drawer.classList.add('active');
        overlay?.classList.add('active');
        header?.classList.add('menu-open');
        document.body.classList.add('mobile-drawer-open');
        if (toggler) toggler.setAttribute('aria-expanded', 'true');
        drawer.setAttribute('aria-hidden', 'false');
        // Haptic feedback
        if (navigator.vibrate) navigator.vibrate([10]);
    } else {
        drawer.classList.remove('active');
        overlay?.classList.remove('active');
        header?.classList.remove('menu-open');
        document.body.classList.remove('mobile-drawer-open');
        if (toggler) toggler.setAttribute('aria-expanded', 'false');
        drawer.setAttribute('aria-hidden', 'true');
        if (navigator.vibrate) navigator.vibrate([5, 15, 5]);
    }
}

function updateHeaderNotifCount() {
    var b = document.getElementById('headerNotifBadge');
    if (!b) return;
    <?php
    $notifUserId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? $_SESSION['associate_id'] ?? $_SESSION['employee_id'] ?? null;
    if ($notifUserId): ?>
    fetch(BASE_URL + '/api/user/notifications/unread-count').then(function(r) {
        return r.json();
    }).then(function(d) {
        var c = d.count || 0;
        b.textContent = c;
        b.style.display = c > 0 ? 'inline' : 'none';
    }).catch(function() {});
    <?php endif; ?>
}

document.addEventListener('DOMContentLoaded', function() {
    var header = document.getElementById('mainHeader');
    var drawer = document.getElementById('mobileDrawer');
    var drawerPanel = document.getElementById('mobileDrawerPanel');
    if (!header || !drawer) return;

    function isMobile() {
        return window.innerWidth <= 1199.98;
    }

    // Close drawer on resize above breakpoint
    window.addEventListener('resize', function() {
        if (!isMobile() && drawer.classList.contains('active')) {
            toggleDrawer(null, 'close');
        }
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && drawer.classList.contains('active')) {
            toggleDrawer(null, 'close');
        }
    });

    // Touch swipe: swipe-left-to-close
    var touchStartX = 0;
    var touchEndX = 0;
    if (drawerPanel) {
        drawerPanel.addEventListener('touchstart', function(e) {
            if (e.touches && e.touches.length > 0) {
                touchStartX = e.touches[0].clientX;
            }
        }, { passive: true });
        drawerPanel.addEventListener('touchend', function(e) {
            if (e.changedTouches && e.changedTouches.length > 0) {
                touchEndX = e.changedTouches[0].clientX;
                if (touchStartX - touchEndX > 80 && drawer.classList.contains('active')) {
                    toggleDrawer(null, 'close');
                }
            }
        }, { passive: true });
    }

    // Auto-close drawer on navigation link click (non-dropdown)
    drawer.addEventListener('click', function(e) {
        var link = e.target.closest('.mobile-nav-link');
        if (!link) return;
        var href = link.getAttribute('href');
        if (href && href !== '#' && !link.querySelector('i.fa-chevron-down') && !link.hasAttribute('data-bs-toggle')) {
            toggleDrawer(null, 'close');
        }
    });

    // Desktop dropdown hover (Bootstrap handles via data-bs-toggle)
    // On mobile inside drawer, prevent Bootstrap dropdown; use accordion-style collapse
    function setupDrawerDropdowns() {
        var isMobileView = isMobile;
        drawer.querySelectorAll('.dropdown-toggle[data-bs-toggle="dropdown"]').forEach(function(dt) {
            dt.addEventListener('click', function(e) {
                if (isMobile()) {
                    e.preventDefault();
                    e.stopPropagation();
                    var menu = dt.nextElementSibling;
                    if (menu && menu.classList.contains('dropdown-menu')) {
                        menu.classList.toggle('show');
                    }
                }
            });
        });
    }
    setupDrawerDropdowns();

    // Notification count polling
    updateHeaderNotifCount();
    setInterval(updateHeaderNotifCount, 30000);

    // Scroll effect
    var scrollTimer;
    window.addEventListener('scroll', function() {
        if (scrollTimer) cancelAnimationFrame(scrollTimer);
        scrollTimer = requestAnimationFrame(function() {
            if (window.scrollY > 50 && !drawer.classList.contains('active')) {
                header.classList.add('header-scrolled');
                header.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.1)';
            } else if (!drawer.classList.contains('active')) {
                header.classList.remove('header-scrolled');
                header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.06)';
            }
        });
    });

    // Sync aria-expanded for Bootstrap dropdowns
    header.querySelectorAll('.dropdown-toggle[data-bs-toggle="dropdown"]').forEach(function(dt) {
        dt.addEventListener('shown.bs.dropdown', function() {
            dt.setAttribute('aria-expanded', 'true');
        });
        dt.addEventListener('hidden.bs.dropdown', function() {
            dt.setAttribute('aria-expanded', 'false');
        });
    });
});
</script>

<!-- Quick Search Typeahead -->
<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    const input = document.getElementById('quickSearchInput');
    const dropdown = document.getElementById('quickSearchResults');
    if (!input || !dropdown) return;

    let debounceTimer = null;
    let activeIndex = -1;
    let lastResults = [];

    function renderResults(results) {
        if (!results.length) {
            dropdown.innerHTML =
                '<div class="quick-search-result text-muted"><i class="fas fa-info-circle"></i><span class="label">No matches found</span></div>';
            dropdown.style.display = 'block';
            return;
        }

        let html = '';
        results.forEach((r, i) => {
            const icon = r.type === 'property' ? 'fa-building' :
                r.type === 'location' ? 'fa-map-marker-alt' :
                'fa-tag';
            html += `<a href="${r.url}" class="quick-search-result" data-idx="${i}">
                <i class="fas ${icon}"></i>
                <span class="label">${escapeHtml(r.label)}</span>
                <span class="type-tag">${r.type}</span>
            </a>`;
        });
        html += `<div class="quick-search-footer">
            <a href="${BASE_URL}/properties?q=${encodeURIComponent(input.value)}" class="text-primary small text-decoration-none">
                <i class="fas fa-search me-1"></i>See all results for "${escapeHtml(input.value)}"
            </a>
        </div>`;
        dropdown.innerHTML = html;
        dropdown.style.display = 'block';

        dropdown.querySelectorAll('.quick-search-result').forEach(el => {
            el.addEventListener('mouseenter', () => {
                activeIndex = parseInt(el.dataset.idx);
                updateActive();
            });
        });
    }

    function escapeHtml(s) {
        return String(s || '').replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        } [c]));
    }

    function updateActive() {
        dropdown.querySelectorAll('.quick-search-result').forEach((el, i) => {
            el.classList.toggle('active', i === activeIndex);
        });
    }

    function search(q) {
        if (q.length < 2) {
            dropdown.style.display = 'none';
            return;
        }
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetch(BASE_URL + '/api/saved-searches/autocomplete?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        lastResults = d.results;
                        renderResults(d.results);
                    }
                })
                .catch(() => {});
        }, 200);
    }

    input.addEventListener('input', e => {
        activeIndex = -1;
        search(e.target.value.trim());
    });

    input.addEventListener('focus', () => {
        if (input.value.trim().length >= 2 && lastResults.length) {
            dropdown.style.display = 'block';
        }
    });

    input.addEventListener('keydown', e => {
        const items = dropdown.querySelectorAll('.quick-search-result');
        if (e.key === 'ArrowDown' && items.length) {
            e.preventDefault();
            activeIndex = Math.min(activeIndex + 1, items.length - 1);
            updateActive();
        } else if (e.key === 'ArrowUp' && items.length) {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
            updateActive();
        } else if (e.key === 'Enter') {
            if (activeIndex >= 0 && lastResults[activeIndex]) {
                e.preventDefault();
                window.location.href = lastResults[activeIndex].url;
            }
        } else if (e.key === 'Escape') {
            dropdown.style.display = 'none';
        }
    });

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
})();

function quickSearchSubmit(e) {
    e.preventDefault();
    const q = document.getElementById('quickSearchInput').value.trim();
    if (q) window.location.href = BASE_URL + '/properties?q=' + encodeURIComponent(q);
    return false;
}
</script>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>" src="<?php echo BASE_URL; ?>/js/visitor-tracking.js" defer></script>
