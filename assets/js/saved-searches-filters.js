// State management for filters
let savedSearches = [];
let filteredSearches = [];
let activeFilters = {
    searchTerm: '',
    propertyType: '',
    sortBy: 'newest',
    priceRange: null,
    bedrooms: null,
    bathrooms: null,
    amenities: []
};

// DOM Elements
const searchFilter = document.getElementById('searchFilter');
const sortBySelect = document.getElementById('sortBy');
const filterByType = document.getElementById('filterByType');
const clearSearchBtn = document.getElementById('clearSearch');
const clearAllFiltersBtn = document.getElementById('clearAllFilters');
const activeFiltersContainer = document.getElementById('activeFilters');

// Debounce function to limit how often a function is called
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

// Initialize filter event listeners
function initFilters() {
    // Search filter with debounce
    if (searchFilter) {
        searchFilter.addEventListener('input', debounce((e) => {
            activeFilters.searchTerm = e.target.value.trim().toLowerCase();
            applyFilters();
            updateActiveFiltersUI();
        }, 300));
    }

    // Clear search button
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', () => {
            if (searchFilter) {
                searchFilter.value = '';
                activeFilters.searchTerm = '';
                applyFilters();
                updateActiveFiltersUI();
            }
        });
    }

    // Sort by select
    if (sortBySelect) {
        sortBySelect.addEventListener('change', (e) => {
            activeFilters.sortBy = e.target.value;
            applyFilters();
            updateActiveFiltersUI();
        });
    }

    // Filter by type
    if (filterByType) {
        filterByType.addEventListener('change', (e) => {
            activeFilters.propertyType = e.target.value;
            applyFilters();
            updateActiveFiltersUI();
        });
    }

    // Clear all filters
    if (clearAllFiltersBtn) {
        clearAllFiltersBtn.addEventListener('click', resetFilters);
    }
    
    // Handle removing individual filter chips
    document.addEventListener('click', (e) => {
        if (e.target.closest('.filter-chip .btn-close')) {
            const chip = e.target.closest('.filter-chip');
            const filterType = chip.dataset.filterType;
            
            switch(filterType) {
                case 'search':
                    activeFilters.searchTerm = '';
                    if (searchFilter) searchFilter.value = '';
                    break;
                case 'type':
                    activeFilters.propertyType = '';
                    if (filterByType) filterByType.value = '';
                    break;
                case 'sort':
                    activeFilters.sortBy = 'newest';
                    if (sortBySelect) sortBySelect.value = 'newest';
                    break;
                case 'price':
                    activeFilters.priceRange = null;
                    // Reset price range slider if exists
                    const priceRangeSlider = document.getElementById('priceRange');
                    if (priceRangeSlider) {
                        priceRangeSlider.noUiSlider.reset();
                    }
                    break;
                case 'bedrooms':
                    activeFilters.bedrooms = null;
                    const bedroomSelect = document.getElementById('bedrooms');
                    if (bedroomSelect) bedroomSelect.value = '';
                    break;
                case 'bathrooms':
                    activeFilters.bathrooms = null;
                    const bathroomSelect = document.getElementById('bathrooms');
                    if (bathroomSelect) bathroomSelect.value = '';
                    break;
                case 'amenity':
                    const amenity = chip.dataset.value;
                    activeFilters.amenities = activeFilters.amenities.filter(a => a !== amenity);
                    // Uncheck corresponding checkbox if it exists
                    const amenityCheckbox = document.querySelector(`input[type="checkbox"][value="${amenity}"]`);
                    if (amenityCheckbox) amenityCheckbox.checked = false;
                    break;
            }
            
            applyFilters();
            updateActiveFiltersUI();
        }
    });
}

