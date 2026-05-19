<section class="py-5" style="background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);min-height:100vh;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="text-white fw-bold display-5"><i class="fas fa-scale-balanced me-2"></i>Rent vs Buy Calculator</h1>
            <p class="text-white-50 fs-5">20 saal mein kya better hai? Rent dena ya EMI dena?</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg" style="border-radius:20px;background:rgba(255,255,255,0.05);backdrop-filter:blur(12px);">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Property Price (₹)</label>
                            <input type="range" id="propPriceR" class="form-range" min="1000000" max="50000000" step="100000" value="5000000" oninput="syncRentBuy()">
                            <div class="d-flex justify-content-between"><span class="text-white-50">₹1L</span><span class="text-white fw-bold" id="propPriceRLabel">₹50,00,000</span><span class="text-white-50">₹5Cr</span></div>
                        </div>
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Monthly Rent (₹)</label>
                            <input type="range" id="monthlyRentR" class="form-range" min="5000" max="100000" step="1000" value="15000" oninput="syncRentBuy()">
                            <div class="d-flex justify-content-between"><span class="text-white-50">₹5K</span><span class="text-white fw-bold" id="monthlyRentRLabel">₹15,000</span><span class="text-white-50">₹1L</span></div>
                        </div>
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Home Loan Rate (%)</label>
                            <input type="range" id="loanRateR" class="form-range" min="5" max="15" step="0.1" value="8.5" oninput="syncRentBuy()">
                            <div class="d-flex justify-content-between"><span class="text-white-50">5%</span><span class="text-white fw-bold" id="loanRateRLabel">8.5%</span><span class="text-white-50">15%</span></div>
                        </div>
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Property Appreciation (%)</label>
                            <input type="range" id="apprecR" class="form-range" min="2" max="15" step="0.5" value="8" oninput="syncRentBuy()">
                            <div class="d-flex justify-content-between"><span class="text-white-50">2%</span><span class="text-white fw-bold" id="apprecRLabel">8%</span><span class="text-white-50">15%</span></div>
                        </div>
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">Rent Increase (% yearly)</label>
                            <input type="range" id="rentIncR" class="form-range" min="2" max="15" step="0.5" value="5" oninput="syncRentBuy()">
                            <div class="d-flex justify-content-between"><span class="text-white-50">2%</span><span class="text-white fw-bold" id="rentIncRLabel">5%</span><span class="text-white-50">15%</span></div>
                        </div>
                        <div class="mb-4">
                            <label class="text-white fw-semibold mb-2">SIP Return (%)</label>
                            <input type="range" id="sipReturnR" class="form-range" min="5" max="20" step="0.5" value="12" oninput="syncRentBuy()">
                            <div class="d-flex justify-content-between"><span class="text-white-50">5%</span><span class="text-white fw-bold" id="sipReturnRLabel">12%</span><span class="text-white-50">20%</span></div>
                        </div>
                        <div class="row g-4 mt-4">
                            <div class="col-md-6">
                                <div class="p-4 rounded-4 text-center" style="background:rgba(255,50,50,0.15);border:1px solid rgba(255,50,50,0.3);">
                                    <h5 class="text-danger"><i class="fas fa-key me-2"></i>Renting (20 Yrs)</h5>
                                    <h2 class="text-white fw-bold" id="totalRentPaid">₹0</h2>
                                    <small class="text-white-50">Total Rent Paid</small>
                                    <h4 class="text-info mt-2" id="sipWealth">₹0</h4>
                                    <small class="text-white-50">SIP Wealth (Difference invested)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-4 rounded-4 text-center" style="background:rgba(50,255,50,0.1);border:1px solid rgba(50,255,50,0.3);">
                                    <h5 class="text-success"><i class="fas fa-house-chimney me-2"></i>Buying (20 Yrs)</h5>
                                    <h2 class="text-white fw-bold" id="finalPropertyValue">₹0</h2>
                                    <small class="text-white-50">Property Value After 20 Yrs</small>
                                    <h4 class="text-warning mt-2" id="totalEmiPaid">₹0</h4>
                                    <small class="text-white-50">Total EMI Paid</small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 p-4 rounded-4 text-center" style="background:rgba(255,215,0,0.1);border:1px solid rgba(255,215,0,0.3);">
                            <h4 class="text-warning fw-bold" id="verdictR">Enter values to compare</h4>
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
function syncRentBuy(){
    let pp=parseFloat(document.getElementById('propPriceR').value)||5000000;
    let mr=parseFloat(document.getElementById('monthlyRentR').value)||15000;
    let lr=parseFloat(document.getElementById('loanRateR').value)||8.5;
    let ap=parseFloat(document.getElementById('apprecR').value)||8;
    let ri=parseFloat(document.getElementById('rentIncR').value)||5;
    let sr=parseFloat(document.getElementById('sipReturnR').value)||12;
    document.getElementById('propPriceRLabel').textContent='₹'+num(pp);
    document.getElementById('monthlyRentRLabel').textContent='₹'+num(mr);
    document.getElementById('loanRateRLabel').textContent=lr+'%';
    document.getElementById('apprecRLabel').textContent=ap+'%';
    document.getElementById('rentIncRLabel').textContent=ri+'%';
    document.getElementById('sipReturnRLabel').textContent=sr+'%';
    let down=pp*0.2, loan=pp-down, yrs=20, n=yrs*12, mrRate=lr/12/100;
    let emi=loan*mrRate*Math.pow(1+mrRate,n)/(Math.pow(1+mrRate,n)-1)||0;
    let totalEmi=emi*n;
    let finalValue=pp*Math.pow(1+ap/100,yrs);
    let totalRent=0, diffSum=0;
    for(let y=1;y<=yrs;y++){
        let annualRent=mr*12*Math.pow(1+ri/100,y-1);
        totalRent+=annualRent;
        let diff=totalEmi/yrs-annualRent;
        if(diff>0) diffSum+=diff;
    }
    let sipWealth=0;
    let monthlyDiff=emi-mr;
    if(monthlyDiff>0){
        let r=sr/12/100;
        sipWealth=monthlyDiff*((Math.pow(1+r,n)-1)/r);
    }
    document.getElementById('totalRentPaid').textContent='₹'+num(Math.round(totalRent));
    document.getElementById('sipWealth').textContent='₹'+num(Math.round(sipWealth));
    document.getElementById('finalPropertyValue').textContent='₹'+num(Math.round(finalValue));
    document.getElementById('totalEmiPaid').textContent='₹'+num(Math.round(totalEmi));
    let netBuy=finalValue-totalEmi-down;
    let netRent=sipWealth-totalRent;
    let v=document.getElementById('verdictR');
    if(netBuy>netRent) v.innerHTML='<i class="fas fa-house-chimney me-2"></i>Buying is better! Net gain: ₹'+num(Math.round(netBuy))+' vs Rent: ₹'+num(Math.round(netRent));
    else v.innerHTML='<i class="fas fa-key me-2"></i>Renting + SIP is better! Net gain: ₹'+num(Math.round(netRent))+' vs Buy: ₹'+num(Math.round(netBuy));
}
syncRentBuy();
</script>
