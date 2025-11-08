/**
 * APS Dream Home - JavaScript Utilities
 * Common functionality shared across multiple pages and components
 *
 * This file provides shared utilities to reduce code duplication and
 * improve maintainability across the application.
 */

// =============================================
// COMMON INITIALIZATION UTILITIES
// =============================================

/**
 * Initialize AOS (Animate On Scroll) animations
 * Used by: downloads.js, faq.js, news.js, testimonials.js, financial-services.js, legal-services.js
 */
export function initAOS() {
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });
    }
}

/**
 * Initialize Bootstrap tooltips
 * Used by: custom.js, properties.js, property-cards.js
 */
export function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize smooth scrolling for anchor links
 * Used by: custom.js, financial-services.js, legal-services.js
 */
export function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Initialize lazy loading for images
 * Used by: custom.js, financial-services.js, legal-services.js
 */
export function initLazyLoading() {
    if (typeof LazyLoad !== 'undefined') {
        new LazyLoad({
            elements_selector: '.lazy',
            threshold: 100,
            callback_loaded: (img) => {
                img.classList.add('loaded');
            }
        });
    }
}

// =============================================
// FORM UTILITIES
// =============================================

/**
 * Initialize common form handling
 * Used by: financial-services.js, legal-services.js
 */
export function initFormHandling() {
    // Add common form validation and submission handling
    const forms = document.querySelectorAll('form[data-ajax="true"]');
    forms.forEach(form => {
        form.addEventListener('submit', handleFormSubmission);
    });
}

/**
 * Handle form submission with loading states
 */
function handleFormSubmission(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;

    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

    // Submit form data
    const formData = new FormData(form);

    fetch(form.action, {
        method: form.method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('Success! Your request has been submitted.', 'success');
            form.reset();
        } else {
            // Show error message
            showNotification(data.message || 'An error occurred. Please try again.', 'error');
        }
    })
    .catch(error => {
        showNotification('Network error. Please check your connection.', 'error');
    })
    .finally(() => {
        // Restore button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

/**
 * Initialize phone number formatting
 * Used by: financial-services.js, legal-services.js
 */
export function initPhoneFormatting(input) {
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');

        // Format for Indian phone numbers
        if (value.length >= 10) {
            value = value.substring(0, 10);
            e.target.value = value.replace(/(\d{5})(\d{5})/, '$1 $2');
        } else {
            e.target.value = value;
        }
    });
}

// =============================================
// UI UTILITIES
// =============================================

/**
 * Show notification messages
 */
export function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

/**
 * Debounce function for search inputs
 * Used by: faq.js, news.js, downloads.js
 */
export function debounce(func, wait) {
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

// =============================================
// ANIMATION UTILITIES
// =============================================

/**
 * Add AOS animations to cards with staggered delays
 * Used by: testimonials.js, downloads.js, news.js
 */
export function addStaggeredAnimations(selector, baseDelay = 100) {
    const cards = document.querySelectorAll(selector);
    cards.forEach((card, index) => {
        card.setAttribute('data-aos', 'fade-up');
        card.setAttribute('data-aos-delay', (index % 3) * baseDelay);
    });
}

/**
 * Initialize loading overlay
 */
export function showLoading() {
    if (!document.getElementById('loadingOverlay')) {
        const overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        overlay.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;
        document.body.appendChild(overlay);
    }
    document.getElementById('loadingOverlay').style.display = 'flex';
}

/**
 * Hide loading overlay
 */
export function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// =============================================
// VALIDATION UTILITIES
// =============================================

/**
 * Validate email format
 */
export function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate phone number (Indian format)
 */
export function isValidPhone(phone) {
    const phoneRegex = /^[6-9]\d{9}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
}

// =============================================
// INITIALIZATION
// =============================================

/**
 * Initialize all common utilities when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize common utilities that don't conflict with page-specific code
    initTooltips();
    initSmoothScrolling();
    initLazyLoading();
});

// Export for use in ES6 modules
export {
    initAOS,
    initTooltips,
    initSmoothScrolling,
    initLazyLoading,
    initFormHandling,
    initPhoneFormatting,
    showNotification,
    debounce,
    addStaggeredAnimations,
    showLoading,
    hideLoading,
    isValidEmail,
    isValidPhone
};
