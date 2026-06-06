<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-id-card me-2"></i>KYC Requests</h1>
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
                            <th>PAN</th>
                            <th>Aadhaar</th>
                            <th>Legal Name</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests ?? [])): ?>
                            <tr><td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-id-card fa-3x text-muted mb-3"></i>
                                <h5>No KYC Requests</h5>
                                <p class="mb-3">No KYC requests submitted yet.</p>
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
                                    <td>
                                        <span class="badge bg-<?= match($r['status'] ?? 'pending') { 'approved' => 'success', 'rejected' => 'danger', 'pending' => 'warning', default => 'secondary' } ?>">
                                            <?= ucfirst($r['status'] ?? 'Pending') ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($r['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/kyc/show/<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
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
