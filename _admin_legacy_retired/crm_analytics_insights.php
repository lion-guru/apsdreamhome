<?php

/**
 * CRM Customer Insights & Analytics
 * Advanced customer relationship management analytics
 */

require_once 'includes/config.php';
require_once 'includes/associate_permissions.php';

// Check if user is admin or has CRM access
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$admin_role = $_SESSION['role'] ?? 'admin';
$crm_roles = ['admin', 'company_owner', 'crm_manager', 'sales_manager', 'marketing_manager'];
if (!in_array($admin_role, $crm_roles)) {
    $_SESSION['error_message'] = "You don't have permission to access CRM analytics.";
    header("Location: index.php");
    exit();
}

// Get analytics data
$customer_analytics = getCustomerAnalytics();
$lead_analytics = getLeadAnalytics();
$sales_analytics = getSalesAnalytics();
$communication_analytics = getCommunicationAnalytics();

// Handle export requests
if (isset($_GET['export'])) {
    exportAnalyticsData($_GET['export']);
}

function getCustomerAnalytics()
{
    $db = \App\Core\App::database();
    $analytics = [];

    // Customer acquisition trends
    $query = "
        SELECT
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as new_customers,
            SUM(CASE WHEN source = 'lead_conversion' THEN 1 ELSE 0 END) as from_leads,
            SUM(CASE WHEN source = 'direct' THEN 1 ELSE 0 END) as direct_signups
        FROM customers
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month";

    try {
        $analytics['acquisition_trends'] = $db->fetch($query);
    } catch (Exception $e) {
        error_log("Error in acquisition_trends: " . $e->getMessage());
        $analytics['acquisition_trends'] = [];
    }

    // Customer lifetime value analysis
    $query = "
        SELECT
            c.id, c.name, c.email,
            COUNT(p.id) as total_purchases,
            SUM(p.amount) as total_spent,
            AVG(p.amount) as avg_order_value,
            MAX(p.payment_date) as last_purchase,
            DATEDIFF(NOW(), MIN(p.payment_date)) as customer_age_days
        FROM customers c
        LEFT JOIN payments p ON c.id = p.customer_id AND p.status = 'completed'
        GROUP BY c.id, c.name, c.email
        ORDER BY total_spent DESC
        LIMIT 100";

    try {
        $analytics['lifetime_value'] = $db->fetch($query);
    } catch (Exception $e) {
        error_log("Error in lifetime_value: " . $e->getMessage());
        $analytics['lifetime_value'] = [];
    }

    // Customer segmentation
    $query = "
        SELECT
            CASE
                WHEN total_spent >= 1000000 THEN 'High Value'
                WHEN total_spent >= 500000 THEN 'Medium Value'
                WHEN total_spent >= 100000 THEN 'Low Value'
                ELSE 'New Customer'
            END as segment,
            COUNT(*) as customer_count,
            AVG(total_spent) as avg_value,
            SUM(total_spent) as total_segment_value
        FROM (
            SELECT c.id, SUM(p.amount) as total_spent
            FROM customers c
            LEFT JOIN payments p ON c.id = p.customer_id AND p.status = 'completed'
            GROUP BY c.id
        ) segment_data
        GROUP BY
            CASE
                WHEN total_spent >= 1000000 THEN 'High Value'
                WHEN total_spent >= 500000 THEN 'Medium Value'
                WHEN total_spent >= 100000 THEN 'Low Value'
                ELSE 'New Customer'
            END";

    try {
        $analytics['segmentation'] = $db->fetch($query);
    } catch (Exception $e) {
        error_log("Error in segmentation: " . $e->getMessage());
        $analytics['segmentation'] = [];
    }

    return $analytics;
}

