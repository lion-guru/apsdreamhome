<?php
$page_title = $page_title ?? 'My Team';
$team = $team ?? [];
$team_count = $team_count ?? 0;
$team_stats = $team_stats ?? ['total' => 0, 'active' => 0, 'total_sales' => 0, 'total_commission' => 0];

$rankColors = [
    'associate' => '#94a3b8', 'senior_associate' => '#a16207', 'bdm' => '#ca8a04',
    'sr_bdm' => '#0891b2', 'vice_president' => '#0f766e', 'president' => '#dc2626', 'site_manager' => '#059669',
];
$rankLabels = [
    'associate' => 'Associate', 'senior_associate' => 'Sr. Associate', 'bdm' => 'BDM',
    'sr_bdm' => 'Sr. BDM', 'vice_president' => 'Vice President', 'president' => 'President', 'site_manager' => 'Site Manager',
];
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-users text-primary me-2"></i>My Team</h4>
            <small class="text-muted">Manage and track your team members' performance</small>
        </div>
        <a href="<?= BASE_URL ?>/associate/dashboard" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="stat-icon" style="background:rgba(99,102,241,0.1);color:#6366f1;width:50px;height:50px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-size:1.3rem;margin:0 auto 10px;">
                    <i class="fas fa-users"></i>
                </div>
                <div style="font-size:1.8rem;font-weight:700;color:#1e293b;"><?= $team_stats['total'] ?></div>
                <div class="text-muted small">Total Members</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="stat-icon" style="background:rgba(16,185,129,0.1);color:#10b981;width:50px;height:50px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-size:1.3rem;margin:0 auto 10px;">
                    <i class="fas fa-user-check"></i>
                </div>
                <div style="font-size:1.8rem;font-weight:700;color:#1e293b;"><?= $team_stats['active'] ?></div>
                <div class="text-muted small">Active</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="stat-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;width:50px;height:50px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-size:1.3rem;margin:0 auto 10px;">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div style="font-size:1.8rem;font-weight:700;color:#1e293b;">₹<?= number_format($team_stats['total_sales']) ?></div>
                <div class="text-muted small">Team Sales</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="stat-icon" style="background:rgba(20,184,166,0.1);color:#14b8a6;width:50px;height:50px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-size:1.3rem;margin:0 auto 10px;">
                    <i class="fas fa-coins"></i>
                </div>
                <div style="font-size:1.8rem;font-weight:700;color:#1e293b;">₹<?= number_format($team_stats['total_commission']) ?></div>
                <div class="text-muted small">Team Earned</div>
            </div>
        </div>
    </div>

    <!-- Team Members Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-list text-primary me-2"></i>Team Members (<?= $team_count ?>)</h5>
                <div class="d-flex gap-2">
                    <input type="text" id="teamSearch" class="form-control form-control-sm" style="max-width:250px;" placeholder="Search by name..." autocomplete="off">
                    <a href="<?= BASE_URL ?>/become-associate" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fas fa-user-plus me-1"></i>Invite
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($team)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Team Members Yet</h5>
                    <p class="text-muted">Share your referral link to start building your team.</p>
                    <a href="<?= BASE_URL ?>/become-associate" class="btn btn-primary" target="_blank">
                        <i class="fas fa-share-alt me-1"></i>Share Referral Link
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Member</th>
                                <th>Rank</th>
                                <th class="text-end">Team Size</th>
                                <th class="text-end">Lifetime Sales</th>
                                <th class="text-end">Earned</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($team as $i => $m):
                                $rank = strtolower($m['rank'] ?? 'associate');
                                $color = $rankColors[$rank] ?? '#94a3b8';
                                $label = $rankLabels[$rank] ?? ucfirst(str_replace('_', ' ', $rank));
                            ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($m['name'] ?? '') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($m['email'] ?? '') ?></small>
                                </td>
                                <td>
                                    <span class="badge" style="background:<?= $color ?>;color:#fff;"><?= htmlspecialchars($label) ?></span>
                                </td>
                                <td class="text-end"><?= (int)($m['team_size'] ?? 0) ?></td>
                                <td class="text-end">₹<?= number_format((float)($m['lifetime_sales'] ?? 0)) ?></td>
                                <td class="text-end">₹<?= number_format((float)($m['total_earned'] ?? 0)) ?></td>
                                <td>
                                    <span class="badge bg-<?= ($m['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($m['status'] ?? 'active') ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?= date('d M Y', strtotime($m['created_at'] ?? 'now')) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('teamSearch')?.addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('#teamTable tbody tr, .table tbody tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
    });
});
</script>
