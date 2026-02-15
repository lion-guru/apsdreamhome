<?php
if (!isAdmin()) {
    header("Location: /admin/login");
    exit();
}
$title = $title ?? $mlSupport->translate("Edit Employee");
require_once ABSPATH . '/resources/views/admin/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="/admin/employees"><?php echo h($mlSupport->translate('Employees')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Edit Employee')); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <?php if ($flash_error = get_flash('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo h($flash_error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="/admin/employees/update/<?php echo h($employee['id']); ?>" method="POST" class="needs-validation" novalidate>
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Full Name')); ?> <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="name" value="<?php echo h($employee['name']); ?>" required>
                                        <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter employee name.')); ?></div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Email Address')); ?></label>
                                        <input class="form-control" type="email" value="<?php echo h($employee['email']); ?>" readonly disabled>
                                        <small class="text-muted"><?php echo h($mlSupport->translate('Email cannot be changed.')); ?></small>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Phone Number')); ?></label>
                                        <input class="form-control" type="text" name="phone" value="<?php echo h($employee['phone']); ?>">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Department')); ?></label>
                                        <select class="form-control" name="department">
                                            <option value="General" <?php echo $employee['department'] == 'General' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('General')); ?></option>
                                            <option value="Sales" <?php echo $employee['department'] == 'Sales' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Sales')); ?></option>
                                            <option value="Marketing" <?php echo $employee['department'] == 'Marketing' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Marketing')); ?></option>
                                            <option value="Accounts" <?php echo $employee['department'] == 'Accounts' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Accounts')); ?></option>
                                            <option value="IT" <?php echo $employee['department'] == 'IT' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('IT')); ?></option>
                                            <option value="Operations" <?php echo $employee['department'] == 'Operations' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Operations')); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('System Role')); ?> <span class="text-danger">*</span></label>
                                        <select class="form-control" name="role_id" required>
                                            <option value=""><?php echo h($mlSupport->translate('Select Role')); ?></option>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?php echo h($role['id']); ?>" <?php echo (isset($assignedRole['id']) && $assignedRole['id'] == $role['id']) ? 'selected' : ''; ?>>
                                                    <?php echo h($mlSupport->translate($role['name'])); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please select a system role.')); ?></div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Designation (Employee Role)')); ?></label>
                                        <select class="form-control" name="role">
                                            <option value="employee" <?php echo $employee['role'] == 'employee' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Employee')); ?></option>
                                            <option value="manager" <?php echo $employee['role'] == 'manager' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Manager')); ?></option>
                                            <option value="supervisor" <?php echo $employee['role'] == 'supervisor' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Supervisor')); ?></option>
                                            <option value="executive" <?php echo $employee['role'] == 'executive' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Executive')); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Salary')); ?></label>
                                        <input class="form-control" type="number" name="salary" step="0.01" value="<?php echo h($employee['salary']); ?>">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Join Date')); ?></label>
                                        <input class="form-control" type="date" name="join_date" value="<?php echo h($employee['join_date']); ?>">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Status')); ?></label>
                                        <select class="form-control" name="status">
                                            <option value="active" <?php echo $employee['status'] == 'active' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Active')); ?></option>
                                            <option value="inactive" <?php echo $employee['status'] == 'inactive' ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Inactive')); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Address')); ?></label>
                                        <textarea class="form-control" name="address" rows="3"><?php echo h($employee['address']); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Notes')); ?></label>
                                        <textarea class="form-control" name="notes" rows="2"><?php echo h($employee['notes']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section mt-4">
                                <button type="submit" class="btn btn-primary submit-btn"><?php echo h($mlSupport->translate('Update Employee')); ?></button>
                                <a href="/admin/employees" class="btn btn-secondary submit-btn ms-2"><?php echo h($mlSupport->translate('Cancel')); ?></a>
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

<?php require_once ABSPATH . '/resources/views/admin/layouts/footer.php'; ?>

