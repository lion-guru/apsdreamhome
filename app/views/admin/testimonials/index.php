<?php
/**
 * Admin Testimonials Index View
 * Official members can manage and approve testimonials
 */

$page_title = $page_title ?? 'Testimonials Management';
$testimonials = $testimonials ?? [];
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'featured' => 0];
$statuses = $statuses ?? ['pending', 'approved', 'rejected'];
$current_status = $current_status ?? 'all';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-comments me-2"></i>Testimonials Management</h1>
            <p class="text-muted mb-0">Review and approve customer testimonials</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                    <small>Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $stats['pending']; ?></h3>
                    <small>Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $stats['approved']; ?></h3>
                    <small>Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $stats['rejected']; ?></h3>
                    <small>Rejected</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $stats['featured']; ?></h3>
                    <small>Featured</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="all" <?php echo $current_status === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status; ?>" <?php echo $current_status === $status ? 'selected' : ''; ?>>
                                <?php echo ucfirst($status); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Testimonials Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Customer</th>
                            <th>Rating</th>
                            <th>Title</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($testimonials)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No testimonials found.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($testimonials as $t): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($t['name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($t['location'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $t['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($t['title'] ?? 'No title'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($t['submitted_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $t['status'] === 'approved' ? 'success' : ($t['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                            <?php echo ucfirst($t['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($t['featured']): ?>
                                            <span class="badge bg-info"><i class="fas fa-star me-1"></i>Featured</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/admin/testimonials/show/<?php echo $t['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i>Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
