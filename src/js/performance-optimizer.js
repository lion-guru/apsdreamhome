// Performance Optimization Script
(function() {
    // Lazy Load Images
    function lazyLoadImages() {
        const images = document.querySelectorAll('img[data-src]');
        const config = {
            rootMargin: '50px 0px',
            threshold: 0.01
        };

        let observer = new IntersectionObserver((entries, self) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    let image = entry.target;
                    image.src = image.dataset.src;
                    image.removeAttribute('data-src');
                    self.unobserve(image);
                }
            });
        }, config);

        images.forEach(image => {
            observer.observe(image);
        });
    }

    // Toast Notification System
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.classList.add('toast', `toast-${type}`);
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Performance Metrics Tracking
    function trackPerformance() {
        if ('performance' in window) {
            const metrics = {
                loadTime: performance.now(),
                resourceList: performance.getEntriesByType('resource')
            };
            console.log('Performance Metrics:', metrics);
        }
    }

    // Debounce Function
    function debounce(func, wait = 250) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', () => {
        lazyLoadImages();
        trackPerformance();
    });

    // Expose utility functions globally
    window.showToast = showToast;
    window.debounce = debounce;
})();
