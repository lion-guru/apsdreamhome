<?php
/**
 * Admin Plot Transfer & Resale NOC Desk — List View
 */
$page_title = $page_title ?? 'Plot Transfer & Resale NOC Desk';
$current_status = $current_status ?? 'all';
?>

<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-exchange-alt text-primary me-2"></i>Plot Transfer & Resale NOC Desk
            </h4>
            <small class="text-muted">Oversee plot resale, family gifts, and ownership handover with audit-ready NOCs.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/plots" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-th-large me-1"></i>All Plots
            </a>
            <a href="/admin/sales/registry-packet" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-folder me-1"></i>Registry Packets
            </a>
        </div>
    </div>

    <!-- Status Tabs / Counters -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-2">
            <a href="/admin/plot-transfers?status=all" class="card text-decoration-none border-0 shadow-sm text-center p-2 <?= $current_status === 'all' ? 'bg-primary text-white' : 'bg-white text-dark' ?>">
                <div class="small fw-semibold">Total Requests</div>
                <div class="fs-5 fw-bold"><?= (int)($counts['all'] ?? 0) ?></div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="/admin/plot-transfers?status=requested" class="card text-decoration-none border-0 shadow-sm text-center p-2 <?= $current_status === 'requested' ? 'bg-warning text-dark' : 'bg-white text-dark' ?>">
                <div class="small fw-semibold">Pending Review</div>
                <div class="fs-5 fw-bold text-warning"><?= (int)($counts['requested'] ?? 0) ?></div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="/admin/plot-transfers?status=approved" class="card text-decoration-none border-0 shadow-sm text-center p-2 <?= $current_status === 'approved' ? 'bg-info text-white' : 'bg-white text-dark' ?>">
                <div class="small fw-semibold">NOC Issued</div>
                <div class="fs-5 fw-bold text-info"><?= (int)($counts['approved'] ?? 0) ?></div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="/admin/plot-transfers?status=completed" class="card text-decoration-none border-0 shadow-sm text-center p-2 <?= $current_status === 'completed' ? 'bg-success text-white' : 'bg-white text-dark' ?>">
                <div class="small fw-semibold">Ownership Transferred</div>
                <div class="fs-5 fw-bold text-success"><?= (int)($counts['completed'] ?? 0) ?></div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="/admin/plot-transfers?status=rejected" class="card text-decoration-none border-0 shadow-sm text-center p-2 <?= $current_status === 'rejected' ? 'bg-danger text-white' : 'bg-white text-dark' ?>">
                <div class="small fw-semibold">Rejected</div>
                <div class="fs-5 fw-bold text-danger"><?= (int)($counts['rejected'] ?? 0) ?></div>
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-2">
            <form method="GET" action="/admin/plot-transfers" class="d-flex gap-2">
                <input type="hidden" name="status" value="<?= htmlspecialchars($current_status) ?>">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Search by NOC #, Buyer name, Phone, Plot #..." value="<?= htmlspecialchars($search ?? '') ?>">
                <button type="submit" class="btn btn-sm btn-primary px-3">
                    <i class="fas fa-search me-1"></i>Search
                </button>
                <?php if (!empty($search)): ?>
                    <a href="/admin/plot-transfers?status=<?= htmlspecialchars($current_status) ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Transfers Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID / NOC #</th>
                        <th>Colony & Plot</th>
                        <th>Current Allottee</th>
                        <th>Proposed Transferee</th>
                        <th>Transfer Type</th>
                        <th>Fee & Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transfers)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No plot transfer requests found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transfers as $t): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold">#<?= (int)$t['id'] ?></div>
                                    <?php if (!empty($t['noc_number'])): ?>
                                        <span class="badge bg-light text-dark border font-monospace">
                                            <?= htmlspecialchars($t['noc_number']) ?>
                                        </span>
                                    <?php else: ?>
                                        <small class="text-muted">Provisional</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-primary">Plot <?= htmlspecialchars($t['plot_number'] ?? '—') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($t['colony_name'] ?? '—') ?> (<?= (float)($t['area_sqft'] ?? 0) ?> sq.ft)</small>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($t['current_customer_name'] ?? '—') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($t['current_customer_phone'] ?? '—') ?></small>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($t['new_buyer_name'] ?? '—') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($t['new_buyer_phone'] ?? '—') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= strtoupper(str_replace('_', ' ', (string)($t['transfer_type'] ?? 'resale'))) ?>
                                    </span>
                                </td>
                                <td>
                                    <div>₹<?= number_format((float)($t['transfer_amount'] ?? 0), 2) ?></div>
                                    <small class="text-muted">Fee: ₹<?= number_format((float)($t['transfer_fee'] ?? 0), 2) ?> (<?= htmlspecialchars($t['transfer_fee_status'] ?? 'pending') ?>)</small>
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
                                    <a href="/admin/plot-transfers/<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-primary" title="Review Request">
                                        <i class="fas fa-eye me-1"></i>Review
                                    </a>
                                    <?php if (!empty($t['noc_number'])): ?>
                                        <a href="/admin/plot-transfers/noc/<?= (int)$t['id'] ?>" target="_blank" class="btn btn-sm btn-outline-success ms-1" title="Print NOC">
                                            <i class="fas fa-print me-1"></i>NOC
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
