<?php
$page_title = 'Registry Details';
$active_page = 'registry';

$statusSteps = [
    'not_started' => ['label' => 'Not Started', 'icon' => 'fa-circle', 'color' => 'secondary', 'step' => 0],
    'documents_pending' => ['label' => 'Documents', 'icon' => 'fa-file-alt', 'color' => 'warning', 'step' => 1],
    'stamp_duty_pending' => ['label' => 'Stamp Duty', 'icon' => 'fa-rupee-sign', 'color' => 'info', 'step' => 2],
    'appointment_scheduled' => ['label' => 'Appointment', 'icon' => 'fa-calendar-check', 'color' => 'primary', 'step' => 3],
    'registered' => ['label' => 'Registered', 'icon' => 'fa-check-circle', 'color' => 'success', 'step' => 4],
    'mutation_pending' => ['label' => 'Mutation', 'icon' => 'fa-exchange-alt', 'color' => 'dark', 'step' => 5],
    'completed' => ['label' => 'Completed', 'icon' => 'fa-flag-checkered', 'color' => 'success', 'step' => 6],
];

$currentStatus = $booking['registry_status'] ?? 'not_started';
$currentStep = $statusSteps[$currentStatus]['step'] ?? 0;
$isCancelled = $currentStatus === 'cancelled';
?>
<style>
    .registry-progress {
        display: flex; justify-content: space-between; align-items: flex-start; position: relative; padding: 20px 0;
    }
    .registry-progress::before {
        content: ''; position: absolute; top: 48px; left: 40px; right: 40px; height: 3px; background: #dee2e6; z-index: 0;
    }
    .registry-progress .step {
        text-align: center; position: relative; z-index: 1; flex: 1;
    }
    .registry-progress .step .step-icon {
        width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 8px; font-size: 18px; border: 3px solid #dee2e6; background: #fff; color: #adb5bd; transition: all .3s;
    }
    .registry-progress .step.completed .step-icon { border-color: #198754; background: #198754; color: #fff; }
    .registry-progress .step.active .step-icon { border-color: #0d6efd; background: #0d6efd; color: #fff; box-shadow: 0 0 0 5px rgba(13,110,253,.2); }
    .registry-progress .step .step-label { font-size: 11px; color: #6c757d; font-weight: 500; }
    .registry-progress .step.completed .step-label { color: #198754; }
    .registry-progress .step.active .step-label { color: #0d6efd; font-weight: 700; }
    .detail-section { margin-bottom: 24px; }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Registry - Booking #<?= htmlspecialchars($booking['booking_number'] ?? $booking['id']) ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/admin/registry" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        <a href="<?= BASE_URL ?>/admin/registry/history/<?= $booking['id'] ?>" class="btn btn-info text-white ms-2"><i class="fas fa-history"></i> Activity Log</a>
        <?php if ($currentStatus === 'registered' || $currentStatus === 'completed'): ?>
            <a href="<?= BASE_URL ?>/admin/registry/certificate/<?= $booking['id'] ?>" class="btn btn-success ms-2" target="_blank"><i class="fas fa-file-pdf"></i> Certificate</a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<?php if ($isCancelled): ?>
    <div class="alert alert-danger"><i class="fas fa-ban"></i> This registry has been <strong>cancelled</strong>.</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body aps-cp-card-body">
        <div class="registry-progress">
            <?php foreach ($statusSteps as $key => $step): ?>
                <?php if ($key === 'cancelled') continue; ?>
                <div class="step <?= $currentStep > $step['step'] ? 'completed' : ($currentStep === $step['step'] && !$isCancelled ? 'active' : '') ?>">
                    <div class="step-icon"><i class="fas <?= $step['icon'] ?>"></i></div>
                    <div class="step-label"><?= $step['label'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card detail-section">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-info-circle"></i> Booking Info</h5></div>
            <div class="card-body aps-cp-card-body">
                <table class="table table-bordered table-sm">
                    <tr><th style="width:140px">Booking #</th><td><strong><?= htmlspecialchars($booking['booking_number'] ?? 'N/A') ?></strong></td></tr>
                    <tr><th>Property</th><td><?= htmlspecialchars($booking['property_title'] ?? '') ?> <small class="text-muted">(<?= htmlspecialchars($booking['property_location'] ?? '') ?>)</small></td></tr>
                    <tr><th>Plot #</th><td><?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></td></tr>
                    <tr><th>Colony</th><td><?= htmlspecialchars($booking['colony_name'] ?? 'N/A') ?></td></tr>
                    <tr><th>Area</th><td><?= number_format($booking['area_sqft'] ?? 0) ?> sqft</td></tr>
                    <tr><th>Price</th><td>&#8377; <?= number_format(floatval($booking['property_price'] ?? 0), 2) ?></td></tr>
                    <tr><th>Buyer</th><td><?= htmlspecialchars($booking['customer_name'] ?? '') ?><br><small><?= htmlspecialchars($booking['customer_email'] ?? '') ?> / <?= htmlspecialchars($booking['customer_phone'] ?? '') ?></small></td></tr>
                    <tr><th>Current Status</th><td><span class="badge bg-<?= $statusSteps[$currentStatus]['color'] ?? 'secondary' ?> fs-6"><?= $statusSteps[$currentStatus]['label'] ?? ucfirst($currentStatus) ?></span></td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card detail-section">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-file-alt"></i> Documents</h5></div>
            <div class="card-body aps-cp-card-body">
                <p class="text-muted">Status: <strong><?= $currentStep >= 1 ? ($currentStep > 1 ? 'Collected &#10003;' : 'Pending') : 'Not started' ?></strong></p>
                <?php if ($currentStep <= 1 && !$isCancelled): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/registry/<?= $booking['id'] ?>/documents">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-2">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Document details, IDs collected..."><?= htmlspecialchars($booking['registry_notes'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" name="status" value="stamp_duty_pending" class="btn btn-success"><i class="fas fa-check"></i> Mark Documents Collected</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card detail-section">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-rupee-sign"></i> Stamp Duty & Fees</h5></div>
            <div class="card-body aps-cp-card-body">
                <table class="table table-sm table-bordered">
                    <tr><th>Stamp Duty</th><td>&#8377; <?= number_format(floatval($booking['stamp_duty_amount'] ?? 0), 2) ?></td></tr>
                    <tr><th>Registration Fees</th><td>&#8377; <?= number_format(floatval($booking['registration_fees'] ?? 0), 2) ?></td></tr>
                    <tr class="table-active"><th>Total</th><td><strong>&#8377; <?= number_format(floatval($booking['stamp_duty_amount'] ?? 0) + floatval($booking['registration_fees'] ?? 0), 2) ?></strong></td></tr>
                </table>
                <?php if ($currentStep <= 2 && $currentStep >= 1 && !$isCancelled): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/registry/<?= $booking['id'] ?>/stamp-duty">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label">Stamp Duty (&#8377;)</label>
                                <input type="number" step="0.01" class="form-control" name="stamp_duty_amount" value="<?= htmlspecialchars($booking['stamp_duty_amount'] ?? '') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Reg. Fees (&#8377;)</label>
                                <input type="number" step="0.01" class="form-control" name="registration_fees" value="<?= htmlspecialchars($booking['registration_fees'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control" name="notes" rows="1" placeholder="Payment reference..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-info text-white"><i class="fas fa-save"></i> Record Payment</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card detail-section">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-calendar-check"></i> Appointment</h5></div>
            <div class="card-body aps-cp-card-body">
                <table class="table table-bordered table-sm">
                    <tr><th style="width:140px">Office</th><td><?= htmlspecialchars($booking['sub_registrar_office'] ?? 'Not scheduled') ?></td></tr>
                    <tr><th>Appointment</th><td><?= !empty($booking['appointment_date']) ? date('d M Y h:i A', strtotime($booking['appointment_date'])) : 'Not scheduled' ?></td></tr>
                </table>
                <?php if ($currentStep <= 3 && $currentStep >= 2 && !$isCancelled): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/registry/<?= $booking['id'] ?>/appointment">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label">Appointment Date & Time</label>
                                <input type="datetime-local" class="form-control" name="appointment_date" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Sub-Registrar Office</label>
                                <input type="text" class="form-control" name="sub_registrar_office" placeholder="e.g. Sub-Registrar Office, Gorakhpur" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control" name="notes" rows="1" placeholder="Appointment details..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-plus"></i> Schedule Appointment</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card detail-section">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-check-circle"></i> Registration</h5></div>
            <div class="card-body aps-cp-card-body">
                <table class="table table-bordered table-sm">
                    <tr><th style="width:140px">Registry #</th><td><?= htmlspecialchars($booking['registry_number'] ?? 'Not registered') ?></td></tr>
                    <tr><th>Registry Date</th><td><?= !empty($booking['registry_date']) ? date('d M Y', strtotime($booking['registry_date'])) : 'N/A' ?></td></tr>
                </table>
                <?php if ($currentStep <= 4 && $currentStep >= 3 && !$isCancelled): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/registry/<?= $booking['id'] ?>/register">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label">Registry Number</label>
                                <input type="text" class="form-control" name="registry_number" placeholder="e.g. REG-2026-001234" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Registry Date</label>
                                <input type="date" class="form-control" name="registry_date" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control" name="notes" rows="1" placeholder="Registration notes..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check-double"></i> Mark Registered</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card detail-section">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Mutation</h5></div>
            <div class="card-body aps-cp-card-body">
                <?php if (!empty($booking['mutation_number'])): ?>
                <table class="table table-bordered table-sm">
                    <tr><th style="width:140px">Mutation #</th><td><?= htmlspecialchars($booking['mutation_number']) ?></td></tr>
                    <tr><th>Mutation Date</th><td><?= !empty($booking['mutation_date']) ? date('d M Y', strtotime($booking['mutation_date'])) : 'N/A' ?></td></tr>
                    <tr><th>Status</th><td><span class="badge bg-<?= ($booking['mutation_status'] ?? '') === 'completed' ? 'success' : (($booking['mutation_status'] ?? '') === 'in_progress' ? 'warning' : 'secondary') ?>"><?= ucfirst($booking['mutation_status'] ?? 'pending') ?></span></td></tr>
                </table>
                <?php else: ?>
                    <p class="text-muted">Mutation not yet initiated.</p>
                <?php endif; ?>
                <?php if ($currentStep >= 4 && !$isCancelled): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/registry/<?= $booking['id'] ?>/mutation">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="mutation_status">
                                    <option value="pending" <?= ($booking['mutation_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="in_progress" <?= ($booking['mutation_status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="completed" <?= ($booking['mutation_status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label">Mutation Number</label>
                                <input type="text" class="form-control" name="mutation_number" value="<?= htmlspecialchars($booking['mutation_number'] ?? '') ?>" placeholder="e.g. MUT-2026-001">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Mutation Date</label>
                                <input type="date" class="form-control" name="mutation_date" value="<?= $booking['mutation_date'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control" name="notes" rows="1" placeholder="Mutation notes..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-dark"><i class="fas fa-sync"></i> Update Mutation</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card detail-section">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-sticky-note"></i> Registry Notes</h5></div>
            <div class="card-body aps-cp-card-body">
                <?php if (!empty($booking['registry_notes'])): ?>
                    <pre style="white-space: pre-wrap; font-family: inherit; background: #f8f9fa; padding: 12px; border-radius: 6px; max-height: 300px; overflow-y: auto;"><?= htmlspecialchars($booking['registry_notes']) ?></pre>
                <?php else: ?>
                    <p class="text-muted">No notes yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card detail-section">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-history"></i> Recent Activity</h5></div>
            <div class="card-body aps-cp-card-body">
                <?php if (!empty($activities)): ?>
                    <ul class="list-unstyled" style="max-height: 250px; overflow-y: auto;">
                        <?php $count = 0; foreach ($activities as $a): if ($count++ >= 5) break; ?>
                            <li class="mb-2 pb-2 border-bottom">
                                <small class="text-muted"><?= date('d M Y h:i A', strtotime($a['created_at'])) ?></small><br>
                                <strong><?= ucfirst(str_replace('_', ' ', $a['action'])) ?></strong>
                                <?php if (!empty($a['details'])): ?><br><small><?= htmlspecialchars(substr($a['details'], 0, 100)) ?></small><?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?= BASE_URL ?>/admin/registry/history/<?= $booking['id'] ?>" class="btn btn-sm btn-outline-info">View Full History</a>
                <?php else: ?>
                    <p class="text-muted">No activity recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
