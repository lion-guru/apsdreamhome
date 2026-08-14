<section class="py-5" class="style-30941">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><i class="fas fa-percentage me-2"></i><?php echo __('tool_gst_calculator_title', [], 'GST Calculator'); ?></h1>
            <p class="text-white-50"><?php echo __('tool_gst_calculator_subtitle', [], 'Real estate property par applicable GST calculate karein'); ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('base_price_rs', [], 'Base Price (â‚¹)'); ?></label>
                            <input type="number" class="form-control form-control-lg" id="basePrice" value="5000000" oninput="calcGST()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('property_type', [], 'Property Type'); ?></label>
                            <select class="form-select" id="propType" onchange="calcGST()">
                                <option value="affordable"><?php echo __('affordable_housing', [], 'Affordable Housing (Up to â‚¹45L, â‰¤60 sqm)'); ?></option>
                                <option value="under_construction"><?php echo __('under_construction', [], 'Under Construction'); ?></option>
                                <option value="ready"><?php echo __('ready_to_move', [], 'Ready to Move (No GST)'); ?></option>
                                <option value="commercial_prop"><?php echo __('commercial_property', [], 'Commercial Property'); ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('input_tax_credit', [], 'Input Tax Credit (ITC)?'); ?></label>
                            <select class="form-select" id="itcOpt" onchange="calcGST()">
                                <option value="yes"><?php echo __('yes_with_itc', [], 'Yes - ITC Available (1% without ITC)'); ?></option>
                                <option value="no"><?php echo __('no_itc', [], 'No - Without ITC (5% without ITC)'); ?></option>
                            </select>
                        </div>
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('gst_amount', [], 'GST Amount'); ?></small>
                                    <h4 class="text-danger mb-0" id="gstAmt">â‚¹2,50,000</h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-primary text-white rounded-3 p-3">
                                    <small class="d-block"><?php echo __('total_price', [], 'Total Price (incl. GST)'); ?></small>
                                    <h4 class="mb-0" id="totalPrice">â‚¹52,50,000</h4>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mt-3 mb-0"><i class="fas fa-info-circle me-1"></i><?php echo __('gst_disclaimer', [], 'GST rates for real estate: Affordable housing - 1% (without ITC), Other than affordable - 5% (without ITC). Ready-to-move properties with OC do not attract GST. Commercial property - 12% with ITC. Stamp duty and registration charges are separate.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function calcGST() {
    const base = parseFloat(document.getElementById('basePrice').value) || 0;
    const type = document.getElementById('propType').value;
    const itc = document.getElementById('itcOpt').value;
    let gstRate = 0;
    if (type === 'ready') gstRate = 0;
    else if (type === 'affordable') gstRate = itc === 'yes' ? 1 : 1;
    else if (type === 'under_construction') gstRate = itc === 'yes' ? 1 : 5;
    else if (type === 'commercial_prop') gstRate = 12;
    const gst = base * gstRate / 100;
    document.getElementById('gstAmt').textContent = '\u20B9' + Math.round(gst).toLocaleString('en-IN');
    document.getElementById('totalPrice').textContent = '\u20B9' + Math.round(base + gst).toLocaleString('en-IN');
}
calcGST();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
