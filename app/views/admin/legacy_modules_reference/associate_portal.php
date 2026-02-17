<?php
/**
 * Complete Associate Portal - APS Dream Homes
 * Ultimate MLM Associate Management System
 * All Features: Dashboard, Profile, Business Reports, Team Management, Customers, Commissions, Support
 */

require_once __DIR__ . '/core/init.php';

// Check if associate is logged in
if (!isAuthenticated() || (getAuthRole() !== 'associate' && getAuthRole() !== 'admin')) {
    header("Location: associate_login.php");
    exit();
}

// Initialize database connection
$db = \App\Core\App::database();

$associate_id = getAuthUserId();
$associate_name = getAuthFullName();
$associate_level = getAuthSubRole();
$associate_email = getAuthUserEmail();

// Get comprehensive associate data
try {
    $sql = "SELECT a.*, u.uname as full_name, u.uemail as email, u.uphone as mobile
            FROM associates a
            JOIN user u ON a.user_id = u.uid
            WHERE a.id = ?";
    $associate_data = $db->fetch($sql, [$associate_id]);
} catch (Exception $e) {
    error_log("Error fetching associate data: " . $e->getMessage());
    $associate_data = [];
}

// Get dashboard statistics
$stats = getAssociateStats($associate_id);

// Level targets and progress
$level_targets = [
    'Associate' => ['min' => 0, 'max' => 1000000, 'commission' => 5, 'reward' => 'Mobile'],
    'Sr. Associate' => ['min' => 1000000, 'max' => 3500000, 'commission' => 7, 'reward' => 'Tablet'],
    'BDM' => ['min' => 3500000, 'max' => 7000000, 'commission' => 10, 'reward' => 'Laptop'],
    'Sr. BDM' => ['min' => 7000000, 'max' => 15000000, 'commission' => 12, 'reward' => 'Tour'],
    'Vice President' => ['min' => 15000000, 'max' => 30000000, 'commission' => 15, 'reward' => 'Bike'],
    'President' => ['min' => 30000000, 'max' => 50000000, 'commission' => 18, 'reward' => 'Bullet'],
    'Site Manager' => ['min' => 50000000, 'max' => 999999999, 'commission' => 20, 'reward' => 'Car']
];

$current_level_info = $level_targets[$associate_level] ?? $level_targets['Associate'];
$progress_percentage = 0;
if ($current_level_info['max'] > $current_level_info['min']) {
    $progress_percentage = min(100, (($stats['total_business'] - $current_level_info['min']) / ($current_level_info['max'] - $current_level_info['min'])) * 100);
}

// Get data for all sections
$profile_data = getAssociateProfile($associate_id);
$business_reports = getBusinessReports($associate_id);
$team_data = getTeamData($associate_id);
$customer_data = getCustomerData($associate_id);
$commission_data = getCommissionData($associate_id);
$support_tickets = getSupportTickets($associate_id);
$recent_activities = getRecentActivities($associate_id);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Security validation failed. Please try again.";
        header("Location: associate_portal.php");
        exit();
    }

    if (isset($_POST['update_profile'])) {
        handleProfileUpdate($associate_id, $_POST);
    } elseif (isset($_POST['create_support_ticket'])) {
        handleSupportTicket($associate_id, $_POST);
    } elseif (isset($_POST['update_commission_settings'])) {
        handleCommissionSettings($associate_id, $_POST);
    }
}

function getAssociateStats($associate_id) {
    $db = \App\Core\App::database();
    $stats = [];

    try {
        // Total Business Volume
        $business_query = "SELECT COALESCE(SUM(total_amount), 0) as total_business FROM plot_bookings WHERE associate_id = ? AND status IN ('confirmed', 'completed')";
        $stats['total_business'] = $db->fetch($business_query, [$associate_id])['total_business'];

        // Total Commissions Earned
        $commission_query = "SELECT COALESCE(SUM(commission_amount), 0) as total_commission FROM mlm_commissions WHERE associate_id = ? AND status = 'paid'";
        $stats['total_commission'] = $db->fetch($commission_query, [$associate_id])['total_commission'];

        // Direct Team Members
        $direct_team_query = "SELECT COUNT(*) as direct_team FROM associates WHERE sponsor_id = ? AND status = 'active'";
        $stats['direct_team'] = $db->fetch($direct_team_query, [$associate_id])['direct_team'];

        // Total Team Size (including indirect)
        $total_team_query = "WITH RECURSIVE team_tree AS (
            SELECT id, sponsor_id, 1 as level FROM associates WHERE sponsor_id = ? AND status = 'active'
            UNION ALL
            SELECT m.id, m.sponsor_id, t.level + 1 FROM associates m
            JOIN team_tree t ON m.sponsor_id = t.id WHERE m.status = 'active'
        )
        SELECT COUNT(*) as total_team FROM team_tree";
        $stats['total_team'] = $db->fetch($total_team_query, [$associate_id])['total_team'];

        // Monthly Business
        $monthly_query = "SELECT COALESCE(SUM(total_amount), 0) as monthly_business FROM plot_bookings
                          WHERE associate_id = ? AND status IN ('confirmed', 'completed')
                          AND booking_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
        $stats['monthly_business'] = $db->fetch($monthly_query, [$associate_id])['monthly_business'];

        // Pending Commissions
        $pending_query = "SELECT COALESCE(SUM(commission_amount), 0) as pending_commission FROM mlm_commissions WHERE associate_id = ? AND status = 'pending'";
        $stats['pending_commission'] = $db->fetch($pending_query, [$associate_id])['pending_commission'];

        // Active Customers
        $customers_query = "SELECT COUNT(DISTINCT customer_id) as active_customers FROM plot_bookings WHERE associate_id = ? AND status IN ('confirmed', 'completed')";
        $stats['active_customers'] = $db->fetch($customers_query, [$associate_id])['active_customers'];

        // This Month Customers
        $monthly_customers_query = "SELECT COUNT(DISTINCT customer_id) as monthly_customers FROM plot_bookings
                                   WHERE associate_id = ? AND status IN ('confirmed', 'completed')
                                   AND booking_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
        $stats['monthly_customers'] = $db->fetch($monthly_customers_query, [$associate_id])['monthly_customers'];

        // Conversion Rate
        $total_leads_query = "SELECT COUNT(*) as total_leads FROM leads WHERE associate_id = ?";
        $total_leads = $db->fetch($total_leads_query, [$associate_id])['total_leads'];

        $converted_leads_query = "SELECT COUNT(DISTINCT l.id) as converted_leads
                                 FROM leads l JOIN plot_bookings b ON l.id = b.lead_id
                                 WHERE b.associate_id = ? AND b.status IN ('confirmed', 'completed')";
        $converted_leads = $db->fetch($converted_leads_query, [$associate_id])['converted_leads'];

        $stats['conversion_rate'] = $total_leads > 0 ? round(($converted_leads / $total_leads) * 100, 2) : 0;

        // Average Deal Size
        $avg_deal_query = "SELECT COALESCE(AVG(total_amount), 0) as avg_deal_size FROM plot_bookings WHERE associate_id = ? AND status IN ('confirmed', 'completed')";
        $stats['avg_deal_size'] = $db->fetch($avg_deal_query, [$associate_id])['avg_deal_size'];

    } catch (Exception $e) {
        error_log("Error fetching dashboard stats: " . $e->getMessage());
        $stats = array_fill_keys(['total_business', 'total_commission', 'direct_team', 'total_team', 'monthly_business', 'pending_commission', 'active_customers', 'monthly_customers', 'conversion_rate', 'avg_deal_size'], 0);
    }

    return $stats;
}

