<?php
$page_title = $page_title ?? 'Collection Detail - APS Dream Home';
$collection = $collection ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$c = $collection;
?>
<div class="container-fluid px-4">
    <div class="mb-4">
        <a href="<?php echo $base; ?>/associate/collections" class="text-decoration-none text-success small">
            <i class="fas fa-arrow-left me-1"></i>Back to Collections
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Status Banner -->
            <?php
            $s = $c['status'] ?? 'submitted';
            $b = $s === 'verified' ? 'success' : ($s === 'rejected' ? 'danger' : 'warning');
            $icon = $s === 'verified' ? 'check-circle' : ($s === 'rejected' ? 'times-circle' : 'hourglass-half');
            ?>
            <div class="alert alert-<?php echo $b; ?> d-flex align-items-center gap-2">
                <i class="fas fa-<?php echo $icon; ?> fa-lg"></i>
                <strong><?php echo ucfirst($s); ?></strong> &mdash;
                <?php if ($s === 'submitted'): ?>
                    This collection is pending admin verification.
                <?php elseif ($s === 'verified'): ?>
                    This collection has been verified and reconciled.
                <?php elseif ($s === 'rejected'): ?>
                    This collection was rejected: <?php echo htmlspecialchars($c['rejection_reason'] ?? 'No reason provided'); ?>
                <?php endif; ?>
            </div>

            <!-- Detail Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Collection #<?php echo (int)($c['id'] ?? 0); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong class="text-muted small">Customer</strong>
                            <p class="mb-0"><?php echo htmlspecialchars($c['customer_name'] ?? '-'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted small">Amount</strong>
                            <p class="mb-0 h5 text-success">₹<?php echo number_format((float)($c['amount'] ?? 0)); ?></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong class="text-muted small">Collection Date</strong>
                            <p class="mb-0"><?php echo htmlspecialchars($c['collection_date'] ?? '-'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted small">Payment Method</strong>
                            <p class="mb-0"><?php echo ucfirst(str_replace('_', ' ', $c['payment_method'] ?? 'cash')); ?></p>
                        </div>
                    </div>
                    <?php if (!empty($c['reference_number'])): ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong class="text-muted small">Reference Number</strong>
                            <p class="mb-0"><?php echo htmlspecialchars($c['reference_number']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($c['notes'])): ?>
                    <div class="mb-3">
                        <strong class="text-muted small">Notes</strong>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($c['notes'])); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($c['verified_at'])): ?>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong class="text-muted small">Verified At</strong>
                            <p class="mb-0"><?php echo htmlspecialchars($c['verified_at']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted small">Verified By</strong>
                            <p class="mb-0"><?php echo htmlspecialchars($c['verified_by_name'] ?? 'Admin #' . ($c['verified_by'] ?? '')); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Receipt Photo -->
            <?php if (!empty($c['receipt_photo'])): ?>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-image text-info me-2"></i>Receipt Photo</h5>
                </div>
                <div class="card-body text-center">
                    <img src="<?php echo $base . '/' . ltrim($c['receipt_photo'], '/'); ?>" alt="Receipt" class="img-fluid rounded" style="max-height:400px;">
                </div>
            </div>
            <?php endif; ?>

            <!-- Back Button -->
            <div class="mt-4 text-center">
                <a href="<?php echo $base; ?>/associate/collections" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Collections
                </a>
            </div>
        </div>
    </div>
</div>
