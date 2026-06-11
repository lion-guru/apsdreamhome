<?php
$payouts = $payouts ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$per_page = $per_page ?? 20;
$total_pages = $total_pages ?? 1;
$page_title = $page_title ?? 'Payouts';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-money-bill-wave me-2 text-success"></i>Payouts</h2>
                <p class="text-muted mb-0">Manage network commissions payouts</p>
            </div>
            <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">Back</a>
        </div>
        
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body aps-cp-card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Pending Payouts</p>
                                <h4 class="mb-0">₹<?php echo number_format(floatval($stats['pending'] ?? 0) ?? 0); ?></h4>
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded p-2">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body aps-cp-card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Processed Today</p>
                                <h4 class="mb-0">₹<?php echo number_format(floatval($stats['processed_today'] ?? 0) ?? 0); ?></h4>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded p-2">
                                <i class="fas fa-check text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body aps-cp-card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Paid (Month)</p>
                                <h4 class="mb-0">₹<?php echo number_format(floatval($stats['month_total'] ?? 0) ?? 0); ?></h4>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded p-2">
                                <i class="fas fa-calendar text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payouts Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Payout Requests</h5>
                <button class="btn btn-sm btn-success" onclick="showToast('Process payouts coming soon', 'info')">
                    <i class="fas fa-sync me-2"></i>Process Batch
                </button>
            </div>
            <div class="card-body aps-cp-card-body">
                <?php if (!empty($payouts)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Payout ID</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Request Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payouts as $payout): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payout['payout_id'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($payout['user_name'] ?? '-'); ?></td>
                                        <td>₹<?php echo number_format(floatval($payout['amount'] ?? 0) ?? 0); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($payout['status'] ?? '') === 'completed' ? 'success' : (($payout['status'] ?? '') === 'pending' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($payout['status'] ?? 'unknown'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo isset($payout['created_at']) ? date('M d, Y', strtotime($payout['created_at'])) : '-'; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="alert('View details')">View</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No payout requests found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    

