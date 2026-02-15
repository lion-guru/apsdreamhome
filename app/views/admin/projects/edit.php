<?php include BASE_PATH . '/resources/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Edit Project')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="/admin/projects"><?php echo h($mlSupport->translate('Projects')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Edit Project')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="/admin/projects/update/<?php echo h($project['project_id']); ?>" method="POST" class="needs-validation" novalidate>
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Project Name')); ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="project_name" class="form-control" value="<?php echo h($project['project_name']); ?>" required>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please provide a project name.')); ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Project Code')); ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="project_code" class="form-control" value="<?php echo h($project['project_code']); ?>" required>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please provide a unique project code.')); ?></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Project Type')); ?></label>
                                    <select name="project_type" class="form-select">
                                        <option value="Residential" <?php echo $project['project_type'] == 'Residential' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Residential')); ?></option>
                                        <option value="Commercial" <?php echo $project['project_type'] == 'Commercial' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Commercial')); ?></option>
                                        <option value="Industrial" <?php echo $project['project_type'] == 'Industrial' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Industrial')); ?></option>
                                        <option value="Mixed Use" <?php echo $project['project_type'] == 'Mixed Use' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Mixed Use')); ?></option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Status')); ?></label>
                                    <select name="project_status" class="form-select">
                                        <option value="Planning" <?php echo $project['project_status'] == 'Planning' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Planning')); ?></option>
                                        <option value="Under Construction" <?php echo $project['project_status'] == 'Under Construction' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Under Construction')); ?></option>
                                        <option value="Completed" <?php echo $project['project_status'] == 'Completed' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Completed')); ?></option>
                                        <option value="Launched" <?php echo $project['project_status'] == 'Launched' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Launched')); ?></option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Starting Price')); ?> (<?php echo h($currency_symbol ?? '₹'); ?>)</label>
                                    <input type="number" name="base_price" class="form-control" step="0.01" value="<?php echo h($project['base_price']); ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Location')); ?></label>
                                    <input type="text" name="location" class="form-control" value="<?php echo h($project['location']); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('City')); ?></label>
                                    <input type="text" name="city" class="form-control" value="<?php echo h($project['city']); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Pincode')); ?></label>
                                    <input type="text" name="pincode" class="form-control" value="<?php echo h($project['pincode']); ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Total Units')); ?></label>
                                    <input type="number" name="total_plots" class="form-control" value="<?php echo h($project['total_plots']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Available Units')); ?></label>
                                    <input type="number" name="available_plots" class="form-control" value="<?php echo h($project['available_plots']); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Description')); ?></label>
                                <textarea name="description" class="form-control" rows="4"><?php echo h($project['description']); ?></textarea>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?php echo $project['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="isActive"><?php echo h($mlSupport->translate('Is Active')); ?></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" <?php echo $project['is_featured'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="isFeatured"><?php echo h($mlSupport->translate('Is Featured')); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary btn-lg px-5"><?php echo h($mlSupport->translate('Update Project')); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>

<?php include BASE_PATH . '/resources/views/admin/layouts/footer.php'; ?>
