<?php
/**
 * Modern Manager Dashboard - APS Dream Home
 * Mobile-First, Responsive Design with Modern UI/UX
 */

session_start();

// Security check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_role'] !== 'manager') {
    header('Location: index.php?error=unauthorized');
    exit();
}

// Include universal dashboard template
require_once 'includes/universal_dashboard_template.php';
require_once __DIR__ . '/../includes/db_connection.php';

$conn = getDbConnection();

// Fetch manager-specific data
$totalProperties = $conn->query("SELECT COUNT(*) as count FROM properties")->fetch_assoc()['count'] ?? 0;
$totalBookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'] ?? 0;
$activeLeads = $conn->query("SELECT COUNT(*) as count FROM leads WHERE status IN ('new', 'contacted')")->fetch_assoc()['count'] ?? 0;
$teamMembers = $conn->query("SELECT COUNT(*) as count FROM employees WHERE manager_id = " . ($_SESSION['admin_id'] ?? 0))->fetch_assoc()['count'] ?? 0;

// Manager specific statistics
$stats = [
    [
        'icon' => 'fas fa-building',
        'value' => number_format($totalProperties),
        'label' => 'Total Properties',
        'change' => '+5 this week',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-calendar-check',
        'value' => number_format($totalBookings),
        'label' => 'Total Bookings',
        'change' => '+12 this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-user-tie',
        'value' => number_format($activeLeads),
        'label' => 'Active Leads',
        'change' => 'Follow up required',
        'change_type' => 'neutral'
    ],
    [
        'icon' => 'fas fa-users',
        'value' => number_format($teamMembers),
        'label' => 'Team Members',
        'change' => 'Under management',
        'change_type' => 'neutral'
    ]
];

// Manager quick actions
$quick_actions = [
    [
        'title' => 'Manage Properties',
        'description' => 'Add, edit, and manage properties',
        'icon' => 'fas fa-building',
        'url' => 'properties.php'
    ],
    [
        'title' => 'Review Bookings',
        'description' => 'View and approve bookings',
        'icon' => 'fas fa-calendar-check',
        'url' => 'bookings.php'
    ],
    [
        'title' => 'Lead Management',
        'description' => 'Manage and convert leads',
        'icon' => 'fas fa-user-tie',
        'url' => 'leads.php'
    ],
    [
        'title' => 'Team Reports',
        'description' => 'View team performance reports',
        'icon' => 'fas fa-chart-bar',
        'url' => 'reports.php'
    ],
    [
        'title' => 'Task Assignment',
        'description' => 'Assign tasks to team members',
        'icon' => 'fas fa-tasks',
        'url' => 'tasks.php'
    ],
    [
        'title' => 'Analytics',
        'description' => 'View detailed analytics',
        'icon' => 'fas fa-analytics',
        'url' => 'analytics_dashboard.php'
    ]
];

// Recent manager activities
$recent_activities = [
    [
        'icon' => 'fas fa-check-circle',
        'title' => 'Booking Approved',
        'description' => 'Approved booking #BK001 for Villa in Sector 15',
        'time' => '10 mins ago'
    ],
    [
        'icon' => 'fas fa-user-plus',
        'title' => 'New Lead Assignment',
        'description' => 'Assigned lead to Sales Executive John',
        'time' => '25 mins ago'
    ],
    [
        'icon' => 'fas fa-building',
        'title' => 'Property Updated',
        'description' => 'Updated property details for Project ABC',
        'time' => '1 hour ago'
    ]
];

// Generate and display the dashboard
echo generateUniversalDashboard('manager', $stats, $quick_actions, $recent_activities);
?>
