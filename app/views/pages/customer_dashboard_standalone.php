<?php
// Customer Dashboard - Standalone
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
$customer_name = $customer_name ?? 'Customer';
$bookings = $bookings ?? [];
$emi_schedule = $emi_schedule ?? [];
$payment_history = $payment_history ?? [];
$stats = $stats ?? ['properties'=>0,'bookings'=>0,'pending_emi'=>0,'total_investment'=>0];
$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#f1f5f9}
        .navbar{background:#fff;border-bottom:1px solid #e2e8f0}
        .stat-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;display:flex;align-items:flex-start;gap:15px}
        .stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem}
        .stat-icon.p{background:#eef2ff;color:#4f46e5}.stat-icon.s{background:#ecfdf5;color:#10b981}
        .stat-icon.w{background:#fffbeb;color:#f59e0b}.stat-icon.d{background:#fef2f2;color:#ef4444}
        .stat-label{font-size:.72rem;color:#64748b;text-transform:uppercase;font-weight:500;margin-bottom:4px}
        .stat-value{font-size:1.5rem;font-weight:700;color:#1e293b}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
        .badge-paid{background:#ecfdf5;color:#10b981}.badge-pending{background:#fffbeb;color:#f59e0b}
        .badge-overdue{background:#fef2f2;color:#ef4444}.badge-active{background:#eef2ff;color:#4f46e5}
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo $base; ?>"><i class="fas fa-home text-primary me-2"></i>APS Dream Home</a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">Welcome, <strong><?php echo htmlspecialchars($customer_name); ?></strong></span>
                <a href="<?php echo $base; ?>/logout" class="btn btn-outline-danger btn-sm"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <h4 class="mb-4 fw-bold">My Dashboard</h4>

        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-icon p"><i class="fas fa-building"></i></div><div><div class="stat-label">My Properties</div><div class="stat-value"><?php echo $stats['properties']; ?></div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-icon s"><i class="fas fa-file-contract"></i></div><div><div class="stat-label">Active Bookings</div><div class="stat-value"><?php echo $stats['bookings']; ?></div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-icon w"><i class="fas fa-calendar-alt"></i></div><div><div class="stat-label">Pending EMI</div><div class="stat-value"><?php echo $stats['pending_emi']; ?></div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-icon d"><i class="fas fa-rupee-sign"></i></div><div><div class="stat-label">Total Investment</div><div class="stat-value">&#8377;<?php echo number_format($stats['total_investment']); ?></div></div></div></div>
        </div>

        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><a href="<?php echo $base; ?>/properties" class="btn btn-outline-primary w-100 py-2"><i class="fas fa-search me-2"></i>Browse Properties</a></div>
                    <div class="col-md-3"><a href="<?php echo $base; ?>/contact" class="btn btn-outline-success w-100 py-2"><i class="fas fa-phone me-2"></i>Contact Support</a></div>
                    <div class="col-md-3"><a href="<?php echo $base; ?>/user/edit-profile" class="btn btn-outline-info w-100 py-2"><i class="fas fa-user-edit me-2"></i>Edit Profile</a></div>
                    <div class="col-md-3"><a href="<?php echo $base; ?>/resell" class="btn btn-outline-warning w-100 py-2"><i class="fas fa-exchange-alt me-2"></i>Resell Property</a></div>
                </div>
            </div>
        </div>

        <!-- My Properties / Bookings -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-building me-2"></i>My Properties</h6>
                <?php if(!empty($bookings)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Property</th><th>Location</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                        <?php foreach($bookings as $b): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($b['property_name']??'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($b['location']??'N/A'); ?></td>
                            <td>&#8377;<?php echo number_format($b['amount']??0); ?></td>
                            <td><span class="badge badge-<?php echo ($b['status']??'')==='active'?'active':(($b['status']??'')==='pending'?'pending':'paid'); ?>"><?php echo ucfirst($b['status']??'unknown'); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($b['created_at']??'now')); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-3">No properties yet. <a href="<?php echo $base; ?>/properties">Browse properties</a></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- EMI Schedule -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-calendar-alt me-2"></i>EMI Schedule</h6>
                <?php if(!empty($emi_schedule)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>EMI #</th><th>Due Date</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach($emi_schedule as $emi): ?>
                        <tr>
                            <td><?php echo $emi['emi_number']??'#'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($emi['due_date']??'now')); ?></td>
                            <td>&#8377;<?php echo number_format($emi['amount']??0); ?></td>
                            <td>
                                <?php $s=$emi['status']??'pending'; ?>
                                <span class="badge badge-<?php echo $s==='paid'?'paid':($s==='overdue'?'overdue':'pending'); ?>">
                                    <i class="fas fa-<?php echo $s==='paid'?'check-circle':($s==='overdue'?'exclamation-circle':'clock'); ?> me-1"></i>
                                    <?php echo ucfirst($s); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-3">No EMI schedule yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment History -->
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-history me-2"></i>Payment History</h6>
                <?php if(!empty($payment_history)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Date</th><th>Description</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach($payment_history as $p): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($p['created_at']??'now')); ?></td>
                            <td><?php echo htmlspecialchars($p['description']??'Payment'); ?></td>
                            <td>&#8377;<?php echo number_format($p['amount']??0); ?></td>
                            <td><span class="badge badge-<?php echo ($p['status']??'')==='completed'?'paid':'pending'; ?>"><?php echo ucfirst($p['status']??'pending'); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-3">No payment history yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
