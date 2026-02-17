<?php

/**
 * Unified Investor Dashboard - APS Dream Homes
 * Complete investor portal with portfolio management, ROI tracking, and investment analytics
 */

require_once __DIR__ . '/../core/init.php';

// Check if user is logged in
adminAccessControl(['investor', 'admin', 'superadmin']);

$investor_id = getAuthUserId();
$investor_name = getAuthFullName();
$investor_email = getAuthUserEmail();

// Initialize database connection
$db = \App\Core\App::database();

// Get investor data
$investor_data = [];
try {
    $investor_data = $db->fetchOne("SELECT * FROM investors WHERE id = :id", ['id' => $investor_id]);
} catch (Exception $e) {
    error_log("Error fetching investor data: " . $e->getMessage());
}

// Get investor statistics
$stats = [
    'total_invested' => 0,
    'active_investments' => 0,
    'total_returns' => 0,
    'roi_percentage' => 0,
    'properties_owned' => 0,
    'monthly_income' => 0
];

try {
    // Total invested
    $row = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM investments WHERE investor_id = :id", ['id' => $investor_id]);
    $stats['total_invested'] = $row['total'] ?? 0;

    // Active investments
    $row = $db->fetchOne("SELECT COUNT(*) as count FROM investments WHERE investor_id = :id AND status = 'active'", ['id' => $investor_id]);
    $stats['active_investments'] = $row['count'] ?? 0;

    // Total returns
    $row = $db->fetchOne("SELECT COALESCE(SUM(return_amount), 0) as total FROM investment_returns WHERE investor_id = :id", ['id' => $investor_id]);
    $stats['total_returns'] = $row['total'] ?? 0;

    // Properties owned
    $row = $db->fetchOne("SELECT COUNT(*) as count FROM property_investments WHERE investor_id = :id", ['id' => $investor_id]);
    $stats['properties_owned'] = $row['count'] ?? 0;

    // Monthly income
    $row = $db->fetchOne("
        SELECT COALESCE(SUM(return_amount), 0) as total
        FROM investment_returns
        WHERE investor_id = :id AND DATE(return_date) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ", ['id' => $investor_id]);
    $stats['monthly_income'] = $row['total'] ?? 0;

    // Calculate ROI percentage
    if ($stats['total_invested'] > 0) {
        $stats['roi_percentage'] = (($stats['total_returns'] / $stats['total_invested']) * 100);
    }
} catch (Exception $e) {
    error_log("Error fetching investor stats: " . $e->getMessage());
}

// Get recent investments
$recent_investments = [];
try {
    $recent_investments = $db->fetchAll("
        SELECT i.*, p.title as property_title, p.location as property_location
        FROM investments i
        LEFT JOIN properties p ON i.property_id = p.id
        WHERE i.investor_id = :id
        ORDER BY i.created_at DESC LIMIT 5
    ", ['id' => $investor_id]);
} catch (Exception $e) {
    error_log("Error fetching recent investments: " . $e->getMessage());
}

// Get investment portfolio
$investment_portfolio = [];
try {
    $investment_portfolio = $db->fetchAll("
        SELECT p.*, pi.investment_amount, pi.shares_percentage, pi.investment_date
        FROM properties p
        INNER JOIN property_investments pi ON p.id = pi.property_id
        WHERE pi.investor_id = :id
        ORDER BY pi.investment_amount DESC LIMIT 4
    ", ['id' => $investor_id]);
} catch (Exception $e) {
    error_log("Error fetching investment portfolio: " . $e->getMessage());
}

// Get recent returns
$recent_returns = [];
try {
    $recent_returns = $db->fetchAll("
        SELECT ir.*, p.title as property_title
        FROM investment_returns ir
        LEFT JOIN properties p ON ir.property_id = p.id
        WHERE ir.investor_id = :id
        ORDER BY ir.return_date DESC LIMIT 5
    ", ['id' => $investor_id]);
} catch (Exception $e) {
    error_log("Error fetching recent returns: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investor Dashboard - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Inter', sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .investment-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .investment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .property-image {
            height: 200px;
            overflow: hidden;
        }

        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .investment-card:hover .property-image img {
            transform: scale(1.05);
        }

        .investment-content {
            padding: 1.5rem;
        }

        .investment-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .investment-amount {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .investment-location {
            color: #666;
            font-size: 0.9rem;
        }

        .activity-item {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--primary-color);
        }

        .activity-type {
            font-size: 0.8rem;
            color: var(--primary-color);
            font-weight: 600;
            text-transform: uppercase;
        }

        .activity-time {
            color: #666;
            font-size: 0.8rem;
        }

        .return-item {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--success-color);
        }

        .quick-action-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            text-decoration: none;
            color: inherit;
        }

        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: inherit;
        }

        .quick-action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .quick-action-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include_once '../includes/components/header.php'; ?>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-2">
                        <i class="fas fa-chart-line me-3"></i>Welcome Back, <?php echo h($investor_name); ?>!
                    </h1>
                    <p class="lead mb-0">Your investment portfolio dashboard</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="#investments" class="btn btn-light">
                            <i class="fas fa-search me-2"></i>Browse Opportunities
                        </a>
                        <a href="#portfolio" class="btn btn-outline-light">
                            <i class="fas fa-briefcase me-2"></i>Portfolio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-number">₹<?php echo h(number_format($stats['total_invested'])); ?></div>
                    <div class="stat-label">Total Invested</div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--success-color);">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="stat-number"><?php echo h($stats['active_investments']); ?></div>
                    <div class="stat-label">Active Investments</div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--warning-color);">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-number"><?php echo h(number_format($stats['roi_percentage'], 1)); ?>%</div>
                    <div class="stat-label">ROI</div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--info-color);">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-number"><?php echo h($stats['properties_owned']); ?></div>
                    <div class="stat-label">Properties Owned</div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3" data-aos="fade-up" data-aos-delay="500">
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--danger-color);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-number">₹<?php echo h(number_format($stats['total_returns'])); ?></div>
                    <div class="stat-label">Total Returns</div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3" data-aos="fade-up" data-aos-delay="600">
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--secondary-color);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-number">₹<?php echo h(number_format($stats['monthly_income'])); ?></div>
                    <div class="stat-label">Monthly Income</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-3" data-aos="fade-up">Quick Actions</h3>
            </div>
            <div class="col-lg-3 col-md-6 mb-3" data-aos="fade-up" data-aos-delay="100">
                <a href="#new-investment" class="quick-action-card">
                    <div class="quick-action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="quick-action-title">New Investment</div>
                    <p class="text-muted">Explore opportunities</p>
                </a>
            </div>

            <div class="col-lg-3 col-md-6 mb-3" data-aos="fade-up" data-aos-delay="200">
                <a href="#portfolio" class="quick-action-card">
                    <div class="quick-action-icon" style="color: var(--success-color);">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="quick-action-title">View Portfolio</div>
                    <p class="text-muted">Manage investments</p>
                </a>
            </div>

            <div class="col-lg-3 col-md-6 mb-3" data-aos="fade-up" data-aos-delay="300">
                <a href="#reports" class="quick-action-card">
                    <div class="quick-action-icon" style="color: var(--warning-color);">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="quick-action-title">ROI Reports</div>
                    <p class="text-muted">Performance analytics</p>
                </a>
            </div>

            <div class="col-lg-3 col-md-6 mb-3" data-aos="fade-up" data-aos-delay="400">
                <a href="#withdraw" class="quick-action-card">
                    <div class="quick-action-icon" style="color: var(--info-color);">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="quick-action-title">Withdraw Returns</div>
                    <p class="text-muted">Access your funds</p>
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Investment Portfolio -->
            <div class="col-lg-8 mb-4">
                <h3 class="mb-3" data-aos="fade-up">Your Investment Portfolio</h3>
                <div class="row">
                    <?php if (!empty($investment_portfolio)): ?>
                        <?php foreach ($investment_portfolio as $property): ?>
                            <div class="col-md-6 col-lg-4 mb-3" data-aos="fade-up" data-aos-delay="<?php echo h(array_search($property, $investment_portfolio) * 100); ?>">
                                <div class="investment-card">
                                    <div class="property-image">
                                        <img src="<?php echo h($property['image_url'] ?? getPlaceholderUrl(300, 200, 'Property')); ?>" alt="<?php echo h($property['title']); ?>">
                                    </div>
                                    <div class="investment-content">
                                        <h5 class="investment-title"><?php echo h($property['title']); ?></h5>
                                        <div class="investment-amount">₹<?php echo h(number_format($property['investment_amount'])); ?></div>
                                        <div class="investment-location">
                                            <i class="fas fa-map-marker-alt me-1"></i><?php echo h($property['location']); ?>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">Shares: <?php echo h($property['shares_percentage']); ?>%</small>
                                        </div>
                                        <div class="mt-3">
                                            <a href="#property-details-<?php echo h($property['id']); ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-chart-line me-1"></i>View Performance
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12" data-aos="fade-up">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                No investments yet. Start exploring investment opportunities!
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Returns -->
            <div class="col-lg-4 mb-4">
                <h3 class="mb-3" data-aos="fade-up">Recent Returns</h3>
                <div class="returns-container">
                    <?php if (!empty($recent_returns)): ?>
                        <?php foreach ($recent_returns as $return): ?>
                            <div class="return-item" data-aos="fade-up" data-aos-delay="<?php echo h(array_search($return, $recent_returns) * 50); ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="activity-type">Return Received</div>
                                        <div class="mt-1">
                                            <?php echo h($return['property_title'] ?? 'General Investment'); ?>
                                        </div>
                                        <div class="mt-1">
                                            <strong>₹<?php echo h(number_format($return['return_amount'])); ?></strong>
                                        </div>
                                    </div>
                                    <div class="activity-time">
                                        <?php echo h(date('M d, Y', strtotime($return['return_date']))); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-light text-center" data-aos="fade-up">
                            <i class="fas fa-history me-2"></i>
                            No returns received yet
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Investments -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-3" data-aos="fade-up">Recent Investment Activity</h3>
            </div>
            <?php if (!empty($recent_investments)): ?>
                <?php foreach ($recent_investments as $investment): ?>
                    <div class="col-md-6 col-lg-4 mb-3" data-aos="fade-up" data-aos-delay="<?php echo h(array_search($investment, $recent_investments) * 100); ?>">
                        <div class="investment-card">
                            <div class="investment-content">
                                <h5 class="investment-title"><?php echo h($investment['property_title'] ?? 'Investment Opportunity'); ?></h5>
                                <div class="investment-amount">₹<?php echo h(number_format($investment['amount'])); ?></div>
                                <div class="investment-location">
                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo h($investment['property_location'] ?? 'Multiple Locations'); ?>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Status: <span class="badge bg-<?php echo h($investment['status'] == 'active' ? 'success' : 'warning'); ?>"><?php echo h(ucfirst($investment['status'])); ?></span></small>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted">Invested on <?php echo h(date('M d, Y', strtotime($investment['created_at']))); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12" data-aos="fade-up">
                    <div class="alert alert-light text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        No investment activities yet
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include_once '../includes/components/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        // Auto-refresh dashboard every 60 seconds
        setInterval(function() {
            location.reload();
        }, 60000);
    </script>

    <!-- Logout Section -->
    <div class="logout-section" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
        <a href="logout.php" class="btn btn-danger btn-sm">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </div>

</body>

</html>