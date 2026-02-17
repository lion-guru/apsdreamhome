<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Create EMI Plan')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/emi"><?php echo h($mlSupport->translate('EMI Plans')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Create Plan')); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <?php if ($flash_error = $this->getFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <?php echo h($mlSupport->translate($flash_error)); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="/admin/emi/store" method="POST" class="needs-validation" id="createEmiForm" novalidate>
                        <?php echo csrf_field(); ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Customer')); ?> <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="customer_id" required>
                                    <option value=""><?php echo h($mlSupport->translate('Select Customer')); ?></option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo $customer['id']; ?>"><?php echo h($customer['name']); ?> (<?php echo h($customer['phone']); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Property')); ?> <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="property_id" required>
                                    <option value=""><?php echo h($mlSupport->translate('Select Property')); ?></option>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?php echo $property['id']; ?>"><?php echo h($property['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Total Amount')); ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" class="form-control" name="total_amount" id="total_amount" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Down Payment')); ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" class="form-control" name="down_payment" id="down_payment" value="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Interest Rate')); ?> (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="interest_rate" id="interest_rate" value="0" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Tenure (Months)')); ?> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="tenure_months" id="tenure_months" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Start Date')); ?> <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="mb-3"><?php echo h($mlSupport->translate('Estimated EMI')); ?></h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <span><?php echo h($mlSupport->translate('Monthly Installment')); ?>:</span>
                                <span class="h4 mb-0 text-primary" id="estimated_emi">₹0.00</span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg"><?php echo h($mlSupport->translate('Create EMI Plan')); ?></button>
                            <a href="/admin/emi" class="btn btn-light"><?php echo h($mlSupport->translate('Cancel')); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalAmountInput = document.getElementById('total_amount');
    const downPaymentInput = document.getElementById('down_payment');
    const interestRateInput = document.getElementById('interest_rate');
    const tenureMonthsInput = document.getElementById('tenure_months');
    const estimatedEmiSpan = document.getElementById('estimated_emi');

    function calculateEMI() {
        const total = parseFloat(totalAmountInput.value) || 0;
        const down = parseFloat(downPaymentInput.value) || 0;
        const rate = parseFloat(interestRateInput.value) || 0;
        const months = parseInt(tenureMonthsInput.value) || 0;

        if (total > 0 && months > 0) {
            const principal = total - down;
            let emi = 0;

            if (rate > 0) {
                const r = rate / (12 * 100);
                emi = principal * r * Math.pow(1 + r, months) / (Math.pow(1 + r, months) - 1);
            } else {
                emi = principal / months;
            }

            estimatedEmiSpan.textContent = '₹' + emi.toFixed(2);
        } else {
            estimatedEmiSpan.textContent = '₹0.00';
        }
    }

    [totalAmountInput, downPaymentInput, interestRateInput, tenureMonthsInput].forEach(input => {
        input.addEventListener('input', calculateEMI);
    });
});
</script>