function getLeadAnalytics()
{
    $db = \App\Core\App::database();
    $analytics = [];

    // Lead conversion funnel
    $query = "
        SELECT
            status,
            COUNT(*) as count,
            SUM(estimated_value) as total_value
        FROM leads
        GROUP BY status
        ORDER BY
            CASE status
                WHEN 'new' THEN 1
                WHEN 'contacted' THEN 2
                WHEN 'qualified' THEN 3
                WHEN 'proposal' THEN 4
                WHEN 'negotiation' THEN 5
                WHEN 'closed_won' THEN 6
                WHEN 'closed_lost' THEN 7
                ELSE 8
            END";

    try {
        $analytics['conversion_funnel'] = $db->fetch($query);
    } catch (Exception $e) {
        error_log("Error in conversion_funnel: " . $e->getMessage());
        $analytics['conversion_funnel'] = [];
    }

    // Lead source performance
    $query = "
        SELECT
            source,
            COUNT(*) as lead_count,
            SUM(CASE WHEN status = 'closed_won' THEN 1 ELSE 0 END) as converted_count,
            AVG(estimated_value) as avg_value
        FROM leads
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY source
        ORDER BY lead_count DESC";

    try {
        $analytics['source_performance'] = $db->fetch($query);
    } catch (Exception $e) {
        error_log("Error in source_performance: " . $e->getMessage());
        $analytics['source_performance'] = [];
    }

    // Lead response time analysis
    $query = "
        SELECT
            AVG(TIMESTAMPDIFF(HOUR, created_at, first_contact_date)) as avg_response_time_hours,
            COUNT(CASE WHEN TIMESTAMPDIFF(HOUR, created_at, first_contact_date) <= 1 THEN 1 END) as responded_within_hour,
            COUNT(*) as total_leads_with_contact
        FROM leads
        WHERE first_contact_date IS NOT NULL
        AND created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";

    try {
        $analytics['response_time'] = $db->fetch($query, [], false);
    } catch (Exception $e) {
        error_log("Error in response_time: " . $e->getMessage());
        $analytics['response_time'] = [];
    }

    return $analytics;
}

function getSalesAnalytics()
{
    $db = \App\Core\App::database();
    $analytics = [];

    // Monthly sales trends
    $query = "
        SELECT
            DATE_FORMAT(payment_date, '%Y-%m') as month,
            SUM(amount) as revenue,
            COUNT(*) as deal_count,
            AVG(amount) as avg_deal_size
        FROM payments
        WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        AND status = 'completed'
        GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
        ORDER BY month";

    try {
        $analytics['monthly_trends'] = $db->fetch($query);
    } catch (Exception $e) {
        error_log("Error in monthly_trends: " . $e->getMessage());
        $analytics['monthly_trends'] = [];
    }

    // Sales by property type
    $query = "
        SELECT
            p.property_type,
            COUNT(pay.id) as sales_count,
            SUM(pay.amount) as total_revenue,
            AVG(pay.amount) as avg_sale_price
        FROM properties p
        JOIN payments pay ON p.id = pay.property_id AND pay.status = 'completed'
        GROUP BY p.property_type
        ORDER BY total_revenue DESC";

    try {
        $analytics['property_type_sales'] = $db->fetch($query);
    } catch (Exception $e) {
        error_log("Error in property_type_sales: " . $e->getMessage());
        $analytics['property_type_sales'] = [];
    }

    // Sales team performance
    $query = "
        SELECT
            u.name as sales_person,
            COUNT(p.id) as deals_closed,
            SUM(p.amount) as total_revenue,
            AVG(p.amount) as avg_deal_size,
            MAX(p.payment_date) as last_sale_date
        FROM users u
        LEFT JOIN leads l ON u.id = l.assigned_to
        LEFT JOIN payments p ON l.id = p.lead_id AND p.status = 'completed'
        WHERE u.role IN ('sales_manager', 'sales_executive', 'associate')
        GROUP BY u.id, u.name
        ORDER BY total_revenue DESC";

    try {
        $analytics['sales_team_performance'] = $db->fetch($query);
    } catch (Exception $e) {
        error_log("Error in sales_team_performance: " . $e->getMessage());
        $analytics['sales_team_performance'] = [];
    }

    return $analytics;
}

function getCommunicationAnalytics()
{
    $db = \App\Core\App::database();
    $analytics = [];

    // Email campaign performance
    $query = "
        SELECT
            campaign_name,
            COUNT(*) as emails_sent,
            SUM(CASE WHEN status = 'opened' THEN 1 ELSE 0 END) as emails_opened,
            SUM(CASE WHEN status = 'clicked' THEN 1 ELSE 0 END) as emails_clicked,
            SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as conversions
        FROM email_campaigns
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
        GROUP BY campaign_name
        ORDER BY emails_sent DESC";

    try {
        $analytics['email_campaigns'] = $db->fetch($query);
    } catch (Exception $e) {
        error_log("Error in email_campaigns: " . $e->getMessage());
        $analytics['email_campaigns'] = [];
    }

    // SMS/WhatsApp performance
    $query = "
        SELECT
            communication_type,
            COUNT(*) as messages_sent,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
            SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
            AVG(response_time_minutes) as avg_response_time
        FROM customer_communications
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        GROUP BY communication_type";

    try {
        $analytics['communication_channels'] = $db->fetch($query);
    } catch (Exception $e) {
        error_log("Error in communication_channels: " . $e->getMessage());
        $analytics['communication_channels'] = [];
    }

    return $analytics;
}

