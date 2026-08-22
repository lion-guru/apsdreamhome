<?php
$page_title = 'My Land Holdings - APS Dream Home';
$current_page = 'farmer-land-holdings';
$extraHead = '<style>.badge-status { font-size:0.8rem; padding:0.35rem 0.75rem; }</style>';
$land_holdings = $land_holdings ?? [];
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-map-marked-alt text-success me-2"></i>My Land Holdings</h4>
        <a href="<?php echo BASE_URL; ?>/farmer/dashboard" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($land_holdings)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-map fa-4x mb-3"></i>
                <p>No land holdings found.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Plot #</th>
                            <th>Area (sq.ft)</th>
                            <th>Village</th>
                            <th>District</th>
                            <th>Acquisition Status</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($land_holdings as $lh): ?>
                        <?php
                        $acqStatus = $lh['acquisition_status'] ?? 'pending';
                        $payStatus = $lh['payment_status'] ?? 'pending';
                        $acqBadge = match($acqStatus) {
                            'acquired' => 'success',
                            'negotiation' => 'warning',
                            'rejected' => 'danger',
                            default => 'secondary'
                        };
                        $payBadge = match($payStatus) {
                            'paid', 'completed' => 'success',
                            'partial' => 'warning',
                            'pending' => 'secondary',
                            'overdue' => 'danger',
                            default => 'secondary'
                        };
                        ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars($lh['khasra_number'] ?? 'N/A'); ?></strong></td>
                            <td><?php echo number_format($lh['area'] ?? 0, 2); ?></td>
                            <td><?php echo htmlspecialchars($lh['village'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($lh['district'] ?? 'N/A'); ?></td>
                            <td><span class="badge bg-<?php echo e($acqBadge); ?> badge-status"><?php echo ucfirst($acqStatus); ?></span></td>
                            <td>₹<?php echo number_format($lh['acquisition_amount'] ?? 0); ?></td>
                            <td><span class="badge bg-<?php echo e($payBadge); ?> badge-status"><?php echo ucfirst($payStatus); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
