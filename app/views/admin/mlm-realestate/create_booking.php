<?php
$page_title = $page_title ?? 'Create Booking - APS Dream Home';
$page_heading = $page_heading ?? 'Create Booking';
$users = $users ?? [];
$plots = $plots ?? [];
$plots2 = $plots2 ?? [];
$error = $error ?? '';
$success = $success ?? '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-file-contract me-2"></i><?= htmlspecialchars($page_heading ?? '') ?></h2>
        <a href="<?= BASE_URL ?>/admin/mlm-realestate" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error ?? '') ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success ?? '') ?></div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Booking Details</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/mlm-realestate/create-booking/store">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control" required placeholder="Full name of buyer">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer User ID</label>
                        <input type="number" name="customer_id" class="form-control" placeholder="If registered user">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Networker / Agent <span class="text-danger">*</span></label>
                        <select name="agent_id" class="form-select" required>
                            <option value="">Select Networker</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Mode</label>
                        <select name="payment_mode" class="form-select">
                            <option value="Full">Full Payment</option>
                            <option value="Installment">Installment</option>
                            <option value="Loan">Loan</option>
                        </select>
                    </div>
                </div>

                <h6 class="text-muted mb-3 mt-4"><i class="fas fa-map me-1"></i> Select Plot</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Inventory Plots</label>
                        <select name="plot_id" class="form-select">
                            <option value="">-- Select from Inventory --</option>
                            <?php foreach ($plots as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['block_name'] ?? '') ?> - Plot <?= htmlspecialchars($p['plot_no'] ?? '') ?> (<?= $p['size_sqft'] ?? '' ?> sqft) - ₹<?= number_format($p['basic_price'] ?? 0) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">OR Colony Plots</label>
                        <select name="plot_id_backup" class="form-select">
                            <option value="">-- Select from Colony Plots --</option>
                            <?php foreach ($plots2 as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['block'] ?? '') ?> - Plot <?= htmlspecialchars($p['plot_no'] ?? '') ?> (<?= $p['area_sqft'] ?? '' ?> sqft) - ₹<?= number_format($p['total_price'] ?? 0) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Initial Payment (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="initial_payment" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Booking Date <span class="text-danger">*</span></label>
                        <input type="date" name="booking_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Booking</button>
                    <a href="<?= BASE_URL ?>/admin/mlm-realestate" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
