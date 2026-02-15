<?php
$page_title = "Edit Land Record";
require_once ABSPATH . '/resources/views/admin/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Land Record</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="/admin/land">Land Records</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <?php if ($flash_success = get_flash('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo h($flash_success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($flash_error = get_flash('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo h($flash_error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="/admin/land/update/<?php echo $record['id']; ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Farmer Name <span class="text-danger">*</span></label>
                                        <input type="text" name="farmer_name" class="form-control" value="<?php echo h($record['farmer_name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Farmer Mobile <span class="text-danger">*</span></label>
                                        <input type="text" name="farmer_mobile" class="form-control" value="<?php echo h($record['farmer_mobile'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" name="bank_name" class="form-control" value="<?php echo h($record['bank_name'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Account Number</label>
                                        <input type="text" name="account_number" class="form-control" value="<?php echo h($record['account_number'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">IFSC Code</label>
                                        <input type="text" name="bank_ifsc" class="form-control" value="<?php echo h($record['bank_ifsc'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Site Name</label>
                                        <input type="text" name="site_name" class="form-control" value="<?php echo h($record['site_name'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Land Area (sqft)</label>
                                        <input type="number" step="0.01" name="land_area" class="form-control" value="<?php echo h($record['land_area'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Total Land Price</label>
                                        <input type="number" step="0.01" name="total_land_price" class="form-control" value="<?php echo h($record['total_land_price'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Gata Number</label>
                                        <input type="text" name="gata_number" class="form-control" value="<?php echo h($record['gata_number'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">District</label>
                                        <input type="text" name="district" class="form-control" value="<?php echo h($record['district'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tehsil</label>
                                        <input type="text" name="tehsil" class="form-control" value="<?php echo h($record['tehsil'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" class="form-control" value="<?php echo h($record['city'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Gram</label>
                                        <input type="text" name="gram" class="form-control" value="<?php echo h($record['gram'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Land Paper</label>
                                        <input type="file" name="land_paper" class="form-control">
                                        <?php if (!empty($record['land_paper'])): ?>
                                            <div class="mt-2">
                                                <small>Current File: <a href="/uploads/land_papers/<?php echo h($record['land_paper']); ?>" target="_blank">View File</a></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Land Manager Name</label>
                                        <input type="text" name="land_manager_name" class="form-control" value="<?php echo h($record['land_manager_name'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Land Manager Mobile</label>
                                        <input type="text" name="land_manager_mobile" class="form-control" value="<?php echo h($record['land_manager_mobile'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Agreement Status</label>
                                        <select name="agreement_status" class="form-select">
                                            <option value="Pending" <?php echo ($record['agreement_status'] ?? '') == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Registered" <?php echo ($record['agreement_status'] ?? '') == 'Registered' ? 'selected' : ''; ?>>Registered</option>
                                            <option value="Done" <?php echo ($record['agreement_status'] ?? '') == 'Done' ? 'selected' : ''; ?>>Done</option>
                                            <option value="Cancelled" <?php echo ($record['agreement_status'] ?? '') == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" name="update_land_details" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ABSPATH . '/resources/views/admin/layouts/footer.php'; ?>