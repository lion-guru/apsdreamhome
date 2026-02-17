<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Land Transactions')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/land"><?php echo h($mlSupport->translate('Land Records')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Transactions')); ?></li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="/admin/land/transactions/add/<?php echo h($land_record['id']); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> <?php echo h($mlSupport->translate('Add Transaction')); ?>
                </a>
            </div>
        </div>
    </div>

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

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0"><?php echo h($mlSupport->translate('Land Record Details')); ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong><?php echo h($mlSupport->translate('Farmer Name')); ?>:</strong>
                    <p class="text-muted mb-0"><?php echo h($land_record['farmer_name'] ?? 'N/A'); ?></p>
                </div>
                <div class="col-md-4 mb-3">
                    <strong><?php echo h($mlSupport->translate('Site Name')); ?>:</strong>
                    <p class="text-muted mb-0"><?php echo h($land_record['site_name'] ?? 'N/A'); ?></p>
                </div>
                <div class="col-md-4 mb-3">
                    <strong><?php echo h($mlSupport->translate('Gata Number')); ?>:</strong>
                    <p class="text-muted mb-0"><?php echo h($land_record['gata_number'] ?? 'N/A'); ?></p>
                </div>
                <div class="col-md-4 mb-3">
                    <strong><?php echo h($mlSupport->translate('Total Land Price')); ?>:</strong>
                    <p class="text-muted mb-0"><?php echo h(number_format((float)($land_record['total_land_price'] ?? 0), 2)); ?></p>
                </div>
                <div class="col-md-4 mb-3">
                    <strong><?php echo h($mlSupport->translate('Total Paid')); ?>:</strong>
                    <p class="text-success mb-0"><?php echo h(number_format((float)($land_record['total_paid_amount'] ?? 0), 2)); ?></p>
                </div>
                <div class="col-md-4 mb-3">
                    <strong><?php echo h($mlSupport->translate('Pending Amount')); ?>:</strong>
                    <p class="text-danger mb-0"><?php echo h(number_format((float)($land_record['amount_pending'] ?? 0), 2)); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0"><?php echo h($mlSupport->translate('Transaction History')); ?></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th><?php echo h($mlSupport->translate('Date')); ?></th>
                            <th><?php echo h($mlSupport->translate('Description')); ?></th>
                            <th><?php echo h($mlSupport->translate('Amount')); ?> (<?php echo h($currency_symbol ?? '₹'); ?>)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted"><?php echo h($mlSupport->translate('No transactions found')); ?></td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $cnt = 1;
                            foreach ($transactions as $row):
                                $amount = number_format((float)($row['amount'] ?? 0), 2);
                                $date = date('d M Y', strtotime($row['date']));
                            ?>
                                <tr>
                                    <td class="ps-4"><?php echo h($cnt++); ?></td>
                                    <td><?php echo h($date); ?></td>
                                    <td><?php echo h($row['description']); ?></td>
                                    <td class="fw-bold text-success"><?php echo h($amount); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
