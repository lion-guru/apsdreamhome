/**
 * AI-Powered Property Search and Recommendations
 */

class PropertyAI {
    /**
     * Initialize the PropertyAI with configuration
     * @param {Object} config - Configuration options
     * @param {string} config.apiBaseUrl - Base URL for API endpoints
     * @param {string} config.csrfToken - CSRF token for secure requests
     */
    constructor(config = {}) {
        // Default configuration
        this.config = {
            apiBaseUrl: '/api/ai',
            csrfToken: '',
            ...config
        };
        
        // Initialize elements
        this.searchForm = document.getElementById('aiSearchForm');
        this.searchInput = document.getElementById('aiSearchInput');
        this.searchResults = document.getElementById('searchResults');
        this.aiLoader = document.getElementById('aiLoader');
        this.aiErrorContainer = document.querySelector('.ai-error-container');
        this.recommendationSection = document.getElementById('aiRecommendations');
        this.pricePredictionBtn = document.getElementById('pricePredictionBtn');
        this.predictionForm = document.getElementById('pricePredictionForm');
        this.predictionResult = document.getElementById('predictionResult');
        this.predictionLoader = document.getElementById('predictionLoader');
        this.voiceSearchBtn = document.getElementById('voiceSearchBtn');
        this.aiChatButton = document.getElementById('aiChatButton');
        
        // Speech recognition for voice search
        this.recognition = null;
        this.isListening = false;
        
        // Initialize the component
        this.init();
    }
    /**
     * Initialize the component
     */
    init() {
        // Initialize event listeners
        this.initEventListeners();
        
        // Load AI recommendations if on homepage or properties page
        if (this.recommendationSection) {
            this.loadAIRecommendations();
        }
        
        // Initialize speech recognition if available
        this.initSpeechRecognition();
    }
    
