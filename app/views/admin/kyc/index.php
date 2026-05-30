<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-id-card me-2"></i>KYC Documents</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/kyc/pending" class="btn btn-outline-warning"><i class="fas fa-clock me-1"></i>Pending Verifications</a>
        </div>
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
                            <th>Issue Date</th>
                            <th>Expiry Date</th>
                            <th>Verification</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($documents ?? [])): ?>
                            <tr><td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-id-card fa-3x text-muted mb-3"></i>
                                <h5>No KYC Documents</h5>
                                <p class="mb-3">No KYC documents uploaded yet.</p>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($documents as $d): ?>
                                <tr>
                                    <td><?= $d['id'] ?? '' ?></td>
                                    <td><strong><?= htmlspecialchars($d['user_name'] ?? '') ?></strong></td>
                                    <td><span class="badge bg-info"><?= strtoupper(htmlspecialchars($d['document_type'] ?? '')) ?></span></td>
                                    <td><?= htmlspecialchars($d['document_number'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($d['issued_by'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($d['issue_date'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($d['expiry_date'] ?? '—') ?></td>
                                    <td>
                                        <span class="badge bg-<?= match($d['verification_status'] ?? 'pending') { 'verified' => 'success', 'rejected' => 'danger', 'pending' => 'warning', default => 'secondary' } ?>">
                                            <?= ucfirst($d['verification_status'] ?? 'Pending') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/kyc/show/<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
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
