<?php
$p = $property ?? [];
$price = (float)($p['price'] ?? 0);
$defaultRate = 2.0;
$commission = $price * $defaultRate / 100;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Commission Management</h1>
        <p class="text-muted mb-0">Configure commission for this property</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin/resell-properties/edit/<?= $id ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
        <a href="<?= BASE_URL ?>/admin/resell-properties" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card aps-cp-card mb-4">
            <div class="card-header aps-cp-card-header">Commission Calculator</div>
            <div class="card-body aps-cp-card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Property Price (₹)</label>
                        <input type="text" class="form-control" id="calcPrice" value="<?= number_format($price) ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Commission Rate (%)</label>
                        <input type="number" class="form-control" id="calcRate" value="<?= $defaultRate ?>" step="0.1" min="0" max="10" oninput="calculateCommission()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Commission Amount (₹)</label>
                        <input type="text" class="form-control fw-bold text-success" id="calcAmount" value="₹<?= number_format($commission, 2) ?>" readonly>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card border text-center py-3" class="style-37644">
                            <div class="small text-muted">1% Rate</div>
                            <div class="fw-bold">₹<?= number_format($price * 1 / 100, 2) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border text-center py-3" class="style-37644">
                            <div class="small text-muted">1.5% Rate</div>
                            <div class="fw-bold">₹<?= number_format($price * 1.5 / 100, 2) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border text-center py-3" class="style-68713">
                            <div class="small text-muted">2% Rate (Default)</div>
                            <div class="fw-bold text-success">₹<?= number_format($price * 2 / 100, 2) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border text-center py-3" class="style-37644">
                            <div class="small text-muted">3% Rate</div>
                            <div class="fw-bold">₹<?= number_format($price * 3 / 100, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card aps-cp-card">
            <div class="card-header aps-cp-card-header">Commission Structure</div>
            <div class="card-body aps-cp-card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Component</th><th>Rate</th><th>Amount (₹)</th><th>Paid To</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Agent Commission</strong></td>
                                <td><?= $defaultRate ?>%</td>
                                <td class="text-success fw-bold">₹<?= number_format($commission, 2) ?></td>
                                <td><span class="badge bg-info">Selling Agent</span></td>
                            </tr>
                            <tr>
                                <td><strong>Upline Override</strong></td>
                                <td>0.5%</td>
                                <td>₹<?= number_format($price * 0.5 / 100, 2) ?></td>
                                <td><span class="badge bg-warning">Upline Manager</span></td>
                            </tr>
                            <tr>
                                <td><strong>Company Share</strong></td>
                                <td><?= 100 - $defaultRate - 0.5 ?>%</td>
                                <td>₹<?= number_format($price * (100 - $defaultRate - 0.5) / 100, 2) ?></td>
                                <td><span class="badge bg-secondary">Company</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card aps-cp-card mb-4">
            <div class="card-header aps-cp-card-header">Property Summary</div>
            <div class="card-body aps-cp-card-body">
                <div class="mb-2"><strong><?= htmlspecialchars($p['name'] ?? 'N/A') ?></strong></div>
                <div class="small text-muted mb-1">Price: ₹<?= number_format($price) ?></div>
                <div class="small text-muted mb-1">Location: <?= htmlspecialchars($p['location'] ?? 'N/A') ?></div>
                <div class="small text-muted">Area: <?= number_format((int)($p['area_sqft'] ?? 0)) ?> sq ft</div>
            </div>
        </div>
        <div class="card aps-cp-card">
            <div class="card-header aps-cp-card-header">Rate Guide</div>
            <div class="card-body aps-cp-card-body">
                <div class="small mb-2"><span class="badge bg-secondary">1%</span> Minimum commission rate</div>
                <div class="small mb-2"><span class="badge bg-success">2%</span> Standard residential rate</div>
                <div class="small mb-2"><span class="badge bg-primary">2.5-3%</span> Commercial / premium</div>
                <div class="small"><span class="badge bg-warning text-dark">0.5%</span> Upline override</div>
            </div>
        </div>
    </div>
</div>

<script>
function calculateCommission() {
    var price = <?= $price ?>;
    var rate = parseFloat(document.getElementById('calcRate').value) || 0;
    var amount = price * rate / 100;
    document.getElementById('calcAmount').value = '₹' + amount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
</script>
