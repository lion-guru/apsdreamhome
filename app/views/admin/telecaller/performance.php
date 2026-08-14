<?php

/**
 * Telecaller Performance - APS Dream Home Admin
 */
$page_title = 'Telecaller Performance';
$page_description = 'Track telecaller performance and ratings';

$overallStats = $overallStats ?? ['total_calls' => 0, 'connected' => 0, 'converted' => 0, 'total_commission' => 0];
$records = $records ?? [];
$telecallers = $telecallers ?? [];
$telecallerFilter = $telecallerFilter ?? '';
$periodFilter = $periodFilter ?? '';

$ratingBadge = function($rating) {
    $map = ['excellent' => 'success', 'good' => 'primary', 'average' => 'warning', 'poor' => 'danger'];
    $class = $map[$rating] ?? 'secondary';
    return "<span class=\"badge bg-{$class}\">" . ucfirst($rating) . '</span>';
};

?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Telecaller Performance</h1>
            <p class="text-muted">Overall performance tracking and rating summaries</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-phone fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Calls</h6>
                            <h3 class="mb-0"><?php echo number_format($overallStats['total_calls'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-phone-alt fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Connected</h6>
                            <h3 class="mb-0"><?php echo number_format($overallStats['connected'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Converted</h6>
                            <h3 class="mb-0"><?php echo number_format($overallStats['converted'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-rupee-sign fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Commission</h6>
                            <h3 class="mb-0">â‚¹<?php echo number_format($overallStats['total_commission'] ?? 0, 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" action="<?php echo BASE_URL; ?>/admin/telecaller/performance" class="row g-3">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-4">
                    <label class="form-label">Telecaller</label>
                    <select name="telecaller_id" class="form-select">
                        <option value="">All Telecallers</option>
                        <?php foreach ($telecallers as $tc): ?>
                            <option value="<?php echo $tc['id']; ?>" <?php echo ($telecallerFilter == $tc['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tc['name'] ?? ''); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?php echo BASE_URL; ?>/admin/telecaller/performance" class="btn btn-outline-secondary"><i class="fas fa-sync-alt me-1"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Performance Records Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Performance Records</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Telecaller</th>
                            <th>Period Start</th>
                            <th>Period End</th>
                            <th>Total Calls</th>
                            <th>Connected</th>
                            <th>Converted</th>
                            <th>Commission</th>
                            <th>Target %</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No performance records found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td><?php echo $r['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($r['telecaller_name'] ?? 'N/A'); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($r['email'] ?? ''); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($r['period_start'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($r['period_end'] ?? ''); ?></td>
                                    <td><?php echo number_format($r['total_calls'] ?? 0); ?></td>
                                    <td><?php echo number_format($r['connected_calls'] ?? 0); ?></td>
                                    <td>
                                        <span class="badge bg-success"><?php echo number_format($r['leads_converted'] ?? 0); ?></span>
                                    </td>
                                    <td>â‚¹<?php echo number_format($r['total_commission'] ?? 0, 2); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" class="style-29939">
                                                <div class="progress-bar bg-<?php echo ($r['target_achieved'] ?? 0) >= 100 ? 'success' : (($r['target_achieved'] ?? 0) >= 50 ? 'warning' : 'danger'); ?>" class="style-38395"></div>
                                            </div>
                                            <small><?php echo number_format($r['target_achieved'] ?? 0, 1); ?>%</small>
                                        </div>
                                    </td>
                                    <td><?php echo $ratingBadge($r['rating'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
