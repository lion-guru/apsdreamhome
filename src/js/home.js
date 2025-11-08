document.addEventListener('DOMContentLoaded', function() {
    // Scroll-based Animation
    function animateOnScroll() {
        const elements = document.querySelectorAll('.animate-on-scroll');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        }, {
            threshold: 0.1
        });

        elements.forEach(element => {
            observer.observe(element);
        });
    }

    // Property Search Autocomplete
    function setupPropertySearchAutocomplete() {
        const searchInput = document.querySelector('.property-search-form input');
        const searchForm = document.querySelector('.property-search-form');
        
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            
            if (query.length > 2) {
                debounceTimer = setTimeout(() => {
                    fetch(`/api/property-suggestions.php?query=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(suggestions => {
                            const dropdown = document.getElementById('search-suggestions') || 
                                createAutocompleteDropdown(searchInput);
                            
                            dropdown.innerHTML = suggestions.length > 0 
                                ? suggestions.map(suggestion => 
                                    `<div class="suggestion-item" 
                                         data-id="${suggestion.id}" 
                                         data-value="${suggestion.value}">
                                        ${suggestion.label}
                                        <span class="suggestion-type">${suggestion.type}</span>
                                    </div>`
                                ).join('')
                                : `<div class="no-suggestions">No properties found</div>`;
                            
                            dropdown.style.display = suggestions.length > 0 ? 'block' : 'none';
                        })
                        .catch(error => {
                            console.error('Autocomplete error:', error);
                            const dropdown = document.getElementById('search-suggestions');
                            if (dropdown) {
                                dropdown.innerHTML = `<div class="error-suggestions">Search unavailable</div>`;
                            }
                        });
                }, 300);
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('search-suggestions');
            if (dropdown && !dropdown.contains(e.target) && e.target !== searchInput) {
                dropdown.style.display = 'none';
            }
        });

        // Handle suggestion selection
        searchForm.addEventListener('click', function(e) {
            const suggestionItem = e.target.closest('.suggestion-item');
            if (suggestionItem) {
                const selectedValue = suggestionItem.dataset.value;
                const selectedId = suggestionItem.dataset.id;
                
                searchInput.value = selectedValue;
                
                // Optional: Redirect to property details or perform search
                if (selectedId && selectedId !== 'ai_suggestion_1') {
                    window.location.href = `/property-detail.php?id=${selectedId}`;
                }
                
                document.getElementById('search-suggestions').style.display = 'none';
            }
        });
    }

    function createAutocompleteDropdown(input) {
        const dropdown = document.createElement('div');
        dropdown.id = 'search-suggestions';
        dropdown.classList.add('autocomplete-dropdown');
        
        input.parentNode.appendChild(dropdown);
        
        dropdown.addEventListener('click', function(e) {
            if (e.target.classList.contains('suggestion-item')) {
                input.value = e.target.dataset.value;
                dropdown.innerHTML = '';
            }
        });

        return dropdown;
    }

    // AI Recommendation Interaction
    function setupAIRecommendationInteraction() {
        const recommendationCards = document.querySelectorAll('.ai-recommendation-card');
        
        recommendationCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('highlight');
            });

            card.addEventListener('mouseleave', function() {
                this.classList.remove('highlight');
            });
        });
    }

    // Initialize Features
    animateOnScroll();
    setupPropertySearchAutocomplete();
    setupAIRecommendationInteraction();

    // Periodic Background Updates
    function updateBackgroundTheme() {
        const hour = new Date().getHours();
        const body = document.body;

        if (hour >= 6 && hour < 12) {
            body.classList.add('morning-theme');
        } else if (hour >= 12 && hour < 18) {
            body.classList.add('afternoon-theme');
        } else {
            body.classList.add('evening-theme');
        }
    }

    updateBackgroundTheme();
    setInterval(updateBackgroundTheme, 60 * 60 * 1000); // Update every hour
});
