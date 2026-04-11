<?php
$page_title = $page_title ?? 'Customers';
$customers = $customers ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$per_page = $per_page ?? 20;
$total_pages = $total_pages ?? 1;
$filters = $filters ?? ['search' => '', 'status' => ''];
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Customers</h1>
            <p class="text-muted mb-0">Manage customer accounts and bookings</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h2 mb-0 text-primary"><?= $total ?></div>
                    <small class="text-muted">Total Customers</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h2 mb-0 text-success"><?= count(array_filter($customers, fn($c) => ($c['status'] ?? '') === 'active')) ?></div>
                    <small class="text-muted">Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h2 mb-0 text-warning"><?= count(array_filter($customers, fn($c) => ($c['status'] ?? '') === 'inactive')) ?></div>
                    <small class="text-muted">Inactive</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h2 mb-0 text-info"><?= array_sum(array_column($customers, 'booking_count')) ?></div>
                    <small class="text-muted">Total Bookings</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/customers" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                </div>
                <div class="col-md-3">
                    <a href="<?= BASE_URL ?>/admin/customers" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Customer</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Bookings</th>
                            <th>Joined</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($customers)): ?>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?= htmlspecialchars($customer['name'] ?? 'N/A') ?></div>
                                                <small class="text-muted">ID: #<?= $customer['id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($customer['email'] ?? 'N/A') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($customer['phone'] ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($customer['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($customer['status'] ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= $customer['booking_count'] ?? 0 ?></span>
                                    </td>
                                    <td>
                                        <?= isset($customer['created_at']) ? date('d M Y', strtotime($customer['created_at'])) : 'N/A' ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="<?= BASE_URL ?>/admin/customers/<?= $customer['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <p class="mb-0">No customers found</p>
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
                <nav aria-label="Customer pagination">
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/admin/customers?page=<?= $i ?>&search=<?= urlencode($filters['search']) ?>&status=<?= urlencode($filters['status']) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
