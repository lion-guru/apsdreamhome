<?php $pageTitle = 'MLM Network Tree'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/mlm">MLM</a></li>
            <li class="breadcrumb-item active" aria-current="page">Network Tree</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-sitemap me-2"></i>Network Tree</h4>
        <div>
            <button class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('treeContainer').classList.toggle('text-center')"><i class="fas fa-expand me-1"></i>Toggle View</button>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body aps-cp-card-body">
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-search me-1"></i>Search Member</label>
                <input type="text" id="treeSearch" class="form-control" placeholder="Type member name or ID..." onkeyup="filterTree(this.value)">
            </div>
            <div id="treeContainer" class="overflow-auto py-3">
                <?php if (!empty($treeData)): ?>
                    <?= buildTreeView($treeData) ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Network Data</h5>
                        <p class="text-muted mb-0">The network tree is empty.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
function buildTreeView($nodes, $depth = 0) {
    if (empty($nodes)) return '<p class="text-muted small mb-0">No members</p>';
    $html = '<ul class="list-unstyled mb-0" class="style-33429">';
    foreach ($nodes as $node) {
        $status = $node['status'] ?? 'active';
        $badge = $status === 'active' ? 'success' : 'secondary';
        $html .= '<li class="mb-2 tree-node">';
        $html .= '<div class="d-inline-flex align-items-center p-2 border rounded bg-white shadow-sm tree-member">';
        $html .= '<i class="fas fa-user-circle text-primary me-2"></i>';
        $html .= '<span class="fw-medium me-2 member-name">' . htmlspecialchars($node['name'] ?? '') . '</span>';
        $html .= '<small class="text-muted me-2">#' . ($node['id'] ?? '') . '</small>';
        $html .= '<span class="badge bg-' . $badge . ' me-2">' . ucfirst($status) . '</span>';
        if (!empty($node['children'])) {
            $html .= '<span class="badge bg-info">' . count($node['children']) . ' downline</span>';
        }
        $html .= '</div>';
        if (!empty($node['children'])) {
            $html .= buildTreeView($node['children'], $depth + 1);
        }
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}
?>
<script>
function filterTree(val) {
    var q = val.toLowerCase();
    document.querySelectorAll('.tree-node').forEach(function(li) {
        var name = li.querySelector('.member-name');
        if (name) {
            li.style.display = name.textContent.toLowerCase().includes(q) ? '' : 'none';
        }
    });
}
</script>
