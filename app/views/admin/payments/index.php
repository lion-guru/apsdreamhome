<?php
/**
 * Payments Index View
 */
$payments = $payments ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$per_page = $per_page ?? 20;
$total_pages = $total_pages ?? 1;
$filters = $filters ?? [];
$page_title = $page_title ?? 'Payments';
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Payments</h2>
                <p class="text-muted mb-0">Manage booking payments</p>
            </div>
            <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">Back</a>
        </div>
        
        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="completed" <?php echo ($filters['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="pending" <?php echo ($filters['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="refunded" <?php echo ($filters['status'] ?? '') === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Payments Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php if (!empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Booking</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['transaction_id'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($payment['booking_number'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($payment['customer_name'] ?? '-'); ?></td>
                                        <td>₹<?php echo number_format(floatval(payment['amount'] ?? 0) ?? 0); ?></td>
                                        <td><?php echo ucfirst($payment['payment_method'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($payment['status'] ?? '') === 'completed' ? 'success' : (($payment['status'] ?? '') === 'pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($payment['status'] ?? 'unknown'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo isset($payment['payment_date']) ? date('M d, Y', strtotime($payment['payment_date'])) : '-'; ?></td>
                                        <td>
                                            <a href="<?php echo $base; ?>/admin/payments/show/<?php echo $payment['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No payments found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
