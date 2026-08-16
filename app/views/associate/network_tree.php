<?php
$page_title = $page_title ?? __('assoc_net_tree', [], 'Network Tree');
$treeData = $treeData ?? ['nodes' => [], 'stats' => []];
$base = defined('BASE_URL') ? BASE_URL : '';
$nodes = $treeData['nodes'] ?? [];
$stats = $treeData['stats'] ?? [];
$totalDownline = (int)($stats['total_downline'] ?? 0);
$leftCount = (int)($stats['left_count'] ?? 0);
$rightCount = (int)($stats['right_count'] ?? 0);

$rankColors = [
    'associate' => '#94a3b8', 'senior_associate' => '#d97706', 'bdm' => '#ca8a04',
    'sr_bdm' => '#0891b2', 'vice_president' => '#0f766e', 'president' => '#dc2626', 'site_manager' => '#059669',
];
$rankIcons = [
    'associate' => 'fa-user', 'senior_associate' => 'fa-medal', 'bdm' => 'fa-award',
    'sr_bdm' => 'fa-trophy', 'vice_president' => 'fa-gem', 'president' => 'fa-crown', 'site_manager' => 'fa-star',
];
$rankLabels = [
    'associate' => 'Associate', 'senior_associate' => 'Sr. Associate', 'bdm' => 'BDM',
    'sr_bdm' => 'Sr. BDM', 'vice_president' => 'Vice President', 'president' => 'President', 'site_manager' => 'Site Manager',
];

$byParent = [];
foreach ($nodes as $n) {
    $pid = $n['parent_id'] ?? null;
    $byParent[$pid][] = $n;
}
$rootNodes = $byParent[null] ?? [];

// Count total hierarchy depth
function countDepth($parentId, $byParent, $depth = 0) {
    $children = $byParent[$parentId] ?? [];
    if (empty($children)) return $depth;
    $max = $depth;
    foreach ($children as $c) {
        $d = countDepth($c['id'] ?? null, $byParent, $depth + 1);
        if ($d > $max) $max = $d;
    }
    return $max;
}
$maxDepth = 0;
foreach ($rootNodes as $rn) {
    $d = countDepth($rn['id'] ?? null, $byParent, 1);
    if ($d > $maxDepth) $maxDepth = $d;
}

// Total team commission
$totalCommission = 0;
foreach ($nodes as $n) {
    $totalCommission += (float)($n['total_commission'] ?? 0);
}
?>

