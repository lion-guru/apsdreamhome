document.addEventListener('DOMContentLoaded', function() {
    // Sticky filter section
    const filterSection = document.querySelector('.filter-section');
    const filterOffset = filterSection.offsetTop;
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > filterOffset) {
            filterSection.classList.add('sticky');
            document.body.style.paddingTop = filterSection.offsetHeight + 'px';
        } else {
            filterSection.classList.remove('sticky');
            document.body.style.paddingTop = '0';
        }
    });

    // Smooth scroll to top
    const scrollToTopBtn = document.querySelector('.scroll-to-top');
    if (scrollToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('show');
            } else {
                scrollToTopBtn.classList.remove('show');
            }
        });

        scrollToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }


    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover'
        });
    });

    // Initialize property filtering
    const propertyFilters = document.querySelectorAll('[data-filter]');
    if (propertyFilters.length > 0) {
        propertyFilters.forEach(filter => {
            filter.addEventListener('click', function() {
                const filterValue = this.getAttribute('data-filter');
                
                // Update active state
                document.querySelectorAll('[data-filter]').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
                
                // Filter properties
                const properties = document.querySelectorAll('.property-card');
                let visibleCount = 0;
                
                properties.forEach(property => {
                    const propertyType = property.getAttribute('data-property-type').toLowerCase();
                    const propertyStatus = property.getAttribute('data-property-status').toLowerCase();
                    
                    if (filterValue === 'all' || 
                        propertyType === filterValue || 
                        propertyStatus === filterValue) {
                        property.style.display = 'block';
                        visibleCount++;
                    } else {
                        property.style.display = 'none';
                    }
                });
                
                // Show/hide no results message
                const noResultsMessage = document.getElementById('noResultsMessage');
                if (noResultsMessage) {
                    if (visibleCount === 0) {
                        noResultsMessage.classList.remove('d-none');
                    } else {
                        noResultsMessage.classList.add('d-none');
                    }
                }
                
                // Scroll to results
                window.scrollTo({
                    top: filterSection.offsetTop - 80,
                    behavior: 'smooth'
                });
            });
        });
    }


    // Initialize image lazy loading
    if ('loading' in HTMLImageElement.prototype) {
        const lazyImages = document.querySelectorAll('img.lazy');
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        });
    } else {
        // Fallback for browsers that don't support lazy loading
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
        document.body.appendChild(script);
    }

    // Handle property image hover effect
    const propertyImages = document.querySelectorAll('.property-image-container');
    propertyImages.forEach(container => {
        const image = container.querySelector('img');
        
        container.addEventListener('mouseenter', () => {
            image.style.transform = 'scale(1.05)';
        });
        
        container.addEventListener('mouseleave', () => {
            image.style.transform = 'scale(1)';
        });
    });

    // Initialize AOS (Animate On Scroll)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    }

});

// Function to handle property search
function handlePropertySearch(event) {
    event.preventDefault();
    
    const searchInput = document.querySelector('#propertySearch');
    const searchTerm = searchInput.value.toLowerCase();
    
    if (searchTerm.length < 2) return;
    
    const properties = document.querySelectorAll('.property-card');
    let visibleCount = 0;
    
    properties.forEach(property => {
        const title = property.querySelector('.card-title').textContent.toLowerCase();
        const location = property.querySelector('.property-location').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || location.includes(searchTerm)) {
            property.style.display = 'block';
            visibleCount++;
        } else {
            property.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    const noResultsMessage = document.getElementById('noResultsMessage');
    if (noResultsMessage) {
        if (visibleCount === 0) {
            noResultsMessage.classList.remove('d-none');
        } else {
            noResultsMessage.classList.add('d-none');
        }
    }
    
    // Scroll to results
    window.scrollTo({
        top: document.querySelector('.properties-section').offsetTop - 100,
        behavior: 'smooth'
    });
}

// Function to reset filters
function resetPropertyFilters() {
    // Reset all property cards to visible
    const properties = document.querySelectorAll('.property-card');
    properties.forEach(property => {
        property.style.display = 'block';
    });
    
    // Reset filter buttons
    document.querySelectorAll('[data-filter]').forEach(btn => {
        if (btn.getAttribute('data-filter') === 'all') {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    // Hide no results message
    const noResultsMessage = document.getElementById('noResultsMessage');
    if (noResultsMessage) {
        noResultsMessage.classList.add('d-none');
    }
    
    // Reset search input
    const searchInput = document.querySelector('#propertySearch');
    if (searchInput) {
        searchInput.value = '';
    }
}
