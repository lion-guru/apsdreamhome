<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome/');
}

// Ensure $data is defined to prevent errors
if (!isset($data)) {
    $data = [
        'user' => ['name' => 'Guest'],
        'stats' => [
            'total_bookings' => 0,
            'total_favorites' => 0,
            'total_inquiries' => 0
        ],
        'recent_properties' => [],
        'favorites' => []
    ];
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="container-fluid py-4 bg-light min-vh-100">
    <div class="row align-items-center mb-4">
        <div class="col-lg-8">
            <h1 class="h3 fw-semibold mb-1">Welcome back, <?php echo htmlspecialchars($data['user']['name']); ?> 👋</h1>
            <p class="text-secondary mb-0">Track your property journey, manage favorites and stay updated on new launches.</p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="<?php echo BASE_URL; ?>properties?sort=latest" class="btn btn-primary me-2">
                <i class="fas fa-bolt me-2"></i>View New Listings
            </a>
            <a href="<?php echo BASE_URL; ?>dashboard/settings" class="btn btn-outline-secondary">
                <i class="fas fa-sliders-h me-2"></i>Dashboard Settings
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small">Total Bookings</span>
                        <span class="badge bg-primary-subtle text-primary-emphasis">This month</span>
                    </div>
                    <h2 class="display-6 fw-semibold mb-0 text-primary"><?php echo $data['stats']['total_bookings']; ?></h2>
                    <p class="text-muted small mb-0">Confirmed & upcoming property visits</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small">Saved Favorites</span>
                        <span class="badge bg-success-subtle text-success-emphasis">Curated</span>
                    </div>
                    <h2 class="display-6 fw-semibold mb-0 text-success"><?php echo $data['stats']['total_favorites']; ?></h2>
                    <p class="text-muted small mb-0">Handpicked properties you loved</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small">Inquiries</span>
                        <span class="badge bg-info-subtle text-info-emphasis">Support</span>
                    </div>
                    <h2 class="display-6 fw-semibold mb-0 text-info"><?php echo $data['stats']['total_inquiries']; ?></h2>
                    <p class="text-muted small mb-0">Assistance requests in progress</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small">Profile Status</span>
                        <i class="fas fa-shield-check text-success"></i>
                    </div>
                    <span class="badge bg-success rounded-pill px-3 py-2">Active</span>
                    <p class="text-muted small mt-2 mb-0">Complete profile for personalised alerts</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-lg-3">
                    <a href="<?php echo BASE_URL; ?>properties" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                        <i class="fas fa-search fa-lg mb-2"></i>
                        Browse Properties
                    </a>
                </div>
                <div class="col-6 col-lg-3">
                    <a href="<?php echo BASE_URL; ?>projects" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                        <i class="fas fa-building fa-lg mb-2"></i>
                        View Projects
                    </a>
                </div>
                <div class="col-6 col-lg-3">
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                        <i class="fas fa-phone fa-lg mb-2"></i>
                        Contact Advisor
                    </a>
                </div>
                <div class="col-6 col-lg-3">
                    <a href="<?php echo BASE_URL; ?>dashboard/alerts" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                        <i class="fas fa-bell fa-lg mb-2"></i>
                        Manage Alerts
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Properties</h5>
                    <a href="<?php echo BASE_URL; ?>properties" class="small text-decoration-none">View all</a>
                </div>
                <div class="card-body">
                    <?php if (empty($data['recent_properties'])): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-compass fa-2x text-primary mb-3"></i>
                            <p class="text-muted mb-3">No recent properties viewed.</p>
                            <a href="<?php echo BASE_URL; ?>properties" class="btn btn-sm btn-primary">Start exploring</a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($data['recent_properties'] as $property): ?>
                                <a href="<?php echo BASE_URL; ?>property/<?php echo $property['id']; ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                                    <div class="avatar bg-primary-subtle text-primary-emphasis rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold"><?php echo htmlspecialchars($property['title']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($property['location']); ?></small>
                                    </div>
                                    <span class="ms-auto fw-semibold text-primary"><?php echo Helpers::formatCurrency($property['price']); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Favorites</h5>
                    <a href="<?php echo BASE_URL; ?>dashboard/favorites" class="small text-decoration-none">Manage</a>
                </div>
                <div class="card-body">
                    <?php if (empty($data['favorites'])): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-heart fa-2x text-danger mb-3"></i>
                            <p class="text-muted mb-3">No favorite properties yet.</p>
                            <a href="<?php echo BASE_URL; ?>properties" class="btn btn-sm btn-outline-primary">Browse & save</a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($data['favorites'] as $property): ?>
                                <a href="<?php echo BASE_URL; ?>property/<?php echo $property['id']; ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                                    <div class="avatar bg-danger-subtle text-danger-emphasis rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold"><?php echo htmlspecialchars($property['title']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($property['location']); ?></small>
                                    </div>
                                    <span class="ms-auto fw-semibold text-primary"><?php echo Helpers::formatCurrency($property['price']); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>