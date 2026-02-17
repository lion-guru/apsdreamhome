<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Add Employee')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/employees"><?php echo h($mlSupport->translate('Employees')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Add Employee')); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <?php if ($flash_error = get_flash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo h($flash_error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="/admin/employees/store" method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label><?php echo h($mlSupport->translate('Full Name')); ?> <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="name" required>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter employee name.')); ?></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label><?php echo h($mlSupport->translate('Email Address')); ?> <span class="text-danger">*</span></label>
                                    <input class="form-control" type="email" name="email" required>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter a valid email.')); ?></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label><?php echo h($mlSupport->translate('Phone Number')); ?></label>
                                    <input class="form-control" type="text" name="phone">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label><?php echo h($mlSupport->translate('Password')); ?></label>
                                    <input class="form-control" type="password" name="password">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label><?php echo h($mlSupport->translate('Department')); ?></label>
                                    <select class="form-control" name="department_id">
                                        <option value=""><?php echo h($mlSupport->translate('Select Department')); ?></option>
                                        <?php if (!empty($departments)): ?>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?php echo h($dept['id']); ?>"><?php echo h($mlSupport->translate($dept['name'])); ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label><?php echo h($mlSupport->translate('System Role')); ?> <span class="text-danger">*</span></label>
                                    <select class="form-control" name="role_id" required>
                                        <option value=""><?php echo h($mlSupport->translate('Select Role')); ?></option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo h($role['id']); ?>"><?php echo h($mlSupport->translate($role['name'])); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please select a system role.')); ?></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label><?php echo h($mlSupport->translate('Designation (Job Title)')); ?></label>
                                    <input class="form-control" type="text" name="designation" placeholder="<?php echo h($mlSupport->translate('e.g. Senior Manager')); ?>">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label><?php echo h($mlSupport->translate('Salary')); ?></label>
                                    <input class="form-control" type="number" name="salary" step="0.01">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label><?php echo h($mlSupport->translate('Join Date')); ?></label>
                                    <input class="form-control" type="date" name="joining_date" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group mb-3">
                                    <label><?php echo h($mlSupport->translate('Address')); ?></label>
                                    <textarea class="form-control" name="address" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group mb-3">
                                    <label><?php echo h($mlSupport->translate('Notes')); ?></label>
                                    <textarea class="form-control" name="notes" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="submit-section mt-4">
                            <button type="submit" class="btn btn-primary submit-btn"><?php echo h($mlSupport->translate('Save Employee')); ?></button>
                            <a href="/admin/employees" class="btn btn-secondary submit-btn ms-2"><?php echo h($mlSupport->translate('Cancel')); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function() {
        'use strict'

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>