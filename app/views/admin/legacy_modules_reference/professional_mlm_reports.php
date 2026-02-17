<?php
/**
 * Professional MLM Report Generator
 * Advanced reporting and analytics for MLM system
 */

require_once 'core/init.php';

use App\Core\Database;
$db = \App\Core\App::database();

// Check admin authentication
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$page_title = "Professional MLM Reports";
$include_datatables = true;

// Handle report generation
$report_type = $_GET['report'] ?? 'overview';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$export_format = $_GET['export'] ?? '';

// Generate reports based on type
switch ($report_type) {
    case 'commission_summary':
        $report_data = generateCommissionSummaryReport($db, $start_date, $end_date);
        break;
    case 'rank_performance':
        $report_data = generateRankPerformanceReport($db, $start_date, $end_date);
        break;
    case 'team_analytics':
        $report_data = generateTeamAnalyticsReport($db, $start_date, $end_date);
        break;
    case 'bonus_analysis':
        $report_data = generateBonusAnalysisReport($db, $start_date, $end_date);
        break;
    case 'payout_summary':
        $report_data = generatePayoutSummaryReport($db, $start_date, $end_date);
        break;
    case 'growth_trends':
        $report_data = generateGrowthTrendsReport($db, $start_date, $end_date);
        break;
    default:
        $report_data = generateOverviewReport($db, $start_date, $end_date);
}

// Export functionality
if ($export_format === 'csv') {
    exportToCSV($report_data, $report_type);
    exit;
} elseif ($export_format === 'pdf') {
    exportToPDF($report_data, $report_type);
    exit;
}

include 'admin_header.php';
include 'admin_sidebar.php';

