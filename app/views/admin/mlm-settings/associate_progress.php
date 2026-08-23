<?php
$users = $users ?? [];
$progress_data = $progress_data ?? [];
?>
<div class="container-fluid px-4">
    <h4 class="mb-4"><i class="fas fa-chart-line text-primary me-2"></i>Associate Rank Progress</h4>

    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Current Level</th>
                    <th>Next Level</th>
                    <th>Team Size</th>
                    <th>Team Progress</th>
                    <th>Direct Refs</th>
                    <th>Direct Progress</th>
                    <th>Sales</th>
                    <th>Sales Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3 style-82835"></i>
                        <h5 class="text-muted">No associates to display</h5>
                        <p class="text-muted mb-3">Rank progress will appear here once associates join the network.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($users as $i => $a):
                    $p = $progress_data[$i] ?? [];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['name'] ?? ''); ?><br><small class="text-muted"><?php echo htmlspecialchars($a['email'] ?? ''); ?></small></td>
                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($a['current_level'] ?? 'N/A'); ?></span></td>
                    <td><?php echo htmlspecialchars($p['next_level'] ?? 'Max level'); ?></td>
                    <td><?php echo $p['team_size'] ?? $a['total_team_size'] ?? 0; ?></td>
                    <td class="style-26283">
                        <?php $tp = $p['progress']['team_size_progress'] ?? null; if ($tp): ?>
                        <div class="progress style-51910"><div class="progress-bar bg-success style-73288"></div></div>
                        <small class="text-muted"><?php echo e($tp['current']); ?>/<?php echo $tp['required']; ?></small>
                        <?php else: ?><small class="text-muted">--</small><?php endif; ?>
                    </td>
                    <td><?php echo $a['direct_referrals'] ?? 0; ?></td>
                    <td class="style-26283">
                        <?php $dp = $p['progress']['direct_progress'] ?? null; if ($dp): ?>
                        <div class="progress style-51910"><div class="progress-bar bg-info style-8346"></div></div>
                        <small class="text-muted"><?php echo e($dp['current']); ?>/<?php echo $dp['required']; ?></small>
                        <?php else: ?><small class="text-muted">--</small><?php endif; ?>
                    </td>
                    <td>₹<?php echo number_format($p['monthly_sales'] ?? 0); ?></td>
                    <td class="style-26283">
                        <?php $sp = $p['progress']['sales_progress'] ?? null; if ($sp): ?>
                        <div class="progress style-51910"><div class="progress-bar bg-warning style-81610"></div></div>
                        <small class="text-muted">₹<?php echo number_format($sp['current']); ?>/₹<?php echo number_format($sp['required']); ?></small>
                        <?php else: ?><small class="text-muted">--</small><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
