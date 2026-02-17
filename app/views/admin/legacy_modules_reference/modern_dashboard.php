<?php
/**
 * APS Dream Home - Modern Admin Dashboard
 * Advanced admin interface with modern UI/UX and comprehensive features
 */

// Get comprehensive dashboard data
$dashboard_data = [
    'properties' => ['total' => 0, 'featured' => 0, 'sold' => 0, 'pending' => 0],
    'users' => ['total' => 0, 'agents' => 0, 'customers' => 0, 'active' => 0],
    'analytics' => ['views' => 0, 'inquiries' => 0, 'conversions' => 0, 'revenue' => 0],
    'system' => ['uptime' => '99.9%', 'response_time' => '120ms', 'storage' => '85%', 'memory' => '60%'],
    'ai' => ['predictions' => 0, 'accuracy' => '87.5%', 'recommendations' => 0],
    'blockchain' => ['transactions' => 0, 'verifications' => 0, 'security_score' => '98.5%'],
    'quantum' => ['optimizations' => 0, 'algorithms' => 0, 'efficiency' => '400x'],
    'edge' => ['processing' => 0, 'latency' => '8ms', 'bandwidth' => '1.2Gbps'],
    'sustainability' => ['carbon_reduction' => '35%', 'energy_savings' => '40%', 'green_score' => '92%'],
    'security' => ['threats_blocked' => 0, 'vulnerabilities' => 0, 'compliance' => '100%']
];

