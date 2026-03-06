<?php
/**
 * AI Features Demo Template
 * Shows how to integrate AI features into the properties page
 */
?>

<!-- AI Search Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">Find Your Dream Home with AI</h2>
                    <p class="lead text-muted">Search using natural language and let our AI find the perfect property for you</p>
                </div>
                
                <!-- AI Search Box -->
                <div class="ai-search-container">
                    <form id="aiSearchForm" class="ai-search-box">
                        <input type="text" 
                               id="aiSearchInput" 
                               class="ai-search-input" 
                               placeholder="Try: 'Show me 3 BHK flats in Mumbai under 1.5 Cr'"
                               autocomplete="off">
                        <div class="ai-search-buttons">
                            <button type="button" id="voiceSearchBtn" class="ai-search-btn" title="Voice Search">
                                <i class="fas fa-microphone"></i>
                            </button>
                            <button type="submit" class="ai-search-btn" title="Search">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    <div class="ai-error-container mt-2"></div>
                </div>
                
                <!-- Search Results -->
                <div id="searchResults" class="mt-5">
                    <!-- Results will be loaded here -->
                </div>
                
                <!-- AI Loader -->
                <div id="aiLoader" class="ai-loader" style="display: none;">
                    <div class="spinner"></div>
                    <p class="text-center text-muted mt-2">Finding the best properties for you...</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- AI Recommendations Section -->
<section id="aiRecommendations" class="ai-recommendations">
    <div class="container">
        <div class="section-header">
            <h2>
                Recommended For You 
                <span class="ai-badge">
                    <i class="fas fa-robot"></i> AI-Powered
                </span>
            </h2>
            <p class="text-muted">Properties selected based on your preferences and behavior</p>
        </div>
        
        <div class="recommendations-container">
            <!-- Recommendations will be loaded here by JavaScript -->
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading personalized recommendations...</p>
            </div>
        </div>
    </div>
</section>

<!-- AI Price Prediction Modal -->
<div class="modal fade price-prediction-modal" id="pricePredictionModal" tabindex="-1" aria-labelledby="pricePredictionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pricePredictionModalLabel">
                    <i class="fas fa-chart-line me-2"></i> AI Price Prediction
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="pricePredictionForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="propertyType" class="form-label">Property Type</label>
                            <select class="form-select" id="propertyType" name="property_type" required>
                                <option value="">Select type</option>
                                <option value="apartment">Apartment</option>
                                <option value="villa">Villa</option>
                                <option value="plot">Plot</option>
                                <option value="house">House</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" placeholder="Enter location" required>
                        </div>
                        <div class="col-md-4">
                            <label for="area" class="form-label">Area (sq.ft)</label>
                            <input type="number" class="form-control" id="area" name="area" min="100" step="1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="bedrooms" class="form-label">Bedrooms</label>
                            <select class="form-select" id="bedrooms" name="bedrooms">
                                <option value="1">1 BHK</option>
                                <option value="2" selected>2 BHK</option>
                                <option value="3">3 BHK</option>
                                <option value="4">4 BHK</option>
                                <option value="5">5+ BHK</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="bathrooms" class="form-label">Bathrooms</label>
                            <select class="form-select" id="bathrooms" name="bathrooms">
                                <option value="1">1</option>
                                <option value="2" selected>2</option>
                                <option value="3">3</option>
                                <option value="4">4+</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="yearBuilt" class="form-label">Year Built</label>
                            <select class="form-select" id="yearBuilt" name="year_built">
                                <?php 
                                $currentYear = date('Y');
                                for ($year = $currentYear; $year >= $currentYear - 30; $year--) {
                                    $selected = ($year == $currentYear - 5) ? 'selected' : '';
                                    echo "<option value='$year' $selected>$year</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="condition" class="form-label">Property Condition</label>
                            <select class="form-select" id="condition" name="condition">
                                <option value="excellent">Excellent</option>
                                <option value="good" selected>Good</option>
                                <option value="needs_repair">Needs Repair</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Amenities</label>
                            <div class="row g-2">
                                <?php 
                                $amenities = [
                                    'swimming_pool' => 'Swimming Pool',
                                    'gym' => 'Gym',
                                    'parking' => 'Parking',
                                    'garden' => 'Garden',
                                    'security' => '24/7 Security',
                                    'lift' => 'Lift',
                                    'power_backup' => 'Power Backup',
                                    'water_supply' => '24/7 Water Supply'
                                ];
                                
                                foreach ($amenities as $value => $label): 
                                ?>
                                <div class="col-md-3 col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="amenities[]" value="<?php echo $value; ?>" id="amenity_<?php echo $value; ?>">
                                        <label class="form-check-label" for="amenity_<?php echo $value; ?>">
                                            <?php echo $label; ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-calculator me-2"></i> Predict Price
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Prediction Result -->
                <div id="predictionResult" class="prediction-result mt-4">
                    <!-- Result will be displayed here -->
                </div>
                
                <!-- AI Loader -->
                <div id="predictionLoader" class="ai-loader" style="display: none;">
                    <div class="spinner"></div>
                    <p class="text-center text-muted mt-2">Analyzing property details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Chat Widget -->
<div class="ai-chat-widget">
    <button class="ai-chat-button" id="aiChatButton" title="AI Property Assistant">
        <i class="fas fa-robot"></i>
    </button>
</div>

<!-- Include JavaScript -->
<script src="/assets/js/ai-property-search.js"></script>

<!-- Include CSS -->
<link rel="stylesheet" href="/assets/css/ai-features.css">

<!-- Initialize AI Features -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AI features
    if (typeof PropertyAI !== 'undefined') {
        window.propertyAI = new PropertyAI();
    }
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Price prediction button click handler
    var pricePredictionBtn = document.getElementById('pricePredictionBtn');
    if (pricePredictionBtn) {
        pricePredictionBtn.addEventListener('click', function() {
            var pricePredictionModal = new bootstrap.Modal(document.getElementById('pricePredictionModal'));
            pricePredictionModal.show();
        });
    }
});
</script>
