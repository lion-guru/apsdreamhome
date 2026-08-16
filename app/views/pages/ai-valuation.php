<?php

/**
 * AI Property Valuation Page
 * Complete AI-powered property valuation and market analysis
 */

$page_title = $page_title ?? __('user_ai_valuation_title', 'AI Property Valuation - APS Dream Home');
$page_heading = $page_heading ?? __('user_ai_valuation_heading', 'AI Property Valuation');
$content = $content ?? '';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2"><i class="fas fa-robot me-2"></i><?= __('user_ai_valuation_page_heading', 'AI Property Valuation') ?></h1>
            <p class="text-muted"><?= __('user_ai_valuation_subtitle', 'Get AI-powered property price estimates based on market trends and comparable properties') ?></p>
        </div>
    </div>

    <div class="row">
        <!-- Valuation Form -->
        <div class="col-md-8">
            <div class="card aps-cp-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i><?= __('user_ai_valuation_property_details', 'Property Details') ?></h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form id="valuation-form">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?= __('user_ai_valuation_label_location', 'Location') ?></label>
                                <select class="form-select" id="location" name="location" required>
                                    <option value=""><?= __('user_ai_valuation_select_location', 'Select Location') ?></option>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?= htmlspecialchars($location ?? '') ?>"><?= htmlspecialchars($location ?? '') ?></option>
                                    <?php endforeach; ?>
                                    <option value="Gorakhpur"><?= __('city_gorakhpur', 'Gorakhpur') ?></option>
                                    <option value="Lucknow"><?= __('city_lucknow', 'Lucknow') ?></option>
                                    <option value="Noida"><?= __('city_noida', 'Noida') ?></option>
                                    <option value="Delhi"><?= __('city_delhi', 'Delhi') ?></option>
                                    <option value="Mumbai"><?= __('city_mumbai', 'Mumbai') ?></option>
                                    <option value="Bangalore"><?= __('city_bangalore', 'Bangalore') ?></option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?= __('user_ai_valuation_label_property_type', 'Property Type') ?></label>
                                <select class="form-select" id="property_type" name="property_type" required>
                                    <option value=""><?= __('user_ai_valuation_select_type', 'Select Type') ?></option>
                                    <?php foreach ($property_types as $type): ?>
                                        <option value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"><?= ucfirst($type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?= __('user_ai_valuation_label_area', 'Area (sqft)') ?></label>
                                <input type="number" class="form-control" id="area_sqft" name="area_sqft"
                                    placeholder="<?= __('user_ai_valuation_placeholder_area', 'e.g. 1200') ?>" required min="100">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label"><?= __('user_ai_valuation_label_bedrooms', 'Bedrooms') ?></label>
                                <input type="number" class="form-control" id="bedrooms" name="bedrooms"
                                    placeholder="<?= __('user_ai_valuation_placeholder_bedrooms', 'e.g. 3') ?>" min="0">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label"><?= __('user_ai_valuation_label_bathrooms', 'Bathrooms') ?></label>
                                <input type="number" class="form-control" id="bathrooms" name="bathrooms"
                                    placeholder="<?= __('user_ai_valuation_placeholder_bathrooms', 'e.g. 2') ?>" min="0">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-magic me-2"></i><?= __('user_ai_valuation_btn_generate', 'Generate Valuation') ?>
                            </button>
                        </div>
                    </form>

                    <!-- Valuation Result -->
                    <div id="valuation-result" class="mt-4" class="style-54390">
                        <hr>
                        <div class="alert alert-success">
                            <h5 class="alert-heading"><i class="fas fa-check-circle me-2"></i><?= __('user_ai_valuation_estimated_price', 'Estimated Price') ?></h5>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h2 class="text-primary mb-0" id="estimated-price">₹0</h2>
                                    <small class="text-muted"><?= __('user_ai_valuation_market_value', 'Estimated Market Value') ?></small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <h4 class="text-success mb-0" id="price-per-sqft">₹0/sqft</h4>
                                    <small class="text-muted"><?= __('user_ai_valuation_price_per_sqft', 'Price per sqft') ?></small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="mb-1"><strong><?= __('user_ai_valuation_price_range', 'Price Range:') ?></strong> <span id="price-range">₹0 - ₹0</span></p>
                                <p class="mb-1"><strong><?= __('user_ai_valuation_confidence', 'Confidence:') ?></strong> <span id="confidence-score">0%</span></p>
                                <p class="mb-0"><strong><?= __('user_ai_valuation_similar_properties', 'Similar Properties:') ?></strong> <span id="similar-count">0</span> <?= __('user_ai_valuation_found', 'found') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Select from Existing Properties -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i><?= __('user_ai_valuation_select_existing', 'Or Select Existing Property') ?></h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row" id="properties-grid">
                        <?php foreach ($properties as $property): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card property-card" class="style-75920"
                                    onclick="selectProperty('<?= htmlspecialchars($property['location'] ?? '') ?>', 
                                         <?= $property['area_sqft'] ?>, '<?= $property['property_type'] ?>', 
                                         <?= $property['bedrooms'] ?? 0 ?>, <?= $property['bathrooms'] ?? 0 ?>, this)">
                                    <div class="row g-0">
                                        <div class="col-4">
                                            <?php if ($property['primary_image']): ?>
                                                <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg"
                                                    class="img-fluid rounded-start h-100" class="style-86926" alt="" />
                                            <?php else: ?>
                                                <div class="bg-light h-100 d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-home text-muted fa-2x"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-8">
                                            <div class="card-body py-2">
                                                <h6 class="card-title mb-1 text-truncate"><?= htmlspecialchars($property['title'] ?? '') ?></h6>
                                                <p class="card-text small text-muted mb-1">
                                                    <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($property['location'] ?? '') ?>
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
            <div class="card aps-cp-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i><?= __('user_ai_valuation_recent', 'Recent Valuations') ?></h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentValuations)): ?>
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p><?= __('user_ai_valuation_no_recent', 'No recent valuations') ?></p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush" class="style-61454">
                            <?php foreach ($recentValuations as $valuation): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 text-truncate" class="style-34136">
                                            <?= $valuation['property_title'] ?? $valuation['location'] ?>
                                        </h6>
                                        <small class="text-muted"><?= date('M d', strtotime($valuation['created_at'])) ?></small>
                                    </div>
                                    <p class="mb-1">
                                        <strong class="text-primary">₹<?= number_format($valuation['estimated_price']) ?></strong>
                                        <small class="text-muted">(<?= $valuation['area_sqft'] ?> sqft)</small>
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-chart-line me-1"></i><?= __('user_ai_valuation_confidence_label', 'Confidence:') ?> <?= $valuation['confidence_score'] ?>%
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
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i><?= __('user_ai_valuation_how_it_works', 'How It Works') ?></h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <ol class="list-group list-group-numbered list-group-flush">
                        <li class="list-group-item px-0"><?= __('user_ai_valuation_step1', 'Enter property details or select existing property') ?></li>
                        <li class="list-group-item px-0"><?= __('user_ai_valuation_step2', 'AI analyzes market trends and comparable properties') ?></li>
                        <li class="list-group-item px-0"><?= __('user_ai_valuation_step3', 'Get estimated market value with confidence score') ?></li>
                        <li class="list-group-item px-0"><?= __('user_ai_valuation_step4', 'View similar properties for comparison') ?></li>
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
            const response = await fetch('<?= BASE_URL ?>/api/ai/valuation', {
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
                alert('<?= __('user_ai_valuation_error', 'Error:') ?> ' + data.message);
            }
        } catch (error) {
            alert('<?= __('user_ai_valuation_failed', 'Failed to generate valuation') ?>');
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