function getAssociateProfile($associate_id) {
    $db = \App\Core\App::database();
    try {
        $query = "SELECT a.*, u.uname as full_name, u.uemail as email, u.uphone as mobile,
                         u.bank_name, u.account_number, u.ifsc_code, u.pan, u.adhaar, u.address
                  FROM associates a
                  JOIN user u ON a.user_id = u.uid
                  WHERE a.id = ?";
        return $db->fetch($query, [$associate_id]);
    } catch (Exception $e) {
        error_log("Error fetching profile: " . $e->getMessage());
        return [];
    }
}

function getBusinessReports($associate_id) {
    $db = \App\Core\App::database();
    $reports = [];

    try {
        // Monthly breakdown for the last 12 months
        $monthly_query = "SELECT DATE_FORMAT(booking_date, '%Y-%m') as month,
                                 SUM(total_amount) as monthly_volume,
                                 COUNT(*) as bookings_count
                          FROM plot_bookings
                          WHERE associate_id = ? AND status IN ('confirmed', 'completed')
                          GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
                          ORDER BY month DESC LIMIT 12";
        $reports['monthly_breakdown'] = $db->fetchAll($monthly_query, [$associate_id]);

        // Property type performance
        $property_query = "SELECT p.type as property_type, COUNT(*) as count, SUM(b.total_amount) as volume
                          FROM plot_bookings b
                          JOIN plots p ON b.plot_id = p.id
                          WHERE b.associate_id = ? AND b.status IN ('confirmed', 'completed')
                          GROUP BY p.type
                          ORDER BY volume DESC";
        $reports['property_performance'] = $db->fetchAll($property_query, [$associate_id]);

        // Lead conversion funnel
        $funnel_query = "SELECT
                            COUNT(*) as total_leads,
                            SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted,
                            SUM(CASE WHEN status = 'qualified' THEN 1 ELSE 0 END) as qualified,
                            SUM(CASE WHEN status = 'proposal' THEN 1 ELSE 0 END) as proposal,
                            SUM(CASE WHEN status = 'negotiation' THEN 1 ELSE 0 END) as negotiation,
                            SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
                         FROM leads WHERE associate_id = ?";
        $reports['lead_funnel'] = $db->fetch($funnel_query, [$associate_id]);

        // Commission trends
        $commission_query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                                    SUM(commission_amount) as monthly_commission
                             FROM mlm_commissions
                             WHERE associate_id = ? AND status = 'paid'
                             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                             ORDER BY month DESC LIMIT 12";
        $reports['commission_trends'] = $db->fetchAll($commission_query, [$associate_id]);

    } catch (Exception $e) {
        error_log("Error fetching business reports: " . $e->getMessage());
        $reports = [];
    }

    return $reports;
}

function getTeamData($associate_id) {
    $db = \App\Core\App::database();
    $team_data = [];

    try {
        // Direct team members
        $direct_query = "SELECT a.id, u.uname as full_name, u.uphone as mobile, u.uemail as email, a.current_level,
                                a.created_at as registration_date, a.status
                         FROM associates a
                         JOIN user u ON a.user_id = u.uid
                         WHERE a.sponsor_id = ?
                         ORDER BY a.created_at DESC";
        $team_data['direct_team'] = $db->fetchAll($direct_query, [$associate_id]);

        // Team hierarchy (3 levels deep)
        $hierarchy_query = "WITH RECURSIVE team_hierarchy AS (
            SELECT a.id, u.uname as full_name, a.sponsor_id, a.current_level, 1 as level
            FROM associates a
            JOIN user u ON a.user_id = u.uid
            WHERE a.sponsor_id = ? AND a.status = 'active'
            UNION ALL
            SELECT a.id, u.uname as full_name, a.sponsor_id, a.current_level, th.level + 1
            FROM associates a
            JOIN user u ON a.user_id = u.uid
            JOIN team_hierarchy th ON a.sponsor_id = th.id
            WHERE a.status = 'active' AND th.level < 3
        )
        SELECT * FROM team_hierarchy ORDER BY level, full_name";
        $team_data['hierarchy'] = $db->fetchAll($hierarchy_query, [$associate_id]);

        // Team performance
        $performance_query = "SELECT
                                COUNT(*) as total_members,
                                COUNT(CASE WHEN current_level != 'Associate' THEN 1 END) as promoted_members
                              FROM associates WHERE sponsor_id = ? AND status = 'active'";
        $team_data['performance'] = $db->fetch($performance_query, [$associate_id]);

    } catch (Exception $e) {
        error_log("Error fetching team data: " . $e->getMessage());
        $team_data = [];
    }

    return $team_data;
}

