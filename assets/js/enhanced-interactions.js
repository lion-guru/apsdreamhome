/**
 * APS Dream Home - Enhanced UI Interactions
 * Modern JavaScript for better user experience
 */

document.addEventListener('DOMContentLoaded', function() {

    // ========================================
    // MOBILE NAVIGATION
    // ========================================

    const mobileNavToggler = document.getElementById('mobile-nav-toggler');
    const navigation = document.getElementById('navigation');

    if (mobileNavToggler && navigation) {
        mobileNavToggler.addEventListener('click', function() {
            navigation.classList.toggle('active');
            const icon = this.querySelector('i');

            if (navigation.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
                document.body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
                document.body.style.overflow = ''; // Restore scrolling
            }
        });

        // Close mobile menu when clicking on a link
        navigation.addEventListener('click', function(e) {
            if (e.target.tagName === 'A') {
                navigation.classList.remove('active');
                mobileNavToggler.querySelector('i').classList.remove('fa-times');
                mobileNavToggler.querySelector('i').classList.add('fa-bars');
                document.body.style.overflow = '';
            }
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navigation.contains(e.target) && !mobileNavToggler.contains(e.target)) {
                navigation.classList.remove('active');
                mobileNavToggler.querySelector('i').classList.remove('fa-times');
                mobileNavToggler.querySelector('i').classList.add('fa-bars');
                document.body.style.overflow = '';
            }
        });
    }

    // ========================================
    // NAVBAR SCROLL EFFECT
    // ========================================

    const header = document.getElementById('header');
    if (header) {
        const handleScroll = () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        };

        window.addEventListener('scroll', handleScroll);
        handleScroll(); // Initial check
    }

    // ========================================
    // BACK TO TOP BUTTON
    // ========================================

    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        const toggleBackToTop = () => {
            if (window.scrollY > 300) {
                backToTop.style.display = 'flex';
                backToTop.style.opacity = '1';
            } else {
                backToTop.style.opacity = '0';
                setTimeout(() => {
                    backToTop.style.display = 'none';
                }, 300);
            }
        };

        window.addEventListener('scroll', toggleBackToTop);
        toggleBackToTop(); // Initial check

        backToTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // ========================================
    // SMOOTH SCROLLING
    // ========================================

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                const headerHeight = header ? header.offsetHeight : 80;
                const targetPosition = targetElement.offsetTop - headerHeight;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ========================================
    // PROPERTY CARD INTERACTIONS
    // ========================================

    // Favorite Toggle
    document.querySelectorAll('.property-favorite').forEach(fav => {
        fav.addEventListener('click', function(e) {
            e.stopPropagation();
            const icon = this.querySelector('i');

            if (this.classList.contains('liked')) {
                this.classList.remove('liked');
                icon.classList.remove('fas', 'text-danger');
                icon.classList.add('far');
                this.setAttribute('title', 'Add to favorites');
                showNotification('Removed from favorites', 'warning');
            } else {
                this.classList.add('liked');
                icon.classList.remove('far');
                icon.classList.add('fas', 'text-danger');
                this.setAttribute('title', 'Remove from favorites');
                showNotification('Added to favorites', 'success');
            }
        });
    });

    // Property Card Click
    document.querySelectorAll('.property-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on favorite button or action buttons
            if (e.target.closest('.property-favorite') || e.target.closest('.property-actions')) {
                return;
            }

            // Add click animation
            this.style.transform = 'translateY(-5px) scale(1.02)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);

            // Here you would typically navigate to property details
            console.log('Navigate to property details');
        });
    });

    // ========================================
    // CONTACT FORM ENHANCEMENT
    // ========================================

    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // Show loading state
            submitBtn.innerHTML = '<div class="loading-spinner"></div> Sending...';
            submitBtn.disabled = true;

            // Collect form data
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            // Simulate API call
            setTimeout(() => {
                // Show success state
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Sent Successfully!';
                submitBtn.classList.add('btn-success');
                submitBtn.classList.remove('btn-primary');

                showNotification('Message sent successfully! We\'ll get back to you soon.', 'success');

                // Reset form after delay
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('btn-success');
                    submitBtn.classList.add('btn-primary');
                    this.reset();
                }, 3000);
            }, 2000);
        });
    }

    // ========================================
    // SCROLL ANIMATIONS
    // ========================================

    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in-up');

                // Special handling for hero stats
                if (entry.target.classList.contains('hero-stat')) {
                    animateCounter(entry.target);
                }
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.property-card, .card, .hero-stats > *, .section-title, .section-subtitle').forEach(el => {
        observer.observe(el);
    });

    // ========================================
    // COUNTER ANIMATION
    // ========================================

    function animateCounter(element) {
        const numberEl = element.querySelector('.hero-stat-number');
        if (!numberEl) return;

        const target = parseInt(numberEl.textContent.replace(/\D/g, ''));
        const duration = 2000; // 2 seconds
        const start = 0;
        const increment = target / (duration / 16); // 60fps
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            numberEl.textContent = Math.floor(current) + '+';
        }, 16);
    }

    // ========================================
    // NOTIFICATION SYSTEM
    // ========================================

    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">
                <i class="fas fa-times"></i>
            </button>
        `;

        // Add to page
        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);

        // Auto hide after 5 seconds
        setTimeout(() => hideNotification(notification), 5000);

        // Close button functionality
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => hideNotification(notification));
    }

    function hideNotification(notification) {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }

    function getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || icons.info;
    }

    // ========================================
    // LAZY LOADING FOR IMAGES
    // ========================================

    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[loading="lazy"]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // ========================================
    // ENHANCED BUTTON EFFECTS
    // ========================================

    document.querySelectorAll('.btn').forEach(btn => {
        // Ripple effect on click
        btn.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');

            this.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // ========================================
    // ACCESSIBILITY ENHANCEMENTS
    // ========================================

    // Add keyboard navigation for property cards
    document.querySelectorAll('.property-card').forEach(card => {
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');

        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Focus management for mobile menu
    if (mobileNavToggler) {
        mobileNavToggler.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    }

    // ========================================
    // PERFORMANCE OPTIMIZATIONS
    // ========================================

    // Throttle scroll events
    let ticking = false;

    function updateScrollEffects() {
        // Add any scroll-based effects here
        ticking = false;
    }

    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(updateScrollEffects);
            ticking = true;
        }
    });

    // Preload critical resources
    const criticalImages = [
        'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400',
        'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=400'
    ];

    criticalImages.forEach(src => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'image';
        link.href = src;
        document.head.appendChild(link);
    });

    console.log('Enhanced UI Interactions loaded successfully! 🚀');
});
