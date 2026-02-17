<?php
/**
 * Advanced CRM Dashboard - Complete Customer Relationship Management
 * Integrates all CRM features with modern UI and analytics
 */

require_once __DIR__ . '/core/init.php';

// Check if user is admin or has CRM access
if (!isAuthenticated()) {
    header("Location: login.php");
    exit();
}

if (!isAdmin() && !isSuperAdmin()) {
    $_SESSION['error_message'] = "You don't have permission to access CRM dashboard.";
    header("Location: dashboard.php");
    exit();
}

// Get dashboard data
$crm_stats = getCRMStats();
$recent_activities = getRecentCRMActivities();
$lead_pipeline = getLeadPipeline();
$sales_forecast = getSalesForecast();
$customer_insights = getCustomerInsights();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Security validation failed. Please try again.";
        header("Location: advanced_crm_dashboard.php");
        exit();
    }

    if (isset($_POST['bulk_action'])) {
        $result = handleBulkAction($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
        header("Location: advanced_crm_dashboard.php");
        exit();
    }
}

function getCRMStats() {
    $db = \App\Core\App::database();
    $stats = [];

    // Total leads
    $row = $db->fetchOne("SELECT COUNT(*) as total FROM leads");
    $stats['total_leads'] = $row ? $row['total'] : 0;

    // New leads this month
    $row = $db->fetchOne("SELECT COUNT(*) as new_this_month FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $stats['new_leads_month'] = $row ? $row['new_this_month'] : 0;

    // Converted leads
    $row = $db->fetchOne("SELECT COUNT(*) as converted FROM leads WHERE status = 'converted'");
    $stats['converted_leads'] = $row ? $row['converted'] : 0;

    // Active customers
    $row = $db->fetchOne("SELECT COUNT(*) as active FROM customers WHERE status = 'active'");
    $stats['active_customers'] = $row ? $row['active'] : 0;

    // Monthly revenue
    $row = $db->fetchOne("SELECT SUM(amount) as monthly_revenue FROM payments WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH) AND status = 'completed'");
    $stats['monthly_revenue'] = $row ? $row['monthly_revenue'] : 0;

    // Average deal size
    $row = $db->fetchOne("SELECT AVG(amount) as avg_deal FROM payments WHERE status = 'completed'");
    $stats['avg_deal_size'] = $row ? $row['avg_deal'] : 0;

    // Conversion rate
    if ($stats['total_leads'] > 0) {
        $stats['conversion_rate'] = round(($stats['converted_leads'] / $stats['total_leads']) * 100, 2);
    } else {
        $stats['conversion_rate'] = 0;
    }

    return $stats;
}

function getRecentCRMActivities() {
    $db = \App\Core\App::database();

    $query = "
        (SELECT 'lead_created' as activity_type, CONCAT('New lead: ', name) as description,
                created_at as activity_date, 'lead' as entity_type, id as entity_id
         FROM leads ORDER BY created_at DESC LIMIT 5)
        UNION ALL
        (SELECT 'lead_converted' as activity_type, CONCAT('Lead converted: ', name) as description,
                updated_at as activity_date, 'customer' as entity_type, id as entity_id
         FROM leads WHERE status = 'converted' ORDER BY updated_at DESC LIMIT 5)
        UNION ALL
        (SELECT 'payment_received' as activity_type, CONCAT('Payment received: ₹', amount) as description,
                payment_date as activity_date, 'payment' as entity_type, id as entity_id
         FROM payments WHERE status = 'completed' ORDER BY payment_date DESC LIMIT 5)
        ORDER BY activity_date DESC LIMIT 15";

    return $db->fetchAll($query);
}

function getLeadPipeline() {
    $db = \App\Core\App::database();

    $query = "
        SELECT status, COUNT(*) as count, SUM(estimated_value) as total_value
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

    return $db->fetchAll($query);
}

function getSalesForecast() {
    $db = \App\Core\App::database();

    // Simple forecasting based on historical data
    $query = "
        SELECT
            DATE_FORMAT(payment_date, '%Y-%m') as month,
            SUM(amount) as revenue,
            COUNT(*) as deals
        FROM payments
        WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        AND status = 'completed'
        GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
        ORDER BY month DESC";

    $historical = $db->fetchAll($query);

    // Calculate forecast for next 3 months
    $forecast = [];
    if (!empty($historical)) {
        $avg_revenue = array_sum(array_column($historical, 'revenue')) / count($historical);
        $avg_deals = array_sum(array_column($historical, 'deals')) / count($historical);

        for ($i = 1; $i <= 3; $i++) {
            $forecast_date = date('Y-m', strtotime("+$i months"));
            $forecast[] = [
                'month' => $forecast_date,
                'predicted_revenue' => round($avg_revenue * (1 + ($i * 0.1))), // 10% growth assumption
                'predicted_deals' => round($avg_deals * (1 + ($i * 0.05))),
                'confidence' => 85 - ($i * 5) // Decreasing confidence
            ];
        }
    }

    return [
        'historical' => $historical,
        'forecast' => $forecast
    ];
}

function getCustomerInsights() {
    $db = \App\Core\App::database();

    // Top customers by spending
    $query = "
        SELECT c.name, SUM(p.amount) as total_spent, COUNT(p.id) as transaction_count
        FROM customers c
        LEFT JOIN payments p ON c.id = p.customer_id AND p.status = 'completed'
        GROUP BY c.id, c.name
        ORDER BY total_spent DESC
        LIMIT 10";

    $top_customers = $db->fetchAll($query);

    // Customer acquisition by source
    $query = "
        SELECT source, COUNT(*) as count
        FROM leads
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
        GROUP BY source
        ORDER BY count DESC";

    $acquisition_sources = $db->fetchAll($query);

    return [
        'top_customers' => $top_customers,
        'acquisition_sources' => $acquisition_sources
    ];
}

function handleBulkAction($data) {
    $db = \App\Core\App::database();

    try {
        $action = $data['bulk_action'];
        $lead_ids = $data['lead_ids'] ?? [];

        if (empty($lead_ids)) {
            return ['success' => false, 'message' => 'No leads selected'];
        }

        switch ($action) {
            case 'delete':
                $placeholders = str_repeat('?,', count($lead_ids) - 1) . '?';
                $query = "DELETE FROM leads WHERE id IN ($placeholders)";
                $db->execute($query, $lead_ids);
                return ['success' => true, 'message' => count($lead_ids) . ' leads deleted successfully'];

            case 'assign':
                $assign_to = $data['assign_to'];
                $placeholders = str_repeat('?,', count($lead_ids) - 1) . '?';
                $query = "UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id IN ($placeholders)";
                $params = array_merge([$assign_to], $lead_ids);
                $db->execute($query, $params);
                return ['success' => true, 'message' => count($lead_ids) . ' leads assigned successfully'];

            case 'status_change':
                $new_status = $data['new_status'];
                $placeholders = str_repeat('?,', count($lead_ids) - 1) . '?';
                $query = "UPDATE leads SET status = ?, updated_at = NOW() WHERE id IN ($placeholders)";
                $params = array_merge([$new_status], $lead_ids);
                $db->execute($query, $params);
                return ['success' => true, 'message' => count($lead_ids) . ' leads status updated successfully'];

            default:
                return ['success' => false, 'message' => 'Invalid action'];
        }

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}
$page_title = "CRM Dashboard";
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<style>
    .stat-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 1rem;
    }
    .pipeline-stage {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        border: 2px solid #17a2b8;
        border-radius: 15px;
        padding: 1.5rem;
        margin: 0.5rem;
        text-align: center;
        transition: all 0.3s ease;
    }
    .activity-item {
        border-left: 4px solid #667eea;
        padding: 10px 15px;
        margin-bottom: 10px;
        background: #f8f9fa;
        border-radius: 0 10px 10px 0;
    }
    .activity-item.lead_created { border-left-color: #28a745; }
    .activity-item.lead_converted { border-left-color: #ffc107; }
    .activity-item.payment_received { border-left-color: #17a2b8; }
    .forecast-card {
        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        border: 2px solid #ffc107;
        border-radius: 15px;
        padding: 1rem;
        margin: 0.5rem 0;
    }
    .customer-card {
        border: none;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #e8f5e8, #d4edda);
        transition: all 0.3s ease;
    }
    .chart-container {
        position: relative;
        height: 300px;
        margin: 1rem 0;
    }
    .crm-module {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin: 1rem 0;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #667eea;
    }
    .module-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 1rem;
    }
    .bg-gradient-success { background: linear-gradient(135deg, #28a745, #218838); }
    .bg-gradient-primary { background: linear-gradient(135deg, #007bff, #0069d9); }
    .bg-gradient-info { background: linear-gradient(135deg, #17a2b8, #138496); }
    .bg-gradient-warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
    .bg-gradient-danger { background: linear-gradient(135deg, #dc3545, #c82333); }
    .bg-gradient-secondary { background: linear-gradient(135deg, #6c757d, #5a6268); }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo $page_title; ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">CRM Dashboard</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <button class="btn btn-primary" onclick="exportCRMData()">
                        <i class="fa fa-download mr-1"></i> Export Data
                    </button>
                    <button class="btn btn-success" onclick="showQuickLead()">
                        <i class="fa fa-plus mr-1"></i> Add Lead
                    </button>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo h($_SESSION['success_message']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['success_message']); endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo h($_SESSION['error_message']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['error_message']); endif; ?>

        <!-- CRM Statistics -->
        <div class="row">
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-gradient-success text-white mx-auto">
                            <i class="fa fa-user-plus"></i>
                        </div>
                        <h5 class="text-success"><?php echo number_format($crm_stats['total_leads']); ?></h5>
                        <p class="text-muted mb-0">Total Leads</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-gradient-primary text-white mx-auto">
                            <i class="fa fa-line-chart"></i>
                        </div>
                        <h5 class="text-primary"><?php echo $crm_stats['conversion_rate']; ?>%</h5>
                        <p class="text-muted mb-0">Conversion Rate</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-gradient-info text-white mx-auto">
                            <i class="fa fa-users"></i>
                        </div>
                        <h5 class="text-info"><?php echo number_format($crm_stats['active_customers']); ?></h5>
                        <p class="text-muted mb-0">Active Customers</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-gradient-warning text-white mx-auto">
                            <i class="fa fa-rupee"></i>
                        </div>
                        <h5 class="text-warning">₹<?php echo number_format($crm_stats['monthly_revenue'], 0); ?></h5>
                        <p class="text-muted mb-0">Monthly Revenue</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-gradient-danger text-white mx-auto">
                            <i class="fa fa-handshake-o"></i>
                        </div>
                        <h5 class="text-danger"><?php echo number_format($crm_stats['converted_leads']); ?></h5>
                        <p class="text-muted mb-0">Converted Leads</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-gradient-secondary text-white mx-auto">
                            <i class="fa fa-money"></i>
                        </div>
                        <h5 class="text-secondary">₹<?php echo number_format($crm_stats['avg_deal_size'], 0); ?></h5>
                        <p class="text-muted mb-0">Avg Deal Size</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="crm-module">
                    <h5 class="mb-4"><i class="fa fa-filter text-primary mr-2"></i>Lead Pipeline</h5>
                    <div class="row">
                        <?php foreach ($lead_pipeline as $stage): ?>
                        <div class="col-md-3 mb-3">
                            <div class="pipeline-stage">
                                <h6><?php echo h(ucfirst(str_replace('_', ' ', $stage['status']))); ?></h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-primary"><?php echo $stage['count']; ?> Leads</span>
                                    <?php if ($stage['total_value']): ?>
                                    <small class="text-muted">₹<?php echo number_format($stage['total_value']/100000, 1); ?>L</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="chart-container">
                        <canvas id="pipelineChart"></canvas>
                    </div>
                </div>

                <div class="crm-module">
                    <h5 class="mb-4"><i class="fa fa-line-chart text-success mr-2"></i>Sales Forecast</h5>
                    <div class="row">
                        <?php foreach ($sales_forecast['forecast'] as $forecast): ?>
                        <div class="col-md-4 mb-3">
                            <div class="forecast-card">
                                <h6><?php echo date('M Y', strtotime($forecast['month'])); ?></h6>
                                <div class="d-flex justify-content-between">
                                    <span>Revenue:</span>
                                    <strong>₹<?php echo number_format($forecast['predicted_revenue']/100000, 1); ?>L</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Deals:</span>
                                    <strong><?php echo $forecast['predicted_deals']; ?></strong>
                                </div>
                                <div class="mt-2 text-right">
                                    <small class="text-muted">Confidence: <?php echo $forecast['confidence']; ?>%</small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="chart-container">
                        <canvas id="forecastChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="crm-module mb-4">
                    <h6 class="mb-3"><i class="fa fa-clock-o text-info mr-2"></i>Recent Activities</h6>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item <?php echo $activity['activity_type']; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?php echo h($activity['description']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo h(ucfirst(str_replace('_', ' ', $activity['entity_type']))); ?></small>
                                </div>
                                <small class="text-muted"><?php echo date('M d, H:i', strtotime($activity['activity_date'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="crm-module mb-4">
                    <h6 class="mb-3"><i class="fa fa-star text-warning mr-2"></i>Top Customers</h6>
                    <?php foreach (array_slice($customer_insights['top_customers'], 0, 5) as $customer): ?>
                    <div class="customer-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo h($customer['name']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo $customer['transaction_count']; ?> transactions</small>
                            </div>
                            <div class="text-right">
                                <strong class="text-success">₹<?php echo number_format($customer['total_spent']); ?></strong>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="crm-module">
                    <h6 class="mb-3"><i class="fa fa-th-large text-primary mr-2"></i>CRM Modules</h6>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="text-center">
                                <div class="module-icon bg-primary text-white mx-auto">
                                    <i class="fa fa-user-plus"></i>
                                </div>
                                <a href="leads.php" class="btn btn-sm btn-outline-primary">Leads</a>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-center">
                                <div class="module-icon bg-success text-white mx-auto">
                                    <i class="fa fa-users"></i>
                                </div>
                                <a href="customers.php" class="btn btn-sm btn-outline-success">Customers</a>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-center">
                                <div class="module-icon bg-info text-white mx-auto">
                                    <i class="fa fa-handshake-o"></i>
                                </div>
                                <a href="opportunities.php" class="btn btn-sm btn-outline-info">Deals</a>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-center">
                                <div class="module-icon bg-warning text-white mx-auto">
                                    <i class="fa fa-bar-chart"></i>
                                </div>
                                <a href="reports.php" class="btn btn-sm btn-outline-warning">Reports</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                                    <h5 class="text-secondary">₹<?php echo number_format($crm_stats['avg_deal_size'], 0); ?></h5>
                                    <p class="text-muted mb-0">Avg Deal Size</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Row -->
                    <div class="row m-4">
                        <!-- Lead Pipeline -->
                        <div class="col-lg-8 mb-4">
                            <div class="crm-module">
                                <h5 class="mb-4">
                                    <i class="fas fa-filter text-primary me-2"></i>Lead Pipeline
                                </h5>

                                <div class="row">
                                    <?php foreach ($lead_pipeline as $stage): ?>
                                    <div class="col-md-3 mb-3">
                                        <div class="pipeline-stage">
                                            <h6><?php echo h(ucfirst(str_replace('_', ' ', $stage['status']))); ?></h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-primary fs-6"><?php echo $stage['count']; ?> Leads</span>
                                                <?php if ($stage['total_value']): ?>
                                                <small class="text-muted">₹<?php echo number_format($stage['total_value']/100000, 1); ?>L</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Pipeline Chart -->
                                <div class="chart-container">
                                    <canvas id="pipelineChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activities & Top Customers -->
                        <div class="col-lg-4">
                            <!-- Recent Activities -->
                            <div class="crm-module mb-4">
                                <h6 class="mb-3">
                                    <i class="fas fa-clock text-info me-2"></i>Recent Activities
                                </h6>

                                <div style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach ($recent_activities as $activity): ?>
                                    <div class="activity-item <?php echo $activity['activity_type']; ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong><?php echo h($activity['description']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo h(ucfirst(str_replace('_', ' ', $activity['entity_type']))); ?></small>
                                            </div>
                                            <small class="text-muted"><?php echo date('M d, H:i', strtotime($activity['activity_date'])); ?></small>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Top Customers -->
                            <div class="crm-module">
                                <h6 class="mb-3">
                                    <i class="fas fa-star text-warning me-2"></i>Top Customers
                                </h6>

                                <?php foreach (array_slice($customer_insights['top_customers'], 0, 5) as $customer): ?>
                                <div class="customer-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo h($customer['name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo $customer['transaction_count']; ?> transactions</small>
                                        </div>
                                        <div class="text-end">
                                            <strong class="text-success">₹<?php echo number_format($customer['total_spent']); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Forecast & Quick Actions -->
                    <div class="row m-4">
                        <!-- Sales Forecast -->
                        <div class="col-lg-8 mb-4">
                            <div class="crm-module">
                                <h5 class="mb-4">
                                    <i class="fas fa-chart-line text-success me-2"></i>Sales Forecast
                                </h5>

                                <div class="row">
                                    <?php foreach ($sales_forecast['forecast'] as $forecast): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="forecast-card">
                                            <h6><?php echo date('M Y', strtotime($forecast['month'])); ?></h6>
                                            <div class="d-flex justify-content-between">
                                                <span>Revenue:</span>
                                                <strong>₹<?php echo number_format($forecast['predicted_revenue']/100000, 1); ?>L</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Deals:</span>
                                                <strong><?php echo $forecast['predicted_deals']; ?></strong>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">Confidence: <?php echo $forecast['confidence']; ?>%</small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Forecast Chart -->
                                <div class="chart-container">
                                    <canvas id="forecastChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions & CRM Modules -->
                        <div class="col-lg-4">
                            <!-- Quick Actions -->
                            <div class="crm-module mb-4">
                                <h6 class="mb-3">
                                    <i class="fas fa-bolt text-warning me-2"></i>Quick Actions
                                </h6>

                                <div class="d-grid gap-2">
                                    <button class="btn btn-success quick-action-btn" onclick="showQuickLead()">
                                        <i class="fas fa-plus me-2"></i>Add New Lead
                                    </button>
                                    <button class="btn btn-primary quick-action-btn" onclick="showBulkActions()">
                                        <i class="fas fa-list me-2"></i>Bulk Actions
                                    </button>
                                    <button class="btn btn-info quick-action-btn" onclick="showReports()">
                                        <i class="fas fa-chart-bar me-2"></i>View Reports
                                    </button>
                                    <button class="btn btn-warning quick-action-btn" onclick="showSettings()">
                                        <i class="fas fa-cog me-2"></i>CRM Settings
                                    </button>
                                </div>
                            </div>

                            <!-- CRM Modules -->
                            <div class="crm-module">
                                <h6 class="mb-3">
                                    <i class="fas fa-th-large text-primary me-2"></i>CRM Modules
                                </h6>

                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <div class="text-center">
                                            <div class="module-icon bg-primary text-white mx-auto">
                                                <i class="fas fa-user-plus"></i>
                                            </div>
                                            <a href="leads.php" class="btn btn-sm btn-outline-primary">Leads</a>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="text-center">
                                            <div class="module-icon bg-success text-white mx-auto">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <a href="customers.php" class="btn btn-sm btn-outline-success">Customers</a>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="text-center">
                                            <div class="module-icon bg-info text-white mx-auto">
                                                <i class="fas fa-handshake"></i>
                                            </div>
                                            <a href="opportunities.php" class="btn btn-sm btn-outline-info">Opportunities</a>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="text-center">
                                            <div class="module-icon bg-warning text-white mx-auto">
                                                <i class="fas fa-chart-bar"></i>
                                            </div>
                                            <a href="reports.php" class="btn btn-sm btn-outline-warning">Reports</a>
                                        </div>
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
        // Pipeline Chart
        const pipelineCtx = document.getElementById('pipelineChart').getContext('2d');
        new Chart(pipelineCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', array_map(function($stage) {
                    return "'".ucfirst(str_replace('_', ' ', $stage['status']))."'";
                }, $lead_pipeline)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($lead_pipeline, 'count')); ?>],
                    backgroundColor: [
                        '#28a745', '#007bff', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Forecast Chart
        const forecastCtx = document.getElementById('forecastChart').getContext('2d');
        new Chart(forecastCtx, {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function($forecast) {
                    return "'".date('M', strtotime($forecast['month']))."'";
                }, $sales_forecast['forecast'])); ?>],
                datasets: [{
                    label: 'Predicted Revenue (₹ Lakhs)',
                    data: [<?php echo implode(',', array_map(function($forecast) {
                        return round($forecast['predicted_revenue']/100000, 1);
                    }, $sales_forecast['forecast'])); ?>],
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

        function showQuickLead() {
            window.location.href = 'leads.php?action=create';
        }

        function showBulkActions() {
            // Show bulk actions modal
            alert('Bulk actions modal will be implemented');
        }

        function showReports() {
            window.location.href = 'reports.php';
        }

        function showSettings() {
            window.location.href = 'settings.php';
        }

        function exportCRMData() {
            // Export CRM data
            window.location.href = 'export_crm_data.php';
        }
    </script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
