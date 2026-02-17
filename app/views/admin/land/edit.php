<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Edit Land Record')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/land"><?php echo h($mlSupport->translate('Land Records')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Edit')); ?></li>
                </ul>
            </div>
        </div>
    </div>

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

                    <?php if ($flash_error = $this->getFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <?php echo h($mlSupport->translate($flash_error)); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="/admin/land/update/<?php echo $land_record['id']; ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Farmer Name')); ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="farmer_name" class="form-control" value="<?php echo h($land_record['farmer_name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Farmer Mobile')); ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="farmer_mobile" class="form-control" value="<?php echo h($land_record['farmer_mobile']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Bank Name')); ?></label>
                                    <input type="text" name="bank_name" class="form-control" value="<?php echo h($land_record['bank_name']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Account Number')); ?></label>
                                    <input type="text" name="account_number" class="form-control" value="<?php echo h($land_record['account_number']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('IFSC Code')); ?></label>
                                    <input type="text" name="bank_ifsc" class="form-control" value="<?php echo h($land_record['bank_ifsc']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Site Name')); ?></label>
                                    <input type="text" name="site_name" class="form-control" value="<?php echo h($land_record['site_name']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Land Area (sqft)')); ?></label>
                                    <input type="number" step="0.01" name="land_area" class="form-control" value="<?php echo h($land_record['land_area']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Total Land Price')); ?></label>
                                    <input type="number" step="0.01" name="total_land_price" class="form-control" value="<?php echo h($land_record['total_land_price']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Total Paid Amount')); ?></label>
                                    <input type="number" step="0.01" name="total_paid_amount" class="form-control" value="<?php echo h($land_record['total_paid_amount']); ?>">
                                    <small class="text-muted"><?php echo h($mlSupport->translate('Manually update if needed, or use Transactions to add payments.')); ?></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Agreement Status')); ?></label>
                                    <select name="agreement_status" class="form-select">
                                        <option value="Pending" <?php echo ($land_record['agreement_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Completed" <?php echo ($land_record['agreement_status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Cancelled" <?php echo ($land_record['agreement_status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Gata Number')); ?></label>
                                    <input type="text" name="gata_number" class="form-control" value="<?php echo h($land_record['gata_number']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('District')); ?></label>
                                    <input type="text" name="district" class="form-control" value="<?php echo h($land_record['district']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Tehsil')); ?></label>
                                    <input type="text" name="tehsil" class="form-control" value="<?php echo h($land_record['tehsil']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('City')); ?></label>
                                    <input type="text" name="city" class="form-control" value="<?php echo h($land_record['city']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Gram')); ?></label>
                                    <input type="text" name="gram" class="form-control" value="<?php echo h($land_record['gram']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Land Manager Name')); ?></label>
                                    <input type="text" name="land_manager_name" class="form-control" value="<?php echo h($land_record['land_manager_name']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Land Manager Mobile')); ?></label>
                                    <input type="text" name="land_manager_mobile" class="form-control" value="<?php echo h($land_record['land_manager_mobile']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Land Paper Display/Upload if needed -->
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Land Paper')); ?></label>
                                    <?php if (!empty($land_record['land_paper'])): ?>
                                        <div class="mb-2">
                                            <a href="/<?php echo h($land_record['land_paper']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-file"></i> <?php echo h($mlSupport->translate('View Current File')); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <small class="text-muted"><?php echo h($mlSupport->translate('Upload feature for update not implemented in this form version.')); ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary"><?php echo h($mlSupport->translate('Update Record')); ?></button>
                            <a href="/admin/land" class="btn btn-secondary"><?php echo h($mlSupport->translate('Cancel')); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>