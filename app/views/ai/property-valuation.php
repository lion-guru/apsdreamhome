<?php
$page_title = 'AI Property Valuation - APS Dream Home';
$page_description = 'Advanced AI-powered property valuation and market analysis';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-robot"></i>
                        AI Property Valuation Engine
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Valuation Form -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="property_id" class="form-label">Property ID</label>
                            <input type="number" class="form-control" id="property_id" placeholder="Enter Property ID">
                            <small class="form-text text-muted">Enter the property ID to generate AI valuation</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary" onclick="generateValuation()">
                                <i class="fas fa-calculator"></i> Generate Valuation
                            </button>
                            <button type="button" class="btn btn-info ml-2" onclick="getValuationHistory()">
                                <i class="fas fa-history"></i> View History
                            </button>
                        </div>
                    </div>

                    <!-- Valuation Results -->
                    <div id="valuation-results" style="display: none;">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h5>Valuation Summary</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Base Valuation:</strong>
                                                <p class="text-primary" id="base-valuation">₹0</p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Final Valuation:</strong>
                                                <p class="text-success" id="final-valuation">₹0</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Location Multiplier:</strong>
                                                <p id="location-multiplier">1.0x</p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Type Multiplier:</strong>
                                                <p id="type-multiplier">1.0x</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Amenity Value:</strong>
                                                <p class="text-info" id="amenity-value">₹0</p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Market Adjustment:</strong>
                                                <p id="market-adjustment">0%</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <strong>Confidence Score:</strong>
                                                <div class="progress">
                                                    <div class="progress-bar" id="confidence-bar" style="width: 0%">0%</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Market Analysis</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <strong>Market Position:</strong>
                                            <p class="badge" id="market-position">Average</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Competitiveness:</strong>
                                            <p class="badge" id="competitiveness">Medium</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Comparable Properties:</strong>
                                            <p id="comparable-count">0</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Price Range:</strong>
                                            <small>
                                                <div>Min: ₹<span id="price-min">0</span></div>
                                                <div>Avg: ₹<span id="price-avg">0</span></div>
                                                <div>Max: ₹<span id="price-max">0</span></div>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recommendations -->
                    <div id="recommendations" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-lightbulb"></i> AI Recommendations</h5>
                            </div>
                            <div class="card-body" id="recommendations-list">
                                <!-- Recommendations will be populated here -->
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div id="loading-state" style="display: none;">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2">AI is analyzing property data and market trends...</p>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div id="error-state" style="display: none;">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span id="error-message">Error occurred during valuation</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.valuation-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.progress {
    height: 20px;
    background-color: #e9ecef;
}

.progress-bar {
    height: 20px;
    background-color: #28a745;
    transition: width 0.3s ease;
}

.badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-weight: bold;
}

.badge.badge-success {
    background-color: #28a745;
    color: white;
}

.badge.badge-warning {
    background-color: #ffc107;
    color: black;
}

.badge.badge-danger {
    background-color: #dc3545;
    color: white;
}

.spinner-border {
    display: inline-block;
    width: 2rem;
    height: 2rem;
    vertical-align: text-bottom;
    border: 0.25em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    border-top-color: #007bff;
    animation: spinner-border .75s linear infinite;
}

@keyframes spinner-border {
    to {
        border-top-color: #28a745;
    }
}
</style>

<script>
function generateValuation() {
    const propertyId = document.getElementById('property_id').value;
    
    if (!propertyId) {
        alert('Please enter a Property ID');
        return;
    }
    
    // Show loading state
    document.getElementById('loading-state').style.display = 'block';
    document.getElementById('valuation-results').style.display = 'none';
    document.getElementById('recommendations').style.display = 'none';
    document.getElementById('error-state').style.display = 'none';
    
    // Make API call
    fetch('/ai/property-valuation/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            property_id: propertyId
        })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loading-state').style.display = 'none';
        
        if (data.success) {
            displayValuationResults(data.data);
        } else {
            displayError(data.message);
        }
    })
    .catch(error => {
        document.getElementById('loading-state').style.display = 'none';
        displayError('Network error occurred');
    });
}

