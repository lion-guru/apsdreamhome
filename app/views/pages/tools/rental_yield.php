<section class="py-5" style="background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><?php echo __('tool_rental_yield_title', [], 'Rental Yield Calculator'); ?></h1>
            <p class="text-white-50"><?php echo __('tool_rental_yield_subtitle', [], 'Apni property se expected rental return jaanein'); ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('property_value_rs', [], 'Property Value (₹)'); ?></label>
                            <input type="number" class="form-control form-control-lg" id="propVal" value="5000000" oninput="calcYield()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('monthly_rent_rs', [], 'Monthly Rent (₹)'); ?></label>
                            <input type="number" class="form-control form-control-lg" id="rentAmt" value="15000" oninput="calcYield()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('annual_expenses_pct', [], 'Annual Expenses (%)'); ?></label>
                            <input type="number" class="form-control" id="expPct" value="10" oninput="calcYield()">
                        </div>
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('gross_yield', [], 'Gross Yield'); ?></small>
                                    <h4 class="text-primary mb-0" id="grossYield">3.6%</h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-success text-white rounded-3 p-3">
                                    <small class="d-block"><?php echo __('net_yield', [], 'Net Yield'); ?></small>
                                    <h4 class="mb-0" id="netYield">3.24%</h4>
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
function calcYield() {
    const val = parseFloat(document.getElementById('propVal').value) || 1;
    const rent = parseFloat(document.getElementById('rentAmt').value) || 0;
    const exp = (parseFloat(document.getElementById('expPct').value) || 10) / 100;
    const gross = (rent * 12 / val) * 100;
    const net = gross * (1 - exp);
    document.getElementById('grossYield').textContent = gross.toFixed(1) + '%';
    document.getElementById('netYield').textContent = net.toFixed(1) + '%';
}
calcYield();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
