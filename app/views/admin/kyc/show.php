<?php $d = $document ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-id-card me-2"></i>KYC: <?= strtoupper(htmlspecialchars($d['document_type'] ?? '')) ?> - <?= htmlspecialchars($d['document_number'] ?? '') ?></h1>
        <a href="<?= BASE_URL ?>/admin/kyc" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Document Details</h5></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><th>ID</th><td>#<?= $d['id'] ?? '' ?></td></tr>
                        <tr><th>Document Type</th><td><span class="badge bg-info fs-6"><?= strtoupper(htmlspecialchars($d['document_type'] ?? '')) ?></span></td></tr>
                        <tr><th>Document Number</th><td><strong><?= htmlspecialchars($d['document_number'] ?? '—') ?></strong></td></tr>
                        <tr><th>Issued By</th><td><?= htmlspecialchars($d['issued_by'] ?? '—') ?></td></tr>
                        <tr><th>Issue Date</th><td><?= htmlspecialchars($d['issue_date'] ?? '—') ?></td></tr>
                        <tr><th>Expiry Date</th><td><?= htmlspecialchars($d['expiry_date'] ?? '—') ?></td></tr>
                        <tr><th>Signature Hash</th><td><code><?= htmlspecialchars(substr($d['signature_hash'] ?? '', 0, 20) ?: '—') ?></code></td></tr>
                        <tr><th>Signed At</th><td><?= htmlspecialchars($d['signed_at'] ?? '—') ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-user me-2"></i>User Info</h5></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><th>Name</th><td><?= htmlspecialchars($d['user_name'] ?? '') ?></td></tr>
                        <tr><th>Email</th><td><?= htmlspecialchars($d['user_email'] ?? '') ?></td></tr>
                        <tr><th>Phone</th><td><?= htmlspecialchars($d['user_phone'] ?? '') ?></td></tr>
                    </table>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Verification</h5></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><th>Status</th><td><span class="badge bg-<?= match($d['verification_status'] ?? 'pending') { 'verified' => 'success', 'rejected' => 'danger', default => 'warning' } ?> fs-6"><?= ucfirst($d['verification_status'] ?? 'Pending') ?></span></td></tr>
                        <tr><th>Verified By</th><td><?= htmlspecialchars($d['verified_by'] ?? '—') ?></td></tr>
                        <tr><th>Verified At</th><td><?= htmlspecialchars($d['verified_at'] ?? '—') ?></td></tr>
                    </table>
                    <form method="post" action="<?= BASE_URL ?>/admin/kyc/verify/<?= $d['id'] ?? 0 ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <select name="status" class="form-select">
                                <option value="verified">Verify</option>
                                <option value="rejected">Reject</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-check me-1"></i>Submit Verification</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
