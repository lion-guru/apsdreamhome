<?php $deal = $deal ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Deal Details</h4>
    <a href="<?= BASE_URL ?>admin/deals" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Deals</a>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><strong><?= htmlspecialchars($deal['name'] ?? $deal['deal_name'] ?? 'Untitled Deal') ?></strong></span>
                <?php $stage = $deal['stage'] ?? 'lead'; $stageLower = strtolower($stage); ?>
                <span class="badge bg-<?= in_array($stageLower, ['won', 'closed_won']) ? 'success' : (in_array($stageLower, ['lost', 'closed_lost']) ? 'danger' : ($stageLower === 'negotiating' ? 'warning' : ($stageLower === 'proposal' ? 'info' : 'primary'))) ?> fs-6">
                    <?= ucfirst($stage) ?>
                </span>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Customer</small>
                        <strong><?= htmlspecialchars($deal['customer_name'] ?? $deal['customer'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Property / Plot</small>
                        <strong><?= htmlspecialchars($deal['property_name'] ?? $deal['property'] ?? $deal['plot'] ?? 'N/A') ?></strong>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Amount</small>
                        <strong class="text-success">â‚¹<?= number_format($deal['amount'] ?? $deal['deal_value'] ?? 0, 2) ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Probability</small>
                        <strong><?= ($deal['probability'] ?? $deal['probability_pct'] ?? 0) ?>%</strong>
                        <div class="progress mt-1" class="style-51910">
                            <div class="progress-bar bg-<?= ($deal['probability'] ?? 0) >= 80 ? 'success' : (($deal['probability'] ?? 0) >= 50 ? 'warning' : 'danger') ?>" class="style-4321"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Expected Close Date</small>
                        <strong><?= htmlspecialchars($deal['expected_close_date'] ?? $deal['close_date'] ?? 'N/A') ?></strong>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Assigned To</small>
                        <strong><?= htmlspecialchars($deal['assigned_to_name'] ?? $deal['assigned_to'] ?? 'Unassigned') ?></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Created</small>
                        <strong><?= htmlspecialchars($deal['created_at'] ?? date('Y-m-d')) ?></strong>
                    </div>
                </div>
                <?php if (!empty($deal['description'])): ?>
                    <hr>
                    <small class="text-muted d-block mb-1">Description</small>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($deal['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header aps-cp-card-header">Actions</div>
            <div class="card-body d-grid gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#moveStageModal"><i class="fas fa-arrow-right"></i> Move Stage</button>
                <button class="btn btn-success" onclick="markDealStatus('won')"><i class="fas fa-check-circle"></i> Mark Won</button>
                <button class="btn btn-danger" onclick="markDealStatus('lost')"><i class="fas fa-times-circle"></i> Mark Lost</button>
            </div>
        </div>
        <div class="card aps-cp-card">
            <div class="card-header aps-cp-card-header">Stage Progress</div>
            <div class="card-body aps-cp-card-body">
                <?php $stages = ['lead', 'qualified', 'proposal', 'negotiating', 'closed_won']; $currentIdx = array_search($stageLower, $stages); ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($stages as $i => $s): ?>
                        <div class="list-group-item d-flex align-items-center border-0 ps-0">
                            <span class="badge bg-<?= $i <= $currentIdx ? 'success' : 'light text-muted' ?> me-2 rounded-circle">
                                <i class="fas fa-<?= $i < $currentIdx ? 'check' : ($i === $currentIdx ? 'chevron-right' : 'circle') ?>"></i>
                            </span>
                            <span class="<?= $i === $currentIdx ? 'fw-bold' : '' ?>"><?= ucfirst($s) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="moveStageModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Move to Next Stage</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="post" action="<?= BASE_URL ?>admin/deals/<?= (int)($deal['id'] ?? 0) ?>/stage">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Select Stage</label>
                <select name="stage" class="form-select" required>
                    <option value="lead" <?= $stageLower === 'lead' ? 'selected' : '' ?>>Lead</option>
                    <option value="qualified" <?= $stageLower === 'qualified' ? 'selected' : '' ?>>Qualified</option>
                    <option value="proposal" <?= $stageLower === 'proposal' ? 'selected' : '' ?>>Proposal</option>
                    <option value="negotiating" <?= $stageLower === 'negotiating' ? 'selected' : '' ?>>Negotiating</option>
                    <option value="won" <?= $stageLower === 'won' ? 'selected' : '' ?>>Won</option>
                    <option value="lost" <?= $stageLower === 'lost' ? 'selected' : '' ?>>Lost</option>
                </select>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update Stage</button></div>
    </form>
</div></div></div>

<script>
function markDealStatus(status) {
    if (!confirm('Mark this deal as ' + status.toUpperCase() + '?')) return;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= BASE_URL ?>admin/deals/<?= (int)($deal['id'] ?? 0) ?>/stage';
    var csrf = document.createElement('input'); csrf.type = 'hidden'; csrf.name = 'csrf_token'; csrf.value = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>';
    var stage = document.createElement('input'); stage.type = 'hidden'; stage.name = 'stage'; stage.value = status;
    form.appendChild(csrf); form.appendChild(stage);
    document.body.appendChild(form); form.submit();
}
</script>
