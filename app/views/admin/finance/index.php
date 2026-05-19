<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-wallet"></i> Finance & Accounts</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/finance/create-invoice" class="btn btn-primary">
                <i class="fas fa-file-invoice"></i> New Invoice
            </a>
            <a href="<?= BASE_URL ?>/admin/finance/create-expense" class="btn btn-danger">
                <i class="fas fa-minus-circle"></i> Add Expense
            </a>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Income</h5>
                    <h3>₹0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Total Expenses</h5>
                    <h3>₹0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Outstanding</h5>
                    <h3>₹0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>EMI Pending</h5>
                    <h3>₹0</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Invoices</h5>
                </div>
                <div class="card-body text-center py-4">
                    <i class="fas fa-file-invoice fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No invoices yet</p>
                    <a href="<?= BASE_URL ?>/admin/finance/create-invoice" class="btn btn-sm btn-primary">Create Invoice</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Expenses</h5>
                </div>
                <div class="card-body text-center py-4">
                    <i class="fas fa-receipt fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No expenses yet</p>
                    <a href="<?= BASE_URL ?>/admin/finance/create-expense" class="btn btn-sm btn-danger">Add Expense</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/admin/finance/invoices" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-file-invoice"></i> All Invoices
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/admin/finance/expenses" class="btn btn-outline-danger w-100 mb-2">
                                <i class="fas fa-receipt"></i> All Expenses
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/admin/finance/payments" class="btn btn-outline-success w-100 mb-2">
                                <i class="fas fa-money-bill"></i> Payments
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/admin/finance/calculator" class="btn btn-outline-warning w-100 mb-2">
                                <i class="fas fa-calculator"></i> EMI Calculator
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>