<?php
// Helper function for HTML escaping if not defined
if (!function_exists('h')) {
    function h($string)
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Helper to create safe HTML id attributes
if (!function_exists('sanitize_for_html_id')) {
    function sanitize_for_html_id($string)
    {
        $id = preg_replace('/[^a-zA-Z0-9]/', '', $string ?? '');
        return $id ?: 'item';
    }
}

// Load site settings if not already loaded
if (!isset($GLOBALS['_site_settings_cache'])) {
    $GLOBALS['_site_settings_cache'] = [];
    try {
        $scPdo = \App\Core\Database\Database::getInstance()->getConnection();
        $scRows = $scPdo->query("SELECT content_key, content_value FROM site_content WHERE section = 'settings' AND is_active = 1")->fetchAll(PDO::FETCH_KEY_PAIR);
        $GLOBALS['_site_settings_cache'] = $scRows;
    } catch (\Exception $e) {
    }
}
$sc = function ($key, $default = '') {
    return $GLOBALS['_site_settings_cache'][$key] ?? $default;
};

// Check authentication
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['associate_id']) || isset($_SESSION['agent_id']) || isset($_SESSION['employee_id']) || isset($_SESSION['admin_id']);

// NavigationHelper for mobile drawer + bottom nav
// Class is autoloaded via App namespace â€” no require_once needed
$nav = \App\Helpers\NavigationHelper::getInstance();
?>

<?php require __DIR__ . '/../components/navigation/mobile_top_bar.php'; ?>

<header class="premium-header fixed-top" id="mainHeader">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <nav class="navbar navbar-expand-xl align-items-center" class="style-53424">
        <div class="container d-flex align-items-center">
            <!-- Logo - Always on the left -->
            <a class="navbar-brand d-flex align-items-center me-0" href="<?php echo BASE_URL; ?>" class="style-38085">
                <?php $brand = $sc('company_name', 'APS Dream Home');
                $logo = $sc('company_logo', '/assets/images/logo/apslogonew.jpg'); ?>
                <img src="<?php echo BASE_URL . h($logo); ?>" alt="<?php echo h($brand); ?>" class="logo" class="style-11857" loading="eager" fetchpriority="high">
                <?php if (isset($brand)): ?>
                    <span class="brand-text d-none d-md-inline ms-2 fw-bold" class="style-38619"><?php echo h($brand); ?></span>
                <?php endif; ?>
            </a>

            <!-- Mobile Hamburger Toggle -->
            <button type="button" class="navbar-toggler ms-auto d-xl-none border-0" id="mobileMenuToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="mobileDrawer">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation & Actions -->
            <div class="collapse navbar-collapse d-none d-xl-flex" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <?php foreach ($nav->getDesktopNavItems() as $item): ?>
                        <?php
                            $hasSubmenu = isset($item['submenu']);
                            $isActive   = !$hasSubmenu && $nav->isActive($item['url'] ?? '');
                        ?>
                        <?php if ($hasSubmenu): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle"
                                   href="#"
                                   id="nav_<?php echo sanitize_for_html_id($item['label'] ?? 'item'); ?>D"
                                   role="button"
                                   data-bs-toggle="dropdown"
                                   aria-expanded="false">
                                    <?php if (isset($item['icon'])): ?><i class="<?php echo $item['icon']; ?> me-1"></i><?php endif; ?>
                                    <?php echo __($item['label']); ?>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="nav_<?php echo sanitize_for_html_id($item['label'] ?? 'item'); ?>D">
                                    <?php foreach ($item['submenu'] as $sub): ?>
                                        <li>
                                            <a class="dropdown-item"
                                               href="<?php echo BASE_URL . rtrim('/' . ltrim($sub['url'] ?? '#', '/'), ''); ?>">
                                                <?php if (isset($sub['icon'])): ?><i class="<?php echo $sub['icon']; ?> me-2"></i><?php endif; ?>
                                                <?php echo __($sub['label']); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $isActive ? 'active' : ''; ?> <?php echo ($item['highlight'] ?? false) ? 'text-warning fw-bold' : ''; ?>"
                                   href="<?php echo BASE_URL . rtrim('/' . ltrim($item['url'] ?? '#', '/'), ''); ?>">
                                    <?php if (isset($item['icon'])): ?><i class="<?php echo $item['icon']; ?> me-1"></i><?php endif; ?>
                                    <?php echo __($item['label']); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

                <!-- Action Buttons (Desktop) -->
                <ul class="navbar-nav ms-auto align-items-center">
                    <!-- Phone Button -->
                    <li class="nav-item ms-2">
                        <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $sc('contact_phone', '+91 92771 21112')); ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-phone me-1"></i>
                            <span><?php echo htmlspecialchars($sc('contact_phone', '+91 92771 21112')); ?></span>
                        </a>
                    </li>

                    <!-- Compare Button -->
                    <li class="nav-item ms-2">
                        <a href="<?php echo BASE_URL; ?>/compare" class="btn btn-outline-info btn-sm position-relative">
                            <i class="fas fa-balance-scale"></i> Compare
                            <span id="compareBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" class="style-62224">0</span>
                        </a>
                    </li>

                    <!-- Admin Login Button (when not logged in) -->
                    <?php if (!$isLoggedIn): ?>
                        <li class="nav-item ms-2">
                            <a href="<?php echo BASE_URL; ?>/admin/login" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-lock me-1"></i>
                                Admin Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mobile Drawer (inside <header> to prevent orphaned <li> elements) -->
    <?php require __DIR__ . '/../components/navigation/mobile_drawer.php'; ?>
