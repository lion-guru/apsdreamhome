<?php
/**
 * Perfect Admin - Booking Management Content
 * This file is included by admin.php and enhanced_admin_system.php
 */

if (!isset($adminService)) {
    $adminService = new PerfectAdminService();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filters = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? ''
];

$bookingData = $adminService->getBookingList($filters, $page);
$bookings = $bookingData['bookings'];
$totalPages = $bookingData['total_pages'];
$totalCount = $bookingData['total_count'];
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">Booking Management</h5>
        <a href="/admin/bookings/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-2"></i>New Booking
        </a>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="row g-3 mb-4">
            <input type="hidden" name="action" value="bookings">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search customer or property..." value="<?php echo h($filters['search']); ?>">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $filters['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $filters['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="completed" <?php echo $filters['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $filters['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Bookings Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Property</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No bookings found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><span class="fw-bold text-dark">#BK-<?php echo h($booking['id']); ?></span></td>
                                <td><?php echo h($booking['customer_name']); ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?php echo h($booking['property_title']); ?>">
                                        <?php echo h($booking['property_title']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold">₹<?php echo number_format($booking['property_price'], 2); ?></div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'secondary';
                                    switch($booking['status']) {
                                        case 'pending': $statusClass = 'warning'; break;
                                        case 'confirmed': $statusClass = 'info'; break;
                                        case 'completed': $statusClass = 'success'; break;
                                        case 'cancelled': $statusClass = 'danger'; break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo h($statusClass); ?>-subtle text-<?php echo h($statusClass); ?> border border-<?php echo h($statusClass); ?>-subtle">
                                        <?php echo h(ucfirst($booking['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo h(date('d M Y', strtotime($booking['created_at']))); ?></td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="booking_view.php?id=<?php echo h($booking['id']); ?>" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-success" title="Approve" onclick="confirmAction('Approve this booking?', () => { window.location.href = 'booking_action.php?action=approve&id=<?php echo h($booking['id']); ?>'; })">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Cancel" onclick="confirmAction('Cancel this booking?', () => { window.location.href = 'booking_action.php?action=cancel&id=<?php echo h($booking['id']); ?>'; })">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?action=bookings&page=<?php echo h($page - 1); ?>&search=<?php echo h($filters['search']); ?>&status=<?php echo h($filters['status']); ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?action=bookings&page=<?php echo h($i); ?>&search=<?php echo h($filters['search']); ?>&status=<?php echo h($filters['status']); ?>"><?php echo h($i); ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?action=bookings&page=<?php echo h($page + 1); ?>&search=<?php echo h($filters['search']); ?>&status=<?php echo h($filters['status']); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
