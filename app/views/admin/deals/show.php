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
                        <strong class="text-success">₹<?= number_format($deal['amount'] ?? $deal['deal_value'] ?? 0, 2) ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Probability</small>
                        <strong><?= ($deal['probability'] ?? $deal['probability_pct'] ?? 0) ?>%</strong>
                        <div class="progress mt-1" style="height:6px">
                            <div class="progress-bar bg-<?= ($deal['probability'] ?? 0) >= 80 ? 'success' : (($deal['probability'] ?? 0) >= 50 ? 'warning' : 'danger') ?>" style="width:<?= $deal['probability'] ?? $deal['probability_pct'] ?? 0 ?>%"></div>
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
                <button class="btn btn-primary" onclick="alert('Move Stage')"><i class="fas fa-arrow-right"></i> Move Stage</button>
                <button class="btn btn-success" onclick="alert('Mark as Won')"><i class="fas fa-check-circle"></i> Mark Won</button>
                <button class="btn btn-danger" onclick="alert('Mark as Lost')"><i class="fas fa-times-circle"></i> Mark Lost</button>
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
