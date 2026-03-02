// Page Transitions and Loading Animation
document.addEventListener('DOMContentLoaded', function() {
    const loadingBar = document.getElementById('loadingBar');
    const pageTransition = document.getElementById('pageTransition');
    const header = document.querySelector('.header');
    let lastScroll = 0;

    // Handle page transitions
    document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"])').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Don't intercept anchor links or external links
            if (href.startsWith('#') || 
                href.startsWith('http') || 
                href.startsWith('mailto:') || 
                href.startsWith('tel:')) {
                return;
            }

            e.preventDefault();
            
            // Show loading bar and transition
            loadingBar.classList.add('active');
            pageTransition.classList.add('active');
            
            // Navigate after a short delay for the animation
            setTimeout(() => {
                window.location.href = href;
            }, 500);
        });
    });

    // Hide loading bar when page is fully loaded
    window.addEventListener('load', function() {
        loadingBar.classList.remove('active');
        pageTransition.classList.remove('active');
    });

    // Handle browser back/forward buttons
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            loadingBar.classList.remove('active');
            pageTransition.classList.remove('active');
        }
    });

    // Header scroll effect
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll <= 0) {
            header.classList.remove('scrolled');
            return;
        }
        
        if (currentScroll > lastScroll && currentScroll > 100) {
            // Scrolling down
            header.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up
            header.style.transform = 'translateY(0)';
            if (currentScroll > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }
        
        lastScroll = currentScroll;
    });
});
