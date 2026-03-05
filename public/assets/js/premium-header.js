/**
 * APS Dream Home - Premium Header Effects
 */

document.addEventListener('DOMContentLoaded', function() {
    // Header elements
    const header = document.querySelector('header');
    const headerSpacer = document.querySelector('.header-spacer');
    
    if (!header) return;
    
    // Dynamic header spacer
    function updateHeaderSpacer() {
        if (headerSpacer) {
            const headerHeight = header.offsetHeight;
            headerSpacer.style.height = headerHeight + 'px';
        }
    }
    
    updateHeaderSpacer();
    
    // Update on resize
    window.addEventListener('resize', apsUtils.debounce(updateHeaderSpacer, 250));
    
    // Scroll effects
    let lastScrollTop = 0;
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Hide/show header on scroll
        if (scrollTop > lastScrollTop) {
            // Scrolling down
            header.classList.add('header-hidden');
            header.classList.remove('header-visible');
        } else {
            // Scrolling up
            header.classList.add('header-visible');
            header.classList.remove('header-hidden');
        }
        
        // Add background when scrolled
        if (scrollTop > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        lastScrollTop = scrollTop;
    });
    
    // Mega menu functionality
    const megaMenuTriggers = document.querySelectorAll('.mega-menu-trigger');
    megaMenuTriggers.forEach(function(trigger) {
        trigger.addEventListener('mouseenter', function() {
            const megaMenu = this.nextElementSibling;
            if (megaMenu && megaMenu.classList.contains('mega-menu')) {
                megaMenu.classList.add('show');
                // Add overlay
                const overlay = document.createElement('div');
                overlay.className = 'mega-menu-overlay';
                document.body.appendChild(overlay);
            }
        });
        
        trigger.addEventListener('mouseleave', function() {
            const megaMenu = this.nextElementSibling;
            if (megaMenu && megaMenu.classList.contains('mega-menu')) {
                megaMenu.classList.remove('show');
                // Remove overlay
                const overlay = document.querySelector('.mega-menu-overlay');
                if (overlay) {
                    overlay.remove();
                }
            }
        });
    });
    
    // Search bar animation
    const searchToggle = document.querySelector('.search-toggle');
    const searchBox = document.querySelector('.search-box');
    
    if (searchToggle && searchBox) {
        searchToggle.addEventListener('click', function() {
            searchBox.classList.toggle('show');
            if (searchBox.classList.contains('show')) {
                searchBox.querySelector('input').focus();
            }
        });
        
        // Close search on escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchBox.classList.contains('show')) {
                searchBox.classList.remove('show');
            }
        });
        
        // Close search on outside click
        document.addEventListener('click', function(e) {
            if (!searchBox.contains(e.target) && !searchToggle.contains(e.target)) {
                searchBox.classList.remove('show');
            }
        });
    }
    
    // User dropdown
    const userDropdown = document.querySelector('.user-dropdown');
    const userDropdownToggle = document.querySelector('.user-dropdown-toggle');
    
    if (userDropdown && userDropdownToggle) {
        userDropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target) && !userDropdownToggle.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
    }
    
    // Notification bell
    const notificationBell = document.querySelector('.notification-bell');
    const notificationDropdown = document.querySelector('.notification-dropdown');
    
    if (notificationBell && notificationDropdown) {
        notificationBell.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
            
            // Mark as read
            if (notificationDropdown.classList.contains('show')) {
                const unreadCount = notificationBell.querySelector('.unread-count');
                if (unreadCount) {
                    unreadCount.style.display = 'none';
                }
            }
        });
        
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!notificationDropdown.contains(e.target) && !notificationBell.contains(e.target)) {
                notificationDropdown.classList.remove('show');
            }
        });
    }
    
    // Language switcher
    const languageSwitcher = document.querySelector('.language-switcher');
    const languageDropdown = document.querySelector('.language-dropdown');
    
    if (languageSwitcher && languageDropdown) {
        languageSwitcher.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            languageDropdown.classList.toggle('show');
        });
        
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!languageDropdown.contains(e.target) && !languageSwitcher.contains(e.target)) {
                languageDropdown.classList.remove('show');
            }
        });
        
        // Handle language selection
        const languageOptions = languageDropdown.querySelectorAll('.language-option');
        languageOptions.forEach(function(option) {
            option.addEventListener('click', function() {
                const lang = this.getAttribute('data-lang');
                // Store language preference
                localStorage.setItem('preferred-language', lang);
                // Reload page with new language
                window.location.reload();
            });
        });
    }
    
    // Mobile menu enhancements
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const mobileMenu = document.querySelector('.mobile-navigation');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('show');
            document.body.classList.toggle('mobile-menu-open');
        });
        
        // Close mobile menu on escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu.classList.contains('show')) {
                mobileMenu.classList.remove('show');
                document.body.classList.remove('mobile-menu-open');
            }
        });
    }
    
    // Add CSS for header effects
    const headerStyles = document.createElement('style');
    headerStyles.textContent = `
        .header-spacer {
            transition: height 0.3s ease;
        }
        
        header {
            transition: transform 0.3s ease, background-color 0.3s ease;
        }
        
        header.header-hidden {
            transform: translateY(-100%);
        }
        
        header.header-visible {
            transform: translateY(0);
        }
        
        header.scrolled {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        .mega-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .mega-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .mega-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .search-box {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .search-box.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .user-dropdown, .notification-dropdown, .language-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            min-width: 200px;
        }
        
        .user-dropdown.show, .notification-dropdown.show, .language-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .mobile-navigation {
            position: fixed;
            top: 0;
            left: -100%;
            width: 280px;
            height: 100vh;
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: left 0.3s ease;
            z-index: 1001;
            overflow-y: auto;
        }
        
        .mobile-navigation.show {
            left: 0;
        }
        
        .mobile-menu-open {
            overflow: hidden;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-button {
                display: block !important;
            }
        }
    `;
    document.head.appendChild(headerStyles);
});
