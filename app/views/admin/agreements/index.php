<?php
$page_title = $page_title ?? 'Agreements - APS Dream Home';
$active_page = 'agreements';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Agreement Generation</h1>
</div>

<?php if (isset($error) && $error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars(is_string($error) ? $error : ($error['message'] ?? 'Unknown error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><?= $total ?></h4>
                        <p class="mb-0">Plot Bookings</p>
                    </div>
                    <i class="fas fa-file-contract fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><?= count(array_filter($bookings, fn($b) => ($agreement_counts[$b['id']]['cnt'] ?? 0) > 0)) ?></h4>
                        <p class="mb-0">With Agreements</p>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><?= count(array_filter($bookings, fn($b) => ($agreement_counts[$b['id']]['cnt'] ?? 0) == 0 && $b['status'] == 'confirmed')) ?></h4>
                        <p class="mb-0">Pending</p>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><?= count(array_filter($bookings, fn($b) => $b['status'] == 'confirmed')) ?></h4>
                        <p class="mb-0">Confirmed</p>
                    </div>
                    <i class="fas fa-thumbs-up fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
        <input type="text" class="form-control" name="search" placeholder="Search by booking no, customer, plot..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
    </div>
    <div class="col-md-3">
        <select class="form-select" name="status">
            <option value="">All Status</option>
            <option value="pending" <?= ($filters['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="confirmed" <?= ($filters['status'] ?? '') == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="completed" <?= ($filters['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled" <?= ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
    </div>
    <div class="col-md-3 text-end">
        <a href="<?= BASE_URL ?>/admin/agreements" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
    </div>
</form>

<!-- Bookings Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Plot / Colony</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Agreements</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No plot bookings found. Bookings must have a plot assigned.</td>
                        </tr>
                    <?php else: ?>
                        <?php $i = ($current_page - 1) * 20 + 1; ?>
                        <?php foreach ($bookings as $b): ?>
                            <?php 
                            $agCount = intval($agreement_counts[$b['id']]['cnt'] ?? 0);
                            $agTypes = explode(',', $agreement_counts[$b['id']]['types'] ?? '');
                            $hasAllotment = in_array('allotment', $agTypes);
                            $hasSaleAgreement = in_array('sale_agreement', $agTypes);
                            $hasPaymentPlan = in_array('payment_plan', $agTypes);
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($b['customer_name'] ?? 'N/A') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($b['customer_email'] ?? '') ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($b['plot_number'] ?? 'N/A') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($b['colony_name'] ?? '') ?></small>
                                </td>
                                <td>Rs. <?= number_format(floatval($b['total_amount'] ?? $b['total_price'] ?? 0), 2) ?></td>
                                <td>
                                    <?php
                                    $badgeClass = match($b['status']) {
                                        'confirmed' => 'success',
                                        'completed' => 'info',
                                        'cancelled' => 'danger',
                                        default => 'warning'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($b['status']) ?></span>
                                </td>
                                <td>
                                    <?php if ($agCount > 0): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> <?= $agCount ?> generated</span>
                                        <div class="mt-1 small">
                                            <?php if ($hasAllotment): ?><span class="badge bg-secondary">Allotment</span> <?php endif; ?>
                                            <?php if ($hasSaleAgreement): ?><span class="badge bg-secondary">Sale</span> <?php endif; ?>
                                            <?php if ($hasPaymentPlan): ?><span class="badge bg-secondary">Payment</span> <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">None</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/admin/agreements/preview/<?= $b['id'] ?>/allotment" class="btn btn-outline-primary" title="Generate Allotment Letter">
                                            <i class="fas fa-file-alt"></i> Allot
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/agreements/preview/<?= $b['id'] ?>/sale_agreement" class="btn btn-outline-success" title="Generate Sale Agreement">
                                            <i class="fas fa-file-signature"></i> Sale
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/agreements/preview/<?= $b['id'] ?>/payment_plan" class="btn btn-outline-info" title="Generate Payment Plan">
                                            <i class="fas fa-credit-card"></i> Pay
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

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $current_page - 1 ?>&status=<?= urlencode($filters['status'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>">Previous</a>
        </li>
        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
            <li class="page-item <?= $p == $current_page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>&status=<?= urlencode($filters['status'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $current_page + 1 ?>&status=<?= urlencode($filters['status'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
