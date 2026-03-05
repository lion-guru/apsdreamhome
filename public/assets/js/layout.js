/**
 * APS Dream Home - Layout JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('[data-bs-toggle="mobile-menu"]');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('show');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (mobileMenu && !mobileMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
            mobileMenu.classList.remove('show');
        }
    });
    
    // Sticky header
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                header.classList.add('sticky');
            } else {
                header.classList.remove('sticky');
            }
        });
    }
    
    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const target = document.querySelector(targetId);
            if (target) {
                apsUtils.scrollToElement(target);
            }
        });
    });
    
    // Search functionality
    const searchForm = document.querySelector('#searchForm');
    const searchInput = document.querySelector('#searchInput');
    
    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = searchInput.value.trim();
            if (query) {
                window.location.href = `/properties?search=${encodeURIComponent(query)}`;
            }
        });
    }
    
    // Newsletter subscription
    const newsletterForm = document.querySelector('#newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value.trim();
            
            if (!email) {
                apsUtils.showNotification('Please enter your email address', 'warning');
                return;
            }
            
            if (!apsUtils.isValidEmail(email)) {
                apsUtils.showNotification('Please enter a valid email address', 'warning');
                return;
            }
            
            // Show loading
            apsUtils.showLoading();
            
            // Submit newsletter subscription
            apsUtils.ajax('/api/newsletter/subscribe', {
                method: 'POST',
                body: JSON.stringify({ email: email })
            })
            .then(function(response) {
                apsUtils.hideLoading();
                if (response.success) {
                    apsUtils.showNotification('Successfully subscribed to newsletter!', 'success');
                    newsletterForm.reset();
                } else {
                    apsUtils.showNotification(response.message || 'Subscription failed', 'error');
                }
            })
            .catch(function(error) {
                apsUtils.hideLoading();
                apsUtils.showNotification('Network error. Please try again.', 'error');
            });
        });
    }
    
    // Property type filters
    const propertyTypeFilters = document.querySelectorAll('.property-type-filter');
    propertyTypeFilters.forEach(function(filter) {
        filter.addEventListener('change', function() {
            const type = this.value;
            if (type) {
                window.location.href = `/properties?type=${type}`;
            } else {
                window.location.href = '/properties';
            }
        });
    });
    
    // Property sorting
    const propertySort = document.querySelector('#propertySort');
    if (propertySort) {
        propertySort.addEventListener('change', function() {
            const sort = this.value;
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('sort', sort);
            window.location.href = currentUrl.toString();
        });
    }
    
    // Lazy loading for images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.getAttribute('data-src');
                img.classList.add('loaded');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(function(img) {
        imageObserver.observe(img);
    });
    
    // Initialize AOS if available
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    }
});

// Handle page transitions
window.addEventListener('beforeunload', function() {
    document.body.classList.add('page-transitioning');
});

window.addEventListener('load', function() {
    document.body.classList.remove('page-transitioning');
});
