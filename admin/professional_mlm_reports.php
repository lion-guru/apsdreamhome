<?php
/**
 * Professional MLM Report Generator
 * Advanced reporting and analytics for MLM system
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check admin authentication
if (!isLoggedIn() || !isAdmin()) {
    redirectTo('login.php');
}

$page_title = "Professional MLM Reports";
include 'includes/header.php';

// Handle report generation
$report_type = $_GET['report'] ?? 'overview';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$export_format = $_GET['export'] ?? '';

// Generate reports based on type
switch ($report_type) {
    case 'commission_summary':
        $report_data = generateCommissionSummaryReport($conn, $start_date, $end_date);
        break;
    case 'rank_performance':
        $report_data = generateRankPerformanceReport($conn, $start_date, $end_date);
        break;
    case 'team_analytics':
        $report_data = generateTeamAnalyticsReport($conn, $start_date, $end_date);
        break;
    case 'bonus_analysis':
        $report_data = generateBonusAnalysisReport($conn, $start_date, $end_date);
        break;
    case 'payout_summary':
        $report_data = generatePayoutSummaryReport($conn, $start_date, $end_date);
        break;
    case 'growth_trends':
        $report_data = generateGrowthTrendsReport($conn, $start_date, $end_date);
        break;
    default:
        $report_data = generateOverviewReport($conn, $start_date, $end_date);
}

// Export functionality
if ($export_format === 'csv') {
    exportToCSV($report_data, $report_type);
    exit;
} elseif ($export_format === 'pdf') {
    exportToPDF($report_data, $report_type);
    exit;
}

function generateOverviewReport($conn, $start_date, $end_date) {
    $report = [];
    
    // Total statistics
    $result = $conn->query("
        SELECT 
            COUNT(DISTINCT u.id) as total_associates,
            COUNT(DISTINCT CASE WHEN u.status = 'active' THEN u.id END) as active_associates,
            SUM(CASE WHEN u.created_at >= '$start_date' AND u.created_at <= '$end_date' THEN 1 ELSE 0 END) as new_associates
        FROM users u 
        WHERE u.role = 'associate'
    ");
    $report['associates'] = $result->fetch_assoc();
    
    // Commission statistics
    $result = $conn->query("
        SELECT 
            SUM(amount) as total_commissions,
            COUNT(*) as total_transactions,
            AVG(amount) as average_commission
        FROM mlm_commissions 
        WHERE created_at >= '$start_date' AND created_at <= '$end_date'
    ");
    $report['commissions'] = $result->fetch_assoc();
    
    // Rank distribution
    $result = $conn->query("
        SELECT 
            l.level_name,
            COUNT(DISTINCT u.id) as associate_count,
            SUM(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) as active_count
        FROM mlm_levels l
        LEFT JOIN users u ON u.rank_id = l.level_id AND u.role = 'associate'
        GROUP BY l.level_id, l.level_name
        ORDER BY l.level_order
    ");
    $report['rank_distribution'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // Monthly trends
    $result = $conn->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as new_associates,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_associates
        FROM users 
        WHERE role = 'associate' AND created_at >= DATE_SUB('$start_date', INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month
    ");
    $report['monthly_trends'] = $result->fetch_all(MYSQLI_ASSOC);
    
    return $report;
}

function generateCommissionSummaryReport($conn, $start_date, $end_date) {
    $report = [];
    
    // Commission by type
    $result = $conn->query("
        SELECT 
            commission_type,
            SUM(amount) as total_amount,
            COUNT(*) as transaction_count,
            AVG(amount) as average_amount
        FROM mlm_commissions 
        WHERE created_at >= '$start_date' AND created_at <= '$end_date'
        GROUP BY commission_type
        ORDER BY total_amount DESC
    ");
    $report['by_type'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // Top earners
    $result = $conn->query("
        SELECT 
            u.full_name,
            u.username,
            SUM(c.amount) as total_commissions,
            COUNT(c.id) as transaction_count
        FROM mlm_commissions c
        JOIN users u ON c.associate_id = u.id
        WHERE c.created_at >= '$start_date' AND c.created_at <= '$end_date'
        GROUP BY c.associate_id
        ORDER BY total_commissions DESC
        LIMIT 20
    ");
    $report['top_earners'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // Monthly commission trends
    $result = $conn->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(amount) as total_commissions,
            COUNT(*) as transaction_count
        FROM mlm_commissions 
        WHERE created_at >= DATE_SUB('$end_date', INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month
    ");
    $report['monthly_trends'] = $result->fetch_all(MYSQLI_ASSOC);
    
    return $report;
}

function generateRankPerformanceReport($conn, $start_date, $end_date) {
    $report = [];
    
    // Rank advancement summary
    $result = $conn->query("
        SELECT 
            l.level_name,
            COUNT(ra.id) as promotions,
            AVG(ra.business_achieved) as avg_business,
            AVG(ra.team_size_achieved) as avg_team_size
        FROM mlm_rank_advancements ra
        JOIN mlm_levels l ON ra.to_rank_id = l.level_id
        WHERE ra.promotion_date >= '$start_date' AND ra.promotion_date <= '$end_date'
        GROUP BY l.level_id, l.level_name
        ORDER BY promotions DESC
    ");
    $report['advancements'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // Fast track promotions
    $result = $conn->query("
        SELECT 
            u.full_name,
            u.username,
            l.level_name as new_rank,
            ra.business_achieved,
            ra.team_size_achieved,
            ra.fast_track_bonus,
            ra.promotion_date
        FROM mlm_rank_advancements ra
        JOIN users u ON ra.user_id = u.id
        JOIN mlm_levels l ON ra.to_rank_id = l.level_id
        WHERE ra.is_fast_track = 1 AND ra.promotion_date >= '$start_date' AND ra.promotion_date <= '$end_date'
        ORDER BY ra.promotion_date DESC
        LIMIT 20
    ");
    $report['fast_track_promotions'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // Rank retention analysis
    $result = $conn->query("
        SELECT 
            l.level_name,
            COUNT(DISTINCT u.id) as current_rank_count,
            AVG(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) as active_percentage,
            AVG(up.total_earnings) as avg_earnings
        FROM mlm_levels l
        LEFT JOIN users u ON u.rank_id = l.level_id AND u.role = 'associate'
        LEFT JOIN mlm_performance up ON up.user_id = u.id
        GROUP BY l.level_id, l.level_name
        ORDER BY l.level_order
    ");
    $report['rank_retention'] = $result->fetch_all(MYSQLI_ASSOC);
    
    return $report;
}

function generateTeamAnalyticsReport($conn, $start_date, $end_date) {
    $report = [];
    
    // Team size analysis
    $result = $conn->query("
        SELECT 
            team_size,
            COUNT(*) as team_count,
            AVG(total_sales) as avg_sales,
            AVG(total_earnings) as avg_earnings
        FROM (
            SELECT 
                u.id,
                COUNT(DISTINCT d.id) as team_size,
                COALESCE(SUM(p.total_amount), 0) as total_sales,
                COALESCE(SUM(c.amount), 0) as total_earnings
            FROM users u
            LEFT JOIN users d ON d.sponsor_id = u.id
            LEFT JOIN plots p ON p.associate_id = u.id AND p.booking_date >= '$start_date' AND p.booking_date <= '$end_date'
            LEFT JOIN mlm_commissions c ON c.associate_id = u.id AND c.created_at >= '$start_date' AND c.created_at <= '$end_date'
            WHERE u.role = 'associate'
            GROUP BY u.id
        ) team_stats
        GROUP BY team_size
        ORDER BY team_size
    ");
    $report['team_size_analysis'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // Top performing teams
    $result = $conn->query("
        SELECT 
            u.full_name as team_leader,
            u.username,
            COUNT(DISTINCT d.id) as team_size,
            COALESCE(SUM(p.total_amount), 0) as team_sales,
            COALESCE(SUM(c.amount), 0) as team_commissions
        FROM users u
        LEFT JOIN users d ON d.sponsor_id = u.id
        LEFT JOIN plots p ON p.associate_id = d.id AND p.booking_date >= '$start_date' AND p.booking_date <= '$end_date'
        LEFT JOIN mlm_commissions c ON c.associate_id = d.id AND c.created_at >= '$start_date' AND c.created_at <= '$end_date'
        WHERE u.role = 'associate'
        GROUP BY u.id
        HAVING team_size > 0
        ORDER BY team_sales DESC
        LIMIT 20
    ");
    $report['top_teams'] = $result->fetch_all(MYSQLI_ASSOC);
    
    return $report;
}

function generateBonusAnalysisReport($conn, $start_date, $end_date) {
    $report = [];
    
    // Bonus distribution
    $result = $conn->query("
        SELECT 
            sb.bonus_name,
            sb.bonus_type,
            COUNT(DISTINCT bs.id) as times_awarded,
            SUM(bs.bonus_amount) as total_amount,
            AVG(bs.bonus_amount) as average_amount
        FROM mlm_special_bonuses sb
        LEFT JOIN mlm_bonus_settings bs ON bs.bonus_type = sb.bonus_type
        WHERE bs.created_at >= '$start_date' AND bs.created_at <= '$end_date'
        GROUP BY sb.bonus_type, sb.bonus_name
        ORDER BY total_amount DESC
    ");
    $report['bonus_distribution'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // Special bonuses by associate
    $result = $conn->query("
        SELECT 
            u.full_name,
            u.username,
            sb.bonus_name,
            bs.bonus_amount,
            bs.qualification_criteria,
            bs.created_at as awarded_date
        FROM mlm_bonus_settings bs
        JOIN users u ON bs.associate_id = u.id
        JOIN mlm_special_bonuses sb ON bs.bonus_type = sb.bonus_type
        WHERE bs.created_at >= '$start_date' AND bs.created_at <= '$end_date'
        ORDER BY bs.bonus_amount DESC
        LIMIT 50
    ");
    $report['special_bonuses'] = $result->fetch_all(MYSQLI_ASSOC);
    
    return $report;
}

function generatePayoutSummaryReport($conn, $start_date, $end_date) {
    $report = [];
    
    // Payout summary
    $result = $conn->query("
        SELECT 
            payout_status,
            COUNT(*) as payout_count,
            SUM(total_amount) as total_payouts,
            AVG(total_amount) as average_payout
        FROM mlm_payouts 
        WHERE payout_date >= '$start_date' AND payout_date <= '$end_date'
        GROUP BY payout_status
        ORDER BY total_payouts DESC
    ");
    $report['payout_summary'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // Payouts by associate
    $result = $conn->query("
        SELECT 
            u.full_name,
            u.username,
            COUNT(p.id) as payout_count,
            SUM(p.total_amount) as total_payouts,
            AVG(p.total_amount) as average_payout,
            MAX(p.payout_date) as last_payout_date
        FROM mlm_payouts p
        JOIN users u ON p.associate_id = u.id
        WHERE p.payout_date >= '$start_date' AND p.payout_date <= '$end_date'
        GROUP BY p.associate_id
        ORDER BY total_payouts DESC
        LIMIT 30
    ");
    $report['associate_payouts'] = $result->fetch_all(MYSQLI_ASSOC);
    
    return $report;
}

function generateGrowthTrendsReport($conn, $start_date, $end_date) {
    $report = [];
    
    // Monthly growth metrics
    $result = $conn->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as new_associates,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_associates,
            SUM(CASE WHEN rank_id > 1 THEN 1 ELSE 0 END) as promoted_associates
        FROM users 
        WHERE role = 'associate' AND created_at >= DATE_SUB('$start_date', INTERVAL 24 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month
    ");
    $report['monthly_growth'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // Revenue growth
    $result = $conn->query("
        SELECT 
            DATE_FORMAT(booking_date, '%Y-%m') as month,
            COUNT(*) as total_bookings,
            SUM(total_amount) as total_revenue,
            AVG(total_amount) as average_booking_value
        FROM plots 
        WHERE booking_date >= DATE_SUB('$start_date', INTERVAL 24 MONTH)
        GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
        ORDER BY month
    ");
    $report['revenue_growth'] = $result->fetch_all(MYSQLI_ASSOC);
    
    return $report;
}

function exportToCSV($data, $report_type) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="mlm_' . $report_type . '_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        // Write headers
        $headers = array_keys(reset($data));
        fputcsv($output, $headers);
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
}

function exportToPDF($data, $report_type) {
    // This would require a PDF library like TCPDF or mPDF
    // For now, we'll just output a simple HTML table
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="mlm_' . $report_type . '_report_' . date('Y-m-d') . '.html"');
    
    echo '<html><head><title>MLM Report</title></head><body>';
    echo '<h1>MLM ' . ucwords(str_replace('_', ' ', $report_type)) . ' Report</h1>';
    echo '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
    
    if (!empty($data)) {
        echo '<table border="1" cellpadding="5" cellspacing="0">';
        echo '<tr>';
        foreach (array_keys(reset($data)) as $header) {
            echo '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . '</th>';
        }
        echo '</tr>';
        
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $value) {
                echo '<td>' . htmlspecialchars($value) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    }
    
    echo '</body></html>';
}

?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 text-primary mb-0">
                <i class="fas fa-chart-line me-2"></i>Professional MLM Reports
            </h1>
            <p class="text-muted">Advanced reporting and analytics for MLM system</p>
        </div>
    </div>

    <!-- Report Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Report Type</label>
                            <select name="report" class="form-select">
                                <option value="overview" <?php echo $report_type == 'overview' ? 'selected' : ''; ?>>Overview Report</option>
                                <option value="commission_summary" <?php echo $report_type == 'commission_summary' ? 'selected' : ''; ?>>Commission Summary</option>
                                <option value="rank_performance" <?php echo $report_type == 'rank_performance' ? 'selected' : ''; ?>>Rank Performance</option>
                                <option value="team_analytics" <?php echo $report_type == 'team_analytics' ? 'selected' : ''; ?>>Team Analytics</option>
                                <option value="bonus_analysis" <?php echo $report_type == 'bonus_analysis' ? 'selected' : ''; ?>>Bonus Analysis</option>
                                <option value="payout_summary" <?php echo $report_type == 'payout_summary' ? 'selected' : ''; ?>>Payout Summary</option>
                                <option value="growth_trends" <?php echo $report_type == 'growth_trends' ? 'selected' : ''; ?>>Growth Trends</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Generate Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Content -->
    <div class="row">
        <div class="col-12">
            <?php switch ($report_type): 
                case 'overview': ?>
                    <!-- Overview Report -->
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Associates</h5>
                                    <h2 class="mb-0"><?php echo number_format($report_data['associates']['total_associates']); ?></h2>
                                    <small>Active: <?php echo number_format($report_data['associates']['active_associates']); ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">New Associates</h5>
                                    <h2 class="mb-0"><?php echo number_format($report_data['associates']['new_associates']); ?></h2>
                                    <small><?php echo $start_date; ?> to <?php echo $end_date; ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Commissions</h5>
                                    <h2 class="mb-0">₹<?php echo number_format($report_data['commissions']['total_commissions'], 2); ?></h2>
                                    <small><?php echo number_format($report_data['commissions']['transaction_count']); ?> transactions</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Rank Distribution</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Total Associates</th>
                                            <th>Active Associates</th>
                                            <th>Activity Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data['rank_distribution'] as $rank): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($rank['level_name']); ?></strong></td>
                                            <td><?php echo number_format($rank['associate_count']); ?></td>
                                            <td><?php echo number_format($rank['active_count']); ?></td>
                                            <td>
                                                <?php echo $rank['associate_count'] > 0 ? 
                                                    round(($rank['active_count'] / $rank['associate_count']) * 100, 1) . '%' : 
                                                    '0%'; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php break;
                    
                case 'commission_summary': ?>
                    <!-- Commission Summary Report -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Commission by Type</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Commission Type</th>
                                            <th>Total Amount</th>
                                            <th>Transaction Count</th>
                                            <th>Average Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data['by_type'] as $commission): ?>
                                        <tr>
                                            <td><strong><?php echo ucwords(str_replace('_', ' ', $commission['commission_type'])); ?></strong></td>
                                            <td>₹<?php echo number_format($commission['total_amount'], 2); ?></td>
                                            <td><?php echo number_format($commission['transaction_count']); ?></td>
                                            <td>₹<?php echo number_format($commission['average_amount'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Top Earners</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Associate Name</th>
                                            <th>Username</th>
                                            <th>Total Commissions</th>
                                            <th>Transaction Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data['top_earners'] as $earner): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($earner['full_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($earner['username']); ?></td>
                                            <td>₹<?php echo number_format($earner['total_commissions'], 2); ?></td>
                                            <td><?php echo number_format($earner['transaction_count']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php break;
                    
                case 'rank_performance': ?>
                    <!-- Rank Performance Report -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Rank Advancements</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Promotions</th>
                                            <th>Avg Business Achieved</th>
                                            <th>Avg Team Size</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data['advancements'] as $advancement): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($advancement['level_name']); ?></strong></td>
                                            <td><?php echo number_format($advancement['promotions']); ?></td>
                                            <td>₹<?php echo number_format($advancement['avg_business'], 2); ?></td>
                                            <td><?php echo number_format($advancement['avg_team_size']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php break;
                    
                case 'team_analytics': ?>
                    <!-- Team Analytics Report -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Top Performing Teams</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Team Leader</th>
                                            <th>Username</th>
                                            <th>Team Size</th>
                                            <th>Team Sales</th>
                                            <th>Team Commissions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data['top_teams'] as $team): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($team['team_leader']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($team['username']); ?></td>
                                            <td><?php echo number_format($team['team_size']); ?></td>
                                            <td>₹<?php echo number_format($team['team_sales'], 2); ?></td>
                                            <td>₹<?php echo number_format($team['team_commissions'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php break;
                    
                default: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Report type "<?php echo $report_type; ?>" is available for export only.
                    </div>
                    <?php break;
            endswitch; ?>
        </div>
    </div>

    <!-- Export Options -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Export Report</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="?report=<?php echo $report_type; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&export=csv" 
                               class="btn btn-success w-100">
                                <i class="fas fa-file-csv me-2"></i>Export as CSV
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="?report=<?php echo $report_type; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&export=pdf" 
                               class="btn btn-danger w-100">
                                <i class="fas fa-file-pdf me-2"></i>Export as PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Initialize date pickers and enhance UI
document.addEventListener('DOMContentLoaded', function() {
    // Add any JavaScript enhancements here
    console.log('Professional MLM Reports loaded');
});
</script>