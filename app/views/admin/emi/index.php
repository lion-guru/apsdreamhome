<?php
/**
 * EMI Plans Index View
 */
$emi_plans = $emi_plans ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$per_page = $per_page ?? 20;
$total_pages = $total_pages ?? 1;
$filters = $filters ?? [];
$page_title = $page_title ?? 'EMI Plans';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">EMI Plans</h2>
                <p class="text-muted mb-0">Manage EMI plans and payment schedules</p>
            </div>
            <div>
                <a href="<?php echo $base; ?>/admin/emi/create" class="btn btn-primary me-2">
                    <i class="fas fa-plus me-2"></i>Create EMI Plan
                </a>
                <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
        
        <!-- Search & Filter -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by booking or customer..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="completed" <?php echo ($filters['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="defaulted" <?php echo ($filters['status'] ?? '') === 'defaulted' ? 'selected' : ''; ?>>Defaulted</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- EMI Plans Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php if (!empty($emi_plans)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Booking</th>
                                    <th>Customer</th>
                                    <th>Property</th>
                                    <th>EMI Amount</th>
                                    <th>Tenure</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($emi_plans as $plan): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($plan['booking_number'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($plan['customer_name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($plan['property_title'] ?? '-'); ?></td>
                                        <td>₹<?php echo number_format($plan['emi_amount'] ?? 0); ?>/month</td>
                                        <td><?php echo $plan['tenure_months'] ?? 0; ?> months</td>
                                        <td>
                                            <span class="badge bg-<?php echo ($plan['status'] ?? '') === 'active' ? 'success' : (($plan['status'] ?? '') === 'completed' ? 'info' : 'danger'); ?>">
                                                <?php echo ucfirst($plan['status'] ?? 'unknown'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo $base; ?>/admin/emi/show/<?php echo $plan['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($filters['search'] ?? ''); ?>&status=<?php echo urlencode($filters['status'] ?? ''); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No EMI plans found</p>
                        <a href="<?php echo $base; ?>/admin/emi/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create First EMI Plan
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