<style>
.tree-node { position: relative; }
.tree-node .card { transition: all 0.25s ease; cursor: pointer; }
.tree-node .card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important; }
.tree-node .card:hover .tree-tooltip { display: block; }
.tree-connector { position: relative; }
.tree-connector::before { content:''; position:absolute; left:50%; top:0; width:2px; height:100%; background:#cbd5e1; }
.tree-h-line { height:2px; background:#cbd5e1; position:relative; }
.tree-v-line { width:2px; background:#cbd5e1; margin:0 auto; }
.rank-pulse { animation: pulse 2s infinite; }
@keyframes pulse { 0%,100% { box-shadow: 0 0 0 0 rgba(99,102,241,0.3); } 50% { box-shadow: 0 0 0 8px rgba(99,102,241,0); } }
.leg-badge { font-size:0.6rem; padding:2px 8px; border-radius:10px; font-weight:700; letter-spacing:0.5px; }
.gen-badge { font-size:0.55rem; padding:1px 6px; border-radius:8px; font-weight:700; background:rgba(99,102,241,0.15); color:#6366f1; }
.overflow-x-auto { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.tree-tooltip {
    display: none; position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%);
    background: #1e293b; color: #fff; padding: 8px 12px; border-radius: 8px;
    font-size: 0.72rem; white-space: nowrap; z-index: 10; pointer-events: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.tree-tooltip::after {
    content: ''; position: absolute; top: 100%; left: 50%; transform: translateX(-50%);
    border: 5px solid transparent; border-top-color: #1e293b;
}
</style>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-project-diagram text-primary me-2"></i><?php echo __('assoc_net_tree', [], 'Network Tree'); ?></h4>
            <small class="text-muted"><?php echo __('assoc_net_subtitle', [], 'Your complete downline hierarchy with performance data'); ?></small>
        </div>
        <a href="<?= BASE_URL ?>/associate/dashboard" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i><?php echo __('assoc_net_dashboard', [], 'Dashboard'); ?>
        </a>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3 h-100">
                <div class="style-15583"><?= $totalDownline ?></div>
                <div class="text-muted small fw-bold"><?php echo __('assoc_net_total_downline', [], 'Total Downline'); ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3 h-100">
                <div class="style-31497"><?= $leftCount ?></div>
                <div class="text-muted small fw-bold"><?php echo __('assoc_net_left_leg', [], 'Left Leg'); ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3 h-100">
                <div class="style-8188"><?= $rightCount ?></div>
                <div class="text-muted small fw-bold"><?php echo __('assoc_net_right_leg', [], 'Right Leg'); ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3 h-100">
                <div class="style-86698"><?= $maxDepth ?></div>
                <div class="text-muted small fw-bold"><?php echo __('assoc_net_max_depth', [], 'Max Depth'); ?></div>
            </div>
        </div>
    </div>

    <!-- Team Earnings Summary -->
    <div class="card border-0 shadow-sm mb-4" class="style-49239">
        <div class="card-body py-3 px-4">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <small class="text-white-50"><?php echo __('assoc_net_team_earnings', [], 'Team Total Earnings'); ?></small>
                    <div class="fw-bold" class="style-4846">₹<?= number_format($totalCommission) ?></div>
                </div>
                <div class="col-md-4">
                    <small class="text-white-50"><?php echo __('assoc_net_left_leg', [], 'Left Leg'); ?></small>
                    <div class="fw-bold" class="style-1357"><?= $leftCount ?> <?php echo __('assoc_net_members', [], 'members'); ?></div>
                </div>
                <div class="col-md-4">
                    <small class="text-white-50"><?php echo __('assoc_net_right_leg', [], 'Right Leg'); ?></small>
                    <div class="fw-bold" class="style-75156"><?= $rightCount ?> <?php echo __('assoc_net_members', [], 'members'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search + View Toggle -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" id="treeSearch" class="form-control form-control-sm" class="style-45496" placeholder="<?php echo __('assoc_net_search', [], 'Search by name, rank...'); ?>" autocomplete="off">
                <select id="rankFilter" class="form-select form-select-sm" class="style-1698">
                    <option value=""><?php echo __('assoc_net_all_ranks', [], 'All Ranks'); ?></option>
                    <?php foreach ($rankLabels as $k => $v): ?>
                        <option value="<?= $k ?>"><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="btn-group btn-group-sm" id="treeViewToggle">
                <button class="btn btn-outline-primary active" data-view="tree"><i class="fas fa-sitemap me-1"></i><?php echo __('assoc_net_tree_view', [], 'Tree'); ?></button>
                <button class="btn btn-outline-primary" data-view="list"><i class="fas fa-list me-1"></i><?php echo __('assoc_net_list_view', [], 'List'); ?></button>
                <button class="btn btn-outline-primary" data-view="cards"><i class="fas fa-th me-1"></i><?php echo __('assoc_net_cards_view', [], 'Cards'); ?></button>
            </div>
        </div>
    </div>

    <!-- Tree View -->
    <div id="treeView">
        <?php if (empty($rootNodes) && empty($nodes)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-project-diagram fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted"><?php echo __('assoc_net_no_network', [], 'No Network Yet'); ?></h4>
                    <p class="text-muted mb-3"><?php echo __('assoc_net_no_network_desc', [], 'Start building your network by sharing your referral code.'); ?></p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="<?= BASE_URL ?>/associate/dashboard" class="btn btn-primary"><i class="fas fa-tachometer-alt me-1"></i> <?php echo __('assoc_net_go_dashboard', [], 'Go to Dashboard'); ?></a>
                        <a href="<?= BASE_URL ?>/become-associate" class="btn btn-outline-primary" target="_blank"><i class="fas fa-share-alt me-1"></i> <?php echo __('assoc_net_share_referral', [], 'Share Referral'); ?></a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="m-0 fw-bold"><i class="fas fa-sitemap text-primary me-2"></i><?php echo __('assoc_net_hierarchy_view', [], 'Hierarchy View'); ?></h5>
                </div>
                <div class="card-body overflow-x-auto" class="style-68694">
                    <?php foreach ($rootNodes as $root):
                        $rootLevel = strtolower($root['current_level'] ?? 'associate');
                        $rootColor = $rankColors[$rootLevel] ?? '#94a3b8';
                        $rootIcon = $rankIcons[$rootLevel] ?? 'fa-user';
                        $rootLabel = $rankLabels[$rootLevel] ?? ucfirst(str_replace('_', ' ', $rootLevel));
                    ?>
                    <!-- Root node (YOU) -->
                    <div class="text-center mb-2">
                        <div class="d-inline-block border-2 rounded-3 p-3 position-relative rank-pulse" class="style-59006">
                            <span class="gen-badge mb-1 d-inline-block"><?php echo __('assoc_net_you', [], 'YOU'); ?> (Gen 0)</span>
                            <span class="leg-badge mb-2 d-inline-block" class="style-61782"><?php echo __('assoc_net_you', [], 'YOU'); ?></span>
                            <div class="fw-bold" class="style-88102"><?= htmlspecialchars($root['name'] ?? 'You') ?></div>
                            <div class="small text-muted mt-1">
                                <span class="badge" class="style-37804"><i class="fas <?= $rootIcon ?> me-1"></i><?= htmlspecialchars($rootLabel ?? '') ?></span>
                            </div>
                            <div class="mt-2" class="style-64777">
                                <span class="me-3"><i class="fas fa-rupee-sign text-success"></i> <?= number_format((float)($root['total_commission'] ?? 0)) ?></span>
                                <span><i class="fas fa-chart-line text-primary"></i> <?= number_format((float)($root['personal_bv'] ?? 0)) ?></span>
                            </div>
                        </div>
                    </div>
                    <!-- Connector -->
                    <?php if (!empty($byParent[$root['id'] ?? null])): ?>
                        <div class="text-center"><div class="tree-v-line" class="style-16797"></div></div>
                    <?php endif; ?>
                    <!-- Children -->
                    <?= buildTreeLevel($root['id'] ?? null, $byParent, $rankColors, $rankIcons, $rankLabels, 0) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- List View -->
    <div id="listView" class="style-2248">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="m-0 fw-bold"><i class="fas fa-list text-primary me-2"></i><?php echo __('assoc_net_list_heading', [], 'List View'); ?> (<?= $totalDownline ?> <?php echo __('assoc_net_members', [], 'members'); ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="listTable">
                        <thead class="bg-light">
                            <tr>
                                <th><?php echo __('assoc_net_th_hash', [], '#'); ?></th>
                                <th><?php echo __('assoc_net_th_member', [], 'Member'); ?></th>
                                <th><?php echo __('assoc_net_th_gen', [], 'Gen'); ?></th>
                                <th><?php echo __('assoc_net_th_rank', [], 'Rank'); ?></th>
                                <th><?php echo __('assoc_net_th_position', [], 'Position'); ?></th>
                                <th><?php echo __('assoc_net_th_level', [], 'Level'); ?></th>
                                <th class="text-end"><?php echo __('assoc_net_th_commission', [], 'Commission'); ?></th>
                                <th class="text-end"><?php echo __('assoc_net_th_bv', [], 'BV'); ?></th>
                                <th><?php echo __('assoc_net_th_joined', [], 'Joined'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($nodes)): ?>
                                <tr><td colspan="9" class="text-center text-muted py-4"><?php echo __('assoc_net_no_members', [], 'No members in your network yet.'); ?></td></tr>
                            <?php else: ?>
                                <?php foreach ($nodes as $i => $n):
                                    $level = strtolower($n['current_level'] ?? 'associate');
                                    $color = $rankColors[$level] ?? '#94a3b8';
                                    $icon = $rankIcons[$level] ?? 'fa-user';
                                    $label = $rankLabels[$level] ?? ucfirst(str_replace('_', ' ', $level));
                                ?>
                                <tr data-rank="<?= $level ?>">
                                    <td class="text-muted"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($n['name'] ?? __('assoc_net_unknown', [], 'Unknown')) ?></div>
                                    </td>
                                    <td><span class="gen-badge">Gen <?= (int)($n['level'] ?? 0) ?></span></td>
                                    <td>
                                        <span class="badge" class="style-76027">
                                            <i class="fas <?= $icon ?> me-1"></i><?= htmlspecialchars($label ?? '') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (($n['position'] ?? '') === 'left'): ?>
                                            <span class="leg-badge" class="style-60845"><?php echo __('assoc_net_leg_left', [], 'LEFT'); ?></span>
                                        <?php elseif (($n['position'] ?? '') === 'right'): ?>
                                            <span class="leg-badge" class="style-46420"><?php echo __('assoc_net_leg_right', [], 'RIGHT'); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center fw-bold"><?= (int)($n['level'] ?? 0) ?></td>
                                    <td class="text-end">₹<?= number_format((float)($n['total_commission'] ?? 0)) ?></td>
                                    <td class="text-end">₹<?= number_format((float)($n['personal_bv'] ?? 0)) ?></td>
                                    <td class="small text-muted"><?= date('d M Y', strtotime($n['joined_at'] ?? 'now')) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards View -->
    <div id="cardsView" class="style-2248">
        <div class="row g-3" id="cardsContainer">
            <?php foreach ($nodes as $n):
                $level = strtolower($n['current_level'] ?? 'associate');
                $color = $rankColors[$level] ?? '#94a3b8';
                $icon = $rankIcons[$level] ?? 'fa-user';
                $label = $rankLabels[$level] ?? ucfirst(str_replace('_', ' ', $level));
            ?>
            <div class="col-md-6 col-lg-4 col-xl-3 member-card" data-rank="<?= $level ?>" data-name="<?= strtolower($n['name'] ?? '') ?>">
                <div class="card border-0 shadow-sm h-100" class="style-68538">
                    <div class="card-body text-center p-3">
                        <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" class="style-78807">
                            <i class="fas <?= $icon ?> fa-lg"></i>
                        </div>
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($n['name'] ?? 'Unknown') ?></h6>
                        <span class="badge mb-2" class="style-34096"><?= htmlspecialchars($label ?? '') ?></span>
                        <div class="gen-badge mb-2 d-inline-block">Gen <?= (int)($n['level'] ?? 0) ?></div>
                        <div class="d-flex justify-content-around mt-2" class="style-436">
                            <div>
                                <div class="fw-bold text-primary">₹<?= number_format((float)($n['total_commission'] ?? 0)) ?></div>
                                <div class="text-muted"><?php echo __('assoc_net_earned', [], 'Earned'); ?></div>
                            </div>
                            <div>
                                <div class="fw-bold text-success">₹<?= number_format((float)($n['personal_bv'] ?? 0)) ?></div>
                                <div class="text-muted"><?php echo __('assoc_net_th_bv', [], 'BV'); ?></div>
                            </div>
                        </div>
                        <?php if (($n['position'] ?? '')): ?>
                            <div class="mt-2">
                                <span class="leg-badge" class="style-46814"><?= strtoupper($n['position']) ?> <?php echo __('assoc_net_leg', [], 'LEG'); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="text-muted mt-2" class="style-68658">
                            <i class="fas fa-calendar me-1"></i><?php echo __('assoc_net_joined', [], 'Joined'); ?> <?= date('d M Y', strtotime($n['joined_at'] ?? 'now')) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
/**
 * Build tree level HTML with vertical connectors and horizontal lines
 */
function buildTreeLevel($parentId, $byParent, $rankColors, $rankIcons, $rankLabels, $depth = 0) {
    $children = $byParent[$parentId] ?? [];
    if (empty($children)) return '';

    $count = count($children);
    $html = '<div class="d-flex justify-content-center gap-3 mt-0" class="style-26370">';

    foreach ($children as $idx => $child) {
        $level = strtolower($child['current_level'] ?? 'associate');
        $color = $rankColors[$level] ?? '#94a3b8';
        $icon = $rankIcons[$level] ?? 'fa-user';
        $label = $rankLabels[$level] ?? ucfirst(str_replace('_', ' ', $level));
        $name = htmlspecialchars($child['name'] ?? __('assoc_net_unknown', [], 'Unknown'));
        $email = htmlspecialchars($child['email'] ?? '');
        $commission = number_format((float)($child['total_commission'] ?? 0));
        $bv = number_format((float)($child['personal_bv'] ?? 0));
        $joinDate = date('d M Y', strtotime($child['joined_at'] ?? 'now'));
        $pos = $child['position'] ?? '';
        $posColor = $pos === 'left' ? '#10b981' : '#f59e0b';
        $posLabel = $pos === 'left' ? 'L' : ($pos === 'right' ? 'R' : '');
        $isActive = (int)($child['is_active'] ?? 1);
        $childId = $child['id'] ?? null;
        $hasChildren = !empty($byParent[$childId]);
        $genNum = $depth + 1;

        $html .= '<div class="tree-node text-center" class="style-14373">';

        // Vertical connector line
        $html .= '<div class="tree-v-line" class="style-13733"></div>';

        // Leg badge
        if ($posLabel) {
            $html .= '<span class="leg-badge mb-1 d-inline-block" class="style-39937">'.$posLabel.' '.__('assoc_net_leg', [], 'LEG').'</span>';
        }

        // Card with tooltip and click-through
        $profileUrl = BASE_URL . '/associate/team';
        $html .= '<a href="'.$profileUrl.'" class="text-decoration-none">';
        $html .= '<div class="card border-0 shadow-sm p-2 text-center" class="style-460">';

        // Tooltip on hover
        $html .= '<div class="tree-tooltip">';
        $html .= $name;
        if ($email) $html .= '<br><small>'.$email.'</small>';
        $html .= '<br>Rank: '.$label.' | Earned: ₹'.$commission;
        if ($pos) $html .= ' | '.strtoupper($pos).' Leg';
        $html .= '</div>';

        // Gen badge
        $html .= '<span class="gen-badge mb-1 d-inline-block">Gen '.$genNum.'</span>';
        $html .= '<span class="badge mb-1" class="style-21508"><i class="fas '.$icon.'"></i> '.$label.'</span>';
        $html .= '<div class="fw-bold" class="style-58264">'.$name.'</div>';
        $html .= '<div class="mt-1" class="style-36196">';
        $html .= '<span class="me-2"><i class="fas fa-rupee-sign"></i>'.$commission.'</span>';
        $html .= '<span><i class="fas fa-chart-line"></i>'.$bv.'</span>';
        $html .= '</div>';
        if ($hasChildren) {
            $directChildCount = count($byParent[$childId]);
            $html .= '<div class="text-muted" class="style-7865"><i class="fas fa-sitemap me-1"></i>'.$directChildCount.' direct</div>';
        }
        $html .= '<div class="text-muted" class="style-56522"><i class="fas fa-calendar me-1"></i>'.__('assoc_net_joined', [], 'Joined').' '.$joinDate.'</div>';
        $html .= '</div>';
        $html .= '</a>';

        // Recurse children
        $childHtml = buildTreeLevel($childId, $byParent, $rankColors, $rankIcons, $rankLabels, $depth + 1);
        if ($childHtml) {
            $html .= $childHtml;
        }

        $html .= '</div>';
    }

    $html .= '</div>';
    return $html;
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('treeSearch');
    var rankFilter = document.getElementById('rankFilter');

    function applyFilters() {
        var q = (searchInput.value || '').toLowerCase();
        var rank = rankFilter.value;

        // List view filter
        document.querySelectorAll('#listTable tbody tr').forEach(function(row) {
            var matchSearch = !q || row.textContent.toLowerCase().indexOf(q) > -1;
            var matchRank = !rank || row.getAttribute('data-rank') === rank;
            row.style.display = (matchSearch && matchRank) ? '' : 'none';
        });

        // Cards view filter
        document.querySelectorAll('.member-card').forEach(function(card) {
            var matchSearch = !q || card.getAttribute('data-name').indexOf(q) > -1 || card.textContent.toLowerCase().indexOf(q) > -1;
            var matchRank = !rank || card.getAttribute('data-rank') === rank;
            card.style.display = (matchSearch && matchRank) ? '' : 'none';
        });

        // Tree view filter (hide/show nodes)
        document.querySelectorAll('.tree-node').forEach(function(node) {
            var matchSearch = !q || node.textContent.toLowerCase().indexOf(q) > -1;
            node.style.display = matchSearch ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', applyFilters);
    rankFilter.addEventListener('change', applyFilters);

    // View toggle
    document.querySelectorAll('#treeViewToggle button').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#treeViewToggle button').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            var view = this.getAttribute('data-view');
            document.getElementById('treeView').style.display = view === 'tree' ? '' : 'none';
            document.getElementById('listView').style.display = view === 'list' ? '' : 'none';
            document.getElementById('cardsView').style.display = view === 'cards' ? '' : 'none';
        });
    });
});
</script>
