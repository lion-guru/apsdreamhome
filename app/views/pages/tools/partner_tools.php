<section class="py-5" class="style-39878">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="text-white fw-bold display-5"><i class="fas fa-handshake me-2"></i>Partner Tools</h1>
            <p class="text-white-50 fs-5">Free tools for small land dealers â€” use without registration!</p>
        </div>

        <!-- Tool 1: Land Area Converter -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card" class="style-18771">
                    <div class="card-header" class="style-84117">
                        <h4 class="text-white mb-0"><i class="fas fa-vector-square me-2"></i>Land Area Converter</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="text-white-50">Value</label>
                                <input type="number" id="areaValue" class="form-control" value="1000" oninput="convertArea()" class="style-46367">
                            </div>
                            <div class="col-md-3">
                                <label class="text-white-50">From</label>
                                <select id="areaFrom" class="form-select" onchange="convertArea()" class="style-46367">
                                    <option value="sqft">Square Feet (sqft)</option>
                                    <option value="sqm">Square Meter (sqm)</option>
                                    <option value="acre">Acre</option>
                                    <option value="bigha">Bigha</option>
                                    <option value="gaj">Gaj / Sq. Yard</option>
                                    <option value="katha">Katha</option>
                                    <option value="marla">Marla</option>
                                    <option value="hectare">Hectare</option>
                                </select>
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                                <i class="fas fa-arrow-right fa-2x" class="style-23621"></i>
                            </div>
                            <div class="col-md-3">
                                <div id="conversionResults" class="style-3672">
                                    <small class="text-white-50 d-block mb-2">Results:</small>
                                    <div id="areaResults"></div>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-outline-light w-100" onclick="document.getElementById('areaValue').value=0;convertArea();">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tool 2: Plot Price Calculator -->
        <div class="row mb-5">
            <div class="col-md-6">
                <div class="card h-100" class="style-18771">
                    <div class="card-header" class="style-93477">
                        <h4 class="text-white mb-0"><i class="fas fa-rupee-sign me-2"></i>Plot Price Calculator</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-white-50">Plot Area (sqft)</label>
                            <input type="number" id="priceArea" class="form-control" value="1000" oninput="calcPrice()" class="style-46367">
                        </div>
                        <div class="mb-3">
                            <label class="text-white-50">Rate per sqft (â‚¹)</label>
                            <input type="number" id="priceRate" class="form-control" value="2500" oninput="calcPrice()" class="style-46367">
                        </div>
                        <div class="mb-3">
                            <label class="text-white-50">PLC Charges (â‚¹, optional)</label>
                            <input type="number" id="pricePLC" class="form-control" value="0" oninput="calcPrice()" class="style-46367">
                        </div>
                        <div class="mb-3">
                            <label class="text-white-50">Discount % (optional)</label>
                            <input type="number" id="priceDiscount" class="form-control" value="0" oninput="calcPrice()" class="style-46367">
                        </div>
                        <div class="style-3672">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50">Base Price:</span>
                                <span class="text-white" id="priceBase">â‚¹25,00,000</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50">PLC:</span>
                                <span class="text-white" id="pricePLCDisplay">â‚¹0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50">Discount:</span>
                                <span class="text-danger" id="priceDiscountDisplay">-â‚¹0</span>
                            </div>
                            <hr class="style-96118">
                            <div class="d-flex justify-content-between">
                                <strong class="text-white">Total Price:</strong>
                                <strong class="text-success fs-4" id="priceTotal">â‚¹25,00,000</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tool 3: Commission Calculator -->
            <div class="col-md-6">
                <div class="card h-100" class="style-18771">
                    <div class="card-header" class="style-38548">
                        <h4 class="text-white mb-0"><i class="fas fa-calculator me-2"></i>Commission Calculator</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-white-50">Sale Amount (â‚¹)</label>
                            <input type="number" id="commAmount" class="form-control" value="2500000" oninput="calcCommission()" class="style-46367">
                        </div>
                        <div class="mb-3">
                            <label class="text-white-50">Your Rank</label>
                            <select id="commRank" class="form-select" onchange="calcCommission()" class="style-46367">
                                <option value="5">Associate (5%)</option>
                                <option value="7">Sr. Associate (7%)</option>
                                <option value="10">BDM (10%)</option>
                                <option value="12">Sr. BDM (12%)</option>
                                <option value="15">Vice President (15%)</option>
                                <option value="18">President (18%)</option>
                                <option value="20">Site Manager (20%)</option>
                            </select>
                        </div>
                        <div class="style-3672">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50">Track A (Direct Sale):</span>
                                <span class="text-success" id="commTrackA">â‚¹1,25,000</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50">Track B (Performance):</span>
                                <span class="text-info" id="commTrackB">â‚¹75,000</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50">Track C (Milestone):</span>
                                <span class="text-warning" id="commTrackC">â‚¹50,000</span>
                            </div>
                            <hr class="style-96118">
                            <div class="d-flex justify-content-between">
                                <strong class="text-white">Total Commission (Max 20%):</strong>
                                <strong class="text-success fs-4" id="commTotal">â‚¹2,50,000</strong>
                            </div>
                            <small class="text-white-50 d-block mt-2">*Actual commission depends on downline performance & rank</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tool 4: Stamp Duty Quick Calc -->
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="card h-100" class="style-18771">
                    <div class="card-header" class="style-95871">
                        <h5 class="text-white mb-0"><i class="fas fa-file-contract me-2"></i>Stamp Duty Quick Calc</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-white-50">Property Value (â‚¹)</label>
                            <input type="number" id="stampValue" class="form-control" value="2500000" oninput="calcStamp()" class="style-46367">
                        </div>
                        <div class="mb-3">
                            <label class="text-white-50">State</label>
                            <select id="stampState" class="form-select" onchange="calcStamp()" class="style-46367">
                                <option value="5">Uttar Pradesh (5%)</option>
                                <option value="6">Delhi (6%)</option>
                                <option value="7">Rajasthan (7%)</option>
                                <option value="3">Haryana (3% rural)</option>
                            </select>
                        </div>
                        <div class="style-3672">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50">Stamp Duty:</span>
                                <span class="text-white" id="stampDuty">â‚¹1,25,000</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50">Registration (1%):</span>
                                <span class="text-white" id="stampReg">â‚¹25,000</span>
                            </div>
                            <hr class="style-96118">
                            <div class="d-flex justify-content-between">
                                <strong class="text-white">Total:</strong>
                                <strong class="text-danger fs-5" id="stampTotal">â‚¹1,50,000</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tool 5: EMI Quick Calc -->
            <div class="col-md-4">
                <div class="card h-100" class="style-18771">
                    <div class="card-header" class="style-84117">
                        <h5 class="text-white mb-0"><i class="fas fa-calendar-alt me-2"></i>EMI Quick Calculator</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-white-50">Loan Amount (â‚¹)</label>
                            <input type="number" id="emiLoan" class="form-control" value="1500000" oninput="calcEMI()" class="style-46367">
                        </div>
                        <div class="mb-3">
                            <label class="text-white-50">Interest Rate (% p.a.)</label>
                            <input type="number" id="emiRate" class="form-control" value="8.5" step="0.1" oninput="calcEMI()" class="style-46367">
                        </div>
                        <div class="mb-3">
                            <label class="text-white-50">Tenure (years)</label>
                            <input type="range" id="emiTenure" class="form-range" min="1" max="30" value="15" oninput="calcEMI()" class="style-87889">
                            <span class="text-white-50" id="emiTenureLabel">15 years</span>
                        </div>
                        <div class="style-3672">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50">Monthly EMI:</span>
                                <strong class="text-success fs-5" id="emiMonthly">â‚¹14,995</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50">Total Interest:</span>
                                <span class="text-danger" id="emiInterest">â‚¹11,99,100</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-white-50">Total Payment:</span>
                                <span class="text-white" id="emiTotal">â‚¹26,99,100</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tool 6: Document Checklist -->
            <div class="col-md-4">
                <div class="card h-100" class="style-18771">
                    <div class="card-header" class="style-31466">
                        <h5 class="text-white mb-0"><i class="fas fa-tasks me-2"></i>Land Deal Checklist</h5>
                    </div>
                    <div class="card-body" class="style-43942">
                        <div id="checklist">
                            <?php
                            $items = [
                                'Land Title Deed (Sale Deed / Registry)',
                                'Seller Aadhaar & PAN Card',
                                'Property Tax Receipts (last 3 years)',
                                'Encumbrance Certificate (EC)',
                                'Land Map / Naksha (Revenue Dept)',
                                'Mutation Register (Fard / Khatauni)',
                                'NOC from Society / Colony',
                                'Bank NOC (if loan property)',
                                'Power of Attorney (if applicable)',
                                'Stamp Paper (appropriate value)',
                                'Passport-size Photos (2 each)',
                                'Witness Aadhaar (2 witnesses)',
                                'Receipt of Earnest Money (token)',
                                'Approved Building Plan (if constructed)',
                                'Occupancy Certificate (if built)',
                            ];
                            foreach ($items as $i => $item): ?>
                                <div class="form-check mb-2" class="style-25010">
                                    <input class="form-check-input" type="checkbox" id="item<?= $i ?>" class="style-37958">
                                    <label class="form-check-label text-white-50 ms-2" for="item<?= $i ?>" class="style-47175"><?= $item ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-white-50" id="checklistProgress">0 of <?= count($items) ?> completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="text-center mt-5">
            <div class="card" class="style-14815">
                <div class="card-body py-5">
                    <h3 class="text-white fw-bold">Become an APS Dream Home Partner</h3>
                    <p class="text-white-50 mb-4">Join 50+ associates earning commission on land deals. Free training, CRM tools, and regular income.</p>
                    <a href="<?= BASE_URL ?>/become-associate" class="btn btn-success btn-lg me-3">
                        <i class="fas fa-user-plus me-2"></i>Become Associate
                    </a>
                    <a href="<?= BASE_URL ?>/properties" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-search me-2"></i>Browse Properties
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Area conversion factors (to sqft)
const areaFactors = {
    sqft: 1, sqm: 10.7639, acre: 43560, bigha: 9680, gaj: 9, katha: 1361, marla: 272.25, hectare: 107639
};
const areaNames = {
    sqft: 'Sq Ft', sqm: 'Sq M', acre: 'Acre', bigha: 'Bigha', gaj: 'Gaj', katha: 'Katha', marla: 'Marla', hectare: 'Hectare'
};

