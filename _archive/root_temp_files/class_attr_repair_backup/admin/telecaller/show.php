<?php

/**
 * Telecaller Daily Task Detail - APS Dream Home Admin
 */
$page_title = $page_title ?? 'Task Detail';
$page_description = 'View telecaller daily task details';

$task = $task ?? [];

$ratingBadge = function($rating) {
    $map = ['excellent' => 'success', 'good' => 'primary', 'average' => 'warning', 'poor' => 'danger'];
    $class = $map[$rating] ?? 'secondary';
    return "<span class=\"badge bg-{$class}\">" . ucfirst($rating) . '</span>';
};

?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin/telecaller">Telecaller Management</a></li>
                    <li class="breadcrumb-item active">Task #<?php echo htmlspecialchars($task['id'] ?? ''); ?></li>
                </ol>
            </nav>
            <h1 class="h3 mb-2">Daily Task Details</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Task Information</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-bordered">
                        <tr>
                            <th class="style-869" class="text-muted">Telecaller</th>
                            <td><strong><?php echo htmlspecialchars($task['telecaller_name'] ?? 'N/A'); ?></strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Email</th>
                            <td><?php echo htmlspecialchars($task['email'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Phone</th>
                            <td><?php echo htmlspecialchars($task['phone'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Task Date</th>
                            <td><?php echo htmlspecialchars($task['task_date'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Leads Assigned</th>
                            <td><?php echo number_format($task['total_leads_assigned'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Calls Made</th>
                            <td><?php echo number_format($task['calls_made'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Calls Connected</th>
                            <td><?php echo number_format($task['calls_connected'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Leads Converted</th>
                            <td><span class="badge bg-success fs-6"><?php echo number_format($task['leads_converted'] ?? 0); ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Callbacks</th>
                            <td><?php echo number_format($task['leads_callback'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Not Interested</th>
                            <td><?php echo number_format($task['leads_not_interested'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Pending Calls</th>
                            <td><?php echo number_format($task['pending_calls'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Target Calls</th>
                            <td><?php echo number_format($task['target_calls'] ?? 0); ?></td>
                        </tr>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Performance at a Glance</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php
                    $callsMade = (int)($task['calls_made'] ?? 0);
                    $target = (int)($task['target_calls'] ?? 1);
                    $connected = (int)($task['calls_connected'] ?? 0);
                    $converted = (int)($task['leads_converted'] ?? 0);
                    $achievePct = $target > 0 ? round(($callsMade / $target) * 100) : 0;
                    $connectRate = $callsMade > 0 ? round(($connected / $callsMade) * 100) : 0;
                    $convertRate = $connected > 0 ? round(($converted / $connected) * 100) : 0;
                    ?>
                    <div class="mb-3">
                        <label class="text-muted small">Target Achievement</label>
                        <div class="progress" class="style-70613">
                            <div class="progress-bar bg-<?php echo $achievePct >= 100 ? 'success' : ($achievePct >= 50 ? 'warning' : 'danger'); ?>" role="progressbar" class="style-53489">
                                <?php echo e($achievePct); ?>%
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Connection Rate</label>
                        <div class="progress" class="style-70613">
                            <div class="progress-bar bg-info" role="progressbar" class="style-35864">
                                <?php echo e($connectRate); ?>%
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small">Conversion Rate</label>
                        <div class="progress" class="style-70613">
                            <div class="progress-bar bg-success" role="progressbar" class="style-41126">
                                <?php echo e($convertRate); ?>%
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Calls Made</span>
                        <strong><?php echo number_format($callsMade); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Connected</span>
                        <strong><?php echo number_format($connected); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Converted</span>
                        <strong class="text-success"><?php echo number_format($converted); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($task['notes'])): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <p class="mb-0"><?php echo nl2br(htmlspecialchars($task['notes'] ?? '')); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>/admin/telecaller" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to List
        </a>
    </div>
</div>
