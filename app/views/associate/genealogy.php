<?php
/**
 * Associate Interactive Genealogy Tree View
 * Fully embedded inside layouts/associate
 */
$page_title = $page_title ?? 'Interactive Genealogy - APS Dream Home';
$currentUser = $currentUser ?? [];
$stats = $stats ?? [];
$upline = $upline ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$userId = (int)($currentUser['id'] ?? ($_SESSION['user_id'] ?? 0));
$totalDownline = (int)($stats['total'] ?? 0);
$directSponsors = (int)($stats['direct'] ?? 0);
$leftCount = (int)($stats['left'] ?? 0);
$rightCount = (int)($stats['right'] ?? 0);
$maxDepth = (int)($stats['max_depth'] ?? 1);
?>

<script src="https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js"></script>

<style>
.genealogy-wrapper {
    position: relative;
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}
.tree-canvas-box {
    position: relative;
    width: 100%;
    height: 620px;
    background: radial-gradient(circle at 50% 50%, #f8fafc 0%, #f1f5f9 100%);
    cursor: grab;
    overflow: hidden;
}
.tree-canvas-box:active {
    cursor: grabbing;
}
.genealogy-toolbar {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: gap;
    gap: 12px;
}
.node-circle {
    fill: #0d9488;
    stroke: #ffffff;
    stroke-width: 3px;
    cursor: pointer;
    transition: all 0.25s ease;
}
.node-circle:hover {
    fill: #0f766e;
    stroke: #f59e0b;
    stroke-width: 4px;
    filter: drop-shadow(0 4px 8px rgba(13, 148, 136, 0.4));
}
.node-circle.root {
    fill: #f59e0b;
    stroke: #d97706;
    stroke-width: 4px;
}
.node-text-name {
    font-size: 12px;
    font-weight: 700;
    fill: #1e293b;
    pointer-events: none;
    text-anchor: middle;
}
.node-text-id {
    font-size: 10px;
    font-weight: 500;
    fill: #64748b;
    pointer-events: none;
    text-anchor: middle;
}
.node-badge {
    font-size: 9px;
    font-weight: 600;
    fill: #0d9488;
    text-anchor: middle;
}
.tree-link {
    fill: none;
    stroke: #cbd5e1;
    stroke-width: 2px;
}
.tree-zoom-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    font-size: 14px;
    transition: all 0.2s;
}
.tree-zoom-btn:hover {
    background: #f8fafc;
    color: #0d9488;
    border-color: #0d9488;
}
.upline-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    background: #f1f5f9;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #334155;
}
.upline-chip i {
    color: #0d9488;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 text-dark fw-bold"><i class="fas fa-sitemap text-teal me-2" style="color: #0d9488;"></i>Interactive Genealogy</h4>
        <small class="text-muted">Explore and analyze your entire downline organization with drag &amp; zoom</small>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= $base ?>/associate/network/tree" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="fas fa-project-diagram me-1"></i> Network Tree View
        </a>
        <a href="<?= $base ?>/associate/team" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-users me-1"></i> Team Directory
        </a>
        <a href="<?= $base ?>/associate/dashboard" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-teal-subtle text-teal flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem; background: rgba(13, 148, 136, 0.12); color: #0d9488;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-dark"><?= number_format($totalDownline) ?></div>
                        <div class="text-muted small">Total Downline</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-dark"><?= number_format($directSponsors) ?></div>
                        <div class="text-muted small">Direct Sponsors</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-info-subtle text-info flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-code-branch"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-dark">L: <?= $leftCount ?> | R: <?= $rightCount ?></div>
                        <div class="text-muted small">Leg Distribution</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-dark"><?= $maxDepth ?> Levels</div>
                        <div class="text-muted small">Maximum Depth</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upline Chain Strip (if available) -->
<?php if (!empty($upline)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <small class="text-muted fw-bold me-2"><i class="fas fa-arrow-up text-primary me-1"></i>Sponsor Chain:</small>
            <?php foreach ($upline as $idx => $up): ?>
                <span class="upline-chip">
                    <i class="fas fa-user-circle"></i>
                    <?= htmlspecialchars($up['name'] ?? 'Sponsor') ?>
                    <small class="text-muted">(<?= htmlspecialchars($up['referral_code'] ?? 'ID#' . ($up['id'] ?? '')) ?>)</small>
                </span>
                <?php if ($idx < count($upline) - 1): ?>
                    <i class="fas fa-chevron-right text-muted small"></i>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Interactive Tree Canvas Box -->
<div class="genealogy-wrapper shadow-sm mb-4">
    <!-- Toolbar -->
    <div class="genealogy-toolbar">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="input-group input-group-sm" style="max-width: 260px;">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="genealogySearch" class="form-control border-start-0" placeholder="Search associate name or ID...">
            </div>
            <select id="levelsSelect" class="form-select form-select-sm" style="max-width: 140px;">
                <option value="3">3 Levels</option>
                <option value="5" selected>5 Levels</option>
                <option value="7">7 Levels</option>
                <option value="10">10 Levels</option>
            </select>
            <button id="btnResetRoot" class="btn btn-sm btn-outline-teal rounded-pill px-3" style="color: #0d9488; border-color: #0d9488;">
                <i class="fas fa-home me-1"></i> My Root
            </button>
        </div>
        <div class="d-flex align-items-center gap-1">
            <button id="btnZoomIn" class="tree-zoom-btn" title="Zoom In"><i class="fas fa-plus"></i></button>
            <button id="btnZoomOut" class="tree-zoom-btn" title="Zoom Out"><i class="fas fa-minus"></i></button>
            <button id="btnResetZoom" class="tree-zoom-btn" title="Reset View"><i class="fas fa-compress-arrows-alt"></i></button>
        </div>
    </div>

    <!-- Tree SVG Area -->
    <div id="treeContainer" class="tree-canvas-box">
        <div id="treeLoading" class="position-absolute top-50 start-50 translate-middle text-center text-muted">
            <div class="spinner-border text-teal mb-2" role="status" style="color: #0d9488;"></div>
            <div>Loading interactive tree network...</div>
        </div>
        <svg id="treeSvg" width="100%" height="100%"></svg>
    </div>
    
    <!-- Legend Footer -->
    <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center flex-wrap gap-2 small">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span> Root (You)
            </span>
            <span class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 50%; background: #0d9488; display: inline-block;"></span> Active Associate
            </span>
            <span class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 50%; background: #94a3b8; display: inline-block;"></span> Inactive / Pending
            </span>
        </div>
        <span class="text-muted"><i class="fas fa-info-circle me-1"></i>Click any member node to inspect performance &amp; team stats</span>
    </div>
</div>

<!-- Member Details Modal -->
<div class="modal fade" id="memberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-dark"><i class="fas fa-id-badge text-teal me-2" style="color: #0d9488;"></i>Member Details</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2 text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3" style="width: 72px; height: 72px; font-size: 1.8rem; background: linear-gradient(135deg, #0d9488, #0f766e);">
                    <i class="fas fa-user"></i>
                </div>
                <h5 id="modalMemberName" class="fw-bold mb-1 text-dark">—</h5>
                <div id="modalMemberCode" class="text-muted small mb-3">Ref: —</div>
                
                <div class="row g-2 text-start bg-light p-3 rounded-3 mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Rank Slab</small>
                        <span id="modalMemberRank" class="fw-semibold text-primary">—</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Team Size</small>
                        <span id="modalMemberTeam" class="fw-semibold text-dark">—</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Position / Leg</small>
                        <span id="modalMemberLeg" class="fw-semibold text-info">—</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Lifetime Sales</small>
                        <span id="modalMemberSales" class="fw-semibold text-success">—</span>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button id="modalFocusBtn" class="btn btn-outline-primary btn-sm flex-fill rounded-pill">
                        <i class="fas fa-crosshairs me-1"></i> Focus Tree on Member
                    </button>
                    <a id="modalCallBtn" href="#" class="btn btn-success btn-sm flex-fill rounded-pill">
                        <i class="fas fa-phone me-1"></i> Contact
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const base = <?= json_encode($base) ?>;
    const currentUserId = <?= (int)$userId ?>;
    let rootUserId = currentUserId;
    
    const svg = d3.select("#treeSvg");
    const container = document.getElementById("treeContainer");
    const loading = document.getElementById("treeLoading");
    const memberModal = new bootstrap.Modal(document.getElementById('memberModal'));
    
    let g = svg.append("g");
    
    const zoom = d3.zoom()
        .scaleExtent([0.2, 3])
        .on("zoom", (event) => {
            g.attr("transform", event.transform);
        });
        
    svg.call(zoom);

    function loadTree(userId, levels = 5) {
        loading.style.display = "block";
        const url = base + "/api/mlm/tree-data?user_id=" + encodeURIComponent(userId) + "&levels=" + encodeURIComponent(levels);
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                loading.style.display = "none";
                if (!data || data.error) {
                    loading.innerHTML = '<span class="text-danger">' + (data.error || 'Failed to load tree') + '</span>';
                    loading.style.display = "block";
                    return;
                }
                renderD3Tree(data);
            })
            .catch(err => {
                loading.innerHTML = '<span class="text-danger">Network error loading tree</span>';
                loading.style.display = "block";
            });
    }

    function renderD3Tree(rootData) {
        g.selectAll("*").remove();
        
        const width = container.clientWidth || 900;
        const height = container.clientHeight || 600;

        const hierarchyRoot = d3.hierarchy(rootData);
        const treeLayout = d3.tree().nodeSize([140, 100]);
        treeLayout(hierarchyRoot);

        // Links
        g.selectAll(".tree-link")
            .data(hierarchyRoot.links())
            .enter()
            .append("path")
            .attr("class", "tree-link")
            .attr("d", d3.linkVertical()
                .x(d => d.x)
                .y(d => d.y)
            );

        // Nodes
        const node = g.selectAll(".tree-node")
            .data(hierarchyRoot.descendants())
            .enter()
            .append("g")
            .attr("class", "tree-node")
            .attr("transform", d => `translate(${d.x},${d.y})`)
            .on("click", (event, d) => showMemberDetails(d.data));

        // Node Circle
        node.append("circle")
            .attr("r", d => d.depth === 0 ? 22 : 18)
            .attr("class", d => d.depth === 0 ? "node-circle root" : "node-circle")
            .style("fill", d => {
                if (d.depth === 0) return "#f59e0b";
                return d.data.is_active ? "#0d9488" : "#94a3b8";
            });

        // Icon inside circle
        node.append("text")
            .attr("text-anchor", "middle")
            .attr("dy", 5)
            .attr("fill", "#ffffff")
            .attr("font-family", "Font Awesome 5 Free, FontAwesome")
            .attr("font-size", d => d.depth === 0 ? "13px" : "11px")
            .text("\uf007");

        // Name
        node.append("text")
            .attr("class", "node-text-name")
            .attr("dy", 35)
            .text(d => {
                const n = d.data.name || 'Associate';
                return n.length > 14 ? n.substring(0, 12) + '...' : n;
            });

        // Code / ID
        node.append("text")
            .attr("class", "node-text-id")
            .attr("dy", 48)
            .text(d => d.data.referral_code || ('ID: ' + d.data.id));

        // Position Badge (Left/Right)
        node.filter(d => d.data.position)
            .append("text")
            .attr("class", "node-badge")
            .attr("dy", -24)
            .text(d => d.data.position === 'left' ? 'LEFT' : 'RIGHT');

        // Center on root node
        const initialTransform = d3.zoomIdentity.translate(width / 2, 70).scale(0.9);
        svg.transition().duration(500).call(zoom.transform, initialTransform);
    }

    function showMemberDetails(member) {
        document.getElementById('modalMemberName').textContent = member.name || 'Associate';
        document.getElementById('modalMemberCode').textContent = 'Ref Code: ' + (member.referral_code || 'APS' + member.id);
        document.getElementById('modalMemberRank').textContent = (member.current_level || 'Associate').toUpperCase();
        document.getElementById('modalMemberTeam').textContent = (member.total_team_size || member.team_size || 0) + ' members';
        document.getElementById('modalMemberLeg').textContent = (member.position ? member.position.toUpperCase() : 'Root');
        document.getElementById('modalMemberSales').textContent = '₹' + Number(member.lifetime_sales || 0).toLocaleString();
        
        const callBtn = document.getElementById('modalCallBtn');
        if (member.phone) {
            callBtn.href = 'tel:' + member.phone;
            callBtn.style.display = 'inline-flex';
        } else {
            callBtn.style.display = 'none';
        }

        const focusBtn = document.getElementById('modalFocusBtn');
        focusBtn.onclick = function() {
            rootUserId = member.id;
            loadTree(member.id, document.getElementById('levelsSelect').value);
            memberModal.hide();
        };

        memberModal.show();
    }

    // Zoom controls
    document.getElementById('btnZoomIn').addEventListener('click', () => {
        svg.transition().duration(250).call(zoom.scaleBy, 1.3);
    });
    document.getElementById('btnZoomOut').addEventListener('click', () => {
        svg.transition().duration(250).call(zoom.scaleBy, 0.77);
    });
    document.getElementById('btnResetZoom').addEventListener('click', () => {
        const width = container.clientWidth || 900;
        const initialTransform = d3.zoomIdentity.translate(width / 2, 70).scale(0.9);
        svg.transition().duration(400).call(zoom.transform, initialTransform);
    });
    document.getElementById('btnResetRoot').addEventListener('click', () => {
        rootUserId = currentUserId;
        loadTree(currentUserId, document.getElementById('levelsSelect').value);
    });
    document.getElementById('levelsSelect').addEventListener('change', (e) => {
        loadTree(rootUserId, e.target.value);
    });

    // Initial load
    loadTree(currentUserId, 5);
})();
</script>
