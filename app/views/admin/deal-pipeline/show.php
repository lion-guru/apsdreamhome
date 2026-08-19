<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Deal Details - <?= htmlspecialchars($deal['deal_number'] ?? '') ?></h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/deal-pipeline" class="btn btn-secondary">Back to Pipeline</a>
            <a href="<?= BASE_URL ?>/admin/deal-pipeline/<?= $deal['id'] ?>/timeline" class="btn btn-info">Timeline</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header aps-cp-card-header"><h5>Deal Information</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Deal Number:</strong> <?= htmlspecialchars($deal['deal_number'] ?? 'N/A') ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Stage:</strong>
                            <span class="badge bg-<?= $deal['stage'] === 'closed_won' ? 'success' : ($deal['stage'] === 'closed_lost' ? 'danger' : 'primary') ?>">
                                <?= $deal['stage_label'] ?? $deal['stage'] ?>
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Deal Value:</strong> ₹<?= number_format($deal['deal_value'] ?? 0) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Priority:</strong>
                            <span class="badge bg-<?= ($deal['priority'] ?? 'medium') === 'urgent' ? 'danger' : (($deal['priority'] ?? 'medium') === 'high' ? 'warning' : 'info') ?>">
                                <?= ucfirst($deal['priority'] ?? 'Medium') ?>
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Expected Close:</strong> <?= htmlspecialchars($deal['expected_close_date'] ?? 'Not set') ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Probability:</strong> <?= $deal['probability'] ?? 0 ?>%
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Source:</strong> <?= ucfirst($deal['source'] ?? 'N/A') ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Assigned To:</strong> <?= htmlspecialchars($deal['assigned_to_name'] ?? 'Unassigned') ?>
                        </div>
                    </div>
                    <?php if (!empty($deal['notes'])): ?>
                    <div class="mt-3">
                        <strong>Notes:</strong>
                        <p class="mt-1"><?= nl2br(htmlspecialchars($deal['notes'] ?? '')) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($deal_history)): ?>
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><h5>Recent Activity</h5></div>
                <div class="card-body aps-cp-card-body">
                    <ul class="list-group">
                        <?php foreach ($deal_history as $h): ?>
                        <li class="list-group-item">
                            <small class="text-muted"><?= $h['created_at'] ?></small><br>
                            <strong><?= ucwords(str_replace('_', ' ', $h['action'])) ?>:</strong>
                            <?= htmlspecialchars($h['old_value'] ?? '') ?> &rarr; <?= htmlspecialchars($h['new_value'] ?? '') ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header aps-cp-card-header"><h5>Customer</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p><strong>Name:</strong> <?= htmlspecialchars($deal['customer_name'] ?? 'N/A') ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($deal['customer_email'] ?? 'N/A') ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($deal['customer_phone'] ?? 'N/A') ?></p>
                </div>
            </div>

            <?php if (!empty($deal['property_title'])): ?>
            <div class="card mb-3">
                <div class="card-header aps-cp-card-header"><h5>Property</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p><strong>Title:</strong> <?= htmlspecialchars($deal['property_title'] ?? '') ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($deal['property_location'] ?? '') ?></p>
                    <p><strong>Price:</strong> ₹<?= number_format($deal['property_price'] ?? 0) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($deal['stage'] !== 'closed_won' && $deal['stage'] !== 'closed_lost'): ?>
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><h5>Actions</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/deal-pipeline/<?= $deal['id'] ?>/move-stage" class="mb-2">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <label class="form-label">Move to Stage:</label>
                        <select name="stage" class="form-select mb-2">
                            <option value="qualified" <?= $deal['stage'] === 'qualified' ? 'selected' : '' ?>>Qualified</option>
                            <option value="site_visit" <?= $deal['stage'] === 'site_visit' ? 'selected' : '' ?>>Site Visit</option>
                            <option value="negotiation" <?= $deal['stage'] === 'negotiation' ? 'selected' : '' ?>>Negotiation</option>
                            <option value="booking" <?= $deal['stage'] === 'booking' ? 'selected' : '' ?>>Booking</option>
                            <option value="agreement" <?= $deal['stage'] === 'agreement' ? 'selected' : '' ?>>Agreement</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm w-100">Move Stage</button>
                    </form>
                    <hr>
                    <a href="<?= BASE_URL ?>/admin/deal-pipeline/<?= $deal['id'] ?>/mark-won" class="btn btn-success btn-sm w-100 mb-1" data-aps-confirm="Mark this deal as won?">Mark as Won</a>
                    <a href="<?= BASE_URL ?>/admin/deal-pipeline/<?= $deal['id'] ?>/mark-lost" class="btn btn-danger btn-sm w-100" data-aps-confirm="Mark this deal as lost?">Mark as Lost</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
