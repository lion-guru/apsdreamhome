<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Add Transaction')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/land"><?php echo h($mlSupport->translate('Land Records')); ?></a></li>
                    <?php if (!empty($kisan_id)): ?>
                        <li class="breadcrumb-item"><a href="/admin/land/transactions/<?php echo $kisan_id; ?>"><?php echo h($mlSupport->translate('Transactions')); ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Add Transaction')); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <?php if ($flash_success = $this->getFlash('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <?php echo h($mlSupport->translate($flash_success)); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($flash_error = $this->getFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <?php echo h($mlSupport->translate($flash_error)); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="/admin/land/transactions/store" method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_field(); ?>

                        <div class="form-group mb-3">
                            <label class="form-label"><?php echo h($mlSupport->translate('Kisan ID')); ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-id-card"></i></span>
                                <input type="number" class="form-control" id="kisan_id" name="kisan_id" value="<?php echo h($kisan_id); ?>" required <?php echo !empty($kisan_id) ? 'readonly' : ''; ?>>
                            </div>
                            <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter the Kisan ID.')); ?></div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label"><?php echo h($mlSupport->translate('Amount')); ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                            </div>
                            <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter the amount.')); ?></div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label"><?php echo h($mlSupport->translate('Date')); ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please select a date.')); ?></div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label"><?php echo h($mlSupport->translate('Description')); ?> <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter a description.')); ?></div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg"><?php echo h($mlSupport->translate('Add Transaction')); ?></button>
                            <a href="/admin/land" class="btn btn-light"><?php echo h($mlSupport->translate('Cancel')); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