function generateOverviewReport($db, $start_date, $end_date) {
    $report = [];

    // Total statistics
    $report['associates'] = $db->fetch("
        SELECT
            COUNT(DISTINCT u.uid) as total_associates,
            COUNT(DISTINCT CASE WHEN a.status = 'active' THEN u.uid END) as active_associates,
            SUM(CASE WHEN u.join_date >= ? AND u.join_date <= ? THEN 1 ELSE 0 END) as new_associates
        FROM user u
        JOIN associates a ON u.uid = a.user_id
        WHERE u.utype = 2
    ", [$start_date, $end_date]);

    // Commission statistics
    $report['commissions'] = $db->fetch("
        SELECT
            SUM(amount) as total_commissions,
            COUNT(*) as total_transactions,
            AVG(amount) as average_commission
        FROM mlm_commissions
        WHERE created_at >= ? AND created_at <= ?
    ", [$start_date, $end_date]);

    // Rank distribution
    $report['rank_distribution'] = $db->fetchAll("
        SELECT
            l.level_name,
            COUNT(DISTINCT u.uid) as associate_count,
            SUM(CASE WHEN a.status = 'active' THEN 1 ELSE 0 END) as active_count
        FROM mlm_levels l
        LEFT JOIN associates a ON a.current_level = l.id
        LEFT JOIN user u ON a.user_id = u.uid AND u.utype = 2
        GROUP BY l.id, l.level_name
        ORDER BY l.level_order
    ");

    // Monthly trends
    $report['monthly_trends'] = $db->fetchAll("
        SELECT
            DATE_FORMAT(u.join_date, '%Y-%m') as month,
            COUNT(*) as new_associates,
            SUM(CASE WHEN a.status = 'active' THEN 1 ELSE 0 END) as active_associates
        FROM user u
        JOIN associates a ON u.uid = a.user_id
        WHERE u.utype = 2 AND u.join_date >= DATE_SUB(?, INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(u.join_date, '%Y-%m')
        ORDER BY month
    ", [$start_date]);

    return $report;
}

function generateCommissionSummaryReport($db, $start_date, $end_date) {
    $report = [];

    // Commission by type
    $report['by_type'] = $db->fetchAll("
        SELECT
            commission_type,
            SUM(amount) as total_amount,
            COUNT(*) as transaction_count,
            AVG(amount) as average_amount
        FROM mlm_commissions
        WHERE created_at >= ? AND created_at <= ?
        GROUP BY commission_type
        ORDER BY total_amount DESC
    ", [$start_date, $end_date]);

    // Top earners
    $report['top_earners'] = $db->fetchAll("
        SELECT
            u.uname as full_name,
            u.uemail as username,
            SUM(c.amount) as total_commissions,
            COUNT(c.id) as transaction_count
        FROM mlm_commissions c
        JOIN user u ON c.associate_id = u.uid
        WHERE c.created_at >= ? AND c.created_at <= ?
        GROUP BY c.associate_id
        ORDER BY total_commissions DESC
        LIMIT 20
    ", [$start_date, $end_date]);

    // Monthly commission trends
    $report['monthly_trends'] = $db->fetchAll("
        SELECT
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(amount) as total_commissions,
            COUNT(*) as transaction_count
        FROM mlm_commissions
        WHERE created_at >= DATE_SUB(?, INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month
    ", [$end_date]);

    return $report;
}

function generateRankPerformanceReport($db, $start_date, $end_date) {
    $report = [];

    // Rank advancement summary
    $report['rank_advancements'] = $db->fetchAll("
        SELECT
            l.level_name,
            COUNT(*) as promotions,
            0 as avg_business,
            0 as avg_team_size
        FROM mlm_rank_advancements ra
        JOIN mlm_levels l ON ra.new_rank = l.id
        WHERE ra.advancement_date >= ? AND ra.advancement_date <= ?
        GROUP BY l.id, l.level_name
        ORDER BY promotions DESC
    ", [$start_date, $end_date]);

    // Fast track promotions
    $report['fast_track_promotions'] = $db->fetchAll("
        SELECT
            u.uname as full_name,
            u.uemail as username,
            l.level_name as new_rank,
            0 as business_achieved,
            0 as team_size_achieved,
            0 as fast_track_bonus,
            ra.advancement_date as promotion_date
        FROM mlm_rank_advancements ra
        JOIN user u ON ra.associate_id = u.uid
        JOIN mlm_levels l ON ra.new_rank = l.id
        WHERE ra.advancement_date >= ? AND ra.advancement_date <= ?
        ORDER BY ra.advancement_date DESC
        LIMIT 20
    ", [$start_date, $end_date]);

    // Rank retention analysis
    $report['rank_retention'] = $db->fetchAll("
        SELECT
            l.level_name,
            COUNT(DISTINCT u.uid) as current_rank_count,
            AVG(CASE WHEN a.status = 'active' THEN 1 ELSE 0 END) as active_percentage,
            AVG(COALESCE(up.total_sales_amount, 0)) as avg_earnings
        FROM mlm_levels l
        LEFT JOIN associates a ON a.current_level = l.id
        LEFT JOIN user u ON a.user_id = u.uid AND u.utype = 2
        LEFT JOIN mlm_performance up ON up.associate_id = u.uid
        GROUP BY l.id, l.level_name
        ORDER BY l.level_order
    ");

    return $report;
}

function generateTeamAnalyticsReport($db, $start_date, $end_date) {
    $report = [];

    // Team size analysis
    $report['team_size_analysis'] = $db->fetchAll("
        SELECT
            team_size,
            COUNT(*) as team_count,
            AVG(total_sales) as avg_sales,
            AVG(total_earnings) as avg_earnings
        FROM (
            SELECT
                u.uid,
                COUNT(DISTINCT d.uid) as team_size,
                COALESCE(SUM(p.total_amount), 0) as total_sales,
                COALESCE(SUM(c.amount), 0) as total_earnings
            FROM user u
            LEFT JOIN user d ON d.sponsor_id = u.uid
            LEFT JOIN plots p ON p.associate_id = u.uid AND p.booking_date >= ? AND p.booking_date <= ?
            LEFT JOIN mlm_commissions c ON c.associate_id = u.uid AND c.created_at >= ? AND c.created_at <= ?
            WHERE u.utype = 2
            GROUP BY u.uid
        ) team_stats
        GROUP BY team_size
        ORDER BY team_size
    ", [$start_date, $end_date, $start_date, $end_date]);

    // Top performing teams
    $report['top_teams'] = $db->fetchAll("
        SELECT
            u.uname as team_leader,
            u.uemail as username,
            COUNT(DISTINCT d.uid) as team_size,
            COALESCE(SUM(p.total_amount), 0) as team_sales,
            COALESCE(SUM(c.amount), 0) as team_commissions
        FROM user u
        LEFT JOIN user d ON d.sponsor_id = u.uid
        LEFT JOIN plots p ON p.associate_id = d.uid AND p.booking_date >= ? AND p.booking_date <= ?
        LEFT JOIN mlm_commissions c ON c.associate_id = d.uid AND c.created_at >= ? AND c.created_at <= ?
        WHERE u.utype = 2
        GROUP BY u.uid
        HAVING team_size > 0
        ORDER BY team_sales DESC
        LIMIT 20
    ", [$start_date, $end_date, $start_date, $end_date]);

    return $report;
}

function generateBonusAnalysisReport($db, $start_date, $end_date) {
    $report = [];

    // Bonus distribution
    $report['bonus_distribution'] = $db->fetchAll("
        SELECT
            sb.bonus_name,
            sb.bonus_type,
            COUNT(DISTINCT bs.id) as times_awarded,
            SUM(bs.bonus_amount) as total_amount,
            AVG(bs.bonus_amount) as average_amount
        FROM mlm_special_bonuses sb
        LEFT JOIN mlm_bonus_settings bs ON bs.bonus_type = sb.bonus_type
        WHERE bs.created_at >= ? AND bs.created_at <= ?
        GROUP BY sb.bonus_type, sb.bonus_name
        ORDER BY total_amount DESC
    ", [$start_date, $end_date]);

    // Special bonuses by associate
    $report['special_bonuses'] = $db->fetchAll("
        SELECT
            u.uname as full_name,
            u.uemail as username,
            sb.bonus_name,
            bs.bonus_amount,
            bs.qualification_criteria,
            bs.created_at as awarded_date
        FROM mlm_bonus_settings bs
        JOIN user u ON bs.associate_id = u.uid
        JOIN mlm_special_bonuses sb ON bs.bonus_type = sb.bonus_type
        WHERE bs.created_at >= ? AND bs.created_at <= ?
        ORDER BY bs.bonus_amount DESC
        LIMIT 50
    ", [$start_date, $end_date]);

    return $report;
}

function generatePayoutSummaryReport($db, $start_date, $end_date) {
    $report = [];

    // Payout summary
    $report['payout_summary'] = $db->fetchAll("
        SELECT
            payout_status,
            COUNT(*) as payout_count,
            SUM(total_amount) as total_payouts,
            AVG(total_amount) as average_payout
        FROM mlm_payouts
        WHERE payout_date >= ? AND payout_date <= ?
        GROUP BY payout_status
        ORDER BY total_payouts DESC
    ", [$start_date, $end_date]);

    // Payouts by associate
    $report['associate_payouts'] = $db->fetchAll("
        SELECT
            u.uname as full_name,
            u.uemail as username,
            COUNT(p.id) as payout_count,
            SUM(p.total_amount) as total_payouts,
            AVG(p.total_amount) as average_payout,
            MAX(p.payout_date) as last_payout_date
        FROM mlm_payouts p
        JOIN user u ON p.associate_id = u.uid
        WHERE p.payout_date >= ? AND p.payout_date <= ?
        GROUP BY p.associate_id
        ORDER BY total_payouts DESC
        LIMIT 30
    ", [$start_date, $end_date]);

    return $report;
}

function generateGrowthTrendsReport($db, $start_date, $end_date) {
    $report = [];

    // Monthly growth metrics
    $report['monthly_growth'] = $db->fetchAll("
        SELECT
            DATE_FORMAT(u.join_date, '%Y-%m') as month,
            COUNT(*) as new_associates,
            SUM(CASE WHEN a.status = 'active' THEN 1 ELSE 0 END) as active_associates,
            SUM(CASE WHEN a.current_level > 1 THEN 1 ELSE 0 END) as promoted_associates
        FROM user u
        JOIN associates a ON u.uid = a.user_id
        WHERE u.utype = 2 AND u.join_date >= DATE_SUB(?, INTERVAL 24 MONTH)
        GROUP BY DATE_FORMAT(u.join_date, '%Y-%m')
        ORDER BY month
    ", [$start_date]);

    // Revenue growth
    $report['revenue_growth'] = $db->fetchAll("
        SELECT
            DATE_FORMAT(booking_date, '%Y-%m') as month,
            COUNT(*) as total_bookings,
            SUM(total_amount) as total_revenue,
            AVG(total_amount) as average_booking_value
        FROM plots
        WHERE booking_date >= DATE_SUB(?, INTERVAL 24 MONTH)
        GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
        ORDER BY month
    ", [$start_date]);

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
            echo '<th>' . h(ucwords(str_replace('_', ' ', $header))) . '</th>';
        }
        echo '</tr>';

        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $value) {
                echo '<td>' . h($value) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    }

    echo '</body></html>';
}

?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">MLM Reports</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Report Navigation -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-3">
                                <label class="form-label">Report Type</label>
                                <select name="report" class="form-control">
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
                                <input type="date" name="start_date" class="form-control" value="<?php echo h($start_date); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo h($end_date); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fa fa-search"></i> Generate Report
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
                                        <h2 class="mb-0 text-white"><?php echo number_format($report_data['associates']['total_associates']); ?></h2>
                                        <small>Active: <?php echo number_format($report_data['associates']['active_associates']); ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">New Associates</h5>
                                        <h2 class="mb-0 text-white"><?php echo h(number_format($report_data['associates']['new_associates'])); ?></h2>
                                        <small><?php echo h($start_date); ?> to <?php echo h($end_date); ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Commissions</h5>
                                        <h2 class="mb-0 text-white">₹<?php echo number_format($report_data['commissions']['total_commissions'], 2); ?></h2>
                                        <small><?php echo number_format($report_data['commissions']['total_transactions']); ?> transactions</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title">Rank Distribution</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0 datatable">
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
                                                <td><strong><?php echo h($rank['level_name']); ?></strong></td>
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
                                <h4 class="card-title">Commission by Type</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0 datatable">
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
                                                <td><strong><?php echo h(ucwords(str_replace('_', ' ', $commission['commission_type']))); ?></strong></td>
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
                                <h4 class="card-title">Top Earners</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0 datatable">
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
                                                <td><strong><?php echo h($earner['full_name']); ?></strong></td>
                                                <td><?php echo h($earner['username']); ?></td>
                                                <td>₹<?php echo h(number_format($earner['total_commissions'], 2)); ?></td>
                                                <td><?php echo h(number_format($earner['transaction_count'])); ?></td>
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
                                <h4 class="card-title">Rank Advancements</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0 datatable">
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
                                                <td><strong><?php echo h($advancement['level_name']); ?></strong></td>
                                                <td><?php echo h(number_format($advancement['promotions'])); ?></td>
                                                <td>₹<?php echo h(number_format($advancement['avg_business'], 2)); ?></td>
                                                <td><?php echo h(number_format($advancement['avg_team_size'])); ?></td>
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
                                <h4 class="card-title">Top Performing Teams</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-center mb-0 datatable">
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
                                                <td><strong><?php echo h($team['team_leader']); ?></strong></td>
                                                <td><?php echo h($team['username']); ?></td>
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
                            <i class="fa fa-info-circle"></i>
                            Report type "<?php echo h($report_type); ?>" is available for export only.
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
                        <h4 class="card-title">Export Report</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="?report=<?php echo h($report_type); ?>&start_date=<?php echo h($start_date); ?>&end_date=<?php echo h($end_date); ?>&export=csv"
                                   class="btn btn-success btn-block">
                                    <i class="fa fa-file-excel-o"></i> Export as CSV
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="?report=<?php echo h($report_type); ?>&start_date=<?php echo h($start_date); ?>&end_date=<?php echo h($end_date); ?>&export=pdf"
                                   class="btn btn-danger btn-block">
                                    <i class="fa fa-file-pdf-o"></i> Export as PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$page_specific_js = "
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Professional MLM Reports loaded');
});
</script>";
include 'admin_footer.php';
?>
