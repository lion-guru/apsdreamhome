/**
 * FAQ Page JavaScript - Updated to use shared utilities
 * Removed duplicate AOS initialization (now handled by utils.js)
 */

// Import shared utilities
import { initAOS, debounce } from './utils.js';

// FAQ Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS animations using shared utility
    initAOS();

    // Handle FAQ search
    const searchForm = document.getElementById('faqSearch');
    const searchInput = document.getElementById('searchInput');
    const accordionItems = document.querySelectorAll('.accordion-item');

    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        searchFAQs();
    });

    searchInput.addEventListener('input', debounce(searchFAQs, 300));

    function searchFAQs() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let hasResults = false;

        // Close all accordion items
        document.querySelectorAll('.accordion-collapse').forEach(item => {
            item.classList.remove('show');
        });

        // Remove existing highlights
        document.querySelectorAll('.search-highlight').forEach(highlight => {
            const text = highlight.textContent;
            highlight.replaceWith(text);
        });

        accordionItems.forEach(item => {
            const question = item.querySelector('.accordion-button').textContent.toLowerCase();
            const answer = item.querySelector('.accordion-body').textContent.toLowerCase();
            
            if (searchTerm === '') {
                item.style.display = '';
                hasResults = true;
            } else if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                item.style.display = '';
                hasResults = true;
                
                // Highlight matches
                highlightMatches(item, searchTerm);
                
                // Open accordion item with matches
                item.querySelector('.accordion-collapse').classList.add('show');
                item.querySelector('.accordion-button').classList.remove('collapsed');
            } else {
                item.style.display = 'none';
            }
        });

        // Show/hide no results message
        const noResultsMessage = document.querySelector('.no-results-message');
        if (!hasResults) {
            if (!noResultsMessage) {
                const message = document.createElement('div');
                message.className = 'no-results-message text-center py-5';
                message.innerHTML = `
                    <h3>No FAQs Found</h3>
                    <p class="text-muted">Try different keywords or browse through our categories</p>
                `;
                document.querySelector('.faq-accordion').appendChild(message);
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

    function highlightMatches(item, searchTerm) {
        const elements = [
            item.querySelector('.accordion-button'),
            item.querySelector('.accordion-body')
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
    const filterButtons = document.querySelectorAll('.faq-filter button');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('btn-primary'));
            this.classList.add('btn-primary');
        });
    });

    // Check for search parameter in URL
    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('search');
    if (searchParam) {
        searchInput.value = searchParam;
        searchFAQs();
    }

    // Smooth scroll to accordion item if hash is present
    if (window.location.hash) {
        const targetAccordion = document.querySelector(window.location.hash);
        if (targetAccordion) {
            targetAccordion.scrollIntoView({ behavior: 'smooth' });
            targetAccordion.querySelector('.accordion-button').click();
        }
    }

    // Add copy link functionality
    document.querySelectorAll('.accordion-button').forEach(button => {
        button.addEventListener('dblclick', function() {
            const accordionId = this.getAttribute('data-bs-target').substring(1);
            const url = new URL(window.location);
            url.hash = accordionId;
            
            navigator.clipboard.writeText(url.toString()).then(() => {
                const tooltip = document.createElement('div');
                tooltip.className = 'copy-tooltip';
                tooltip.textContent = 'Link copied!';
                this.appendChild(tooltip);
                
                setTimeout(() => tooltip.remove(), 2000);
            });
        });
    });
});