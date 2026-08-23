<?php
$page_title = $page_title ?? 'Generate Agreement - APS Dream Home';
$active_page = 'agreements';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Generate Agreement</h1>
    <a href="<?= BASE_URL ?>/admin/agreements" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card aps-cp-card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Booking Details</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="table-responsive"><table class="table table-sm table-borderless">
                    <tr>
                        <th class="text-muted">Customer:</th>
                        <td><?= htmlspecialchars($booking['customer_name'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Email:</th>
                        <td><?= htmlspecialchars($booking['customer_email'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Phone:</th>
                        <td><?= htmlspecialchars($booking['customer_phone'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Plot No:</th>
                        <td><?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Colony:</th>
                        <td><?= htmlspecialchars($booking['colony_name'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Block:</th>
                        <td><?= htmlspecialchars($booking['block'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Area:</th>
                        <td><?= number_format(floatval($booking['area_sqft'] ?? 0), 2) ?> sq.ft.</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Amount:</th>
                        <td><strong>Rs. <?= number_format(floatval($booking['total_amount'] ?? $booking['total_price'] ?? 0), 2) ?></strong></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Status:</th>
                        <td><span class="badge bg-<?= match($booking['status'] ?? '') { 'confirmed' => 'success', 'completed' => 'info', 'cancelled' => 'danger', default => 'warning' } ?>"><?= ucfirst($booking['status'] ?? 'N/A') ?></span></td>
                    </tr>
                </table></div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-cog"></i> Actions</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/admin/agreements/generate/<?= $booking['id'] ?>/allotment" class="btn btn-primary" data-aps-confirm="Generate Allotment Letter?">
                        <i class="fas fa-file-alt"></i> Generate Allotment Letter
                    </a>
                    <a href="<?= BASE_URL ?>/admin/agreements/generate/<?= $booking['id'] ?>/sale_agreement" class="btn btn-success" data-aps-confirm="Generate Sale Agreement?">
                        <i class="fas fa-file-signature"></i> Generate Sale Agreement
                    </a>
                    <a href="<?= BASE_URL ?>/admin/agreements/generate/<?= $booking['id'] ?>/payment_plan" class="btn btn-info text-white" data-aps-confirm="Generate Payment Plan?">
                        <i class="fas fa-credit-card"></i> Generate Payment Plan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card aps-cp-card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-eye"></i> Preview: 
                    <?= ucwords(str_replace('_', ' ', $agreement_type ?? '')) ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="preview-container" class="style-60710">
                    <?= $preview_html ?? '<p class="text-muted p-3">Preview not available.</p>' ?>
                </div>
            </div>
        </div>
    </div>
</div>
