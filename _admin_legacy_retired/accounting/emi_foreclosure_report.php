<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../includes/emi_foreclosure_logger.php';

class EMIForeclosureReportGenerator {
    private $conn;
    private $logger;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->logger = new EMIForeclosureLogger($dbConnection);
    }

    /**
     * Generate comprehensive foreclosure report
     * 
     * @param array $filters Filtering options for the report
     * @return array Detailed foreclosure report
     */
    public function generateDetailedReport(array $filters = []): array {
        try {
            $query = "SELECT 
                fl.id AS log_id,
                fl.emi_plan_id,
                ep.customer_id,
                c.name AS customer_name,
                p.title AS property_title,
                fl.status AS foreclosure_status,
                fl.message,
                fl.foreclosure_amount,
                fl.attempted_at,
                u.first_name AS admin_name,
                ep.total_amount AS original_loan_amount,
                ep.tenure_months,
                ep.start_date AS loan_start_date
            FROM foreclosure_logs fl
            JOIN emi_plans ep ON fl.emi_plan_id = ep.id
            JOIN customers c ON ep.customer_id = c.id
            JOIN properties p ON ep.property_id = p.id
            JOIN users u ON fl.attempted_by = u.id
            WHERE 1=1";

            $params = [];
            $paramTypes = '';

            // Apply dynamic filters
            if (!empty($filters['start_date'])) {
                $query .= " AND fl.attempted_at >= ?";
                $params[] = $filters['start_date'];
                $paramTypes .= 's';
            }

            if (!empty($filters['end_date'])) {
                $query .= " AND fl.attempted_at <= ?";
                $params[] = $filters['end_date'];
                $paramTypes .= 's';
            }

            if (!empty($filters['status'])) {
                $query .= " AND fl.status = ?";
                $params[] = $filters['status'];
                $paramTypes .= 's';
            }

            if (!empty($filters['customer_id'])) {
                $query .= " AND ep.customer_id = ?";
                $params[] = $filters['customer_id'];
                $paramTypes .= 'i';
            }

            $query .= " ORDER BY fl.attempted_at DESC LIMIT 500";

            $stmt = $this->conn->prepare($query);

            if (!empty($params)) {
                $stmt->bind_param($paramTypes, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log('Foreclosure Report Generation Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate foreclosure statistics
     * 
     * @return array Comprehensive foreclosure statistics
     */
    public function getForeclosureStatistics(): array {
        try {
            $statsQuery = "SELECT 
                COUNT(*) AS total_attempts,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS successful_attempts,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed_attempts,
                SUM(foreclosure_amount) AS total_foreclosure_amount,
                AVG(foreclosure_amount) AS average_foreclosure_amount
            FROM foreclosure_logs";

            $result = $this->conn->query($statsQuery);
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log('Foreclosure Statistics Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate monthly foreclosure trend
     * 
     * @param int $months Number of months to analyze
     * @return array Monthly foreclosure trends
     */
    public function getMonthlyForeclosureTrend(int $months = 12): array {
        try {
            $query = "SELECT 
                DATE_FORMAT(attempted_at, '%Y-%m') AS month,
                COUNT(*) AS total_attempts,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS successful_attempts,
                SUM(foreclosure_amount) AS total_foreclosure_amount
            FROM foreclosure_logs
            WHERE attempted_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY month
            ORDER BY month DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $months);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log('Monthly Foreclosure Trend Error: ' . $e->getMessage());
            return [];
        }
    }
}

// Handle AJAX requests for report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        global $con;
        $conn = $con;
        $reportGenerator = new EMIForeclosureReportGenerator($conn);
        
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'detailed_report':
                $filters = $_POST['filters'] ?? [];
                $report = $reportGenerator->generateDetailedReport($filters);
                echo json_encode(['success' => true, 'data' => $report]);
                break;
            
            case 'statistics':
                $stats = $reportGenerator->getForeclosureStatistics();
                echo json_encode(['success' => true, 'data' => $stats]);
                break;
            
            case 'monthly_trend':
                $months = $_POST['months'] ?? 12;
                $trend = $reportGenerator->getMonthlyForeclosureTrend($months);
                echo json_encode(['success' => true, 'data' => $trend]);
                break;
            
            default:
                throw new Exception('Invalid report action');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EMI Foreclosure Reports</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script src="/assets/js/chart.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>EMI Foreclosure Reports</h1>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Foreclosure Statistics</div>
                    <div class="card-body">
                        <canvas id="foreclosureStatsChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Monthly Foreclosure Trend</div>
                    <div class="card-body">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Detailed Foreclosure Report</div>
                    <div class="card-body">
                        <table id="foreclosureReportTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>EMI Plan ID</th>
                                    <th>Customer</th>
                                    <th>Property</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="reportTableBody">
                                <!-- Dynamic content will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch and render reports
        async function fetchReport(action, filters = {}) {
            const response = await fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}&${new URLSearchParams(filters)}`
            });
            return await response.json();
        }

        // Render statistics chart
        async function renderStatsChart() {
            const statsData = await fetchReport('statistics');
            const ctx = document.getElementById('foreclosureStatsChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Successful', 'Failed'],
                    datasets: [{
                        data: [
                            statsData.data.successful_attempts, 
                            statsData.data.failed_attempts
                        ],
                        backgroundColor: ['#28a745', '#dc3545']
                    }]
                }
            });
        }

        // Render monthly trend chart
        async function renderMonthlyTrendChart() {
            const trendData = await fetchReport('monthly_trend');
            const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendData.data.map(item => item.month),
                    datasets: [{
                        label: 'Total Foreclosure Amount',
                        data: trendData.data.map(item => item.total_foreclosure_amount),
                        borderColor: '#007bff',
                        fill: false
                    }]
                }
            });
        }

        // Render detailed report table
        async function renderDetailedReport() {
            const reportData = await fetchReport('detailed_report');
            const tableBody = document.getElementById('reportTableBody');
            reportData.data.forEach(item => {
                const row = `
                    <tr>
                        <td>${item.emi_plan_id}</td>
                        <td>${item.customer_name}</td>
                        <td>${item.property_title}</td>
                        <td>${item.foreclosure_status}</td>
                        <td>₹${item.foreclosure_amount.toFixed(2)}</td>
                        <td>${item.attempted_at}</td>
                    </tr>
                `;
                tableBody.innerHTML += row;
            });
        }

        // Initialize all reports
        renderStatsChart();
        renderMonthlyTrendChart();
        renderDetailedReport();
    });
    </script>
</body>
</html>