// Reset all filters to default values
function resetFilters() {
    activeFilters = {
        searchTerm: '',
        propertyType: '',
        sortBy: 'newest',
        priceRange: null,
        bedrooms: null,
        bathrooms: null,
        amenities: []
    };
    
    // Reset form elements
    if (searchFilter) searchFilter.value = '';
    if (sortBySelect) sortBySelect.value = 'newest';
    if (filterByType) filterByType.value = '';
    
    // Reset additional filter elements if they exist
    const priceRangeSlider = document.getElementById('priceRange');
    if (priceRangeSlider && priceRangeSlider.noUiSlider) {
        priceRangeSlider.noUiSlider.reset();
    }
    
    const bedroomSelect = document.getElementById('bedrooms');
    if (bedroomSelect) bedroomSelect.value = '';
    
    const bathroomSelect = document.getElementById('bathrooms');
    if (bathroomSelect) bathroomSelect.value = '';
    
    // Uncheck all amenity checkboxes
    document.querySelectorAll('input[type="checkbox"][name="amenities[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    applyFilters();
    updateActiveFiltersUI();
}

// Apply filters and sorting to saved searches
function applyFilters() {
    if (savedSearches.length === 0) return;
    
    // Filter searches
    filteredSearches = savedSearches.filter(search => {
        // Filter by search term
        const matchesSearch = !activeFilters.searchTerm || 
            search.name.toLowerCase().includes(activeFilters.searchTerm) ||
            (search.search_params?.location && search.search_params.location.toLowerCase().includes(activeFilters.searchTerm)) ||
            (search.search_params?.description && search.search_params.description.toLowerCase().includes(activeFilters.searchTerm));
        
        // Filter by property type
        const matchesType = !activeFilters.propertyType || 
            (search.search_params?.property_type && 
             search.search_params.property_type.toLowerCase() === activeFilters.propertyType.toLowerCase());
        
        // Filter by price range
        const matchesPrice = !activeFilters.priceRange || (
            (!activeFilters.priceRange.min || (search.search_params?.min_price && 
             parseFloat(search.search_params.min_price) >= activeFilters.priceRange.min)) &&
            (!activeFilters.priceRange.max || (search.search_params?.max_price && 
             parseFloat(search.search_params.max_price) <= activeFilters.priceRange.max))
        );
        
        // Filter by bedrooms
        const matchesBedrooms = !activeFilters.bedrooms || 
            (search.search_params?.bedrooms && 
             parseInt(search.search_params.bedrooms) === parseInt(activeFilters.bedrooms));
        
        // Filter by bathrooms
        const matchesBathrooms = !activeFilters.bathrooms || 
            (search.search_params?.bathrooms && 
             parseInt(search.search_params.bathrooms) === parseInt(activeFilters.bathrooms));
        
        // Filter by amenities
        const matchesAmenities = activeFilters.amenities.length === 0 || 
            (search.search_params?.amenities && 
             activeFilters.amenities.every(amenity => 
                 search.search_params.amenities.includes(amenity)
             ));
        
        return matchesSearch && matchesType && matchesPrice && 
               matchesBedrooms && matchesBathrooms && matchesAmenities;
    });
    
    // Sort searches
    filteredSearches.sort((a, b) => {
        switch(activeFilters.sortBy) {
            case 'oldest':
                return new Date(a.created_at) - new Date(b.created_at);
            case 'name_asc':
                return a.name.localeCompare(b.name);
            case 'name_desc':
                return b.name.localeCompare(a.name);
            case 'price_asc':
                return (parseFloat(a.search_params?.min_price) || 0) - (parseFloat(b.search_params?.min_price) || 0);
            case 'price_desc':
                return (parseFloat(b.search_params?.max_price) || 0) - (parseFloat(a.search_params?.max_price) || 0);
            case 'newest':
            default:
                return new Date(b.created_at) - new Date(a.created_at);
        }
    });
    
    // Update the UI
    renderSavedSearches();
}

// Update the active filters UI
function updateActiveFiltersUI() {
    if (!activeFiltersContainer) return;
    
    const filters = [];
    
    // Add search term filter if present
    if (activeFilters.searchTerm) {
        filters.push({
            type: 'search',
            label: `Search: "${activeFilters.searchTerm}"`,
            icon: 'search'
        });
    }
    
    // Add property type filter if present
    if (activeFilters.propertyType) {
        const typeLabel = activeFilters.propertyType.charAt(0).toUpperCase() + activeFilters.propertyType.slice(1);
        filters.push({
            type: 'type',
            label: `Type: ${typeLabel}`,
            icon: 'tag'
        });
    }
    
    // Add price range filter if present
    if (activeFilters.priceRange) {
        let priceLabel = 'Price: ';
        if (activeFilters.priceRange.min && activeFilters.priceRange.max) {
            priceLabel += `$${activeFilters.priceRange.min.toLocaleString()} - $${activeFilters.priceRange.max.toLocaleString()}`;
        } else if (activeFilters.priceRange.min) {
            priceLabel += `From $${activeFilters.priceRange.min.toLocaleString()}`;
        } else if (activeFilters.priceRange.max) {
            priceLabel += `Up to $${activeFilters.priceRange.max.toLocaleString()}`;
        }
        
        filters.push({
            type: 'price',
            label: priceLabel,
            icon: 'dollar-sign'
        });
    }
    
    // Add bedrooms filter if present
    if (activeFilters.bedrooms) {
        filters.push({
            type: 'bedrooms',
            label: `${activeFilters.bedrooms} ${activeFilters.bedrooms === '1' ? 'Bedroom' : 'Bedrooms'}`,
            icon: 'bed'
        });
    }
    
    // Add bathrooms filter if present
    if (activeFilters.bathrooms) {
        filters.push({
            type: 'bathrooms',
            label: `${activeFilters.bathrooms} ${activeFilters.bathrooms === '1' ? 'Bathroom' : 'Bathrooms'}`,
            icon: 'bath'
        });
    }
    
    // Add amenities filters if present
    activeFilters.amenities.forEach(amenity => {
        filters.push({
            type: 'amenity',
            label: amenity.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
            value: amenity,
            icon: 'check-circle'
        });
    });
    
    // Add sort filter if not default
    if (activeFilters.sortBy !== 'newest') {
        const sortLabels = {
            'oldest': 'Oldest First',
            'name_asc': 'Name (A-Z)',
            'name_desc': 'Name (Z-A)',
            'price_asc': 'Price: Low to High',
            'price_desc': 'Price: High to Low'
        };
        
        filters.push({
            type: 'sort',
            label: `Sorted by: ${sortLabels[activeFilters.sortBy] || 'Newest First'}`,
            icon: 'sort'
        });
    }
    
    // Update the active filters container
    if (filters.length > 0) {
        activeFiltersContainer.style.display = 'flex';
        activeFiltersContainer.innerHTML = `
            <small class="text-muted me-2">Filters:</small>
            ${filters.map(filter => `
                <div class="filter-chip bg-light border rounded-pill px-2 py-1 d-flex align-items-center me-2 mb-1" 
                     data-filter-type="${filter.type}" ${filter.value ? `data-value="${filter.value}"` : ''}>
                    <i class="fas fa-${filter.icon} me-1" style="font-size: 0.7rem;"></i>
                    <span class="filter-text">${filter.label}</span>
                    <button type="button" class="btn-close btn-close-sm ms-1" aria-label="Remove filter"></button>
                </div>
            `).join('')}
            <button class="btn btn-sm btn-link text-decoration-none ms-auto" id="clearAllFilters">
                <i class="fas fa-times me-1"></i>Clear All
            </button>`;
        
        // Update clear all button event listener
        const clearAllBtn = activeFiltersContainer.querySelector('#clearAllFilters');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', resetFilters);
        }
    } else {
        activeFiltersContainer.style.display = 'none';
    }
}

// Initialize the filters when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initFilters();
    
    // Load saved searches and apply initial filters
    loadSavedSearches().then(() => {
        applyFilters();
    });
});
