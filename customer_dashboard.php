<?php
/**
 * Enhanced Customer Dashboard - Modern & Beautiful
 * Professional design with animations and better UX
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Start session with consistent settings
if (session_status() === PHP_SESSION_NONE) {
    session_name('APS_DREAM_HOME_SESSID');
    session_set_cookie_params(86400, '/', $_SERVER['HTTP_HOST'] ?? '', false, true);
    session_start();
}

// Include configuration
require_once __DIR__ . '/config.php';

// Check if customer is logged in
if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
    header('Location: customer_login.php');
    exit();
}

// Update last activity
$_SESSION['last_activity'] = time();

// Get customer info
$customer_id = $_SESSION['customer_id'] ?? 0;
$customer_name = $_SESSION['customer_name'] ?? 'Customer';

// Enhanced database connection with more stats
$dashboard_stats = [
    'active_properties' => 0,
    'total_inquiries' => 0,
    'total_documents' => 0,
    'completed_payments' => 0,
    'total_spent' => 0,
    'pending_approvals' => 0,
    'recent_activities' => 0,
    'profile_completion' => 0
];

try {
    if (isset($conn)) {
        // Get comprehensive customer dashboard stats
        $stats_query = "
            SELECT
                (SELECT COUNT(*) FROM properties WHERE customer_id = ? AND status = 'active') as active_properties,
                (SELECT COUNT(*) FROM property_inquiries WHERE customer_id = ?) as total_inquiries,
                (SELECT COUNT(*) FROM documents WHERE customer_id = ?) as total_documents,
                (SELECT COUNT(*) FROM payments WHERE customer_id = ? AND status = 'completed') as completed_payments,
                (SELECT SUM(amount) FROM payments WHERE customer_id = ? AND status = 'completed') as total_spent,
                (SELECT COUNT(*) FROM properties WHERE customer_id = ? AND status = 'pending') as pending_approvals,
                (SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0) as unread_notifications
        ";

        $stmt = $conn->prepare($stats_query);
        if ($stmt) {
            $stmt->bind_param('iiiiiii', $customer_id, $customer_id, $customer_id, $customer_id, $customer_id, $customer_id, $customer_id);
            $stmt->execute();
            $stats_result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($stats_result) {
                $dashboard_stats = [
                    'active_properties' => $stats_result['active_properties'] ?? 0,
                    'total_inquiries' => $stats_result['total_inquiries'] ?? 0,
                    'total_documents' => $stats_result['total_documents'] ?? 0,
                    'completed_payments' => $stats_result['completed_payments'] ?? 0,
                    'total_spent' => $stats_result['total_spent'] ?? 0,
                    'pending_approvals' => $stats_result['pending_approvals'] ?? 0,
                    'unread_notifications' => $stats_result['unread_notifications'] ?? 0,
                    'recent_activities' => 0, // Will be calculated
                    'profile_completion' => 75 // Default value
                ];
            }
        }
    }
} catch (Exception $e) {
    // Use default stats on error
    error_log('Dashboard error: ' . $e->getMessage());
}

// Ensure BASE_URL is defined
if (!defined('BASE_URL')) {
    $base_url = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/apsdreamhome/';
} else {
    $base_url = BASE_URL;
}

// Calculate profile completion
$total_fields = 8; // Basic profile fields
$filled_fields = 0;
if (!empty($_SESSION['customer_name'])) $filled_fields++;
if (!empty($_SESSION['customer_email'])) $filled_fields++;
if (!empty($_SESSION['customer_phone'])) $filled_fields++;
if ($dashboard_stats['active_properties'] > 0) $filled_fields++;
if ($dashboard_stats['total_documents'] > 0) $filled_fields++;
if ($dashboard_stats['completed_payments'] > 0) $filled_fields++;
$dashboard_stats['profile_completion'] = min(100, round(($filled_fields / $total_fields) * 100));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Customer Dashboard - APS Dream Homes</title>
    <link rel="stylesheet" href="<?php echo rtrim($base_url, '/'); ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="<?php echo rtrim($base_url, '/'); ?>/assets/favicon.ico" type="image/x-icon">

    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #667eea;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c, #4facfe, #00f2fe);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.08)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
            z-index: -1;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .welcome-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .greeting h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            animation: textGlow 3s ease-in-out infinite alternate;
        }

        @keyframes textGlow {
            from { filter: brightness(1); }
            to { filter: brightness(1.1); }
        }

        .greeting p {
            color: #6c757d;
            font-size: 1.1rem;
            margin: 0;
        }

        .profile-completion {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .completion-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .completion-circle::before {
            content: '';
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: white;
            position: absolute;
        }

        .completion-text {
            text-align: center;
            font-weight: 600;
            color: var(--primary-color);
            z-index: 1;
        }

        .completion-number {
            font-size: 1.2rem;
            line-height: 1;
        }

        .completion-label {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-color), var(--secondary-color));
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
            position: relative;
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .stat-icon.properties { background: linear-gradient(135deg, #ff6b6b, #ee5a24); }
        .stat-icon.inquiries { background: linear-gradient(135deg, #4ecdc4, #44a08d); }
        .stat-icon.documents { background: linear-gradient(135deg, #45b7d1, #96c93d); }
        .stat-icon.payments { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .stat-icon.approvals { background: linear-gradient(135deg, #ffecd2, #fcb69f); color: var(--dark-color); }
        .stat-icon.notifications { background: linear-gradient(135deg, #a8edea, #fed6e3); color: var(--dark-color); }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            display: block;
        }

        .stat-label {
            font-size: 1rem;
            color: #6c757d;
            font-weight: 500;
        }

        .quick-actions {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 2rem;
            text-align: center;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .action-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-decoration: none;
            color: inherit;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s;
        }

        .action-card:hover::before {
            left: 100%;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border-color: var(--accent-color);
        }

        .action-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
            position: relative;
            transition: all 0.3s ease;
        }

        .action-card:hover .action-icon {
            transform: scale(1.1) rotate(-5deg);
        }

        .action-icon.add { background: linear-gradient(135deg, #667eea, #764ba2); }
        .action-icon.upload { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .action-icon.payment { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .action-icon.support { background: linear-gradient(135deg, #43e97b, #38f9d7); }

        .action-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .action-desc {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .recent-activity {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 15px;
            margin-bottom: 1rem;
            background: rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background: rgba(255, 255, 255, 0.8);
            transform: translateX(10px);
        }

        .activity-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
            color: white;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .logout-section {
            text-align: center;
            margin-top: 2rem;
        }

        .btn-logout {
            background: linear-gradient(135deg, var(--danger-color), #c82333);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 10px 25px rgba(220, 53, 69, 0.3);
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(220, 53, 69, 0.4);
            color: white;
            text-decoration: none;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 15px;
            }

            .welcome-header {
                flex-direction: column;
                text-align: center;
            }

            .greeting h1 {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .action-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .welcome-section {
                padding: 2rem;
            }

            .quick-actions {
                padding: 2rem;
            }

            .recent-activity {
                padding: 2rem;
            }
        }

        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            z-index: 9999;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .fade-in {
            animation: fadeInUp 0.6s ease-out;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .floating-element {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 20%;
            right: 15%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 15%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <div class="loading-spinner" id="loadingSpinner"></div>

    <!-- Floating Elements -->
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section fade-in">
            <div class="welcome-header">
                <div class="greeting">
                    <h1>Welcome back, <?php echo htmlspecialchars($customer_name); ?>! 👋</h1>
                    <p>Here's what's happening with your property portfolio today</p>
                </div>
                <div class="profile-completion">
                    <div class="completion-circle" style="background: conic-gradient(from 0deg, #28a745 <?php echo $dashboard_stats['profile_completion']; ?>%, #e9ecef <?php echo $dashboard_stats['profile_completion']; ?>%);">
                        <div class="completion-text">
                            <div class="completion-number"><?php echo $dashboard_stats['profile_completion']; ?>%</div>
                            <div class="completion-label">Complete</div>
                        </div>
                    </div>
                    <div>
                        <h4 style="color: var(--primary-color); margin: 0;">Profile Status</h4>
                        <p style="color: #6c757d; margin: 0;">Keep your profile updated</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <!-- Active Properties -->
            <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                <div class="stat-icon properties">
                    <i class="fas fa-home"></i>
                </div>
                <div class="stat-value"><?php echo $dashboard_stats['active_properties']; ?></div>
                <div class="stat-label">Active Properties</div>
            </div>

            <!-- Total Inquiries -->
            <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                <div class="stat-icon inquiries">
                    <i class="fas fa-search"></i>
                </div>
                <div class="stat-value"><?php echo $dashboard_stats['total_inquiries']; ?></div>
                <div class="stat-label">Total Inquiries</div>
            </div>

            <!-- Documents -->
            <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                <div class="stat-icon documents">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-value"><?php echo $dashboard_stats['total_documents']; ?></div>
                <div class="stat-label">Documents Uploaded</div>
            </div>

            <!-- Total Spent -->
            <div class="stat-card fade-in" style="animation-delay: 0.4s;">
                <div class="stat-icon payments">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value">₹<?php echo number_format($dashboard_stats['total_spent'], 2); ?></div>
                <div class="stat-label">Total Investment</div>
            </div>

            <!-- Pending Approvals -->
            <div class="stat-card fade-in" style="animation-delay: 0.5s;">
                <div class="stat-icon approvals">
                    <i class="fas fa-clock"></i>
                    <?php if ($dashboard_stats['pending_approvals'] > 0): ?>
                        <div class="notification-badge"><?php echo $dashboard_stats['pending_approvals']; ?></div>
                    <?php endif; ?>
                </div>
                <div class="stat-value"><?php echo $dashboard_stats['pending_approvals']; ?></div>
                <div class="stat-label">Pending Approvals</div>
            </div>

            <!-- Notifications -->
            <div class="stat-card fade-in" style="animation-delay: 0.6s;">
                <div class="stat-icon notifications">
                    <i class="fas fa-bell"></i>
                    <?php if ($dashboard_stats['unread_notifications'] > 0): ?>
                        <div class="notification-badge pulse"><?php echo $dashboard_stats['unread_notifications']; ?></div>
                    <?php endif; ?>
                </div>
                <div class="stat-value"><?php echo $dashboard_stats['unread_notifications']; ?></div>
                <div class="stat-label">Unread Notifications</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions fade-in" style="animation-delay: 0.7s;">
            <h2 class="section-title">Quick Actions</h2>
            <div class="action-grid">
                <a href="<?php echo $base_url; ?>properties.php?action=add" class="action-card">
                    <div class="action-icon add">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h3 class="action-title">Add New Property</h3>
                    <p class="action-desc">List your property for investment opportunities</p>
                </a>
                <a href="<?php echo $base_url; ?>documents.php" class="action-card">
                    <div class="action-icon upload">
                        <i class="fas fa-upload"></i>
                    </div>
                    <h3 class="action-title">Upload Documents</h3>
                    <p class="action-desc">Submit required documents for verification</p>
                </a>
                <a href="<?php echo $base_url; ?>payments.php" class="action-card">
                    <div class="action-icon payment">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="action-title">Make Payment</h3>
                    <p class="action-desc">Complete your pending payments securely</p>
                </a>
                <a href="<?php echo $base_url; ?>support.php" class="action-card">
                    <div class="action-icon support">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="action-title">Get Support</h3>
                    <p class="action-desc">Contact our support team for assistance</p>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity fade-in" style="animation-delay: 0.8s;">
            <h2 class="section-title">Recent Activity</h2>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Dashboard Access</div>
                        <div class="activity-time">Just now</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Portfolio Updated</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">New Notification</div>
                        <div class="activity-time">1 day ago</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logout Section -->
        <div class="logout-section fade-in" style="animation-delay: 0.9s;">
            <a href="customer_login.php?logout=1" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Logout Securely
            </a>
        </div>
    </div>

    <!-- Enhanced JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Show loading spinner on page load
        $(document).ready(function() {
            $('#loadingSpinner').fadeOut(500);

            // Add click effects to action cards
            $('.action-card').click(function(e) {
                const ripple = $('<div class="ripple"></div>');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.css({
                    width: size,
                    height: size,
                    left: x,
                    top: y,
                    position: 'absolute',
                    borderRadius: '50%',
                    background: 'rgba(255, 255, 255, 0.6)',
                    transform: 'scale(0)',
                    animation: 'ripple 0.6s linear',
                    pointerEvents: 'none'
                });

                $(this).append(ripple);

                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });

            // Add ripple animation CSS
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);

            // Enhanced hover effects for stat cards
            $('.stat-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-10px) scale(1.02)');
                },
                function() {
                    $(this).css('transform', 'translateY(0) scale(1)');
                }
            );

            // Add floating animation to elements
            $('.floating-element').each(function(index) {
                $(this).css('animation-delay', index * 2 + 's');
            });

            // Add counter animation for numbers
            $('.stat-value').each(function() {
                const $this = $(this);
                const countTo = parseInt($this.text().replace(/[^\d]/g, '')) || 0;

                if (countTo > 0) {
                    $({countNum: 0}).animate({
                        countNum: countTo
                    }, {
                        duration: 2000,
                        easing: 'swing',
                        step: function() {
                            $this.text(Math.floor(this.countNum));
                        },
                        complete: function() {
                            $this.text(countTo);
                        }
                    });
                }
            });

            // Add typing effect to greeting
            const greetingText = "Welcome back, <?php echo htmlspecialchars($customer_name); ?>! 👋";
            const greetingElement = $('.greeting h1');
            greetingElement.text('');

            let i = 0;
            const typeWriter = () => {
                if (i < greetingText.length) {
                    greetingElement.text(greetingElement.text() + greetingText.charAt(i));
                    i++;
                    setTimeout(typeWriter, 100);
                }
            };

            setTimeout(typeWriter, 1000);
        });

        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add intersection observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stat-card, .action-card, .activity-item').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>
