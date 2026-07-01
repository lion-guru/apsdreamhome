<?php $associate = $associate ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-user me-2"></i><?= htmlspecialchars($associate['name'] ?? 'Associate') ?></h1>
        <a href="<?= BASE_URL ?>/admin/associate-extensions" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Info</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th>Name</th><td><?= htmlspecialchars($associate['name'] ?? '') ?></td></tr>
                        <tr><th>Email</th><td><?= htmlspecialchars($associate['email'] ?? '') ?></td></tr>
                        <tr><th>Phone</th><td><?= htmlspecialchars($associate['phone'] ?? '') ?></td></tr>
                        <tr><th>Role</th><td><span class="badge bg-info"><?= htmlspecialchars($associate['role'] ?? '') ?></span></td></tr>
                        <tr><th>Status</th><td><span class="badge bg-<?= ($associate['user_status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($associate['user_status'] ?? '') ?></span></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-star me-2"></i>Extension Data</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th>Points</th><td><span class="badge bg-warning text-dark fs-6"><?= (int)($associate['points'] ?? 0) ?></span></td></tr>
                        <tr><th>Badges</th><td><?= htmlspecialchars($associate['badges'] ?? '—') ?></td></tr>
                        <tr><th>Training Progress</th><td><div class="progress"><div class="progress-bar bg-info" style="width:<?= (int)($associate['training_progress'] ?? 0) ?>%"><?= (int)($associate['training_progress'] ?? 0) ?>%</div></div></td></tr>
                        <tr><th>Total Visits</th><td><?= (int)($associate['total_visits'] ?? 0) ?></td></tr>
                        <tr><th>Total Appointments</th><td><?= (int)($associate['total_appointments'] ?? 0) ?></td></tr>
                        <tr><th>Total Referrals</th><td><?= (int)($associate['total_referrals'] ?? 0) ?></td></tr>
                    </table></div>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Extension</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/associate-extensions/update-points/<?= $associate['id'] ?? 0 ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Points</label>
                            <input type="number" name="points" class="form-control" value="<?= (int)($associate['points'] ?? 0) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Badges (comma-separated)</label>
                            <input type="text" name="badges" class="form-control" value="<?= htmlspecialchars($associate['badges'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Training Progress (%)</label>
                            <input type="number" name="training_progress" class="form-control" min="0" max="100" value="<?= (int)($associate['training_progress'] ?? 0) ?>">
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
