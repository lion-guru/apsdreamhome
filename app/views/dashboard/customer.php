<?php
/**
 * Customer Dashboard View - APS Dream Home
 * Renders inside layouts/customer.php
 */
$user = $user ?? [];
$stats = $stats ?? [];
$recent_activities = $recent_activities ?? [];
$favorite_properties = $favorite_properties ?? [];
$recommended_properties = $recommended_properties ?? [];

$userName = htmlspecialchars($user['name'] ?? $_SESSION['user_name'] ?? 'Valued Customer');
$customerId = htmlspecialchars($user['customer_id'] ?? ('APS-CUST-' . str_pad((string)($_SESSION['user_id'] ?? 1), 4, '0', STR_PAD_LEFT)));
$joinDate = !empty($user['join_date']) ? date('M Y', strtotime($user['join_date'])) : date('M Y');
?>

<div class="customer-dashboard-wrapper">
    <!-- Welcome Hero Banner -->
    <div class="card border-0 mb-4 overflow-hidden text-white" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 16px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);">
        <div class="card-body p-4 p-md-5 position-relative">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 px-3 py-1 rounded-pill fw-medium">
                            <i class="fas fa-crown me-1 text-warning"></i> Customer Portal
                        </span>
                        <span class="text-white-50 small">| ID: <?= $customerId ?></span>
                    </div>
                    <h2 class="display-6 fw-bold mb-2 text-white">Welcome back, <?= $userName ?>!</h2>
                    <p class="text-white-50 mb-4 mb-lg-0" style="max-width: 600px;">
                        Manage your real estate journey, track booked plots, monitor EMI payment schedules, and schedule site visits effortlessly from your dedicated portal.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="<?= BASE_URL ?>/properties" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold shadow-sm">
                            <i class="fas fa-search me-2"></i>Browse Plots
                        </a>
                        <a href="<?= BASE_URL ?>/user/profile" class="btn btn-outline-light px-4 py-2 rounded-pill fw-medium">
                            <i class="fas fa-user-cog me-2"></i>My Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 h-100 shadow-sm" style="border-radius: 14px; transition: transform 0.2s ease;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-semibold text-uppercase">Booked Plots</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary" style="width: 44px; height: 44px;">
                            <i class="fas fa-file-signature fa-lg"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark"><?= (int)($stats['bookings_count'] ?? 0) ?></h3>
                    <a href="<?= BASE_URL ?>/user/bookings" class="small text-primary text-decoration-none fw-medium">
                        View Bookings <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 h-100 shadow-sm" style="border-radius: 14px; transition: transform 0.2s ease;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-semibold text-uppercase">Saved Properties</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-danger" style="width: 44px; height: 44px;">
                            <i class="fas fa-heart fa-lg"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark"><?= (int)($stats['favorites_count'] ?? 0) ?></h3>
                    <a href="<?= BASE_URL ?>/user/favorites" class="small text-danger text-decoration-none fw-medium">
                        View Favorites <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 h-100 shadow-sm" style="border-radius: 14px; transition: transform 0.2s ease;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-semibold text-uppercase">Site Visits</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success" style="width: 44px; height: 44px;">
                            <i class="fas fa-map-marked-alt fa-lg"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark"><?= (int)($stats['visits_count'] ?? 0) ?></h3>
                    <a href="<?= BASE_URL ?>/user/site-visits" class="small text-success text-decoration-none fw-medium">
                        Visit Details <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 h-100 shadow-sm" style="border-radius: 14px; transition: transform 0.2s ease;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-semibold text-uppercase">Active Inquiries</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning" style="width: 44px; height: 44px;">
                            <i class="fas fa-envelope-open-text fa-lg"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark"><?= (int)($stats['inquiries_count'] ?? 0) ?></h3>
                    <a href="<?= BASE_URL ?>/user/inquiries" class="small text-warning text-decoration-none fw-medium">
                        Track Inquiries <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Hub -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
            <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h5>
            <p class="text-muted small mb-0">Fast shortcuts for common tasks</p>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <a href="<?= BASE_URL ?>/properties" class="btn btn-light w-100 p-3 text-start border d-flex align-items-center gap-3 rounded-3 transition-hover h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-search fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">Find Properties</div>
                            <div class="small text-muted">Browse colonies & plots</div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-6">
                    <a href="<?= BASE_URL ?>/user/emi-tracker" class="btn btn-light w-100 p-3 text-start border d-flex align-items-center gap-3 rounded-3 transition-hover h-100">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-calculator fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">EMI Tracker</div>
                            <div class="small text-muted">View upcoming dues</div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-6">
                    <a href="<?= BASE_URL ?>/user/payment-history" class="btn btn-light w-100 p-3 text-start border d-flex align-items-center gap-3 rounded-3 transition-hover h-100">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-receipt fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">Payment History</div>
                            <div class="small text-muted">Download receipts</div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-6">
                    <a href="<?= BASE_URL ?>/user/tickets" class="btn btn-light w-100 p-3 text-start border d-flex align-items-center gap-3 rounded-3 transition-hover h-100">
                        <div class="rounded-circle bg-purple bg-opacity-10 p-3 text-purple d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-headset fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">Customer Support</div>
                            <div class="small text-muted">Raise a help ticket</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Content Row -->
    <div class="row g-4 mb-4">
        <!-- Shortlisted Properties -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-heart text-danger me-2"></i>Shortlisted Properties</h5>
                        <p class="text-muted small mb-0">Your saved favorites</p>
                    </div>
                    <a href="<?= BASE_URL ?>/user/favorites" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        View All (<?= count($favorite_properties) ?>)
                    </a>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($favorite_properties)): ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach (array_slice($favorite_properties, 0, 4) as $property): ?>
                                <div class="d-flex align-items-center p-3 rounded-3 border bg-light bg-opacity-50">
                                    <div class="rounded-3 overflow-hidden me-3 bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; flex-shrink: 0;">
                                        <?php if (!empty($property['image'])): ?>
                                            <img src="<?= htmlspecialchars($property['image']) ?>" alt="<?= htmlspecialchars($property['title'] ?? '') ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <i class="fas fa-building text-muted fa-2x"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="mb-1 text-truncate fw-bold text-dark"><?= htmlspecialchars($property['title'] ?? 'Prime Colony Plot') ?></h6>
                                        <div class="small text-muted text-truncate mb-1">
                                            <i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($property['location'] ?? 'Ayodhya Road, Lucknow') ?>
                                        </div>
                                        <div class="fw-bold text-primary small">
                                            <?= htmlspecialchars($property['price'] ?? '₹ 15,00,000') ?>
                                        </div>
                                    </div>
                                    <a href="<?= BASE_URL ?>/property/<?= (int)($property['id'] ?? 1) ?>" class="btn btn-sm btn-primary rounded-pill px-3 ms-2 text-nowrap">
                                        Details
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="rounded-circle bg-light d-inline-flex p-4 mb-3 text-muted">
                                <i class="far fa-heart fa-2x"></i>
                            </div>
                            <h6 class="fw-semibold text-muted">No Saved Properties Yet</h6>
                            <p class="text-muted small mb-3">Explore our colony plots and tap the heart icon to shortlist properties.</p>
                            <a href="<?= BASE_URL ?>/properties" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                Explore Plots Now
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-stream text-primary me-2"></i>Recent Activity</h5>
                    <p class="text-muted small mb-0">Your latest actions and updates</p>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($recent_activities)): ?>
                        <div class="timeline-feed">
                            <?php foreach (array_slice($recent_activities, 0, 5) as $activity): ?>
                                <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; flex-shrink: 0;">
                                        <i class="fas fa-bell small"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="small fw-semibold text-dark"><?= htmlspecialchars($activity['property'] ?? 'Property update') ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($activity['type'] ?? 'Viewed') ?></div>
                                    </div>
                                    <span class="small text-muted"><?= htmlspecialchars($activity['date'] ?? 'Recently') ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="rounded-circle bg-light d-inline-flex p-4 mb-3 text-muted">
                                <i class="fas fa-history fa-2x"></i>
                            </div>
                            <h6 class="fw-semibold text-muted">No Activity Logged</h6>
                            <p class="text-muted small mb-0">Your recent views and inquiries will appear here automatically.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>