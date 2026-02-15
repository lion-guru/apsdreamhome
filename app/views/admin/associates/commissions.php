<?php
if (!isAdmin()) {
    header("Location: /admin/login");
    exit();
}
$title = $title ?? $mlSupport->translate("Commission Report");
require_once ABSPATH . '/resources/views/admin/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Commission Report')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="/admin/associates"><?php echo h($mlSupport->translate('Associates')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Commissions')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <!-- Summary Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h4><?php echo h($associate['user_name'] ?? $mlSupport->translate('Associate')); ?> (<?php echo h($associate['associate_code'] ?? ''); ?>)</h4>
                                <p class="text-muted mb-0"><?php echo h($mlSupport->translate('Total Earnings')); ?>: <span class="fw-bold text-success">₹<?php echo h(number_format((float)($summary['total_commissions'] ?? 0), 2)); ?></span></p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="btn-group">
                                    <span class="badge bg-info me-2 p-2"><?php echo h($mlSupport->translate('Level')); ?> 1: ₹<?php echo h(number_format((float)($summary['level_1_earnings'] ?? 0), 2)); ?></span>
                                    <span class="badge bg-primary me-2 p-2"><?php echo h($mlSupport->translate('Level')); ?> 2: ₹<?php echo h(number_format((float)($summary['level_2_earnings'] ?? 0), 2)); ?></span>
                                    <span class="badge bg-secondary p-2"><?php echo h($mlSupport->translate('Level')); ?> 3: ₹<?php echo h(number_format((float)($summary['level_3_earnings'] ?? 0), 2)); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commissions Table -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h4 class="card-title mb-0"><?php echo h($mlSupport->translate('Commission Ledger')); ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Date')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Transaction ID')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Property/Plot')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Customer')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Level')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Amount')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($commissions)): ?>
                                        <?php foreach ($commissions as $row): ?>
                                            <tr>
                                                <td><?php echo h(date('d M Y', strtotime($row['created_at']))); ?></td>
                                                <td>#<?php echo h($row['id'] ?? ''); ?></td>
                                                <td><?php echo h($row['property_title'] ?: $mlSupport->translate('N/A')); ?></td>
                                                <td><?php echo h($row['customer_name'] ?: $mlSupport->translate('N/A')); ?></td>
                                                <td><span class="badge bg-info"><?php echo h($mlSupport->translate('Level')); ?> <?php echo h($row['level'] ?? ''); ?></span></td>
                                                <td>₹<?php echo h(number_format((float)($row['commission_amount'] ?? 0), 2)); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($row['status'] ?? '') == 'paid' ? 'success' : 'warning'; ?>">
                                                        <?php echo h($mlSupport->translate(ucfirst($row['status'] ?? 'pending'))); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4"><?php echo h($mlSupport->translate('No commission records found.')); ?></td>
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

<?php require_once ABSPATH . '/resources/views/admin/layouts/footer.php'; ?>