</header>

<?php require __DIR__ . '/../components/navigation/mobile_bottom_nav.php'; ?>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
window.BASE_URL = '<?php echo BASE_URL; ?>';

/**
 * Unified drawer toggle â€” replaces openMenu/closeMenu pattern.
 *   toggleDrawer(event)      â†’ toggles open state
 *   toggleDrawer(null,'close') â†’ closes
 *   toggleDrawer(null,'open')  â†’ opens
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
        _drawerPreviousFocus = document.activeElement;
        drawer.classList.add('active');
        overlay?.classList.add('active');
        header?.classList.add('menu-open');
        document.body.classList.add('mobile-drawer-open');
        if (toggler) toggler.setAttribute('aria-expanded', 'true');
        drawer.setAttribute('aria-hidden', 'false');
        var firstFocusable = drawer.querySelector('a, button, input, [tabindex]:not([tabindex="-1"])');
        if (firstFocusable) setTimeout(function() { firstFocusable.focus(); }, 100);
        if (navigator.vibrate) navigator.vibrate([10]);
        if (typeof window.apsAnnounce === 'function') window.apsAnnounce('Navigation menu opened');
    } else {
        drawer.classList.remove('active');
        overlay?.classList.remove('active');
        header?.classList.remove('menu-open');
        document.body.classList.remove('mobile-drawer-open');
        if (toggler) toggler.setAttribute('aria-expanded', 'false');
        drawer.setAttribute('aria-hidden', 'true');
        if (_drawerPreviousFocus && _drawerPreviousFocus.focus) {
            _drawerPreviousFocus.focus();
            _drawerPreviousFocus = null;
        }
        if (navigator.vibrate) navigator.vibrate([5, 15, 5]);
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

    // Mobile drawer dropdowns â€” accordion-style
    function setupDrawerDropdowns() {
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

    // Keyboard Trap for Drawer (Accessibility)
    drawer.addEventListener('keydown', function(e) {
        if (e.key !== 'Tab' || !drawer.classList.contains('active')) return;

        var focusables = drawer.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        if (focusables.length === 0) return;

        var first = focusables[0];
        var last = focusables[focusables.length - 1];

        if (e.shiftKey) {
            if (document.activeElement === first) {
                e.preventDefault();
                last.focus();
            }
        } else {
            if (document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });

    // Swipe-to-Close Drawer Gesture (Mobile)
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
        if (deltaY > 30) {
            _swipeTracking = false;
            return;
        }
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

    // Smart Auto-Hiding Header
    var lastScrollY = 0;
    var headerHidden = false;
    var scrollTick = false;

    function updateHeaderOnScroll() {
        var currentY = window.scrollY;
        var delta = currentY - lastScrollY;

        if (drawer.classList.contains('active') || currentY < 10) {
            if (headerHidden) {
                header.style.transform = 'translateY(0)';
                header.classList.remove('header-hidden');
                headerHidden = false;
            }
            if (currentY < 10) {
                header.classList.remove('header-scrolled');
            }
            lastScrollY = currentY;
            scrollTick = false;
            return;
        }

        if (isMobile()) {
            if (delta > 8 && !headerHidden) {
                header.style.transform = 'translateY(-100%)';
                header.classList.add('header-hidden');
                headerHidden = true;
            } else if (delta < -8 && headerHidden) {
                header.style.transform = 'translateY(0)';
                header.classList.remove('header-hidden');
                headerHidden = false;
            }
        } else {
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

<!-- ARIA Live Region -->
<div id="ariaLiveRegion" aria-live="polite" aria-atomic="true" class="d-none"></div>
<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function announce(message) {
    var region = document.getElementById('ariaLiveRegion');
    if (region) {
        region.textContent = '';
        setTimeout(function() { region.textContent = message; }, 50);
    }
}
window.apsAnnounce = announce;
</script>

<!-- Command Palette (Ctrl+K) -->
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

    function getSearchResults(query) {
        var q = query.toLowerCase().trim();
        if (q.length < 1) return [];

        var matches = commands.filter(function(c) {
            return c.label.toLowerCase().indexOf(q) !== -1;
        }).slice(0, 5);

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
            results.innerHTML = '<div class="style-68356">No results found</div>';
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

    // Ctrl+K / Cmd+K
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (isOpen) close(); else open();
        }
        if (e.key === 'Escape' && isOpen) {
            close();
        }
    });

    input.addEventListener('input', function() {
        var q = input.value.trim();
        if (q.length === 0) {
            render(commands.slice(0, 8));
        } else {
            render(getSearchResults(q));
        }
    });

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

    results.addEventListener('click', function(e) {
        var item = e.target.closest('.command-palette-item');
        if (item) {
            var idx = parseInt(item.getAttribute('data-cmd-idx'), 10);
            if (currentItems[idx]) saveRecentSearch(currentItems[idx]);
        }
    });

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) close();
    });

    window.openCommandPalette = open;
    window.closeCommandPalette = close;
})();
</script>

<!-- Quick Search Typeahead -->
<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    var input = document.getElementById('quickSearchInput');
    var dropdown = document.getElementById('quickSearchResults');
    if (!input || !dropdown) return;

    var debounceTimer = null;
    var activeIndex = -1;
    var lastResults = [];

    function renderResults(results) {
        if (!results.length) {
            dropdown.innerHTML =
                '<div class="quick-search-result text-muted"><i class="fas fa-info-circle"></i><span class="label">No matches found</span></div>';
            dropdown.style.display = 'block';
            return;
        }

        var html = '';
        results.forEach(function(r, i) {
            var icon = r.type === 'property' ? 'fa-building' :
                r.type === 'location' ? 'fa-map-marker-alt' :
                'fa-tag';
            html += '<a href="' + r.url + '" class="quick-search-result" data-idx="' + i + '">'
                + '<i class="fas ' + icon + '"></i>'
                + '<span class="label">' + escapeHtml(r.label) + '</span>'
                + '<span class="type-tag">' + r.type + '</span>'
                + '</a>';
        });
        html += '<div class="quick-search-footer">'
            + '<a href="' + BASE_URL + '/properties?q=' + encodeURIComponent(input.value) + '" class="text-primary small text-decoration-none">'
            + '<i class="fas fa-search me-1"></i>See all results for "' + escapeHtml(input.value) + '"'
            + '</a></div>';
        dropdown.innerHTML = html;
        dropdown.style.display = 'block';

        dropdown.querySelectorAll('.quick-search-result').forEach(function(el) {
            el.addEventListener('mouseenter', function() {
                activeIndex = parseInt(el.dataset.idx);
                updateActive();
            });
        });
    }

    function escapeHtml(s) {
        return String(s || '').replace(/[&<>"']/g, function(c) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[c];
        });
    }

    function updateActive() {
        dropdown.querySelectorAll('.quick-search-result').forEach(function(el, i) {
            el.classList.toggle('active', i === activeIndex);
        });
    }

    function search(q) {
        if (q.length < 2) {
            dropdown.style.display = 'none';
            return;
        }
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            fetch(BASE_URL + '/api/saved-searches/autocomplete?q=' + encodeURIComponent(q))
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        lastResults = d.results;
                        renderResults(d.results);
                    }
                })
                .catch(function() {});
        }, 200);
    }

    input.addEventListener('input', function(e) {
        activeIndex = -1;
        search(e.target.value.trim());
    });

    input.addEventListener('focus', function() {
        if (input.value.trim().length >= 2 && lastResults.length) {
            dropdown.style.display = 'block';
        }
    });

    input.addEventListener('keydown', function(e) {
        var items = dropdown.querySelectorAll('.quick-search-result');
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

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
})();

function quickSearchSubmit(e) {
    e.preventDefault();
    var q = document.getElementById('quickSearchInput').value.trim();
    if (q) window.location.href = BASE_URL + '/properties?q=' + encodeURIComponent(q);
    return false;
}
</script>

<!-- Mobile Bottom Nav Active State Highlighting -->
<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    var bottomNav = document.querySelector('.mobile-bottom-nav');
    if (!bottomNav) return;

    var path = window.location.pathname;
    var navMap = {
        'home': ['/'],
        'properties': ['/properties'],
        'plots': ['/plots'],
        'projects': ['/projects'],
        'contact': ['/contact'],
        'notifications': ['/user/notifications', '/employee/notifications'],
        'login': ['/login', '/associate/login', '/agent/login', '/employee/login', '/farmer/login'],
        'dashboard': ['/user/dashboard', '/associate/dashboard', '/agent/dashboard', '/admin/dashboard', '/employee/dashboard'],
        'profile': ['/user/profile', '/associate/profile', '/agent/profile', '/admin/profile', '/employee/profile'],
        'about': ['/about'],
        'search': ['/search']
    };

    var items = bottomNav.querySelectorAll('.nav-item');
    items.forEach(function(item) {
        var navKey = item.getAttribute('data-nav-item');
        if (!navKey || !navMap[navKey]) return;

        var active = navMap[navKey].some(function(url) {
            if (url === '/' && path === '/') return true;
            return path === url || path.startsWith(url + '?') || path === url.replace(/\?$/, '');
        });

        if (active) {
            item.classList.add('active');
            var link = item.querySelector('a');
            if (link) link.classList.add('active');
        }
    });
})();
</script>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>" src="<?php echo BASE_URL; ?>/js/visitor-tracking.js" defer></script>
<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    /* Fix navbar alignment - logo strictly on the left */
    .premium-header .navbar {
        display: flex !important;
        align-items: center !important;
        flex-wrap: nowrap;
        padding: 0.5rem 0 !important;
    }

    .premium-header .container {
        display: flex !important;
        align-items: center !important;
        flex-wrap: nowrap;
        width: 100%;
        position: relative;
    }

    .premium-header .navbar-brand {
        flex: 0 0 auto !important;
        margin-right: auto !important;
        margin-left: 0 !important;
        padding: 0 !important;
    }

    .premium-header .navbar-toggler {
        order: 2;
        flex: 0 0 auto;
    }

    .premium-header .navbar-collapse {
        flex: 0 1 auto;
        order: 3;
    }

    .premium-header .navbar-nav {
        flex: 0 0 auto;
    }

    /* Main content padding */
    #main-content {
        padding-top: 80px !important;
    }

    @media (max-width: 768px) {
        #main-content {
            padding-top: 70px !important;
        }

        .premium-header .navbar-brand img {
            height: 28px !important;
            max-width: 100px !important;
        }

        .premium-header .navbar-brand span {
            font-size: 0.9rem !important;
        }

        .premium-header .navbar-collapse {
            width: 100%;
        }

        .premium-header .navbar-nav {
            width: 100%;
            flex-direction: column;
        }
    }

    @media (max-width: 1199px) {
        .premium-header .navbar-nav.d-none.d-xl-flex {
            display: none !important;
        }
    }
</style>