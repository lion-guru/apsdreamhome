/**
 * APS Dream Home - Property Search JavaScript
 * Advanced property search functionality
 */

// ===== PROPERTY SEARCH MANAGER =====
class PropertySearchManager {
    constructor() {
        this.searchForm = document.getElementById('quickSearchForm');
        this.searchResults = document.getElementById('searchResults');
        this.featuredProperties = document.getElementById('featuredProperties');
        this.loadingSpinner = null;
        this.searchTimeout = null;
        this.currentFilters = {};
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.initSearchForm();
        this.loadFeaturedProperties();
    }
    
    setupEventListeners() {
        // Form submission
        if (this.searchForm) {
            this.searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.performSearch();
            });
            
            // Real-time search on input change
            const inputs = this.searchForm.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.performSearch();
                    }, 500);
                });
            });
        }
        
        // Search button clicks
        document.querySelectorAll('[data-search-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const action = btn.dataset.searchAction;
                this.handleSearchAction(action);
            });
        });
    }
    
    initSearchForm() {
        if (this.searchForm) {
            // Initialize form with default values
            const formData = new FormData(this.searchForm);
            formData.forEach((value, key) => {
                this.currentFilters[key] = value;
            });
        }
    }
    
    async performSearch() {
        if (!this.searchForm) return;
        
        // Get form data
        const formData = new FormData(this.searchForm);
        const searchParams = {};
        
        formData.forEach((value, key) => {
            if (value) {
                searchParams[key] = value;
            }
        });
        
        // Update current filters
        this.currentFilters = searchParams;
        
        // Show loading state
        this.showLoading();
        
        try {
            // Make API call
            const response = await fetch(`${BASE_URL}/api/properties/search`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(searchParams)
            });
            
            if (!response.ok) {
                throw new Error('Search request failed');
            }
            
            const data = await response.json();
            this.displaySearchResults(data);
            
        } catch (error) {
            console.error('Search error:', error);
            this.displayError('Unable to search properties. Please try again.');
        } finally {
            this.hideLoading();
        }
    }
    
    async loadFeaturedProperties() {
        if (!this.featuredProperties) return;
        
        this.showLoading(this.featuredProperties);
        
        try {
            const response = await fetch(`${BASE_URL}/api/properties/featured`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to load featured properties');
            }
            
            const data = await response.json();
            this.displayFeaturedProperties(data);
            
        } catch (error) {
            console.error('Featured properties error:', error);
            this.displayError('Unable to load featured properties.', this.featuredProperties);
        } finally {
            this.hideLoading();
        }
    }
    
    displaySearchResults(data) {
        if (!this.searchResults) {
            // Create search results container if it doesn't exist
            this.searchResults = document.createElement('div');
            this.searchResults.id = 'searchResults';
            this.searchResults.className = 'search-results mt-4';
            this.searchForm.parentNode.appendChild(this.searchResults);
        }
        
        if (data.properties && data.properties.length > 0) {
            const html = this.generatePropertyCards(data.properties, true);
            this.searchResults.innerHTML = `
                <div class="row mb-4">
                    <div class="col-12">
                        <h3 class="h4">Search Results (${data.properties.length} properties found)</h3>
                    </div>
                </div>
                <div class="row">
                    ${html}
                </div>
                ${data.pagination ? this.generatePagination(data.pagination) : ''}
            `;
            
            // Scroll to results
            this.searchResults.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            this.searchResults.innerHTML = `
                <div class="row">
                    <div class="col-12 text-center">
                        <div class="alert alert-info">
                            <i class="fas fa-search me-2"></i>
                            No properties found matching your criteria. Try adjusting your filters.
                        </div>
                    </div>
                </div>
            `;
        }
    }
    
    displayFeaturedProperties(data) {
        if (!this.featuredProperties) return;
        
        if (data.properties && data.properties.length > 0) {
            const html = this.generatePropertyCards(data.properties);
            this.featuredProperties.innerHTML = html;
        } else {
            this.featuredProperties.innerHTML = `
                <div class="col-12 text-center">
                    <p class="text-muted">No featured properties available at the moment.</p>
                </div>
            `;
        }
    }
    
    generatePropertyCards(properties, showSearchInfo = false) {
        return properties.map(property => `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card property-card h-100" data-property-id="${property.id}">
                    ${property.featured ? '<div class="featured-badge">Featured</div>' : ''}
                    <div class="position-relative overflow-hidden">
                        <img src="${property.image}" class="card-img-top property-image" alt="${property.title}" loading="lazy">
                        ${property.status ? `
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-${property.status === 'ready-to-move' ? 'success' : 'warning'}">
                                    ${property.status}
                                </span>
                            </div>
                        ` : ''}
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">${property.title}</h5>
                        <p class="text-muted mb-2">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            ${property.location}
                        </p>
                        <p class="text-primary fw-bold mb-2">₹${this.formatPrice(property.price)}</p>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">
                                <i class="fas fa-bed me-1"></i>${property.bedrooms} BHK
                            </span>
                            <span class="small text-muted">
                                <i class="fas fa-expand me-1"></i>${property.area} sq.ft.
                            </span>
                        </div>
                        <p class="card-text small">${property.description}</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="property-actions">
                                <button class="btn btn-outline-primary btn-sm me-2" onclick="propertySearch.toggleFavorite(${property.id})">
                                    <i class="far fa-heart"></i>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="propertySearch.shareProperty(${property.id})">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="propertySearch.viewPropertyDetails(${property.id})">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    generatePagination(pagination) {
        if (pagination.totalPages <= 1) return '';
        
        let html = '<div class="row mt-4"><div class="col-12"><nav><ul class="pagination justify-content-center">';
        
        // Previous button
        if (pagination.currentPage > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="propertySearch.goToPage(${pagination.currentPage - 1})">Previous</a>
            </li>`;
        }
        
        // Page numbers
        for (let i = 1; i <= pagination.totalPages; i++) {
            const active = i === pagination.currentPage ? 'active' : '';
            html += `<li class="page-item ${active}">
                <a class="page-link" href="#" onclick="propertySearch.goToPage(${i})">${i}</a>
            </li>`;
        }
        
        // Next button
        if (pagination.currentPage < pagination.totalPages) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="propertySearch.goToPage(${pagination.currentPage + 1})">Next</a>
            </li>`;
        }
        
        html += '</ul></nav></div></div>';
        return html;
    }
    
    formatPrice(price) {
        return new Intl.NumberFormat('en-IN').format(price);
    }
    
    showLoading(container = null) {
        const target = container || this.featuredProperties;
        if (target) {
            this.loadingSpinner = document.createElement('div');
            this.loadingSpinner.className = 'text-center py-5';
            this.loadingSpinner.innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            `;
            target.appendChild(this.loadingSpinner);
        }
    }
    
    hideLoading() {
        if (this.loadingSpinner && this.loadingSpinner.parentNode) {
            this.loadingSpinner.parentNode.removeChild(this.loadingSpinner);
            this.loadingSpinner = null;
        }
    }
    
    displayError(message, container = null) {
        const target = container || this.featuredProperties;
        if (target) {
            target.innerHTML = `
                <div class="col-12 text-center">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${message}
                    </div>
                </div>
            `;
        }
    }
    
    handleSearchAction(action) {
        switch (action) {
            case 'clear':
                this.clearFilters();
                break;
            case 'save':
                this.saveSearch();
                break;
            case 'share':
                this.shareSearch();
                break;
            default:
                console.warn('Unknown search action:', action);
        }
    }
    
    clearFilters() {
        if (this.searchForm) {
            this.searchForm.reset();
            this.currentFilters = {};
            this.loadFeaturedProperties();
        }
    }
    
    saveSearch() {
        const searchName = prompt('Enter a name for this search:');
        if (searchName) {
            const searches = JSON.parse(localStorage.getItem('savedSearches') || '[]');
            searches.push({
                name: searchName,
                filters: this.currentFilters,
                timestamp: new Date().toISOString()
            });
            localStorage.setItem('savedSearches', JSON.stringify(searches));
            showSuccessMessage('Search saved successfully!');
        }
    }
    
    shareSearch() {
        const searchParams = new URLSearchParams(this.currentFilters);
        const shareUrl = `${BASE_URL}/properties?${searchParams.toString()}`;
        
        if (navigator.share) {
            navigator.share({
                title: 'APS Dream Home - Property Search',
                text: 'Check out these properties I found!',
                url: shareUrl
            });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(shareUrl).then(() => {
                showSuccessMessage('Search link copied to clipboard!');
            });
        }
    }
    
    toggleFavorite(propertyId) {
        const favorites = JSON.parse(localStorage.getItem('favoriteProperties') || '[]');
        const index = favorites.indexOf(propertyId);
        
        if (index > -1) {
            favorites.splice(index, 1);
            showSuccessMessage('Property removed from favorites');
        } else {
            favorites.push(propertyId);
            showSuccessMessage('Property added to favorites');
        }
        
        localStorage.setItem('favoriteProperties', JSON.stringify(favorites));
        this.updateFavoriteButtons();
    }
    
    updateFavoriteButtons() {
        const favorites = JSON.parse(localStorage.getItem('favoriteProperties') || '[]');
        document.querySelectorAll('[onclick*="toggleFavorite"]').forEach(btn => {
            const propertyId = parseInt(btn.getAttribute('onclick').match(/\d+/)[0]);
            const icon = btn.querySelector('i');
            if (favorites.includes(propertyId)) {
                icon.classList.remove('far');
                icon.classList.add('fas', 'text-danger');
            } else {
                icon.classList.remove('fas', 'text-danger');
                icon.classList.add('far');
            }
        });
    }
    
    shareProperty(propertyId) {
        const shareUrl = `${BASE_URL}/properties/${propertyId}`;
        
        if (navigator.share) {
            navigator.share({
                title: 'APS Dream Home - Property',
                text: 'Check out this amazing property!',
                url: shareUrl
            });
        } else {
            navigator.clipboard.writeText(shareUrl).then(() => {
                showSuccessMessage('Property link copied to clipboard!');
            });
        }
    }
    
    viewPropertyDetails(propertyId) {
        // Navigate to property details page
        window.location.href = `${BASE_URL}/properties/${propertyId}`;
    }
    
    goToPage(page) {
        this.currentFilters.page = page;
        this.performSearch();
    }
}

// ===== INITIALIZATION =====
let propertySearch;

document.addEventListener('DOMContentLoaded', function() {
    propertySearch = new PropertySearchManager();
    
    // Update favorite buttons on page load
    if (propertySearch) {
        propertySearch.updateFavoriteButtons();
    }
});

// ===== GLOBAL FUNCTIONS =====
function showSuccessMessage(message) {
    if (window.APSAnimations && window.APSAnimations.showSuccessMessage) {
        window.APSAnimations.showSuccessMessage(message);
    } else {
        alert(message);
    }
}

// Export for external use
window.PropertySearchManager = PropertySearchManager;
window.propertySearch = propertySearch;
