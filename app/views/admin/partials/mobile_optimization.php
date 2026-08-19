<!-- Mobile Optimization — JS only (CSS is in admin.css) -->
<button class="mobile-menu-toggle d-md-none" id="mobileMenuToggle" aria-label="Menu"><i class="fas fa-bars"></i></button>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.sidebar');
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
    function setViewportHeight() {
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', vh + 'px');
    }
    setViewportHeight();
    window.addEventListener('resize', setViewportHeight);
});
</script>
