<?php

/**
 * AI Property Valuation Page
 * Complete AI-powered property valuation and market analysis
 */

$page_title = 'AI Property Valuation - APS Dream Home';
include __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2"><i class="fas fa-robot me-2"></i>AI Property Valuation</h1>
            <p class="text-muted">Get AI-powered property price estimates based on market trends and comparable properties</p>
        </div>
    </div>

    <div class="row">
        <!-- Valuation Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Property Details</h5>
                </div>
                <div class="card-body">
                    <form id="valuation-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <select class="form-select" id="location" name="location" required>
                                    <option value="">Select Location</option>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?= htmlspecialchars($location) ?>"><?= htmlspecialchars($location) ?></option>
                                    <?php endforeach; ?>
                                    <option value="Gorakhpur">Gorakhpur</option>
                                    <option value="Lucknow">Lucknow</option>
                                    <option value="Noida">Noida</option>
                                    <option value="Delhi">Delhi</option>
                                    <option value="Mumbai">Mumbai</option>
                                    <option value="Bangalore">Bangalore</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Property Type</label>
                                <select class="form-select" id="property_type" name="property_type" required>
                                    <option value="">Select Type</option>
                                    <?php foreach ($property_types as $type): ?>
                                        <option value="<?= $type ?>"><?= ucfirst($type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Area (sqft)</label>
                                <input type="number" class="form-control" id="area_sqft" name="area_sqft"
                                    placeholder="e.g. 1200" required min="100">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Bedrooms</label>
                                <input type="number" class="form-control" id="bedrooms" name="bedrooms"
                                    placeholder="e.g. 3" min="0">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Bathrooms</label>
                                <input type="number" class="form-control" id="bathrooms" name="bathrooms"
                                    placeholder="e.g. 2" min="0">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-magic me-2"></i>Generate Valuation
                            </button>
                        </div>
                    </form>

                    <!-- Valuation Result -->
                    <div id="valuation-result" class="mt-4" style="display: none;">
                        <hr>
                        <div class="alert alert-success">
                            <h5 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Estimated Price</h5>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h2 class="text-primary mb-0" id="estimated-price">₹0</h2>
                                    <small class="text-muted">Estimated Market Value</small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <h4 class="text-success mb-0" id="price-per-sqft">₹0/sqft</h4>
                                    <small class="text-muted">Price per sqft</small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="mb-1"><strong>Price Range:</strong> <span id="price-range">₹0 - ₹0</span></p>
                                <p class="mb-1"><strong>Confidence:</strong> <span id="confidence-score">0%</span></p>
                                <p class="mb-0"><strong>Similar Properties:</strong> <span id="similar-count">0</span> found</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Select from Existing Properties -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Or Select Existing Property</h5>
                </div>
                <div class="card-body">
                    <div class="row" id="properties-grid">
                        <?php foreach ($properties as $property): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card property-card" style="cursor: pointer;"
                                    onclick="selectProperty('<?= htmlspecialchars($property['location']) ?>', 
                                         <?= $property['area_sqft'] ?>, '<?= $property['property_type'] ?>', 
                                         <?= $property['bedrooms'] ?? 0 ?>, <?= $property['bathrooms'] ?? 0 ?>, this)">
                                    <div class="row g-0">
                                        <div class="col-4">
                                            <?php if ($property['primary_image']): ?>
                                                <img src="/<?= htmlspecialchars($property['primary_image']) ?>"
                                                    class="img-fluid rounded-start h-100" style="object-fit: cover;" alt="">
                                            <?php else: ?>
                                                <div class="bg-light h-100 d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-home text-muted fa-2x"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-8">
                                            <div class="card-body py-2">
                                                <h6 class="card-title mb-1 text-truncate"><?= htmlspecialchars($property['title']) ?></h6>
                                                <p class="card-text small text-muted mb-1">
                                                    <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($property['location']) ?>
                                                </p>
                                                <p class="card-text small mb-0">
                                                    <span class="me-2"><i class="fas fa-ruler-combined me-1"></i><?= $property['area_sqft'] ?> sqft</span>
                                                    <span><i class="fas fa-bed me-1"></i><?= $property['bedrooms'] ?> BHK</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Valuations -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Valuations</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentValuations)): ?>
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No recent valuations</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($recentValuations as $valuation): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 text-truncate" style="max-width: 70%;">
                                            <?= $valuation['property_title'] ?? $valuation['location'] ?>
                                        </h6>
                                        <small class="text-muted"><?= date('M d', strtotime($valuation['created_at'])) ?></small>
                                    </div>
                                    <p class="mb-1">
                                        <strong class="text-primary">₹<?= number_format($valuation['estimated_price']) ?></strong>
                                        <small class="text-muted">(<?= $valuation['area_sqft'] ?> sqft)</small>
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-chart-line me-1"></i>Confidence: <?= $valuation['confidence_score'] ?>%
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- How It Works -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>How It Works</h5>
                </div>
                <div class="card-body">
                    <ol class="list-group list-group-numbered list-group-flush">
                        <li class="list-group-item px-0">Enter property details or select existing property</li>
                        <li class="list-group-item px-0">AI analyzes market trends and comparable properties</li>
                        <li class="list-group-item px-0">Get estimated market value with confidence score</li>
                        <li class="list-group-item px-0">View similar properties for comparison</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('valuation-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = {
            location: document.getElementById('location').value,
            property_type: document.getElementById('property_type').value,
            area_sqft: parseFloat(document.getElementById('area_sqft').value),
            bedrooms: parseInt(document.getElementById('bedrooms').value) || 0,
            bathrooms: parseInt(document.getElementById('bathrooms').value) || 0
        };

        try {
            const response = await fetch('/api/ai/valuation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                displayValuation(data.valuation);
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            alert('Failed to generate valuation');
        }
    });

    function displayValuation(valuation) {
        document.getElementById('estimated-price').textContent = '₹' + valuation.estimated_price.toLocaleString();
        document.getElementById('price-per-sqft').textContent = '₹' + valuation.price_per_sqft + '/sqft';
        document.getElementById('price-range').textContent = '₹' + valuation.price_range.min.toLocaleString() + ' - ₹' + valuation.price_range.max.toLocaleString();
        document.getElementById('confidence-score').textContent = valuation.confidence_score + '%';
        document.getElementById('similar-count').textContent = valuation.similar_properties_count;

        document.getElementById('valuation-result').style.display = 'block';

        document.getElementById('valuation-result').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function selectProperty(location, area, type, bedrooms, bathrooms, element) {
        document.getElementById('location').value = location;
        document.getElementById('property_type').value = type;
        document.getElementById('area_sqft').value = area;
        document.getElementById('bedrooms').value = bedrooms;
        document.getElementById('bathrooms').value = bathrooms;

        document.querySelectorAll('.property-card').forEach(card => {
            card.classList.remove('border', 'border-primary', 'border-2');
        });
        element.classList.add('border', 'border-primary', 'border-2');

        document.getElementById('valuation-form').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
</script>

<style>
    .property-card {
        transition: all 0.3s ease;
        border: 1px solid #dee2e6;
    }

    .property-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>