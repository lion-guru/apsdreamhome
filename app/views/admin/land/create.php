<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Add Land Record')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/land"><?php echo h($mlSupport->translate('Land Records')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Add New')); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
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

                    <form action="/admin/land/store" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Farmer Name')); ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="farmer_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Farmer Mobile')); ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="farmer_mobile" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Bank Name')); ?></label>
                                    <input type="text" name="bank_name" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Account Number')); ?></label>
                                    <input type="text" name="account_number" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('IFSC Code')); ?></label>
                                    <input type="text" name="bank_ifsc" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Site Name')); ?></label>
                                    <input type="text" name="site_name" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Land Area (sqft)')); ?></label>
                                    <input type="number" step="0.01" name="land_area" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Total Land Price')); ?></label>
                                    <input type="number" step="0.01" name="total_land_price" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Gata Number')); ?></label>
                                    <input type="text" name="gata_number" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('District')); ?></label>
                                    <input type="text" name="district" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Tehsil')); ?></label>
                                    <input type="text" name="tehsil" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('City')); ?></label>
                                    <input type="text" name="city" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Gram')); ?></label>
                                    <input type="text" name="gram" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Land Paper')); ?></label>
                                    <input type="file" name="land_paper" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Land Manager Name')); ?></label>
                                    <input type="text" name="land_manager_name" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Land Manager Mobile')); ?></label>
                                    <input type="text" name="land_manager_mobile" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Agreement Status')); ?></label>
                                    <select name="agreement_status" class="form-select">
                                        <option value="Pending"><?php echo h($mlSupport->translate('Pending')); ?></option>
                                        <option value="Registered"><?php echo h($mlSupport->translate('Registered')); ?></option>
                                        <option value="Done"><?php echo h($mlSupport->translate('Done')); ?></option>
                                        <option value="Cancelled"><?php echo h($mlSupport->translate('Cancelled')); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" name="add_land_details" class="btn btn-primary"><?php echo h($mlSupport->translate('Submit')); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
