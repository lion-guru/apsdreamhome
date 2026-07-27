<?php
$page_title = $page_title ?? 'Start Reconciliation Session';
$page_heading = $page_heading ?? 'Start Reconciliation Session';
$collectors = $collectors ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-balance-scale me-2"></i>Start Reconciliation Session</h2>
            <p class="text-muted mb-0">Create a new daily reconciliation for a field agent</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/cash-collections/reconciliations" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <form method="POST" action="<?= BASE_URL ?>/admin/cash-collections/reconciliations/create">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-user me-2"></i>Session Details</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Collector (Field Agent) *</label>
                                <select class="form-select" name="collector_id" required>
                                    <option value="">Select collector...</option>
                                    <?php foreach ($collectors as $c): ?>
                                        <option value="<?= $c['collector_id'] ?? $c['id'] ?? '' ?>"><?= htmlspecialchars($c['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Session Date *</label>
                                <input type="date" class="form-control" name="session_date" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Any notes about this reconciliation session..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>How It Works</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <ul class="mb-0 small">
                            <li class="mb-2">Select the collector and date to reconcile their collections for that day.</li>
                            <li class="mb-2">The system will automatically sum up all <strong>submitted</strong> and <strong>verified</strong> receipts for that collector on that date.</li>
                            <li class="mb-2">If submitted amount ≠ verified amount, a <strong>discrepancy</strong> is flagged.</li>
                            <li class="mb-2">Close the session once you've verified everything is correct.</li>
                        </ul>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-play me-2"></i>Start Reconciliation</button>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-clock me-2"></i>Status Values</h5></div>
                <div class="card-body aps-cp-card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2"><span class="badge bg-success">Open</span> — Session active, can be closed</li>
                        <li class="mb-2"><span class="badge bg-secondary">Closed</span> — Finalized</li>
                        <li class="mb-0"><span class="badge bg-warning text-dark">Discrepancy</span> — Mismatch found</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';
