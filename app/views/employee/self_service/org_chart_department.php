<?php
/** Employee Self-Service Department Org Chart */

$page_title = $page_title ?? 'Department Org Chart';
$tree = $tree ?? [];
$department = $department ?? 'Department';
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.org-chart {
    font-family: 'Segoe UI', system-ui, sans-serif;
}
.org-node {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    min-width: 180px;
    max-width: 220px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.2s;
    position: relative;
}
.org-node:hover {
    border-color: #0d6efd;
    box-shadow: 0 4px 16px rgba(13,110,253,0.15);
    transform: translateY(-2px);
}
.org-node.vacant {
    border-style: dashed;
    background: #fefce8;
    border-color: #f59e0b;
}
.org-node.level-0 { border-top: 4px solid #0d6efd; }
.org-node.level-1 { border-top: 4px solid #0dcaf0; }
.org-node.level-2 { border-top: 4px solid #20c997; }
.org-node.level-3 { border-top: 4px solid #6c757d; }

.org-tree { display: flex; flex-direction: column; align-items: center; }
.org-level { display: flex; gap: 1.5rem; justify-content: center; margin-bottom: 2rem; flex-wrap: wrap; }
.org-branch { display: flex; gap: 1rem; justify-content: center; margin-top: 1rem; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee/self-service/org-chart" class="text-decoration-none">Org Chart</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($department) ?></li>
                </ol>
            </nav>
            <h4 class="mb-1 fw-bold"><i class="fas fa-users-cog me-2 text-primary"></i>Department: <?= htmlspecialchars($department) ?></h4>
            <p class="text-muted mb-0 small">Hierarchical team view for <?= htmlspecialchars($department) ?></p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/employee/self-service/org-chart" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Full Chart
            </a>
        </div>
    </div>

    <!-- Org Chart Visualization -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-sitemap me-2 text-primary"></i><?= htmlspecialchars($department) ?> Structure</h5>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="showVacantDept" checked onchange="toggleVacantDept()">
                <label class="form-check-label small" for="showVacantDept">Show Vacant</label>
            </div>
        </div>
        <div class="card-body">
            <div class="org-chart-container" style="overflow-x: auto; overflow-y: auto; max-height: 70vh;">
                <div class="org-chart" id="deptOrgChart">
                    <?php if (empty($tree)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Department Hierarchy Found</h5>
                        <p class="text-muted small">No positions or employees mapped to <?= htmlspecialchars($department) ?> yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function renderDeptTree(nodes) {
    if (!nodes || nodes.length === 0) return '';
    let html = '<div class="org-tree">';
    
    const levels = {};
    nodes.forEach(node => {
        const lvl = node.level || 0;
        if (!levels[lvl]) levels[lvl] = [];
        levels[lvl].push(node);
    });
    
    const maxLevel = Math.max(...Object.keys(levels).map(Number));
    
    for (let lvl = 0; lvl <= maxLevel; lvl++) {
        if (!levels[lvl]) continue;
        
        html += '<div class="org-level" data-level="' + lvl + '">';
        levels[lvl].forEach(node => {
            const isVacant = node.is_vacant == 1;
            
            html += '<div class="org-node ' + (isVacant ? 'vacant' : '') + ' level-' + lvl + '" data-id="' + node.id + '">';
            html += '<div class="d-flex justify-content-between align-items-start mb-2">';
            html += '<span class="badge bg-' + (lvl === 0 ? 'primary' : 'secondary') + '">L' + lvl + '</span>';
            if (isVacant) {
                html += '<span class="badge bg-warning text-dark">Vacant</span>';
            }
            html += '</div>';
            
            html += '<div class="fw-semibold fs-6 text-truncate" style="max-width: 200px;">' + escapeHtml(node.position_title) + '</div>';
            if (node.employee_name) {
                html += '<div class="small text-muted">' + escapeHtml(node.employee_name) + '</div>';
            } else {
                html += '<div class="small text-muted fst-italic">Unassigned</div>';
            }
            if (node.employee_email) {
                html += '<div class="small text-muted">' + escapeHtml(node.employee_email) + '</div>';
            }
            html += '</div>';
        });
        html += '</div>';
    }
    
    html += '</div>';
    return html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function toggleVacantDept() {
    const show = document.getElementById('showVacantDept').checked;
    document.querySelectorAll('.org-node.vacant').forEach(node => {
        node.style.display = show ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const treeData = <?= json_encode($tree) ?>;
    const container = document.getElementById('deptOrgChart');
    if (treeData && treeData.length > 0) {
        container.innerHTML = renderDeptTree(treeData);
    }
});
</script>
