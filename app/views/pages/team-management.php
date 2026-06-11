<?php
$page_title = $page_title ?? 'Team Management - APS Dream Home';
$page_description = $page_description ?? 'Manage your team members and track performance';
$team_stats = $team_stats ?? ['total_members' => 0, 'active_members' => 0, 'new_members' => 0, 'total_commission' => 0, 'level_distribution' => [], 'growth_rate' => 0];
$recent_activities = $recent_activities ?? [];
$top_performers = $top_performers ?? [];
$error = $error ?? null;
$base = $base ?? BASE_URL;
?>

<section class="py-5 bg-gradient-success text-white position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, #0f3443 0%, #34e89e 100%);"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3"><i class="fas fa-users me-3"></i>Team Management</h1>
                <p class="lead mb-0">Manage members, track performance, and grow your network</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                    <i class="fas fa-user-plus me-1"></i> Add Member
                </button>
            </div>
        </div>
    </div>
</section>

<?php if ($error): ?>
<div class="container mt-3">
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<section class="py-4">
    <div class="container">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-primary mb-2"><?= htmlspecialchars(number_format($team_stats['total_members'] ?? 0)) ?></div>
                        <h6 class="text-muted mb-0">Total Members</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-success mb-2"><?= htmlspecialchars(number_format($team_stats['active_members'] ?? 0)) ?></div>
                        <h6 class="text-muted mb-0">Active (30d)</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-warning mb-2"><?= htmlspecialchars(number_format($team_stats['new_members'] ?? 0)) ?></div>
                        <h6 class="text-muted mb-0">New This Month</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-info mb-2">&#8377; <?= htmlspecialchars($team_stats['total_commission'] ?? 0) ?></div>
                        <h6 class="text-muted mb-0">Total Commission</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-success"></i>Recent Activities</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($recent_activities)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_activities as $activity): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= htmlspecialchars($activity['user_name'] ?? 'Unknown') ?></strong>
                                        <span class="badge bg-info ms-1"><?= htmlspecialchars($activity['action'] ?? 'N/A') ?></span>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars(date('d M H:i', strtotime($activity['created_at'] ?? 'now'))) ?></small>
                                </div>
                                <p class="small text-muted mb-0 mt-1"><?= htmlspecialchars($activity['description'] ?? '') ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No recent activities</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Top Performers</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($top_performers)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($top_performers as $i => $performer): ?>
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 text-center" style="width:30px;">
                                        <?php if ($i === 0): ?><i class="fas fa-crown text-warning"></i>
                                        <?php elseif ($i === 1): ?><i class="fas fa-medal text-secondary"></i>
                                        <?php elseif ($i === 2): ?><i class="fas fa-medal text-bronze"></i>
                                        <?php else: ?><span class="text-muted">#<?= $i + 1 ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong><?= htmlspecialchars($performer['name'] ?? 'Unknown') ?></strong>
                                        <small class="text-muted d-block">Level <?= htmlspecialchars($performer['level'] ?? 0) ?> &middot; Team: <?= htmlspecialchars($performer['team_size'] ?? 0) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-success fw-bold">&#8377; <?= htmlspecialchars(number_format(intval($performer['commission'] ?? 0))) ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No performers data yet</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-layer-group me-2 text-info"></i>Level Distribution</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <?php $levels = $team_stats['level_distribution'] ?? []; ?>
                        <?php if (!empty($levels)): ?>
                        <?php foreach ($levels as $level): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Level <?= htmlspecialchars($level['level'] ?? 0) ?></span>
                            <span class="badge bg-primary rounded-pill"><?= htmlspecialchars($level['count'] ?? 0) ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <p class="text-muted text-center mb-0 small">No level data</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add Team Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMemberForm">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <select name="position" class="form-control">
                            <option value="left">Left</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitAddMember()">
                    <i class="fas fa-check me-1"></i> Add Member
                </button>
            </div>
        </div>
    </div>
</div>

<script>
async function submitAddMember() {
    const form = document.getElementById('addMemberForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    try {
        const res = await fetch('<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/team/add-member', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            alert('Member added successfully!');
            location.reload();
        } else {
            alert('Error: ' + (result.message ?? 'Unknown error'));
        }
    } catch (e) {
        alert('Failed to add member. Please try again.');
    }
}
</script>
