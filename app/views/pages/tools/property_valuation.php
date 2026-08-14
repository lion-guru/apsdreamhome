<section class="py-5" class="style-30941">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><i class="fas fa-home me-2"></i><?php echo __('tool_property_valuation_title', [], 'Property Valuation Tool'); ?></h1>
            <p class="text-white-50"><?php echo __('tool_property_valuation_subtitle', [], 'Know the current market value of your property'); ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('property_type', [], 'Property Type'); ?></label>
                                <select class="form-select" id="propType" onchange="calcVal()">
                                    <option value="plot"><?php echo __('plot', [], 'Plot'); ?></option>
                                    <option value="house" selected><?php echo __('house', [], 'House'); ?></option>
                                    <option value="flat"><?php echo __('flat', [], 'Flat'); ?></option>
                                    <option value="shop"><?php echo __('shop', [], 'Shop'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('area_sqft', [], 'Area (sq ft)'); ?></label>
                                <input type="number" class="form-control form-control-lg" id="area" value="1500" min="100" oninput="calcVal()">
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('location', [], 'Location'); ?></label>
                                <select class="form-select" id="locSel" onchange="calcVal()">
                                    <option value="gorakhpur"><?php echo __('gorakhpur', [], 'Gorakhpur'); ?></option>
                                    <option value="lucknow"><?php echo __('lucknow', [], 'Lucknow'); ?></option>
                                    <option value="kushinagar"><?php echo __('kushinagar', [], 'Kushinagar'); ?></option>
                                    <option value="varanasi"><?php echo __('varanasi', [], 'Varanasi'); ?></option>
                                    <option value="delhi"><?php echo __('delhi', [], 'Delhi'); ?></option>
                                    <option value="mumbai"><?php echo __('mumbai', [], 'Mumbai'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('property_age_years', [], 'Property Age (Years)'); ?></label>
                                <input type="number" class="form-control" id="age" value="0" min="0" max="50" oninput="calcVal()">
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('bedrooms', [], 'Bedrooms'); ?></label>
                                <select class="form-select" id="beds" onchange="calcVal()">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3" selected>3</option>
                                    <option value="4">4</option>
                                    <option value="5">5+</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('bathrooms', [], 'Bathrooms'); ?></label>
                                <select class="form-select" id="baths" onchange="calcVal()">
                                    <option value="1">1</option>
                                    <option value="2" selected>2</option>
                                    <option value="3">3</option>
                                    <option value="4">4+</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('furnished_status', [], 'Furnished?'); ?></label>
                                <select class="form-select" id="furn" onchange="calcVal()">
                                    <option value="unfurnished"><?php echo __('unfurnished', [], 'Unfurnished'); ?></option>
                                    <option value="semi"><?php echo __('semi_furnished', [], 'Semi-Furnished'); ?></option>
                                    <option value="furnished"><?php echo __('fully_furnished', [], 'Fully Furnished'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 text-center">
                            <div class="col-md-4">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('estimated_value', [], 'Estimated Value'); ?></small>
                                    <h4 class="text-primary mb-0" id="estVal">â‚¹75,00,000</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('price_per_sqft', [], 'Price per sq ft'); ?></small>
                                    <h4 class="text-success mb-0" id="ppsf">â‚¹5,000</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-primary text-white rounded-3 p-3">
                                    <small class="d-block"><?php echo __('range', [], 'Range'); ?></small>
                                    <h5 class="mb-0" id="valRange">â‚¹67L â€” â‚¹83L</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function calcVal() {
    const type = document.getElementById('propType').value;
    const area = parseFloat(document.getElementById('area').value) || 0;
    const loc = document.getElementById('locSel').value;
    const age = parseInt(document.getElementById('age').value) || 0;
    const beds = parseInt(document.getElementById('beds').value) || 3;
    const baths = parseInt(document.getElementById('baths').value) || 2;
    const furn = document.getElementById('furn').value;
    const baseRates = { plot: 2800, house: 5000, flat: 4500, shop: 8000 };
    const locMult = { gorakhpur: 1.0, lucknow: 1.2, kushinagar: 0.7, varanasi: 1.1, delhi: 2.5, mumbai: 4.0 };
    let ppsf = (baseRates[type] || 5000) * (locMult[loc] || 1);
    if (age > 0) ppsf *= Math.max(0.5, 1 - age * 0.03);
    if (beds >= 4) ppsf *= 1.1;
    if (furn === 'furnished') ppsf *= 1.2;
    else if (furn === 'semi') ppsf *= 1.1;
    const total = Math.round(ppsf * area);
    const low = Math.round(total * 0.9);
    const high = Math.round(total * 1.1);
    document.getElementById('estVal').textContent = '\u20B9' + total.toLocaleString('en-IN');
    document.getElementById('ppsf').textContent = '\u20B9' + Math.round(ppsf).toLocaleString('en-IN');
    document.getElementById('valRange').textContent = '\u20B9' + (low / 100000).toFixed(1) + 'L \u2014 \u20B9' + (high / 100000).toFixed(1) + 'L';
}
calcVal();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
