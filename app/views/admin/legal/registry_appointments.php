<?php
/**
 * Admin SRO Registry Appointments Desk
 */
$page_title = $page_title ?? 'SRO Deed Registry Execution Desk';
$appointments = $appointments ?? [];
$current_status = $current_status ?? 'all';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-0 text-dark">
                <i class="fas fa-stamp text-danger me-2"></i>SRO Deed Registry Execution Desk
            </h3>
            <p class="text-muted small mb-0">Sub-Registrar Office appointments, UP Stamp Duty verification, witness clearance & sale deed completion.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/plot-transfers" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-exchange-alt me-1"></i>Resale NOC Desk
            </a>
            <button onclick="location.reload()" class="btn btn-primary btn-sm">
                <i class="fas fa-sync-alt me-1"></i>Refresh Desk
            </button>
        </div>
    </div>

    <!-- Status Tabs Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2">
                <a href="/admin/registry-appointments?status=all" class="btn btn-sm <?= $current_status === 'all' ? 'btn-dark' : 'btn-outline-secondary' ?>">
                    All Appointments (<?= count($appointments) ?>)
                </a>
                <a href="/admin/registry-appointments?status=requested" class="btn btn-sm <?= $current_status === 'requested' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' ?>">
                    Requested / Pending Slots
                </a>
                <a href="/admin/registry-appointments?status=slot_confirmed" class="btn btn-sm <?= $current_status === 'slot_confirmed' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Slots Confirmed
                </a>
                <a href="/admin/registry-appointments?status=completed" class="btn btn-sm <?= $current_status === 'completed' ? 'btn-success' : 'btn-outline-success' ?>">
                    Registry Executed / Deeds Issued
                </a>
            </div>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Token #</th>
                        <th>Buyer & Booking</th>
                        <th>Colony & Plot</th>
                        <th>SRO Office</th>
                        <th>Preferred Dates</th>
                        <th>Confirmed Date</th>
                        <th>Stamp Duty & Fee</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-calendar-check fa-2x mb-2 d-block text-secondary"></i>
                                No registry appointments found matching the current status filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $a): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace text-danger"><?= htmlspecialchars($a['appointment_token']) ?></span>
                                    <div class="text-muted small"><?= date('d M Y', strtotime($a['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($a['customer_name'] ?? 'Buyer') ?></div>
                                    <div class="text-muted small">
                                        <i class="fas fa-phone-alt me-1 text-success"></i><?= htmlspecialchars($a['customer_phone'] ?? '') ?>
                                    </div>
                                    <span class="badge bg-light text-dark border font-monospace small">Booking #<?= $a['booking_id'] ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary"><?= htmlspecialchars($a['colony_name'] ?? 'Colony') ?></span>
                                    <div class="badge bg-secondary">Plot #<?= htmlspecialchars($a['plot_number'] ?? '') ?></div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark"><?= htmlspecialchars($a['sro_office']) ?></div>
                                    <small class="text-muted">Tehsil Jurisdiction</small>
                                </td>
                                <td class="small font-monospace">
                                    <div>1: <?= date('d M Y', strtotime($a['preferred_date_1'])) ?></div>
                                    <?php if (!empty($a['preferred_date_2'])): ?>
                                        <div class="text-muted">2: <?= date('d M Y', strtotime($a['preferred_date_2'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($a['confirmed_date'])): ?>
                                        <span class="badge bg-primary fs-6 font-monospace">
                                            <i class="far fa-calendar-check me-1"></i><?= date('d M Y', strtotime($a['confirmed_date'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-warning small fw-semibold"><i class="fas fa-clock me-1"></i>Pending Slot</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small fw-bold">₹<?= number_format((float)$a['stamp_duty_amount'], 2) ?></div>
                                    <div class="text-muted small">+₹<?= number_format((float)$a['reg_fee_amount'], 2) ?> fee</div>
                                </td>
                                <td>
                                    <?php if ($a['status'] === 'completed'): ?>
                                        <span class="badge bg-success">Registry Done</span>
                                    <?php elseif ($a['status'] === 'slot_confirmed'): ?>
                                        <span class="badge bg-primary">Slot Confirmed</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Requested</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/admin/registry-appointments/<?= $a['id'] ?>" class="btn btn-outline-primary" title="View & Manage SRO Execution">
                                            <i class="fas fa-gavel me-1"></i>Manage
                                        </a>
                                        <a href="/admin/registry-appointments/<?= $a['id'] ?>/slip" target="_blank" class="btn btn-outline-secondary" title="Print Appointment Slip">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
