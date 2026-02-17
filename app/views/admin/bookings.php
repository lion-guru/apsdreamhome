<?php

/**
 * Admin Bookings Management View
 * Handles booking administration interface
 */

if (!isset($title)) {
    $title = 'Booking Management';
}

if (!isset($bookings)) {
    $bookings = [];
}

if (!isset($filters)) {
    $filters = [];
}

if (!isset($bookingStats)) {
    $bookingStats = [];
}
?>

<!-- Admin Header -->
<section class="admin-header py-4 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>
                    Booking Management
                </h1>
                <p class="mb-0 opacity-75">Manage property bookings and reservations</p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <a href="<?php echo BASE_URL; ?>admin/bookings/create" class="btn btn-light btn-lg">
                    <i class="fas fa-plus me-2"></i>Create Booking
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Bookings Management -->
<section class="bookings-management py-5">
    <div class="container">
        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="filter-card">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label fw-medium">
                                <i class="fas fa-search text-primary me-2"></i>
                                Search Bookings
                            </label>
                            <input type="text"
                                class="form-control"
                                id="search"
                                name="search"
                                placeholder="Search by booking # or customer name"
                                value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="status" class="form-label fw-medium">
                                <i class="fas fa-toggle-on text-primary me-2"></i>
                                Status
                            </label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo ($filters['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo ($filters['status'] ?? '') === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo ($filters['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="completed" <?php echo ($filters['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-medium">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-card">
                    <!-- Table Header -->
                    <div class="table-header d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-list text-primary me-2"></i>
                                Bookings (<?php echo number_format($total_bookings ?? 0); ?>)
                            </h5>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center">
                                <label for="per_page" class="form-label mb-0 me-2">Show:</label>
                                <select class="form-select form-select-sm" id="per_page" name="per_page"
                                    onchange="window.location.href = this.value">
                                    <option value="<?php echo BASE_URL; ?>admin/bookings?<?php echo http_build_query(array_merge($_GET, ['per_page' => 10])); ?>" <?php echo ($filters['per_page'] ?? 10) == 10 ? 'selected' : ''; ?>>10</option>
                                    <option value="<?php echo BASE_URL; ?>admin/bookings?<?php echo http_build_query(array_merge($_GET, ['per_page' => 25])); ?>" <?php echo ($filters['per_page'] ?? 10) == 25 ? 'selected' : ''; ?>>25</option>
                                    <option value="<?php echo BASE_URL; ?>admin/bookings?<?php echo http_build_query(array_merge($_GET, ['per_page' => 50])); ?>" <?php echo ($filters['per_page'] ?? 10) == 50 ? 'selected' : ''; ?>>50</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Table Responsive -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Booking #</th>
                                    <th>Customer</th>
                                    <th>Property</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($bookings)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="no-data">
                                                <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No bookings found</h5>
                                                <p class="text-muted">Try adjusting your search or filter criteria</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-primary"><?php echo htmlspecialchars($booking['booking_number'] ?? 'N/A'); ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-medium"><?php echo htmlspecialchars($booking['customer_name']); ?></div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($booking['property_title']); ?>">
                                                    <?php echo htmlspecialchars($booking['property_title']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold">₹<?php echo number_format($booking['total_amount'] ?? 0); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php
                                                                        echo match ($booking['status']) {
                                                                            'confirmed' => 'success',
                                                                            'pending' => 'warning',
                                                                            'cancelled' => 'danger',
                                                                            'completed' => 'info',
                                                                            default => 'secondary'
                                                                        };
                                                                        ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo BASE_URL; ?>admin/bookings/view/<?php echo $booking['id']; ?>"
                                                        class="btn btn-sm btn-outline-info"
                                                        title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>admin/bookings/edit/<?php echo $booking['id']; ?>"
                                                        class="btn btn-sm btn-outline-primary"
                                                        title="Edit Booking">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-wrapper mt-4">
                            <nav aria-label="Bookings pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($filters['page'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                                href="<?php echo BASE_URL; ?>admin/bookings?<?php echo http_build_query(array_merge($_GET, ['page' => $filters['page'] - 1])); ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $start_page = max(1, $filters['page'] - 2);
                                    $end_page = min($total_pages, $filters['page'] + 2);

                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <li class="page-item <?php echo $i === $filters['page'] ? 'active' : ''; ?>">
                                            <a class="page-link"
                                                href="<?php echo BASE_URL; ?>admin/bookings?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($filters['page'] < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                                href="<?php echo BASE_URL; ?>admin/bookings?<?php echo http_build_query(array_merge($_GET, ['page' => $filters['page'] + 1])); ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .filter-card,
    .table-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        padding: 1.5rem;
    }

    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
</style>
