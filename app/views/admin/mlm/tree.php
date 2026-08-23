<?php
$page_title = $page_title ?? "Network Tree";
$treeData = $treeData ?? ['nodes' => [], 'stats' => []];
$base = defined('BASE_URL') ? BASE_URL : '';
$nodes = $treeData['nodes'] ?? [];
$stats = $treeData['stats'] ?? [];
$totalDownline = (int)($stats['total_downline'] ?? 0);
$leftCount = (int)($stats['left_count'] ?? 0);
$rightCount = (int)($stats['right_count'] ?? 0);
$pairingBonus = (float)($stats['pairing_bonus'] ?? 0);

$rankColors = [
    'associate' => '#94a3b8', 'bronze' => '#a16207', 'silver' => '#94a3b8',
    'gold' => '#ca8a04', 'platinum' => '#0891b2', 'diamond' => '#0f766e',
];
$rankIcons = [
    'associate' => 'fa-user', 'bronze' => 'fa-medal', 'silver' => 'fa-award',
    'gold' => 'fa-trophy', 'platinum' => 'fa-gem', 'diamond' => 'fa-crown',
];

$byParent = [];
foreach ($nodes as $n) {
    $pid = $n['parent_id'] ?? null;
    $byParent[$pid][] = $n;
}
$rootNodes = $byParent[null] ?? [];

