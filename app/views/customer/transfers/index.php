<?php
/**
 * Customer Resale & Plot Transfer Desk — Dashboard View
 */
$page_title = $page_title ?? 'Plot Resale & Transfer Desk';
$bookings = $bookings ?? [];
$transfers = $transfers ?? [];
?>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="mb-1 fw-bold">
                <i class="fas fa-exchange-alt text-primary me-2"></i>Plot Resale & Transfer Desk
            </h3>
            <p class="text-muted mb-0">Apply for ownership transfer, gift to family, or resell your plot with an official APS NOC.</p>
        </div>
        <a href="/customer/documents" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-folder me-1"></i>My Documents
        </a>
    </div>

    <!-- Active Requests Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 fw-bold d-flex justify-content-between align-items-center">
            <span><i class="fas fa-history text-primary me-2"></i>My Transfer Applications</span>
            <span class="badge bg-light text-dark"><?= count($transfers) ?> Applications</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($transfers)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    You have not submitted any transfer or resale requests yet.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ref / NOC</th>
                                <th>Plot & Colony</th>
                                <th>Transferee (Buyer)</th>
                                <th>Transfer Type</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transfers as $t): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold">#<?= (int)$t['id'] ?></div>
                                        <?php if (!empty($t['noc_number'])): ?>
                                            <span class="badge bg-success font-monospace"><?= htmlspecialchars($t['noc_number']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong>Plot <?= htmlspecialchars($t['plot_number'] ?? '—') ?></strong>
                                        <div class="small text-muted"><?= htmlspecialchars($t['colony_name'] ?? '—') ?></div>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($t['new_buyer_name'] ?? '—') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($t['new_buyer_phone'] ?? '—') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= strtoupper(str_replace('_', ' ', (string)($t['transfer_type'] ?? 'resale'))) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $st = $t['status'] ?? 'requested';
                                        $badge = match ($st) {
                                            'requested' => 'warning text-dark',
                                            'under_review' => 'info text-white',
                                            'approved' => 'primary',
                                            'completed' => 'success',
                                            'rejected' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $badge ?>"><?= strtoupper(str_replace('_', ' ', $st)) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <?php if (!empty($t['noc_number'])): ?>
                                            <a href="/customer/plot-transfers/noc/<?= (int)$t['id'] ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-print me-1"></i>View NOC
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Under Review</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Eligible Bookings Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 fw-bold">
            <i class="fas fa-home text-primary me-2"></i>My Booked Plots — Transfer Eligibility
        </div>
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <div class="text-center py-4 text-muted">
                    No active plot bookings found in your account.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($bookings as $b): ?>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="fw-bold mb-0 text-primary">Plot <?= htmlspecialchars($b['plot_number'] ?? '—') ?></h5>
                                            <small class="text-muted"><?= htmlspecialchars($b['colony_name'] ?? '—') ?> (<?= (float)($b['area_sqft'] ?? 0) ?> sq.ft)</small>
                                        </div>
                                        <span class="badge bg-light text-dark border">
                                            Booking #<?= htmlspecialchars($b['booking_number'] ?? $b['id']) ?>
                                        </span>
                                    </div>

                                    <?php if ($b['transfer_eligible']): ?>
                                        <div class="alert alert-success py-2 px-3 small mb-3">
                                            <i class="fas fa-check-circle me-1"></i><strong>Eligible for Resale / Transfer:</strong> All installment & maintenance dues cleared.
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning py-2 px-3 small mb-3">
                                            <i class="fas fa-exclamation-circle me-1"></i><strong>Pending Clearances:</strong>
                                            <ul class="mb-0 ps-3 mt-1">
                                                <?php foreach ($b['transfer_reasons'] as $r): ?>
                                                    <li><?= htmlspecialchars($r) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-2">
                                    <?php if ($b['transfer_eligible']): ?>
                                        <a href="/customer/plot-transfers/apply/<?= (int)$b['id'] ?>" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-paper-plane me-1"></i>Apply for Transfer / Resale NOC
                                        </a>
                                    <?php else: ?>
                                        <a href="/customer/maintenance-dues" class="btn btn-outline-warning btn-sm w-100">
                                            <i class="fas fa-wallet me-1"></i>Clear Dues to Unlock Transfer
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
