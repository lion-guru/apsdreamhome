/**
 * APS Dream Home - Premium Header Effects
 * Advanced header functionality with animations and interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    initPremiumHeader();
});

function initPremiumHeader() {
    // Header elements
    const header = document.querySelector('.premium-header');
    const headerSpacer = document.querySelector('.header-spacer');
    const navbar = document.querySelector('.navbar');
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (!header) return;
    
    // Dynamic header spacer
    function updateHeaderSpacer() {
        if (headerSpacer) {
            const headerHeight = header.offsetHeight;
            headerSpacer.style.height = headerHeight + 'px';
        }
    }
    
    updateHeaderSpacer();
    
    // Scroll effects
    let lastScrollTop = 0;
    let scrollDirection = 'down';
    
    function handleScroll() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Determine scroll direction
        if (scrollTop > lastScrollTop) {
            scrollDirection = 'down';
        } else {
            scrollDirection = 'up';
        }
        
        lastScrollTop = scrollTop;
        
        // Add scrolled class
        if (scrollTop > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // Hide/show header on scroll
        if (scrollTop > 200) {
            if (scrollDirection === 'down') {
                header.style.transform = 'translateY(-100%)';
            } else {
                header.style.transform = 'translateY(0)';
            }
        } else {
            header.style.transform = 'translateY(0)';
        }
        
        updateHeaderSpacer();
    }
    
    // Mobile menu functionality
    function setupMobileMenu() {
        if (navbarToggler && navbarCollapse) {
            navbarToggler.addEventListener('click', function() {
                const isExpanded = navbarToggler.getAttribute('aria-expanded') === 'true';
                
                if (isExpanded) {
                    navbarCollapse.classList.remove('show');
                    navbarToggler.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                } else {
                    navbarCollapse.classList.add('show');
                    navbarToggler.setAttribute('aria-expanded', 'true');
                    document.body.style.overflow = 'hidden';
                }
            });
            
            // Close menu on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && navbarCollapse.classList.contains('show')) {
                    navbarCollapse.classList.remove('show');
                    navbarToggler.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                }
            });
            
            // Close menu on outside click
            document.addEventListener('click', function(e) {
                if (!navbar.contains(e.target) && navbarCollapse.classList.contains('show')) {
                    navbarCollapse.classList.remove('show');
                    navbarToggler.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                }
            });
        }
    }
    
    // Active navigation highlighting
    function setupActiveNavigation() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
        
        function updateActiveNav() {
            const scrollPos = window.pageYOffset || document.documentElement.scrollTop;
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                const sectionHeight = section.offsetHeight;
                const sectionId = section.getAttribute('id');
                
                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === '#' + sectionId) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        }
        
        // Update on scroll
        window.addEventListener('scroll', updateActiveNav);
        
        // Update on click
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                setTimeout(updateActiveNav, 100);
            });
        });
    }
    
    // Search functionality
    function setupSearch() {
        const searchToggle = document.querySelector('.search-toggle');
        const searchOverlay = document.querySelector('.search-overlay');
        const searchInput = document.querySelector('.search-input');
        
        if (searchToggle && searchOverlay) {
            searchToggle.addEventListener('click', function() {
                searchOverlay.classList.add('show');
                if (searchInput) {
                    searchInput.focus();
                }
            });
            
            searchOverlay.addEventListener('click', function(e) {
                if (e.target === searchOverlay) {
                    searchOverlay.classList.remove('show');
                }
            });
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && searchOverlay.classList.contains('show')) {
                    searchOverlay.classList.remove('show');
                }
            });
        }
    }
    
    // Initialize all features
    setupMobileMenu();
    setupActiveNavigation();
    setupSearch();
    
    // Event listeners
    window.addEventListener('scroll', handleScroll);
    window.addEventListener('resize', updateHeaderSpacer);
    
    // Initial call
    handleScroll();
    updateHeaderSpacer();
}

// Export for external use
window.initPremiumHeader = initPremiumHeader;
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
