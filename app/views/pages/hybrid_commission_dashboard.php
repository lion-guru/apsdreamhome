<?php
/**
 * Hybrid Commission Dashboard
 * Comprehensive dashboard for both company and resell properties
 */

require_once 'includes/config.php';
require_once 'includes/associate_permissions.php';
require_once 'includes/hybrid_commission_system.php';

// Initialize database connection
$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

// Check if connection is successful
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

session_start();
if (!isset($_SESSION['associate_logged_in']) || $_SESSION['associate_logged_in'] !== true) {
    header("Location: associate_login.php");
    exit();
}

$associate_id = $_SESSION['associate_id'];
$associate_name = $_SESSION['associate_name'];

// Initialize hybrid commission system
$hybrid_system = new HybridRealEstateCommission($conn);

// Get commission summary with error handling
try {
    $commission_summary = $hybrid_system->getCommissionSummary($associate_id) ?: [];
    $monthly_summary = $hybrid_system->getCommissionSummary($associate_id, date('Y-m-01'), date('Y-m-t')) ?: [];
} catch (Exception $e) {
    $commission_summary = [];
    $monthly_summary = [];
}

$pending_commissions = getPendingCommissions($associate_id);
$recent_sales = getRecentSales($associate_id);

// Get property statistics
$property_stats = getPropertyStatistics();

// Helper functions
function getPendingCommissions($associate_id) {
    global $conn;

    try {
        $query = "SELECT
                    SUM(commission_amount) as total_pending,
                    COUNT(*) as total_count,
                    AVG(commission_amount) as avg_commission
                 FROM hybrid_commission_records
                 WHERE associate_id = ? AND payout_status = 'pending'";

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return ['total_pending' => 0, 'total_count' => 0, 'avg_commission' => 0];
        }

        $stmt->bind_param("i", $associate_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: ['total_pending' => 0, 'total_count' => 0, 'avg_commission' => 0];
    } catch (Exception $e) {
        return ['total_pending' => 0, 'total_count' => 0, 'avg_commission' => 0];
    }
}

function getRecentSales($associate_id) {
    global $conn;

    try {
        $query = "SELECT
                    hcr.*,
                    p.property_name,
                    p.property_type,
                    p.location,
                    c.first_name,
                    c.last_name
                 FROM hybrid_commission_records hcr
                 LEFT JOIN real_estate_properties p ON hcr.property_id = p.id
                 LEFT JOIN customers c ON hcr.customer_id = c.id
                 WHERE hcr.associate_id = ?
                 ORDER BY hcr.created_at DESC
                 LIMIT 10";

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $associate_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC) ?: [];
    } catch (Exception $e) {
        return [];
    }
}

