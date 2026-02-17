<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Add New User')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/users"><?php echo h($mlSupport->translate('Users')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Add User')); ?></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <?php if ($flash_error = get_flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo h($mlSupport->translate($flash_error)); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="/admin/users/store" method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Username')); ?> <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" required>
                                <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please provide a username.')); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Email')); ?> <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                                <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please provide a valid email.')); ?></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Password')); ?> <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                                <div class="invalid-feedback"><?php echo h($mlSupport->translate('Password must be at least 6 characters.')); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Mobile')); ?></label>
                                <input type="text" name="mobile" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Role')); ?></label>
                                <select name="role" class="form-select">
                                    <option value="customer"><?php echo h($mlSupport->translate('Customer')); ?></option>
                                    <option value="admin"><?php echo h($mlSupport->translate('Admin')); ?></option>
                                    <option value="sales"><?php echo h($mlSupport->translate('Sales')); ?></option>
                                    <option value="agent"><?php echo h($mlSupport->translate('Agent')); ?></option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Status')); ?></label>
                                <select name="status" class="form-select">
                                    <option value="active"><?php echo h($mlSupport->translate('Active')); ?></option>
                                    <option value="inactive"><?php echo h($mlSupport->translate('Inactive')); ?></option>
                                    <option value="blocked"><?php echo h($mlSupport->translate('Blocked')); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="/admin/users" class="btn btn-secondary me-2"><?php echo h($mlSupport->translate('Cancel')); ?></a>
                            <button type="submit" class="btn btn-primary px-5"><?php echo h($mlSupport->translate('Create User')); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
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