function getCustomerData($associate_id) {
    $db = \App\Core\App::database();
    $customer_data = [];

    try {
        // Recent customers
        $recent_query = "SELECT c.id, c.name as customer_name, c.email, c.phone, c.city,
                                b.total_amount, b.booking_amount as paid_amount, b.booking_date, b.status,
                                p.plot_number as property_title
                         FROM customers c
                         JOIN plot_bookings b ON c.id = b.customer_id
                         LEFT JOIN plots p ON b.plot_id = p.id
                         WHERE b.associate_id = ?
                         ORDER BY b.booking_date DESC LIMIT 10";
        $customer_data['recent_customers'] = $db->fetchAll($recent_query, [$associate_id]);

        // Customer statistics
        $stats_query = "SELECT
                           COUNT(DISTINCT c.id) as total_customers,
                           SUM(b.total_amount) as total_booking_value,
                           AVG(b.total_amount) as avg_booking_value,
                           COUNT(CASE WHEN b.status = 'completed' THEN 1 END) as completed_bookings,
                           COUNT(CASE WHEN b.status = 'pending' THEN 1 END) as pending_bookings,
                           COUNT(CASE WHEN b.status = 'cancelled' THEN 1 END) as cancelled_bookings
                        FROM customers c
                        JOIN plot_bookings b ON c.id = b.customer_id
                        WHERE b.associate_id = ?";
        $customer_data['statistics'] = $db->fetch($stats_query, [$associate_id]);

        // Payment tracking
        $payment_query = "SELECT
                             SUM(b.total_amount - b.booking_amount) as total_pending,
                             SUM(b.booking_amount) as total_received,
                             COUNT(CASE WHEN b.total_amount = b.booking_amount THEN 1 END) as fully_paid,
                             COUNT(CASE WHEN b.total_amount > b.booking_amount THEN 1 END) as partially_paid
                          FROM plot_bookings b WHERE b.associate_id = ?";
        $customer_data['payment_tracking'] = $db->fetch($payment_query, [$associate_id]);

    } catch (Exception $e) {
        error_log("Error fetching customer data: " . $e->getMessage());
        $customer_data = [];
    }

    return $customer_data;
}

function getCommissionData($associate_id) {
    $db = \App\Core\App::database();
    $commission_data = [];

    try {
        // Commission summary
        $summary_query = "SELECT
                             status,
                             COUNT(*) as count,
                             SUM(commission_amount) as total_amount
                          FROM mlm_commissions
                          WHERE associate_id = ?
                          GROUP BY status";
        $commission_data['summary'] = $db->fetchAll($summary_query, [$associate_id]);

        // Recent commissions
        $recent_query = "SELECT
                            id, commission_amount, commission_type, description,
                            status, created_at, paid_at, booking_id
                         FROM mlm_commissions
                         WHERE associate_id = ?
                         ORDER BY created_at DESC LIMIT 10";
        $commission_data['recent'] = $db->fetchAll($recent_query, [$associate_id]);

        // Commission by type
        $type_query = "SELECT
                          commission_type,
                          COUNT(*) as count,
                          SUM(commission_amount) as total_amount
                       FROM mlm_commissions
                       WHERE associate_id = ? AND status = 'paid'
                       GROUP BY commission_type
                       ORDER BY total_amount DESC";
        $commission_data['by_type'] = $db->fetchAll($type_query, [$associate_id]);

        // Monthly commission trends
        $monthly_query = "SELECT
                             DATE_FORMAT(created_at, '%Y-%m') as month,
                             SUM(commission_amount) as monthly_commission,
                             COUNT(*) as commission_count
                          FROM mlm_commissions
                          WHERE associate_id = ? AND status = 'paid'
                          GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                          ORDER BY month DESC LIMIT 12";
        $commission_data['monthly_trends'] = $db->fetchAll($monthly_query, [$associate_id]);

    } catch (Exception $e) {
        error_log("Error fetching commission data: " . $e->getMessage());
        $commission_data = [];
    }

    return $commission_data;
}

function getSupportTickets($associate_id) {
    $db = \App\Core\App::database();
    try {
        $query = "SELECT
                     id, subject, message, priority, status, category,
                     created_at, updated_at, admin_response
                  FROM support_tickets
                  WHERE associate_id = ?
                  ORDER BY created_at DESC";
        return $db->fetchAll($query, [$associate_id]);
    } catch (Exception $e) {
        error_log("Error fetching support tickets: " . $e->getMessage());
        return [];
    }
}

function getRecentActivities($associate_id) {
    $db = \App\Core\App::database();
    $activities = [];

    try {
        // Recent bookings
        $booking_query = "SELECT 'booking' as type, pb.booking_date as date,
                                 CONCAT('New booking: ', c.name, ' - ₹', pb.total_amount) as description
                          FROM plot_bookings pb
                          JOIN customers c ON pb.customer_id = c.id
                          WHERE pb.associate_id = ?
                          ORDER BY pb.booking_date DESC LIMIT 3";
        $activities = array_merge($activities, $db->fetchAll($booking_query, [$associate_id]));

        // Recent commissions
        $commission_query = "SELECT 'commission' as type, created_at as date,
                                   CONCAT('Commission earned: ₹', commission_amount, ' - ', commission_type) as description
                            FROM mlm_commissions
                            WHERE associate_id = ? AND status = 'paid'
                            ORDER BY created_at DESC LIMIT 3";
        $activities = array_merge($activities, $db->fetchAll($commission_query, [$associate_id]));

        // Recent team additions
        $team_query = "SELECT 'team' as type, a.created_at as date,
                             CONCAT('New team member: ', u.uname) as description
                      FROM associates a
                      JOIN user u ON a.user_id = u.uid
                      WHERE a.sponsor_id = ?
                      ORDER BY a.created_at DESC LIMIT 3";
        $activities = array_merge($activities, $db->fetchAll($team_query, [$associate_id]));

        // Sort by date
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 10);

    } catch (Exception $e) {
        error_log("Error fetching recent activities: " . $e->getMessage());
        return [];
    }
}

