<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'MLM Genealogy'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .genealogy-tree {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
        }
        .tree {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .tree-node {
            position: relative;
            padding: 15px 20px;
            margin: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            min-width: 200px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .tree-node:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .tree-node.active {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .tree-node.level-1 { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); }
        .tree-node.level-2 { background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%); }
        .tree-node.level-3 { background: linear-gradient(135deg, #45b7d1 0%, #96c93d 100%); }
        .tree-node.level-4 { background: linear-gradient(135deg, #f9ca24 0%, #f0932b 100%); }
        .tree-node.level-5 { background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%); }
        .tree-node.level-6 { background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%); }
        .tree-node.level-7 { background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%); }

        .node-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .node-name {
            font-weight: bold;
            font-size: 1.1em;
            margin-bottom: 5px;
        }
        .node-details {
            font-size: 0.9em;
            opacity: 0.9;
        }
        .node-level {
            position: absolute;
            top: -8px;
            right: -8px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            font-weight: bold;
        }
        .tree-line {
            width: 2px;
            background: #007bff;
            position: absolute;
        }
        .tree-horizontal {
            height: 2px;
            background: #007bff;
            position: absolute;
        }
        .tree-children {
            display: flex;
            justify-content: center;
            position: relative;
            margin-top: 30px;
        }
        .tree-children::before {
            content: '';
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 20px;
            background: #007bff;
        }
        .controls {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/layouts/associate_header.php'; ?>

    <div class="container-fluid genealogy-tree">
        <!-- Controls -->
        <div class="controls">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3><i class="fas fa-project-diagram me-2"></i>Your MLM Genealogy Tree</h3>
                    <p class="text-muted">Visual representation of your network structure</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="zoomIn()">
                            <i class="fas fa-search-plus"></i> Zoom In
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="zoomOut()">
                            <i class="fas fa-search-minus"></i> Zoom Out
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="resetView()">
                            <i class="fas fa-expand"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h4><?php echo $associate_info['total_downline'] ?? 0; ?></h4>
                    <p class="mb-0">Total Downline</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-layer-group fa-2x mb-2"></i>
                    <h4><?php echo count($genealogy['children'] ?? []); ?></h4>
                    <p class="mb-0">Direct Referrals</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-star fa-2x mb-2"></i>
                    <h4><?php echo $associate_info['level'] ?? 1; ?></h4>
                    <p class="mb-0">Current Level</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-coins fa-2x mb-2"></i>
                    <h4>₹<?php echo number_format($associate_info['total_commission'] ?? 0); ?></h4>
                    <p class="mb-0">Total Commission</p>
                </div>
            </div>
        </div>

        <!-- Genealogy Tree -->
        <div class="tree-container">
            <div class="tree" id="genealogyTree">
                <!-- Root Node (Current User) -->
                <div class="tree-node level-<?php echo $associate_info['level'] ?? 1; ?> active" data-node-id="<?php echo $associate_info['user_id'] ?? 0; ?>">
                    <div class="node-level">L<?php echo $associate_info['level'] ?? 1; ?></div>
                    <div class="node-content">
                        <div class="node-name">You</div>
                        <div class="node-details">
                            <?php echo htmlspecialchars($associate_info['associate_name'] ?? ''); ?><br>
                            <small><?php echo htmlspecialchars($associate_info['associate_email'] ?? ''); ?></small>
                        </div>
                    </div>
                </div>

                <!-- Tree Children -->
                <div class="tree-children">
                    <?php if (!empty($genealogy['children'])): ?>
                        <?php foreach ($genealogy['children'] as $child): ?>
                            <div class="tree-node level-<?php echo $child['level']; ?>" data-node-id="<?php echo $child['id']; ?>">
                                <div class="node-level">L<?php echo $child['level']; ?></div>
                                <div class="node-content">
                                    <div class="node-name"><?php echo htmlspecialchars($child['name'] ?? 'Unknown'); ?></div>
                                    <div class="node-details">
                                        <?php echo htmlspecialchars($child['city'] ?? ''); ?>
                                        <?php if (!empty($child['city']) && !empty($child['state'])) echo ', '; ?>
                                        <?php echo htmlspecialchars($child['state'] ?? ''); ?><br>
                                        <small>Joined: <?php echo date('M d, Y', strtotime($child['joining_date'])); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <p>No downline members yet. Start building your network!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Level Guide</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($mlm_levels as $level => $config): ?>
                                <div class="col-md-3 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="tree-node level-<?php echo $level; ?> me-3" style="width: 40px; height: 40px; padding: 8px;">
                                            <div class="node-level" style="font-size: 0.7em;">L<?php echo $level; ?></div>
                                        </div>
                                        <div>
                                            <strong><?php echo $config['name']; ?></strong><br>
                                            <small class="text-muted">
                                                Commission: <?php echo $config['commission']; ?>% |
                                                Downline: <?php echo $config['min_downline']; ?>-<?php echo $config['max_downline']; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Node Details Modal -->
    <div class="modal fade" id="nodeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Member Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="nodeDetails">
                    <!-- Details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let zoomLevel = 1;

        function zoomIn() {
            zoomLevel = Math.min(zoomLevel + 0.1, 2);
            document.getElementById('genealogyTree').style.transform = `scale(${zoomLevel})`;
        }

        function zoomOut() {
            zoomLevel = Math.max(zoomLevel - 0.1, 0.5);
            document.getElementById('genealogyTree').style.transform = `scale(${zoomLevel})`;
        }

        function resetView() {
            zoomLevel = 1;
            document.getElementById('genealogyTree').style.transform = `scale(${zoomLevel})`;
        }

        // Node click handler
        document.querySelectorAll('.tree-node').forEach(node => {
            node.addEventListener('click', function() {
                const nodeId = this.dataset.nodeId;
                if (nodeId) {
                    // Load node details
                    loadNodeDetails(nodeId);
                }
            });
        });

        function loadNodeDetails(nodeId) {
            // In a real implementation, this would make an AJAX call
            // For now, show a placeholder
            document.getElementById('nodeDetails').innerHTML = `
                <p><strong>Node ID:</strong> ${nodeId}</p>
                <p>Detailed information would be loaded here from the server.</p>
            `;
            new bootstrap.Modal(document.getElementById('nodeModal')).show();
        }
    </script>
</body>
</html>
