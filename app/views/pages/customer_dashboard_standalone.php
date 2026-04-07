<?php
/**
 * Customer Dashboard Standalone View
 * Shows plots, bookings, EMI, payment history
 */

// Ensure data variables are set
$customer_name = $customer_name ?? 'Customer';
$bookings = $bookings ?? [];
$emi_schedule = $emi_schedule ?? [];
$payment_history = $payment_history ?? [];
$stats = $stats ?? ['properties' => 0, 'bookings' => 0, 'pending_emi' => 0, 'total_investment' => 0];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'My Dashboard'); ?> - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; }
        .dashboard-card { border: none; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .table-container { background: white; border-radius: 12px; padding: 20px; }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-home me-2 text-primary"></i>My Dashboard</h2>
            <div>
                <span class="text-muted">Welcome, </span>
                <strong><?php echo htmlspecialchars($customer_name); ?></strong>
                <a href="<?php echo $base; ?>/logout" class="btn btn-sm btn-outline-danger ms-3">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card stat-card">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?php echo $stats['properties'] ?? 0; ?></h3>
                        <small>My Properties</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?php echo $stats['bookings'] ?? 0; ?></h3>
                        <small>Active Bookings</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?php echo $stats['pending_emi'] ?? 0; ?></h3>
                        <small>Pending EMI</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">₹<?php echo number_format($stats['total_investment'] ?? 0); ?></h3>
                        <small>Total Investment</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Bookings -->
            <div class="col-md-6 mb-4">
                <div class="table-container">
                    <h5 class="mb-3"><i class="fas fa-file-contract me-2 text-primary"></i>My Bookings</h5>
                    <?php if (!empty($bookings)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Property</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($bookings, 0, 5) as $booking): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['property_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($booking['status'] ?? '') === 'active' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($booking['status'] ?? 'pending'); ?>
                                                </span>
                                            </td>
                                            <td>₹<?php echo number_format($booking['amount'] ?? 0); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No bookings found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- EMI Schedule -->
            <div class="col-md-6 mb-4">
                <div class="table-container">
                    <h5 class="mb-3"><i class="fas fa-calendar-alt me-2 text-primary"></i>EMI Schedule</h5>
                    <?php if (!empty($emi_schedule)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($emi_schedule, 0, 5) as $emi): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($emi['due_date'] ?? 'now')); ?></td>
                                            <td>₹<?php echo number_format($emi['amount'] ?? 0); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($emi['status'] ?? '') === 'paid' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($emi['status'] ?? 'pending'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No EMI schedule found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="table-container">
            <h5 class="mb-3"><i class="fas fa-credit-card me-2 text-primary"></i>Payment History</h5>
            <?php if (!empty($payment_history)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payment_history as $payment): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($payment['created_at'] ?? 'now')); ?></td>
                                    <td>₹<?php echo number_format($payment['amount'] ?? 0); ?></td>
                                    <td><?php echo htmlspecialchars($payment['payment_method'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($payment['status'] ?? '') === 'completed' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($payment['status'] ?? 'pending'); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No payment history found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
