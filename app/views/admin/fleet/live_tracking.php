<?php
/**
 * Sunday Fleet Live Dispatch Monitor & Occupancy Radar
 */
$page_title = $page_title ?? 'Sunday Fleet Live Dispatch Radar';
$trips = $trips ?? [];
$date = $date ?? date('Y-m-d');
$base = defined('BASE_URL') ? BASE_URL : '';
?>

<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-satellite-dish text-primary me-2"></i>Sunday Fleet Live Dispatch Radar
            </h4>
            <small class="text-muted">Real-time site-visit cab dispatch tracking, passenger pickup status, and vehicle occupancy.</small>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" action="/admin/site-visits/fleet/live-tracking" class="d-flex gap-2 align-items-center">
                <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()">
            </form>
            <a href="/admin/site-visits/manifest?date=<?= urlencode($date) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-print me-1"></i>Print Manifest
            </a>
            <a href="/admin/site-visits/fleet" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Fleet Dashboard
            </a>
        </div>
    </div>

    <!-- Active Trips Radar Cards -->
    <div class="row g-3">
        <?php if (empty($trips)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5">
                    <i class="fas fa-car-side fa-3x text-muted mb-2"></i>
                    <h5 class="fw-bold text-muted">No scheduled trips for <?= date('d M Y', strtotime($date)) ?></h5>
                    <p class="text-muted small">Schedule a trip from the fleet manager to track vehicle dispatch and pickups.</p>
                    <div>
                        <a href="/admin/site-visits/fleet/trips" class="btn btn-primary btn-sm">Schedule New Trip</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($trips as $t): ?>
                <?php
                $status = $t['status'] ?? 'scheduled';
                $statusBadge = match ($status) {
                    'scheduled' => 'secondary',
                    'in_progress' => 'primary',
                    'completed' => 'success',
                    default => 'warning'
                };
                $driverLink = "{$base}/driver/trip/" . (int)$t['id'] . "?token=" . urlencode($t['dispatch_token'] ?? '');
                $occupancy = (int)($t['occupancy_pct'] ?? 0);
                $barColor = $occupancy >= 90 ? 'danger' : ($occupancy >= 60 ? 'primary' : 'success');
                ?>
                <div class="col-lg-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-car me-2 text-primary"></i><?= htmlspecialchars($t['vehicle_no'] ?? 'Unassigned') ?>
                                </h6>
                                <small class="text-muted"><?= htmlspecialchars($t['vehicle_name'] ?? 'Cab') ?></small>
                            </div>
                            <span class="badge bg-<?= $statusBadge ?> text-uppercase">
                                <?= str_replace('_', ' ', $status) ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <!-- Route & Destination -->
                            <div class="mb-3">
                                <div class="small text-muted">Destination Township</div>
                                <div class="fw-bold fs-6 text-dark">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($t['colony_name'] ?? 'Colony Site') ?>
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="fas fa-crosshairs me-1 text-secondary"></i>Pickup: <?= htmlspecialchars($t['pickup_point'] ?? 'General') ?>
                                </div>
                            </div>

                            <!-- Driver Info -->
                            <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded mb-3">
                                <div>
                                    <small class="text-muted d-block">Assigned Driver</small>
                                    <span class="fw-semibold"><?= htmlspecialchars($t['driver_name'] ?? 'Not set') ?></span>
                                </div>
                                <?php if (!empty($t['driver_phone'])): ?>
                                    <a href="tel:<?= htmlspecialchars($t['driver_phone']) ?>" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-phone me-1"></i>Call
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Occupancy & Passenger Progress -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-semibold">Seat Occupancy</small>
                                    <small class="fw-bold"><?= (int)($t['total_passengers'] ?? 0) ?> / <?= (int)($t['capacity'] ?? 6) ?> seats (<?= $occupancy ?>%)</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-<?= $barColor ?>" style="width: <?= $occupancy ?>%"></div>
                                </div>
                            </div>

                            <!-- Pickup Breakdown Badges -->
                            <div class="d-flex justify-content-around text-center py-2 border rounded bg-light mb-3">
                                <div>
                                    <div class="fs-6 fw-bold text-success"><?= (int)($t['picked_up_count'] ?? 0) ?></div>
                                    <small class="text-muted" style="font-size: 11px;">Picked Up</small>
                                </div>
                                <div class="border-start"></div>
                                <div>
                                    <div class="fs-6 fw-bold text-warning"><?= (int)($t['waiting_count'] ?? 0) ?></div>
                                    <small class="text-muted" style="font-size: 11px;">Waiting</small>
                                </div>
                                <div class="border-start"></div>
                                <div>
                                    <div class="fs-6 fw-bold text-danger"><?= (int)($t['noshow_count'] ?? 0) ?></div>
                                    <small class="text-muted" style="font-size: 11px;">No Show</small>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <a href="<?= htmlspecialchars($driverLink) ?>" target="_blank" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="fas fa-mobile-alt me-1"></i>Driver Console
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText('<?= addslashes($driverLink) ?>'); alert('Driver mobile dispatch link copied to clipboard!');" title="Copy driver mobile link">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