function convertArea() {
    const val = parseFloat(document.getElementById('areaValue').value) || 0;
    const from = document.getElementById('areaFrom').value;
    const sqft = val * areaFactors[from];
    let html = '';
    for (const [key, factor] of Object.entries(areaFactors)) {
        const converted = sqft / factor;
        const highlight = key === from ? 'color:#0d9488;font-weight:bold;' : '';
        html += '<div class="d-flex justify-content-between mb-1" class="style-64711">';
        html += '<span class="text-white-50">' + areaNames[key] + ':</span>';
        html += '<span class="text-white">' + converted.toLocaleString('en-IN', {maximumFractionDigits: 2}) + '</span>';
        html += '</div>';
    }
    document.getElementById('areaResults').innerHTML = html;
}

function calcPrice() {
    const area = parseFloat(document.getElementById('priceArea').value) || 0;
    const rate = parseFloat(document.getElementById('priceRate').value) || 0;
    const plc = parseFloat(document.getElementById('pricePLC').value) || 0;
    const disc = parseFloat(document.getElementById('priceDiscount').value) || 0;
    const base = area * rate;
    const discount = base * disc / 100;
    const total = base + plc - discount;
    document.getElementById('priceBase').textContent = 'â‚¹' + base.toLocaleString('en-IN');
    document.getElementById('pricePLCDisplay').textContent = 'â‚¹' + plc.toLocaleString('en-IN');
    document.getElementById('priceDiscountDisplay').textContent = '-â‚¹' + discount.toLocaleString('en-IN');
    document.getElementById('priceTotal').textContent = 'â‚¹' + total.toLocaleString('en-IN');
}

