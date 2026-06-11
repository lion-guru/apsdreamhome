<?php
$pageTitle = $pageTitle ?? 'Commission Calculator';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/apsdreamhome');
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-calculator me-2 text-primary"></i>Commission Calculator</h1>
        <a href="<?= $base ?>/admin/commission" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Calculate Commission</h6></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= $base ?>/admin/commission/calculate">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Agent Name</label>
                            <input type="text" name="agent_name" class="form-control" placeholder="Enter agent name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Property Price (₹)</label>
                            <input type="number" name="property_price" class="form-control" placeholder="Enter property price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Commission Rate (%)</label>
                            <input type="number" name="commission_rate" class="form-control" placeholder="Enter commission rate" step="0.1" min="0" max="100" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-calculator me-1"></i>Calculate</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-success">Result</h6></div>
                <div class="card-body text-center py-5">
                    <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                    <p class="text-muted">Enter values and click Calculate to see the commission result.</p>
                </div>
            </div>
        </div>
    </div>
</div>
