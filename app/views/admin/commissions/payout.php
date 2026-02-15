<?php
/**
 * Payout Processing View
 */
$page_title = 'Process Payouts';
$include_datatables = true;
include(VIEW_PATH . '/admin/admin_header.php');
include(VIEW_PATH . '/admin/admin_sidebar.php');
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Process Payouts')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo h(BASE_URL); ?>admin/mlm"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="<?php echo h(BASE_URL); ?>admin/commissions/manage"><?php echo h($mlSupport->translate('Commissions')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Process Payouts')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <?php if ($flash_success = get_flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo h($mlSupport->translate($flash_success)); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($flash_error = get_flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo h($mlSupport->translate($flash_error)); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><?php echo h($mlSupport->translate('Approved Commissions (Pending Payout)')); ?></h4>
                        <div class="text-right">
                            <form action="<?php echo h(BASE_URL); ?>admin/commissions/payout" method="POST" id="payout-form">
                                <?php echo getCsrfField(); ?>
                                <input type="hidden" name="batch_reference" value="MLM-<?php echo h(date('Ymd-His')); ?>">
                                <input type="hidden" name="min_amount" value="100"> <!-- Min payout threshold -->
                                <button type="submit" class="btn btn-success" id="btn-generate-batch" <?php echo empty($approvedCommissions) ? 'disabled' : ''; ?>>
                                    <?php echo h($mlSupport->translate('Generate Payout Batch')); ?>
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
                                                    <span class="badge badge-pill bg-success-light"><?php echo h($mlSupport->translate('Approved')); ?></span>
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
</div>
<!-- /Page Wrapper -->

<?php include(VIEW_PATH . '/admin/admin_footer.php'); ?>

<script>
$(document).ready(function() {
    $('#payout-form').submit(function() {
        return confirm('<?php echo $mlSupport->translate('Are you sure you want to generate a payout batch for all approved commissions?'); ?>');
    });
});
</script>
