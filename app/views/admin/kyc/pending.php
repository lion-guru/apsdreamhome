<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-clock me-2"></i>Pending KYC Verifications</h1>
        <a href="<?= BASE_URL ?>/admin/kyc" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>All Requests</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>PAN</th>
                            <th>Aadhaar</th>
                            <th>Legal Name</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests ?? [])): ?>
                            <tr><td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5>All Clear!</h5>
                                <p class="mb-3">No pending KYC verifications.</p>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($requests as $r): ?>
                                <tr>
                                    <td><?= $r['id'] ?? '' ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($r['user_name'] ?? '') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($r['user_email'] ?? '') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($r['pan_number'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($r['aadhaar_number'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($r['legal_name'] ?? '—') ?></td>
                                    <td><?= date('M j, Y', strtotime($r['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/kyc/show/<?= $r['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-check"></i> Verify</a>
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