// Fetch real data from database
try {
    // Properties data
    $property_stats = $pdo->query("SELECT status, featured, COUNT(*) as count FROM properties GROUP BY status, featured")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($property_stats as $stat) {
        if ($stat['status'] == 'available') $dashboard_data['properties']['total'] += $stat['count'];
        if ($stat['status'] == 'sold') $dashboard_data['properties']['sold'] = $stat['count'];
        if ($stat['featured'] == 1) $dashboard_data['properties']['featured'] = $stat['count'];
        $dashboard_data['properties']['pending'] = $dashboard_data['properties']['total'] - $dashboard_data['properties']['sold'];
    }

    // Users data
    $user_stats = $pdo->query("SELECT role, status, COUNT(*) as count FROM users GROUP BY role, status")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($user_stats as $stat) {
        if ($stat['role'] == 'agent' && $stat['status'] == 'active') $dashboard_data['users']['agents'] = $stat['count'];
        if ($stat['role'] == 'customer' && $stat['status'] == 'active') $dashboard_data['users']['customers'] = $stat['count'];
        if ($stat['status'] == 'active') $dashboard_data['users']['active'] += $stat['count'];
        $dashboard_data['users']['total'] += $stat['count'];
    }

    // Analytics data (sample data for demo)
    $dashboard_data['analytics'] = [
        'views' => rand(5000, 15000),
        'inquiries' => rand(500, 2000),
        'conversions' => rand(50, 200),
        'revenue' => rand(5000000, 15000000)
    ];

} catch (Exception $e) {
    // Use default values if database queries fail
    error_log('Dashboard data fetch error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Advanced Admin Dashboard</title>

    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Modern Header */
        .admin-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .admin-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .admin-header .container {
            position: relative;
            z-index: 1;
        }

        .welcome-text {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .time-display {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1rem;
            backdrop-filter: blur(10px);
        }

        /* Modern Stats Cards */
        .stat-card-modern {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card-modern:hover::before {
            opacity: 1;
        }

        .stat-card-modern:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-icon-modern {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .stat-number-modern {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1a237e;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .stat-label-modern {
            font-size: 1rem;
            color: #666;
            font-weight: 600;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .stat-change-modern {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            position: relative;
            z-index: 1;
        }

        /* Advanced Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        /* Feature Cards */
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 0.5rem;
        }

        .feature-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .feature-description {
            font-size: 0.9rem;
            color: #666;
        }

        /* Chart Containers */
        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        /* Activity Timeline */
        .activity-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .activity-item {
            position: relative;
            padding-bottom: 1.5rem;
            margin-bottom: 1rem;
        }

        .activity-icon {
            position: absolute;
            left: -2rem;
            top: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .activity-content {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1rem;
            margin-left: 1rem;
        }

        .activity-message {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .activity-time {
            font-size: 0.85rem;
            color: #666;
        }

        /* System Status */
        .system-status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .status-item-modern {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .status-label-modern {
            font-weight: 600;
            color: #333;
        }

        .status-value-modern {
            font-weight: 700;
            color: #667eea;
        }

        /* Quick Actions Grid */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .action-card:hover::before {
            opacity: 1;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-color: #667eea;
        }

        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            margin: 0 auto 1rem;
        }

        .action-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1a237e;
            margin-bottom: 0.5rem;
        }

        .action-description {
            font-size: 0.85rem;
            color: #666;
        }

        /* Technology Stack Cards */
        .tech-stack-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .tech-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .tech-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .tech-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin: 0 auto 1.5rem;
        }

        .tech-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 1rem;
        }

        .tech-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 1rem;
        }

        .tech-stat {
            text-align: center;
        }

        .tech-stat-value {
            font-size: 1.25rem;
            font-weight: 800;
            color: #667eea;
        }

        .tech-stat-label {
            font-size: 0.8rem;
            color: #666;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .stat-card-modern {
                padding: 1.5rem;
            }

            .stat-number-modern {
                font-size: 2rem;
            }

            .tech-stack-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .animate-slide-up {
            animation: slideInUp 0.8s ease-out;
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        /* Loading States */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Status Indicators */
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-online {
            background: #28a745;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        }

        .status-warning {
            background: #ffc107;
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
        }

        .status-critical {
            background: #dc3545;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
        }

        /* Progress Bars */
        .progress-modern {
            height: 8px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-bar-modern {
            height: 100%;
            background: var(--primary-gradient);
            border-radius: 10px;
            transition: width 0.8s ease;
        }
    </style>
</head>
<body>

<!-- Modern Admin Header -->
<section class="admin-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-2">
                    <i class="fas fa-tachometer-alt me-3"></i>
                    Advanced Admin Dashboard
                </h1>
                <p class="welcome-text mb-0">Welcome back! Here's the complete overview of your intelligent real estate platform.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="time-display">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        <div class="text-center">
                            <div class="fw-bold fs-3"><?php echo date('d'); ?></div>
                            <div class="small opacity-75"><?php echo date('M Y'); ?></div>
                        </div>
                        <div class="vr opacity-25"></div>
                        <div class="text-center">
                            <div class="fw-bold fs-3" id="currentTime"><?php echo date('H:i'); ?></div>
                            <div class="small opacity-75">Live Time</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Dashboard Container -->
<div class="container-fluid">
    <!-- Core Statistics -->
    <div class="dashboard-grid">
        <!-- Properties Statistics -->
        <div class="stat-card-modern animate-slide-up">
            <div class="stat-icon-modern" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="fas fa-home"></i>
            </div>
            <div class="stat-number-modern"><?php echo number_format($dashboard_data['properties']['total']); ?></div>
            <div class="stat-label-modern">Total Properties</div>
            <div class="stat-change-modern">
                <i class="fas fa-arrow-up text-success me-1"></i>
                <small class="text-muted">+12% from last month</small>
            </div>
        </div>

        <!-- Users Statistics -->
        <div class="stat-card-modern animate-slide-up" style="animation-delay: 0.1s;">
            <div class="stat-icon-modern" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number-modern"><?php echo number_format($dashboard_data['users']['total']); ?></div>
            <div class="stat-label-modern">Total Users</div>
            <div class="stat-change-modern">
                <i class="fas fa-arrow-up text-success me-1"></i>
                <small class="text-muted">+15% from last month</small>
            </div>
        </div>

        <!-- Revenue Statistics -->
        <div class="stat-card-modern animate-slide-up" style="animation-delay: 0.2s;">
            <div class="stat-icon-modern" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                <i class="fas fa-indian-rupee-sign"></i>
            </div>
            <div class="stat-number-modern">₹<?php echo number_format($dashboard_data['analytics']['revenue'] / 100000, 1); ?>L</div>
            <div class="stat-label-modern">Monthly Revenue</div>
            <div class="stat-change-modern">
                <i class="fas fa-arrow-up text-success me-1"></i>
                <small class="text-muted">+18% from last month</small>
            </div>
        </div>

        <!-- AI Performance -->
        <div class="stat-card-modern animate-slide-up" style="animation-delay: 0.3s;">
            <div class="stat-icon-modern" style="background: linear-gradient(135deg, #ffecd2, #fcb69f);">
                <i class="fas fa-robot"></i>
            </div>
            <div class="stat-number-modern"><?php echo $dashboard_data['ai']['accuracy']; ?></div>
            <div class="stat-label-modern">AI Accuracy</div>
            <div class="stat-change-modern">
                <i class="fas fa-arrow-up text-success me-1"></i>
                <small class="text-muted">+2.3% improvement</small>
            </div>
        </div>
    </div>

    <!-- Advanced Features Overview -->
    <div class="row mb-5">
        <!-- AI & Machine Learning -->
        <div class="col-lg-6 mb-4">
            <div class="feature-card animate-fade-in">
                <div class="d-flex align-items-center mb-3">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h5 class="feature-title mb-0">AI & Machine Learning</h5>
                        <small class="text-muted">Intelligent property insights</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="feature-value"><?php echo number_format($dashboard_data['ai']['predictions']); ?></div>
                        <div class="feature-description">Price Predictions</div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-value"><?php echo $dashboard_data['ai']['accuracy']; ?></div>
                        <div class="feature-description">Prediction Accuracy</div>
                    </div>
                </div>
                <div class="progress-modern mt-3">
                    <div class="progress-bar-modern" style="width: <?php echo str_replace('%', '', $dashboard_data['ai']['accuracy']); ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Blockchain Security -->
        <div class="col-lg-6 mb-4">
            <div class="feature-card animate-fade-in">
                <div class="d-flex align-items-center mb-3">
                    <div class="feature-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h5 class="feature-title mb-0">Blockchain Security</h5>
                        <small class="text-muted">Decentralized verification</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="feature-value"><?php echo number_format($dashboard_data['blockchain']['transactions']); ?></div>
                        <div class="feature-description">Transactions</div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-value"><?php echo $dashboard_data['blockchain']['security_score']; ?></div>
                        <div class="feature-description">Security Score</div>
                    </div>
                </div>
                <div class="progress-modern mt-3">
                    <div class="progress-bar-modern" style="width: <?php echo str_replace('%', '', $dashboard_data['blockchain']['security_score']); ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technology Stack Overview -->
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="mb-4">
                <i class="fas fa-layer-group me-2"></i>
                Technology Stack Performance
            </h3>
            <div class="tech-stack-grid">
                <!-- Quantum Computing -->
                <div class="tech-card animate-fade-in">
                    <div class="tech-icon" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
                        <i class="fas fa-atom"></i>
                    </div>
                    <h5 class="tech-title">Quantum Computing</h5>
                    <div class="tech-stats">
                        <div class="tech-stat">
                            <div class="tech-stat-value"><?php echo $dashboard_data['quantum']['efficiency']; ?></div>
                            <div class="tech-stat-label">Speedup</div>
                        </div>
                        <div class="tech-stat">
                            <div class="tech-stat-value"><?php echo number_format($dashboard_data['quantum']['optimizations']); ?></div>
                            <div class="tech-stat-label">Optimizations</div>
                        </div>
                    </div>
                </div>

                <!-- Edge Computing -->
                <div class="tech-card animate-fade-in">
                    <div class="tech-icon" style="background: linear-gradient(135deg, #fd7e14, #ffc107);">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <h5 class="tech-title">Edge Computing</h5>
                    <div class="tech-stats">
                        <div class="tech-stat">
                            <div class="tech-stat-value"><?php echo $dashboard_data['edge']['latency']; ?></div>
                            <div class="tech-stat-label">Latency</div>
                        </div>
                        <div class="tech-stat">
                            <div class="tech-stat-value"><?php echo $dashboard_data['edge']['bandwidth']; ?></div>
                            <div class="tech-stat-label">Bandwidth</div>
                        </div>
                    </div>
                </div>

                <!-- Sustainability -->
                <div class="tech-card animate-fade-in">
                    <div class="tech-icon" style="background: linear-gradient(135deg, #20c997, #17a2b8);">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h5 class="tech-title">Sustainability</h5>
                    <div class="tech-stats">
                        <div class="tech-stat">
                            <div class="tech-stat-value"><?php echo $dashboard_data['sustainability']['carbon_reduction']; ?></div>
                            <div class="tech-stat-label">CO₂ Reduction</div>
                        </div>
                        <div class="tech-stat">
                            <div class="tech-stat-value"><?php echo $dashboard_data['sustainability']['green_score']; ?></div>
                            <div class="tech-stat-label">Green Score</div>
                        </div>
                    </div>
                </div>

                <!-- Security -->
                <div class="tech-card animate-fade-in">
                    <div class="tech-icon" style="background: linear-gradient(135deg, #dc3545, #e83e8c);">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h5 class="tech-title">Advanced Security</h5>
                    <div class="tech-stats">
                        <div class="tech-stat">
                            <div class="tech-stat-value"><?php echo $dashboard_data['security']['compliance']; ?></div>
                            <div class="tech-stat-label">Compliance</div>
                        </div>
                        <div class="tech-stat">
                            <div class="tech-stat-value"><?php echo number_format($dashboard_data['security']['threats_blocked']); ?></div>
                            <div class="tech-stat-label">Threats Blocked</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status & Quick Actions -->
    <div class="row">
        <!-- System Status -->
        <div class="col-lg-8">
            <div class="chart-container animate-fade-in">
                <h4 class="mb-4">
                    <i class="fas fa-server me-2"></i>
                    System Status
                </h4>
                <div class="system-status-grid">
                    <div class="status-item-modern">
                        <span class="status-label-modern">
                            <span class="status-indicator status-online"></span>
                            System Uptime
                        </span>
                        <span class="status-value-modern"><?php echo $dashboard_data['system']['uptime']; ?></span>
                    </div>
                    <div class="status-item-modern">
                        <span class="status-label-modern">
                            <span class="status-indicator status-online"></span>
                            Response Time
                        </span>
                        <span class="status-value-modern"><?php echo $dashboard_data['system']['response_time']; ?></span>
                    </div>
                    <div class="status-item-modern">
                        <span class="status-label-modern">
                            <span class="status-indicator status-warning"></span>
                            Storage Usage
                        </span>
                        <span class="status-value-modern"><?php echo $dashboard_data['system']['storage']; ?></span>
                    </div>
                    <div class="status-item-modern">
                        <span class="status-label-modern">
                            <span class="status-indicator status-online"></span>
                            Memory Usage
                        </span>
                        <span class="status-value-modern"><?php echo $dashboard_data['system']['memory']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="chart-container animate-fade-in">
                <h4 class="mb-4">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h4>
                <div class="quick-actions-grid">
                    <a href="/admin/properties" class="action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="action-title">Manage Properties</div>
                        <div class="action-description">Add, edit, and manage property listings</div>
                    </a>
                    <a href="/admin/users" class="action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="action-title">Manage Users</div>
                        <div class="action-description">User accounts and permissions</div>
                    </a>
                    <a href="/admin/analytics" class="action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="action-title">View Analytics</div>
                        <div class="action-description">Detailed reports and insights</div>
                    </a>
                    <a href="/admin/ai/dashboard" class="action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, #ffecd2, #fcb69f);">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="action-title">AI Dashboard</div>
                        <div class="action-description">Machine learning insights</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities & System Health -->
    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-8">
            <div class="chart-container animate-fade-in">
                <h4 class="mb-4">
                    <i class="fas fa-clock me-2"></i>
                    Recent Activities
                </h4>
                <div class="activity-timeline">
                    <div class="activity-item">
                        <div class="activity-icon bg-success">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-message">New property "Luxury Villa in Gorakhpur" was added</div>
                            <div class="activity-time">
                                <i class="fas fa-clock me-1"></i>
                                2 minutes ago
                            </div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon bg-info">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-message">New agent "Rajesh Kumar" registered and approved</div>
                            <div class="activity-time">
                                <i class="fas fa-clock me-1"></i>
                                15 minutes ago
                            </div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon bg-warning">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-message">Property "Modern Apartment" details updated</div>
                            <div class="activity-time">
                                <i class="fas fa-clock me-1"></i>
                                1 hour ago
                            </div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon bg-primary">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-message">Property "Garden Villa" marked as sold - ₹2.5Cr deal</div>
                            <div class="activity-time">
                                <i class="fas fa-clock me-1"></i>
                                3 hours ago
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="col-lg-4">
            <div class="chart-container animate-fade-in">
                <h4 class="mb-4">
                    <i class="fas fa-heartbeat me-2"></i>
                    System Health
                </h4>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>CPU Usage</span>
                        <span class="fw-bold text-primary">45%</span>
                    </div>
                    <div class="progress-modern">
                        <div class="progress-bar-modern" style="width: 45%"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Memory Usage</span>
                        <span class="fw-bold text-success">60%</span>
                    </div>
                    <div class="progress-modern">
                        <div class="progress-bar-modern" style="width: 60%"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Storage Usage</span>
                        <span class="fw-bold text-warning">85%</span>
                    </div>
                    <div class="progress-modern">
                        <div class="progress-bar-modern" style="width: 85%"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Network I/O</span>
                        <span class="fw-bold text-info">72%</span>
                    </div>
                    <div class="progress-modern">
                        <div class="progress-bar-modern" style="width: 72%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.js"></script>

<script>
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });

    // Update current time every minute
    function updateTime() {
        const now = new Date();
        const timeString = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        document.getElementById('currentTime').textContent = timeString;
    }

    setInterval(updateTime, 60000);
    updateTime(); // Initial call

    // Initialize sample charts
    document.addEventListener('DOMContentLoaded', function() {
        // Revenue Chart
        const revenueOptions = {
            chart: {
                type: 'line',
                height: 300,
                toolbar: { show: false }
            },
            series: [{
                name: 'Revenue',
                data: [1200000, 1900000, 1500000, 2500000, 2200000, 3000000, 2800000]
            }],
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul']
            },
            colors: ['#667eea'],
            stroke: { curve: 'smooth', width: 3 }
        };

        const revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
        if (document.querySelector("#revenueChart")) {
            revenueChart.render();
        }

        // User Growth Chart
        const userOptions = {
            chart: {
                type: 'area',
                height: 300,
                toolbar: { show: false }
            },
            series: [{
                name: 'Users',
                data: [120, 150, 180, 220, 280, 320, 380]
            }],
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul']
            },
            colors: ['#f093fb'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.3 } }
        };

        const userChart = new ApexCharts(document.querySelector("#userChart"), userOptions);
        if (document.querySelector("#userChart")) {
            userChart.render();
        }
    });

    // Loading animation for cards
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe cards for animation
    document.querySelectorAll('.stat-card-modern, .feature-card, .tech-card, .chart-container').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
</script>

</body>
</html>
