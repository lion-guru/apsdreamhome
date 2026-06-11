<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-users"></i> My Team</h1>
        <div>
            <a href="<?= BASE_URL ?>/associate/team/add" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Add Member
            </a>
            <a href="<?= BASE_URL ?>/associate/team/performance" class="btn btn-info ms-2">
                <i class="fas fa-chart-bar"></i> Performance
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Team</h5>
                    <h2><?= $team_count ?? 0 ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header">
            <h5>Team Members</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover table-responsive">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($team)): ?>
                        <tr><td colspan="6" class="text-center">No team members yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($team as $i => $member): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($member['name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($member['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($member['phone'] ?? '') ?></td>
                            <td><span class="badge bg-<?= ($member['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($member['status'] ?? 'active') ?></span></td>
                            <td><?= date('d M Y', strtotime($member['created_at'] ?? 'now')) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>