<?php

// TODO: Add proper error handling with try-catch blocks

$$page_title = 'AI Property Valuation - APS Dream Home';
$page_description = 'Advanced AI-powered property valuation and market analysis';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid py-4">
    <div class="ai-valuation-container">
        <div class="ai-header">
            <h1><i class="fas fa-chart-line me-3"></i>AI Property Valuation</h1>
            <p>Advanced AI-powered property valuation and market analysis</p>
        </div>
        
        <div class="ai-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="valuation-form-card">
                        <h3><i class="fas fa-home me-2"></i>Property Details</h3>
                        <form id="valuationForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Location</label>
                                    <select name="location" class="form-select" required>
                                        <option value="">Select Location</option>
                                        <option value="mumbai">Mumbai</option>
                                        <option value="delhi">Delhi</option>
                                        <option value="bangalore">Bangalore</option>
                                        <option value="pune">Pune</option>
                                        <option value="hyderabad">Hyderabad</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Property Type</label>
                                    <select name="type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="apartment">Apartment</option>
                                        <option value="house">House</option>
                                        <option value="villa">Villa</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Size (sqft)</label>
                                    <input type="number" name="size" class="form-control" value="1000" min="500" max="10000" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Age (years)</label>
                                    <input type="number" name="age" class="form-control" value="0" min="0" max="50" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Condition</label>
                                    <select name="condition" class="form-select" required>
                                        <option value="excellent">Excellent</option>
                                        <option value="good">Good</option>
                                        <option value="average" selected>Average</option>
                                        <option value="fair">Fair</option>
                                        <option value="poor">Poor</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Amenities</label>
                                <div class="amenities-grid">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="amenities[]" value="parking" id="parking">
                                        <label class="form-check-label" for="parking">Parking</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="amenities[]" value="gym" id="gym">
                                        <label class="form-check-label" for="gym">Gym</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="amenities[]" value="pool" id="pool">
                                        <label class="form-check-label" for="pool">Swimming Pool</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="amenities[]" value="garden" id="garden">
                                        <label class="form-check-label" for="garden">Garden</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="amenities[]" value="security" id="security">
                                        <label class="form-check-label" for="security">24/7 Security</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="amenities[]" value="power_backup" id="power_backup">
                                        <label class="form-check-label" for="power_backup">Power Backup</label>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-calculator me-2"></i>Calculate Valuation
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="valuation-results-card" id="valuationResults" style="display: none;">
                        <h3><i class="fas fa-chart-pie me-2"></i>Valuation Results</h3>
                        <div class="results-content">
                            <!-- Results will be populated here -->
                        </div>
                    </div>
                    
                    <div class="market-trends-card" id="marketTrends" style="display: none;">
                        <h3><i class="fas fa-chart-area me-2"></i>Market Trends</h3>
                        <div class="trends-content">
                            <!-- Market trends will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="investment-analysis-card" id="investmentAnalysis" style="display: none;">
                        <h3><i class="fas fa-coins me-2"></i>Investment Analysis</h3>
                        <div class="analysis-content">
                            <!-- Investment analysis will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ai-valuation-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    margin: 20px auto;
    max-width: 1400px;
}

.ai-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    text-align: center;
    border-radius: 20px 20px 0 0;
}

.valuation-form-card,
.valuation-results-card,
.market-trends-card,
.investment-analysis-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.amenities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
}

.form-check {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 5px;
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

.results-content,
.trends-content,
.analysis-content {
    margin-top: 20px;
}

.valuation-metric {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 15px;
}

.valuation-metric h4 {
    font-size: 1.2rem;
    margin-bottom: 10px;
}

.valuation-metric .value {
    font-size: 2rem;
    font-weight: bold;
}

.confidence-score {
    background: #28a745;
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    display: inline-block;
    margin: 10px 0;
}

.recommendation {
    background: #f8f9fa;
    border-left: 4px solid #667eea;
    padding: 15px;
    margin: 10px 0;
    border-radius: 0 8px 8px 0;
}

.trend-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
}

.trend-item:last-child {
    border-bottom: none;
}

.trend-label {
    font-weight: 600;
    color: #333;
}

.trend-value {
    font-weight: bold;
    color: #667eea;
}

.investment-score {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 20px;
}

.risk-level {
    padding: 10px 20px;
    border-radius: 25px;
    display: inline-block;
    margin: 5px;
    font-weight: bold;
}

.risk-low {
    background: #28a745;
    color: white;
}

.risk-medium {
    background: #ffc107;
    color: #333;
}

.risk-high {
    background: #dc3545;
    color: white;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 25px;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.form-control:focus,
.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}
</style>

<script>
document.getElementById('valuationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const propertyData = {};
    
    for (let [key, value] of formData.entries()) {
        if (key === 'amenities[]') {
            if (!propertyData.amenities) propertyData.amenities = [];
            propertyData.amenities.push(value);
        } else {
            propertyData[key] = value;
        }
    }
    
    // Show loading
    document.getElementById('valuationResults').style.display = 'block';
    document.getElementById('valuationResults').querySelector('.results-content').innerHTML = 
        '<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-3">Calculating valuation...</p></div>';
    
    // Calculate valuation
    fetch('/ai-valuation/calculate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayValuationResults(data.valuation);
            fetchMarketTrends(propertyData.location);
            fetchInvestmentAnalysis(propertyData);
        } else {
            showError('Failed to calculate valuation');
        }
    })
    .catch(error => {
        showError('Error calculating valuation');
        console.error(error);
    });
});

