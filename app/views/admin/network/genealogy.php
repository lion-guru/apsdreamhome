<?php
/**
 * Genealogy Report View
 */
$genealogy_data = $genealogy_data ?? [];
$associate_id = $associate_id ?? 0;
$levels = $levels ?? 5;
$page_title = $page_title ?? 'Genealogy Report';
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
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Genealogy Report</h2>
                <p class="text-muted mb-0">Complete downline analysis</p>
            </div>
            <div>
                <a href="<?php echo $base; ?>/admin/network/tree?associate_id=<?php echo $associate_id; ?>" class="btn btn-outline-primary me-2">Tree View</a>
                <a href="<?php echo $base; ?>/admin/network" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
        
        <?php if (!empty($genealogy_data['root'])): ?>
        <!-- Root Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="mb-1"><?php echo htmlspecialchars($genealogy_data['root']['name'] ?? 'Unknown'); ?></h4>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($genealogy_data['root']['email'] ?? ''); ?></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span class="badge bg-primary me-2"><?php echo $genealogy_data['root']['mlm_rank'] ?? 'Associate'; ?></span>
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h3 class="mb-1"><?php echo $genealogy_data['stats']['total_members'] ?? 0; ?></h3>
                        <p class="text-muted mb-0">Total Members</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                        <h3 class="mb-1"><?php echo $genealogy_data['stats']['active_members'] ?? 0; ?></h3>
                        <p class="text-muted mb-0">Active Members</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-layer-group fa-2x text-warning mb-2"></i>
                        <h3 class="mb-1"><?php echo $genealogy_data['stats']['levels'] ?? $levels; ?></h3>
                        <p class="text-muted mb-0">Levels Deep</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-percentage fa-2x text-info mb-2"></i>
                        <h3 class="mb-1">
                            <?php 
                                $total = $genealogy_data['stats']['total_members'] ?? 0;
                                $active = $genealogy_data['stats']['active_members'] ?? 0;
                                echo $total > 0 ? round(($active / $total) * 100) : 0;
                            ?>%
                        </h3>
                        <p class="text-muted mb-0">Activity Rate</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Downline Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Downline Members</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($genealogy_data['downline'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Level</th>
                                    <th>Rank</th>
                                    <th>Referrals</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($genealogy_data['downline'] as $member): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($member['name'] ?? 'Unknown'); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($member['email'] ?? ''); ?></small>
                                        </td>
                                        <td><?php echo $member['level'] ?? 1; ?></td>
                                        <td><?php echo htmlspecialchars($member['mlm_rank'] ?? 'Associate'); ?></td>
                                        <td><?php echo $member['direct_referrals'] ?? 0; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($member['status'] ?? '') === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($member['status'] ?? 'unknown'); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No downline members found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Genealogy data not available. Please select an associate.
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
