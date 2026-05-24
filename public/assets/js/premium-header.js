document.addEventListener('DOMContentLoaded', function() {
    // Header scroll behavior
    var header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Smooth scrolling for anchor links (skip dropdown toggles and tab toggles)
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        if (anchor.hasAttribute('data-bs-toggle')) return;
        if (anchor.getAttribute('role') === 'tab') return;
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
