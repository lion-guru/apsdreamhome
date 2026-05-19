<section class="py-5" style="background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold">Property Valuation Tool</h1>
            <p class="text-white-50">Apni property ki estimated market value turant jaanein</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">City</label>
                                <select class="form-select" id="valCity" onchange="calcValuation()">
                                    <option value="3000">Gorakhpur</option>
                                    <option value="4000">Lucknow</option>
                                    <option value="2500">Varanasi</option>
                                    <option value="2000">Kushinagar</option>
                                    <option value="3500">Ayodhya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Property Type</label>
                                <select class="form-select" id="valType" onchange="calcValuation()">
                                    <option value="1.0">Residential Plot</option>
                                    <option value="1.5">House / Villa</option>
                                    <option value="1.2">Flat / Apartment</option>
                                    <option value="0.8">Agricultural Land</option>
                                    <option value="1.8">Commercial Shop</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Area (sq ft)</label>
                                <input type="number" class="form-control" id="valArea" value="1200" oninput="calcValuation()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Age of Property</label>
                                <select class="form-select" id="valAge" onchange="calcValuation()">
                                    <option value="1.0">New (0-5 years)</option>
                                    <option value="0.85">Moderate (5-15 years)</option>
                                    <option value="0.70">Old (15+ years)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 text-center" id="valResults">
                            <div class="col-4">
                                <div class="bg-light rounded p-3">
                                    <small class="text-muted">Per Sq Ft Rate</small>
                                    <h5 class="text-primary mb-0" id="perSqft">₹3,000</h5>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded p-3">
                                    <small class="text-muted">Estimated Value</small>
                                    <h5 class="text-success mb-0" id="totalValue">₹36,00,000</h5>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded p-3">
                                    <small class="text-muted">Market Range</small>
                                    <h5 class="text-danger mb-0" id="marketRange">₹32.4L - ₹39.6L</h5>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mt-3 text-center"><i class="fas fa-info-circle me-1"></i>This is an estimated value based on area rates. Actual value may vary. For accurate valuation, contact our team.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function calcValuation() {
    const baseRate = parseFloat(document.getElementById('valCity').value) || 3000;
    const typeMul = parseFloat(document.getElementById('valType').value) || 1.0;
    const area = parseFloat(document.getElementById('valArea').value) || 0;
    const ageMul = parseFloat(document.getElementById('valAge').value) || 1.0;
    const rate = baseRate * typeMul;
    const value = rate * area * ageMul;
    const lowValue = value * 0.9;
    const highValue = value * 1.1;
    document.getElementById('perSqft').textContent = '₹' + Math.round(rate).toLocaleString('en-IN');
    document.getElementById('totalValue').textContent = '₹' + Math.round(value).toLocaleString('en-IN');
    document.getElementById('marketRange').textContent = '₹' + Math.round(lowValue / 100000) / 10 + 'L - ₹' + Math.round(highValue / 100000) / 10 + 'L';
}
calcValuation();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
