<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Edit Gata</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url('admin/dashboard'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('admin/gata/list'); ?>">Gata Master</a></li>
                <li class="breadcrumb-item active">Edit Gata</li>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Gata Details</h4>
            </div>
            <div class="card-body">
                <?php echo \App\Helpers\SessionHelper::getFlashMessage(); ?>
                
                <form action="<?php echo url('admin/gata/update/' . $gata['gata_id']); ?>" method="POST">
                    <?php echo \App\Helpers\CsrfHelper::getCsrfField(); ?>
                    
                    <div class="form-group">
                        <label>Site Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($site['site_name']); ?>" readonly>
                        <input type="hidden" name="site_id_edit" value="<?php echo $site['site_id']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Gata Number <span class="text-danger">*</span></label>
                        <input type="text" name="gata_no" class="form-control" value="<?php echo htmlspecialchars($gata['gata_no']); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Current Total Area</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($gata['area']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Available Area</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($gata['available_area']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5 class="text-primary mb-3">Modify Area</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Action</label>
                                <select name="area_edit_type" class="form-control">
                                    <option value="">No Change</option>
                                    <option value="add_area">Add Area</option>
                                    <option value="subs_area">Subtract Area</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount (sqft)</label>
                                <input type="number" name="area_edit_new" step="0.01" class="form-control" placeholder="0">
                            </div>
                        </div>
                    </div>
                    <small class="form-text text-muted mb-3">Adding area will deduct from Site Available Area. Subtracting will add back to Site.</small>

                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-primary px-4">Update</button>
                        <a href="<?php echo url('admin/gata/list'); ?>" class="btn btn-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>