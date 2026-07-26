<?php
$page_title = $page_title ?? __('assoc_team_title', [], 'My Team');
$team = $team ?? [];
$team_count = $team_count ?? 0;
$team_stats = $team_stats ?? ['total' => 0, 'active' => 0, 'total_sales' => 0, 'total_commission' => 0];

$rankLabels = [
    'associate' => __('assoc_rank_associate', [], 'Associate'),
    'senior_associate' => __('assoc_rank_sr_associate', [], 'Sr. Associate'),
    'bdm' => __('assoc_rank_bdm', [], 'BDM'),
    'sr_bdm' => __('assoc_rank_sr_bdm', [], 'Sr. BDM'),
    'vice_president' => __('assoc_rank_vp', [], 'Vice President'),
    'president' => __('assoc_rank_president', [], 'President'),
    'site_manager' => __('assoc_rank_site_manager', [], 'Site Manager'),
];
$rankColors = [
    'associate' => '#94a3b8', 'senior_associate' => '#d97706', 'bdm' => '#ca8a04',
    'sr_bdm' => '#0891b2', 'vice_president' => '#0f766e', 'president' => '#dc2626', 'site_manager' => '#059669',
];
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-users text-primary me-2"></i><?= __('assoc_team_title', [], 'My Team') ?></h4>
            <small class="text-muted"><?= __('assoc_team_subtitle', [], 'Manage and track your team members\' performance') ?></small>
        </div>
        <a href="<?= BASE_URL ?>/associate/dashboard" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i><?= __('assoc_team_dashboard', [], 'Dashboard') ?>
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
                <div class="text-muted small"><?= __('assoc_team_total_members', [], 'Total Members') ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="stat-icon" style="background:rgba(16,185,129,0.1);color:#10b981;width:50px;height:50px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-size:1.3rem;margin:0 auto 10px;">
                    <i class="fas fa-user-check"></i>
                </div>
                <div style="font-size:1.8rem;font-weight:700;color:#1e293b;"><?= $team_stats['active'] ?></div>
                <div class="text-muted small"><?= __('assoc_team_active', [], 'Active') ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="stat-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;width:50px;height:50px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-size:1.3rem;margin:0 auto 10px;">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div style="font-size:1.8rem;font-weight:700;color:#1e293b;">₹<?= number_format($team_stats['total_sales']) ?></div>
                <div class="text-muted small"><?= __('assoc_team_sales', [], 'Team Sales') ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="stat-icon" style="background:rgba(20,184,166,0.1);color:#14b8a6;width:50px;height:50px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-size:1.3rem;margin:0 auto 10px;">
                    <i class="fas fa-coins"></i>
                </div>
                <div style="font-size:1.8rem;font-weight:700;color:#1e293b;">₹<?= number_format($team_stats['total_commission']) ?></div>
                <div class="text-muted small"><?= __('assoc_team_earned', [], 'Team Earned') ?></div>
            </div>
        </div>
    </div>

    <!-- Team Members Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-list text-primary me-2"></i><?= __('assoc_team_members_count', [], 'Team Members') ?> (<?= $team_count ?>)</h5>
                <div class="d-flex gap-2">
                    <input type="text" id="teamSearch" class="form-control form-control-sm" style="max-width:250px;" placeholder="<?= __('assoc_team_search', [], 'Search by name...') ?>" autocomplete="off">
                    <a href="<?= BASE_URL ?>/become-associate" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fas fa-user-plus me-1"></i><?= __('assoc_team_invite', [], 'Invite') ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($team)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted"><?= __('assoc_team_empty', [], 'No Team Members Yet') ?></h5>
                    <p class="text-muted"><?= __('assoc_team_empty_desc', [], 'Share your referral link to start building your team.') ?></p>
                    <a href="<?= BASE_URL ?>/become-associate" class="btn btn-primary" target="_blank">
                        <i class="fas fa-share-alt me-1"></i><?= __('assoc_team_share_link', [], 'Share Referral Link') ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th><?= __('assoc_team_th_hash', [], '#') ?></th>
                                <th><?= __('assoc_team_th_member', [], 'Member') ?></th>
                                <th><?= __('assoc_team_th_rank', [], 'Rank') ?></th>
                                <th class="text-end"><?= __('assoc_team_th_team_size', [], 'Team Size') ?></th>
                                <th class="text-end"><?= __('assoc_team_th_sales', [], 'Lifetime Sales') ?></th>
                                <th class="text-end"><?= __('assoc_team_th_earned', [], 'Earned') ?></th>
                                <th><?= __('assoc_team_th_status', [], 'Status') ?></th>
                                <th><?= __('assoc_team_th_joined', [], 'Joined') ?></th>
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
