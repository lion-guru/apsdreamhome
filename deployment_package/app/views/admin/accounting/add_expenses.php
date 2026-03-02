<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?= h($mlSupport->translate('Add New Expense')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?= h($mlSupport->translate('Dashboard')) ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/accounting"><?= h($mlSupport->translate('Accounting')) ?></a></li>
                    <li class="breadcrumb-item active"><?= h($mlSupport->translate('Add Expense')) ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="/admin/accounting/expense/store" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= $this->getCsrfToken() ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= h($mlSupport->translate('Date')) ?> <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
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
                                <label class="form-label"><?= h($mlSupport->translate('Paid To (Source/Payee)')) ?> <span class="text-danger">*</span></label>
                                <input type="text" name="source" class="form-control" placeholder="<?= h($mlSupport->translate('e.g. Office Rent, Vendor Name')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= h($mlSupport->translate('Category')) ?></label>
                                <select name="category" class="form-select">
                                    <option value="Operational"><?= h($mlSupport->translate('Operational')) ?></option>
                                    <option value="Salary"><?= h($mlSupport->translate('Salary')) ?></option>
                                    <option value="Marketing"><?= h($mlSupport->translate('Marketing')) ?></option>
                                    <option value="Maintenance"><?= h($mlSupport->translate('Maintenance')) ?></option>
                                    <option value="Other"><?= h($mlSupport->translate('Other')) ?></option>
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
                                <i class="fas fa-save me-2"></i><?= h($mlSupport->translate('Save Expense')) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>