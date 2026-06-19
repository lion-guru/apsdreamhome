<section class="py-5" style="background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><i class="fas fa-home me-2"></i><?php echo __('tool_valuation_title', [], 'Property Valuation Calculator'); ?></h1>
            <p class="text-white-50"><?php echo __('tool_valuation_subtitle', [], 'Find out your property\'s estimated market value instantly'); ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('location_district', [], 'Location / District'); ?></label>
                                <select class="form-select" id="valCity">
                                    <option value="">-- <?php echo __('select_district', [], 'Select District'); ?> --</option>
                                    <?php foreach (($districts ?? []) as $d): ?>
                                        <option value="<?= htmlspecialchars($d['name']) ?>"><?= htmlspecialchars($d['name']) ?></option>
                                    <?php endforeach; ?>
                                    <option value="Gorakhpur">Gorakhpur</option>
                                    <option value="Lucknow">Lucknow</option>
                                    <option value="Varanasi">Varanasi</option>
                                    <option value="Kushinagar">Kushinagar</option>
                                    <option value="Ayodhya">Ayodhya</option>
                                    <option value="Prayagraj">Prayagraj</option>
                                    <option value="Noida">Noida</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('property_type', [], 'Property Type'); ?></label>
                                <select class="form-select" id="valType">
                                    <option value="plot"><?php echo __('residential_plot', [], 'Residential Plot'); ?></option>
                                    <option value="house"><?php echo __('house_villa', [], 'House / Villa'); ?></option>
                                    <option value="flat"><?php echo __('flat_apartment', [], 'Flat / Apartment'); ?></option>
                                    <option value="shop"><?php echo __('commercial_shop', [], 'Commercial Shop'); ?></option>
                                    <option value="farmhouse"><?php echo __('farmhouse', [], 'Farmhouse'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('area_sqft', [], 'Area (sq ft)'); ?></label>
                                <input type="number" class="form-control" id="valArea" value="1200" min="0" placeholder="<?php echo __('eg_1200', [], 'e.g. 1200'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('bedrooms', [], 'Bedrooms'); ?></label>
                                <select class="form-select" id="valBedrooms">
                                    <option value="0"><?php echo __('na_plot_land', [], 'N/A (Plot/Land)'); ?></option>
                                    <option value="1">1 BHK</option>
                                    <option value="2">2 BHK</option>
                                    <option value="3" selected>3 BHK</option>
                                    <option value="4">4 BHK</option>
                                    <option value="5">5+ BHK</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('age_of_property', [], 'Age of Property'); ?></label>
                                <select class="form-select" id="valAge">
                                    <option value="0"><?php echo __('new_0_5', [], 'New (0-5 years)'); ?></option>
                                    <option value="5">5-10 <?php echo __('years', [], 'years'); ?></option>
                                    <option value="10">10-20 <?php echo __('years', [], 'years'); ?></option>
                                    <option value="20">20+ <?php echo __('years', [], 'years'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('condition', [], 'Condition'); ?></label>
                                <select class="form-select" id="valCondition">
                                    <option value="new"><?php echo __('new_well_maintained', [], 'New / Well-maintained'); ?></option>
                                    <option value="old"><?php echo __('old_needs_repair', [], 'Old / Needs Repair'); ?></option>
                                    <option value="renovated"><?php echo __('recently_renovated', [], 'Recently Renovated'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button class="btn btn-primary btn-lg w-100" onclick="calcValuation()"><i class="fas fa-calculator me-1"></i><?php echo __('estimate_value', [], 'Estimate Value'); ?></button>
                            </div>
                        </div>

                        <div id="valResults" style="display:none;">
                            <div class="row g-3 text-center mb-3">
                                <div class="col-md-3 col-6">
                                    <div class="bg-light rounded-3 p-3">
                                        <small class="text-muted d-block"><?php echo __('price_per_sqft', [], 'Price per Sq Ft'); ?></small>
                                        <h5 class="text-primary mb-0" id="perSqft">&#8377;3,000</h5>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="bg-primary text-white rounded-3 p-3">
                                        <small class="d-block"><?php echo __('estimated_value', [], 'Estimated Value'); ?></small>
                                        <h5 class="mb-0" id="totalValue">&#8377;36,00,000</h5>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="bg-light rounded-3 p-3">
                                        <small class="text-muted d-block"><?php echo __('low_range', [], 'Low Range'); ?></small>
                                        <h5 class="text-success mb-0" id="lowRange">&#8377;32.4L</h5>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="bg-light rounded-3 p-3">
                                        <small class="text-muted d-block"><?php echo __('high_range', [], 'High Range'); ?></small>
                                        <h5 class="text-danger mb-0" id="highRange">&#8377;39.6L</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card bg-light border-0 mt-2">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted"><?php echo __('confidence_score', [], 'Confidence Score'); ?></small>
                                            <div class="progress mt-1" style="width:200px;height:8px;">
                                                <div class="progress-bar bg-success" id="confBar" style="width:70%"></div>
                                            </div>
                                        </div>
                                        <span class="badge bg-success" id="confBadge">70%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted small mt-3 mb-0"><i class="fas fa-info-circle me-1"></i><?php echo __('valuation_disclaimer', [], 'Estimate based on location averages and property characteristics. Actual value may vary depending on road access, amenities, and market conditions. For accurate valuation, contact our team at'); ?> <strong>+91 92771 21112</strong>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
const baseRates = {
    'gorakhpur': 3000, 'lucknow': 4500, 'varanasi': 3800, 'kushinagar': 2200,
    'ayodhya': 3500, 'prayagraj': 3200, 'noida': 7000, 'ghaziabad': 6000,
    'meerut': 4500, 'kanpur': 4000, 'agra': 3500, 'bareilly': 3000,
    'jhansi': 2800, 'aligarh': 3000, 'saharanpur': 3200, 'moradabad': 3000,
    'rampur': 2500, 'ballia': 2000, 'deoria': 2200, 'azamgarh': 2300,
    'basti': 2000, 'mahrajganj': 1800, 'siddharthnagar': 1800, 'gonda': 2000,
    'bahraich': 2000, 'sitapur': 2200, 'hardoi': 2200,
    'barabanki': 2800, 'unnao': 2200, 'fatehpur': 2000, 'pratapgarh': 2000,
    'jaunpur': 2000, 'mirzapur': 2200, 'sonbhadra': 1500, 'chitrakoot': 1800,
    'mahoba': 1800, 'hamirpur': 1800, 'jalaun': 2000, 'lalitpur': 2000,
    'eteawah': 2200, 'firozabad': 2500, 'mainpuri': 2200, 'etah': 2200,
    'budaun': 2200, 'pilibhit': 2200, 'shahjahanpur': 2200
};
const typeMultipliers = { 'plot': 1.0, 'house': 1.25, 'flat': 1.15, 'shop': 1.5, 'farmhouse': 1.3 };
const condFactors = { 'new': 1.0, 'old': 0.85, 'renovated': 0.95 };

function fmtINR(n) {
    if (n >= 10000000) return '\u20B9' + (n / 10000000).toFixed(1) + ' Cr';
    if (n >= 100000) return '\u20B9' + (n / 100000).toFixed(1) + ' L';
    return '\u20B9' + Math.round(n).toLocaleString('en-IN');
}

function calcValuation() {
    const location = (document.getElementById('valCity').value || '').trim().toLowerCase();
    const type = document.getElementById('valType').value;
    const area = parseFloat(document.getElementById('valArea').value) || 0;
    const bedrooms = parseInt(document.getElementById('valBedrooms').value) || 0;
    const age = parseInt(document.getElementById('valAge').value) || 0;
    const condition = document.getElementById('valCondition').value;
    if (area <= 0) { document.getElementById('valResults').style.display = 'none'; return; }
    let rate = baseRates[location] || 2500;
    let typeMul = typeMultipliers[type] || 1.0;
    if (bedrooms > 0 && type !== 'plot') typeMul += (bedrooms - 2) * 0.03;
    let ageFactor = 1.0;
    if (age <= 5) ageFactor = 1.0;
    else if (age <= 10) ageFactor = 0.95;
    else if (age <= 20) ageFactor = 0.85;
    else ageFactor = 0.75;
    let condFactor = condFactors[condition] || 1.0;
    let pricePerSqft = rate * typeMul * ageFactor * condFactor;
    let estimated = pricePerSqft * area;
    let minPrice = estimated * 0.85;
    let maxPrice = estimated * 1.15;
    let confidence = 60;
    if (location && baseRates[location]) confidence += 10;
    if (type !== 'plot') confidence += 5;
    if (age < 5) confidence += 5;
    if (area > 0) confidence += 5;
    confidence = Math.min(confidence, 95);
    document.getElementById('perSqft').textContent = '\u20B9' + Math.round(pricePerSqft).toLocaleString('en-IN');
    document.getElementById('totalValue').textContent = '\u20B9' + Math.round(estimated).toLocaleString('en-IN');
    document.getElementById('lowRange').textContent = fmtINR(minPrice);
    document.getElementById('highRange').textContent = fmtINR(maxPrice);
    document.getElementById('confBar').style.width = confidence + '%';
    document.getElementById('confBadge').textContent = confidence + '%';
    document.getElementById('valResults').style.display = 'block';
}
['valCity','valType','valArea','valBedrooms','valAge','valCondition'].forEach(function(id) {
    document.getElementById(id).addEventListener('change', calcValuation);
    document.getElementById(id).addEventListener('input', calcValuation);
});
calcValuation();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