function handleProfileUpdate($associate_id, $data) {
    $db = \App\Core\App::database();
    try {
        // Get user_id first
        $user_query = "SELECT user_id FROM associates WHERE id = ?";
        $user_data = $db->fetch($user_query, [$associate_id]);
        if (!$user_data) return;
        $user_id = $user_data['user_id'];

        $update_fields = [];
        $params = [];

        if (!empty($data['full_name'])) {
            $update_fields[] = "uname = ?";
            $params[] = $data['full_name'];
        }

        if (!empty($data['email'])) {
            $update_fields[] = "uemail = ?";
            $params[] = $data['email'];
        }

        if (!empty($data['mobile'])) {
            $update_fields[] = "uphone = ?";
            $params[] = $data['mobile'];
        }

        if (!empty($data['address'])) {
            $update_fields[] = "address = ?";
            $params[] = $data['address'];
        }

        if (!empty($update_fields)) {
            $params[] = $user_id;
            $query = "UPDATE user SET " . implode(", ", $update_fields) . " WHERE uid = ?";

            if ($db->query($query, $params)) {
                $_SESSION['success_message'] = "Profile updated successfully!";
                header("Location: associate_portal.php?section=profile");
                exit();
            }
        }

    } catch (Exception $e) {
        error_log("Error updating profile: " . $e->getMessage());
        $_SESSION['error_message'] = "Error updating profile. Please try again.";
    }
}

function handleSupportTicket($associate_id, $data) {
    $db = \App\Core\App::database();
    try {
        $query = "INSERT INTO support_tickets (associate_id, subject, message, priority, category, status, created_at)
                  VALUES (?, ?, ?, ?, ?, 'open', NOW())";

        if ($db->query($query, [$associate_id, $data['subject'], $data['message'], $data['priority'], $data['category']])) {
            $_SESSION['success_message'] = "Support ticket created successfully!";
            header("Location: associate_portal.php?section=support");
            exit();
        }

    } catch (Exception $e) {
        error_log("Error creating support ticket: " . $e->getMessage());
        $_SESSION['error_message'] = "Error creating support ticket. Please try again.";
    }
}

