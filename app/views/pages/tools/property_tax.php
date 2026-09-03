<section class="py-5 text-white" style="background: linear-gradient(135deg, #0a192f 0%, #1e3a5f 100%)">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><?php echo __('tool_property_tax_title', [], 'Property Tax Calculator'); ?></h1>
            <p class="text-white-50"><?php echo __('tool_property_tax_subtitle', [], 'Know the estimated annual tax on your property'); ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('property_value_rs', [], 'Property Value (â‚¹)'); ?></label>
                            <input type="number" class="form-control form-control-lg" id="propVal" value="5000000" oninput="calcTax()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('area_sqft', [], 'Area (sq ft)'); ?></label>
                            <input type="number" class="form-control" id="area" value="1500" oninput="calcTax()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('property_type', [], 'Property Type'); ?></label>
                            <select class="form-select" id="propType" onchange="calcTax()">
                                <option value="residential"><?php echo __('residential', [], 'Residential'); ?></option>
                                <option value="commercial"><?php echo __('commercial', [], 'Commercial'); ?></option>
                            </select>
                        </div>
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('annual_tax', [], 'Annual Tax'); ?></small>
                                    <h4 class="text-danger mb-0" id="annualTax">â‚¹7,500</h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('monthly_tax', [], 'Monthly Tax'); ?></small>
                                    <h4 class="text-primary mb-0" id="monthlyTax">â‚¹625</h4>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mt-3 mb-0"><i class="fas fa-info-circle me-1"></i><?php echo __('property_tax_disclaimer', [], 'Property tax rates vary by municipality. This is an estimate based on standard municipal rates. Contact your local municipal corporation for exact tax amounts.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function calcTax() {
    const val = parseFloat(document.getElementById('propVal').value) || 0;
    const area = parseFloat(document.getElementById('area').value) || 1500;
    const type = document.getElementById('propType').value;
    let rate = type === 'commercial' ? 0.003 : 0.0015;
    const tax = Math.max(500, val * rate);
    document.getElementById('annualTax').textContent = '\u20B9' + Math.round(tax).toLocaleString('en-IN');
    document.getElementById('monthlyTax').textContent = '\u20B9' + Math.round(tax / 12).toLocaleString('en-IN');
}
calcTax();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
