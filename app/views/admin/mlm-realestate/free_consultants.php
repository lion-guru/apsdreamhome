<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-user-friends me-2"></i>Free Consultants</h1>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#registerModal"><i class="fas fa-user-plus me-1"></i>Register Consultant</button>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Wallet</th><th>Cumulative Sales</th><th>Payout Slab</th><th>RERA</th><th>Registered</th></tr></thead>
                    <tbody>
                        <?php if (empty($consultants ?? [])): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-user-friends fa-3x text-muted mb-3" class="style-82835"></i>
                                <h5 class="text-muted">No free consultants found</h5>
                                <p class="text-muted mb-3">Register consultants who operate outside the standard associate hierarchy.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($consultants as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><strong><?= htmlspecialchars($c['name'] ?? '') ?></strong></td>
                            <td><?= htmlspecialchars($c['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($c['phone'] ?? '') ?></td>
                            <td>â‚¹<?= number_format((float)($c['wallet_balance'] ?? 0), 2) ?></td>
                            <td>â‚¹<?= number_format((float)($c['cumulative_sales'] ?? 0), 2) ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($c['associate_payout_slab'] ?? '5%') ?></span></td>
                            <td><span class="badge bg-<?= $c['is_rera_approved'] ? 'success' : 'secondary' ?>"><?= $c['is_rera_approved'] ? 'Yes' : 'No' ?></span></td>
                            <td><?= htmlspecialchars($c['created_at'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="registerModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="<?= BASE_URL ?>/admin/mlm-realestate/free-consultants/register">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="modal-header"><h5 class="modal-title">Register Free Consultant</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Sponsor ID (optional)</label><input type="number" name="sponsor_id" class="form-control" placeholder="User ID of sponsor"></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Register</button></div>
    </form>
</div></div></div>