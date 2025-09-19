// Main JavaScript file for APS Dream Homes website

import './tilt';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize smooth scrolling
    initSmoothScroll();
    
    // Initialize form validation
    initFormValidation();
    
    // Initialize lazy loading for images
    initLazyLoading();
    
    // Initialize popup handling
    initPopupHandling();
});

// Smooth scrolling for anchor links
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

// Form validation
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    
    // Validate required fields
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            showError(field, 'This field is required');
        } else {
            clearError(field);
        }
    });
    
    // Validate email fields
    form.querySelectorAll('input[type="email"]').forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            isValid = false;
            showError(field, 'Please enter a valid email address');
        }
    });
    
    return isValid;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showError(field, message) {
    const errorDiv = field.nextElementSibling?.classList.contains('error-message') ?
        field.nextElementSibling :
        createErrorElement();
    
    errorDiv.textContent = message;
    if (!field.nextElementSibling?.classList.contains('error-message')) {
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
    field.classList.add('error');
}

function clearError(field) {
    const errorDiv = field.nextElementSibling;
    if (errorDiv?.classList.contains('error-message')) {
        errorDiv.remove();
    }
    field.classList.remove('error');
}

function createErrorElement() {
    const div = document.createElement('div');
    div.className = 'error-message';
    return div;
}

// Lazy loading for images
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy-load');
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img.lazy-load').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Popup handling
function initPopupHandling() {
    const popup = document.getElementById('specialOfferPopup');
    if (!popup) return;

    // Show popup after 5 seconds
    setTimeout(() => {
        popup.style.display = 'flex';
    }, 5000);

    // Close popup when clicking outside
    popup.addEventListener('click', (e) => {
        if (e.target === popup) {
            closePopup();
        }
    });

    // Close popup with escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && popup.style.display === 'flex') {
            closePopup();
        }
    });
}

function closePopup() {
    const popup = document.getElementById('specialOfferPopup');
    if (popup) {
        popup.style.display = 'none';
    }
}