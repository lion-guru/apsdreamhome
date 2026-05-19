<section class="py-5" style="background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold">Rental Yield Calculator</h1>
            <p class="text-white-50">Property ki rental income aur ROI ka accurate estimate paayein</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Property Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-lg" id="propPrice" value="5000000" min="1" oninput="calcRentalYield()">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Monthly Rent (₹) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="monthlyRent" value="25000" min="1" oninput="calcRentalYield()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Annual Maintenance (% of value)</label>
                                <input type="number" class="form-control" id="annualMaint" value="1" step="0.1" oninput="calcRentalYield()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Property Tax Annual (₹)</label>
                                <input type="number" class="form-control" id="propTax" value="0" oninput="calcRentalYield()">
                            </div>
                        </div>
                        <button class="btn btn-primary w-100 btn-lg mb-4" onclick="calcRentalYield()"><i class="fas fa-calculator me-2"></i>Calculate Yield</button>
                        <div id="yieldResults">
                            <div class="row g-3 text-center mb-3">
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Gross Rental Yield</small>
                                        <h5 class="text-primary mb-0" id="grossYield">6.00%</h5>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Net Rental Yield</small>
                                        <h5 class="text-success mb-0" id="netYield">5.00%</h5>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Payback Period</small>
                                        <h5 class="text-danger mb-0" id="paybackPeriod">16.7 years</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 text-center mb-3">
                                <div class="col-md-3">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Monthly Income</small>
                                        <h5 class="text-info mb-0" id="monthlyIncome">₹25,000</h5>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Annual Income</small>
                                        <h5 class="text-info mb-0" id="annualIncome">₹3,00,000</h5>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Annual Expenses</small>
                                        <h5 class="text-warning mb-0" id="annualExpenses">₹50,000</h5>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Net Annual Income</small>
                                        <h5 class="text-success mb-0" id="netAnnualIncome">₹2,50,000</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <h6 class="fw-bold text-center mb-3">Yield Gauge</h6>
                                <div class="mb-1 d-flex justify-content-between">
                                    <small>0%</small>
                                    <small id="gaugeLabel">6.00% Gross Yield</small>
                                    <small>20%+</small>
                                </div>
                                <div class="progress" style="height:24px;border-radius:12px;background:#e9ecef;">
                                    <div class="progress-bar" id="yieldGauge" role="progressbar" style="width:30%;border-radius:12px;background:linear-gradient(90deg,#28a745,#ffc107,#dc3545);" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-success">Excellent &gt;8%</small>
                                    <small class="text-warning">Good 4-8%</small>
                                    <small class="text-danger">Low &lt;4%</small>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mt-3 text-center mb-0"><i class="fas fa-info-circle me-1"></i>Note: These are estimated costs. Actual costs may vary based on location and materials.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function calcRentalYield() {
    const price = parseFloat(document.getElementById('propPrice').value) || 0;
    const rent = parseFloat(document.getElementById('monthlyRent').value) || 0;
    const maintPct = parseFloat(document.getElementById('annualMaint').value) || 0;
    const tax = parseFloat(document.getElementById('propTax').value) || 0;
    if (price <= 0 || rent <= 0) {
        document.getElementById('grossYield').textContent = '0%';
        document.getElementById('netYield').textContent = '0%';
        document.getElementById('paybackPeriod').textContent = '0 years';
        document.getElementById('monthlyIncome').textContent = '₹0';
        document.getElementById('annualIncome').textContent = '₹0';
        document.getElementById('annualExpenses').textContent = '₹0';
        document.getElementById('netAnnualIncome').textContent = '₹0';
        document.getElementById('yieldGauge').style.width = '0%';
        document.getElementById('gaugeLabel').textContent = '0% Gross Yield';
        return;
    }
    const annualRent = rent * 12;
    const maintCost = price * maintPct / 100;
    const totalExpenses = maintCost + tax;
    const netIncome = annualRent - totalExpenses;
    const grossYield = (annualRent / price) * 100;
    const netYield = price > 0 ? (netIncome / price) * 100 : 0;
    const payback = netIncome > 0 ? price / netIncome : 0;
    document.getElementById('monthlyIncome').textContent = '₹' + Math.round(rent).toLocaleString('en-IN');
    document.getElementById('annualIncome').textContent = '₹' + Math.round(annualRent).toLocaleString('en-IN');
    document.getElementById('annualExpenses').textContent = '₹' + Math.round(totalExpenses).toLocaleString('en-IN');
    document.getElementById('netAnnualIncome').textContent = '₹' + Math.round(netIncome).toLocaleString('en-IN');
    document.getElementById('grossYield').textContent = grossYield.toFixed(2) + '%';
    document.getElementById('netYield').textContent = netYield.toFixed(2) + '%';
    document.getElementById('paybackPeriod').textContent = payback.toFixed(1) + ' years';
    const gaugePct = Math.min(grossYield / 20 * 100, 100);
    document.getElementById('yieldGauge').style.width = gaugePct + '%';
    document.getElementById('yieldGauge').textContent = grossYield.toFixed(2) + '%';
    document.getElementById('gaugeLabel').textContent = grossYield.toFixed(2) + '% Gross Yield';
}
calcRentalYield();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
