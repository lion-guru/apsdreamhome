<?php
/** Employee Self-Service - Expenses */

$page_title = $page_title ?? 'My Expense Claims';
$claims = $claims ?? [];
$categories = $categories ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.claim-row { transition: background 0.15s; }
.claim-row:hover { background: #fefcf6; }
.status-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 999px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-receipt me-2 text-primary"></i>My Expense Claims</h4>
            <p class="text-muted mb-0 small">Track your expense reimbursement claims</p>
        </div>
        <a href="<?= BASE_URL ?>/employee/self-service/expenses/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Claim
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= count($claims) ?></div>
                    <div class="text-white-50 small">Total Claims</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= count(array_filter($claims, fn($c) => in_array($c['status'], ['submitted', 'pending']))) ?></div>
                    <div class="text-white-50 small">Pending Review</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= count(array_filter($claims, fn($c) => $c['status'] === 'approved')) ?></div>
                    <div class="text-white-50 small">Approved</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= count(array_filter($claims, fn($c) => $c['status'] === 'paid')) ?></div>
                    <div class="text-white-50 small">Paid</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Claims Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($claims)): ?>
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Expense Claims</h5>
                <p class="text-muted small mb-3">You haven't submitted any expense claims yet</p>
                <a href="<?= BASE_URL ?>/employee/self-service/expenses/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Submit Claim</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px">Claim #</th>
                            <th>Category</th>
                            <th style="width: 120px">Amount</th>
                            <th style="width": 100px>Date</th>
                            <th style="width: 120px">Status</th>
                            <th style="width: 120px">Submitted</th>
                            <th style="width": 120px>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($claims as $claim): 
                            $statusClass = [
                                'draft' => 'secondary',
                                'submitted' => 'warning',
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'paid' => 'primary',
                                'cancelled' => 'secondary',
                            ][$claim['status']] ?? 'secondary';
                        ?>
                        <tr class="claim-row">
                            <td><code class="small"><?= htmlspecialchars($claim['claim_number']) ?></code></td>
                            <td>
                                <span class="badge bg-light text-dark" style="background: #f1f5f9; color: #475569;">
                                    <?= htmlspecialchars($claim['category_name'] ?? '—') ?>
                                </span>
                            </td>
                            <td class="fw-semibold">₹<?= number_format($claim['total_amount'] ?? 0, 2) ?></td>
                            <td><?= $claim['claim_date'] ? date('d M Y', strtotime($claim['claim_date'])) : '—' ?></td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?> status-badge">
                                    <?= ucfirst($claim['status']) ?>
                                </span>
                            </td>
                            <td><?= $claim['submitted_at'] ? date('d M Y H:i', strtotime($claim['submitted_at'])) : ($claim['created_at'] ? date('d M Y H:i', strtotime($claim['created_at'])) : '—') ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/employee/self-service/expenses/<?= $claim['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> View
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
</div>