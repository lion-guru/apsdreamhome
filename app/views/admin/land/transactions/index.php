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
                <a href="/admin/land/transactions/create?kisan_id=<?php echo $land_record['id']; ?>" class="btn btn-primary">
                    <i class="fa fa-plus"></i> <?php echo h($mlSupport->translate('Add Transaction')); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Land Record Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><?php echo h($mlSupport->translate('Land Record Details')); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong><?php echo h($mlSupport->translate('Farmer Name')); ?>:</strong> <?php echo h($land_record['farmer_name']); ?></p>
                            <p><strong><?php echo h($mlSupport->translate('Mobile')); ?>:</strong> <?php echo h($land_record['farmer_mobile']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong><?php echo h($mlSupport->translate('Site Name')); ?>:</strong> <?php echo h($land_record['site_name']); ?></p>
                            <p><strong><?php echo h($mlSupport->translate('Land Area')); ?>:</strong> <?php echo h($land_record['land_area']); ?> sqft</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong><?php echo h($mlSupport->translate('Total Price')); ?>:</strong> ₹<?php echo number_format($land_record['total_land_price'], 2); ?></p>
                            <p><strong><?php echo h($mlSupport->translate('Total Paid')); ?>:</strong> <span class="text-success">₹<?php echo number_format($land_record['total_paid_amount'], 2); ?></span></p>
                            <p><strong><?php echo h($mlSupport->translate('Pending')); ?>:</strong> <span class="text-danger">₹<?php echo number_format($land_record['amount_pending'], 2); ?></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions List -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <?php if ($flash_success = $this->getFlash('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <?php echo h($mlSupport->translate($flash_success)); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover table-center mb-0">
                            <thead>
                                <tr>
                                    <th><?php echo h($mlSupport->translate('ID')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Date')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Description')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Amount')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($transactions)): ?>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td><?php echo h($transaction['id']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($transaction['date'])); ?></td>
                                            <td><?php echo h($transaction['description']); ?></td>
                                            <td class="text-success fw-bold">₹<?php echo number_format($transaction['amount'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center"><?php echo h($mlSupport->translate('No transactions found.')); ?></td>
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
