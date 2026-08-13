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

<header class="<?= $header_class ?>" id="mainHeader">

    <!-- Mobile Top Bar (sm/md) -->
    <?php require __DIR__ . '/../components/navigation/mobile_top_bar.php'; ?>

    <!-- Desktop Navbar (lg+) -->
    <div class="d-none d-xl-block">
        <?php require __DIR__ . '/../components/navigation/desktop_navbar.php'; ?>
    </div>

    <!-- Mobile Drawer -->
    <?php require __DIR__ . '/../components/navigation/mobile_drawer.php'; ?>

    <!-- ARIA Live Region for Screen Reader Announcements -->
    <div id="ariaLiveRegion" class="visually-hidden" aria-live="polite" aria-atomic="true" role="status"></div>

</header>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
window.BASE_URL = '<?php echo BASE_URL; ?>';

/**
 * Unified drawer toggle — replaces openMenu/closeMenu pattern.
 *   toggleDrawer(event)      → toggles open state
 *   toggleDrawer(null,'close') → closes
 *   toggleDrawer(null,'open')  → opens
 */
var _drawerPreviousFocus = null;

function toggleDrawer(event, action) {
    var drawer = document.getElementById('mobileDrawer');
    var overlay = document.getElementById('mobileDrawerOverlay');
    var toggler = event ? event.currentTarget : document.getElementById('mobileMenuToggle');
    var header = document.getElementById('mainHeader');
    if (!drawer) return;

    var isOpen = drawer.classList.contains('active');
    var doOpen = (action === 'open') || (!action && !isOpen);

    if (doOpen) {
        // Save current focus for restoration
        _drawerPreviousFocus = document.activeElement;
        drawer.classList.add('active');
        overlay?.classList.add('active');
        header?.classList.add('menu-open');
        document.body.classList.add('mobile-drawer-open');
        if (toggler) toggler.setAttribute('aria-expanded', 'true');
        drawer.setAttribute('aria-hidden', 'false');
        // Focus first interactive element in drawer
        var firstFocusable = drawer.querySelector('a, button, input, [tabindex]:not([tabindex="-1"])');
        if (firstFocusable) setTimeout(function() { firstFocusable.focus(); }, 100);
        // Haptic feedback
        if (navigator.vibrate) navigator.vibrate([10]);
        // Screen reader announcement
        if (typeof window.apsAnnounce === 'function') window.apsAnnounce('Navigation menu opened');
    } else {
        drawer.classList.remove('active');
        overlay?.classList.remove('active');
        header?.classList.remove('menu-open');
        document.body.classList.remove('mobile-drawer-open');
        if (toggler) toggler.setAttribute('aria-expanded', 'false');
        drawer.setAttribute('aria-hidden', 'true');
        // Restore focus to element that opened the drawer
        if (_drawerPreviousFocus && _drawerPreviousFocus.focus) {
            _drawerPreviousFocus.focus();
            _drawerPreviousFocus = null;
        }
        if (navigator.vibrate) navigator.vibrate([5, 15, 5]);
        // Screen reader announcement
        if (typeof window.apsAnnounce === 'function') window.apsAnnounce('Navigation menu closed');
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

    // ── Keyboard Trap for Drawer (Accessibility) ──
    // When drawer is open, Tab cycles within drawer only
    drawer.addEventListener('keydown', function(e) {
        if (e.key !== 'Tab' || !drawer.classList.contains('active')) return;

        var focusables = drawer.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        if (focusables.length === 0) return;

        var first = focusables[0];
        var last = focusables[focusables.length - 1];

        if (e.shiftKey) {
            // Shift+Tab: if on first element, wrap to last
            if (document.activeElement === first) {
                e.preventDefault();
                last.focus();
            }
        } else {
            // Tab: if on last element, wrap to first
            if (document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });

    // ── Swipe-to-Close Drawer Gesture (Mobile) ──
    var _swipeStartX = 0;
    var _swipeStartY = 0;
    var _swipeTracking = false;

    drawer.addEventListener('touchstart', function(e) {
        if (!drawer.classList.contains('active')) return;
        var touch = e.touches[0];
        _swipeStartX = touch.clientX;
        _swipeStartY = touch.clientY;
        _swipeTracking = true;
    }, { passive: true });

    drawer.addEventListener('touchmove', function(e) {
        if (!_swipeTracking) return;
        var touch = e.touches[0];
        var deltaX = touch.clientX - _swipeStartX;
        var deltaY = Math.abs(touch.clientY - _swipeStartY);
        // Only track horizontal swipes (right-to-left or left-to-right)
        if (deltaY > 30) {
            _swipeTracking = false;
            return;
        }
        // If swiping right by >80px, close the drawer
        if (deltaX > 80) {
            _swipeTracking = false;
            toggleDrawer(null, 'close');
        }
    }, { passive: true });

    drawer.addEventListener('touchend', function() {
        _swipeTracking = false;
    }, { passive: true });

    // Notification count polling
    updateHeaderNotifCount();
    setInterval(updateHeaderNotifCount, 30000);

    // ── Smart Auto-Hiding Header ──
    // On mobile: hide on scroll down, show on scroll up (app-like UX)
    // On desktop: always visible with subtle shadow on scroll
    var lastScrollY = 0;
    var headerHidden = false;
    var scrollTick = false;

    // ── ARIA Live Announcements ──
    function announce(message) {
        var region = document.getElementById('ariaLiveRegion');
        if (region) {
            region.textContent = '';
            // Small delay so screen readers detect the change
            setTimeout(function() { region.textContent = message; }, 50);
        }
    }
    window.apsAnnounce = announce;

    function isMobileView() {
        return window.innerWidth <= 1199.98;
    }

    function updateHeaderOnScroll() {
        var currentY = window.scrollY;
        var delta = currentY - lastScrollY;

        // Don't hide if drawer is open or near top
        if (drawer.classList.contains('active') || currentY < 10) {
            if (headerHidden) {
                header.style.transform = 'translateY(0)';
                header.classList.remove('header-hidden');
                headerHidden = false;
            }
            // Always remove scrolled class near top
            if (currentY < 10) {
                header.classList.remove('header-scrolled');
            }
            lastScrollY = currentY;
            scrollTick = false;
            return;
        }

        if (isMobileView()) {
            // Mobile: auto-hide behavior
            if (delta > 8 && !headerHidden) {
                // Scrolling down — hide header
                header.style.transform = 'translateY(-100%)';
                header.classList.add('header-hidden');
                headerHidden = true;
            } else if (delta < -8 && headerHidden) {
                // Scrolling up — show header
                header.style.transform = 'translateY(0)';
                header.classList.remove('header-hidden');
                headerHidden = false;
            }
        } else {
            // Desktop: always visible, shadow on scroll
            if (currentY > 50) {
                header.classList.add('header-scrolled');
                header.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.1)';
            } else {
                header.classList.remove('header-scrolled');
                header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.06)';
            }
        }

        lastScrollY = currentY;
        scrollTick = false;
    }

    window.addEventListener('scroll', function() {
        if (!scrollTick) {
            requestAnimationFrame(updateHeaderOnScroll);
            scrollTick = true;
        }
    }, { passive: true });

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

<!-- ============================================================
     GLOBAL COMMAND PALETTE (Ctrl+K)
     ============================================================ -->
<div class="command-palette-overlay" id="commandPalette">
    <div class="command-palette" role="dialog" aria-label="Search and navigate">
        <div class="command-palette-input-wrap">
            <i class="fas fa-search"></i>
            <input type="text"
                   class="command-palette-input"
                   id="cmdPaletteInput"
                   placeholder="Search properties, pages, actions..."
                   autocomplete="off"
                   autofocus>
            <span class="command-palette-kbd">ESC</span>
        </div>
        <div class="command-palette-results" id="cmdPaletteResults"></div>
        <div class="command-palette-footer">
            <span><kbd>&uarr;</kbd><kbd>&darr;</kbd> Navigate</span>
            <span><kbd>&crarr;</kbd> Open</span>
            <span><kbd>ESC</kbd> Close</span>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    var overlay = document.getElementById('commandPalette');
    var input = document.getElementById('cmdPaletteInput');
    var results = document.getElementById('cmdPaletteResults');
    if (!overlay || !input || !results) return;

    var activeIndex = -1;
    var currentItems = [];
    var isOpen = false;
    var RECENT_KEY = 'aps_recent_searches';
    var MAX_RECENT = 5;

    function getRecentSearches() {
        try { return JSON.parse(localStorage.getItem(RECENT_KEY)) || []; }
        catch(e) { return []; }
    }

    function saveRecentSearch(item) {
        var recents = getRecentSearches().filter(function(r) { return r.label !== item.label; });
        recents.unshift({ label: item.label, url: item.url, icon: item.icon, group: 'Recent' });
        if (recents.length > MAX_RECENT) recents = recents.slice(0, MAX_RECENT);
        try { localStorage.setItem(RECENT_KEY, JSON.stringify(recents)); } catch(e) {}
    }

    /* ── Static commands + pages ── */
    var commands = [
        { label: 'Dashboard', icon: 'fas fa-gauge-high bg-teal', url: BASE_URL + '/admin/dashboard', group: 'Pages' },
        { label: 'Properties', icon: 'fas fa-building bg-teal', url: BASE_URL + '/properties', group: 'Pages' },
        { label: 'Colonies', icon: 'fas fa-map-location-dot bg-blue', url: BASE_URL + '/projects', group: 'Pages' },
        { label: 'Post Property', icon: 'fas fa-plus-circle bg-amber', url: BASE_URL + '/list-property', group: 'Actions' },
        { label: 'Compare Properties', icon: 'fas fa-code-compare bg-purple', url: BASE_URL + '/compare', group: 'Actions' },
        { label: 'Saved Searches', icon: 'fas fa-bookmark bg-blue', url: BASE_URL + '/saved-searches', group: 'Pages' },
        { label: 'Tools Hub', icon: 'fas fa-flask bg-purple', url: BASE_URL + '/tools-hub', group: 'Pages' },
        { label: 'Partner Tools', icon: 'fas fa-calculator bg-teal', url: BASE_URL + '/partner-tools', group: 'Pages' },
        { label: 'Contact Us', icon: 'fas fa-phone bg-amber', url: BASE_URL + '/contact', group: 'Pages' },
        { label: 'About Us', icon: 'fas fa-circle-info bg-blue', url: BASE_URL + '/about', group: 'Pages' },
        { label: 'My Profile', icon: 'fas fa-user bg-teal', url: BASE_URL + '/user/profile', group: 'Account' },
        { label: 'My Bookings', icon: 'fas fa-calendar-check bg-blue', url: BASE_URL + '/user/bookings', group: 'Account' },
        { label: 'My Favorites', icon: 'fas fa-heart bg-purple', url: BASE_URL + '/user/favorites', group: 'Account' },
        { label: 'Notifications', icon: 'fas fa-bell bg-amber', url: BASE_URL + '/user/notifications', group: 'Account' },
    ];

    /* ── Dynamic: add property search shortcut ── */
    function getSearchResults(query) {
        var q = query.toLowerCase().trim();
        if (q.length < 1) return [];

        var matches = commands.filter(function(c) {
            return c.label.toLowerCase().indexOf(q) !== -1;
        }).slice(0, 5);

        /* Add "search for ..." option */
        matches.push({
            label: 'Search for "' + query + '"',
            icon: 'fas fa-search bg-teal',
            url: BASE_URL + '/properties?q=' + encodeURIComponent(query),
            group: 'Search'
        });

        return matches;
    }

    function render(items) {
        currentItems = items.filter(function(i) { return !i.divider; });
        activeIndex = -1;
        if (currentItems.length === 0) {
            results.innerHTML = '<div style="padding:24px 16px;text-align:center;color:#94a3b8;font-size:13px;">No results found</div>';
            return;
        }

        var groups = {};
        items.forEach(function(item) {
            if (item.divider) {
                if (!groups[item.group]) groups[item.group] = [];
                return;
            }
            if (!groups[item.group]) groups[item.group] = [];
            groups[item.group].push(item);
        });

        var html = '';
        var idx = 0;
        Object.keys(groups).forEach(function(group) {
            html += '<div class="command-palette-group">' + group + '</div>';
            if (!groups[group]) return;
            groups[group].forEach(function(item) {
                html += '<a href="' + item.url + '" class="command-palette-item" data-cmd-idx="' + idx + '">'
                    + '<i class="' + item.icon + '"></i>'
                    + '<div class="command-palette-item-text"><strong>' + item.label + '</strong></div>'
                    + '</a>';
                idx++;
            });
        });
        results.innerHTML = html;
    }

    function setActive(index) {
        activeIndex = index;
        results.querySelectorAll('.command-palette-item').forEach(function(el, i) {
            el.classList.toggle('active', i === index);
            if (i === index) el.scrollIntoView({ block: 'nearest' });
        });
    }

    function open() {
        if (isOpen) return;
        isOpen = true;
        overlay.classList.add('active');
        input.value = '';
        // Show recent searches first, then default commands
        var recents = getRecentSearches();
        var items = recents.length > 0
            ? recents.concat([{ divider: true, group: 'Suggestions' }]).concat(commands.slice(0, 6))
            : commands.slice(0, 8);
        render(items);
        setTimeout(function() { input.focus(); }, 50);
        document.body.style.overflow = 'hidden';
    }

    function close() {
        if (!isOpen) return;
        isOpen = false;
        overlay.classList.remove('active');
        input.value = '';
        document.body.style.overflow = '';
    }

    /* ── Keyboard shortcut: Ctrl+K or Cmd+K ── */
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (isOpen) close(); else open();
        }
        if (e.key === 'Escape' && isOpen) {
            close();
        }
    });

    /* ── Input handler ── */
    input.addEventListener('input', function() {
        var q = input.value.trim();
        if (q.length === 0) {
            render(commands.slice(0, 8));
        } else {
            render(getSearchResults(q));
        }
    });

    /* ── Arrow key navigation ── */
    input.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setActive(Math.min(activeIndex + 1, currentItems.length - 1));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setActive(Math.max(activeIndex - 1, 0));
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeIndex >= 0 && currentItems[activeIndex]) {
                saveRecentSearch(currentItems[activeIndex]);
                window.location.href = currentItems[activeIndex].url;
            }
        }
    });

    /* ── Click on item to navigate + save recent ── */
    results.addEventListener('click', function(e) {
        var item = e.target.closest('.command-palette-item');
        if (item) {
            var idx = parseInt(item.getAttribute('data-cmd-idx'), 10);
            if (currentItems[idx]) saveRecentSearch(currentItems[idx]);
        }
    });

    /* ── Click on overlay to close ── */
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) close();
    });

    /* ── Expose globally ── */
    window.openCommandPalette = open;
    window.closeCommandPalette = close;
})();
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
