<section class="py-5" style="background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);min-height:100vh;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="text-white fw-bold display-5"><i class="fas fa-coins me-2"></i>Capital Gains Calculator</h1>
            <p class="text-white-50 fs-5">Property bechne par LTCG / STCG tax calculate karein CII indexation ke saath</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg" style="border-radius:20px;background:rgba(255,255,255,0.05);backdrop-filter:blur(12px);">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Purchase Price (₹)</label>
                            <input type="range" id="purchasePriceC" class="form-range" min="500000" max="10000000" step="50000" value="2000000" oninput="syncCapital()">
                            <div class="d-flex justify-content-between"><span class="text-white-50">₹5L</span><span class="text-white fw-bold" id="purchasePriceCLabel">₹20,00,000</span><span class="text-white-50">₹1Cr</span></div>
                        </div>
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Selling Price (₹)</label>
                            <input type="range" id="sellPriceC" class="form-range" min="1000000" max="20000000" step="50000" value="5000000" oninput="syncCapital()">
                            <div class="d-flex justify-content-between"><span class="text-white-50">₹10L</span><span class="text-white fw-bold" id="sellPriceCLabel">₹50,00,000</span><span class="text-white-50">₹2Cr</span></div>
                        </div>
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Purchase Year (CII Index)</label>
                            <select id="purchaseYearC" class="form-select bg-dark text-white border-secondary" onchange="syncCapital()">
                                <optgroup label="Old Rates">
                                    <option value="100">2001-02 (CII: 100)</option>
                                    <option value="105">2002-03 (105)</option>
                                    <option value="109">2003-04 (109)</option>
                                    <option value="113">2004-05 (113)</option>
                                    <option value="117">2005-06 (117)</option>
                                    <option value="122">2006-07 (122)</option>
                                    <option value="129">2007-08 (129)</option>
                                    <option value="137">2008-09 (137)</option>
                                    <option value="148">2009-10 (148)</option>
                                    <option value="167">2010-11 (167)</option>
                                    <option value="184">2011-12 (184)</option>
                                    <option value="200">2012-13 (200)</option>
                                    <option value="220">2013-14 (220)</option>
                                    <option value="240">2014-15 (240)</option>
                                    <option value="254">2015-16 (254)</option>
                                    <option value="264">2016-17 (264)</option>
                                    <option value="272">2017-18 (272)</option>
                                    <option value="280">2018-19 (280)</option>
                                    <option value="289">2019-20 (289)</option>
                                    <option value="301">2020-21 (301)</option>
                                    <option value="317">2021-22 (317)</option>
                                    <option value="331">2022-23 (331)</option>
                                    <option value="348" selected>2023-24 (348)</option>
                                    <option value="363">2024-25 (363)</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Holding Period</label>
                            <select id="holdingPeriodC" class="form-select bg-dark text-white border-secondary" onchange="syncCapital()">
                                <option value="ltcg">Long Term (&gt;24 months)</option>
                                <option value="stcg">Short Term (&lt;=24 months)</option>
                            </select>
                        </div>
                        <div class="row g-4 mt-4">
                            <div class="col-md-6">
                                <div class="p-4 rounded-4 text-center" style="background:rgba(255,215,0,0.1);border:1px solid rgba(255,215,0,0.3);">
                                    <h5 class="text-warning"><i class="fas fa-file-invoice me-2"></i>Indexed Cost</h5>
                                    <h3 class="text-white fw-bold" id="indexedCostC">₹0</h3>
                                    <small class="text-white-50">After CII adjustment</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-4 rounded-4 text-center" style="background:rgba(255,50,50,0.1);border:1px solid rgba(255,50,50,0.3);">
                                    <h5 class="text-danger"><i class="fas fa-coins me-2"></i>Capital Gain</h5>
                                    <h3 class="text-white fw-bold" id="gainAmountC">₹0</h3>
                                    <small class="text-white-50">Taxable Gain</small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 p-4 rounded-4 text-center" style="background:rgba(50,255,50,0.1);border:1px solid rgba(50,255,50,0.3);">
                            <h4 class="text-success">Tax Payable: <span id="taxPayableC">₹0</span></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
<script>
function num(n){return n.toLocaleString('en-IN')}
function syncCapital(){
    let pp=parseFloat(document.getElementById('purchasePriceC').value)||2000000;
    let sp=parseFloat(document.getElementById('sellPriceC').value)||5000000;
    let ci=parseFloat(document.getElementById('purchaseYearC').value)||348;
    let hp=document.getElementById('holdingPeriodC').value;
    document.getElementById('purchasePriceCLabel').textContent='₹'+num(pp);
    document.getElementById('sellPriceCLabel').textContent='₹'+num(sp);
    let currentCII=363;
    let indexedCost=pp*(currentCII/ci);
    let gain=Math.max(0,sp-indexedCost);
    let taxRate=hp==='ltcg'?0.20:0.30;
    if(hp==='ltcg'&&gain>100000) taxRate=0.20;
    else if(hp==='stcg') taxRate=0.30;
    else taxRate=0;
    let tax=gain*taxRate;
    if(gain>0){
        if(hp==='ltcg'&&gain<=100000) tax=0;
    }
    document.getElementById('indexedCostC').textContent='₹'+num(Math.round(indexedCost));
    document.getElementById('gainAmountC').textContent='₹'+num(Math.round(gain));
    document.getElementById('taxPayableC').textContent='₹'+num(Math.round(tax));
}
syncCapital();
</script>
