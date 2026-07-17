/**
 * Toast Notifications - APS Dream Home
 * Modern, non-blocking alerts to replace standard browser alerts and flash messages.
 */
(function(window) {
    'use strict';

    const APSToast = {
        container: null,
        
        init() {
            if (this.container) return;
            this.container = document.createElement('div');
            this.container.id = 'aps-toast-container';
            this.container.style.cssText = `
                position: fixed;
                bottom: 24px;
                right: 24px;
                z-index: 100000;
                display: flex;
                flex-direction: column;
                gap: 12px;
                pointer-events: none;
            `;
            document.body.appendChild(this.container);
        },

        /**
         * Show a toast message
         * @param {string} message - The message to display
         * @param {string} type - 'success', 'error', 'info', 'warning'
         * @param {number} duration - Auto-dismiss delay in ms
         */
        show(message, type = 'info', duration = 4000) {
            this.init();

            const toast = document.createElement('div');
            
            // Base styles matching premium theme
            toast.style.cssText = `
                background: var(--premium-white, #FFFFFF);
                border-radius: var(--radius-md, 12px);
                padding: 16px 20px;
                box-shadow: 0 10px 40px -10px rgba(10, 25, 47, 0.15);
                display: flex;
                align-items: center;
                gap: 12px;
                pointer-events: auto;
                transform: translateX(120%);
                opacity: 0;
                transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                min-width: 300px;
                max-width: 400px;
                border-left: 4px solid var(--premium-navy, #0A192F);
            `;

            // Type specifics
            let iconClass = 'fa-solid fa-circle-info';
            let iconColor = 'var(--premium-navy)';

            if (type === 'success') {
                iconClass = 'fa-solid fa-circle-check';
                iconColor = 'var(--premium-emerald, #14B8A6)';
                toast.style.borderLeftColor = iconColor;
            } else if (type === 'error') {
                iconClass = 'fa-solid fa-circle-exclamation';
                iconColor = '#ef4444';
                toast.style.borderLeftColor = iconColor;
            } else if (type === 'warning') {
                iconClass = 'fa-solid fa-triangle-exclamation';
                iconColor = 'var(--premium-gold, #D4AF37)';
                toast.style.borderLeftColor = iconColor;
            }

            toast.innerHTML = `
                <i class="${iconClass}" style="color: ${iconColor}; font-size: 1.25rem;"></i>
                <div style="flex-grow: 1; font-family: var(--font-primary, 'Plus Jakarta Sans', sans-serif); color: var(--premium-dark, #1E293B); font-size: 0.95rem; font-weight: 500;">
                    ${message}
                </div>
                <button aria-label="Close" style="background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

            // Close button listener
            toast.querySelector('button').addEventListener('click', () => {
                this.dismiss(toast);
            });

            this.container.appendChild(toast);

            // Animate in
            requestAnimationFrame(() => {
                toast.style.transform = 'translateX(0)';
                toast.style.opacity = '1';
            });

            // Auto dismiss
            if (duration > 0) {
                setTimeout(() => {
                    this.dismiss(toast);
                }, duration);
            }
        },

        dismiss(toast) {
            toast.style.transform = 'translateX(120%)';
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 400); // match transition duration
        }
    };

    // Expose to global window object
    window.APS = window.APS || {};
    window.APS.toast = APSToast.show.bind(APSToast);

    // Initialize automatically to catch existing DOM flashes
    document.addEventListener('DOMContentLoaded', () => {
        // Auto-convert existing backend flash messages to toasts
        document.querySelectorAll('.alert-success, .alert-danger').forEach(alert => {
            const msg = alert.innerText.replace('×', '').trim();
            if (msg) {
                const type = alert.classList.contains('alert-success') ? 'success' : 'error';
                setTimeout(() => window.APS.toast(msg, type), 500);
            }
            alert.style.display = 'none'; // hide native alert
        });
    });

})(window);
