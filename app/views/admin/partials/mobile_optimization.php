<!-- Mobile Optimization Component -->
<style>
/* Mobile-First Responsive Design */
@media (max-width: 576px) {
    .container-fluid {
        padding: 0.5rem !important;
    }
    
    .card {
        margin-bottom: 1rem;
        border-radius: 0.5rem;
    }
    
    .card-body {
        padding: 1rem !important;
    }
    
    .btn {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
    
    .btn-group .btn {
        margin-bottom: 0;
    }
    
    .table {
        font-size: 0.85rem;
    }
    
    .table th,
    .table td {
        padding: 0.5rem;
        vertical-align: middle;
    }
    
    .stats-card {
        margin-bottom: 1rem;
    }
    
    .stats-icon {
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
    }
    
    .form-control,
    .form-select {
        font-size: 1rem;
        padding: 0.75rem;
        border-radius: 0.5rem;
    }
    
    .input-group-text {
        padding: 0.75rem;
    }
    
    h1, h2, h3, h4, h5, h6 {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }
    
    .breadcrumb {
        background: transparent;
        padding: 0.5rem 0;
        margin-bottom: 1rem;
    }
    
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
    
    .mobile-menu-toggle {
        display: block !important;
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1000;
        background: #0d6efd;
        color: white;
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem;
        font-size: 1.2rem;
    }
    
    .main-content {
        margin-left: 0 !important;
        padding: 1rem !important;
    }
    
    /* Touch-friendly buttons */
    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    /* Mobile table scroll */
    .table-responsive {
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    /* Mobile search optimization */
    .search-bar-mobile {
        position: sticky;
        top: 0;
        background: white;
        z-index: 100;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    /* Mobile stats cards */
    .mobile-stats-row {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .mobile-stats-row .col-md-3 {
        width: 100% !important;
        flex: 0 0 100%;
        max-width: 100%;
    }
}

@media (min-width: 577px) and (max-width: 768px) {
    .container-fluid {
        padding: 1rem !important;
    }
    
    .stats-card {
        margin-bottom: 1rem;
    }
    
    .btn {
        padding: 0.6rem 1rem;
        font-size: 0.95rem;
    }
    
    .table {
        font-size: 0.9rem;
    }
    
    .mobile-stats-row .col-md-3 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (min-width: 769px) {
    .mobile-menu-toggle {
        display: none !important;
    }
    
    .sidebar {
        transform: translateX(0);
    }
}

/* Touch-friendly interactions */
@media (hover: none) and (pointer: coarse) {
    .btn:hover,
    .btn:focus {
        transform: scale(1.02);
        transition: transform 0.1s ease;
    }
    
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transition: box-shadow 0.3s ease;
    }
    
    .table tr:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
}

/* Dark mode support for mobile */
@media (prefers-color-scheme: dark) {
    .card {
        background-color: #2d3748;
        border-color: #4a5568;
        color: #e2e8f0;
    }
    
    .table {
        color: #e2e8f0;
    }
    
    .table th {
        background-color: #4a5568;
        border-color: #718096;
    }
    
    .form-control,
    .form-select {
        background-color: #2d3748;
        border-color: #4a5568;
        color: #e2e8f0;
    }
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Pull-to-refresh indicator */
.pull-to-refresh {
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 0 0 0.5rem 0.5rem;
    margin-bottom: 1rem;
}

.pull-to-refresh .spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #e9ecef;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
</style>

<!-- Mobile Menu Toggle Button -->
<button class="mobile-menu-toggle d-md-none" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Mobile Optimization Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
    
    // Touch-friendly interactions
    if ('ontouchstart' in window) {
        document.body.classList.add('touch-device');
        
        // Add touch feedback to buttons
        const buttons = document.querySelectorAll('.btn, .card');
        buttons.forEach(button => {
            button.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
            });
            
            button.addEventListener('touchend', function() {
                this.style.transform = 'scale(1)';
            });
        });
    }
    
    // Mobile table optimization
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        const wrapper = document.createElement('div');
        wrapper.className = 'table-responsive';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });
    
    // Mobile search optimization
    const searchInput = document.getElementById('globalSearch');
    if (searchInput) {
        searchInput.parentElement.classList.add('search-bar-mobile');
    }
    
    // Pull-to-refresh functionality
    let startY = 0;
    let isPulling = false;
    
        document.addEventListener('touchstart', function(e) {
        if (window.scrollY === 0) {
            startY = e.touches[0].clientY;
            isPulling = true;
        }
    });
    
    document.addEventListener('touchmove', function(e) {
        if (isPulling && window.scrollY === 0) {
            const currentY = e.touches[0].clientY;
            const diff = currentY - startY;
            
            if (diff > 50) {
                showPullToRefresh();
            }
        }
    });
    
    document.addEventListener('touchend', function() {
        if (isPulling) {
            hidePullToRefresh();
            isPulling = false;
        }
    });
    
    function showPullToRefresh() {
        let indicator = document.querySelector('.pull-to-refresh');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'pull-to-refresh';
            indicator.innerHTML = '<div class="spinner"></div><span class="ms-2">Refreshing...</span>';
            document.body.insertBefore(indicator, document.body.firstChild);
        }
        indicator.style.display = 'flex';
    }
    
    function hidePullToRefresh() {
        const indicator = document.querySelector('.pull-to-refresh');
        if (indicator) {
            indicator.style.display = 'none';
        }
    }
    
    // Mobile viewport height fix
    function setViewportHeight() {
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }
    
    setViewportHeight();
    window.addEventListener('resize', setViewportHeight);
    
    // Mobile performance optimization
    if (window.innerWidth <= 768) {
        // Reduce animations on mobile
        document.body.style.setProperty('--transition-duration', '0.2s');
        
        // Optimize images for mobile
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            img.loading = 'lazy';
        });
    }
});

// Mobile-specific helper functions
window.mobileHelpers = {
    isMobile: function() {
        return window.innerWidth <= 768;
    },
    
    isTouch: function() {
        return 'ontouchstart' in window;
    },
    
    showToast: function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close float-end" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 3000);
    },
    
    vibrate: function(pattern = [100, 50, 100]) {
        if ('vibrate' in navigator) {
            navigator.vibrate(pattern);
        }
    }
};
</script>
