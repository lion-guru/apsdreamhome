<?php
$sales = $sales ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$per_page = $per_page ?? 20;
$total_pages = $total_pages ?? 1;
$filters = $filters ?? ['search' => '', 'status' => '', 'associate_id' => ''];
$associates = $associates ?? [];
$page_title = $page_title ?? 'Sales Management';
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Sales</h1>
            <p class="text-muted mb-0">Manage sales and transactions</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h2 mb-0 text-primary"><?= $total ?></div>
                    <small class="text-muted">Total Sales</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h2 mb-0 text-success"><?= count(array_filter($sales, fn($s) => ($s['status'] ?? '') === 'completed')) ?></div>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h2 mb-0 text-warning"><?= count(array_filter($sales, fn($s) => ($s['status'] ?? '') === 'pending')) ?></div>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h2 mb-0 text-info">₹<?= number_format(array_sum(array_column($sales, 'sale_value')), 0) ?></div>
                    <small class="text-muted">Total Value</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/sales" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search sale #, property, customer..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="associate_id" class="form-select">
                        <option value="">All Associates</option>
                        <?php foreach ($associates as $associate): ?>
                            <option value="<?= $associate['id'] ?>" <?= $filters['associate_id'] == $associate['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($associate['name'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="<?= BASE_URL ?>/admin/sales" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Sale #</th>
                            <th>Property</th>
                            <th>Customer</th>
                            <th>Associate</th>
                            <th>Value</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sales)): ?>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-semibold"><?= htmlspecialchars($sale['sale_number'] ?? 'N/A') ?></span>
                                        <?php if (!empty($sale['booking_number'])): ?>
                                            <br><small class="text-muted">Booking: <?= htmlspecialchars($sale['booking_number']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($sale['property_title'] ?? 'N/A') ?>
                                        <br><small class="text-muted">₹<?= number_format($sale['property_price'] ?? 0, 0) ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($sale['customer_name'] ?? 'N/A') ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($sale['customer_email'] ?? '') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($sale['associate_name'] ?? 'Unassigned') ?></td>
                                    <td>
                                        <span class="fw-semibold">₹<?= number_format($sale['sale_value'] ?? 0, 0) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($sale['status'] ?? '') === 'completed' ? 'success' : (($sale['status'] ?? '') === 'pending' ? 'warning' : 'danger') ?>">
                                            <?= ucfirst($sale['status'] ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td><?= isset($sale['created_at']) ? date('d M Y', strtotime($sale['created_at'])) : 'N/A' ?></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-handshake fa-2x mb-2"></i>
                                    <p class="mb-0">No sales found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white">
                <nav aria-label="Sales pagination">
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/admin/sales?page=<?= $i ?>&search=<?= urlencode($filters['search']) ?>&status=<?= urlencode($filters['status']) ?>&associate_id=<?= urlencode($filters['associate_id']) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
