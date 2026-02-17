<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Add Transaction')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/land"><?php echo h($mlSupport->translate('Land Records')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/land/transactions/<?php echo h($land_record['id']); ?>"><?php echo h($mlSupport->translate('Transactions')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Add')); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <?php if ($flash_error = get_flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <?php echo h($mlSupport->translate($flash_error)); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><?php echo h($mlSupport->translate('Transaction Details')); ?></h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-sm-6">
                                <small class="text-muted d-block"><?php echo h($mlSupport->translate('Farmer Name')); ?></small>
                                <strong><?php echo h($land_record['farmer_name'] ?? 'N/A'); ?></strong>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <small class="text-muted d-block"><?php echo h($mlSupport->translate('Site / Gata')); ?></small>
                                <strong><?php echo h($land_record['site_name'] ?? 'N/A'); ?> / <?php echo h($land_record['gata_number'] ?? 'N/A'); ?></strong>
                            </div>
                        </div>
                    </div>

                    <form action="/admin/land/transactions/store/<?php echo h($land_record['id']); ?>" method="POST" class="needs-validation" novalidate>
                        <?php echo $csrf_field ?? ''; ?>
                        
                        <div class="mb-3">
                            <label class="form-label"><?php echo h($mlSupport->translate('Amount')); ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo h($currency_symbol ?? '₹'); ?></span>
                                <input type="number" step="0.01" class="form-control" name="amount" required>
                            </div>
                            <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter a valid amount.')); ?></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo h($mlSupport->translate('Date')); ?> <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please select a date.')); ?></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?php echo h($mlSupport->translate('Description')); ?> <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                            <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter a description.')); ?></div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg"><?php echo h($mlSupport->translate('Save Transaction')); ?></button>
                            <a href="/admin/land/transactions/<?php echo h($land_record['id']); ?>" class="btn btn-light btn-lg"><?php echo h($mlSupport->translate('Cancel')); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
