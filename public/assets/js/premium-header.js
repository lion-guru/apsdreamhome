document.addEventListener('DOMContentLoaded', function() {
    // Header scroll behavior
    var header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            // Keep header stably fixed at top without scroll jump
            header.style.transform = 'translateY(0)';
        });
    }

    // Mobile drawer toggle
    function initMobileDrawer() {
        var toggleBtn = document.querySelector('[data-mobile-drawer-toggle]');
        var drawer = document.querySelector('.mobile-drawer');
        var overlay = document.querySelector('.mobile-drawer-overlay');

        if (!toggleBtn || !drawer || !overlay) return;

        function openDrawer() {
            drawer.classList.add('active');
            overlay.classList.add('active');
            document.body.classList.add('mobile-drawer-open');
            toggleBtn.setAttribute('aria-expanded', 'true');
        }

        function closeDrawer() {
            drawer.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('mobile-drawer-open');
            toggleBtn.setAttribute('aria-expanded', 'false');
        }

        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (drawer.classList.contains('active')) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });

        overlay.addEventListener('click', closeDrawer);

        // Close drawer on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && drawer.classList.contains('active')) {
                closeDrawer();
            }
        });

        // Close drawer on window resize if desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1200 && drawer.classList.contains('active')) {
                closeDrawer();
            }
        });

        // Mobile submenu toggle
        var submenuToggles = drawer.querySelectorAll('[data-submenu-toggle]');
        submenuToggles.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var submenu = this.nextElementSibling;
                if (submenu) {
                    submenu.classList.toggle('show');
                    this.setAttribute('aria-expanded', submenu.classList.contains('show'));
                    var icon = this.querySelector('i');
                    if (icon) {
                        if (submenu.classList.contains('show')) {
                            icon.classList.remove('fa-chevron-right');
                            icon.classList.add('fa-chevron-down');
                        } else {
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-right');
                        }
                    }
                }
            });
        });
    }

    // Initialize mobile drawer at the right time
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileDrawer);
    } else {
        initMobileDrawer();
    }

    // Mobile bottom nav active state + haptic feedback
    (function() {
        var bottomNav = document.getElementById('mobileBottomNav');
        if (!bottomNav) return;

        var path = window.location.pathname.replace('/apsdreamhome', '');
        if (path === '') path = '/';

        // Map nav items to their matching paths
        var navMap = {
            'home': ['/'],
            'properties': ['/properties'],
            'plots': ['/plots'],
            'contact': ['/contact'],
            'notifications': ['/user/notifications'],
            'login': ['/login', '/associate/login', '/agent/login', '/employee/login', '/farmer/login']
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
            }
        });

        // Haptic feedback on mobile nav taps
        if ('vibrate' in navigator) {
            items.forEach(function(item) {
                item.addEventListener('click', function() {
                    try { navigator.vibrate([8]); } catch (e) {}
                });
            });
        }
    })();

    // Smooth scrolling for anchor links (skip dropdown toggles and tab toggles)
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        if (anchor.hasAttribute('data-bs-toggle')) return;
        if (anchor.getAttribute('role') === 'tab') return;
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Mobile swipe gestures for property cards
    (function() {
        var isMobile = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        if (!isMobile) return;

        var cards = document.querySelectorAll('.property-card-swipeable');
        if (!cards.length) return;

        var touchStartX = 0;
        var touchEndX = 0;
        var swipeThreshold = 50;

        cards.forEach(function(card) {
            var cardBody = card.querySelector('.card-body');
            var swipeActions = card.querySelector('.swipe-actions');
            if (!cardBody || !swipeActions) return;

            card.addEventListener('touchstart', function(e) {
                touchStartX = e.touches[0].clientX;
            }, { passive: true });

            card.addEventListener('touchmove', function(e) {
                touchEndX = e.touches[0].clientX;
                var deltaX = touchStartX - touchEndX;

                if (deltaX > 30) {
                    cardBody.style.transform = 'translateX(-60px)';
                    swipeActions.style.display = 'flex';
                    card.classList.add('swipe-reveal');
                } else if (deltaX < -30) {
                    cardBody.style.transform = 'translateX(0)';
                    swipeActions.style.display = 'none';
                    card.classList.remove('swipe-reveal');
                }
            }, { passive: true });

            card.addEventListener('touchend', function() {
                var deltaX = touchStartX - touchEndX;
                if (Math.abs(deltaX) < swipeThreshold) {
                    cardBody.style.transform = 'translateX(0)';
                    swipeActions.style.display = 'none';
                    card.classList.remove('swipe-reveal');
                }
            });
        });
    })();
});
