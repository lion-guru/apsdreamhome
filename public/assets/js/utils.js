/**
 * APS Dream Home - Utility Functions
 */

// Global utility functions
window.apsUtils = {
    // Format currency
    formatCurrency: function(amount, currency = '₹') {
        return currency + parseFloat(amount).toLocaleString('en-IN');
    },
    
    // Format date
    formatDate: function(date, format = 'd M Y') {
        const d = new Date(date);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return format
            .replace('d', d.getDate())
            .replace('M', months[d.getMonth()])
            .replace('Y', d.getFullYear());
    },
    
    // Debounce function
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Show loading
    showLoading: function() {
        const loadingBar = document.getElementById('loadingBar');
        if (loadingBar) {
            loadingBar.style.display = 'block';
        }
    },
    
    // Hide loading
    hideLoading: function() {
        const loadingBar = document.getElementById('loadingBar');
        if (loadingBar) {
            loadingBar.style.display = 'none';
        }
    },
    
    // Show notification
    showNotification: function(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideInRight 0.3s ease-out;
        `;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()">
                <span>&times;</span>
            </button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    },
    
    // AJAX helper
    ajax: function(url, options = {}) {
        return new Promise((resolve, reject) => {
            const defaults = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            const config = Object.assign({}, defaults, options);
            
            fetch(url, config)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => resolve(data))
                .catch(error => reject(error));
        });
    },
    
    // Form validation
    validateForm: function(form) {
        const errors = [];
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(function(input) {
            const value = input.value.trim();
            const required = input.hasAttribute('required');
            const type = input.type || input.tagName.toLowerCase();
            
            // Required validation
            if (required && !value) {
                errors.push(`${input.name || input.placeholder || 'This field'} is required`);
                return;
            }
            
            // Email validation
            if (type === 'email' && value && !apsUtils.isValidEmail(value)) {
                errors.push('Please enter a valid email address');
            }
            
            // Phone validation
            if ((type === 'tel' || input.name === 'phone') && value && !apsUtils.isValidPhone(value)) {
                errors.push('Please enter a valid phone number');
            }
        });
        
        return errors;
    },
    
    // Email validation
    isValidEmail: function(email) {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(email);
    },
    
    // Phone validation
    isValidPhone: function(phone) {
        const pattern = /^[0-9]{10,15}$/;
        return pattern.test(phone.replace(/[\s\-\(\)]/g, ''));
    },
    
    // Smooth scroll
    scrollToElement: function(element) {
        if (element) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    },
    
    // Get URL parameter
    getUrlParameter: function(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    },
    
    // Set URL parameter
    setUrlParameter: function(name, value) {
        const url = new URL(window.location);
        url.searchParams.set(name, value);
        window.history.replaceState({}, '', url);
    }
};

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .position-fixed {
        position: fixed !important;
    }
`;
document.head.appendChild(style);

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any tooltips
    const tooltipTriggers = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggers.forEach(function(trigger) {
        trigger.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'custom-tooltip';
            tooltip.textContent = this.getAttribute('title') || this.getAttribute('data-bs-title');
            tooltip.style.cssText = `
                position: absolute;
                background: #333;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 1000;
                white-space: nowrap;
                top: ${this.offsetTop - 30}px;
                left: ${this.offsetLeft}px;
            `;
            document.body.appendChild(tooltip);
        });
        
        trigger.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.custom-tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
});
