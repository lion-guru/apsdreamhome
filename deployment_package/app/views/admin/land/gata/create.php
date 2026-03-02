<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Add New Gata</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url('admin/dashboard'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('admin/gata/list'); ?>">Gata Master</a></li>
                <li class="breadcrumb-item active">Add Gata</li>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="card-title mb-0">Gata Details</h4>
            </div>
            <div class="card-body">
                <?php echo \App\Helpers\SessionHelper::getFlashMessage(); ?>
                
                <form action="<?php echo url('admin/gata/store'); ?>" method="POST">
                    <?php echo \App\Helpers\CsrfHelper::getCsrfField(); ?>
                    
                    <div class="form-group">
                        <label>Site Name <span class="text-danger">*</span></label>
                        <select name="site_name" class="form-control select" required>
                            <option value="">Select Site</option>
                            <?php if (!empty($sites)): ?>
                                <?php foreach ($sites as $site): ?>
                                    <option value="<?php echo $site['site_id']; ?>">
                                        <?php echo htmlspecialchars($site['site_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="form-text text-muted">Area will be deducted from selected Site.</small>
                    </div>

                    <div class="form-group">
                        <label>Gata Number <span class="text-danger">*</span></label>
                        <input type="text" name="gata_no" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Total Area (sqft) <span class="text-danger">*</span></label>
                        <input type="number" name="area" step="0.01" class="form-control" required>
                    </div>

                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-primary px-4">Submit</button>
                        <a href="<?php echo url('admin/gata/list'); ?>" class="btn btn-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>