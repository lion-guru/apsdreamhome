<?php
$page_title = $page_title ?? "MLM Genealogy";
$genealogyData = $genealogyData ?? ['members' => [], 'stats' => []];
$base = defined('BASE_URL') ? BASE_URL : '';
$members = $genealogyData['members'] ?? [];
$stats = $genealogyData['stats'] ?? [];
$totalMembers = (int)($stats['total'] ?? 0);
$activeMembers = (int)($stats['active'] ?? 0);
$totalVolume = (float)($stats['total_volume'] ?? 0);
$maxDepth = (int)($stats['max_depth'] ?? 0);

$rankColors = [
    'associate' => '#94a3b8', 'bronze' => '#a16207', 'silver' => '#94a3b8',
    'gold' => '#ca8a04', 'platinum' => '#0891b2', 'diamond' => '#7c3aed',
];
$rankIcons = [
    'associate' => 'fa-user', 'bronze' => 'fa-medal', 'silver' => 'fa-award',
    'gold' => 'fa-trophy', 'platinum' => 'fa-gem', 'diamond' => 'fa-crown',
];

$sponsorTree = [];
foreach ($members as $m) {
    $sponsorTree[$m['user_id']][] = $m;
}

function renderGenealogyNode($userId, $sponsorTree, $rankColors, $rankIcons, $base, $depth = 0) {
    $children = $sponsorTree[$userId] ?? [];
    if (empty($children) && $depth > 0) return '';
    $html = '';
    if ($depth > 0) $html .= '<ul class="genealogy-tree" style="list-style:none;padding-left:24px;margin:0;">';
    if ($depth === 0) $html .= '<ul class="genealogy-tree" style="list-style:none;padding-left:0;margin:0;">';
    foreach ($children as $m) {
        $level = strtolower($m['current_level'] ?? 'associate');
        $color = $rankColors[$level] ?? '#94a3b8';
        $icon = $rankIcons[$level] ?? 'fa-user';
        $name = htmlspecialchars($m['name'] ?? 'Unknown');
        $email = htmlspecialchars($m['email'] ?? '');
        $refCode = htmlspecialchars($m['referral_code'] ?? '');
        $commission = number_format((float)$m['total_commission'] ?? 0);
        $sales = number_format((float)$m['lifetime_sales'] ?? 0);
        $html .= '<li style="margin:4px 0;">';
        $html .= '<div class="d-inline-flex align-items-center border rounded px-3 py-2 genealogy-node" style="border-left:4px solid ' . $color . ' !important;background:#fff;">';
        $html .= '<span class="badge me-2" style="background:' . $color . ';color:#fff;"><i class="fas ' . $icon . '"></i></span>';
        $html .= '<span class="fw-bold small me-2">' . $name . '</span>';
        $html .= '<span class="text-muted" style="font-size:0.72rem;">' . $email . '</span>';
        $html .= '</span></div>';
        $html .= '<div class="ms-4 mt-1" style="font-size:0.72rem;color:#6b7280;">';
        $html .= '<span class="me-3"><i class="fas fa-tag me-1"></i>' . $refCode . '</span>';
        $html .= '<span class="me-3"><i class="fas fa-rupee-sign me-1"></i>' . $commission . ' earned</span>';
        $html .= '<span class="me-3"><i class="fas fa-chart-line me-1"></i>' . $sales . ' volume</span>';
        $html .= '</div>';
        $childHtml = renderGenealogyNode($m['user_id'], $sponsorTree, $rankColors, $rankIcons, $base, $depth + 1);
        $html .= $childHtml;
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-sitemap me-2"></i>MLM Genealogy</h4>
        <div>
            <a href="<?= htmlspecialchars($base) ?>/admin/mlm/tree" class="btn btn-outline-primary btn-sm me-2"><i class="fas fa-project-diagram me-1"></i>Network Tree</a>
            <a href="<?= htmlspecialchars($base) ?>/admin/mlm" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to MLM</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <div style="font-size:1.8rem;font-weight:700;color:var(--primary);"><?= $totalMembers ?></div>
                    <div class="text-muted small">Total Members</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <div style="font-size:1.8rem;font-weight:700;color:#10b981;"><?= $activeMembers ?></div>
                    <div class="text-muted small">Active Members</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <div style="font-size:1.8rem;font-weight:700;color:#f59e0b;">&#8377;<?= number_format($totalVolume / 1000, 1) ?>K</div>
                    <div class="text-muted small">Total Team Volume</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <div style="font-size:1.8rem;font-weight:700;color:#7c3aed;"><?= $maxDepth ?></div>
                    <div class="text-muted small">Generation Depth</div>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
            <h5 class="m-0"><i class="fas fa-search me-2"></i>Search Members</h5>
            <span class="badge bg-primary"><?= $totalMembers ?> members</span>
        </div>
        <div class="aps-cp-card-body">
            <input type="text" id="genealogySearch" class="form-control" placeholder="Search by name, email, or referral code..." autocomplete="off">
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header">
                    <h5 class="m-0"><i class="fas fa-sitemap me-2"></i>Genealogy Tree</h5>
                </div>
                <div class="aps-cp-card-body" style="overflow-x:auto;">
                    <?php
                    $rootMembers = [];
                    foreach ($members as $m) {
                        if (empty($m['sponsor_user_id']) || !isset($sponsorTree[$m['sponsor_user_id']])) {
                            $rootMembers[] = $m;
                        }
                    }
                    if (empty($rootMembers)):
                    ?>
                        <p class="text-muted text-center py-4">No genealogy data found. Members will appear here once they join through referral links.</p>
                    <?php else: ?>
                        <?php foreach ($rootMembers as $rm):
                            $level = strtolower($rm['current_level'] ?? 'associate');
                            $color = $rankColors[$level] ?? '#94a3b8';
                            $icon = $rankIcons[$level] ?? 'fa-user';
                        ?>
                        <div class="mb-4 genealogy-root">
                            <div class="d-inline-flex align-items-center border rounded px-3 py-2 mb-2 genealogy-node" style="border-left:4px solid <?= $color ?> !important;background:#f8fafc;font-size:0.95rem;">
                                <span class="badge me-2" style="background:<?= $color ?>;color:#fff;"><i class="fas <?= $icon ?>"></i></span>
                                <strong class="me-2"><?= htmlspecialchars($rm['name'] ?? 'Unknown') ?></strong>
                                <span class="text-muted small"><?= htmlspecialchars($rm['email'] ?? '') ?></span>
                                <span class="ms-3 badge bg-light text-dark"><i class="fas fa-tag me-1"></i><?= htmlspecialchars($rm['referral_code'] ?? '') ?></span>
                            </div>
                            <div class="ms-3" style="font-size:0.72rem;color:#6b7280;">
                                <span class="me-3"><i class="fas fa-users me-1"></i><?= (int)($rm['direct_referrals'] ?? 0) ?> direct</span>
                                <span class="me-3"><i class="fas fa-sitemap me-1"></i><?= (int)($rm['total_team_size'] ?? 0) ?> team</span>
                                <span class="me-3"><i class="fas fa-rupee-sign me-1"></i><?= number_format((float)$rm['total_commission'] ?? 0) ?> earned</span>
                                <span><i class="fas fa-chart-line me-1"></i>&#8377;<?= number_format((float)$rm['lifetime_sales'] ?? 0) ?> volume</span>
                            </div>
                            <?= renderGenealogyNode($rm['user_id'], $sponsorTree, $rankColors, $rankIcons, $base, 0) ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <h5 class="m-0"><i class="fas fa-list me-2"></i>All Members</h5>
                </div>
                <div class="aps-cp-card-body p-0" style="max-height:500px;overflow-y:auto;">
                    <?php if (empty($members)): ?>
                        <p class="text-muted text-center py-3">No members found.</p>
                    <?php else: ?>
                        <?php foreach ($members as $m):
                            $level = strtolower($m['current_level'] ?? 'associate');
                            $color = $rankColors[$level] ?? '#94a3b8';
                            $icon = $rankIcons[$level] ?? 'fa-user';
                        ?>
                        <div class="d-flex align-items-center border-bottom px-3 py-2 genealogy-member-item">
                            <span class="badge me-2" style="background:<?= $color ?>;color:#fff;min-width:28px;"><i class="fas <?= $icon ?>"></i></span>
                            <div class="flex-grow-1">
                                <div class="small fw-bold"><?= htmlspecialchars($m['name'] ?? 'Unknown') ?></div>
                                <div class="text-muted" style="font-size:0.7rem;"><?= htmlspecialchars($m['email'] ?? '') ?></div>
                            </div>
                            <div class="text-end">
                                <div class="small" style="color:<?= $color ?>;font-weight:600;"><?= htmlspecialchars(ucfirst($level)) ?></div>
                                <div class="text-muted" style="font-size:0.7rem;">&#8377;<?= number_format((float)$m['total_commission'] ?? 0) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="aps-cp-card">
                <div class="aps-cp-card-header">
                    <h5 class="m-0"><i class="fas fa-layer-group me-2"></i>Rank Summary</h5>
                </div>
                <div class="aps-cp-card-body p-0">
                    <?php
                    $rankSummary = [];
                    foreach ($members as $m) {
                        $lv = ucfirst($m['current_level'] ?? 'associate');
                        if (!isset($rankSummary[$lv])) $rankSummary[$lv] = ['count' => 0, 'color' => $rankColors[strtolower($lv)] ?? '#94a3b8'];
                        $rankSummary[$lv]['count']++;
                    }
                    foreach ($rankSummary as $rk => $rv):
                    ?>
                    <div class="d-flex align-items-center justify-content-between border-bottom px-3 py-2">
                        <div class="d-flex align-items-center">
                            <span class="badge me-2" style="background:<?= $rv['color'] ?>;color:#fff;"><i class="fas <?= $rankIcons[strtolower($rk)] ?? 'fa-user' ?>"></i></span>
                            <span class="small fw-bold"><?= htmlspecialchars($rk) ?></span>
                        </div>
                        <span class="badge bg-light text-dark"><?= $rv['count'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('genealogySearch');
    if (!searchInput) return;
    searchInput.addEventListener('input', function() {
        var q = this.value.toLowerCase();
        document.querySelectorAll('.genealogy-member-item').forEach(function(el) {
            var text = el.textContent.toLowerCase();
            el.style.display = text.indexOf(q) > -1 ? '' : 'none';
        });
        document.querySelectorAll('.genealogy-root').forEach(function(el) {
            var text = el.textContent.toLowerCase();
            el.style.display = text.indexOf(q) > -1 ? '' : 'none';
        });
    });
});
</script>

<style>
.genealogy-tree li { position: relative; }
.genealogy-tree li::before {
    content: '';
    position: absolute;
    left: -16px;
    top: 0;
    height: 100%;
    width: 1px;
    background: #e2e8f0;
}
.genealogy-tree li::after {
    content: '';
    position: absolute;
    left: -16px;
    top: 14px;
    width: 12px;
    height: 1px;
    background: #e2e8f0;
}
.genealogy-tree > li::before,
.genealogy-tree > li::after { display: none; }
.genealogy-node { transition: box-shadow 0.2s; }
.genealogy-node:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
</style>
