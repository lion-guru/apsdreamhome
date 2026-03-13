<?php

/**
 * Customer Dashboard - APS Dream Home
 */

$layout = 'layouts/base';
$page_title = $page_title ?? 'Customer Dashboard - APS Dream Home';
$page_description = $page_description ?? 'Manage your properties and investments';
$user = $user ?? [];
$stats = $stats ?? [];
$recent_activities = $recent_activities ?? [];
$favorite_properties = $favorite_properties ?? [];
$recent_inquiries = $recent_inquiries ?? [];
$recommended_properties = $recommended_properties ?? [];
?>

<!-- Dashboard Header -->
<div class="dashboard-header bg-gradient-primary text-white py-4 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3 mb-0">Customer Dashboard</h1>
                <p class="mb-0 opacity-75">Welcome back, <?= htmlspecialchars($user['name'] ?? 'Customer') ?>!</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-inline-flex align-items-center">
                    <span class="badge bg-success me-2">Active</span>
                    <span class="me-3">Customer ID: <?= htmlspecialchars($user['customer_id'] ?? '') ?></span>
                    <a href="<?= BASE_URL ?>/dashboard/profile" class="btn btn-light btn-sm">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Content -->
<div class="container">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Bookings</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_bookings'] ?? 0) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-home fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Active Bookings</h6>
                            <h3 class="mb-0"><?= number_format($stats['active_bookings'] ?? 0) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Inquiries</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_inquiries'] ?? 0) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-envelope fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Favorites</h6>
                            <h3 class="mb-0"><?= number_format($stats['favorite_properties'] ?? 0) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-heart fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Quick Actions</h5>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="<?= BASE_URL ?>/properties" class="btn btn-outline-primary w-100">
                                <i class="fas fa-search me-2"></i>Browse Properties
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= BASE_URL ?>/dashboard/favorites" class="btn btn-outline-danger w-100">
                                <i class="fas fa-heart me-2"></i>My Favorites
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= BASE_URL ?>/dashboard/inquiries" class="btn btn-outline-info w-100">
                                <i class="fas fa-envelope me-2"></i>My Inquiries
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-success w-100">
                                <i class="fas fa-phone me-2"></i>Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Recent Activities</h5>
                    <?php if (!empty($recent_activities)): ?>
                        <div class="activity-list">
                            <?php foreach (array_slice($recent_activities, 0, 5) as $activity): ?>
                                <div class="activity-item d-flex align-items-center mb-3">
                                    <div class="activity-icon me-3">
                                        <i class="fas fa-<?= $activity['icon'] ?? 'circle' ?> text-primary"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title"><?= htmlspecialchars($activity['title'] ?? '') ?></div>
                                        <div class="activity-time text-muted small"><?= date('d M Y, h:i A', strtotime($activity['created_at'] ?? 'now')) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No recent activities</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Favorite Properties -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Favorite Properties</h5>
                    <?php if (!empty($favorite_properties)): ?>
                        <div class="property-list">
                            <?php foreach (array_slice($favorite_properties, 0, 3) as $property): ?>
                                <div class="property-item d-flex align-items-center mb-3">
                                    <img src="<?= BASE_URL ?>/assets/images/properties/<?= $property['image'] ?? 'placeholder.jpg' ?>" 
                                         class="property-thumbnail me-3" alt="<?= htmlspecialchars($property['title'] ?? '') ?>">
                                    <div class="property-content">
                                        <div class="property-title"><?= htmlspecialchars($property['title'] ?? '') ?></div>
                                        <div class="property-location text-muted small"><?= htmlspecialchars($property['location'] ?? '') ?></div>
                                        <div class="property-price text-primary fw-bold">₹<?= number_format($property['price'] ?? 0) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>/dashboard/favorites" class="btn btn-primary btn-sm">View All Favorites</a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No favorite properties yet</p>
                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>/properties" class="btn btn-primary btn-sm">Browse Properties</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommended Properties -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Recommended Properties</h5>
                    <?php if (!empty($recommended_properties)): ?>
                        <div class="row">
                            <?php foreach (array_slice($recommended_properties, 0, 4) as $property): ?>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card property-card h-100">
                                        <img src="<?= BASE_URL ?>/assets/images/properties/<?= $property['image'] ?? 'placeholder.jpg' ?>" 
                                             class="card-img-top" alt="<?= htmlspecialchars($property['title'] ?? '') ?>">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($property['title'] ?? '') ?></h6>
                                            <p class="text-muted small"><?= htmlspecialchars($property['location'] ?? '') ?></p>
                                            <p class="text-primary fw-bold">₹<?= number_format($property['price'] ?? 0) ?></p>
                                            <a href="<?= BASE_URL ?>/properties/<?= $property['id'] ?? '' ?>" class="btn btn-primary btn-sm">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No recommended properties available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.activity-item {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.property-item {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.property-item:last-child {
    border-bottom: none;
}

.property-thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.property-card .card-img-top {
    height: 150px;
    object-fit: cover;
}
</style>
