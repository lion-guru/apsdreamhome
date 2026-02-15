<?php
/**
 * Commission Management View
 */
$page_title = 'Commission Management';
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
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Commission Management')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>admin/mlm"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Commissions')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><?php echo h($mlSupport->translate('Pending Commissions')); ?></h4>
                        <div class="text-right">
                            <button class="btn btn-primary" id="btn-calculate"><?php echo h($mlSupport->translate('Calculate Commissions')); ?></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Associate ID')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Associate Name')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Level')); ?></th>
                                        <th><?php echo h($mlSupport->translate('BV/Sales')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Commission Amount')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Date')); ?></th>
                                        <th class="text-right"><?php echo h($mlSupport->translate('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($pendingCommissions)): ?>
                                        <?php foreach ($pendingCommissions as $commission): ?>
                                            <tr>
                                                <td><?php echo h($commission['associate_id']); ?></td>
                                                <td><?php echo h($commission['associate_name']); ?></td>
                                                <td><?php echo h($mlSupport->translate('Level')); ?> <?php echo h($commission['level']); ?></td>
                                                <td><?php echo h(number_format($commission['bv_amount'], 2)); ?></td>
                                                <td><?php echo h(number_format($commission['commission_amount'], 2)); ?></td>
                                                <td>
                                                    <span class="badge badge-pill bg-warning-light"><?php echo h($mlSupport->translate('Pending')); ?></span>
                                                </td>
                                                <td><?php echo h(date('d M Y', strtotime($commission['created_at']))); ?></td>
                                                <td class="text-right">
                                                    <button class="btn btn-sm btn-success btn-approve" data-id="<?php echo h($commission['id']); ?>">
                                                        <i class="fe fe-check"></i> <?php echo h($mlSupport->translate('Approve')); ?>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center"><?php echo h($mlSupport->translate('No pending commissions found.')); ?></td>
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
    var csrfToken = '<?php echo csrf_token(); ?>';

    $('#btn-calculate').click(function() {
        if (confirm('<?php echo $mlSupport->translate('Are you sure you want to run commission calculation for all pending sales?'); ?>')) {
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo $mlSupport->translate('Calculating...'); ?>');

            $.post('<?php echo BASE_URL; ?>admin/commissions/calculate', { csrf_token: csrfToken }, function(response) {
                if (response.success) {
                    alert('<?php echo $mlSupport->translate('Commission calculation completed successfully!'); ?>');
                    location.reload();
                } else {
                    alert('<?php echo $mlSupport->translate('Error: '); ?>' + response.message);
                }
            }).fail(function() {
                alert('<?php echo $mlSupport->translate('An error occurred during calculation.'); ?>');
            }).always(function() {
                $('#btn-calculate').prop('disabled', false).text('<?php echo $mlSupport->translate('Calculate Commissions'); ?>');
            });
        }
    });

    $('.btn-approve').click(function() {
        var id = $(this).data('id');
        if (confirm('<?php echo $mlSupport->translate('Approve this commission for payout?'); ?>')) {
            $.post('<?php echo BASE_URL; ?>admin/commissions/approve', { id: id, csrf_token: csrfToken }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('<?php echo $mlSupport->translate('Error: '); ?>' + response.message);
                }
            });
        }
    });
});
</script>
