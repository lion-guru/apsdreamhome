/**
 * APS Dream Home - Animations JavaScript
 * Modern animations and interactions
 */

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', function() {
    initAnimations();
    initScrollAnimations();
    initHoverEffects();
    initCounterAnimations();
    initParallaxEffects();
});

// ===== ANIMATION INITIALIZATION =====
function initAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe all elements with animation classes
    document.querySelectorAll('.animate-fade-in, .animate-slide-in-right, .animate-bounce').forEach(el => {
        observer.observe(el);
    });
}

// ===== SCROLL ANIMATIONS =====
function initScrollAnimations() {
    // Header scroll effect
    const header = document.querySelector('.premium-header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
    
    // Back to top button
    initBackToTop();
}

// ===== BACK TO TOP =====
function initBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });
        
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// ===== HOVER EFFECTS =====
function initHoverEffects() {
    // Card hover effects
    document.querySelectorAll('.property-card, .service-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Button hover effects
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

// ===== COUNTER ANIMATIONS =====
function initCounterAnimations() {
    const counters = document.querySelectorAll('.animate-counter');
    
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.dataset.target);
                const duration = 2000; // 2 seconds
                const increment = target / (duration / 16); // 60fps
                let current = 0;
                
                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        counter.textContent = Math.ceil(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                updateCounter();
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => {
        counterObserver.observe(counter);
    });
}

// ===== PARALLAX EFFECTS =====
function initParallaxEffects() {
    const heroImage = document.querySelector('.hero-image img');
    
    if (heroImage) {
        window.addEventListener('scroll', () => {
            const scrolled = window.scrollY;
            const parallax = scrolled * 0.5;
            heroImage.style.transform = `perspective(1000px) rotateY(-5deg) translateY(${parallax}px)`;
        });
    }
}

// ===== PREMIUM HEADER =====
function initPremiumHeader() {
    const navbar = document.querySelector('.navbar');
    const toggler = document.querySelector('.navbar-toggler');
    const collapse = document.querySelector('.navbar-collapse');
    
    if (toggler && collapse) {
        toggler.addEventListener('click', () => {
            collapse.classList.toggle('show');
        });
    }
    
    // Mobile menu close on outside click
    document.addEventListener('click', (e) => {
        if (!navbar.contains(e.target) && collapse.classList.contains('show')) {
            collapse.classList.remove('show');
        }
    });
}

// ===== LOADING STATES =====
function showLoading(element) {
    element.innerHTML = `
        <div class="d-flex justify-content-center align-items-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
}

function hideLoading(element, content) {
    element.innerHTML = content;
}

// ===== FORM ENHANCEMENTS =====
function initFormEnhancements() {
    // Floating labels
    document.querySelectorAll('.form-control').forEach(input => {
        const label = document.querySelector(`label[for="${input.id}"]`);
        if (label) {
            input.addEventListener('focus', () => {
                label.classList.add('focused');
            });
            
            input.addEventListener('blur', () => {
                label.classList.remove('focused');
            });
        }
    });
    
    // Form validation feedback
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', () => {
            validateField(input);
        });
    });
}

// ===== FIELD VALIDATION =====
function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    
    let isValid = true;
    let errorMessage = '';
    
    // Remove previous error states
    field.classList.remove('is-invalid', 'is-valid');
    
    // Email validation
    if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        isValid = emailRegex.test(value);
        errorMessage = 'Please enter a valid email address';
    }
    
    // Phone validation
    if (type === 'tel' && value) {
        const phoneRegex = /^[6-9]\d{9}$/;
        isValid = phoneRegex.test(value);
        errorMessage = 'Please enter a valid 10-digit phone number';
    }
    
    // Required field validation
    if (required && !value) {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    // Update field appearance
    if (value) {
        if (isValid) {
            field.classList.add('is-valid');
        } else {
            field.classList.add('is-invalid');
            showFieldError(field, errorMessage);
        }
    }
}

// ===== FIELD ERROR DISPLAY =====
function showFieldError(field, message) {
    // Remove existing error
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Create error element
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error text-danger small mt-1';
    errorElement.textContent = message;
    
    field.parentNode.appendChild(errorElement);
}

// ===== SUCCESS MESSAGES =====
function showSuccessMessage(message, type = 'success') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <strong>Success!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// ===== ERROR MESSAGES =====
function showErrorMessage(message) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <strong>Error!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHtml);
}

// ===== UTILITY FUNCTIONS =====
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ===== LAZY LOADING =====
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('loaded');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => {
        imageObserver.observe(img);
    });
}

// ===== SMOOTH SCROLL =====
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// ===== KEYBOARD NAVIGATION =====
function initKeyboardNavigation() {
    document.addEventListener('keydown', (e) => {
        // ESC key to close modals/menus
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                openModal.classList.remove('show');
            }
            
            const openMenu = document.querySelector('.navbar-collapse.show');
            if (openMenu) {
                openMenu.classList.remove('show');
            }
        }
        
        // Tab navigation for forms
        if (e.key === 'Tab') {
            // Handle focus management for accessibility
            const focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
            document.addEventListener('focusin', handleFocusIn);
        }
    });
}

function handleFocusIn(e) {
    // Add focus styles for accessibility
    document.body.classList.add('keyboard-nav');
    
    // Remove keyboard-nav class on mouse interaction
    document.addEventListener('mousedown', () => {
        document.body.classList.remove('keyboard-nav');
    }, { once: true });
}

// ===== PERFORMANCE OPTIMIZATION =====
function optimizeAnimations() {
    // Reduce motion for users who prefer it
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.body.classList.add('reduced-motion');
    }
    
    // Optimize for mobile devices
    if (window.innerWidth < 768) {
        document.body.classList.add('mobile-device');
    }
}

// ===== INITIALIZATION CALLS =====
function initializeAllAnimations() {
    initAnimations();
    initScrollAnimations();
    initHoverEffects();
    initCounterAnimations();
    initParallaxEffects();
    initPremiumHeader();
    initFormEnhancements();
    initLazyLoading();
    initSmoothScroll();
    initKeyboardNavigation();
    optimizeAnimations();
}

// Export for external use
window.APSAnimations = {
    init: initializeAllAnimations,
    showLoading,
    hideLoading,
    showSuccessMessage,
    showErrorMessage,
    debounce,
    throttle
};
