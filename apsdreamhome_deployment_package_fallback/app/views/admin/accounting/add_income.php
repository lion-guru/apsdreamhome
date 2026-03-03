<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?= h($mlSupport->translate('Add New Income')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?= h($mlSupport->translate('Dashboard')) ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/accounting"><?= h($mlSupport->translate('Accounting')) ?></a></li>
                    <li class="breadcrumb-item active"><?= h($mlSupport->translate('Add Income')) ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="/admin/accounting/income/store" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= $this->getCsrfToken() ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= h($mlSupport->translate('Date')) ?> <span class="text-danger">*</span></label>
                                <input type="date" name="income_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= h($mlSupport->translate('Amount')) ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><?= h($currency_symbol ?? '₹') ?></span>
                                    <input type="number" step="0.01" name="amount" class="form-control" required min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= h($mlSupport->translate('Category')) ?> <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    <option value=""><?= h($mlSupport->translate('Select Category')) ?></option>
                                    <option value="Sales"><?= h($mlSupport->translate('Sales')) ?></option>
                                    <option value="Services"><?= h($mlSupport->translate('Services')) ?></option>
                                    <option value="Rental"><?= h($mlSupport->translate('Rental')) ?></option>
                                    <option value="Other"><?= h($mlSupport->translate('Other')) ?></option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= h($mlSupport->translate('Payment Method')) ?></label>
                                <select name="payment_method" class="form-select">
                                    <option value="cash"><?= h($mlSupport->translate('Cash')) ?></option>
                                    <option value="bank_transfer"><?= h($mlSupport->translate('Bank Transfer')) ?></option>
                                    <option value="cheque"><?= h($mlSupport->translate('Cheque')) ?></option>
                                    <option value="online"><?= h($mlSupport->translate('Online')) ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= h($mlSupport->translate('Customer')) ?></label>
                                <select name="customer_id" class="form-select">
                                    <option value=""><?= h($mlSupport->translate('Select Customer (Optional)')) ?></option>
                                    <?php if (!empty($customers)): ?>
                                        <?php foreach ($customers as $customer): ?>
                                            <option value="<?= h($customer['id']) ?>"><?= h($customer['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= h($mlSupport->translate('Project')) ?></label>
                                <select name="project_id" class="form-select">
                                    <option value=""><?= h($mlSupport->translate('Select Project (Optional)')) ?></option>
                                    <?php if (!empty($projects)): ?>
                                        <?php foreach ($projects as $project): ?>
                                            <option value="<?= h($project['id']) ?>"><?= h($project['pname']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><?= h($mlSupport->translate('Description')) ?></label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="text-end">
                            <a href="/admin/accounting" class="btn btn-secondary me-2"><?= h($mlSupport->translate('Cancel')) ?></a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?= h($mlSupport->translate('Save Income')) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>