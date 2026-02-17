<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Add Associate')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/associates"><?php echo h($mlSupport->translate('Associates')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Add Associate')); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <?php if ($flash_error = get_flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo h($flash_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="/admin/associates/store" method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Full Name')); ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?php echo h(old('name')); ?>" required>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter full name.')); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Email Address')); ?> <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="<?php echo h(old('email')); ?>" required>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter a valid email.')); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Phone Number')); ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" pattern="[6-9][0-9]{9}" value="<?php echo h(old('phone')); ?>" required>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter a valid 10-digit phone number.')); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Password')); ?> <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" required minlength="6">
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Password must be at least 6 characters.')); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Sponsor (Optional)')); ?></label>
                                    <select name="sponsor_id" class="form-control select2">
                                        <option value=""><?php echo h($mlSupport->translate('-- No Sponsor --')); ?></option>
                                        <?php foreach ($sponsors as $sponsor): ?>
                                            <option value="<?php echo h($sponsor['id']); ?>" <?php echo old('sponsor_id') == $sponsor['id'] ? 'selected' : ''; ?>>
                                                <?php echo h($sponsor['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Commission Rate (%)')); ?></label>
                                    <input type="number" name="commission_rate" class="form-control" step="0.01" min="0" max="100" value="<?php echo h(old('commission_rate', '0.00')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="submit-section mt-4">
                            <button type="submit" class="btn btn-primary submit-btn"><?php echo h($mlSupport->translate('Create Associate')); ?></button>
                            <a href="/admin/associates" class="btn btn-secondary submit-btn"><?php echo h($mlSupport->translate('Cancel')); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Bootstrap validation
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
