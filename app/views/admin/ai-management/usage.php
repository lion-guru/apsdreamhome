<?php
$page_title = $page_title ?? 'AI Usage Analytics';
$usage = $usage ?? [];
$byFeature = $byFeature ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">AI Usage Analytics</h1>
        <p class="text-muted mb-0">Track usage patterns across AI features</p>
    </div>
</div>

<div class="row mb-4">
    <?php if (!empty($byFeature)): ?>
        <?php foreach ($byFeature as $feat): ?>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body aps-cp-card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                    <i class="fas fa-cog fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $feat['feature_name'] ?? 'unknown'))) ?></h6>
                                <h3 class="mb-0"><?= number_format($feat['count'] ?? 0) ?></h3>
                                <small class="text-muted"><?= number_format($feat['unique_users'] ?? 0) ?> unique users</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4 text-muted">
                    <i class="fas fa-chart-bar fa-3x mb-3"></i>
                    <p>No usage data available yet</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Recent Activity</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usage)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">No activity recorded</td></tr>
                    <?php else: ?>
                        <?php foreach ($usage as $row): ?>
                            <tr>
                                <td><span class="badge bg-info"><?= htmlspecialchars($row['feature_name'] ?? 'N/A') ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['user_name'] ?? 'Guest') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($row['user_email'] ?? '') ?></small>
                                </td>
                                <td><small><?= htmlspecialchars(mb_substr($row['action'] ?? '', 0, 80)) ?></small></td>
                                <td><small><?= date('M j, Y H:i', strtotime($row['created_at'] ?? 'now')) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const featureData = <?= json_encode($byFeature) ?>;
const labels = featureData.map(f => f.feature_name ? f.feature_name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : 'Unknown');
const counts = featureData.map(f => parseInt(f.count) || 0);
const uniqueUsers = featureData.map(f => parseInt(f.unique_users) || 0);
</script>
