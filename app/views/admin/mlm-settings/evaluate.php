<?php
$results = $results ?? [];
$promoted_count = $promoted_count ?? 0;
$total_count = $total_count ?? 0;
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-sync text-success me-2"></i>Rank Evaluation Results</h4>
        <a href="<?php echo BASE_URL; ?>/admin/mlm-settings/levels" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Levels
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body aps-cp-card-body">
                    <h6>Promoted</h6>
                    <h3 class="mb-0"><?php echo e($promoted_count); ?> / <?php echo $total_count; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body aps-cp-card-body">
                    <h6>Total Evaluated</h6>
                    <h3 class="mb-0"><?php echo e($total_count); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body aps-cp-card-body">
                    <h6>To Re-run</h6>
                    <a href="<?php echo BASE_URL; ?>/admin/mlm-settings/evaluate" class="btn btn-light btn-sm mt-2">Re-evaluate Now</a>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-dark">
                <tr>
                    <th>User ID</th>
                    <th>Current Level</th>
                    <th>Eligible Level</th>
                    <th>Team Size</th>
                    <th>Direct Refs</th>
                    <th>Monthly Sales</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)): ?>
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-sync fa-3x text-muted mb-3 style-82835"></i>
                        <h5 class="text-muted">No evaluation results</h5>
                        <p class="text-muted mb-3">Run a rank evaluation to see promotion results here.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($results as $r): ?>
                <tr class="<?php echo ($r['promoted'] ?? false) ? 'table-success' : ''; ?>">
                    <td><?php echo $r['user_id'] ?? 'N/A'; ?></td>
                    <td><?php echo htmlspecialchars($r['current_level'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($r['eligible_level'] ?? 'N/A'); ?></td>
                    <td><?php echo $r['team_size'] ?? 0; ?></td>
                    <td><?php echo $r['direct_referrals'] ?? 0; ?></td>
                    <td>₹<?php echo number_format($r['monthly_sales'] ?? 0); ?></td>
                    <td>
                        <?php if ($r['promoted'] ?? false): ?>
                            <span class="badge bg-success">Promoted!</span>
                        <?php elseif (isset($r['error'])): ?>
                            <span class="badge bg-danger"><?php echo htmlspecialchars($r['error'] ?? ''); ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary">No change</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
