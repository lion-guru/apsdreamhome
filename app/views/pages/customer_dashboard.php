<?php
$page_title = $page_title ?? 'My Dashboard';
$customer_name = $customer_name ?? 'Customer';
$customer_properties = $customer_properties ?? [];
$customer_bookings = $customer_bookings ?? [];
$emi_schedule = $emi_schedule ?? [];
$payment_history = $payment_history ?? [];
$stats = $stats ?? ['properties' => 0, 'bookings' => 0, 'pending_emi' => 0, 'total_investment' => 0];
?>

<!-- Welcome Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($customer_name) ?></h2>
        <p class="text-muted mb-0">Here's an overview of your real estate portfolio</p>
    </div>
    <div>
        <span class="badge bg-light text-dark fs-6 px-3 py-2">
            <i class="far fa-calendar-alt me-1"></i>
            <?= date('l, F j, Y') ?>
        </span>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 stat-card">
            <div class="card-body d-flex align-items-center p-4">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                    <i class="fas fa-building fa-lg text-primary"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small text-uppercase fw-semibold">My Properties</p>
                    <h3 class="fw-bold mb-0"><?= (int)$stats['properties'] ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 stat-card">
            <div class="card-body d-flex align-items-center p-4">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                    <i class="fas fa-file-signature fa-lg text-success"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small text-uppercase fw-semibold">Active Bookings</p>
                    <h3 class="fw-bold mb-0"><?= (int)$stats['bookings'] ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 stat-card">
            <div class="card-body d-flex align-items-center p-4">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                    <i class="fas fa-credit-card fa-lg text-warning"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small text-uppercase fw-semibold">EMI Payments</p>
                    <h3 class="fw-bold mb-0"><?= (int)$stats['pending_emi'] ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 stat-card">
            <div class="card-body d-flex align-items-center p-4">
                <div class="rounded-circle bg-purple bg-opacity-10 p-3 me-3" style="background-color: rgba(111,66,193,0.1) !important;">
                    <i class="fas fa-rupee-sign fa-lg" style="color: #6f42c1;"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small text-uppercase fw-semibold">Total Investment</p>
                    <h3 class="fw-bold mb-0">&#8377;<?= number_format((float)$stats['total_investment'], 0) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h6>
        <div class="row g-3">
            <div class="col-xl-3 col-md-6">
                <a href="#properties-section" class="btn btn-outline-primary w-100 py-3 d-flex align-items-center justify-content-center text-decoration-none quick-action-btn">
                    <i class="fas fa-home me-2"></i> View Properties
                </a>
            </div>
            <div class="col-xl-3 col-md-6">
                <a href="#emi-section" class="btn btn-outline-success w-100 py-3 d-flex align-items-center justify-content-center text-decoration-none quick-action-btn">
                    <i class="fas fa-wallet me-2"></i> Make Payment
                </a>
            </div>
            <div class="col-xl-3 col-md-6">
                <a href="#payment-section" class="btn btn-outline-info w-100 py-3 d-flex align-items-center justify-content-center text-decoration-none quick-action-btn">
                    <i class="fas fa-file-invoice me-2"></i> Download Receipt
                </a>
            </div>
            <div class="col-xl-3 col-md-6">
                <a href="mailto:support@example.com" class="btn btn-outline-secondary w-100 py-3 d-flex align-items-center justify-content-center text-decoration-none quick-action-btn">
                    <i class="fas fa-headset me-2"></i> Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">

    <!-- My Properties -->
    <div class="col-lg-6" id="properties-section">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4">
                <h5 class="fw-bold mb-0"><i class="fas fa-building me-2 text-primary"></i>My Properties</h5>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($customer_properties)): ?>
                    <?php foreach ($customer_properties as $property): ?>
                        <div class="d-flex align-items-center p-3 rounded-3 mb-3 property-item" style="background-color: #f8f9fa; transition: all 0.2s ease;">
                            <div class="rounded-3 bg-white shadow-sm d-flex align-items-center justify-content-center me-3" style="width: 56px; height: 56px; min-width: 56px;">
                                <i class="fas fa-home fa-lg text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-semibold mb-1"><?= htmlspecialchars($property['name'] ?? $property['title'] ?? 'Property') ?></h6>
                                <p class="text-muted small mb-1">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= htmlspecialchars($property['location'] ?? $property['address'] ?? 'N/A') ?>
                                </p>
                                <?php if (!empty($property['price'])): ?>
                                    <span class="text-dark fw-semibold small">&#8377;<?= number_format((float)$property['price'], 0) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="ms-2">
                                <?php
                                $status = strtolower($property['status'] ?? 'pending');
                                $badgeClass = match ($status) {
                                    'booked', 'confirmed' => 'bg-success',
                                    'owned', 'registered' => 'bg-primary',
                                    'under construction' => 'bg-warning text-dark',
                                    'cancelled' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?> px-3 py-2"><?= htmlspecialchars(ucfirst($property['status'] ?? 'Pending')) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-home fa-3x text-muted mb-3 opacity-50"></i>
                        <p class="text-muted mb-0">No properties found</p>
                        <a href="#" class="btn btn-primary btn-sm mt-3">Browse Properties</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4">
                <h5 class="fw-bold mb-0"><i class="fas fa-file-alt me-2 text-success"></i>Recent Bookings</h5>
                <a href="#" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($customer_bookings)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th class="border-0 ps-0">Booking ID</th>
                                    <th class="border-0">Property</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0 pe-0">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customer_bookings as $booking): ?>
                                    <tr class="booking-row">
                                        <td class="ps-0">
                                            <span class="fw-semibold text-primary"><?= htmlspecialchars($booking['id'] ?? $booking['booking_id'] ?? 'N/A') ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($booking['property'] ?? $booking['property_name'] ?? 'N/A') ?></td>
                                        <td class="text-muted small"><?= htmlspecialchars($booking['date'] ?? $booking['booking_date'] ?? 'N/A') ?></td>
                                        <td class="pe-0">
                                            <?php
                                            $bStatus = strtolower($booking['status'] ?? 'pending');
                                            $bClass = match ($bStatus) {
                                                'confirmed', 'active' => 'bg-success',
                                                'pending', 'processing' => 'bg-warning text-dark',
                                                'cancelled', 'rejected' => 'bg-danger',
                                                'completed' => 'bg-info',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $bClass ?> px-2 py-1"><?= htmlspecialchars(ucfirst($booking['status'] ?? 'Pending')) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3 opacity-50"></i>
                        <p class="text-muted mb-0">No bookings yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">

    <!-- EMI Schedule -->
    <div class="col-lg-7" id="emi-section">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4">
                <h5 class="fw-bold mb-0"><i class="fas fa-calendar-check me-2 text-warning"></i>EMI Schedule</h5>
                <a href="#" class="btn btn-sm btn-outline-warning">Full Schedule</a>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($emi_schedule)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th class="border-0 ps-0">EMI No.</th>
                                    <th class="border-0">Due Date</th>
                                    <th class="border-0">Amount</th>
                                    <th class="border-0 pe-0">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($emi_schedule as $emi): ?>
                                    <?php
                                    $eStatus = strtolower($emi['status'] ?? 'pending');
                                    $eRowClass = '';
                                    $eBadgeClass = match ($eStatus) {
                                        'paid', 'completed' => 'bg-success',
                                        'overdue', 'missed' => 'bg-danger',
                                        'pending', 'upcoming' => 'bg-warning text-dark',
                                        default => 'bg-secondary'
                                    };
                                    $eIcon = match ($eStatus) {
                                        'paid', 'completed' => 'fa-check-circle',
                                        'overdue', 'missed' => 'fa-exclamation-circle',
                                        default => 'fa-clock'
                                    };
                                    ?>
                                    <tr class="emi-row">
                                        <td class="ps-0 fw-semibold"><?= htmlspecialchars($emi['emi_number'] ?? $emi['number'] ?? $emi['id'] ?? 'N/A') ?></td>
                                        <td class="text-muted small"><?= htmlspecialchars($emi['due_date'] ?? $emi['date'] ?? 'N/A') ?></td>
                                        <td class="fw-semibold">&#8377;<?= number_format((float)($emi['amount'] ?? 0), 0) ?></td>
                                        <td class="pe-0">
                                            <span class="badge <?= $eBadgeClass ?> px-2 py-1">
                                                <i class="fas <?= $eIcon ?> me-1"></i>
                                                <?= htmlspecialchars(ucfirst($emi['status'] ?? 'Pending')) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-check fa-3x text-muted mb-3 opacity-50"></i>
                        <p class="text-muted mb-0">No EMI schedule available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="col-lg-5" id="payment-section">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4">
                <h5 class="fw-bold mb-0"><i class="fas fa-receipt me-2 text-info"></i>Payment History</h5>
                <a href="#" class="btn btn-sm btn-outline-info">View All</a>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($payment_history)): ?>
                    <?php foreach ($payment_history as $payment): ?>
                        <?php
                        $pStatus = strtolower($payment['status'] ?? 'completed');
                        $pClass = match ($pStatus) {
                            'completed', 'success' => 'success',
                            'pending', 'processing' => 'warning',
                            'failed', 'rejected' => 'danger',
                            'refunded' => 'info',
                            default => 'secondary'
                        };
                        $pIcon = match ($pStatus) {
                            'completed', 'success' => 'fa-arrow-down',
                            'failed', 'rejected' => 'fa-times',
                            'refunded' => 'fa-undo',
                            default => 'fa-clock'
                        };
                        ?>
                        <div class="d-flex align-items-center p-3 rounded-3 mb-3 payment-item" style="background-color: #f8f9fa; transition: all 0.2s ease;">
                            <div class="rounded-circle bg-<?= $pClass ?> bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; min-width: 44px;">
                                <i class="fas <?= $pIcon ?> text-<?= $pClass ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-semibold mb-1 small"><?= htmlspecialchars($payment['description'] ?? $payment['type'] ?? 'Payment') ?></h6>
                                <p class="text-muted small mb-0"><?= htmlspecialchars($payment['date'] ?? $payment['payment_date'] ?? 'N/A') ?></p>
                            </div>
                            <div class="text-end">
                                <p class="fw-bold mb-0">&#8377;<?= number_format((float)($payment['amount'] ?? 0), 0) ?></p>
                                <span class="badge bg-<?= $pClass ?> bg-opacity-10 text-<?= $pClass ?> small"><?= htmlspecialchars(ucfirst($payment['status'] ?? 'Completed')) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3 opacity-50"></i>
                        <p class="text-muted mb-0">No payment history</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 12px;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important;
    }
    .quick-action-btn {
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .quick-action-btn:hover {
        transform: translateY(-1px);
    }
    .property-item:hover {
        background-color: #e9ecef !important;
    }
    .payment-item:hover {
        background-color: #e9ecef !important;
    }
    .booking-row:hover,
    .emi-row:hover {
        background-color: #f8f9fa;
    }
    .card {
        border-radius: 12px;
    }
    .card-header {
        border-radius: 12px 12px 0 0;
    }
    table th {
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
</style>
