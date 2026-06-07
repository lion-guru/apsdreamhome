<?php
$acquisitions = $acquisitions ?? [];
$stats = $stats ?? ['total'=>0, 'open'=>0, 'registered'=>0, 'lost'=>0, 'pipeline_value'=>0];
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-file-contract text-primary me-2"></i>Land Acquisitions (Closed Deals)</h4>
        <a href="<?= BASE_URL ?>/admin/land-inventory/leads" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-mountain me-1"></i>View Leads
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="aps-cp-card text-center">
                <div class="aps-cp-card-body">
                    <h3 class="text-primary mb-0"><?= (int)$stats['total'] ?></h3>
                    <small class="text-muted">Total Deals</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card text-center">
                <div class="aps-cp-card-body">
                    <h3 class="text-info mb-0"><?= (int)$stats['open'] ?></h3>
                    <small class="text-muted">In Pipeline</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card text-center">
                <div class="aps-cp-card-body">
                    <h3 class="text-success mb-0"><?= (int)$stats['registered'] ?></h3>
                    <small class="text-muted">Registered</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card text-center">
                <div class="aps-cp-card-body">
                    <h3 class="text-danger mb-0"><?= (int)$stats['lost'] ?></h3>
                    <small class="text-muted">Lost / Dropped</small>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Pipeline</div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Deal #</th>
                            <th>Lead</th>
                            <th>Owner</th>
                            <th>Survey #</th>
                            <th>Final Price</th>
                            <th>Stamp Duty</th>
                            <th>Reg Fee</th>
                            <th>Status</th>
                            <th>Reg Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($acquisitions as $a): ?>
                        <tr>
                            <td><strong>#<?= (int)($a['id'] ?? 0) ?></strong></td>
                            <td><small>Lead #<?= (int)($a['land_lead_id'] ?? 0) ?></small></td>
                            <td><?= htmlspecialchars($a['land_owner_name'] ?? '—') ?></td>
                            <td><small><?= htmlspecialchars($a['survey_number'] ?? '—') ?></small></td>
                            <td>₹<?= number_format((float)($a['final_price'] ?? 0)) ?></td>
                            <td>₹<?= number_format((float)($a['stamp_duty_amount'] ?? 0)) ?></td>
                            <td>₹<?= number_format((float)($a['registration_fee'] ?? 0)) ?></td>
                            <td>
                                <span class="badge bg-<?= ($a['status'] ?? '') === 'registered' ? 'success' : (($a['status'] ?? '') === 'dropped' ? 'danger' : 'info') ?>">
                                    <?= htmlspecialchars(ucwords(str_replace('_',' ', $a['status'] ?? ''))) ?>
                                </span>
                            </td>
                            <td><small><?= htmlspecialchars($a['registration_date'] ?? '—') ?></small></td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/land-inventory/acquisitions/<?= (int)($a['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($acquisitions)): ?>
                        <tr><td colspan="10" class="text-center text-muted py-4">No closed deals yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
