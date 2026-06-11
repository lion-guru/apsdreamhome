<?php $pageTitle = $page_title ?? 'Smart Home ROI Calculator'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-calculator me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Input</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= ($base ?? BASE_URL) ?>iot/roi-calculator">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Property Value (₹)</label>
                            <input type="number" name="property_value" class="form-control" placeholder="e.g. 5000000" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Smart Home Investment (₹)</label>
                            <input type="number" name="smart_home_cost" class="form-control" placeholder="e.g. 200000" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monthly Energy Savings (₹)</label>
                            <input type="number" name="energy_savings" class="form-control" placeholder="e.g. 5000" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-calculator me-1"></i>Calculate ROI</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>About ROI</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p>Calculate the return on investment for your smart home setup.</p>
                    <ul class="mb-0">
                        <li>15% average property value increase</li>
                        <li>20-30% energy cost savings</li>
                        <li>Typical payback period: 12-24 months</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
