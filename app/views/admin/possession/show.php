<?php
$page_title = $page_title ?? 'Possession Details';
$active_page = 'possession';

$possessionLabels = [
    'not_due' => ['label' => 'Not Due', 'class' => 'secondary', 'icon' => 'fa-clock', 'step' => 0],
    'ready' => ['label' => 'Ready', 'class' => 'success', 'icon' => 'fa-check-circle', 'step' => 1],
    'scheduled' => ['label' => 'Scheduled', 'class' => 'primary', 'icon' => 'fa-calendar-check', 'step' => 2],
    'handed_over' => ['label' => 'Handed Over', 'class' => 'info', 'icon' => 'fa-home', 'step' => 3],
    'delayed' => ['label' => 'Delayed', 'class' => 'danger', 'icon' => 'fa-exclamation-triangle', 'step' => -1],
];

$currentStatus = $booking['possession_status'] ?? 'not_due';
$currentStep = $possessionLabels[$currentStatus]['step'] ?? 0;
$isDelayed = $currentStatus === 'delayed';
?>
<style>
    .possession-progress {
        display: flex; justify-content: space-between; align-items: flex-start; position: relative; padding: 20px 0;
    }
    .possession-progress::before {
        content: ''; position: absolute; top: 48px; left: 60px; right: 60px; height: 3px; background: #dee2e6; z-index: 0;
    }
    .possession-progress .step {
        text-align: center; position: relative; z-index: 1; flex: 1;
    }
    .possession-progress .step .step-icon {
        width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 8px; font-size: 22px; border: 3px solid #dee2e6; background: #fff; color: #adb5bd; transition: all .3s;
    }
    .possession-progress .step.completed .step-icon { border-color: #0dcaf0; background: #0dcaf0; color: #fff; }
    .possession-progress .step.active .step-icon { border-color: #0d6efd; background: #0d6efd; color: #fff; box-shadow: 0 0 0 5px rgba(13,110,253,.2); }
    .possession-progress .step.delayed .step-icon { border-color: #dc3545; background: #dc3545; color: #fff; animation: pulse 1.5s infinite; }
    .possession-progress .step .step-label { font-size: 11px; color: #6c757d; font-weight: 500; }
    .possession-progress .step.completed .step-label { color: #0dcaf0; font-weight: 700; }
    .possession-progress .step.active .step-label { color: #0d6efd; font-weight: 700; }
    .possession-progress .step.delayed .step-label { color: #dc3545; font-weight: 700; }
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.08); } 100% { transform: scale(1); } }
    .checklist-item { display: flex; align-items: center; gap: 12px; padding: 8px 12px; border-bottom: 1px solid #eee; }
    .checklist-item:last-child { border-bottom: none; }
    .checklist-item .item-name { flex: 1; }
    .checklist-item.completed { background: #f0fff4; }
    .checklist-item .badge { cursor: pointer; }
    .section-header { margin-bottom: 16px; }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-key"></i> Possession - Booking #<?= htmlspecialchars($booking['booking_number'] ?? $booking['id']) ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/admin/possession" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        <?php if ($currentStatus === 'handed_over' && !empty($booking['possession_letter_number'])): ?>
            <a href="<?= BASE_URL ?>/admin/possession/letter/<?= $booking['id'] ?>" class="btn btn-success ms-2" target="_blank"><i class="fas fa-file-pdf"></i> Possession Letter</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/admin/possession/checklist/<?= $booking['id'] ?>" class="btn btn-outline-primary ms-2"><i class="fas fa-list"></i> Manage Checklist</a>
    </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<?php if ($isDelayed): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> This possession has been <strong>delayed</strong>.</div>
<?php endif; ?>

<?php if (isset($pending_handovers) && $pending_handovers > 0): ?>
    <div class="alert alert-warning"><i class="fas fa-info-circle"></i> <strong><?= $pending_handovers ?></strong> other booking(s) awaiting handover.</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body aps-cp-card-body">
        <div class="possession-progress">
            <?php foreach ([
                'not_due' => ['label' => 'Registered', 'icon' => 'fa-file-signature'],
                'ready' => ['label' => 'Ready', 'icon' => 'fa-check-circle'],
                'scheduled' => ['label' => 'Scheduled', 'icon' => 'fa-calendar-check'],
                'handed_over' => ['label' => 'Handed Over', 'icon' => 'fa-home'],
            ] as $key => $step): ?>
                <?php $st = $possessionLabels[$key]; ?>
                <div class="step <?= $currentStep > $st['step'] ? 'completed' : ($currentStep === $st['step'] && !$isDelayed ? 'active' : '') ?>">
                    <div class="step-icon"><i class="fas <?= $step['icon'] ?>"></i></div>
                    <div class="step-label"><?= $step['label'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-info-circle"></i> Booking Info</h5></div>
            <div class="card-body aps-cp-card-body">
                <div class="table-responsive"><table class="table table-bordered table-sm">
                    <tr><th class="style-97126">Booking #</th><td><strong><?= htmlspecialchars($booking['booking_number'] ?? 'N/A') ?></strong></td></tr>
                    <tr><th>Property</th><td><?= htmlspecialchars($booking['property_title'] ?? '') ?> <small class="text-muted">(<?= htmlspecialchars($booking['property_location'] ?? '') ?>)</small></td></tr>
                    <tr><th>Plot #</th><td><?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></td></tr>
                    <tr><th>Colony</th><td><?= htmlspecialchars($booking['colony_name'] ?? 'N/A') ?></td></tr>
                    <tr><th>Area</th><td><?= number_format($booking['area_sqft'] ?? 0) ?> sqft</td></tr>
                    <tr><th>Buyer</th><td><?= htmlspecialchars($booking['customer_name'] ?? '') ?><br><small><?= htmlspecialchars($booking['customer_email'] ?? '') ?> / <?= htmlspecialchars($booking['customer_phone'] ?? '') ?></small></td></tr>
                    <tr><th>Possession Status</th><td><span class="badge bg-<?= $possessionLabels[$currentStatus]['class'] ?? 'secondary' ?> fs-6"><?= $possessionLabels[$currentStatus]['label'] ?? ucfirst($currentStatus) ?></span></td></tr>
                    <?php if (!empty($booking['possession_date'])): ?>
                        <tr><th>Possession Date</th><td><?= date('d M Y', strtotime($booking['possession_date'])) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($booking['possession_letter_number'])): ?>
                        <tr><th>Letter #</th><td><?= htmlspecialchars($booking['possession_letter_number']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($booking['handover_by_name'])): ?>
                        <tr><th>Handover By</th><td><?= htmlspecialchars($booking['handover_by_name']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($booking['defect_liability_end_date'])): ?>
                        <tr><th>Defect Liability Until</th><td><?= date('d M Y', strtotime($booking['defect_liability_end_date'])) ?> <small class="text-muted">(<?= intval($booking['defect_liability_period'] ?? 0) ?> days)</small></td></tr>
                    <?php endif; ?>
                </table></div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <?php if ($currentStep < 2 && !$isDelayed): ?>
        <div class="card mb-4">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Schedule Handover</h5></div>
            <div class="card-body aps-cp-card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/possession/<?= $booking['id'] ?>/schedule">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-2">
                        <label class="form-label">Possession Date</label>
                        <input type="date" class="form-control" name="possession_date" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                    </div>
                    <div class="mb-2">
                        <textarea class="form-control" name="handover_notes" rows="2" placeholder="Notes about this handover..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Schedule Handover</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-check-double"></i> Mark Handed Over</h5></div>
            <div class="card-body aps-cp-card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/possession/<?= $booking['id'] ?>/handover">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Handover Date</label>
                            <input type="date" class="form-control" name="possession_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Defect Liability (days)</label>
                            <input type="number" class="form-control" name="defect_liability_period" value="365" min="30" max="1095">
                        </div>
                    </div>
                    <div class="mb-2">
                        <textarea class="form-control" name="handover_notes" rows="2" placeholder="Handover notes..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success" <?= $currentStatus === 'handed_over' ? 'disabled' : '' ?>><i class="fas fa-home"></i> <?= $currentStatus === 'handed_over' ? 'Already Handed Over' : 'Mark as Handed Over' ?></button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-sticky-note"></i> Handover Notes</h5></div>
            <div class="card-body aps-cp-card-body">
                <?php if (!empty($booking['handover_notes'])): ?>
                    <pre class="style-86263"><?= htmlspecialchars($booking['handover_notes']) ?></pre>
                <?php else: ?>
                    <p class="text-muted">No handover notes yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> Handover Checklist</h5>
                <a href="<?= BASE_URL ?>/admin/possession/checklist/<?= $booking['id'] ?>" class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="card-body aps-cp-card-body">
                <?php if (empty($checklist)): ?>
                    <p class="text-muted">No checklist items yet. <a href="<?= BASE_URL ?>/admin/possession/checklist/<?= $booking['id'] ?>">Add items</a>.</p>
                <?php else: ?>
                    <?php $completedCount = 0; foreach ($checklist as $item): if ($item['is_completed']) $completedCount++; endforeach; ?>
                    <div class="mb-2">
                        <small class="text-muted"><?= $completedCount ?> / <?= count($checklist) ?> completed</small>
                        <div class="progress" class="style-12222">
                            <div class="progress-bar bg-success" class="style-24354"></div>
                        </div>
                    </div>
                    <div class="style-52319">
                        <?php foreach ($checklist as $item): ?>
                            <div class="checklist-item <?= $item['is_completed'] ? 'completed' : '' ?>">
                                <span class="badge bg-<?= $item['is_completed'] ? 'success' : 'secondary' ?>">
                                    <i class="fas <?= $item['is_completed'] ? 'fa-check-circle' : 'fa-circle' ?>"></i>
                                </span>
                                <span class="item-name"><?= htmlspecialchars($item['item_name']) ?></span>
                                <?php if ($item['is_completed']): ?>
                                    <small class="text-muted">by <?= htmlspecialchars($item['completed_by'] ?? '') ?> on <?= !empty($item['completed_at']) ? date('d M', strtotime($item['completed_at'])) : '' ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-bug"></i> Defect Reports</h5>
                <?php if ($currentStatus === 'handed_over'): ?>
                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#reportDefectForm">Report Defect</button>
                <?php endif; ?>
            </div>
            <div class="card-body aps-cp-card-body">
                <?php if ($currentStatus === 'handed_over'): ?>
                <div class="collapse <?= ($focus_section ?? '') === 'defects' ? 'show' : '' ?>" id="reportDefectForm">
                    <form method="POST" action="<?= BASE_URL ?>/admin/possession/defects/<?= $booking['id'] ?>/report" class="mb-3 p-3 bg-light rounded">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-2">
                            <label class="form-label">Defect Type</label>
                            <select class="form-select" name="defect_type">
                                <option value="structural">Structural</option>
                                <option value="plumbing">Plumbing</option>
                                <option value="electrical">Electrical</option>
                                <option value="flooring">Flooring</option>
                                <option value="painting">Painting</option>
                                <option value="boundary">Boundary Wall</option>
                                <option value="road">Road/Approach</option>
                                <option value="drainage">Drainage</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="2" required></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-plus"></i> Report Defect</button>
                    </form>
                </div>
                <?php endif; ?>

                <?php if (empty($defects)): ?>
                    <p class="text-muted">No defect reports for this booking.</p>
                <?php else: ?>
                    <div class="style-61454">
                        <?php foreach ($defects as $d): ?>
                            <?php $priorityClass = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger', 'critical' => 'dark']; ?>
                            <?php $statusClass = ['open' => 'danger', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary']; ?>
                            <div class="border rounded p-3 mb-2 <?= $d['status'] === 'resolved' || $d['status'] === 'closed' ? 'bg-light' : 'bg-white' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= htmlspecialchars(ucfirst($d['defect_type'] ?? 'General')) ?></strong>
                                        <span class="badge bg-<?= $priorityClass[$d['priority']] ?? 'secondary' ?> ms-1"><?= ucfirst($d['priority']) ?></span>
                                        <span class="badge bg-<?= $statusClass[$d['status']] ?? 'secondary' ?> ms-1"><?= ucfirst($d['status']) ?></span>
                                    </div>
                                    <small class="text-muted"><?= date('d M Y', strtotime($d['created_at'])) ?></small>
                                </div>
                                <p class="mb-1 mt-1"><?= htmlspecialchars($d['description']) ?></p>
                                <small class="text-muted">Reported by: <?= htmlspecialchars($d['reported_by_name'] ?? 'Admin') ?></small>
                                <?php if (!empty($d['resolution_notes'])): ?>
                                    <div class="mt-1 p-2 bg-success bg-opacity-10 rounded">
                                        <small><strong>Resolution:</strong> <?= htmlspecialchars($d['resolution_notes']) ?></small>
                                        <?php if (!empty($d['resolved_by_name'])): ?><br><small class="text-muted">Resolved by: <?= htmlspecialchars($d['resolved_by_name']) ?> on <?= !empty($d['resolved_at']) ? date('d M Y', strtotime($d['resolved_at'])) : '' ?></small><?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($d['status'] === 'open' || $d['status'] === 'in_progress'): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/possession/defects/resolve/<?= $d['id'] ?>" class="mt-2">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="defect_id" value="<?= $d['id'] ?>">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" name="resolution_notes" placeholder="Resolution notes..." required>
                                            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Resolve</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
