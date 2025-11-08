/**
 * Testimonials Page JavaScript - Updated to use shared utilities
 * Removed duplicate AOS initialization (now handled by utils.js)
 */

// Import shared utilities
import { initAOS, addStaggeredAnimations } from './utils.js';

// Testimonials Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS animations using shared utility
    initAOS();

    // Add animation to testimonial cards
    const testimonialCards = document.querySelectorAll('.testimonial-card');
    testimonialCards.forEach((card, index) => {
        card.setAttribute('data-aos', 'fade-up');
        card.setAttribute('data-aos-delay', (index % 3) * 100);
    });

    // Handle category filter buttons
    const filterButtons = document.querySelectorAll('.testimonial-filter button');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('btn-primary'));
            // Add active class to clicked button
            this.classList.add('btn-primary');
        });
    });

    // Truncate long testimonials
    const testimonialTexts = document.querySelectorAll('.testimonial-text');
    const maxLength = 200;

    testimonialTexts.forEach(text => {
        const content = text.textContent;
        if (content.length > maxLength) {
            const truncated = content.substring(0, maxLength);
            const remainder = content.substring(maxLength);
            
            text.innerHTML = `
                ${truncated}<span class="text-truncated">${remainder}</span>
                <button class="btn btn-link btn-sm read-more p-0">Read More</button>
            `;

            const readMoreBtn = text.querySelector('.read-more');
            const truncatedText = text.querySelector('.text-truncated');
            
            readMoreBtn.addEventListener('click', function() {
                if (truncatedText.style.display === 'none') {
                    truncatedText.style.display = 'inline';
                    this.textContent = 'Read Less';
                } else {
                    truncatedText.style.display = 'none';
                    this.textContent = 'Read More';
                }
            });

            // Initially hide truncated text
            truncatedText.style.display = 'none';
        }
    });

    // Lazy load images
    const images = document.querySelectorAll('.author-image');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => {
            if (img.dataset.src) {
                imageObserver.observe(img);
            }
        });
    }
});