document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Favorite button functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-favorite') || e.target.closest('.btn-favorite i')) {
            e.preventDefault();
            const button = e.target.closest('.btn-favorite') || e.target.closest('.btn-favorite i').closest('.btn-favorite');
            const icon = button.querySelector('i');
            
            // Toggle favorite state
            const isFavorite = button.classList.contains('favorited');
            
            if (isFavorite) {
                button.classList.remove('favorited');
                icon.classList.remove('fas', 'text-danger');
                icon.classList.add('far');
                button.setAttribute('title', 'Add to favorites');
                // Here you would typically make an AJAX call to remove from favorites
            } else {
                button.classList.add('favorited');
                icon.classList.remove('far');
                icon.classList.add('fas', 'text-danger');
                button.setAttribute('title', 'Remove from favorites');
                // Here you would typically make an AJAX call to add to favorites
            }
            
            // Update the tooltip title
            const tooltip = bootstrap.Tooltip.getInstance(button);
            if (tooltip) {
                tooltip.dispose();
                new bootstrap.Tooltip(button);
            }
        }
    });

    // Lazy load images
    const lazyImages = [].slice.call(document.querySelectorAll('img.lazy'));
    
    if ('IntersectionObserver' in window) {
        const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const lazyImage = entry.target;
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImage.classList.remove('lazy');
                    lazyImageObserver.unobserve(lazyImage);
                }
            });
        });

        lazyImages.forEach(function(lazyImage) {
            lazyImageObserver.observe(lazyImage);
        });
    }

    // Add animation on scroll for property cards
    const propertyCards = document.querySelectorAll('.property-card');
    
    const animateOnScroll = function() {
        propertyCards.forEach(card => {
            const cardPosition = card.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.3;
            
            if (cardPosition < screenPosition) {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }
        });
    };
    
    // Initial check
    animateOnScroll();
    
    // Check on scroll
    window.addEventListener('scroll', animateOnScroll);
});

// Price formatting
function formatPrice(price) {
    if (!price) return 'Contact for Price';
    
    // Convert to number if it's a string
    const numPrice = typeof price === 'string' ? parseFloat(price.replace(/[^0-9.-]+/g, '')) : price;
    
    // Format as currency
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(numPrice);
}

// Initialize price formatting on page load
document.addEventListener('DOMContentLoaded', function() {
    const priceElements = document.querySelectorAll('.property-price');
    priceElements.forEach(el => {
        const price = el.textContent.trim();
        el.textContent = formatPrice(price);
    });
});
