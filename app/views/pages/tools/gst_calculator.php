<section class="py-5" style="background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);min-height:100vh;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="text-white fw-bold display-5"><i class="fas fa-receipt me-2"></i>GST Calculator</h1>
            <p class="text-white-50 fs-5">Property par GST kitna lagega? Under construction vs ready-to-move</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg" style="border-radius:20px;background:rgba(255,255,255,0.05);backdrop-filter:blur(12px);">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Base Property Price (₹)</label>
                            <input type="range" id="basePriceG" class="form-range" min="500000" max="20000000" step="50000" value="5000000" oninput="syncGst()">
                            <div class="d-flex justify-content-between"><span class="text-white-50">₹5L</span><span class="text-white fw-bold" id="basePriceGLabel">₹50,00,000</span><span class="text-white-50">₹2Cr</span></div>
                        </div>
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Property Type</label>
                            <select id="propTypeG" class="form-select bg-dark text-white border-secondary" onchange="syncGst()">
                                <option value="under">Under Construction (12% GST on 1/3rd)</option>
                                <option value="ready">Ready-to-Move (No GST)</option>
                                <option value="affordable">Affordable Housing (1% GST)</option>
                            </select>
                        </div>
                        <div class="row g-4 mt-4">
                            <div class="col-md-4">
                                <div class="p-4 rounded-4 text-center" style="background:rgba(50,200,255,0.1);border:1px solid rgba(50,200,255,0.3);">
                                    <h5 class="text-info">Base Price</h5>
                                    <h3 class="text-white fw-bold" id="basePriceDisplayG">₹0</h3>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-4 rounded-4 text-center" style="background:rgba(255,215,0,0.1);border:1px solid rgba(255,215,0,0.3);">
                                    <h5 class="text-warning">GST Amount</h5>
                                    <h3 class="text-white fw-bold" id="gstAmountG">₹0</h3>
                                    <small class="text-white-50" id="gstRateG">@ 12%</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-4 rounded-4 text-center" style="background:rgba(50,255,50,0.1);border:1px solid rgba(50,255,50,0.3);">
                                    <h5 class="text-success">Total Price</h5>
                                    <h3 class="text-white fw-bold" id="totalPriceG">₹0</h3>
                                    <small class="text-white-50">Including GST</small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 p-4 rounded-4" style="background:rgba(108,117,125,0.1);border:1px solid rgba(108,117,125,0.3);">
                            <p class="text-white-50 mb-0 small">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Note:</strong> Under-construction property par GST sirf land value ke alawa construction portion par lagta hai (approx 1/3rd price). Ready-to-move properties GST-exempt hain. Affordable housing par 1% GST without ITC.
                            </p>
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
function syncGst(){
    let bp=parseFloat(document.getElementById('basePriceG').value)||5000000;
    let type=document.getElementById('propTypeG').value;
    document.getElementById('basePriceGLabel').textContent='₹'+num(bp);
    document.getElementById('basePriceDisplayG').textContent='₹'+num(bp);
    let gstPct=0, taxablePortion=1;
    if(type==='under'){gstPct=12; taxablePortion=1/3;}
    else if(type==='ready'){gstPct=0;}
    else if(type==='affordable'){gstPct=1; taxablePortion=1;}
    let taxableAmt=bp*taxablePortion;
    let gst=taxableAmt*gstPct/100;
    let total=bp+gst;
    document.getElementById('gstRateG').textContent='@ '+gstPct+'%'+(type==='under'?' on 1/3rd':'');
    document.getElementById('gstAmountG').textContent='₹'+num(Math.round(gst));
    document.getElementById('totalPriceG').textContent='₹'+num(Math.round(total));
}
syncGst();
</script>
