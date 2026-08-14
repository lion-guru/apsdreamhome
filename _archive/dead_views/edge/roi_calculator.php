<?php $pageTitle = $page_title ?? 'Edge Computing ROI Calculator'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-calculator me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Calculate ROI</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form id="edgeRoiForm">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label">Initial Investment (₹)</label>
                            <input type="number" name="initial_investment" class="form-control" placeholder="e.g. 1000000" value="1000000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Timeframe (years)</label>
                            <select name="timeframe" class="form-select"><option value="1">1 Year</option><option value="3" selected>3 Years</option><option value="5">5 Years</option></select>
                        </div>
                        <button type="submit" class="btn btn-primary" id="calcEdgeRoi"><i class="fas fa-calculator me-1"></i>Calculate</button>
                    </form>
                    <div id="edgeRoiResult" class="mt-3"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>About Edge ROI</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p>Calculate return on investment for edge computing infrastructure.</p>
                    <ul class="mb-0">
                        <li>40% lower latency vs cloud</li>
                        <li>60% bandwidth savings</li>
                        <li>Typical payback: 12-18 months</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('edgeRoiForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('<?= ($base ?? BASE_URL) ?>edge/roi-calculator', { method:'POST', body:JSON.stringify(Object.fromEntries(fd)), headers:{'Content-Type':'application/json'} })
    .then(r=>r.json()).then(d=>{
        let html='<div class="alert alert-success">';
        if(d.data?.roi_metrics) {
            html+='<h6>ROI: '+d.data.roi_metrics.total_roi+'%</h6>';
            html+='<p>Payback: '+d.data.roi_metrics.payback_period+' months</p>';
            html+='<p>Annual ROI: '+d.data.roi_metrics.annual_roi+'%</p>';
        }
        html+='</div>';
        document.getElementById('edgeRoiResult').innerHTML=html;
    }).catch(()=>{document.getElementById('edgeRoiResult').innerHTML='<div class="alert alert-danger">Calculation failed</div>';});
});
</script>
