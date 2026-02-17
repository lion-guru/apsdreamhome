<?php
/**
 * Payout Processing View
 */
?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Process Payouts')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/commissions"><?php echo h($mlSupport->translate('Commissions')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Process Payouts')); ?></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <?php if ($flash_success = get_flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <?php echo h($mlSupport->translate($flash_success)); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flash_error = get_flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <?php echo h($mlSupport->translate($flash_error)); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><?php echo h($mlSupport->translate('Approved Commissions (Pending Payout)')); ?></h4>
                    <div class="text-end">
                        <form action="<?php echo h(BASE_URL); ?>admin/commissions/processPayout" method="POST" id="payout-form">
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <input type="hidden" name="batch_reference" value="MLM-<?php echo h(date('Ymd-His')); ?>">
                            <input type="hidden" name="min_amount" value="100"> <!-- Min payout threshold -->
                            <button type="submit" class="btn btn-success" id="btn-generate-batch" <?php echo empty($approvedCommissions) ? 'disabled' : ''; ?>>
                                <i class="fas fa-money-bill-wave me-2"></i><?php echo h($mlSupport->translate('Generate Payout Batch')); ?>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-center mb-0 datatable">
                            <thead>
                                <tr>
                                    <th><?php echo h($mlSupport->translate('Associate ID')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Associate Name')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Transaction ID')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Amount')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Approved Date')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($approvedCommissions)): ?>
                                    <?php foreach ($approvedCommissions as $payout): ?>
                                        <tr>
                                            <td><?php echo h($payout['beneficiary_user_id']); ?></td>
                                            <td><?php echo h($payout['associate_name']); ?></td>
                                            <td><?php echo h($payout['transaction_reference'] ?? $mlSupport->translate('N/A')); ?></td>
                                            <td><?php echo h(number_format($payout['amount'], 2)); ?></td>
                                            <td><?php echo h(date('d M Y', strtotime($payout['updated_at']))); ?></td>
                                            <td>
                                                <span class="badge bg-success-light text-success"><?php echo h($mlSupport->translate('Approved')); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center"><?php echo h($mlSupport->translate('No approved commissions waiting for payout.')); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#payout-form').submit(function() {
        return confirm('<?php echo $mlSupport->translate('Are you sure you want to generate a payout batch for all approved commissions?'); ?>');
    });
});
</script>