function displayValuationResults(valuation) {
    const resultsHTML = `
        <div class="valuation-metric">
            <h4>Estimated Value</h4>
            <div class="value">₹${valuation.estimated_price.toLocaleString('en-IN')}</div>
        </div>
        
        <div class="confidence-score">
            Confidence Score: ${(valuation.confidence_score * 100).toFixed(1)}%
        </div>
        
        <div class="recommendation">
            <h5><i class="fas fa-lightbulb me-2"></i>Recommendations</h5>
            <ul class="mb-0">
                ${valuation.recommendations.map(rec => `<li>${rec}</li>`).join('')}
            </ul>
        </div>
        
        <div class="recommendation">
            <h5><i class="fas fa-chart-line me-2"></i>Market Analysis</h5>
            <ul class="mb-0">
                <li>Market Trend: ${valuation.market_analysis.market_trend}</li>
                <li>Growth Rate: ${valuation.market_analysis.growth_rate}</li>
                <li>Demand Level: ${valuation.market_analysis.demand_level}</li>
                <li>Average Days on Market: ${valuation.market_analysis.average_days_on_market}</li>
            </ul>
        </div>
    `;
    
    document.getElementById('valuationResults').querySelector('.results-content').innerHTML = resultsHTML;
}

function fetchMarketTrends(location) {
    fetch(`/ai-valuation/market-trends?location=${location}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayMarketTrends(data.trends);
        }
    })
    .catch(error => console.error('Error fetching market trends:', error));
}

function displayMarketTrends(trends) {
    const trendsHTML = `
        <div class="trend-item">
            <span class="trend-label">Average Price per sqft</span>
            <span class="trend-value">₹${trends.avg_price_per_sqft.toLocaleString('en-IN')}</span>
        </div>
        <div class="trend-item">
            <span class="trend-label">6 Months Change</span>
            <span class="trend-value">${trends.price_change_6months}</span>
        </div>
        <div class="trend-item">
            <span class="trend-label">1 Year Change</span>
            <span class="trend-value">${trends.price_change_1year}</span>
        </div>
        <div class="trend-item">
            <span class="trend-label">Inventory Level</span>
            <span class="trend-value">${trends.inventory_level}</span>
        </div>
        <div class="trend-item">
            <span class="trend-label">Days on Market</span>
            <span class="trend-value">${trends.days_on_market}</span>
        </div>
        <div class="trend-item">
            <span class="trend-label">Market Sentiment</span>
            <span class="trend-value">${trends.market_sentiment}</span>
        </div>
    `;
    
    document.getElementById('marketTrends').style.display = 'block';
    document.getElementById('marketTrends').querySelector('.trends-content').innerHTML = trendsHTML;
}

function fetchInvestmentAnalysis(propertyData) {
    const formData = new FormData();
    formData.append('property_data', JSON.stringify(propertyData));
    
    fetch('/ai-valuation/investment-analysis', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayInvestmentAnalysis(data.analysis);
        }
    })
    .catch(error => console.error('Error fetching investment analysis:', error));
}

function displayInvestmentAnalysis(analysis) {
    const riskClass = `risk-${analysis.risk_assessment.overall_risk.toLowerCase()}`;
    
    const analysisHTML = `
        <div class="investment-score">
            <h4>Investment Score</h4>
            <div class="value">${analysis.investment_score}/100</div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-chart-line me-2"></i>ROI Projection</h5>
                <div class="trend-item">
                    <span class="trend-label">Annual ROI</span>
                    <span class="trend-value">' + analysis.roi_projection.annual_roi + '%</span>
                </div>
                <div class="trend-item">
                    <span class="trend-label">5 Year Projection</span>
                    <span class="trend-value">' + analysis.roi_projection.five_year_projection + '%</span>
                </div>
                <div class="trend-item">
                    <span class="trend-label">10 Year Projection</span>
                    <span class="trend-value">' + analysis.roi_projection.ten_year_projection + '%</span>
                </div>
            </div>
            
            <div class="col-md-6">
                <h5><i class="fas fa-shield-alt me-2"></i>Risk Assessment</h5>
                <div class="trend-item">
                    <span class="trend-label">Market Risk</span>
                    <span class="risk-level risk-' + (analysis.risk_assessment.market_risk <= 2 ? 'low' : analysis.risk_assessment.market_risk <= 3.5 ? 'medium' : 'high') + '">' + analysis.risk_assessment.market_risk + '/5</span>
                </div>
                <div class="trend-item">
                    <span class="trend-label">Property Risk</span>
                    <span class="risk-level risk-' + (analysis.risk_assessment.property_risk <= 2 ? 'low' : analysis.risk_assessment.property_risk <= 3.5 ? 'medium' : 'high') + '">' + analysis.risk_assessment.property_risk + '/5</span>
                </div>
                <div class="trend-item">
                    <span class="trend-label">Location Risk</span>
                    <span class="risk-level risk-' + (analysis.risk_assessment.location_risk <= 2 ? 'low' : analysis.risk_assessment.location_risk <= 3.5 ? 'medium' : 'high') + '">' + analysis.risk_assessment.location_risk + '/5</span>
                </div>
                <div class="trend-item">
                    <span class="trend-label">Overall Risk</span>
                    <span class="risk-level risk-' + riskClass + '">' + analysis.risk_assessment.overall_risk + '</span>
                </div>
            </div>
        </div>
        
        <div class="recommendation">
            <h5><i class="fas fa-thumbs-up me-2"></i>Investment Recommendation</h5>
            <p class="mb-0">' + analysis.recommendation + '</p>
        </div>
    `;
    
    document.getElementById('investmentAnalysis').style.display = 'block';
    document.getElementById('investmentAnalysis').querySelector('.analysis-content').innerHTML = analysisHTML;
}

function showError(message) {
    document.getElementById('valuationResults').querySelector('.results-content').innerHTML = 
        '<div class="alert alert-danger">' + message + '</div>';
}
</script>
