<?php
/**
 * Sales Entry View
 */
?>
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($page_title); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Sales Entry')); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <?php if (isset($flash['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo h($flash['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($flash['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo h($flash['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="/admin/sales/store" method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_field(); ?>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Associate')); ?> <span class="text-danger">*</span></label>
                                    <select name="associate_id" class="form-control select2" required>
                                        <option value=""><?php echo h($mlSupport->translate('-- Select Associate --')); ?></option>
                                        <?php foreach ($associates as $associate): ?>
                                            <option value="<?php echo h($associate['id']); ?>">
                                                <?php echo h($associate['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please select an associate.')); ?></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Amount')); ?> (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                    </div>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter a valid amount.')); ?></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Date')); ?> <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" value="<?php echo h(date('Y-m-d')); ?>" required>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please select a date.')); ?></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label"><?php echo h($mlSupport->translate('Booking ID / Remarks')); ?></label>
                                    <input type="text" name="booking_id" class="form-control" placeholder="<?php echo h($mlSupport->translate('Optional booking reference')); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> <?php echo h($mlSupport->translate('Save Sales Entry')); ?>
                            </button>
                            <a href="/admin" class="btn btn-secondary"><?php echo h($mlSupport->translate('Cancel')); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize select2 if available
    if ($.fn.select2) {
        $('.select2').select2({
            width: '100%'
        });
    }

    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>
