<section class="py-5" class="style-30941">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><?php echo __('tool_rent_vs_buy_title', [], 'Rent vs Buy Calculator'); ?></h1>
            <p class="text-white-50"><?php echo __('tool_rent_vs_buy_subtitle', [], 'Understand whether renting or buying is better for you'); ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('property_price_rs', [], 'Property Price (â‚¹)'); ?></label>
                                <input type="number" class="form-control form-control-lg" id="propPrice" value="5000000" oninput="calcRB()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('monthly_rent_rs', [], 'Monthly Rent (â‚¹)'); ?></label>
                                <input type="number" class="form-control form-control-lg" id="monthRent" value="15000" oninput="calcRB()">
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('tenure_years', [], 'Tenure (Years)'); ?></label>
                                <input type="number" class="form-control" id="tenure" value="10" min="1" max="30" oninput="calcRB()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('rent_growth', [], 'Rent Growth (%)'); ?></label>
                                <input type="number" class="form-control" id="rentGr" value="5" oninput="calcRB()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('appreciation', [], 'Appreciation (%)'); ?></label>
                                <input type="number" class="form-control" id="appRate" value="8" oninput="calcRB()">
                            </div>
                        </div>

                        <div class="row g-3 text-center">
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('total_rent_paid', [], 'Total Rent Paid'); ?></small>
                                    <h4 class="text-danger mb-0" id="rentTotal">â‚¹18,00,000</h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('property_future_value', [], 'Property Future Value'); ?></small>
                                    <h4 class="text-success mb-0" id="propFuture">â‚¹10,80,000</h4>
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
function calcRB() {
    const price = parseFloat(document.getElementById('propPrice').value) || 0;
    const rent = parseFloat(document.getElementById('monthRent').value) || 0;
    const tenure = parseInt(document.getElementById('tenure').value) || 10;
    const rentGr = (parseFloat(document.getElementById('rentGr').value) || 5) / 100;
    const appRate = (parseFloat(document.getElementById('appRate').value) || 8) / 100;
    let totalRent = 0;
    let annualRent = rent * 12;
    for (let i = 0; i < tenure; i++) { totalRent += annualRent; annualRent *= (1 + rentGr); }
    const futureVal = price * Math.pow(1 + appRate, tenure);
    document.getElementById('rentTotal').textContent = '\u20B9' + Math.round(totalRent).toLocaleString('en-IN');
    document.getElementById('propFuture').textContent = '\u20B9' + Math.round(futureVal).toLocaleString('en-IN');
}
calcRB();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