    /**
     * Initialize event listeners
     */
    initEventListeners() {
        // AI Search
        if (this.searchForm) {
            this.searchForm.addEventListener('submit', (e) => this.handleSearch(e));
        }
        
        // Price Prediction
        if (this.pricePredictionBtn) {
            this.pricePredictionBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.showPricePredictionModal();
            });
        }
        
        // Price prediction form
        if (this.predictionForm) {
            this.predictionForm.addEventListener('submit', (e) => this.predictPrice(e));
        }
        
        // Voice search
        if (this.voiceSearchBtn) {
            this.voiceSearchBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleVoiceSearch();
            });
        }
        
        // AI Chat button
        if (this.aiChatButton) {
            this.aiChatButton.addEventListener('click', () => this.toggleChat());
        }
    }
    
    /**
     * Handle AI-powered search
     */
    async handleSearch(e) {
        e.preventDefault();
        
        const query = this.searchInput.value.trim();
        if (!query) return;
        
        try {
            this.showLoader();
            
            // Process query with AI
            const response = await fetch('/api/ai/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ query })
            });
            
            if (!response.ok) throw new Error('Search failed');
            
            const data = await response.json();
            
            // Display results
            this.displaySearchResults(data);
            
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Failed to process search. Please try again.');
        } finally {
            this.hideLoader();
        }
    }
    
    /**
     * Display search results
     */
    displaySearchResults(results) {
        if (!results || results.length === 0) {
            this.searchResults.innerHTML = `
                <div class="alert alert-info">
                    No properties found matching your search. Try different keywords.
                </div>
            `;
            return;
        }
        
        let html = `
            <div class="row g-4">
                ${results.map(property => `
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 property-card">
                            <img src="${property.main_image || '/assets/img/default-property.jpg'}" 
                                 class="card-img-top" 
                                 alt="${property.title}">
                            <div class="card-body">
                                <h5 class="card-title">${property.title}</h5>
                                <p class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> ${property.location}
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 mb-0 text-primary">${property.price_formatted || 'Price on Request'}</span>
                                    <a href="/property-details.php?id=${property.id}" class="btn btn-sm btn-outline-primary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        
        this.searchResults.innerHTML = html;
    }
    
    /**
     * Load AI-powered property recommendations
     */
    async loadAIRecommendations() {
        try {
            const response = await fetch('/api/ai/recommendations');
            if (!response.ok) throw new Error('Failed to load recommendations');
            
            const properties = await response.json();
            
            if (properties && properties.length > 0) {
                this.renderRecommendations(properties);
            }
            
        } catch (error) {
            console.error('Error loading recommendations:', error);
            // Hide the section if there's an error
            if (this.recommendationSection) {
                this.recommendationSection.style.display = 'none';
            }
        }
    }
    
    /**
     * Render AI recommendations
     */
    renderRecommendations(properties) {
        const container = this.recommendationSection.querySelector('.recommendations-container');
        
        if (!container) return;
        
        container.innerHTML = `
            <div class="row g-4">
                ${properties.map(property => `
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="card h-100 property-card">
                            <div class="position-relative">
                                <img src="${property.main_image || '/assets/img/default-property.jpg'}" 
                                     class="card-img-top" 
                                     alt="${property.title}">
                                <span class="position-absolute top-0 end-0 m-2 badge bg-primary">
                                    <i class="fas fa-robot me-1"></i> AI Recommended
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">${property.title}</h5>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt text-danger"></i> ${property.location}
                                </p>
                                <div class="property-features d-flex justify-content-between mb-3">
                                    <span><i class="fas fa-bed text-muted me-1"></i> ${property.bedrooms || 'N/A'} Beds</span>
                                    <span><i class="fas fa-bath text-muted me-1"></i> ${property.bathrooms || 'N/A'} Baths</span>
                                    <span><i class="fas fa-ruler-combined text-muted me-1"></i> ${property.area || 'N/A'} sq.ft</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 mb-0 text-primary">${property.price_formatted || 'Price on Request'}</span>
                                    <a href="/property-details.php?id=${property.id}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    /**
     * Show price prediction modal
     */
    showPricePredictionModal() {
        this.pricePredictionModal.show();
    }
    
    /**
     * Predict property price using AI
     */
    async predictPrice(e) {
        e.preventDefault();
        
        const formData = new FormData(this.predictionForm);
        const propertyData = {
            type: formData.get('property_type'),
            location: formData.get('location'),
            area: parseFloat(formData.get('area')),
            bedrooms: parseInt(formData.get('bedrooms')),
            bathrooms: parseInt(formData.get('bathrooms')),
            year_built: formData.get('year_built'),
            condition: formData.get('condition'),
            amenities: formData.getAll('amenities')
        };
        
        try {
            this.showLoader(this.predictionForm);
            
            const response = await fetch('/api/ai/predict-price', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(propertyData)
            });
            
            if (!response.ok) throw new Error('Prediction failed');
            
            const result = await response.json();
            this.displayPredictionResult(result);
            
        } catch (error) {
            console.error('Prediction error:', error);
            this.showError('Failed to predict price. Please try again.', this.predictionForm);
        } finally {
            this.hideLoader(this.predictionForm);
        }
    }
    
    /**
     * Display price prediction result
     */
    displayPredictionResult(result) {
        if (!result || !result.predicted_price) {
            this.showError('Could not generate prediction. Please try again.', this.predictionForm);
            return;
        }
        
        const confidence = Math.round((result.confidence || 0.8) * 100);
        const price = new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(result.predicted_price);
        
        let factorsHtml = '';
        if (result.factors) {
            factorsHtml = `
                <div class="mt-4">
                    <h6 class="mb-3">Key Factors:</h6>
                    <ul class="list-unstyled">
                        ${result.factors.property_type ? `<li><strong>Type:</strong> ${this.capitalizeFirstLetter(result.factors.property_type)}</li>` : ''}
                        ${result.factors.area ? `<li><strong>Area:</strong> ${result.factors.area} sq.ft</li>` : ''}
                        ${result.factors.bedrooms ? `<li><strong>Bedrooms:</strong> ${result.factors.bedrooms}</li>` : ''}
                        ${result.factors.condition ? `<li><strong>Condition:</strong> ${this.capitalizeFirstLetter(result.factors.condition)}</li>` : ''}
                        ${result.factors.price_per_sqft ? `<li><strong>Price per sq.ft:</strong> ₹${Math.round(result.factors.price_per_sqft)}</li>` : ''}
                    </ul>
                </div>
            `;
        }
        
        this.predictionResult.innerHTML = `
            <div class="alert alert-success">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Estimated Property Value</h5>
                    <span class="badge bg-primary">${confidence}% Confidence</span>
                </div>
                <div class="display-4 fw-bold my-4 text-center">${price}</div>
                <p class="text-muted text-center mb-0">
                    Based on current market trends and property details
                </p>
                ${factorsHtml}
                <div class="mt-4 text-center">
                    <button class="btn btn-primary me-2">Contact Agent</button>
                    <button class="btn btn-outline-secondary">Save Estimate</button>
                </div>
            </div>
        `;
    }
    
    /**
     * Start voice search
     */
    startVoiceSearch() {
        if (!('webkitSpeechRecognition' in window) && !('speechRecognition' in window)) {
            this.showError('Speech recognition is not supported in your browser');
            return;
        }
        
        const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.lang = 'en-IN';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;
        
        // Show recording indicator
        const voiceSearchBtn = document.getElementById('voiceSearchBtn');
        if (voiceSearchBtn) {
            voiceSearchBtn.innerHTML = '<i class="fas fa-microphone-slash"></i>';
            voiceSearchBtn.classList.add('recording');
        }
        
        recognition.start();
        
        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            this.searchInput.value = transcript;
            this.searchForm.dispatchEvent(new Event('submit'));
        };
        
        recognition.onerror = (event) => {
            console.error('Speech recognition error', event.error);
            this.showError('Error recognizing speech. Please try again.');
        };
        
        recognition.onend = () => {
            if (voiceSearchBtn) {
                voiceSearchBtn.innerHTML = '<i class="fas fa-microphone"></i>';
                voiceSearchBtn.classList.remove('recording');
            }
        };
    }
    
    /**
     * Toggle AI chat widget
     */
    toggleChat() {
        // Implementation for AI chat widget
        console.log('AI Chat toggled');
        // Add your chat widget implementation here
    }
    
    /**
     * Track property click for analytics
     * @param {string} propertyId - ID of the clicked property
     */
    trackPropertyClick(propertyId) {
        // Send analytics event
        if (typeof gtag === 'function') {
            gtag('event', 'select_content', {
                'content_type': 'property',
                'item_id': propertyId
            });
        }
        
        // You can also send this data to your backend
        this.sendAnalytics('property_click', { property_id: propertyId });
    }
    
    /**
     * Track recommendation click for analytics
     * @param {string} propertyId - ID of the clicked recommended property
     */
    trackRecommendationClick(propertyId) {
        // Send analytics event
        if (typeof gtag === 'function') {
            gtag('event', 'select_content', {
                'content_type': 'recommended_property',
                'item_id': propertyId
            });
        }
        
        // You can also send this data to your backend
        this.sendAnalytics('recommendation_click', { property_id: propertyId });
    }
    
    /**
     * Send analytics data to the server
     * @param {string} event - Event name
     * @param {Object} data - Event data
     */
    sendAnalytics(event, data = {}) {
        // Don't block the main thread with analytics
        if (navigator.sendBeacon) {
            const analyticsData = new FormData();
            analyticsData.append('event', event);
            analyticsData.append('data', JSON.stringify(data));
            analyticsData.append('timestamp', new Date().toISOString());
            analyticsData.append('url', window.location.href);
            
            navigator.sendBeacon('/api/analytics/track', analyticsData);
        } else {
            // Fallback to fetch API if sendBeacon is not available
            fetch('/api/analytics/track', {
                method: 'POST',
                body: JSON.stringify({
                    event,
                    data,
                    timestamp: new Date().toISOString(),
                    url: window.location.href
                }),
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': this.config.csrfToken || ''
                },
                keepalive: true
            }).catch(console.error);
        }
    }
    
    /**
     * Initialize speech recognition for voice search
     */
    initSpeechRecognition() {
        if ('webkitSpeechRecognition' in window || 'speechRecognition' in window) {
            this.recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
            this.recognition.lang = 'en-IN';
            this.recognition.interimResults = false;
            this.recognition.maxAlternatives = 1;
            
            this.recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                this.searchInput.value = transcript;
                this.searchForm.dispatchEvent(new Event('submit'));
                this.isListening = false;
                this.updateVoiceSearchUI();
            };
            
            this.recognition.onerror = (event) => {
                console.error('Speech recognition error', event.error);
                this.showError('Error recognizing speech. Please try again.');
                this.isListening = false;
                this.updateVoiceSearchUI();
            };
            
            this.recognition.onend = () => {
                if (this.isListening) {
                    this.recognition.start();
                } else {
                    this.updateVoiceSearchUI();
                }
            };
        }
    }
    
    /**
     * Show loader
     */
    showLoader(context = document) {
        const loader = context.querySelector('.ai-loader');
        if (loader) loader.style.display = 'block';
    }
    
    /**
     * Hide loader
     */
    hideLoader(context = document) {
        const loader = context.querySelector('.ai-loader');
        if (loader) loader.style.display = 'none';
    }
    
    /**
     * Show error message
     */
    showError(message, context = document) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger mt-3';
        errorDiv.textContent = message;
        
        const container = context.querySelector('.ai-error-container');
        if (container) {
            container.innerHTML = '';
            container.appendChild(errorDiv);
        } else {
            context.appendChild(errorDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }
    }
    
    /**
     * Utility: Capitalize first letter
     */
    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Check if we're on a page that needs AI features
    if (document.querySelector('.ai-powered-search') || document.getElementById('aiRecommendations') || document.getElementById('pricePredictionBtn')) {
        window.propertyAI = new PropertyAI();
    }
});
