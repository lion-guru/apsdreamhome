<section class="py-5 text-white" style="background: linear-gradient(135deg, #0a192f 0%, #1e3a5f 100%)">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><i class="fas fa-arrow-trend-up me-2"></i>Property Appreciation Calculator</h1>
            <p class="text-white-50">Future me aapki property kitni value banegi - dekho appreciation se</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Current Property Value (₹)</label>
                                <input type="number" class="form-control" id="paCurrent" value="5000000" oninput="calcPA()">
                                <small class="text-muted">Aaj ki market value</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Annual Appreciation Rate (%)</label>
                                <input type="range" class="form-range" id="paRate" min="1" max="15" value="7" step="0.5" oninput="document.getElementById('paRateVal').textContent=this.value+'%'; calcPA()">
                                <div class="d-flex justify-content-between"><small class="text-muted">1%</small><span class="badge bg-primary" id="paRateVal">7%</span><small class="text-muted">15%</small></div>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Time Period (Years)</label>
                                <input type="range" class="form-range" id="paYears" min="1" max="30" value="10" step="1" oninput="document.getElementById('paYearsVal').textContent=this.value+' yrs'; calcPA()">
                                <div class="d-flex justify-content-between"><small class="text-muted">1 yr</small><span class="badge bg-success" id="paYearsVal">10 yrs</span><small class="text-muted">30 yrs</small></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Expected Rent Yield (%) <small class="text-muted">(optional)</small></label>
                                <input type="number" class="form-control" id="paRent" value="3" step="0.1" oninput="calcPA()">
                                <small class="text-muted">Salana kiraya se return</small>
                            </div>
                        </div>
                        <div class="row g-3 text-center">
                            <div class="col-md-4">
                                <div class="bg-primary text-white rounded-3 p-3">
                                    <small class="d-block opacity-75">Future Value</small>
                                    <h4 class="mb-0" id="paFuture">₹98,35,756</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block">Total Gain</small>
                                    <h4 class="text-success mb-0" id="paGain">₹48,35,756</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block">Rent Income (total)</small>
                                    <h4 class="text-info mb-0" id="paRentTotal">₹15,00,000</h4>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <canvas id="paChart" height="120"></canvas>
                        </div>
                        <div class="alert alert-info small mt-3 mb-0"><i class="fas fa-info-circle me-1"></i>Appreciation = Compound growth. Future Value = Current × (1 + rate%)^years. Rent extra return alag se. Ye estimate hai — actual market pe depend karta hai. Rate 5-7% Gorakhpur/Lucknow average maana gaya hai.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let paChart;
function calcPA(){
    const cur=parseFloat(document.getElementById('paCurrent').value)||0;
    const rate=parseFloat(document.getElementById('paRate').value)||0;
    const yrs=parseInt(document.getElementById('paYears').value)||0;
    const rentPct=parseFloat(document.getElementById('paRent').value)||0;
    const future = cur * Math.pow(1+rate/100, yrs);
    const gain = future - cur;
    const rentTotal = cur * (rentPct/100) * yrs; // simple rent without growth
    document.getElementById('paFuture').textContent = '\u20B9' + Math.round(future).toLocaleString('en-IN');
    document.getElementById('paGain').textContent = '\u20B9' + Math.round(gain).toLocaleString('en-IN');
    document.getElementById('paRentTotal').textContent = '\u20B9' + Math.round(rentTotal).toLocaleString('en-IN');
    // chart
    const labels=[], data=[];
    for(let y=0;y<=yrs;y++){ labels.push('Y'+y); data.push(Math.round(cur * Math.pow(1+rate/100, y))); }
    const ctx=document.getElementById('paChart').getContext('2d');
    if(paChart) paChart.destroy();
    paChart=new Chart(ctx,{type:'line',data:{labels,datasets:[{label:'Value',data, borderColor:'#667eea', backgroundColor:'rgba(102,126,234,0.15)', fill:true, tension:0.4}]},options:{responsive:true, plugins:{legend:{display:false}}, scales:{y:{ticks:{callback:v=>'\u20B9'+(v/100000).toFixed(1)+'L'}},x:{grid:{display:false}}}}});
}
calcPA();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