function calcCommission() {
    const amount = parseFloat(document.getElementById('commAmount').value) || 0;
    const rate = parseFloat(document.getElementById('commRank').value) || 5;
    const globalCap = 20;
    const trackA = amount * Math.min(rate, 15) / 100;
    const trackB = amount * 3 / 100;
    const trackC = amount * 2 / 100;
    const total = Math.min(trackA + trackB + trackC, amount * globalCap / 100);
    document.getElementById('commTrackA').textContent = 'â‚¹' + trackA.toLocaleString('en-IN');
    document.getElementById('commTrackB').textContent = 'â‚¹' + trackB.toLocaleString('en-IN');
    document.getElementById('commTrackC').textContent = 'â‚¹' + trackC.toLocaleString('en-IN');
    document.getElementById('commTotal').textContent = 'â‚¹' + total.toLocaleString('en-IN');
}

function calcStamp() {
    const val = parseFloat(document.getElementById('stampValue').value) || 0;
    const rate = parseFloat(document.getElementById('stampState').value) || 5;
    const duty = val * rate / 100;
    const reg = val * 1 / 100;
    document.getElementById('stampDuty').textContent = 'â‚¹' + duty.toLocaleString('en-IN');
    document.getElementById('stampReg').textContent = 'â‚¹' + reg.toLocaleString('en-IN');
    document.getElementById('stampTotal').textContent = 'â‚¹' + (duty + reg).toLocaleString('en-IN');
}

function calcEMI() {
    const P = parseFloat(document.getElementById('emiLoan').value) || 0;
    const R = (parseFloat(document.getElementById('emiRate').value) || 8.5) / 12 / 100;
    const N = parseInt(document.getElementById('emiTenure').value) || 15;
    document.getElementById('emiTenureLabel').textContent = N + ' years';
    if (P <= 0 || R <= 0) { return; }
    const emi = P * R * Math.pow(1 + R, N * 12) / (Math.pow(1 + R, N * 12) - 1);
    const total = emi * N * 12;
    const interest = total - P;
    document.getElementById('emiMonthly').textContent = 'â‚¹' + Math.round(emi).toLocaleString('en-IN');
    document.getElementById('emiInterest').textContent = 'â‚¹' + Math.round(interest).toLocaleString('en-IN');
    document.getElementById('emiTotal').textContent = 'â‚¹' + Math.round(total).toLocaleString('en-IN');
}

// Checklist progress
document.querySelectorAll('#checklist input[type=checkbox]').forEach(cb => {
    cb.addEventListener('change', () => {
        const total = document.querySelectorAll('#checklist input[type=checkbox]').length;
        const checked = document.querySelectorAll('#checklist input[type=checkbox]:checked').length;
        document.getElementById('checklistProgress').textContent = checked + ' of ' + total + ' completed';
    });
});

// Initialize all calculators
convertArea(); calcPrice(); calcCommission(); calcStamp(); calcEMI();
</script>
