<?php
require_once '../core/init.php';
require_once '../includes/emi_foreclosure_logger.php';

class EMIForeclosureReportGenerator {
    private $logger;

    public function __construct() {
        $this->logger = new EMIForeclosureLogger();
    }

    /**
     * Generate comprehensive foreclosure report
     *
     * @param array $filters Filtering options for the report
     * @return array Detailed foreclosure report
     */
    public function generateDetailedReport(array $filters = []): array {
        try {
            $db = \App\Core\App::database();
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
                u.auser AS admin_name,
                ep.total_amount AS original_loan_amount,
                ep.tenure_months,
                ep.start_date AS loan_start_date
            FROM foreclosure_logs fl
            JOIN emi_plans ep ON fl.emi_plan_id = ep.id
            JOIN customers c ON ep.customer_id = c.id
            JOIN properties p ON ep.property_id = p.id
            JOIN admin u ON fl.attempted_by = u.id
            WHERE 1=1";

            $params = [];

            // Apply dynamic filters
            if (!empty($filters['start_date'])) {
                $query .= " AND fl.attempted_at >= ?";
                $params[] = $filters['start_date'];
            }

            if (!empty($filters['end_date'])) {
                $query .= " AND fl.attempted_at <= ?";
                $params[] = $filters['end_date'];
            }

            if (!empty($filters['status'])) {
                $query .= " AND fl.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['customer_id'])) {
                $query .= " AND ep.customer_id = ?";
                $params[] = $filters['customer_id'];
            }

            $query .= " ORDER BY fl.attempted_at DESC LIMIT 500";

            return $db->fetchAll($query, $params);
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
            $db = \App\Core\App::database();
            $statsQuery = "SELECT
                COUNT(*) AS total_attempts,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS successful_attempts,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed_attempts,
                SUM(foreclosure_amount) AS total_foreclosure_amount,
                AVG(foreclosure_amount) AS average_foreclosure_amount
            FROM foreclosure_logs";

            return $db->fetchOne($statsQuery);
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
            $db = \App\Core\App::database();
            $query = "SELECT
                DATE_FORMAT(attempted_at, '%Y-%m') AS month,
                COUNT(*) AS total_attempts,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS successful_attempts,
                SUM(foreclosure_amount) AS total_foreclosure_amount
            FROM foreclosure_logs
            WHERE attempted_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY month
            ORDER BY month DESC";

            return $db->fetchAll($query, [$months]);
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
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            throw new Exception(h($mlSupport->translate('Invalid CSRF token')));
        }

        $reportGenerator = new EMIForeclosureReportGenerator();

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
                throw new Exception(h($mlSupport->translate('Invalid report action')));
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => h($e->getMessage())
        ]);
    }
    exit;
}

$page_title = $mlSupport->translate('EMI Foreclosure Reports');
require_once '../admin_header.php';
require_once '../admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800 fw-bold"><?php echo h($page_title); ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo h(ADMIN_URL); ?>/dashboard.php"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="<?php echo h(ADMIN_URL); ?>/accounting/emi.php"><?php echo h($mlSupport->translate('EMI Management')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Foreclosure Reports')); ?></li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?php echo h($mlSupport->translate('Foreclosure Statistics')); ?></h5>
                    </div>
                    <div class="card-body">
                        <canvas id="foreclosureStatsChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?php echo h($mlSupport->translate('Monthly Foreclosure Trend')); ?></h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyTrendChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold"><?php echo h($mlSupport->translate('Detailed Foreclosure Report')); ?></h5>
                <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="exportReport()">
                    <i class="fas fa-download me-1"></i> <?php echo h($mlSupport->translate('Export Report')); ?>
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="foreclosureReportTable" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo h($mlSupport->translate('EMI Plan ID')); ?></th>
                                <th><?php echo h($mlSupport->translate('Customer')); ?></th>
                                <th><?php echo h($mlSupport->translate('Property')); ?></th>
                                <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                <th><?php echo h($mlSupport->translate('Amount')); ?></th>
                                <th><?php echo h($mlSupport->translate('Date')); ?></th>
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

<?php
$page_specific_js = <<<'EOF'
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Helper function to escape HTML for security
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Fetch and render reports
    async function fetchReport(action, filters = {}) {
        try {
            const response = await fetch('emi_foreclosure_report.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: new URLSearchParams({
                    action: action,
                    ...filters
                })
            });
            return await response.json();
        } catch (error) {
            console.error('Error fetching report:', error);
            return { success: false };
        }
    }

    // Render statistics chart
    async function renderStatsChart() {
        const statsData = await fetchReport('statistics');
        if (!statsData.success) return;

        const ctx = document.getElementById('foreclosureStatsChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['{{SUCCESSFUL}}', '{{FAILED}}'],
                datasets: [{
                    data: [
                        statsData.data.successful_attempts,
                        statsData.data.failed_attempts
                    ],
                    backgroundColor: ['#10b981', '#ef4444'],
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
    }

    // Render monthly trend chart
    async function renderMonthlyTrendChart() {
        const trendData = await fetchReport('monthly_trend');
        if (!trendData.success) return;

        const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendData.data.map(item => escapeHtml(item.month)),
                datasets: [{
                    label: '{{TOTAL_FORECLOSURE_AMOUNT}}',
                    data: trendData.data.map(item => item.total_foreclosure_amount),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Render detailed report table
    async function renderDetailedReport() {
        const reportData = await fetchReport('detailed_report');
        if (!reportData.success) return;

        const tableBody = document.getElementById('reportTableBody');
        tableBody.innerHTML = '';
        reportData.data.forEach(item => {
            const statusClass = item.foreclosure_status === 'success' ? 'bg-success' : 'bg-danger';
            const row = `<tr>
                <td class="fw-bold text-primary">#${escapeHtml(item.emi_plan_id)}</td>
                <td>${escapeHtml(item.customer_name)}</td>
                <td>${escapeHtml(item.property_title)}</td>
                <td><span class="badge rounded-pill ${statusClass} px-3">${escapeHtml(item.foreclosure_status.toUpperCase())}</span></td>
                <td class="fw-bold">₹${parseFloat(item.foreclosure_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                <td>${escapeHtml(new Date(item.attempted_at).toLocaleString())}</td>
            </tr>`;
            tableBody.innerHTML += row;
        });
    }

    // Initialize all reports
    renderStatsChart();
    renderMonthlyTrendChart();
    renderDetailedReport();
});

function exportReport() {
    // Basic export functionality
    window.print();
}
</script>
EOF;

$page_specific_js = str_replace(
    ['{{SUCCESSFUL}}', '{{FAILED}}', '{{TOTAL_FORECLOSURE_AMOUNT}}'],
    [
        h($mlSupport->translate('Successful')),
        h($mlSupport->translate('Failed')),
        h($mlSupport->translate('Total Foreclosure Amount'))
    ],
    $page_specific_js
);

require_once '../admin_footer.php';
?>
