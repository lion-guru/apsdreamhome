<?php
if (!isAdmin()) {
    header("Location: /admin/login");
    exit();
}
$page_title = $page_title ?? $mlSupport->translate("Add Customer");
require_once ABSPATH . '/resources/views/admin/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="/admin/customers"><?php echo h($mlSupport->translate('Customers')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Add Customer')); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <?php if($flash_success = get_flash('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo h($flash_success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <?php if($flash_error = get_flash('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo h($flash_error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="/admin/customers/store" method="POST" class="needs-validation" novalidate>
                            <?php echo csrf_field(); ?>
                            
                            <h4 class="card-title"><?php echo h($mlSupport->translate('Personal Information')); ?></h4>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Full Name')); ?> <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="name" required>
                                        <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter customer name.')); ?></div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Email Address')); ?></label>
                                        <input class="form-control" type="email" name="email">
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
                                        <label><?php echo h($mlSupport->translate('Date of Birth')); ?></label>
                                        <input class="form-control" type="date" name="date_of_birth">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Gender')); ?></label>
                                        <select class="form-control" name="gender">
                                            <option value=""><?php echo h($mlSupport->translate('Select Gender')); ?></option>
                                            <option value="male"><?php echo h($mlSupport->translate('Male')); ?></option>
                                            <option value="female"><?php echo h($mlSupport->translate('Female')); ?></option>
                                            <option value="other"><?php echo h($mlSupport->translate('Other')); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Marital Status')); ?></label>
                                        <select class="form-control" name="marital_status">
                                            <option value=""><?php echo h($mlSupport->translate('Select Status')); ?></option>
                                            <option value="single"><?php echo h($mlSupport->translate('Single')); ?></option>
                                            <option value="married"><?php echo h($mlSupport->translate('Married')); ?></option>
                                            <option value="divorced"><?php echo h($mlSupport->translate('Divorced')); ?></option>
                                            <option value="widowed"><?php echo h($mlSupport->translate('Widowed')); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <h4 class="card-title mt-4"><?php echo h($mlSupport->translate('Address Details')); ?></h4>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Full Address')); ?></label>
                                        <textarea class="form-control" name="address" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('City')); ?></label>
                                        <input class="form-control" type="text" name="city">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('State')); ?></label>
                                        <input class="form-control" type="text" name="state">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Pincode')); ?></label>
                                        <input class="form-control" type="text" name="pincode">
                                    </div>
                                </div>
                            </div>

                            <h4 class="card-title mt-4"><?php echo h($mlSupport->translate('Other Information')); ?></h4>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Occupation')); ?></label>
                                        <input class="form-control" type="text" name="occupation">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Referral Source')); ?></label>
                                        <input class="form-control" type="text" name="referral_source" placeholder="<?php echo h($mlSupport->translate('e.g. Newspaper, Friend, Web')); ?>">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('Sponsor ID (Optional)')); ?></label>
                                        <input class="form-control" type="text" name="sponsor_id" placeholder="<?php echo h($mlSupport->translate('Enter Sponsor Referral Code')); ?>">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label><?php echo h($mlSupport->translate('KYC Status')); ?></label>
                                        <select class="form-control" name="kyc_status">
                                            <option value="Not Submitted"><?php echo h($mlSupport->translate('Not Submitted')); ?></option>
                                            <option value="Pending"><?php echo h($mlSupport->translate('Pending')); ?></option>
                                            <option value="Verified"><?php echo h($mlSupport->translate('Verified')); ?></option>
                                            <option value="Rejected"><?php echo h($mlSupport->translate('Rejected')); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-4 mb-0">
                                <button class="btn btn-primary btn-lg" type="submit"><?php echo h($mlSupport->translate('Add Customer')); ?></button>
                                <a href="/admin/customers" class="btn btn-secondary btn-lg"><?php echo h($mlSupport->translate('Cancel')); ?></a>
                            </div>
                        </form>
                    </div>
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
  Array.prototype.slice.call(forms).forEach(function (form) {
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

