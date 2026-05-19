<section class="py-5" style="background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold">Property Tax Calculator</h1>
            <p class="text-white-50">Apni property ka estimated annual tax aur breakdown turant dekhein</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Property Type</label>
                                <select class="form-select" id="taxPropType" onchange="calcPropertyTax()">
                                    <option value="1.0">Residential</option>
                                    <option value="1.5">Commercial</option>
                                    <option value="1.3">Industrial</option>
                                    <option value="0.7">Agricultural</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">City Type</label>
                                <select class="form-select" id="cityType" onchange="calcPropertyTax()">
                                    <option value="2.0">Metro</option>
                                    <option value="1.5" selected>Tier 2 City</option>
                                    <option value="1.0">Tier 3 City</option>
                                    <option value="0.6">Rural</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Property Value (₹) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-lg" id="taxPropVal" value="5000000" min="1" oninput="calcPropertyTax()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Age of Property</label>
                                <select class="form-select" id="propAge" onchange="calcPropertyTax()">
                                    <option value="1.0">&lt; 5 years</option>
                                    <option value="0.9" selected>5-10 years</option>
                                    <option value="0.8">10-20 years</option>
                                    <option value="0.7">&gt; 20 years</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Occupancy</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="occupancy" id="selfOcc" value="1.0" checked onchange="calcPropertyTax()">
                                    <label class="form-check-label" for="selfOcc">Self-Occupied</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="occupancy" id="rented" value="1.2" onchange="calcPropertyTax()">
                                    <label class="form-check-label" for="rented">Rented</label>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary w-100 btn-lg mb-4" onclick="calcPropertyTax()"><i class="fas fa-calculator me-2"></i>Calculate Tax</button>
                        <div id="taxResults">
                            <div class="row g-3 text-center mb-3">
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Taxable Value</small>
                                        <h5 class="text-info mb-0" id="taxableValue">₹36,00,000</h5>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Applicable Tax Rate</small>
                                        <h5 class="text-primary mb-0" id="taxRate">1.35%</h5>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Annual Property Tax</small>
                                        <h5 class="text-danger mb-0" id="annualTax">₹48,600</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 text-center mb-3">
                                <div class="col-md-6">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Monthly Tax Burden</small>
                                        <h5 class="text-warning mb-0" id="monthlyTax">₹4,050</h5>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Municipal Tax</small>
                                        <h5 class="text-success mb-0" id="municipalTax">₹36,450</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 p-3 bg-light rounded">
                                <h6 class="fw-bold mb-2">Tax Breakdown</h6>
                                <div class="d-flex justify-content-between mb-1">
                                    <small>Municipal Tax (75%)</small>
                                    <small id="municipalBreakdown">₹36,450</small>
                                </div>
                                <div class="progress mb-2" style="height:18px;">
                                    <div class="progress-bar bg-primary" id="municipalBar" style="width:75%">75%</div>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <small>Education Cess (12%)</small>
                                    <small id="educationCess">₹5,832</small>
                                </div>
                                <div class="progress mb-2" style="height:18px;">
                                    <div class="progress-bar bg-info" id="educationBar" style="width:12%">12%</div>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <small>Other Charges (13%)</small>
                                    <small id="otherCharges">₹6,318</small>
                                </div>
                                <div class="progress mb-2" style="height:18px;">
                                    <div class="progress-bar bg-secondary" id="otherBar" style="width:13%">13%</div>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3 mb-0 small">
                                <i class="fas fa-lightbulb me-2"></i><strong>Smart Suggestion:</strong> You can claim tax deduction under Section 24 for home loan interest up to ₹2,00,000 per year on self-occupied property.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function calcPropertyTax() {
    const propType = parseFloat(document.getElementById('taxPropType').value) || 1.0;
    const cityFactor = parseFloat(document.getElementById('cityType').value) || 1.5;
    const value = parseFloat(document.getElementById('taxPropVal').value) || 0;
    const ageFactor = parseFloat(document.getElementById('propAge').value) || 0.9;
    const occupancy = parseFloat(document.querySelector('input[name="occupancy"]:checked').value) || 1.0;
    if (value <= 0) {
        document.getElementById('taxableValue').textContent = '₹0';
        document.getElementById('taxRate').textContent = '0%';
        document.getElementById('annualTax').textContent = '₹0';
        document.getElementById('monthlyTax').textContent = '₹0';
        document.getElementById('municipalTax').textContent = '₹0';
        document.getElementById('municipalBreakdown').textContent = '₹0';
        document.getElementById('educationCess').textContent = '₹0';
        document.getElementById('otherCharges').textContent = '₹0';
        document.getElementById('municipalBar').style.width = '0%';
        document.getElementById('municipalBar').textContent = '0%';
        document.getElementById('educationBar').style.width = '0%';
        document.getElementById('educationBar').textContent = '0%';
        document.getElementById('otherBar').style.width = '0%';
        document.getElementById('otherBar').textContent = '0%';
        return;
    }
    const baseRate = 0.5 * propType * cityFactor * occupancy;
    const effectiveRate = baseRate * ageFactor;
    const ratePct = effectiveRate;
    const standardDeduction = 0.20;
    const taxableVal = value * (1 - standardDeduction);
    const annualTaxAmt = taxableVal * ratePct / 100;
    const monthlyTaxAmt = annualTaxAmt / 12;
    const municipalAmt = annualTaxAmt * 0.75;
    const eduCessAmt = annualTaxAmt * 0.12;
    const otherAmt = annualTaxAmt * 0.13;
    document.getElementById('taxableValue').textContent = '₹' + Math.round(taxableVal).toLocaleString('en-IN');
    document.getElementById('taxRate').textContent = ratePct.toFixed(2) + '%';
    document.getElementById('annualTax').textContent = '₹' + Math.round(annualTaxAmt).toLocaleString('en-IN');
    document.getElementById('monthlyTax').textContent = '₹' + Math.round(monthlyTaxAmt).toLocaleString('en-IN');
    document.getElementById('municipalTax').textContent = '₹' + Math.round(municipalAmt).toLocaleString('en-IN');
    document.getElementById('municipalBreakdown').textContent = '₹' + Math.round(municipalAmt).toLocaleString('en-IN');
    document.getElementById('educationCess').textContent = '₹' + Math.round(eduCessAmt).toLocaleString('en-IN');
    document.getElementById('otherCharges').textContent = '₹' + Math.round(otherAmt).toLocaleString('en-IN');
    const mPct = annualTaxAmt > 0 ? 75 : 0;
    const ePct = annualTaxAmt > 0 ? 12 : 0;
    const oPct = annualTaxAmt > 0 ? 13 : 0;
    document.getElementById('municipalBar').style.width = mPct + '%';
    document.getElementById('municipalBar').textContent = mPct + '%';
    document.getElementById('educationBar').style.width = ePct + '%';
    document.getElementById('educationBar').textContent = ePct + '%';
    document.getElementById('otherBar').style.width = oPct + '%';
    document.getElementById('otherBar').textContent = oPct + '%';
}
calcPropertyTax();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
