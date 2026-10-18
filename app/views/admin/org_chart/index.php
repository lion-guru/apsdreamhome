<?php
/** Admin Org Chart */

$page_title = $page_title ?? 'Organizational Chart';
$tree = $tree ?? [];
$stats = $stats ?? [];
$departments = $departments ?? [];
$vacant = $vacant ?? [];
$employees = $employees ?? [];
$nodes = $nodes ?? [];
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
.org-connector { position: relative; }
.org-connector::before {
    content: '';
    position: absolute;
    top: -1.5rem;
    left: 50%;
    width: 2px;
    height: 1.5rem;
    background: #cbd5e1;
}
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
            <p class="text-muted mb-0 small">Visualize and manage organizational structure</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addNodeModal">
                <i class="fas fa-plus me-1"></i> Add Position
            </button>
            <button class="btn btn-outline-secondary" onclick="syncOrgChart()">
                <i class="fas fa-sync me-1"></i> Sync from Employees
            </button>
            <div class="btn-group">
                <button class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/org-chart/export">JSON Export</a></li>
                </ul>
            </div>
            <div class="btn-group">
                <button class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-upload me-1"></i> Import
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/org-chart/import">JSON Import</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['total_positions'] ?? 0 ?></div>
                    <div class="text-white-50 small">Total Positions</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['vacant_positions'] ?? 0 ?></div>
                    <div class="text-white-50 small">Vacant</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['departments'] ?? 0 ?></div>
                    <div class="text-white-50 small">Departments</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['max_level'] ?? 0 ?> Levels</div>
                    <div class="text-white-50 small">Max Depth</div>
                </div>
            </div>
        </div>
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
                        <h5 class="text-muted">No Organization Chart</h5>
                        <p class="text-muted small mb-3">Click "Sync from Employees" to auto-populate from employee data</p>
                        <button class="btn btn-primary" onclick="syncOrgChart()"><i class="fas fa-sync me-1"></i> Sync Now</button>
                    </div>
                    <?php else: ?>
                        <?php echo renderOrgTree($tree, 'all'); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Position Modal -->
<div class="modal fade" id="addNodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/org-chart/create">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add Position</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $e): ?>
                                <?php 
                                $hasNode = false;
                                foreach ($nodes as $n) { if ($n['employee_id'] == $e['id']) { $hasNode = true; break; } }
                                if (!$hasNode): ?>
                                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name'] . ' (' . $e['designation'] . ' - ' . $e['department'] . ')') ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reports To</label>
                        <select name="parent_id" class="form-select">
                            <option value="">Top Level (No Manager)</option>
                            <?php foreach ($nodes as $n): ?>
                                <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['position_title'] . ' - ' . ($n['employee_name'] ?? 'Vacant')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Position Title <span class="text-danger">*</span></label>
                        <input type="text" name="position_title" class="form-control" required placeholder="e.g., Senior Manager">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department</label>
                        <select name="department" class="form-select">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" name="is_vacant" value="1" id="is_vacant">
                                <label class="form-check-label" for="is_vacant">Vacant Position</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Position</button>
                </div>
            </form>
        </div>
    </div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function renderOrgTree(nodes, filterDept) {
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
            const hasChildren = node.children && node.children.length > 0;
            
            html += '<div class="org-node ' + (isVacant ? 'vacant' : '') + ' level-' + lvl + '" data-id="' + node.id + '" data-dept="' + (node.department || '') + '">';
            html += '<div class="d-flex justify-content-between align-items-start mb-2">';
            html += '<span class="badge bg-' + (lvl === 0 ? 'primary' : (lvl === 1 ? 'warning' : 'secondary')) + '">L' + lvl + '</span>';
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
            if (node.department) {
                html += '<div class="small"><span class="badge bg-light text-dark" style="background:#f1f5f9;color:#475569;">' + escapeHtml(node.department) + '</span></div>';
            }
            if (node.employee_email) {
                html += '<div class="small text-muted">' + escapeHtml(node.employee_email) + '</div>';
            }
            
            html += '<div class="d-flex gap-1 mt-2">';
            html += '<button class="btn btn-sm btn-outline-primary" onclick="editNode(' + node.id + ')" title="Edit"><i class="fas fa-edit"></i></button>';
            html += '<button class="btn btn-sm btn-outline-info" onclick="viewDetails(' + node.id + ')" title="Details"><i class="fas fa-eye"></i></button>';
            html += '<button class="btn btn-sm btn-outline-danger" onclick="deleteNode(' + node.id + ')" title="Delete"><i class="fas fa-trash"></i></button>';
            html += '</div>';
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
        container.innerHTML = renderOrgTree(treeData, dept);
    };
    
    window.expandAll = function() {
        // Expand all nodes (already expanded in this view)
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
    
    window.syncOrgChart = function() {
        if (confirm('Sync organization chart from employee data? This will update positions and create missing nodes.')) {
            fetch('<?= BASE_URL ?>/admin/org-chart/sync', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'csrf_token=' + encodeURIComponent('<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>')
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            });
        }
    };
    
    window.editNode = function(id) {
        window.location.href = '<?= BASE_URL ?>/admin/org-chart/edit/' + id;
    };
    
    window.viewDetails = function(id) {
        // Could show modal with details
        alert('View details for node ' + id);
    };
    
    window.deleteNode = function(id) {
        if (confirm('Delete this position?')) {
            fetch('<?= BASE_URL ?>/admin/org-chart/delete/' + id, {
                method: 'GET'
            }).then(r => r.json()).then(data => {
                if (data.success) location.reload();
                else alert(data.message);
            });
        }
    };
});
</script>