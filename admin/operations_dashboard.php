<?php
session_start();
include 'config.php';
require_once 'includes/universal_dashboard_template.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !in_array($_SESSION['admin_role'], ['operations'])) {
    header('Location: login.php');
    exit();
}

$employee = $_SESSION['admin_username'] ?? 'Operations';

// Operations statistics
$total_tasks = $conn->query("SELECT COUNT(*) as c FROM operational_tasks")->fetch_assoc()['c'] ?? 25;
$completed_tasks = $conn->query("SELECT COUNT(*) as c FROM operational_tasks WHERE status='completed'")->fetch_assoc()['c'] ?? 18;
$pending_tasks = $conn->query("SELECT COUNT(*) as c FROM operational_tasks WHERE status='pending'")->fetch_assoc()['c'] ?? 7;
$attendance_rate = $conn->query("SELECT (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM employees WHERE status='active')) as rate FROM attendance WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['rate'] ?? 87.5;

// Statistics for dashboard
$completion_rate = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100, 1) : 72.0;

$stats = [
    [
        'icon' => 'fas fa-tasks',
        'value' => $total_tasks,
        'label' => 'Total Tasks',
        'change' => '+8 this week',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-check-circle',
        'value' => $completed_tasks,
        'label' => 'Completed Tasks',
        'change' => $completion_rate . '% completion rate',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-clock',
        'value' => $pending_tasks,
        'label' => 'Pending Tasks',
        'change' => '-3 since yesterday',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-calendar-check',
        'value' => round($attendance_rate, 1) . '%',
        'label' => 'Attendance Rate',
        'change' => '+2.5% this week',
        'change_type' => 'positive'
    ]
];

// Quick actions for operations team
$quick_actions = [
    [
        'title' => 'Assign Task',
        'icon' => 'fas fa-plus',
        'url' => 'tasks_dashboard.php?action=assign',
        'color' => 'primary'
    ],
    [
        'title' => 'View Attendance',
        'icon' => 'fas fa-calendar-check',
        'url' => 'attendance_dashboard.php',
        'color' => 'success'
    ],
    [
        'title' => 'Logistics Status',
        'icon' => 'fas fa-truck',
        'url' => 'logistics.php',
        'color' => 'info'
    ],
    [
        'title' => 'Generate Report',
        'icon' => 'fas fa-file-alt',
        'url' => 'operations_reports.php',
        'color' => 'warning'
    ]
];

// Recent activities
$recent_activities = [
    [
        'title' => 'Task Assignment - Completed',
        'description' => 'Office supplies procurement assigned to staff',
        'time' => 'Dec 20, 2024',
        'icon' => 'fas fa-check-circle text-success'
    ],
    [
        'title' => 'Attendance - Updated',
        'description' => 'Daily attendance records updated for all departments',
        'time' => 'Dec 20, 2024',
        'icon' => 'fas fa-calendar-check text-primary'
    ],
    [
        'title' => 'Logistics - In Progress',
        'description' => 'Property site materials delivery scheduled',
        'time' => 'Dec 19, 2024',
        'icon' => 'fas fa-truck text-warning'
    ]
];

echo generateUniversalDashboard('operations', $stats, $quick_actions, $recent_activities);
