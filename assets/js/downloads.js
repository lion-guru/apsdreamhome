/**
 * Downloads Page JavaScript - Updated to use shared utilities
 * Removed duplicate AOS initialization (now handled by utils.js)
 */

// Import shared utilities
import { initAOS, addStaggeredAnimations, debounce } from './utils.js';

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS animations using shared utility
    initAOS();

    // Add staggered animations to download cards
    addStaggeredAnimations('.download-card');

    // Search functionality
    const searchInput = document.getElementById('downloads-search');
    const downloadCards = document.querySelectorAll('.download-card');
    let searchTimeout;

    function searchDownloads(searchTerm) {
    searchTerm = searchTerm.toLowerCase();
    
    downloadCards.forEach(card => {
        const title = card.querySelector('.download-title').textContent.toLowerCase();
        const description = card.querySelector('.download-description').textContent.toLowerCase();
        const match = title.includes(searchTerm) || description.includes(searchTerm);
        
        card.style.display = match ? 'block' : 'none';
        
        if (match && searchTerm !== '') {
            highlightText(card.querySelector('.download-title'), searchTerm);
            highlightText(card.querySelector('.download-description'), searchTerm);
        } else {
            card.querySelector('.download-title').innerHTML = card.querySelector('.download-title').textContent;
            card.querySelector('.download-description').innerHTML = card.querySelector('.download-description').textContent;
        }
    });

    updateNoResultsMessage();
}

function highlightText(element, searchTerm) {
    const text = element.textContent;
    const regex = new RegExp(`(${searchTerm})`, 'gi');
    element.innerHTML = text.replace(regex, '<span class="search-highlight">$1</span>');
}

function updateNoResultsMessage() {
    const visibleCards = document.querySelectorAll('.download-card[style="display: block"]');
    const noResultsMessage = document.getElementById('no-results-message');
    
    if (visibleCards.length === 0 && searchInput.value !== '') {
        if (!noResultsMessage) {
            const message = document.createElement('div');
            message.id = 'no-results-message';
            message.className = 'text-center py-5';
            message.innerHTML = `
                <h3>No Results Found</h3>
                <p>Try adjusting your search criteria or browse all resources.</p>
            `;
            document.querySelector('.downloads-grid').appendChild(message);
        }
    } else if (noResultsMessage) {
        noResultsMessage.remove();
    }
}

if (searchInput) {
    searchInput.addEventListener('input', debounce((e) => {
        searchDownloads(e.target.value);
    }, 300));
}

// Category filter functionality
const filterButtons = document.querySelectorAll('.filter-btn');

filterButtons.forEach(button => {
    button.addEventListener('click', () => {
        const category = button.getAttribute('data-category');
        
        // Update active state of filter buttons
        filterButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        
        // Filter download cards
        downloadCards.forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            if (category === 'all' || cardCategory === category) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        updateNoResultsMessage();
    });
});

// Process URL search parameters
function processURLParams() {
    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('q');
    const category = urlParams.get('category');
    
    if (searchQuery) {
        searchInput.value = searchQuery;
        searchDownloads(searchQuery);
    }
    
    if (category) {
        const categoryButton = document.querySelector(`.filter-btn[data-category="${category}"]`);
        if (categoryButton) {
            categoryButton.click();
        }
    }
}

// Initialize download tracking
function trackDownload(downloadId, fileName) {
    // Send download tracking data to server
    fetch('/api/track-download', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            downloadId: downloadId,
            fileName: fileName,
            timestamp: new Date().toISOString()
        })
    })
    .catch(error => console.error('Error tracking download:', error));
}

// Add download tracking to all download buttons
document.querySelectorAll('.download-button').forEach(button => {
    button.addEventListener('click', (e) => {
        const downloadId = button.getAttribute('data-download-id');
        const fileName = button.getAttribute('data-filename');
        if (downloadId && fileName) {
            trackDownload(downloadId, fileName);
        }
    });
});

// Initialize lazy loading for download icons
const iconObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const icon = entry.target;
            const iconClass = icon.getAttribute('data-icon');
            if (iconClass) {
                icon.innerHTML = `<i class="${iconClass}"></i>`;
                iconObserver.unobserve(icon);
            }
        }
    });
}, {
    rootMargin: '50px'
});

document.querySelectorAll('.download-icon[data-icon]').forEach(icon => {
    iconObserver.observe(icon);
});

// Initialize tooltips
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

// Process URL parameters on page load
document.addEventListener('DOMContentLoaded', processURLParams);