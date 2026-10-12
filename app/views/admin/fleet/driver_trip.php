<?php
/**
 * Driver Mobile Check-In & Route Dispatch Console
 * Optimized for mobile touch screens
 */
$trip = $trip ?? [];
$passengers = $passengers ?? [];
$token = is_string($token ?? null) ? $token : '';
$status = $trip['status'] ?? 'scheduled';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Driver Dispatch — <?= htmlspecialchars($trip['vehicle_no'] ?? 'Trip') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding-bottom: 80px;
        }
        .header-bar {
            background: #1a365d;
            color: #fff;
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .passenger-card {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border-left: 4px solid #cbd5e0;
        }
        .passenger-card.picked_up {
            border-left-color: #10b981;
            background: #f0fdf4;
        }
        .passenger-card.no_show {
            border-left-color: #ef4444;
            opacity: 0.7;
        }
        .status-btn {
            font-size: 13px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
        }
    </style>
</head>
<body>

<div class="header-bar d-flex justify-content-between align-items-center">
    <div>
        <h5 class="mb-0 fw-bold"><?= htmlspecialchars($trip['vehicle_no'] ?? 'Cab') ?></h5>
        <small class="opacity-75"><?= htmlspecialchars($trip['driver_name'] ?? 'Driver') ?> · <?= date('d M', strtotime($trip['trip_date'] ?? 'now')) ?></small>
    </div>
    <span class="badge bg-light text-dark text-uppercase px-2 py-1">
        <?= str_replace('_', ' ', $status) ?>
    </span>
</div>

<div class="container py-3">
    <!-- Trip Destination Banner -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex align-items-start gap-2">
                <i class="fas fa-map-marked-alt text-danger fa-lg mt-1"></i>
                <div class="flex-fill">
                    <div class="small text-muted text-uppercase fw-bold">Destination Colony</div>
                    <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($trip['colony_name'] ?? 'Site Visit') ?></h5>
                    <?php if (!empty($trip['colony_location'])): ?>
                        <div class="small text-muted"><?= htmlspecialchars($trip['colony_location']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top small text-muted">
                <i class="fas fa-street-view me-1 text-primary"></i><strong>Pickup Hub:</strong> <?= htmlspecialchars($trip['pickup_point'] ?? 'Station') ?>
            </div>
        </div>
    </div>

    <!-- Quick Trip Lifecycle Controls -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="POST" action="/driver/trip/<?= (int)$trip['id'] ?>/trip-status">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <?php if ($status === 'scheduled'): ?>
                    <button type="submit" name="action" value="start" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="fas fa-play me-2"></i>Start Journey
                    </button>
                <?php elseif ($status === 'in_progress'): ?>
                    <button type="submit" name="action" value="complete" class="btn btn-success w-100 py-2 fw-bold">
                        <i class="fas fa-check-double me-2"></i>Arrived & Complete Site Drop
                    </button>
                <?php else: ?>
                    <div class="text-center text-success py-1 fw-bold">
                        <i class="fas fa-flag-checkered me-1"></i>Trip Completed
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Passenger Manifest List -->
    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
        <h6 class="fw-bold mb-0 text-muted text-uppercase">Passengers (<?= count($passengers) ?>)</h6>
        <span class="small text-muted">Tap to update status</span>
    </div>

    <?php if (empty($passengers)): ?>
        <div class="card border-0 shadow-sm p-4 text-center text-muted">
            <i class="fas fa-user-friends fa-2x mb-2"></i>
            No passengers assigned to this cab trip yet.
        </div>
    <?php else: ?>
        <?php foreach ($passengers as $p): ?>
            <?php
            $ps = $p['pickup_status'] ?? 'waiting';
            $phone = preg_replace('/[^0-9]/', '', (string)$p['visitor_phone']);
            ?>
            <div class="passenger-card <?= htmlspecialchars($ps) ?>">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($p['visitor_name'] ?? 'Visitor') ?></h6>
                        <small class="text-muted"><i class="fas fa-map-pin me-1"></i><?= htmlspecialchars($p['pickup_location'] ?? 'Pickup Point') ?></small>
                    </div>
                    <!-- Call & WA Buttons -->
                    <div class="d-flex gap-1">
                        <?php if (!empty($phone)): ?>
                            <a href="tel:<?= htmlspecialchars($phone) ?>" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px; padding: 4px;" title="Call">
                                <i class="fas fa-phone"></i>
                            </a>
                            <a href="https://wa.me/91<?= htmlspecialchars($phone) ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-circle" style="width: 32px; height: 32px; padding: 4px;" title="WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status Toggles Form -->
                <form method="POST" action="/driver/trip/<?= (int)$trip['id'] ?>/passenger/<?= (int)$p['site_visit_id'] ?>/status" class="d-flex gap-2 mt-2">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <button type="submit" name="status" value="picked_up" class="btn btn-sm <?= $ps === 'picked_up' ? 'btn-success' : 'btn-outline-success' ?> status-btn flex-fill">
                        <i class="fas fa-check me-1"></i>Picked Up
                    </button>
                    <button type="submit" name="status" value="waiting" class="btn btn-sm <?= $ps === 'waiting' ? 'btn-warning' : 'btn-outline-warning' ?> status-btn flex-fill">
                        Waiting
                    </button>
                    <button type="submit" name="status" value="no_show" class="btn btn-sm <?= $ps === 'no_show' ? 'btn-danger' : 'btn-outline-danger' ?> status-btn flex-fill">
                        No Show
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
