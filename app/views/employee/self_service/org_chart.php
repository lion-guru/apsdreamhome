<?php
/** Employee Self-Service Org Chart */

$page_title = $page_title ?? 'Organizational Chart';
$tree = $tree ?? [];
$my_node = $my_node ?? null;
$departments = $departments ?? [];
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
    border-color: #7c2d12;
    box-shadow: 0 4px 16px rgba(124,45,18,0.15);
    transform: translateY(-2px);
}
.org-node.me {
    border-color: #7c2d12;
    box-shadow: 0 0 0 3px rgba(124,45,18,0.2);
    background: #fefce8;
}
.org-node.vacant {
    border-style: dashed;
    background: #fefce8;
    border-color: #f59e0b;
}
.org-node.level-0 { border-top: 4px solid #7c2d12; }
.org-node.level-1 { border-top: 4px solid #b45309; }
.org-node.level-2 { border-top: 4px solid #ea580c; }
.org-node.level-3 { border-top: 4px solid #f97316; }

.org-tree { display: flex; flex-direction: column; align-items: center; }
.org-level { display: flex; gap: 1.5rem; justify-content: center; margin-bottom: 2rem; flex-wrap: wrap; }
.org-branch { display: flex; gap: 1rem; justify-content: center; margin-top: 1rem; }
.org-branch::before {
    content: '';
    position: absolute;
    top: 0;
    left: 10%;
    right: 10%;
    height: 2px;
    background: #cbd5e1;
}
.dept-filter { cursor: pointer; }
.dept-filter.active { font-weight: 600; color: #7c2d12; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-sitemap me-2 text-primary"></i>Organizational Chart</h4>
            <p class="text-muted mb-0 small">View company organizational structure</p>
        </div>
        <?php if ($my_node): ?>
        <div class="alert alert-info d-flex align-items-center gap-2 mb-0 py-2" role="alert">
            <i class="fas fa-user-circle fa-lg"></i>
            <div>
                <div class="fw-semibold">Your Position: <?= htmlspecialchars($my_node['position_title']) ?></div>
                <div class="small text-muted"><?= htmlspecialchars($my_node['department'] ?? '') ?> • Level <?= $my_node['level'] ?? 0 ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Department Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <span class="dept-filter active badge bg-primary px-3 py-2" data-dept="all" style="font-size: 0.85rem;">All Departments</span>
                <?php foreach ($departments as $dept): ?>
                    <span class="dept-filter badge bg-light text-dark px-3 py-2" data-dept="<?= htmlspecialchars($dept) ?>" style="font-size: 0.85rem;">
                        <?= htmlspecialchars($dept) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Org Chart Visualization -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-diagram-project me-2"></i>Organization Structure</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="expandAll()">Expand All</button>
                <button class="btn btn-sm btn-outline-secondary" onclick="collapseAll()">Collapse All</button>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="showVacant" checked onchange="toggleVacant()">
                    <label class="form-check-label small" for="showVacant">Show Vacant</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="org-chart-container" style="overflow-x: auto; overflow-y: auto; max-height: 70vh;">
                <div class="org-chart" id="orgChart">
                    <?php if (empty($tree)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Organization Chart Not Available</h5>
                        <p class="text-muted small">Please contact HR for access</p>
                    </div>
                    <?php else: ?>
                        <?php echo renderOrgTree($tree, 'all', $my_node ? $my_node['id'] : null); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function renderOrgTree(nodes, filterDept, myNodeId) {
    let html = '<div class="org-tree">';
    
    // Group by level
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
            if (filterDept !== 'all' && node.department !== filterDept) return;
            
            const isVacant = node.is_vacant == 1;
            const isMe = node.id == myNodeId;
            
            html += '<div class="org-node ' + (isVacant ? 'vacant' : '') + ' ' + (isMe ? 'me' : '') + ' level-' + lvl + '" data-id="' + node.id + '" data-dept="' + (node.department || '') + '">';
            html += '<div class="d-flex justify-content-between align-items-start mb-2">';
            html += '<span class="badge bg-' + (lvl === 0 ? 'primary' : (lvl === 1 ? 'warning' : 'secondary')) + '">L' + lvl + '</span>';
            if (isVacant) {
                html += '<span class="badge bg-warning text-dark">Vacant</span>';
            }
            if (isMe) {
                html += '<span class="badge bg-success">You</span>';
            }
            html += '</div>';
            
            html += '<div class="fw-semibold fs-6 text-truncate" style="max-width: 200px;">' + escapeHtml(node.position_title) + '</div>';
            if (node.employee_name) {
                html += '<div class="small text-muted">' + escapeHtml(node.employee_name) + '</div>';
            } else {
                html += '<div class="small text-muted fst-italic">Unassigned</div>';
            }
            if (node.department) {
                html += '<div class="small"><span class="badge bg-light text-dark" style="background:#f1f5f9;color:#475569;">' + escapeHtml(node.department) + '</span></div>';
            }
            if (node.employee_email) {
                html += '<div class="small text-muted">' + escapeHtml(node.employee_email) + '</div>';
            }
            
            if (isMe) {
                html += '<div class="d-flex gap-1 mt-2">';
                html += '<a href="<?= BASE_URL ?>/employee/self-service/profile" class="btn btn-sm btn-primary w-100"><i class="fas fa-user me-1"></i> My Profile</a>';
                html += '</div>';
            }
            
            html += '</div>';
        });
        html += '</div>';
        
        // Branch connectors
        if (lvl < maxLevel) {
            html += '<div class="org-branch"></div>';
        }
    }
    
    html += '</div>';
    return html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Client-side rendering
document.addEventListener('DOMContentLoaded', function() {
    const treeData = <?= json_encode($tree) ?>;
    const myNodeId = <?= $my_node ? $my_node['id'] : 'null' ?>;
    const deptFilters = document.querySelectorAll('.dept-filter');
    
    deptFilters.forEach(btn => {
        btn.addEventListener('click', function() {
            deptFilters.forEach(b => b.classList.remove('active', 'bg-primary'));
            b.classList.add('bg-light', 'text-dark');
            this.classList.add('active', 'bg-primary');
            this.classList.remove('bg-light', 'text-dark');
            
            const dept = this.dataset.dept;
            renderFilteredTree(dept);
        });
    });
    
    window.renderFilteredTree = function(dept) {
        const container = document.getElementById('orgChart');
        container.innerHTML = renderOrgTree(treeData, dept, myNodeId);
    };
    
    window.expandAll = function() {
        // Already expanded in this view
    };
    
    window.collapseAll = function() {
        // Could implement collapsible logic here
    };
    
    window.toggleVacant = function() {
        const show = document.getElementById('showVacant').checked;
        document.querySelectorAll('.org-node.vacant').forEach(node => {
            node.style.display = show ? '' : 'none';
        });
    };
});
</script>