function buildTreeHtml($parentId, $byParent, $rankColors, $rankIcons) {
    $children = $byParent[$parentId] ?? [];
    if (empty($children)) return '';
    $html = '<div class="tree-children d-flex justify-content-center gap-4 mt-2 style-92811">';
    foreach ($children as $idx => $child) {
        $level = strtolower($child['current_level'] ?? 'associate');
        $color = $rankColors[$level] ?? '#94a3b8';
        $icon = $rankIcons[$level] ?? 'fa-user';
        $name = htmlspecialchars($child['name'] ?? 'Unknown');
        $email = htmlspecialchars($child['email'] ?? '');
        $commission = number_format((float)($child['total_commission'] ?? 0));
        $bv = number_format((float)($child['personal_bv'] ?? 0));
        $joinDate = htmlspecialchars(substr($child['joined_at'] ?? '', 0, 10));
        $pos = $child['position'] ?? '';
        $posLabel = $pos === 'left' ? 'L' : ($pos === 'right' ? 'R' : '');
        $posColor = $pos === 'left' ? '#10b981' : ($pos === 'right' ? '#f59e0b' : '#94a3b8');
        $isActive = (int)($child['is_active'] ?? 1);

        $html .= '<div class="tree-node-wrapper style-13858">';
        if ($posLabel) {
            $html .= '<div class="text-center mb-1"><span class="badge style-42629">' . $posLabel . ' Leg</span></div>';
        }
        $html .= '<div class="tree-card border rounded p-2 text-center style-73782">';
        $html .= '<span class="badge mb-1 style-29474"><i class="fas ' . $icon . '"></i></span>';
        $html .= '<div class="fw-bold small">' . $name . '</div>';
        $html .= '<div class="text-muted style-60898">' . $email . '</div>';
        $html .= '<div class="mt-1 style-68658">';
        $html .= '<span class="me-2"><i class="fas fa-rupee-sign"></i>' . $commission . '</span>';
        $html .= '<span><i class="fas fa-chart-line"></i>' . $bv . '</span>';
        $html .= '</div>';
        if ($joinDate) {
            $html .= '<div class="text-muted mt-1 style-56522"><i class="fas fa-calendar me-1"></i>' . $joinDate . '</div>';
        }
        $html .= '</div>';
        $childId = $child['id'] ?? null;
        $childHtml = buildTreeHtml($childId, $byParent, $rankColors, $rankIcons);
        if ($childHtml) {
            $html .= '<div class="tree-connector text-center style-41562"></div>';
            $html .= $childHtml;
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-project-diagram me-2"></i>Network Tree</h4>
        <div>
            <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/genealogy" class="btn btn-outline-primary btn-sm me-2"><i class="fas fa-sitemap me-1"></i>Genealogy</a>
            <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to MLM</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <div class="style-71517"><?= $totalDownline ?></div>
                    <div class="text-muted small">Total Downline</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <div class="style-23322"><?= $leftCount ?></div>
                    <div class="text-muted small">Left Leg Count</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <div class="style-39581"><?= $rightCount ?></div>
                    <div class="text-muted small">Right Leg Count</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <div class="style-93706">&#8377;<?= number_format($pairingBonus) ?></div>
                    <div class="text-muted small">Pairing Bonus Paid</div>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
            <h5 class="m-0"><i class="fas fa-search me-2"></i>Search</h5>
            <div class="btn-group btn-group-sm" id="treeViewToggle">
                <button class="btn btn-outline-primary active" data-view="tree"><i class="fas fa-project-diagram me-1"></i>Tree</button>
                <button class="btn btn-outline-primary" data-view="list"><i class="fas fa-list me-1"></i>List</button>
            </div>
        </div>
        <div class="aps-cp-card-body">
            <input type="text" id="treeSearch" class="form-control" placeholder="Search by name, email, or referral code..." autocomplete="off">
        </div>
    </div>

    <div id="treeView">
        <?php if (empty($rootNodes)): ?>
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center py-5">
                    <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Network Data</h5>
                    <p class="text-muted">The network tree will populate as associates recruit members.</p>
                    <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/users" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Associate</a>
                </div>
            </div>
        <?php else: ?>
            <div class="aps-cp-card">
                <div class="aps-cp-card-header">
                    <h5 class="m-0"><i class="fas fa-project-diagram me-2"></i>Tree View</h5>
                </div>
                <div class="aps-cp-card-body style-24784">
                    <?php foreach ($rootNodes as $root):
                        $rootLevel = strtolower($root['current_level'] ?? 'associate');
                        $rootColor = $rankColors[$rootLevel] ?? '#94a3b8';
                        $rootIcon = $rankIcons[$rootLevel] ?? 'fa-user';
                    ?>
                    <div class="text-center mb-4 tree-root">
                        <div class="d-inline-block border rounded p-3 tree-card style-75034">
                            <span class="badge mb-1 style-11051"><i class="fas <?= $rootIcon ?> me-1"></i><?= htmlspecialchars(ucfirst($rootLevel ?? '')) ?></span>
                            <div class="fw-bold"><?= htmlspecialchars($root['name'] ?? 'Unknown') ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($root['email'] ?? '') ?></div>
                            <div class="mt-1 style-436">
                                <span class="me-2"><i class="fas fa-rupee-sign"></i><?= number_format((float)($root['total_commission'] ?? 0)) ?></span>
                                <span><i class="fas fa-chart-line"></i>&#8377;<?= number_format((float)($root['personal_bv'] ?? 0)) ?></span>
                            </div>
                            <?php if (!empty($root['joined_at'])): ?>
                            <div class="text-muted mt-1 style-68658"><i class="fas fa-calendar me-1"></i><?= htmlspecialchars(substr($root['joined_at'], 0, 10)) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="style-91240"></div>
                        <?= buildTreeHtml($root['id'] ?? null, $byParent, $rankColors, $rankIcons) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="listView" class="style-2248">
        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5 class="m-0"><i class="fas fa-list me-2"></i>List View</h5>
            </div>
            <div class="aps-cp-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Member</th>
                                <th>Rank</th>
                                <th>Position</th>
                                <th>Level</th>
                                <th class="text-end">Commission</th>
                                <th class="text-end">Business Volume</th>
                                <th class="text-end">Left BV</th>
                                <th class="text-end">Right BV</th>
                                <th>Joined</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($nodes)): ?>
                                <tr><td colspan="10" class="text-center text-muted py-4">No network data.</td></tr>
                            <?php else: ?>
                                <?php foreach ($nodes as $n):
                                    $level = strtolower($n['current_level'] ?? 'associate');
                                    $color = $rankColors[$level] ?? '#94a3b8';
                                    $icon = $rankIcons[$level] ?? 'fa-user';
                                    $pos = $n['position'] ?? '-';
                                    $posColor = $pos === 'left' ? '#10b981' : ($pos === 'right' ? '#f59e0b' : '#94a3b8');
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge me-2 style-37328"><i class="fas <?= $icon ?>"></i></span>
                                            <div>
                                                <div class="fw-bold small"><?= htmlspecialchars($n['name'] ?? 'Unknown') ?></div>
                                                <div class="text-muted style-68658"><?= htmlspecialchars($n['email'] ?? '') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge style-37328"><?= htmlspecialchars(ucfirst($level ?? '')) ?></span></td>
                                    <td>
                                        <?php if ($pos && $pos !== '-'): ?>
                                            <span class="badge style-8985"><?= htmlspecialchars(ucfirst($pos ?? '')) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= (int)($n['level'] ?? 0) ?></td>
                                    <td class="text-end">&#8377;<?= number_format((float)($n['total_commission'] ?? 0)) ?></td>
                                    <td class="text-end">&#8377;<?= number_format((float)($n['personal_bv'] ?? 0)) ?></td>
                                    <td class="text-end">&#8377;<?= number_format((float)($n['total_left_bv'] ?? 0)) ?></td>
                                    <td class="text-end">&#8377;<?= number_format((float)($n['total_right_bv'] ?? 0)) ?></td>
                                    <td class="small text-muted"><?= htmlspecialchars(substr($n['joined_at'] ?? '', 0, 10)) ?></td>
                                    <td>
                                        <?php $isActive = (int)($n['is_active'] ?? 1); ?>
                                        <span class="badge bg-<?= $isActive ? 'success' : 'secondary' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></span>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('treeSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var q = this.value.toLowerCase();
            document.querySelectorAll('.tree-root, .tree-node-wrapper').forEach(function(el) {
                var text = el.textContent.toLowerCase();
                el.style.display = text.indexOf(q) > -1 ? '' : 'none';
            });
            document.querySelectorAll('#listView tbody tr').forEach(function(el) {
                var text = el.textContent.toLowerCase();
                el.style.display = text.indexOf(q) > -1 ? '' : 'none';
            });
        });
    }

    var toggleBtns = document.querySelectorAll('#treeViewToggle button');
    toggleBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            toggleBtns.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            var view = this.getAttribute('data-view');
            document.getElementById('treeView').style.display = view === 'tree' ? '' : 'none';
            document.getElementById('listView').style.display = view === 'list' ? '' : 'none';
        });
    });
});
</script>

<style>
.tree-card { transition: box-shadow 0.2s, transform 0.15s; }
.tree-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: translateY(-1px); }
.tree-children { flex-wrap: wrap; }
.tree-node-wrapper { flex: 0 0 auto; }
</style>
