<section class="py-5" style="background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);min-height:100vh;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="text-white fw-bold display-5"><i class="fas fa-chart-line me-2"></i>SIP vs Real Estate</h1>
            <p class="text-white-50 fs-5">20 saal mein SIP better hai ya property? Dono ka side-by-side comparison</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg" style="border-radius:20px;background:rgba(255,255,255,0.05);backdrop-filter:blur(12px);">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Monthly Investment (₹)</label>
                            <input type="range" id="monthlySip" class="form-range" min="5000" max="100000" step="1000" value="25000" oninput="syncSip()">
                            <div class="d-flex justify-content-between"><span class="text-white-50">₹5K</span><span class="text-white fw-bold" id="monthlySipLabel">₹25,000</span><span class="text-white-50">₹1L</span></div>
                        </div>
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">SIP Return Rate (%)</label>
                            <input type="range" id="sipRateS" class="form-range" min="8" max="20" step="0.5" value="12" oninput="syncSip()">
                            <div class="d-flex justify-content-between"><span class="text-white-50">8%</span><span class="text-white fw-bold" id="sipRateSLabel">12%</span><span class="text-white-50">20%</span></div>
                        </div>
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Property Appreciation (%)</label>
                            <input type="range" id="propAppS" class="form-range" min="3" max="15" step="0.5" value="8" oninput="syncSip()">
                            <div class="d-flex justify-content-between"><span class="text-white-50">3%</span><span class="text-white fw-bold" id="propAppSLabel">8%</span><span class="text-white-50">15%</span></div>
                        </div>
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Property Loan Rate (%)</label>
                            <input type="range" id="loanRateS" class="form-range" min="5" max="15" step="0.1" value="8.5" oninput="syncSip()">
                            <div class="d-flex justify-content-between"><span class="text-white-50">5%</span><span class="text-white fw-bold" id="loanRateSLabel">8.5%</span><span class="text-white-50">15%</span></div>
                        </div>
                        <div class="row g-4 mt-4">
                            <div class="col-md-6">
                                <div class="p-4 rounded-4 text-center" style="background:rgba(50,200,255,0.1);border:1px solid rgba(50,200,255,0.3);">
                                    <h5 class="text-info"><i class="fas fa-chart-simple me-2"></i>SIP Mutual Fund</h5>
                                    <h2 class="text-white fw-bold" id="sipFinalS">₹0</h2>
                                    <small class="text-white-50">After 20 Years</small>
                                    <p class="text-info mt-2 mb-0" id="sipInvestedS">Total Invested: ₹0</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-4 rounded-4 text-center" style="background:rgba(50,255,50,0.1);border:1px solid rgba(50,255,50,0.3);">
                                    <h5 class="text-success"><i class="fas fa-building me-2"></i>Real Estate</h5>
                                    <h2 class="text-white fw-bold" id="reFinalS">₹0</h2>
                                    <small class="text-white-50">After 20 Years</small>
                                    <p class="text-success mt-2 mb-0" id="reInvestedS">Total Invested: ₹0</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 p-4 rounded-4 text-center" style="background:rgba(255,215,0,0.1);border:1px solid rgba(255,215,0,0.3);">
                            <h4 class="text-warning fw-bold" id="verdictS">Enter values to compare</h4>
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
function syncSip(){
    let ms=parseFloat(document.getElementById('monthlySip').value)||25000;
    let sr=parseFloat(document.getElementById('sipRateS').value)||12;
    let pa=parseFloat(document.getElementById('propAppS').value)||8;
    let lr=parseFloat(document.getElementById('loanRateS').value)||8.5;
    document.getElementById('monthlySipLabel').textContent='₹'+num(ms);
    document.getElementById('sipRateSLabel').textContent=sr+'%';
    document.getElementById('propAppSLabel').textContent=pa+'%';
    document.getElementById('loanRateSLabel').textContent=lr+'%';
    let yrs=20, n=yrs*12, r=sr/12/100;
    let sipFinal=ms*((Math.pow(1+r,n)-1)/r);
    let totalInvested=ms*n;
    let propertyPrice=ms*12*20;
    let down=propertyPrice*0.2, loan=propertyPrice-down;
    let mr=lr/12/100;
    let emi=loan*mr*Math.pow(1+mr,n)/(Math.pow(1+mr,n)-1)||0;
    let totalEmi=emi*n;
    let reValue=propertyPrice*Math.pow(1+pa/100,yrs);
    let reNet=reValue-totalEmi-down;
    document.getElementById('sipFinalS').textContent='₹'+num(Math.round(sipFinal));
    document.getElementById('sipInvestedS').textContent='Total Invested: ₹'+num(Math.round(totalInvested));
    document.getElementById('reFinalS').textContent='₹'+num(Math.round(reValue));
    document.getElementById('reInvestedS').textContent='Total Invested (EMI): ₹'+num(Math.round(totalEmi));
    let v=document.getElementById('verdictS');
    if(sipFinal>reNet) v.innerHTML='<i class="fas fa-chart-simple me-2"></i>SIP is better! Final corpus: ₹'+num(Math.round(sipFinal))+' vs Real Estate Net: ₹'+num(Math.round(reNet));
    else v.innerHTML='<i class="fas fa-building me-2"></i>Real Estate is better! Net gain: ₹'+num(Math.round(reNet))+' vs SIP: ₹'+num(Math.round(sipFinal));
}
syncSip();
</script>
