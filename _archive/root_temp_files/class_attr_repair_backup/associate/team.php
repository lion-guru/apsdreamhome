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
    'associate' => '#94a3b8', 'senior_associate' => '#f59e0b', 'bdm' => '#3b82f6',
    'sr_bdm' => '#06b6d4', 'vice_president' => '#8b5cf6', 'president' => '#ef4444', 'site_manager' => '#10b981',
];
?>

<style>
/* Modern Glassmorphism & Micro-animations */
:root {
    --glass-bg: rgba(255, 255, 255, 0.9);
    --glass-border: rgba(255, 255, 255, 0.2);
    --card-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
}

body {
    background-color: #f8fafc;
}

.modern-header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 16px;
    padding: 25px 30px;
    margin-bottom: 30px;
    color: #f8fafc;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-card-glass {
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    padding: 24px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--card-shadow);
    height: 100%;
    display: flex;
    align-items: center;
    gap: 20px;
}
.stat-card-glass:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.stat-icon-wrapper {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    color: #fff;
    background: var(--icon-bg);
    box-shadow: 0 8px 16px var(--icon-shadow);
    flex-shrink: 0;
}

.stat-value {
    font-size: 1.6rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}

.stat-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
}

.list-card {
    background: #fff;
    border-radius: 16px;
    border: none;
    box-shadow: var(--card-shadow);
    overflow: hidden;
}

.modern-table {
    width: 100%;
    margin-bottom: 0;
}
.modern-table th {
    background: #f8fafc;
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
}
.modern-table td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
}
.modern-table tr:last-child td {
    border-bottom: none;
}
.modern-table tr:hover td {
    background: #f8fafc;
}

.rank-pill {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #fff;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
</style>

<div class="container-fluid px-4 py-3">
    <!-- Modern Header -->
    <div class="modern-header flex-wrap gap-3">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-users text-primary me-2" class="style-37609"></i><?= __('assoc_team_title', [], 'My Team') ?></h4>
            <div class="text-white-50 small"><?= __('assoc_team_subtitle', [], 'Manage and track your team members\' performance') ?></div>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/associate/dashboard" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-arrow-left me-2"></i><?= __('assoc_team_dashboard', [], 'Dashboard') ?>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-glass" class="style-61710">
                <div class="stat-icon-wrapper"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-value"><?= $team_stats['total'] ?></div>
                    <div class="stat-label"><?= __('assoc_team_total_members', [], 'Total Members') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-glass" class="style-47504">
                <div class="stat-icon-wrapper"><i class="fas fa-user-check"></i></div>
                <div>
                    <div class="stat-value"><?= $team_stats['active'] ?></div>
                    <div class="stat-label"><?= __('assoc_team_active', [], 'Active') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-glass" class="style-58217">
                <div class="stat-icon-wrapper"><i class="fas fa-chart-line"></i></div>
                <div>
                    <div class="stat-value">₹<?= number_format($team_stats['total_sales']) ?></div>
                    <div class="stat-label"><?= __('assoc_team_sales', [], 'Team Sales') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-glass" class="style-48682">
                <div class="stat-icon-wrapper"><i class="fas fa-coins"></i></div>
                <div>
                    <div class="stat-value">₹<?= number_format($team_stats['total_commission']) ?></div>
                    <div class="stat-label"><?= __('assoc_team_earned', [], 'Team Earned') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Members Table -->
    <div class="list-card">
        <div class="px-4 py-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white">
            <h5 class="fw-bold m-0 text-dark">
                <i class="fas fa-list text-primary me-2"></i><?= __('assoc_team_members_count', [], 'Team Members') ?> 
                <span class="badge bg-secondary ms-2 rounded-pill"><?= $team_count ?></span>
            </h5>
            <div class="d-flex gap-3">
                <div class="input-group input-group-sm rounded-pill overflow-hidden shadow-sm border">
                    <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="teamSearch" class="form-control border-0 shadow-none" placeholder="<?= __('assoc_team_search', [], 'Search by name...') ?>" autocomplete="off" class="style-869">
                </div>
                <a href="<?= BASE_URL ?>/become-associate" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm" target="_blank">
                    <i class="fas fa-user-plus me-1"></i><?= __('assoc_team_invite', [], 'Invite') ?>
                </a>
            </div>
        </div>
        
        <div class="card-body p-0">
            <?php if (empty($team)): ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle" class="style-78270">
                            <i class="fas fa-user-friends fa-3x text-muted opacity-50"></i>
                        </div>
                    </div>
                    <h5 class="text-dark fw-bold"><?= __('assoc_team_empty', [], 'No Team Members Yet') ?></h5>
                    <p class="text-muted mb-4"><?= __('assoc_team_empty_desc', [], 'Share your referral link to start building your team.') ?></p>
                    <a href="<?= BASE_URL ?>/become-associate" class="btn btn-primary rounded-pill px-4 shadow-sm" target="_blank">
                        <i class="fas fa-share-alt me-2"></i><?= __('assoc_team_share_link', [], 'Share Referral Link') ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th width="5%"><?= __('assoc_team_th_hash', [], '#') ?></th>
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
                                <td class="text-muted fw-bold"><?= $i + 1 ?></td>
                                <td>
                                    <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($m['name'] ?? '') ?></div>
                                    <div class="small text-muted"><i class="far fa-envelope me-1"></i><?= htmlspecialchars($m['email'] ?? '') ?></div>
                                </td>
                                <td>
                                    <span class="rank-pill" class="style-19790">
                                        <?= htmlspecialchars($label ?? '') ?>
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-dark"><?= (int)($m['team_size'] ?? 0) ?></td>
                                <td class="text-end fw-bold text-success">₹<?= number_format((float)($m['lifetime_sales'] ?? 0)) ?></td>
                                <td class="text-end fw-bold text-primary">₹<?= number_format((float)($m['total_earned'] ?? 0)) ?></td>
                                <td>
                                    <?php $isAct = ($m['status'] ?? '') === 'active'; ?>
                                    <span class="badge rounded-pill bg-<?= $isAct ? 'success' : 'secondary' ?> bg-opacity-10 text-<?= $isAct ? 'success' : 'secondary' ?> px-3 border border-<?= $isAct ? 'success' : 'secondary' ?>">
                                        <i class="fas fa-circle fa-xs me-1"></i><?= ucfirst($m['status'] ?? 'active') ?>
                                    </span>
                                </td>
                                <td class="small text-muted fw-bold">
                                    <i class="far fa-calendar-alt me-1"></i><?= date('d M Y', strtotime($m['created_at'] ?? 'now')) ?>
                                </td>
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
    document.querySelectorAll('.modern-table tbody tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
    });
});
</script>