function exportAnalyticsData($type)
{
    // Export functionality
    $filename = "crm_analytics_" . $type . "_" . date('Y-m-d') . ".csv";

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    switch ($type) {
        case 'customers':
            fputcsv($output, ['Customer Name', 'Email', 'Total Spent', 'Purchase Count', 'Last Purchase']);
            // Add customer data
            break;
        case 'leads':
            fputcsv($output, ['Lead Name', 'Email', 'Status', 'Source', 'Estimated Value']);
            // Add lead data
            break;
        case 'sales':
            fputcsv($output, ['Month', 'Revenue', 'Deal Count', 'Avg Deal Size']);
            // Add sales data
            break;
    }

    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Analytics & Insights - APS Dream Home</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .analytics-dashboard {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
            overflow: hidden;
        }

        .analytics-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .insight-card {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border: 2px solid var(--info-color);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1rem 0;
            transition: all 0.3s ease;
        }

        .insight-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
        }

        .metric-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .segment-card {
            border: none;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 0.5rem;
            color: white;
            text-align: center;
        }

        .segment-high {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .segment-medium {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .segment-low {
            background: linear-gradient(135deg, #17a2b8, #6f42c1);
        }

        .segment-new {
            background: linear-gradient(135deg, #6c757d, #495057);
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }

        .export-btn {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin: 0.25rem;
        }

        .export-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .analytics-nav {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 1rem;
            margin: 1rem 0;
        }

        .nav-tab {
            border: none;
            background: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            margin: 0 0.25rem;
            transition: all 0.3s ease;
            color: #6c757d;
        }

        .nav-tab.active {
            background: var(--primary-color);
            color: white;
        }

        .nav-tab:hover {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="advanced_crm_dashboard.php">
                <i class="fas fa-chart-bar me-2"></i>CRM Analytics
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="advanced_crm_dashboard.php"><i class="fas fa-home me-2"></i>Main Dashboard</a></li>
                        <li><a class="dropdown-item" href="leads.php"><i class="fas fa-user-plus me-2"></i>Lead Management</a></li>
                        <li><a class="dropdown-item" href="customers.php"><i class="fas fa-users me-2"></i>Customer Management</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="analytics-dashboard">
                    <!-- Header -->
                    <div class="analytics-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="mb-2">
                                    <i class="fas fa-analytics me-2"></i>
                                    CRM Analytics & Insights
                                </h1>
                                <p class="mb-0">Deep insights into customer behavior and business performance</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="btn-group">
                                    <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-download me-2"></i>Export Data
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="?export=customers"><i class="fas fa-users me-2"></i>Customer Data</a></li>
                                        <li><a class="dropdown-item" href="?export=leads"><i class="fas fa-user-plus me-2"></i>Lead Data</a></li>
                                        <li><a class="dropdown-item" href="?export=sales"><i class="fas fa-chart-line me-2"></i>Sales Data</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics Navigation -->
                    <div class="analytics-nav">
                        <div class="row">
                            <div class="col-12">
                                <div class="btn-group w-100" role="group">
                                    <button class="nav-tab active" onclick="showSection('overview')">
                                        <i class="fas fa-home me-2"></i>Overview
                                    </button>
                                    <button class="nav-tab" onclick="showSection('customers')">
                                        <i class="fas fa-users me-2"></i>Customers
                                    </button>
                                    <button class="nav-tab" onclick="showSection('leads')">
                                        <i class="fas fa-user-plus me-2"></i>Leads
                                    </button>
                                    <button class="nav-tab" onclick="showSection('sales')">
                                        <i class="fas fa-chart-line me-2"></i>Sales
                                    </button>
                                    <button class="nav-tab" onclick="showSection('communication')">
                                        <i class="fas fa-comments me-2"></i>Communication
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Overview Section -->
                    <div id="overview-section" class="p-4">
                        <div class="row">
                            <!-- Customer Acquisition Trends -->
                            <div class="col-lg-6 mb-4">
                                <div class="insight-card">
                                    <h5 class="mb-3">
                                        <i class="fas fa-chart-line text-primary me-2"></i>Customer Acquisition Trends
                                    </h5>
                                    <div class="chart-container">
                                        <canvas id="acquisitionChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- Lead Conversion Funnel -->
                            <div class="col-lg-6 mb-4">
                                <div class="insight-card">
                                    <h5 class="mb-3">
                                        <i class="fas fa-filter text-success me-2"></i>Lead Conversion Funnel
                                    </h5>
                                    <div class="chart-container">
                                        <canvas id="conversionChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Segmentation -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="insight-card">
                                    <h5 class="mb-3">
                                        <i class="fas fa-pie-chart text-info me-2"></i>Customer Segmentation
                                    </h5>
                                    <div class="row">
                                        <?php foreach ($customer_analytics['segmentation'] as $segment): ?>
                                            <div class="col-md-3 mb-3">
                                                <div class="segment-card segment-<?php echo strtolower(str_replace(' ', '-', $segment['segment'])); ?>">
                                                    <h4><?php echo number_format($segment['customer_count']); ?></h4>
                                                    <p class="mb-1"><?php echo $segment['segment']; ?></p>
                                                    <small>Total Value: ₹<?php echo number_format($segment['total_segment_value'] / 100000, 1); ?>L</small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Sales Trends -->
                        <div class="row">
                            <div class="col-12">
                                <div class="insight-card">
                                    <h5 class="mb-3">
                                        <i class="fas fa-chart-area text-warning me-2"></i>Monthly Sales Trends
                                    </h5>
                                    <div class="chart-container">
                                        <canvas id="salesTrendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Analytics Section -->
                    <div id="customers-section" class="p-4" style="display: none;">
                        <h4 class="mb-4">
                            <i class="fas fa-users text-primary me-2"></i>Customer Analytics
                        </h4>

                        <!-- Top Customers -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="insight-card">
                                    <h5 class="mb-3">Top Customers by Lifetime Value</h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Customer</th>
                                                    <th>Total Spent</th>
                                                    <th>Purchases</th>
                                                    <th>Avg Order Value</th>
                                                    <th>Last Purchase</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($customer_analytics['lifetime_value'], 0, 10) as $customer): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($customer['name']); ?></strong>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($customer['email']); ?></small>
                                                        </td>
                                                        <td>₹<?php echo number_format($customer['total_spent']); ?></td>
                                                        <td><?php echo $customer['total_purchases']; ?></td>
                                                        <td>₹<?php echo number_format($customer['avg_order_value']); ?></td>
                                                        <td><?php echo $customer['last_purchase'] ? date('M d, Y', strtotime($customer['last_purchase'])) : 'Never'; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lead Analytics Section -->
                    <div id="leads-section" class="p-4" style="display: none;">
                        <h4 class="mb-4">
                            <i class="fas fa-user-plus text-success me-2"></i>Lead Analytics
                        </h4>

                        <!-- Lead Source Performance -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="insight-card">
                                    <h5 class="mb-3">Lead Source Performance</h5>
                                    <div class="chart-container">
                                        <canvas id="sourceChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lead Response Time -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="metric-card text-center">
                                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                    <h4><?php echo round($lead_analytics['response_time']['avg_response_time_hours'] ?? 0, 1); ?> hrs</h4>
                                    <p class="text-muted mb-0">Avg Response Time</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="metric-card text-center">
                                    <i class="fas fa-bolt fa-2x text-success mb-2"></i>
                                    <h4><?php echo $lead_analytics['response_time']['responded_within_hour'] ?? 0; ?></h4>
                                    <p class="text-muted mb-0">Responded <1hr< /p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="metric-card text-center">
                                    <i class="fas fa-users fa-2x text-info mb-2"></i>
                                    <h4><?php echo $lead_analytics['response_time']['total_leads_with_contact'] ?? 0; ?></h4>
                                    <p class="text-muted mb-0">Total Contacted</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Analytics Section -->
                    <div id="sales-section" class="p-4" style="display: none;">
                        <h4 class="mb-4">
                            <i class="fas fa-chart-line text-warning me-2"></i>Sales Analytics
                        </h4>

                        <!-- Sales Team Performance -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="insight-card">
                                    <h5 class="mb-3">Sales Team Performance</h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Sales Person</th>
                                                    <th>Deals Closed</th>
                                                    <th>Total Revenue</th>
                                                    <th>Avg Deal Size</th>
                                                    <th>Last Sale</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($sales_analytics['sales_team_performance'], 0, 10) as $sales_person): ?>
                                                    <tr>
                                                        <td><strong><?php echo htmlspecialchars($sales_person['sales_person']); ?></strong></td>
                                                        <td><?php echo $sales_person['deals_closed']; ?></td>
                                                        <td>₹<?php echo number_format($sales_person['total_revenue']); ?></td>
                                                        <td>₹<?php echo number_format($sales_person['avg_deal_size']); ?></td>
                                                        <td><?php echo $sales_person['last_sale_date'] ? date('M d, Y', strtotime($sales_person['last_sale_date'])) : 'No sales'; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Communication Analytics Section -->
                    <div id="communication-section" class="p-4" style="display: none;">
                        <h4 class="mb-4">
                            <i class="fas fa-comments text-info me-2"></i>Communication Analytics
                        </h4>

                        <!-- Communication Channels Performance -->
                        <div class="row">
                            <div class="col-12">
                                <div class="insight-card">
                                    <h5 class="mb-3">Communication Channel Performance</h5>
                                    <div class="chart-container">
                                        <canvas id="communicationChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Email Campaign Performance -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="insight-card">
                                    <h5 class="mb-3">Email Campaign Performance</h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Campaign</th>
                                                    <th>Sent</th>
                                                    <th>Opened</th>
                                                    <th>Clicked</th>
                                                    <th>Converted</th>
                                                    <th>Open Rate</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($communication_analytics['email_campaigns'] as $campaign): ?>
                                                    <tr>
                                                        <td><strong><?php echo htmlspecialchars($campaign['campaign_name']); ?></strong></td>
                                                        <td><?php echo $campaign['emails_sent']; ?></td>
                                                        <td><?php echo $campaign['emails_opened']; ?></td>
                                                        <td><?php echo $campaign['emails_clicked']; ?></td>
                                                        <td><?php echo $campaign['conversions']; ?></td>
                                                        <td>
                                                            <?php
                                                            $openRate = $campaign['emails_sent'] > 0 ?
                                                                round(($campaign['emails_opened'] / $campaign['emails_sent']) * 100, 1) : 0;
                                                            ?>
                                                            <span class="badge bg-<?php echo $openRate >= 20 ? 'success' : ($openRate >= 10 ? 'warning' : 'danger'); ?>">
                                                                <?php echo $openRate; ?>%
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function showSection(section) {
            // Hide all sections
            document.querySelectorAll('[id$="-section"]').forEach(sec => {
                sec.style.display = 'none';
            });

            // Remove active class from all tabs
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected section
            document.getElementById(section + '-section').style.display = 'block';

            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Customer Acquisition Chart
        const acquisitionCtx = document.getElementById('acquisitionChart').getContext('2d');
        new Chart(acquisitionCtx, {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function ($trend) {
                                return "'" . date('M Y', strtotime($trend['month'] . '-01')) . "'";
                            }, $customer_analytics['acquisition_trends'])); ?>],
                datasets: [{
                    label: 'New Customers',
                    data: [<?php echo implode(',', array_column($customer_analytics['acquisition_trends'], 'new_customers')); ?>],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Lead Conversion Chart
        const conversionCtx = document.getElementById('conversionChart').getContext('2d');
        new Chart(conversionCtx, {
            type: 'funnel',
            data: {
                labels: [<?php echo implode(',', array_map(function ($stage) {
                                return "'" . ucfirst(str_replace('_', ' ', $stage['status'])) . "'";
                            }, $lead_analytics['conversion_funnel'])); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($lead_analytics['conversion_funnel'], 'count')); ?>],
                    backgroundColor: [
                        '#28a745', '#007bff', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Sales Trend Chart
        const salesCtx = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function ($trend) {
                                return "'" . date('M Y', strtotime($trend['month'] . '-01')) . "'";
                            }, $sales_analytics['monthly_trends'])); ?>],
                datasets: [{
                    label: 'Revenue (₹ Lakhs)',
                    data: [<?php echo implode(',', array_map(function ($trend) {
                                return round($trend['revenue'] / 100000, 1);
                            }, $sales_analytics['monthly_trends'])); ?>],
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: '#667eea',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Lead Source Chart
        const sourceCtx = document.getElementById('sourceChart').getContext('2d');
        new Chart(sourceCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', array_map(function ($source) {
                                return "'" . ucfirst($source['source']) . "'";
                            }, $lead_analytics['source_performance'])); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($lead_analytics['source_performance'], 'lead_count')); ?>],
                    backgroundColor: [
                        '#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Communication Chart
        const communicationCtx = document.getElementById('communicationChart').getContext('2d');
        new Chart(communicationCtx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function ($channel) {
                                return "'" . ucfirst($channel['communication_type']) . "'";
                            }, $communication_analytics['communication_channels'])); ?>],
                datasets: [{
                    label: 'Messages Sent',
                    data: [<?php echo implode(',', array_column($communication_analytics['communication_channels'], 'messages_sent')); ?>],
                    backgroundColor: 'rgba(23, 162, 184, 0.8)'
                }, {
                    label: 'Delivered',
                    data: [<?php echo implode(',', array_column($communication_analytics['communication_channels'], 'delivered')); ?>],
                    backgroundColor: 'rgba(40, 167, 69, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>