<?php
/**
 * Customer Registry Appointments Portal View
 */
$page_title = $page_title ?? 'Registry & Deed Execution Desk';
$appointments = $appointments ?? [];
$bookings = $bookings ?? [];
?>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-0 text-dark">
                <i class="fas fa-stamp text-danger me-2"></i>Plot Registry & SRO Execution Desk
            </h3>
            <p class="text-muted small mb-0">Book your Sub-Registrar Office registry date slot, check stamp duty & download appointment gatepasses.</p>
        </div>
        <div>
            <a href="/customer/documents" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-folder me-1"></i>My Document Locker
            </a>
        </div>
    </div>

    <!-- Active Registry Appointments -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-calendar-check text-primary me-2"></i>My Registry Appointments</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($appointments)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-times fa-2x mb-2 d-block text-secondary"></i>
                    You have no active registry appointments scheduled. Check eligible plots below to request a slot.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Token #</th>
                                <th>Colony & Plot</th>
                                <th>SRO Office</th>
                                <th>Slot Date</th>
                                <th>Status</th>
                                <th class="text-end">Gatepass Slip</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $a): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold font-monospace text-danger"><?= htmlspecialchars($a['appointment_token']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary"><?= htmlspecialchars($a['colony_name'] ?? 'Colony') ?></div>
                                        <div class="badge bg-secondary">Plot #<?= htmlspecialchars($a['plot_number'] ?? '') ?></div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark"><?= htmlspecialchars($a['sro_office']) ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($a['confirmed_date'])): ?>
                                            <span class="badge bg-primary fs-6 font-monospace">
                                                <i class="far fa-calendar-check me-1"></i><?= date('d M Y', strtotime($a['confirmed_date'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">Pref: <?= date('d M Y', strtotime($a['preferred_date_1'])) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($a['status'] === 'completed'): ?>
                                            <span class="badge bg-success">Registry Done</span>
                                        <?php elseif ($a['status'] === 'slot_confirmed'): ?>
                                            <span class="badge bg-primary">Slot Confirmed</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Under Review</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="/customer/registry-appointments/slip/<?= $a['id'] ?>" target="_blank" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-print me-1"></i>Print Slip
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Eligible Bookings for Registry -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-map-marked-alt text-success me-2"></i>My Plots Available for Registry</h5>
        </div>
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <div class="text-center py-4 text-muted">
                    No active plot bookings eligible for registration at this time.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($bookings as $b): ?>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($b['colony_name']) ?> — Plot #<?= htmlspecialchars($b['plot_number']) ?></h6>
                                    <div class="text-muted small">Booking #<?= $b['booking_number'] ?> | Total: ₹<?= number_format((float)$b['total_plot_value'], 2) ?></div>
                                    <span class="badge bg-success small text-uppercase"><?= str_replace('_', ' ', $b['status']) ?></span>
                                </div>
                                <div>
                                    <a href="/customer/registry-appointments/book/<?= $b['id'] ?>" class="btn btn-danger btn-sm">
                                        <i class="fas fa-calendar-plus me-1"></i>Book SRO Slot
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
