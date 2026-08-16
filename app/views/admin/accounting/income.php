ï»¿<?php $pageTitle = 'Income Ledger'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-arrow-up text-success me-2"></i>Income Ledger</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/accounting">Accounting</a></li>
                    <li class="breadcrumb-item active">Income</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/accounting/add_income" class="btn btn-success btn-sm"><i class="fas fa-plus me-1"></i>Add Income</a>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col"><h5 class="mb-0"><i class="fas fa-list me-2"></i>All Income Records</h5></div>
                <div class="col-auto">
                    <form class="d-flex" method="GET"><input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search income..." class="style-47085"><button class="btn btn-sm btn-outline-primary" type="submit"><i class="fas fa-search"></i></button></form>
    <?php echo CSRFProtection::csrfField(); ?>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">#</th><th>Date</th><th>Source</th><th>Description</th><th>Category</th><th class="text-end pe-4">Amount</th></tr></thead>
                    <tbody>
                        <?php if (empty($incomeList)): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-coins fa-3x d-block mb-3"></i>No income records found</td></tr>
                        <?php else: ?>
                            <?php $i=1; foreach ($incomeList as $inc): ?>
                            <tr><td class="ps-4"><?= $i++ ?></td><td><?= date('d M Y', strtotime($inc['date'])) ?></td><td><?= $inc['source'] ?? '-' ?></td><td><?= $inc['description'] ?></td><td><span class="badge bg-info-subtle text-info rounded-pill px-3"><?= $inc['category'] ?? 'General' ?></span></td><td class="text-end pe-4 fw-bold text-success">+₹<?= number_format($inc['amount'] ?? 0, 2) ?></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
