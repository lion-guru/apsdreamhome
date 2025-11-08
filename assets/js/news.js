/**
 * News Page JavaScript - Updated to use shared utilities
 * Removed duplicate AOS initialization (now handled by utils.js)
 */

// Import shared utilities
import { initAOS, addStaggeredAnimations, debounce } from './utils.js';

// News Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS animations using shared utility
    initAOS();

    // Add animation to news cards
    const newsCards = document.querySelectorAll('.news-card');
    newsCards.forEach((card, index) => {
        card.setAttribute('data-aos', 'fade-up');
        card.setAttribute('data-aos-delay', (index % 3) * 100);
    });

    // Handle news search
    const searchForm = document.getElementById('newsSearch');
    const searchInput = document.getElementById('searchInput');

    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            searchNews();
        });

        searchInput.addEventListener('input', debounce(searchNews, 300));
    }

    function searchNews() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const newsCards = document.querySelectorAll('.news-card');
        let hasResults = false;

        // Remove existing highlights
        document.querySelectorAll('.search-highlight').forEach(highlight => {
            const text = highlight.textContent;
            highlight.replaceWith(text);
        });

        newsCards.forEach(card => {
            const title = card.querySelector('.news-title').textContent.toLowerCase();
            const excerpt = card.querySelector('.news-excerpt').textContent.toLowerCase();
            const category = card.querySelector('.news-category').textContent.toLowerCase();
            
            if (searchTerm === '') {
                card.closest('.col-lg-4').style.display = '';
                hasResults = true;
            } else if (title.includes(searchTerm) || excerpt.includes(searchTerm) || category.includes(searchTerm)) {
                card.closest('.col-lg-4').style.display = '';
                hasResults = true;
                
                // Highlight matches
                highlightMatches(card, searchTerm);
            } else {
                card.closest('.col-lg-4').style.display = 'none';
            }
        });

        // Show/hide no results message
        const noResultsMessage = document.querySelector('.no-results-message');
        if (!hasResults) {
            if (!noResultsMessage) {
                const message = document.createElement('div');
                message.className = 'no-results-message text-center py-5';
                message.innerHTML = `
                    <h3>No News Found</h3>
                    <p class="text-muted">Try different keywords or browse through our categories</p>
                `;
                document.querySelector('.row.g-4').appendChild(message);
            }
        } else if (noResultsMessage) {
            noResultsMessage.remove();
        }

        // Update URL with search term
        const url = new URL(window.location);
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        window.history.replaceState({}, '', url);
    }

    function highlightMatches(card, searchTerm) {
        const elements = [
            card.querySelector('.news-title'),
            card.querySelector('.news-excerpt'),
            card.querySelector('.news-category')
        ];

        elements.forEach(element => {
            if (!element) return;

            const html = element.innerHTML;
            const regex = new RegExp(searchTerm, 'gi');
            element.innerHTML = html.replace(regex, match => 
                `<span class="search-highlight">${match}</span>`
            );
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Handle category filter buttons
    const filterButtons = document.querySelectorAll('.news-filter button');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('btn-primary'));
            this.classList.add('btn-primary');
        });
    });

    // Handle newsletter form submission
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();

            if (email) {
                // Show loading state
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
                submitButton.disabled = true;

                // Make API call to subscribe
                fetch('/api/newsletter/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email })
                })
                .then(response => response.json())
                .then(data => {
                    // Show success message
                    emailInput.value = '';
                    showToast('Success!', 'You have been subscribed to our newsletter.', 'success');
                })
                .catch(error => {
                    showToast('Error!', 'Failed to subscribe. Please try again later.', 'error');
                })
                .finally(() => {
                    // Restore button state
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                });
            }
        });
    }

    // Toast notification helper
    function showToast(title, message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <div class="toast-header">
                <strong>${title}</strong>
                <button type="button" class="btn-close"></button>
            </div>
            <div class="toast-body">${message}</div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);

        // Handle close button
        toast.querySelector('.btn-close').addEventListener('click', () => {
            toast.remove();
        });
    }

    // Check for search parameter in URL
    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('search');
    if (searchParam && searchInput) {
        searchInput.value = searchParam;
        searchNews();
    }

    // Lazy load images
    const images = document.querySelectorAll('.news-image img');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => {
            if (img.src !== img.dataset.src) {
                imageObserver.observe(img);
            }
        });
    }
});