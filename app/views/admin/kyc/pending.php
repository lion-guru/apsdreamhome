<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-clock me-2"></i>Pending KYC Verifications</h1>
        <a href="<?= BASE_URL ?>/admin/kyc" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>All Documents</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Document Type</th>
                            <th>Document #</th>
                            <th>Issued By</th>
                            <th>Expiry</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($documents ?? [])): ?>
                            <tr><td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5>All Clear!</h5>
                                <p class="mb-3">No pending KYC verifications.</p>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($documents as $d): ?>
                                <tr>
                                    <td><?= $d['id'] ?? '' ?></td>
                                    <td><strong><?= htmlspecialchars($d['user_name'] ?? '') ?></strong></td>
                                    <td><span class="badge bg-info"><?= strtoupper(htmlspecialchars($d['document_type'] ?? '')) ?></span></td>
                                    <td><?= htmlspecialchars($d['document_number'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($d['issued_by'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($d['expiry_date'] ?? '—') ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/kyc/show/<?= $d['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-check"></i> Verify</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
