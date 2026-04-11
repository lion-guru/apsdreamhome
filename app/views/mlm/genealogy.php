<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Network | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            min-height: 100vh;
            color: #fff;
        }
        
        /* Header */
        .main-header {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .main-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }
        .main-header h1 i {
            color: #fbbf24;
            margin-right: 12px;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 20px 30px;
        }
        .stat-card {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.12);
        }
        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        .stat-card .value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stat-card .label {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Tree Container */
        .tree-container {
            position: relative;
            height: calc(100vh - 300px);
            min-height: 600px;
            margin: 0 30px 30px;
            background: rgba(255,255,255,0.03);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        /* SVG Styles */
        #tree-svg {
            width: 100%;
            height: 100%;
            cursor: grab;
        }
        #tree-svg:active {
            cursor: grabbing;
        }
        
        .node circle {
            fill: #4f46e5;
            stroke: #fff;
            stroke-width: 3px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .node circle:hover {
            fill: #7c3aed;
            stroke: #fbbf24;
            stroke-width: 4px;
            filter: drop-shadow(0 0 10px rgba(124,58,237,0.6));
        }
        .node circle.root {
            fill: #fbbf24;
            stroke: #f59e0b;
            stroke-width: 4px;
        }
        .node circle.root:hover {
            fill: #f59e0b;
        }
        .node text {
            font-size: 12px;
            fill: #fff;
            text-anchor: middle;
            pointer-events: none;
            font-weight: 500;
        }
        .node .email-text {
            font-size: 10px;
            fill: rgba(255,255,255,0.6);
        }
        .node .id-text {
            font-size: 9px;
            fill: #fbbf24;
            font-weight: 600;
        }
        
        .link {
            fill: none;
            stroke: rgba(255,255,255,0.2);
            stroke-width: 2px;
        }
        
        /* Controls */
        .controls {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 100;
        }
        .control-btn {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            border: none;
            background: rgba(255,255,255,0.1);
            color: #fff;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .control-btn:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }
        .control-btn.active {
            background: #4f46e5;
        }
        
        /* Search Box */
        .search-container {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 100;
        }
        .search-box {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 12px 20px;
            width: 300px;
            color: #fff;
            font-size: 14px;
            backdrop-filter: blur(10px);
        }
        .search-box::placeholder {
            color: rgba(255,255,255,0.5);
        }
        .search-box:focus {
            outline: none;
            border-color: #4f46e5;
            background: rgba(255,255,255,0.15);
        }
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            margin-top: 8px;
            background: rgba(30,27,75,0.95);
            border-radius: 12px;
            max-height: 300px;
            overflow-y: auto;
            display: none;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .search-results.active {
            display: block;
        }
        .search-result-item {
            padding: 12px 20px;
            cursor: pointer;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            transition: background 0.2s;
        }
        .search-result-item:hover {
            background: rgba(255,255,255,0.1);
        }
        .search-result-item .name {
            font-weight: 600;
            color: #fff;
        }
        .search-result-item .id {
            font-size: 0.8rem;
            color: #fbbf24;
        }
        
        /* Upline Panel */
        .upline-panel {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(30,27,75,0.9);
            border-radius: 16px;
            padding: 20px;
            max-width: 300px;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        .upline-panel h4 {
            margin: 0 0 15px 0;
            font-size: 1rem;
            color: #fbbf24;
        }
        .upline-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .upline-item:last-child {
            border-bottom: none;
        }
        .upline-item .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        .upline-item .info {
            flex: 1;
        }
        .upline-item .name {
            font-weight: 600;
            font-size: 0.9rem;
        }
        .upline-item .id {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.5);
        }
        .upline-item .level {
            font-size: 0.75rem;
            color: #fbbf24;
            background: rgba(251,191,36,0.2);
            padding: 2px 8px;
            border-radius: 10px;
        }
        
        /* Member Details Modal */
        .member-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }
        .member-modal.active {
            display: flex;
        }
        .member-modal-content {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            border-radius: 24px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
        }
        .member-modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .member-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 20px;
        }
        .member-name {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .member-id {
            text-align: center;
            color: #fbbf24;
            margin-bottom: 25px;
        }
        .member-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        .member-stat {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
        }
        .member-stat .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fbbf24;
        }
        .member-stat .label {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
        }
        
        /* Legend */
        .legend {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(30,27,75,0.9);
            border-radius: 12px;
            padding: 15px 20px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 0.85rem;
        }
        .legend-item:last-child {
            margin-bottom: 0;
        }
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 50%;
        }
        
        /* Loading */
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255,255,255,0.1);
            border-top-color: #fbbf24;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            .main-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="main-header">
        <div>
            <h1><i class="fas fa-sitemap"></i>My Network</h1>
            <p class="mb-0 text-white-50">Visualize your entire referral genealogy</p>
        </div>
        <div>
            <a href="<?php echo $base; ?>/associate/dashboard" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="icon text-warning"><i class="fas fa-users"></i></div>
            <div class="value"><?php echo number_format($stats['total_members']); ?></div>
            <div class="label">Total Team Size</div>
        </div>
        <div class="stat-card">
            <div class="icon text-success"><i class="fas fa-user-plus"></i></div>
            <div class="value"><?php echo number_format($stats['direct_referrals']); ?></div>
            <div class="label">Direct Referrals</div>
        </div>
        <div class="stat-card">
            <div class="icon text-info"><i class="fas fa-layer-group"></i></div>
            <div class="value"><?php echo $stats['max_depth']; ?></div>
            <div class="label">Network Depth</div>
        </div>
        <div class="stat-card">
            <div class="icon" style="color: #f472b6;"><i class="fas fa-rupee-sign"></i></div>
            <div class="value">₹<?php echo number_format($stats['total_team_commission'], 2); ?></div>
            <div class="label">Team Commission</div>
        </div>
    </div>

    <!-- Tree Container -->
    <div class="tree-container">
        <!-- Loading -->
        <div class="loading" id="loading">
            <div class="loading-spinner"></div>
            <p>Loading your network...</p>
        </div>

        <!-- Search -->
        <div class="search-container">
            <input type="text" class="search-box" id="searchBox" placeholder="Search members by name, email, or ID...">
            <div class="search-results" id="searchResults"></div>
        </div>

        <!-- Controls -->
        <div class="controls">
            <button class="control-btn" id="zoomIn" title="Zoom In"><i class="fas fa-plus"></i></button>
            <button class="control-btn" id="zoomOut" title="Zoom Out"><i class="fas fa-minus"></i></button>
            <button class="control-btn" id="resetView" title="Reset View"><i class="fas fa-compress-arrows-alt"></i></button>
            <button class="control-btn" id="expandAll" title="Expand All"><i class="fas fa-expand"></i></button>
            <button class="control-btn" id="collapseAll" title="Collapse All"><i class="fas fa-compress"></i></button>
            <button class="control-btn" id="exportSvg" title="Export as SVG"><i class="fas fa-download"></i></button>
        </div>

        <!-- SVG Container -->
        <svg id="tree-svg"></svg>

        <!-- Upline Panel -->
        <?php if (!empty($upline)): ?>
        <div class="upline-panel">
            <h4><i class="fas fa-arrow-up me-2"></i>Your Upline</h4>
            <?php foreach ($upline as $index => $parent): ?>
            <div class="upline-item">
                <div class="avatar"><?php echo strtoupper(substr($parent['name'], 0, 1)); ?></div>
                <div class="info">
                    <div class="name"><?php echo htmlspecialchars($parent['name']); ?></div>
                    <div class="id"><?php echo $parent['customer_id']; ?></div>
                </div>
                <span class="level">Level <?php echo $parent['level']; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Legend -->
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #fbbf24;"></div>
                <span>You (Root)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #4f46e5;"></div>
                <span>Direct Referral</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #7c3aed;"></div>
                <span>Extended Network</span>
            </div>
        </div>
    </div>

    <!-- Member Details Modal -->
    <div class="member-modal" id="memberModal">
        <div class="member-modal-content">
            <button class="member-modal-close" onclick="closeModal()">&times;</button>
            <div class="member-avatar" id="modalAvatar">?</div>
            <div class="member-name" id="modalName">Member Name</div>
            <div class="member-id" id="modalId">ID: ABC123</div>
            
            <div class="member-stats">
                <div class="member-stat">
                    <div class="value" id="modalWallet">₹0</div>
                    <div class="label">Wallet</div>
                </div>
                <div class="member-stat">
                    <div class="value" id="modalCommission">₹0</div>
                    <div class="label">Commission</div>
                </div>
                <div class="member-stat">
                    <div class="value" id="modalTeam">0</div>
                    <div class="label">Team Size</div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <a href="#" class="btn btn-primary" id="modalViewProfile">
                    <i class="fas fa-user me-2"></i>View Profile
                </a>
                <button class="btn btn-outline-light" onclick="focusOnNode(currentNodeId)">
                    <i class="fas fa-crosshairs me-2"></i>Focus on Tree
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let svg, g, tree, root;
        let zoom;
        let i = 0;
        let currentNodeId = null;
        const duration = 750;
        const baseUrl = '<?php echo $base; ?>';
        const rootId = <?php echo $currentUser['id']; ?>;
        
        // Initialize tree
        document.addEventListener('DOMContentLoaded', function() {
            initTree();
            loadTreeData();
        });
        
        function initTree() {
            const container = document.querySelector('.tree-container');
            const width = container.clientWidth;
            const height = container.clientHeight;
            
            // Create SVG
            svg = d3.select('#tree-svg')
                .attr('width', width)
                .attr('height', height);
            
            // Add zoom behavior
            zoom = d3.zoom()
                .scaleExtent([0.1, 4])
                .on('zoom', (event) => {
                    g.attr('transform', event.transform);
                });
            
            svg.call(zoom);
            
            // Create main group
            g = svg.append('g')
                .attr('transform', `translate(${width / 2}, 100)`);
            
            // Setup tree layout
            tree = d3.tree()
                .size([width - 100, height - 200]);
            
            // Control buttons
            document.getElementById('zoomIn').addEventListener('click', () => {
                svg.transition().call(zoom.scaleBy, 1.3);
            });
            
            document.getElementById('zoomOut').addEventListener('click', () => {
                svg.transition().call(zoom.scaleBy, 0.7);
            });
            
            document.getElementById('resetView').addEventListener('click', () => {
                svg.transition().call(zoom.transform, d3.zoomIdentity);
                centerRoot();
            });
            
            document.getElementById('expandAll').addEventListener('click', expandAll);
            document.getElementById('collapseAll').addEventListener('click', collapseAll);
            document.getElementById('exportSvg').addEventListener('click', exportSvg);
            
            // Search
            const searchBox = document.getElementById('searchBox');
            let searchTimeout;
            
            searchBox.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    document.getElementById('searchResults').classList.remove('active');
                    return;
                }
                
                searchTimeout = setTimeout(() => searchMembers(query), 300);
            });
            
            // Close modal on outside click
            document.getElementById('memberModal').addEventListener('click', function(e) {
                if (e.target === this) closeModal();
            });
        }
        
        function loadTreeData() {
            fetch(`${baseUrl}/api/mlm/tree-data?root_id=${rootId}&levels=5`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                        return;
                    }
                    
                    document.getElementById('loading').style.display = 'none';
                    renderTree(data);
                })
                .catch(error => {
                    console.error('Error loading tree:', error);
                    document.getElementById('loading').innerHTML = '<p class="text-danger">Failed to load network data</p>';
                });
        }
        
        function renderTree(data) {
            // Convert data to hierarchy
            root = d3.hierarchy(data);
            
            // Collapse after level 2
            root.children && root.children.forEach(collapse);
            
            update(root);
            
            // Center the root node
            centerRoot();
        }
        
        function update(source) {
            const treeData = tree(root);
            
            // Compute the new tree layout
            const nodes = treeData.descendants();
            const links = treeData.links();
            
            // Normalize for fixed-depth
            nodes.forEach(d => { d.y = d.depth * 120; });
            
            // ****************** Nodes ******************
            const node = g.selectAll('g.node')
                .data(nodes, d => d.id || (d.id = ++i));
            
            // Enter any new nodes at the parent's previous position
            const nodeEnter = node.enter().append('g')
                .attr('class', 'node')
                .attr('transform', d => `translate(${source.x0 || source.x},${source.y0 || source.y})`)
                .on('click', (event, d) => {
                    if (d.children || d._children) {
                        toggle(d);
                        update(d);
                    }
                    showMemberDetails(d.data.id);
                });
            
            // Add circle
            nodeEnter.append('circle')
                .attr('r', 1e-6)
                .attr('class', d => d.depth === 0 ? 'root' : '');
            
            // Add labels
            nodeEnter.append('text')
                .attr('dy', -35)
                .attr('class', 'name-text')
                .text(d => truncateText(d.data.name, 20))
                .style('fill-opacity', 1e-6);
            
            nodeEnter.append('text')
                .attr('dy', -20)
                .attr('class', 'id-text')
                .text(d => d.data.customer_id)
                .style('fill-opacity', 1e-6);
            
            nodeEnter.append('text')
                .attr('dy', 45)
                .attr('class', 'email-text')
                .text(d => `Team: ${d.data.team_size || 0}`)
                .style('fill-opacity', 1e-6);
            
            // UPDATE
            const nodeUpdate = node.merge(nodeEnter);
            
            // Transition to the proper position
            nodeUpdate.transition()
                .duration(duration)
                .attr('transform', d => `translate(${d.x},${d.y})`);
            
            // Update the circle attributes
            nodeUpdate.select('circle')
                .attr('r', d => d.depth === 0 ? 25 : 18)
                .style('fill', d => d._children ? '#7c3aed' : (d.depth === 0 ? '#fbbf24' : '#4f46e5'))
                .attr('cursor', 'pointer');
            
            // Update text
            nodeUpdate.selectAll('text')
                .style('fill-opacity', 1);
            
            // Remove any exiting nodes
            const nodeExit = node.exit().transition()
                .duration(duration)
                .attr('transform', d => `translate(${source.x},${source.y})`)
                .remove();
            
            nodeExit.select('circle').attr('r', 1e-6);
            nodeExit.selectAll('text').style('fill-opacity', 1e-6);
            
            // ****************** Links ******************
            const link = g.selectAll('path.link')
                .data(links, d => d.target.id);
            
            // Enter any new links at the parent's previous position
            const linkEnter = link.enter().insert('path', 'g')
                .attr('class', 'link')
                .attr('d', d => {
                    const o = {x: source.x0 || source.x, y: source.y0 || source.y};
                    return diagonal(o, o);
                });
            
            // UPDATE
            const linkUpdate = link.merge(linkEnter);
            
            // Transition links to their new position
            linkUpdate.transition()
                .duration(duration)
                .attr('d', d => diagonal(d.source, d.target));
            
            // Remove any exiting links
            link.exit().transition()
                .duration(duration)
                .attr('d', d => {
                    const o = {x: source.x, y: source.y};
                    return diagonal(o, o);
                })
                .remove();
            
            // Store the old positions for transition
            nodes.forEach(d => {
                d.x0 = d.x;
                d.y0 = d.y;
            });
        }
        
        function diagonal(s, d) {
            return `M ${s.x} ${s.y}
                    C ${s.x} ${(s.y + d.y) / 2},
                      ${d.x} ${(s.y + d.y) / 2},
                      ${d.x} ${d.y}`;
        }
        
        function toggle(d) {
            if (d.children) {
                d._children = d.children;
                d.children = null;
            } else {
                d.children = d._children;
                d._children = null;
            }
        }
        
        function collapse(d) {
            if (d.children) {
                d._children = d.children;
                d._children.forEach(collapse);
                d.children = null;
            }
        }
        
        function expand(d) {
            if (d._children) {
                d.children = d._children;
                d.children.forEach(expand);
                d._children = null;
            }
        }
        
        function collapseAll() {
            if (root.children) {
                root.children.forEach(collapse);
            }
            update(root);
            centerRoot();
        }
        
        function expandAll() {
            if (root._children) {
                root.children = root._children;
            }
            if (root.children) {
                root.children.forEach(expand);
            }
            update(root);
            centerRoot();
        }
        
        function centerRoot() {
            const container = document.querySelector('.tree-container');
            const width = container.clientWidth;
            const height = container.clientHeight;
            
            svg.transition().duration(750).call(
                zoom.transform,
                d3.zoomIdentity.translate(width / 2, 80).scale(1)
            );
        }
        
        function truncateText(text, maxLength) {
            if (!text) return '';
            return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
        }
        
        // Search functionality
        function searchMembers(query) {
            fetch(`${baseUrl}/api/mlm/search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(results => {
                    displaySearchResults(results);
                })
                .catch(error => console.error('Search error:', error));
        }
        
        function displaySearchResults(results) {
            const container = document.getElementById('searchResults');
            
            if (results.length === 0) {
                container.innerHTML = '<div class="search-result-item"><span class="text-muted">No results found</span></div>';
                container.classList.add('active');
                return;
            }
            
            container.innerHTML = results.map(member => `
                <div class="search-result-item" onclick="focusOnNode(${member.id})">
                    <div class="name">${member.name}</div>
                    <div class="id">${member.customer_id} • Level ${member.level}</div>
                </div>
            `).join('');
            
            container.classList.add('active');
        }
        
        function focusOnNode(nodeId) {
            document.getElementById('searchResults').classList.remove('active');
            document.getElementById('searchBox').value = '';
            
            // Find node in tree
            let targetNode = null;
            root.descendants().forEach(d => {
                if (d.data.id == nodeId) {
                    targetNode = d;
                }
            });
            
            if (targetNode) {
                // Expand path to node
                let current = targetNode;
                while (current.parent) {
                    if (current.parent._children) {
                        current.parent.children = current.parent._children;
                        current.parent._children = null;
                    }
                    current = current.parent;
                }
                
                update(root);
                
                // Center on node
                const container = document.querySelector('.tree-container');
                const width = container.clientWidth;
                const height = container.clientHeight;
                
                svg.transition().duration(750).call(
                    zoom.transform,
                    d3.zoomIdentity
                        .translate(width / 2 - targetNode.x, height / 2 - targetNode.y)
                        .scale(1.5)
                );
                
                showMemberDetails(nodeId);
            }
        }
        
        // Member details modal
        function showMemberDetails(memberId) {
            currentNodeId = memberId;
            
            fetch(`${baseUrl}/api/mlm/member-details?id=${memberId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) return;
                    
                    const member = data.member;
                    
                    document.getElementById('modalAvatar').textContent = member.name.charAt(0).toUpperCase();
                    document.getElementById('modalName').textContent = member.name;
                    document.getElementById('modalId').textContent = `ID: ${member.customer_id}`;
                    document.getElementById('modalWallet').textContent = '₹' + parseFloat(member.points_balance || 0).toLocaleString();
                    document.getElementById('modalCommission').textContent = '₹' + parseFloat(member.commission_earnings || 0).toLocaleString();
                    document.getElementById('modalTeam').textContent = member.total_team_size || 0;
                    document.getElementById('modalViewProfile').href = `${baseUrl}/admin/users/${member.id}`;
                    
                    document.getElementById('memberModal').classList.add('active');
                })
                .catch(error => console.error('Error loading member:', error));
        }
        
        function closeModal() {
            document.getElementById('memberModal').classList.remove('active');
        }
        
        // Export SVG
        function exportSvg() {
            const svgElement = document.getElementById('tree-svg');
            const svgData = new XMLSerializer().serializeToString(svgElement);
            
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            
            img.onload = function() {
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
                
                const link = document.createElement('a');
                link.download = 'my-network-aps-dream-home.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            };
            
            img.src = 'data:image/svg+xml;base64,' + btoa(svgData);
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                document.getElementById('searchResults').classList.remove('active');
            }
        });
    </script>
</body>
</html>