function handleCommissionSettings($associate_id, $data) {
    // Handle commission-related settings
    $_SESSION['success_message'] = "Commission settings updated successfully!";
    header("Location: associate_portal.php?section=commissions");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associate Portal - APS Dream Homes</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

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

        .main-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 20px 0;
            overflow: hidden;
        }

        .top-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .sidebar {
            background: white;
            border-right: 1px solid #eee;
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: #333;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f8f9fa;
            transition: all 0.3s;
            font-weight: 500;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }

        .stats-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-align: center;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0,0,0,0.15);
        }

        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        .level-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .progress {
            height: 10px;
            border-radius: 5px;
        }

        .quick-action-btn {
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
            border: none;
            width: 100%;
            font-weight: 500;
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-active { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-completed { background-color: #d1ecf1; color: #0c5460; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
            font-weight: 600;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
        }

        .referral-code {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .activity-item {
            padding: 1rem;
            border-left: 4px solid var(--primary-color);
            background: #f8f9fa;
            border-radius: 0 10px 10px 0;
            margin-bottom: 1rem;
        }

        .activity-item i {
            color: var(--primary-color);
            margin-right: 0.5rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -280px;
                width: 280px;
                z-index: 1000;
                transition: all 0.3s ease;
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <button class="btn btn-outline-light me-2 d-lg-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-home me-2"></i>APS Dream Homes
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo h($associate_name); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="shareReferralCode()"><i class="fas fa-share-alt me-2"></i>My Referral</a></li>
                        <li><a class="dropdown-item" href="associate_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 sidebar d-none d-lg-block" id="sidebar">
                <div class="p-3">
                    <div class="text-center mb-4">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <h6 class="mt-2 mb-0"><?php echo h($associate_name); ?></h6>
                        <small class="text-muted"><?php echo $associate_level; ?></small>
                    </div>

                    <nav class="nav nav-pills flex-column">
                        <a href="?section=dashboard" class="nav-link <?php echo (!isset($_GET['section']) || $_GET['section'] == 'dashboard') ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="?section=profile" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'profile') ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="?section=business" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'business') ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i> Business Reports
                        </a>
                        <a href="?section=team" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'team') ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i> Team Management
                        </a>
                        <a href="?section=customers" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'customers') ? 'active' : ''; ?>">
                            <i class="fas fa-user-friends"></i> Customers
                        </a>
                        <a href="?section=commissions" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'commissions') ? 'active' : ''; ?>">
                            <i class="fas fa-rupee-sign"></i> Commissions
                        </a>
                        <a href="?section=support" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'support') ? 'active' : ''; ?>">
                            <i class="fas fa-headset"></i> Support
                        </a>
                        <hr>
                        <a href="#" onclick="shareReferralCode()" class="nav-link">
                            <i class="fas fa-share-alt"></i> Share Referral
                        </a>
                        <a href="properties.php" target="_blank" class="nav-link">
                            <i class="fas fa-building"></i> View Properties
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 col-12">
                <div class="main-container">
                    <!-- Header -->
                    <div class="top-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h1 class="mb-2">Welcome back, <?php echo h($associate_name); ?>!</h1>
                                <p class="mb-0">
                                    <span class="level-badge"><?php echo $associate_level; ?></span>
                                    <span class="ms-3">Referral Code: <strong><?php echo $associate_data['referral_code'] ?? 'N/A'; ?></strong></span>
                                </p>
                            </div>
                            <div class="col-md-6 text-end">
                                <button class="btn btn-light btn-lg" onclick="shareReferralCode()">
                                    <i class="fas fa-share-alt me-2"></i>Share Referral
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <!-- Success/Error Messages -->
                        <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Dashboard Section -->
                        <?php if (!isset($_GET['section']) || $_GET['section'] == 'dashboard'): ?>
                        <!-- Key Performance Stats -->
                        <div class="row mb-4">
                            <div class="col-xl-3 col-lg-6 mb-3">
                                <div class="stats-card">
                                    <i class="fas fa-chart-line stats-icon"></i>
                                    <h2>₹<?php echo number_format($stats['total_business']); ?></h2>
                                    <h5>Total Business Volume</h5>
                                    <small>This Month: ₹<?php echo number_format($stats['monthly_business']); ?></small>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 mb-3">
                                <div class="stats-card">
                                    <i class="fas fa-rupee-sign stats-icon"></i>
                                    <h2>₹<?php echo number_format($stats['total_commission']); ?></h2>
                                    <h5>Total Commissions</h5>
                                    <small>Pending: ₹<?php echo number_format($stats['pending_commission']); ?></small>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 mb-3">
                                <div class="stats-card">
                                    <i class="fas fa-users stats-icon"></i>
                                    <h2><?php echo $stats['direct_team']; ?></h2>
                                    <h5>Direct Team</h5>
                                    <small>Total Team: <?php echo $stats['total_team']; ?></small>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 mb-3">
                                <div class="stats-card">
                                    <i class="fas fa-user-friends stats-icon"></i>
                                    <h2><?php echo $stats['active_customers']; ?></h2>
                                    <h5>Active Customers</h5>
                                    <small>This Month: <?php echo $stats['monthly_customers']; ?></small>
                                </div>
                            </div>
                        </div>

                        <!-- Level Progress -->
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Level Progress</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6>Current Level: <span class="text-primary"><?php echo $associate_level; ?></span></h6>
                                            <small class="text-muted">Progress to next level</small>
                                        </div>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-primary" role="progressbar"
                                                 style="width: <?php echo $progress_percentage; ?>%"
                                                 aria-valuenow="<?php echo $progress_percentage; ?>"
                                                 aria-valuemin="0" aria-valuemax="100">
                                                <?php echo round($progress_percentage, 1); ?>%
                                            </div>
                                        </div>
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <strong>Current</strong><br>
                                                ₹<?php echo number_format($stats['total_business']); ?>
                                            </div>
                                            <div class="col-4">
                                                <strong>Target</strong><br>
                                                ₹<?php echo number_format($current_level_info['max']); ?>
                                            </div>
                                            <div class="col-4">
                                                <strong>Reward</strong><br>
                                                <?php echo $current_level_info['reward']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                                    </div>
                                    <div class="card-body">
                                        <button class="btn btn-primary quick-action-btn" onclick="shareReferralCode()">
                                            <i class="fas fa-share-alt me-2"></i>Share Referral Link
                                        </button>
                                        <button class="btn btn-success quick-action-btn" onclick="viewProjects()">
                                            <i class="fas fa-building me-2"></i>View Properties
                                        </button>
                                        <button class="btn btn-info quick-action-btn" onclick="downloadBrochure()">
                                            <i class="fas fa-download me-2"></i>Download Materials
                                        </button>
                                        <button class="btn btn-warning quick-action-btn" onclick="viewReports()">
                                            <i class="fas fa-chart-bar me-2"></i>View Reports
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Section -->
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Business Performance</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="businessChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Team Distribution</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="teamChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($recent_activities)): ?>
                                        <?php foreach ($recent_activities as $activity): ?>
                                        <div class="activity-item">
                                            <i class="fas fa-<?php
                                                echo $activity['type'] == 'booking' ? 'calendar-plus' :
                                                     ($activity['type'] == 'commission' ? 'rupee-sign' : 'users');
                                            ?>"></i>
                                            <strong><?php echo h($activity['description']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y H:i', strtotime($activity['date'])); ?>
                                            </small>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No recent activity</h6>
                                            <p class="text-muted">Activity will appear here as you work</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php endif; ?>

                        <!-- Profile Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'profile'): ?>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <?= getCsrfField() ?>
                                            <input type="hidden" name="update_profile" value="1">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Full Name</label>
                                                    <input type="text" class="form-control" name="full_name"
                                                           value="<?php echo h($profile_data['full_name'] ?? ''); ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" class="form-control" name="email"
                                                           value="<?php echo h($profile_data['email'] ?? ''); ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Mobile</label>
                                                    <input type="tel" class="form-control" name="mobile"
                                                           value="<?php echo h($profile_data['mobile'] ?? ''); ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Current Level</label>
                                                    <input type="text" class="form-control" readonly
                                                           value="<?php echo h($associate_level); ?>">
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Address</label>
                                                    <textarea class="form-control" name="address" rows="3"><?php
                                                        echo h($profile_data['address'] ?? '');
                                                    ?></textarea>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">City</label>
                                                    <input type="text" class="form-control" name="city"
                                                           value="<?php echo h($profile_data['city'] ?? ''); ?>">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">State</label>
                                                    <input type="text" class="form-control" name="state"
                                                           value="<?php echo h($profile_data['state'] ?? ''); ?>">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Pincode</label>
                                                    <input type="text" class="form-control" name="pincode"
                                                           value="<?php echo h($profile_data['pincode'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save me-2"></i>Update Profile
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Profile Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-4">
                                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                                <i class="fas fa-user fa-2x"></i>
                                            </div>
                                            <h5 class="mt-3"><?php echo h($associate_name); ?></h5>
                                            <p class="text-muted"><?php echo $associate_level; ?></p>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Associate ID:</strong> <?php echo $associate_id; ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Referral Code:</strong>
                                            <div class="referral-code mt-2">
                                                <?php echo $associate_data['referral_code'] ?? 'REF123'; ?>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Registration Date:</strong><br>
                                            <?php echo date('M d, Y', strtotime($profile_data['registration_date'] ?? date('Y-m-d'))); ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Total Business:</strong><br>
                                            ₹<?php echo number_format($stats['total_business']); ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Commission Rate:</strong><br>
                                            <?php echo $current_level_info['commission']; ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Business Reports Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'business'): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Business Reports</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <h6>Monthly Performance</h6>
                                                <div class="chart-container">
                                                    <canvas id="monthlyChart"></canvas>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <h6>Commission Trends</h6>
                                                <div class="chart-container">
                                                    <canvas id="commissionChart"></canvas>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <div class="row">
                                            <div class="col-lg-6">
                                                <h6>Property Performance</h6>
                                                <?php if (!empty($business_reports['property_performance'])): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Property Type</th>
                                                                <th>Bookings</th>
                                                                <th>Volume</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($business_reports['property_performance'] as $property): ?>
                                                            <tr>
                                                                <td><?php echo h($property['property_type']); ?></td>
                                                                <td><?php echo $property['count']; ?></td>
                                                                <td>₹<?php echo number_format($property['volume']); ?></td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php else: ?>
                                                <p class="text-muted">No property performance data available</p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-lg-6">
                                                <h6>Lead Conversion Funnel</h6>
                                                <?php if (isset($business_reports['lead_funnel'])): ?>
                                                <div class="row text-center">
                                                    <div class="col-6">
                                                        <div class="card bg-light">
                                                            <div class="card-body">
                                                                <h4><?php echo $business_reports['lead_funnel']['total_leads']; ?></h4>
                                                                <p class="mb-0">Total Leads</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="card bg-light">
                                                            <div class="card-body">
                                                                <h4><?php echo $business_reports['lead_funnel']['closed']; ?></h4>
                                                                <p class="mb-0">Closed Deals</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <p><strong>Conversion Rate:</strong> <?php
                                                        echo $business_reports['lead_funnel']['total_leads'] > 0 ?
                                                             round(($business_reports['lead_funnel']['closed'] / $business_reports['lead_funnel']['total_leads']) * 100, 2) : 0;
                                                    ?>%</p>
                                                </div>
                                                <?php else: ?>
                                                <p class="text-muted">No lead funnel data available</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Team Management Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'team'): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Team Management</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-4">
                                            <div class="col-lg-3">
                                                <div class="card bg-primary text-white">
                                                    <div class="card-body text-center">
                                                        <h4><?php echo $stats['direct_team']; ?></h4>
                                                        <p class="mb-0">Direct Team</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="card bg-success text-white">
                                                    <div class="card-body text-center">
                                                        <h4><?php echo $stats['total_team']; ?></h4>
                                                        <p class="mb-0">Total Team</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="card bg-info text-white">
                                                    <div class="card-body text-center">
                                                        <h4>₹<?php echo number_format($team_data['performance']['team_volume'] ?? 0); ?></h4>
                                                        <p class="mb-0">Team Volume</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="card bg-warning text-white">
                                                    <div class="card-body text-center">
                                                        <h4><?php echo $team_data['performance']['promoted_members'] ?? 0; ?></h4>
                                                        <p class="mb-0">Promoted</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-6">
                                                <h6>Direct Team Members</h6>
                                                <?php if (!empty($team_data['direct_team'])): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Name</th>
                                                                <th>Level</th>
                                                                <th>Business</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($team_data['direct_team'] as $member): ?>
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                                                            <i class="fas fa-user"></i>
                                                                        </div>
                                                                        <div>
                                                                            <strong><?php echo h($member['full_name']); ?></strong><br>
                                                                            <small class="text-muted"><?php echo h($member['mobile']); ?></small>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-primary"><?php echo $member['current_level']; ?></span>
                                                                </td>
                                                                <td>₹<?php echo number_format($member['total_business']); ?></td>
                                                                <td>
                                                                    <span class="status-badge status-<?php echo $member['status'] == 'active' ? 'active' : 'pending'; ?>">
                                                                        <?php echo ucfirst($member['status']); ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php else: ?>
                                                <p class="text-muted">No direct team members yet</p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-lg-6">
                                                <h6>Team Hierarchy</h6>
                                                <?php if (!empty($team_data['hierarchy'])): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Name</th>
                                                                <th>Level</th>
                                                                <th>Business</th>
                                                                <th>Depth</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($team_data['hierarchy'] as $member): ?>
                                                            <tr>
                                                                <td>
                                                                    <div style="margin-left: <?php echo ($member['level'] - 1) * 20; ?>px;">
                                                                        <?php if ($member['level'] > 1): ?>
                                                                        <i class="fas fa-level-down-alt text-muted me-1"></i>
                                                                        <?php endif; ?>
                                                                        <?php echo h($member['full_name']); ?>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-info"><?php echo $member['current_level']; ?></span>
                                                                </td>
                                                                <td>₹<?php echo number_format($member['total_business']); ?></td>
                                                                <td>Level <?php echo $member['level']; ?></td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php else: ?>
                                                <p class="text-muted">No team hierarchy data available</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Customers Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'customers'): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-user-friends me-2"></i>Customer Management</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-4">
                                            <div class="col-lg-3">
                                                <div class="card bg-primary text-white">
                                                    <div class="card-body text-center">
                                                        <h4><?php echo $customer_data['statistics']['total_customers'] ?? 0; ?></h4>
                                                        <p class="mb-0">Total Customers</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="card bg-success text-white">
                                                    <div class="card-body text-center">
                                                        <h4>₹<?php echo number_format($customer_data['statistics']['total_booking_value'] ?? 0); ?></h4>
                                                        <p class="mb-0">Total Value</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="card bg-info text-white">
                                                    <div class="card-body text-center">
                                                        <h4><?php echo $customer_data['statistics']['completed_bookings'] ?? 0; ?></h4>
                                                        <p class="mb-0">Completed</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="card bg-warning text-white">
                                                    <div class="card-body text-center">
                                                        <h4>₹<?php echo number_format($customer_data['payment_tracking']['total_pending'] ?? 0); ?></h4>
                                                        <p class="mb-0">Pending</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <h6>Recent Customers</h6>
                                                <?php if (!empty($customer_data['recent_customers'])): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover" id="customersTable">
                                                        <thead>
                                                            <tr>
                                                                <th>Customer</th>
                                                                <th>Property</th>
                                                                <th>Amount</th>
                                                                <th>Status</th>
                                                                <th>Date</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($customer_data['recent_customers'] as $customer): ?>
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                                            <i class="fas fa-user"></i>
                                                                        </div>
                                                                        <div>
                                                                            <strong><?php echo h($customer['customer_name']); ?></strong><br>
                                                                            <small class="text-muted"><?php echo h($customer['email']); ?></small>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td><?php echo h($customer['property_title'] ?? 'N/A'); ?></td>
                                                                <td>
                                                                    <strong>₹<?php echo number_format($customer['total_amount']); ?></strong><br>
                                                                    <small class="text-<?php echo $customer['total_amount'] == $customer['paid_amount'] ? 'success' : 'warning'; ?>">
                                                                        <?php echo $customer['total_amount'] == $customer['paid_amount'] ? 'Paid' : '₹' . number_format($customer['total_amount'] - $customer['paid_amount']) . ' pending'; ?>
                                                                    </small>
                                                                </td>
                                                                <td>
                                                                    <span class="status-badge status-<?php echo strtolower($customer['status']); ?>">
                                                                        <?php echo ucfirst($customer['status']); ?>
                                                                    </span>
                                                                </td>
                                                                <td><?php echo date('M d, Y', strtotime($customer['booking_date'])); ?></td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php else: ?>
                                                <div class="text-center py-4">
                                                    <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                                                    <h6 class="text-muted">No customers yet</h6>
                                                    <p class="text-muted">Start earning by sharing your referral link</p>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Commissions Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'commissions'): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-rupee-sign me-2"></i>Commission Management</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-4">
                                            <div class="col-lg-4">
                                                <div class="card bg-success text-white">
                                                    <div class="card-body text-center">
                                                        <h4>₹<?php echo number_format($stats['total_commission']); ?></h4>
                                                        <p class="mb-0">Total Earned</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="card bg-warning text-white">
                                                    <div class="card-body text-center">
                                                        <h4>₹<?php echo number_format($stats['pending_commission']); ?></h4>
                                                        <p class="mb-0">Pending</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="card bg-info text-white">
                                                    <div class="card-body text-center">
                                                        <h4><?php echo $current_level_info['commission']; ?>%</h4>
                                                        <p class="mb-0">Current Rate</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-6">
                                                <h6>Commission Summary</h6>
                                                <?php if (!empty($commission_data['summary'])): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Status</th>
                                                                <th>Count</th>
                                                                <th>Total Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($commission_data['summary'] as $summary): ?>
                                                            <tr>
                                                                <td>
                                                                    <span class="status-badge status-<?php echo strtolower($summary['status']); ?>">
                                                                        <?php echo ucfirst($summary['status']); ?>
                                                                    </span>
                                                                </td>
                                                                <td><?php echo $summary['count']; ?></td>
                                                                <td>₹<?php echo number_format($summary['total_amount']); ?></td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php else: ?>
                                                <p class="text-muted">No commission data available</p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-lg-6">
                                                <h6>Recent Commissions</h6>
                                                <?php if (!empty($commission_data['recent'])): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Type</th>
                                                                <th>Description</th>
                                                                <th>Amount</th>
                                                                <th>Date</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($commission_data['recent'] as $commission): ?>
                                                            <tr>
                                                                <td>
                                                                    <span class="badge bg-<?php echo $commission['status'] == 'paid' ? 'success' : 'warning'; ?>">
                                                                        <?php echo ucfirst($commission['commission_type']); ?>
                                                                    </span>
                                                                </td>
                                                                <td><?php echo h($commission['description']); ?></td>
                                                                <td class="text-success">+₹<?php echo number_format($commission['commission_amount']); ?></td>
                                                                <td><?php echo date('M d, Y', strtotime($commission['created_at'])); ?></td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php else: ?>
                                                <p class="text-muted">No recent commissions</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Support Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'support'): ?>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create Support Ticket</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <?= getCsrfField() ?>
                                            <input type="hidden" name="create_support_ticket" value="1">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Subject</label>
                                                    <input type="text" class="form-control" name="subject" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Priority</label>
                                                    <select class="form-select" name="priority" required>
                                                        <option value="low">Low</option>
                                                        <option value="medium" selected>Medium</option>
                                                        <option value="high">High</option>
                                                        <option value="urgent">Urgent</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Category</label>
                                                    <select class="form-select" name="category" required>
                                                        <option value="technical">Technical Issue</option>
                                                        <option value="account">Account Problem</option>
                                                        <option value="commission">Commission Issue</option>
                                                        <option value="training">Training Request</option>
                                                        <option value="general">General Inquiry</option>
                                                        <option value="bug">Bug Report</option>
                                                        <option value="feature">Feature Request</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Message</label>
                                                    <textarea class="form-control" name="message" rows="5" required></textarea>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-paper-plane me-2"></i>Submit Ticket
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Support Tickets</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($support_tickets)): ?>
                                        <?php foreach ($support_tickets as $ticket): ?>
                                        <div class="card mb-3 border-<?php
                                            echo $ticket['priority'] == 'urgent' ? 'danger' :
                                                 ($ticket['priority'] == 'high' ? 'warning' : 'info'); ?>">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo h($ticket['subject']); ?></h6>
                                                        <small class="text-muted">
                                                            <?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                    <span class="status-badge status-<?php echo strtolower($ticket['status']); ?>">
                                                        <?php echo ucfirst($ticket['status']); ?>
                                                    </span>
                                                </div>
                                                <p class="mb-2 small"><?php echo h(substr($ticket['message'], 0, 100)); ?>...</p>
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">
                                                        <i class="fas fa-tag me-1"></i><?php echo ucfirst($ticket['category']); ?>
                                                    </small>
                                                    <small class="text-muted">
                                                        <i class="fas fa-exclamation-triangle me-1"></i><?php echo ucfirst($ticket['priority']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No support tickets</h6>
                                            <p class="text-muted">Create your first support ticket</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Initialize DataTables
        document.addEventListener('DOMContentLoaded', function() {
            $('#customersTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[4, 'desc']]
            });
        });

        // Chart data
        const businessData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Business Volume',
                data: [1200000, 1900000, 1500000, 2500000, 2200000, <?php echo $stats['monthly_business']; ?>],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        };

        const teamData = {
            labels: ['Direct', 'Indirect', 'Total'],
            datasets: [{
                data: [<?php echo $stats['direct_team']; ?>, <?php echo $stats['total_team'] - $stats['direct_team']; ?>, <?php echo $stats['total_team']; ?>],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)'
                ],
                borderWidth: 2
            }]
        };

        // Monthly performance chart
        const monthlyData = {
            labels: <?php echo json_encode(array_column($business_reports['monthly_breakdown'] ?? [], 'month')); ?>,
            datasets: [{
                label: 'Monthly Volume',
                data: <?php echo json_encode(array_column($business_reports['monthly_breakdown'] ?? [], 'monthly_volume')); ?>,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        };

        // Commission trends chart
        const commissionData = {
            labels: <?php echo json_encode(array_column($commission_data['monthly_trends'] ?? [], 'month')); ?>,
            datasets: [{
                label: 'Monthly Commissions',
                data: <?php echo json_encode(array_column($commission_data['monthly_trends'] ?? [], 'monthly_commission')); ?>,
                borderColor: 'rgb(255, 193, 7)',
                backgroundColor: 'rgba(255, 193, 7, 0.2)',
                tension: 0.1
            }]
        };

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Business chart
            const businessCtx = document.getElementById('businessChart')?.getContext('2d');
            if (businessCtx) {
                new Chart(businessCtx, {
                    type: 'line',
                    data: businessData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₹' + value.toLocaleString('en-IN');
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '₹' + context.parsed.y.toLocaleString('en-IN');
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Team chart
            const teamCtx = document.getElementById('teamChart')?.getContext('2d');
            if (teamCtx) {
                new Chart(teamCtx, {
                    type: 'doughnut',
                    data: teamData,
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

            // Monthly chart
            const monthlyCtx = document.getElementById('monthlyChart')?.getContext('2d');
            if (monthlyCtx) {
                new Chart(monthlyCtx, {
                    type: 'bar',
                    data: monthlyData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₹' + value.toLocaleString('en-IN');
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '₹' + context.parsed.y.toLocaleString('en-IN');
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Commission chart
            const commissionCtx = document.getElementById('commissionChart')?.getContext('2d');
            if (commissionCtx) {
                new Chart(commissionCtx, {
                    type: 'line',
                    data: commissionData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₹' + value.toLocaleString('en-IN');
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '₹' + context.parsed.y.toLocaleString('en-IN');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });

        // Utility functions
        function shareReferralCode() {
            const referralCode = '<?php echo $associate_data['referral_code'] ?? 'REF123'; ?>';
            const modal = new bootstrap.Modal(document.createElement('div'));
            const modalContent = `
                <div class="modal fade" id="referralModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title"><i class="fas fa-share-alt me-2"></i>Your Referral Details</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="referral-code">
                                            ${referralCode}
                                        </div>
                                        <p class="mt-3 text-center">Share this code with potential associates</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Referral Link</h6>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="referralLink"
                                                   value="${window.location.origin}/associate_registration.php?ref=${referralCode}" readonly>
                                            <button class="btn btn-primary" onclick="copyReferralLink()">Copy</button>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-success" onclick="shareWhatsApp('${referralCode}')">
                                                <i class="fab fa-whatsapp me-2"></i>Share on WhatsApp
                                            </button>
                                            <button class="btn btn-info" onclick="shareTelegram('${referralCode}')">
                                                <i class="fab fa-telegram me-2"></i>Share on Telegram
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalContent);
            new bootstrap.Modal(document.getElementById('referralModal')).show();
        }

        function copyReferralLink() {
            const linkInput = document.getElementById('referralLink');
            linkInput.select();
            document.execCommand('copy');
            alert('Referral link copied to clipboard!');
        }

        function shareWhatsApp(referralCode) {
            const message = `🏠 Join APS Dream Homes as Associate!\n\n✅ High Commissions (5-20%)\n✅ Amazing Rewards (Mobile, Laptop, Car)\n✅ Team Building Opportunities\n\nUse my referral code: ${referralCode}\n\nRegister: ${window.location.origin}/associate_registration.php?ref=${referralCode}`;
            window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, '_blank');
        }

        function shareTelegram(referralCode) {
            const message = `Join APS Dream Homes as Associate! Use referral code: ${referralCode}`;
            window.open(`https://t.me/share/url?url=${encodeURIComponent(window.location.origin)}/associate_registration.php?ref=${referralCode}&text=${encodeURIComponent(message)}`, '_blank');
        }

        function viewProjects() {
            window.open('properties.php', '_blank');
        }

        function downloadBrochure() {
            alert('Marketing materials will be available for download soon!');
        }

        function viewReports() {
            alert('Detailed business reports will be available in the Reports section!');
        }
    </script>
</body>
</html>
