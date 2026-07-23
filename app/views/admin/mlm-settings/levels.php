<?php $levels = $levels ?? []; ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-layer-group text-primary me-2"></i>MLM Levels</h4>
        <a href="<?php echo BASE_URL; ?>/admin/mlm-settings/evaluate" class="btn btn-success btn-sm">
            <i class="fas fa-sync me-1"></i>Evaluate All Ranks
        </a>
    </div>

    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo $msg; ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo $msg; ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Level</th>
                    <th>Direct %</th>
                    <th>Team %</th>
                    <th>Diff %</th>
                    <th>Matching</th>
                    <th>Team Size</th>
                    <th>Direct Refs</th>
                    <th>Monthly Target</th>
                    <th>Joining Fee</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($levels)): ?>
                <tr>
                    <td colspan="11" class="text-center py-5">
                        <i class="fas fa-layer-group fa-3x text-muted mb-3" style="opacity:0.2"></i>
                        <h5 class="text-muted">No MLM levels found</h5>
                        <p class="text-muted mb-3">Configure MLM levels to define the commission and ranking structure.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($levels as $l): ?>
                <tr>
                    <td><?php echo $l['level_number']; ?></td>
                    <td><strong><?php echo htmlspecialchars($l['level_name']); ?></strong></td>
                    <td><?php echo $l['direct_commission_percentage']; ?>%</td>
                    <td><?php echo $l['team_commission_percentage']; ?>%</td>
                    <td><?php echo $l['level_difference_commission_percentage']; ?>%</td>
                    <td><?php echo $l['matching_bonus_percentage']; ?>%</td>
                    <td><?php echo $l['team_size_required']; ?></td>
                    <td><?php echo $l['direct_referrals_required']; ?></td>
                    <td>₹<?php echo number_format($l['monthly_target']); ?></td>
                    <td>₹<?php echo number_format($l['joining_fee']); ?></td>
                    <td><a href="<?php echo BASE_URL; ?>/admin/mlm-settings/levels/edit/<?php echo $l['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
