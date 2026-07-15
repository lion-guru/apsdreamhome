<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-calculator me-2"></i>Security ROI Calculator</h4>
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-sliders me-2"></i>Investment Parameters</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form id="roiForm" onsubmit="event.preventDefault(); calculateROI();">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Security Investment Amount (₹)</label>
                            <input type="number" class="form-control" id="security_investment" value="5000000" min="100000" step="100000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Timeframe (Years)</label>
                            <select class="form-select" id="timeframe">
                                <option value="1">1 Year</option>
                                <option value="2">2 Years</option>
                                <option value="3" selected>3 Years</option>
                                <option value="5">5 Years</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-calculator me-2"></i>Calculate ROI</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Results</h5></div>
                <div class="card-body aps-cp-card-body" id="roiResults">
                    <div class="text-center text-muted py-5"><i class="fas fa-arrow-left me-2"></i>Set parameters and calculate</div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function calculateROI() {
    const investment = parseInt(document.getElementById('security_investment').value) || 5000000;
    const timeframe = parseInt(document.getElementById('timeframe').value) || 3;

    fetch('<?= $base ?? BASE_URL ?>security/roi-calculator', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({security_investment: investment, timeframe: timeframe})
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) { document.getElementById('roiResults').innerHTML = '<div class="alert alert-danger">Error calculating ROI</div>'; return; }
        const d = res.data;
        let html = '<div class="row g-3">';
        html += '<div class="col-6"><div class="p-3 bg-light rounded text-center"><div class="fs-3 fw-bold text-primary">' + d.roi_metrics.total_roi + '%</div><small>Total ROI</small></div></div>';
        html += '<div class="col-6"><div class="p-3 bg-light rounded text-center"><div class="fs-3 fw-bold text-success">' + d.roi_metrics.annual_roi + '%</div><small>Annual ROI</small></div></div>';
        html += '<div class="col-6"><div class="p-3 bg-light rounded text-center"><div class="fs-3 fw-bold text-info">' + d.roi_metrics.payback_period + ' yrs</div><small>Payback Period</small></div></div>';
        html += '<div class="col-6"><div class="p-3 bg-light rounded text-center"><div class="fs-3 fw-bold text-warning">' + d.roi_metrics.break_even_months + ' mo</div><small>Break-Even</small></div></div>';
        html += '</div><hr>';
        html += '<h6>Investment Breakdown</h6>';
        for (let [k, v] of Object.entries(d.investment_breakdown)) { html += '<div class="d-flex justify-content-between mb-1"><small>' + k.replace(/_/g, ' ') + '</small><strong>₹' + v.toLocaleString() + '</strong></div>'; }
        html += '<hr><h6>Benefits Analysis</h6>';
        for (let [k, v] of Object.entries(d.benefits_analysis)) { html += '<div class="d-flex justify-content-between mb-1"><small>' + k.replace(/_/g, ' ') + '</small><strong>₹' + v.toLocaleString() + '</strong></div>'; }
        document.getElementById('roiResults').innerHTML = html;
    })
    .catch(() => { document.getElementById('roiResults').innerHTML = '<div class="alert alert-danger">Network error</div>'; });
}
</script>