function displayValuationResults(data) {
    // Update valuation summary
    document.getElementById('base-valuation').textContent = '₹' + data.base_valuation.toLocaleString();
    document.getElementById('final-valuation').textContent = '₹' + data.final_valuation.toLocaleString();
    document.getElementById('location-multiplier').textContent = data.location_multiplier + 'x';
    document.getElementById('type-multiplier').textContent = data.type_multiplier + 'x';
    document.getElementById('amenity-value').textContent = '₹' + data.amenity_value.toLocaleString();
    document.getElementById('market-adjustment').textContent = data.market_adjustment;
    
    // Update confidence score
    const confidenceBar = document.getElementById('confidence-bar');
    confidenceBar.style.width = data.confidence_score + '%';
    confidenceBar.textContent = data.confidence_score + '%';
    
    // Update market analysis
    const marketAnalysis = data.market_analysis;
    document.getElementById('market-position').textContent = marketAnalysis.market_position.charAt(0).toUpperCase() + marketAnalysis.market_position.slice(1);
    document.getElementById('market-position').className = 'badge badge-' + getCompetitivenessClass(marketAnalysis.competitiveness);
    
    document.getElementById('competitiveness').textContent = marketAnalysis.competitiveness.charAt(0).toUpperCase() + marketAnalysis.competitiveness.slice(1);
    document.getElementById('competitiveness').className = 'badge badge-' + getCompetitivenessClass(marketAnalysis.competitiveness);
    
    document.getElementById('comparable-count').textContent = data.comparable_properties;
    
    if (marketAnalysis.price_range) {
        document.getElementById('price-min').textContent = marketAnalysis.price_range.min.toLocaleString();
        document.getElementById('price-avg').textContent = marketAnalysis.price_range.average.toLocaleString();
        document.getElementById('price-max').textContent = marketAnalysis.price_range.max.toLocaleString();
    }
    
    // Update recommendations
    displayRecommendations(data.recommendations);
    
    // Show results
    document.getElementById('valuation-results').style.display = 'block';
    document.getElementById('recommendations').style.display = 'block';
}

function displayRecommendations(recommendations) {
    const container = document.getElementById('recommendations-list');
    container.innerHTML = '';
    
    recommendations.forEach(rec => {
        const div = document.createElement('div');
        div.className = 'alert alert-' + getPriorityClass(rec.priority) + ' mb-2';
        
        const icon = getPriorityIcon(rec.priority);
        const type = getTypeIcon(rec.type);
        
        div.innerHTML = `
            <strong>${icon} ${type} ${rec.priority.toUpperCase()}:</strong>
            ${rec.message}
        `;
        
        container.appendChild(div);
    });
}

function displayError(message) {
    document.getElementById('error-message').textContent = message;
    document.getElementById('error-state').style.display = 'block';
}

function getPriorityClass(priority) {
    switch(priority) {
        case 'high': return 'danger';
        case 'medium': return 'warning';
        case 'low': return 'info';
        default: return 'secondary';
    }
}

function getPriorityIcon(priority) {
    switch(priority) {
        case 'high': return '<i class="fas fa-exclamation-triangle"></i>';
        case 'medium': return '<i class="fas fa-exclamation-circle"></i>';
        case 'low': return '<i class="fas fa-info-circle"></i>';
        default: return '<i class="fas fa-info"></i>';
    }
}

function getTypeIcon(type) {
    switch(type) {
        case 'price': return '<i class="fas fa-rupee-sign"></i>';
        case 'marketing': return '<i class="fas fa-bullhorn"></i>';
        case 'features': return '<i class="fas fa-star"></i>';
        default: return '<i class="fas fa-info"></i>';
    }
}

function getCompetitivenessClass(competitiveness) {
    switch(competitiveness) {
        case 'high': return 'success';
        case 'medium': return 'warning';
        case 'low': return 'danger';
        default: return 'secondary';
    }
}

// Auto-generate valuation on page load if property ID is in URL
const urlParams = new URLSearchParams(window.location.search);
const propertyId = urlParams.get('property_id');
if (propertyId) {
    document.getElementById('property_id').value = propertyId;
    setTimeout(() => generateValuation(), 1000);
}
</script>
