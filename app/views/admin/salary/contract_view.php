<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-contract me-2"></i>Contract Details</h1>
        <a href="<?= BASE_URL ?>/admin/salary/contracts" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Contract #<?= $contract['id'] ?? '' ?></h5>
                    <span class="badge bg-<?= match($contract['status']??'active') { 'active'=>'success', 'expired'=>'secondary', 'terminated'=>'danger', default=>'secondary' } ?> fs-6"><?= ucfirst($contract['status'] ?? 'active') ?></span>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Employee:</strong> <?= htmlspecialchars($contract['employee_name'] ?? '') ?></div>
                        <div class="col-md-6"><strong>Email:</strong> <?= htmlspecialchars($contract['employee_email'] ?? '') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Type:</strong> <span class="badge bg-<?= match($contract['contract_type']??'permanent') { 'permanent'=>'primary', 'probation'=>'info', 'contract'=>'warning', 'intern'=>'secondary', default=>'secondary' } ?>"><?= ucfirst($contract['contract_type'] ?? 'permanent') ?></span></div>
                        <div class="col-md-4"><strong>Start Date:</strong> <?= htmlspecialchars($contract['start_date'] ?? '') ?></div>
                        <div class="col-md-4"><strong>End Date:</strong> <?= htmlspecialchars($contract['end_date'] ?? 'Not set') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Salary Amount:</strong> ₹<?= number_format($contract['salary_amount'] ?? 0, 2) ?></div>
                        <div class="col-md-6"><strong>Signing Bonus:</strong> ₹<?= number_format($contract['signing_bonus'] ?? 0, 2) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Created By:</strong> <?= htmlspecialchars($contract['created_by_name'] ?? '-') ?></div>
                        <div class="col-md-6"><strong>Created At:</strong> <?= htmlspecialchars($contract['created_at'] ?? '') ?></div>
                    </div>
                    <?php if ($contract['terms'] ?? ''): ?>
                    <div class="mt-3">
                        <h6>Terms & Conditions</h6>
                        <div class="border p-3 rounded bg-light"><?= nl2br(htmlspecialchars($contract['terms'] ?? '')) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Actions</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (($contract['status'] ?? '') === 'active'): ?>
                        <form method="post" action="<?= BASE_URL ?>/admin/salary/contracts/terminate/<?= $contract['id'] ?>" onsubmit="return confirm('Terminate this contract permanently?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="btn btn-danger w-100"><i class="fas fa-ban me-1"></i>Terminate Contract</button>
                        </form>
                    <?php elseif (($contract['status'] ?? '') === 'terminated'): ?>
                        <div class="alert alert-danger mb-0">This contract has been terminated.</div>
                    <?php else: ?>
                        <div class="alert alert-secondary mb-0">This contract has expired.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
