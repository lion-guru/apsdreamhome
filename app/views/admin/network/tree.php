<?php
/**
 * Network Tree View
 */
$network_data = $network_data ?? [];
$selected_associate = $selected_associate ?? 0;
$page_title = $page_title ?? 'Network Tree';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .tree-container { overflow-x: auto; padding: 20px; }
        .tree-node { 
            display: inline-block; 
            padding: 10px 15px; 
            margin: 5px; 
            background: #fff; 
            border: 2px solid #e2e8f0; 
            border-radius: 8px; 
            text-align: center;
            min-width: 150px;
        }
        .tree-node.active { border-color: #4f46e5; background: #eef2ff; }
        .tree-level { display: flex; justify-content: center; margin: 10px 0; }
        .connectors { display: flex; justify-content: center; }
        .connector { width: 2px; height: 20px; background: #cbd5e1; margin: 0 75px; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Network Tree</h2>
                <p class="text-muted mb-0">View MLM network structure</p>
            </div>
            <div>
                <a href="<?php echo $base; ?>/admin/network" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
                <a href="<?php echo $base; ?>/admin/network/ranks" class="btn btn-outline-primary">
                    <i class="fas fa-trophy me-2"></i>Ranks
                </a>
            </div>
        </div>
        
        <!-- Search -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Associate ID</label>
                        <input type="number" name="associate_id" class="form-control" value="<?php echo $selected_associate; ?>" placeholder="Enter associate ID">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>View Tree
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tree Display -->
        <div class="card border-0 shadow-sm">
            <div class="card-body tree-container">
                <?php if (!empty($network_data['associate'])): ?>
                    <!-- Root Node -->
                    <div class="tree-level">
                        <div class="tree-node active">
                            <i class="fas fa-user-circle fa-2x text-primary mb-2"></i>
                            <h5 class="mb-1"><?php echo htmlspecialchars($network_data['associate']['name'] ?? 'Unknown'); ?></h5>
                            <p class="text-muted mb-1"><?php echo htmlspecialchars($network_data['associate']['mlm_rank'] ?? 'Associate'); ?></p>
                            <span class="badge bg-success">Active</span>
                        </div>
                    </div>
                    
                    <?php if (!empty($network_data['downline'])): ?>
                        <div class="connectors">
                            <div class="connector"></div>
                        </div>
                        
                        <!-- Downline -->
                        <div class="tree-level">
                            <?php foreach ($network_data['downline'] as $member): ?>
                                <div class="tree-node">
                                    <i class="fas fa-user text-secondary mb-2"></i>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($member['name'] ?? 'Unknown'); ?></h6>
                                    <p class="text-muted small mb-1">Level <?php echo $member['level'] ?? 1; ?></p>
                                    <span class="badge bg-info"><?php echo $member['direct_referrals'] ?? 0; ?> Referrals</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-muted">No downline members found</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if (!empty($network_data['root_associates'])): ?>
                        <div class="tree-level">
                            <?php foreach ($network_data['root_associates'] as $root): ?>
                                <div class="tree-node">
                                    <i class="fas fa-user-circle fa-2x text-primary mb-2"></i>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($root['name'] ?? 'Unknown'); ?></h5>
                                    <p class="text-muted mb-1">Root Associate</p>
                                    <a href="?associate_id=<?php echo $root['id']; ?>" class="btn btn-sm btn-outline-primary">View Tree</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No network data available</p>
                            <p class="small text-muted">Enter an associate ID to view their network tree</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