function getPropertyStatistics() {
    global $conn;

    try {
        $query = "SELECT
                    property_type,
                    COUNT(*) as total_properties,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
                    AVG(total_value) as avg_value
                 FROM real_estate_properties
                 GROUP BY property_type";

        $result = $conn->query($query);

        if (!$result) {
            return [
                ['property_type' => 'company', 'total_properties' => 0, 'available' => 0, 'sold' => 0, 'avg_value' => 0],
                ['property_type' => 'resell', 'total_properties' => 0, 'available' => 0, 'sold' => 0, 'avg_value' => 0]
            ];
        }

        return $result->fetch_all(MYSQLI_ASSOC) ?: [
            ['property_type' => 'company', 'total_properties' => 0, 'available' => 0, 'sold' => 0, 'avg_value' => 0],
            ['property_type' => 'resell', 'total_properties' => 0, 'available' => 0, 'sold' => 0, 'avg_value' => 0]
        ];
    } catch (Exception $e) {
        return [
            ['property_type' => 'company', 'total_properties' => 0, 'available' => 0, 'sold' => 0, 'avg_value' => 0],
            ['property_type' => 'resell', 'total_properties' => 0, 'available' => 0, 'sold' => 0, 'avg_value' => 0]
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hybrid Commission Dashboard - APS Dream Homes</title>

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
            --dark-color: #343a40;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 20px 0;
            overflow: hidden;
        }

        .summary-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            margin: 1rem 0;
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .summary-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .summary-card p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1rem 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 4px solid var(--info-color);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .stats-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stats-card.company {
            border-left-color: var(--success-color);
        }

        .stats-card.company .icon {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            color: white;
        }

        .stats-card.resell {
            border-left-color: var(--warning-color);
        }

        .stats-card.resell .icon {
            background: linear-gradient(135deg, var(--warning-color), #fd7e14);
            color: white;
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: var(--dark-color);
        }

        .badge-company {
            background: linear-gradient(45deg, var(--success-color), #20c997);
            color: white;
        }

        .badge-resell {
            background: linear-gradient(45deg, var(--warning-color), #fd7e14);
            color: white;
        }

        .badge-pending {
            background: linear-gradient(45deg, var(--warning-color), #fd7e14);
            color: white;
        }

        .badge-paid {
            background: linear-gradient(45deg, var(--success-color), #20c997);
            color: white;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }

        .performance-indicator {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            text-align: center;
        }

        .excellent {
            background: linear-gradient(45deg, var(--success-color), #20c997);
            color: white;
        }

        .good {
            background: linear-gradient(45deg, var(--info-color), #17a2b8);
            color: white;
        }

        .average {
            background: linear-gradient(45deg, var(--warning-color), #fd7e14);
            color: white;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="associate_dashboard.php">
                <i class="fas fa-home me-2"></i>APS Dream Homes
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($associate_name); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="associate_dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a></li>
                        <li><a class="dropdown-item" href="development_cost_calculator.php">
                            <i class="fas fa-calculator me-2"></i>Cost Calculator
                        </a></li>
                        <li><a class="dropdown-item" href="property_management.php">
                            <i class="fas fa-building me-2"></i>Property Management
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="associate_logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="dashboard-container">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <h1 class="mb-3">
                                <i class="fas fa-chart-line text-primary me-2"></i>Hybrid Commission Dashboard
                            </h1>
                            <p class="text-muted">
                                Complete overview of your company and resell property commissions
                            </p>
                        </div>

                        <!-- Key Metrics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="summary-card">
                                    <h3>₹<?php echo number_format($commission_summary[0]['total_commission'] ?? 0); ?></h3>
                                    <p>Total Commission Earned</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card">
                                    <h3>₹<?php echo number_format($pending_commissions['total_pending'] ?? 0); ?></h3>
                                    <p>Pending Commission</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card">
                                    <h3><?php echo $commission_summary[0]['total_sales'] ?? 0; ?></h3>
                                    <p>Total Sales</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card">
                                    <h3>₹<?php echo number_format($commission_summary[0]['avg_commission'] ?? 0); ?></h3>
                                    <p>Average Commission</p>
                                </div>
                            </div>
                        </div>

                        <!-- Property Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="stats-card company">
                                    <div class="icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <h5>Company Properties</h5>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h4 class="text-success"><?php echo $property_stats[0]['sold'] ?? 0; ?></h4>
                                            <small>Sold</small>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-warning"><?php echo $property_stats[0]['available'] ?? 0; ?></h4>
                                            <small>Available</small>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            Avg Value: ₹<?php echo number_format($property_stats[0]['avg_value'] ?? 0); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stats-card resell">
                                    <div class="icon">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <h5>Resell Properties</h5>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h4 class="text-success"><?php echo $property_stats[1]['sold'] ?? 0; ?></h4>
                                            <small>Sold</small>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-warning"><?php echo $property_stats[1]['available'] ?? 0; ?></h4>
                                            <small>Available</small>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            Avg Value: ₹<?php echo number_format($property_stats[1]['avg_value'] ?? 0); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts and Tables -->
                        <div class="row">
                            <!-- Commission Breakdown Chart -->
                            <div class="col-md-6">
                                <div class="stats-card">
                                    <h5>Commission Breakdown</h5>
                                    <div class="chart-container">
                                        <canvas id="commissionChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Sales -->
                            <div class="col-md-6">
                                <div class="stats-card">
                                    <h5>Recent Sales</h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Property</th>
                                                    <th>Type</th>
                                                    <th>Commission</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_sales as $sale): ?>
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($sale['property_name']); ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($sale['location']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?php echo $sale['property_type'] === 'company' ? 'badge-company' : 'badge-resell'; ?>">
                                                            <?php echo ucfirst($sale['property_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td>₹<?php echo number_format($sale['commission_amount']); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $sale['payout_status'] === 'paid' ? 'badge-paid' : 'badge-pending'; ?>">
                                                            <?php echo ucfirst($sale['payout_status']); ?>
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

                        <!-- Monthly Performance -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="stats-card">
                                    <h5>Monthly Performance</h5>
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="performance-indicator excellent">
                                                <i class="fas fa-chart-line me-1"></i>
                                                Excellent
                                            </div>
                                            <h4 class="mt-2">₹<?php echo number_format($monthly_summary[0]['total_commission'] ?? 0); ?></h4>
                                            <small>This Month</small>
                                        </div>
                                        <div class="col-md-3">
                                            <h4 class="text-info"><?php echo $monthly_summary[0]['total_sales'] ?? 0; ?></h4>
                                            <small>Sales This Month</small>
                                        </div>
                                        <div class="col-md-3">
                                            <h4 class="text-success">₹<?php echo number_format($monthly_summary[0]['avg_commission'] ?? 0); ?></h4>
                                            <small>Avg Commission</small>
                                        </div>
                                        <div class="col-md-3">
                                            <h4 class="text-warning">₹<?php echo number_format($pending_commissions['total_pending'] ?? 0); ?></h4>
                                            <small>Pending Payout</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="text-center">
                                    <a href="development_cost_calculator.php" class="btn btn-primary-custom me-2">
                                        <i class="fas fa-calculator me-2"></i>Calculate Plot Rate
                                    </a>
                                    <a href="property_management.php" class="btn btn-primary-custom me-2">
                                        <i class="fas fa-building me-2"></i>Manage Properties
                                    </a>
                                    <a href="commission_plan_manager.php" class="btn btn-primary-custom">
                                        <i class="fas fa-cog me-2"></i>Manage Plans
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Chart data
        <?php
        $company_commission = 0;
        $resell_commission = 0;
        foreach ($commission_summary as $summary) {
            if ($summary['commission_type'] === 'company_mlm') {
                $company_commission = $summary['total_commission'];
            } elseif ($summary['commission_type'] === 'resell_fixed') {
                $resell_commission = $summary['total_commission'];
            }
        }
        ?>

        const chartData = {
            labels: ['Company Properties', 'Resell Properties'],
            datasets: [{
                label: 'Commission by Property Type',
                data: [<?php echo $company_commission; ?>, <?php echo $resell_commission; ?>],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 193, 7, 1)'
                ],
                borderWidth: 2
            }]
        };

        const chartConfig = {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = <?php echo $company_commission + $resell_commission; ?>;
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return context.label + ': ₹' + context.parsed.toLocaleString('en-IN') + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        };

        // Initialize chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('commissionChart').getContext('2d');
            new Chart(ctx, chartConfig);
        });

        // Auto refresh data every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
