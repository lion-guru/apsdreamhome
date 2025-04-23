/**
 * Enhanced Scripts for APS Dream Homes
 * Improves website interactivity and user experience
 */

document.addEventListener('DOMContentLoaded', function() {
    // Header scroll effect
    const header = document.querySelector('.header');
    const backToTop = document.querySelector('.back-to-top');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
            if (backToTop) backToTop.classList.add('active');
        } else {
            header.classList.remove('scrolled');
            if (backToTop) backToTop.classList.remove('active');
        }
    });
    
    // Mobile menu toggle
    const mobileToggler = document.querySelector('.mobile-nav-toggler');
    const mainMenu = document.querySelector('.main-menu');
    
    if (mobileToggler && mainMenu) {
        mobileToggler.addEventListener('click', function() {
            mainMenu.classList.toggle('show');
        });
    }
    
    // Add active class to current page in navigation
    const currentLocation = window.location.pathname;
    const navLinks = document.querySelectorAll('.navigation li a');
    
    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        if (currentLocation.includes(linkPath) && linkPath !== '/index.php') {
            link.parentElement.classList.add('active');
        } else if (currentLocation === '/' || currentLocation.includes('/index.php')) {
            // Home page
            if (linkPath.includes('/index.php')) {
                link.parentElement.classList.add('active');
            }
        }
    });
    
    // Property image hover effect enhancement
    const propertyImages = document.querySelectorAll('.property-card .property-image');
    
    propertyImages.forEach(image => {
        image.addEventListener('mouseenter', function() {
            this.querySelector('img').style.transform = 'scale(1.05)';
        });
        
        image.addEventListener('mouseleave', function() {
            this.querySelector('img').style.transform = 'scale(1)';
        });
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            if (this.getAttribute('href') !== '#') {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    // Back to top button functionality
    if (backToTop) {
        backToTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Form input enhancement
    const formInputs = document.querySelectorAll('input, select, textarea');
    
    formInputs.forEach(input => {
        // Add focus effect
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('input-focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('input-focused');
        });
    });
    
    // Image lazy loading
    if ('loading' in HTMLImageElement.prototype) {
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    } else {
        // Fallback for browsers that don't support lazy loading
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
        document.body.appendChild(script);
    }
});