<?php
$result = $result ?? null;
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="text-center mb-5">
                <i class="fas fa-gavel fa-3x text-primary mb-3"></i>
                <h1>RERA Compliance Lookup</h1>
                <p class="text-muted">Check your Real Estate Regulatory Authority (RERA) compliance status</p>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <form method="get" action="<?= $base ?>/rera-lookup">
                        <div class="input-group input-group-lg">
                            <input type="text" name="rera_number" class="form-control" placeholder="Enter RERA Number (e.g., UP/RERA/2026/XXXXX)" value="<?= htmlspecialchars($_GET['rera_number'] ?? '') ?>" required>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-2"></i>Lookup</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($result !== null): ?>
                <?php if ($result['found'] ?? false): $u = $result['user']; ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>RERA Status</h5>
                            <span class="badge bg-<?= ($u['is_rera_approved'] ?? 0) ? 'success' : 'warning' ?> fs-6"><?= ($u['is_rera_approved'] ?? 0) ? 'APPROVED' : 'PENDING' ?></span>
                        </div>
                        <div class="card-body aps-cp-card-body">
                            <div class="row g-3">
                                <div class="col-md-6"><strong>RERA Number:</strong><br><?= htmlspecialchars($u['rera_number'] ?? 'N/A') ?></div>
                                <div class="col-md-6"><strong>Name:</strong><br><?= htmlspecialchars($u['name'] ?? 'N/A') ?></div>
                                <div class="col-md-6"><strong>Email:</strong><br><?= htmlspecialchars($u['email'] ?? 'N/A') ?></div>
                                <div class="col-md-6"><strong>Phone:</strong><br><?= htmlspecialchars($u['phone'] ?? 'N/A') ?></div>
                                <div class="col-md-6"><strong>RERA Deduction Wallet:</strong><br>₹<?= number_format($u['rera_deduction_wallet'] ?? 0, 2) ?></div>
                                <div class="col-12">
                                    <hr>
                                    <h6>Recent RERA Requests</h6>
                                    <?php if (!empty($result['requests'])): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead><tr><th>ID</th><th>Amount</th><th>Status</th><th>RERA #</th><th>Date</th></tr></thead>
                                                <tbody>
                                                    <?php foreach ($result['requests'] as $req): ?>
                                                    <tr>
                                                        <td>#<?= $req['id'] ?></td>
                                                        <td>₹<?= number_format((float)$req['deducted_amount'], 2) ?></td>
                                                        <td><span class="badge bg-<?= $req['status'] === 'approved' ? 'success' : ($req['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= htmlspecialchars($req['status']) ?></span></td>
                                                        <td><?= htmlspecialchars($req['rera_number'] ?? '-') ?></td>
                                                        <td><?= htmlspecialchars($req['created_at'] ?? '') ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No RERA requests found</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($result['message'] ?? 'No record found for the provided RERA number.') ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
