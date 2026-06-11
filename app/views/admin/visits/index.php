<?php
$page_title = $page_title ?? 'Property Visits';
$page_heading = $page_heading ?? 'Property Visit Schedule';
$content = $content ?? '';
$stats = $stats ?? [];
$visits = $visits ?? [];
$slots = $slots ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Property Visits</h2>
            <p class="text-muted mb-0">Manage site visit bookings and time slots</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Total Visits</p>
                    <h3><?= number_format($stats['total'] ?? 0) ?></h3>
                    <small class="text-muted"><?= $stats['scheduled'] ?? 0 ?> scheduled, <?= $stats['confirmed'] ?? 0 ?> confirmed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Today</p>
                    <h3 class="text-info"><?= number_format($stats['today'] ?? 0) ?></h3>
                    <small class="text-muted"><?= $stats['this_week'] ?? 0 ?> this week</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Completed</p>
                    <h3 class="text-success"><?= number_format($stats['completed'] ?? 0) ?></h3>
                    <small class="text-muted"><?= $stats['cancelled'] ?? 0 ?> cancelled, <?= $stats['no_show'] ?? 0 ?> no-show</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Available Slots</p>
                    <h3 class="text-warning"><?= number_format($stats['available_slots'] ?? 0) ?></h3>
                    <small class="text-muted">Next 14 days · Avg rating: <?= number_format($stats['avg_rating'] ?? 0, 1) ?>⭐</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Upcoming Visits</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($visits)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No visits scheduled</td></tr>
                        <?php else: ?>
                            <?php foreach ($visits as $v): ?>
                                <tr>
                                    <td>#<?= $v['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($v['display_name'] ?? $v['customer_name'] ?? 'Guest') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($v['customer_phone'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($v['property_title'] ?? 'Property #' . $v['property_id']) ?>
                                    </td>
                                    <td>
                                        <strong><?= date('M j, Y', strtotime($v['visit_date'])) ?></strong>
                                        <br><small class="text-muted"><?= date('h:i A', strtotime($v['visit_time'])) ?></small>
                                    </td>
                                    <td><span class="badge bg-light text-dark"><?= ucfirst(str_replace('_', ' ', $v['visit_type'] ?? 'site_visit')) ?></span></td>
                                    <td>
                                        <?php
                                        $statusColors = ['scheduled' => 'warning', 'confirmed' => 'info', 'completed' => 'success', 'cancelled' => 'danger', 'rescheduled' => 'secondary', 'no_show' => 'dark'];
                                        ?>
                                        <span class="badge bg-<?= $statusColors[$v['status']] ?? 'secondary' ?>">
                                            <?= ucfirst($v['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($v['status'] === 'scheduled'): ?>
                                            <a href="<?= BASE_URL ?>/admin/visits/confirm?id=<?= $v['id'] ?>" class="btn btn-sm btn-info" title="Confirm"><i class="fas fa-check"></i></a>
                                        <?php endif; ?>
                                        <?php if (in_array($v['status'], ['scheduled', 'confirmed'])): ?>
                                            <a href="<?= BASE_URL ?>/admin/visits/complete?id=<?= $v['id'] ?>" class="btn btn-sm btn-success" title="Mark completed"><i class="fas fa-flag-checkered"></i></a>
                                            <a href="<?= BASE_URL ?>/admin/visits/noshow?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-secondary" title="No show" onclick="return confirm('Mark as no-show?')"><i class="fas fa-user-times"></i></a>
                                            <a href="<?= BASE_URL ?>/admin/visits/cancel?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-danger" title="Cancel" onclick="return confirm('Cancel this visit?')"><i class="fas fa-times"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Available Time Slots (next 14 days)</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($slots)): ?>
                <p class="text-muted">No available slots</p>
            <?php else: ?>
                <?php
                $byDate = [];
                foreach ($slots as $s) {
                    $byDate[$s['date']][] = $s;
                }
                ?>
                <div class="row g-3">
                    <?php foreach (array_slice($byDate, 0, 7, true) as $date => $daySlots): ?>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <strong><?= date('D, M j', strtotime($date)) ?></strong>
                                </div>
                                <div class="card-body p-2">
                                    <?php foreach ($daySlots as $slot): ?>
                                        <div class="d-flex justify-content-between align-items-center py-1">
                                            <span><i class="far fa-clock text-muted me-1"></i> <?= date('h:i A', strtotime($slot['time_slot'])) ?></span>
                                            <span class="badge bg-<?= $slot['current_bookings'] >= $slot['max_bookings'] ? 'danger' : ($slot['current_bookings'] > 0 ? 'warning' : 'success') ?>">
                                                <?= $slot['current_bookings'] ?>/<?= $slot['max_bookings'] ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/unified.php';
