<section class="py-5" style="background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold">Construction Cost Estimator</h1>
            <p class="text-white-50">Apne ghar ke nirman ka estimated cost turant calculate karein</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Plot Area (sq ft) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-lg" id="plotArea" value="1200" min="1" oninput="calcConstruction()">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Number of Floors</label>
                                <select class="form-select" id="numFloors" onchange="calcConstruction()">
                                    <option value="1">1 Floor</option>
                                    <option value="2" selected>2 Floors</option>
                                    <option value="3">3 Floors</option>
                                    <option value="4">4 Floors</option>
                                    <option value="5">5 Floors</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Construction Quality</label>
                                <select class="form-select" id="buildQuality" onchange="calcConstruction()">
                                    <option value="1800">Basic - ₹1,800/sqft</option>
                                    <option value="2500" selected>Standard - ₹2,500/sqft</option>
                                    <option value="3500">Premium - ₹3,500/sqft</option>
                                    <option value="5000">Luxury - ₹5,000/sqft</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="includeFinishing" onchange="calcConstruction()">
                                    <label class="form-check-label fw-bold" for="includeFinishing">
                                        Include Finishing <small class="text-muted">(+30%)</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary w-100 btn-lg mb-4" onclick="calcConstruction()"><i class="fas fa-calculator me-2"></i>Calculate Cost</button>
                        <div id="constructionResults">
                            <div class="row g-3 text-center mb-3">
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Total Built-up Area</small>
                                        <h5 class="text-info mb-0" id="builtupArea">3,600 sqft</h5>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Per Sqft Cost</small>
                                        <h5 class="text-primary mb-0" id="perSqftCost">₹2,500</h5>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Total Construction Cost</small>
                                        <h5 class="text-success mb-0" id="totalConstCost">₹90,00,000</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 text-center mb-3" id="finishingRow">
                                <div class="col-md-6">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Finishing Cost</small>
                                        <h5 class="text-warning mb-0" id="finishingCost">₹27,00,000</h5>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light rounded p-3">
                                        <small class="text-muted">Grand Total</small>
                                        <h5 class="text-danger mb-0" id="grandTotal">₹1,17,00,000</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4" id="costChart">
                                <h6 class="fw-bold text-center mb-3">Cost Breakdown</h6>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Construction</small>
                                        <small id="chartConstLabel">₹90,00,000</small>
                                    </div>
                                    <div class="progress" style="height:28px;">
                                        <div class="progress-bar bg-success" id="chartConstBar" style="width:77%">77%</div>
                                    </div>
                                </div>
                                <div class="mb-2" id="chartFinishingWrap">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Finishing</small>
                                        <small id="chartFinLabel">₹27,00,000</small>
                                    </div>
                                    <div class="progress" style="height:28px;">
                                        <div class="progress-bar bg-warning" id="chartFinBar" style="width:23%">23%</div>
                                    </div>
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
function calcConstruction() {
    const area = parseFloat(document.getElementById('plotArea').value) || 0;
    const floors = parseInt(document.getElementById('numFloors').value) || 1;
    const rate = parseFloat(document.getElementById('buildQuality').value) || 2500;
    const finishing = document.getElementById('includeFinishing').checked;
    const builtup = area * floors;
    const baseCost = builtup * rate;
    const finCost = finishing ? baseCost * 0.30 : 0;
    const grand = baseCost + finCost;
    document.getElementById('builtupArea').textContent = builtup.toLocaleString('en-IN') + ' sqft';
    document.getElementById('perSqftCost').textContent = '₹' + rate.toLocaleString('en-IN');
    document.getElementById('totalConstCost').textContent = '₹' + Math.round(baseCost).toLocaleString('en-IN');
    const finishingRow = document.getElementById('finishingRow');
    const chartFinWrap = document.getElementById('chartFinishingWrap');
    if (finishing) {
        finishingRow.style.display = 'flex';
        chartFinWrap.style.display = 'block';
        document.getElementById('finishingCost').textContent = '₹' + Math.round(finCost).toLocaleString('en-IN');
        document.getElementById('grandTotal').textContent = '₹' + Math.round(grand).toLocaleString('en-IN');
        const constPct = grand > 0 ? Math.round(baseCost / grand * 100) : 0;
        const finPct = grand > 0 ? Math.round(finCost / grand * 100) : 0;
        document.getElementById('chartConstLabel').textContent = '₹' + Math.round(baseCost).toLocaleString('en-IN');
        document.getElementById('chartConstBar').style.width = constPct + '%';
        document.getElementById('chartConstBar').textContent = constPct + '%';
        document.getElementById('chartFinLabel').textContent = '₹' + Math.round(finCost).toLocaleString('en-IN');
        document.getElementById('chartFinBar').style.width = finPct + '%';
        document.getElementById('chartFinBar').textContent = finPct + '%';
    } else {
        finishingRow.style.display = 'none';
        chartFinWrap.style.display = 'none';
        document.getElementById('grandTotal').textContent = '₹' + Math.round(baseCost).toLocaleString('en-IN');
        document.getElementById('chartConstLabel').textContent = '₹' + Math.round(baseCost).toLocaleString('en-IN');
        document.getElementById('chartConstBar').style.width = '100%';
        document.getElementById('chartConstBar').textContent = '100%';
    }
}
calcConstruction();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
