<?php

/**
 * Customer Dashboard View
 * Content only - layout handled by controller
 */

// Set page variables for layout
$page_title = $page_title ?? 'My Dashboard';
$current_page = 'dashboard';

// Dashboard data
$stats = $stats ?? [
    'total_properties' => 3,
    'active_inquiries' => 5,
    'saved_properties' => 12,
    'total_views' => 156
];

$my_properties = $my_properties ?? [
    ['title' => '2 BHK Apartment', 'location' => 'Suryoday Colony', 'price' => 4500000, 'status' => 'active', 'image' => 'property1.jpg'],
    ['title' => 'Residential Plot', 'location' => 'Raghunath City', 'price' => 2500000, 'status' => 'pending', 'image' => 'property2.jpg'],
];

$recent_inquiries = $recent_inquiries ?? [
    ['property' => '3 BHK Villa - Suryoday Heights', 'type' => 'Buy', 'status' => 'pending', 'date' => '2026-04-10'],
    ['property' => 'Commercial Shop - City Center', 'type' => 'Rent', 'status' => 'replied', 'date' => '2026-04-08'],
    ['property' => '2 BHK Flat - Braj Radha Enclave', 'type' => 'Buy', 'status' => 'viewing', 'date' => '2026-04-05'],
];

$services = $services ?? [
    ['icon' => 'fa-home', 'title' => 'Buy Property', 'description' => 'Find your dream home', 'url' => '/buy', 'color' => 'primary'],
    ['icon' => 'fa-building', 'title' => 'Sell Property', 'description' => 'List your property', 'url' => '/sell', 'color' => 'success'],
    ['icon' => 'fa-chart-line', 'title' => 'Investment', 'description' => 'Grow your wealth', 'url' => '/invest', 'color' => 'info'],
];
?>

<!-- Welcome Banner -->
<div class="card border-0 shadow-sm mb-4 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Customer'); ?>! 👋</h4>
                <p class="mb-0 opacity-75">Manage your properties, track inquiries, and explore our services all in one place.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i>Post Property
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-value"><?php echo $stats['total_properties']; ?></div>
            <div class="stat-label">My Properties</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-value"><?php echo $stats['active_inquiries']; ?></div>
            <div class="stat-label">Active Inquiries</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-heart"></i>
            </div>
            <div class="stat-value"><?php echo $stats['saved_properties']; ?></div>
            <div class="stat-label">Saved Properties</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-eye"></i>
            </div>
            <div class="stat-value"><?php echo $stats['total_views']; ?></div>
            <div class="stat-label">Total Views</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- My Properties -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-building text-primary me-2"></i>My Properties</h5>
                    <a href="<?php echo BASE_URL; ?>/user/properties" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($my_properties as $property): ?>
                        <div class="col-md-6">
                            <div class="property-card border rounded-3 p-3">
                                <div class="d-flex gap-3">
                                    <div class="property-image bg-light rounded-3" style="width: 80px; height: 80px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-home fa-2x text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($property['title']); ?></h6>
                                        <p class="text-muted mb-1 small"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($property['location']); ?></p>
                                        <p class="mb-2"><strong>₹<?php echo number_format($property['price']); ?></strong></p>
                                        <span class="badge bg-<?php echo $property['status'] === 'active' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($property['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Recent Inquiries -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-envelope text-success me-2"></i>Recent Inquiries</h5>
                    <a href="<?php echo BASE_URL; ?>/user/inquiries" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Property</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_inquiries as $inquiry): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($inquiry['property']); ?></td>
                                    <td><?php echo htmlspecialchars($inquiry['type']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $inquiry['status'] === 'replied' ? 'success' : ($inquiry['status'] === 'pending' ? 'warning' : 'info'); ?>">
                                            <?php echo ucfirst($inquiry['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d', strtotime($inquiry['date'])); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Post New Property
                    </a>
                    <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>Browse Properties
                    </a>
                    <a href="<?php echo BASE_URL; ?>/financial-services" class="btn btn-outline-success">
                        <i class="fas fa-hand-holding-usd me-2"></i>Apply for Loan
                    </a>
                </div>
            </div>
        </div>

        <!-- Services -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-concierge-bell text-info me-2"></i>Our Services</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($services as $service): ?>
                        <div class="col-6">
                            <a href="<?php echo BASE_URL . $service['url']; ?>" class="text-decoration-none">
                                <div class="service-card text-center p-3 border rounded-3 h-100">
                                    <div class="service-icon mb-2 text-<?php echo $service['color']; ?>">
                                        <i class="fas <?php echo $service['icon']; ?> fa-2x"></i>
                                    </div>
                                    <h6 class="mb-1"><?php echo $service['title']; ?></h6>
                                    <small class="text-muted"><?php echo $service['desc']; ?></small>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Profile Completion -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-user-check text-purple me-2"></i>Profile Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Profile Completion</span>
                    <strong>75%</strong>
                </div>
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar bg-primary" style="width: 75%"></div>
                </div>
                <div class="small text-muted">
                    <p class="mb-1"><i class="fas fa-check text-success me-1"></i> Basic info added</p>
                    <p class="mb-1"><i class="fas fa-check text-success me-1"></i> Contact details added</p>
                    <p class="mb-1"><i class="fas fa-times text-danger me-1"></i> Bank details pending</p>
                    <p class="mb-0"><i class="fas fa-times text-danger me-1"></i> KYC verification pending</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/user/profile" class="btn btn-outline-primary btn-sm w-100 mt-3">
                    Complete Profile
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        height: 100%;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .stat-icon.blue {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .stat-icon.green {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .stat-icon.orange {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .stat-icon.purple {
        background: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #64748b;
    }

    .property-card {
        transition: all 0.2s ease;
    }

    .property-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .service-card {
        transition: all 0.2s ease;
    }

    .service-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
</style>