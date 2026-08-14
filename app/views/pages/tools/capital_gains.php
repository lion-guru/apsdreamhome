<section class="py-5" class="style-30941">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><i class="fas fa-chart-line me-2"></i><?php echo __('tool_capital_gains_title', [], 'Capital Gains Tax Calculator'); ?></h1>
            <p class="text-white-50"><?php echo __('tool_capital_gains_subtitle', [], 'Property bechne par kitna tax lagega jaanein'); ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('purchase_price_rs', [], 'Purchase Price (â‚¹)'); ?></label>
                                <input type="number" class="form-control" id="purchasePrice" value="3000000" oninput="calcCG()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('sale_price_rs', [], 'Sale Price (â‚¹)'); ?></label>
                                <input type="number" class="form-control" id="salePrice" value="5000000" oninput="calcCG()">
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('holding_period', [], 'Holding Period'); ?></label>
                                <select class="form-select" id="holdPeriod" onchange="calcCG()">
                                    <option value="short"><?php echo __('short_term', [], 'Short Term (â‰¤ 2 years)'); ?></option>
                                    <option value="long" selected><?php echo __('long_term', [], 'Long Term (> 2 years)'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('improvement_cost_rs', [], 'Improvement Cost (â‚¹)'); ?></label>
                                <input type="number" class="form-control" id="improveCost" value="200000" oninput="calcCG()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('indexation', [], 'Indexation Benefit?'); ?></label>
                                <select class="form-select" id="indexation" onchange="calcCG()">
                                    <option value="yes"><?php echo __('yes', [], 'Yes'); ?></option>
                                    <option value="no"><?php echo __('no', [], 'No'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 text-center">
                            <div class="col-md-4">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('taxable_gain', [], 'Taxable Gain'); ?></small>
                                    <h4 class="text-danger mb-0" id="taxableGain">â‚¹18,00,000</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('tax_rate', [], 'Tax Rate'); ?></small>
                                    <h4 class="text-warning mb-0" id="taxRate">20%</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-danger text-white rounded-3 p-3">
                                    <small class="d-block"><?php echo __('capital_gains_tax', [], 'Capital Gains Tax'); ?></small>
                                    <h4 class="mb-0" id="cgTax">â‚¹3,60,000</h4>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mt-3 mb-0"><i class="fas fa-info-circle me-1"></i><?php echo __('capital_gains_disclaimer', [], 'Short-term capital gains are taxed at applicable income tax slab rate. Long-term capital gains with indexation are taxed at 20% with indexation benefit. Without indexation (new regime), the rate is 12.5% as per Budget 2024. Consult a CA for exact tax computation.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function calcCG() {
    const purchase = parseFloat(document.getElementById('purchasePrice').value) || 0;
    const sale = parseFloat(document.getElementById('salePrice').value) || 0;
    const period = document.getElementById('holdPeriod').value;
    const improve = parseFloat(document.getElementById('improveCost').value) || 0;
    const indexation = document.getElementById('indexation').value;
    const indexedPurchase = purchase + improve;
    const gain = sale - indexedPurchase;
    let taxRate, tax;
    if (period === 'short') {
        taxRate = '30% (Slab)';
        tax = Math.max(0, gain) * 0.3;
    } else {
        if (indexation === 'yes') {
            taxRate = '20% (with Indexation)';
            tax = Math.max(0, gain) * 0.2;
        } else {
            taxRate = '12.5% (without Indexation)';
            tax = Math.max(0, gain) * 0.125;
        }
    }
    document.getElementById('taxableGain').textContent = '\u20B9' + Math.max(0, gain).toLocaleString('en-IN');
    document.getElementById('taxRate').textContent = taxRate;
    document.getElementById('cgTax').textContent = '\u20B9' + Math.round(tax).toLocaleString('en-IN');
}
calcCG();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
