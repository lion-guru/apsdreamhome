<?php
session_start();
include 'config.php';
require_once 'includes/universal_dashboard_template.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !in_array($_SESSION['admin_role'], ['official_employee'])) {
    header('Location: login.php');
    exit();
}

$employee = $_SESSION['admin_username'] ?? 'Employee';

// Employee statistics
$my_tasks = $conn->query("SELECT COUNT(*) as c FROM employee_tasks WHERE assigned_to = ?")->execute([$employee]) ? $conn->query("SELECT COUNT(*) as c FROM employee_tasks WHERE assigned_to = ?")->fetch()['c'] ?? 0 : 5;
$completed_tasks = $conn->query("SELECT COUNT(*) as c FROM employee_tasks WHERE assigned_to = ? AND status='completed'")->execute([$employee]) ? $conn->query("SELECT COUNT(*) as c FROM employee_tasks WHERE assigned_to = ? AND status='completed'")->fetch()['c'] ?? 0 : 3;
$pending_tasks = $my_tasks - $completed_tasks;
$attendance_rate = 92.5; // Mock data

// Statistics for dashboard
$completion_rate = $my_tasks > 0 ? round(($completed_tasks / $my_tasks) * 100, 1) : 60.0;

$stats = [
    [
        'icon' => 'fas fa-tasks',
        'value' => $my_tasks,
        'label' => 'Assigned Tasks',
        'change' => '+2 this week',
        'change_type' => 'neutral'
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
        'change' => '-1 since yesterday',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-calendar-check',
        'value' => $attendance_rate . '%',
        'label' => 'Attendance Rate',
        'change' => '+3% this month',
        'change_type' => 'positive'
    ]
];

// Quick actions for employee
$quick_actions = [
    [
        'title' => 'View My Tasks',
        'icon' => 'fas fa-list-check',
        'url' => 'my_tasks.php',
        'color' => 'primary'
    ],
    [
        'title' => 'Request Leave',
        'icon' => 'fas fa-calendar-times',
        'url' => 'leave_request.php',
        'color' => 'success'
    ],
    [
        'title' => 'Support Tickets',
        'icon' => 'fas fa-ticket-alt',
        'url' => 'tickets.php',
        'color' => 'info'
    ],
    [
        'title' => 'Upload Document',
        'icon' => 'fas fa-file-upload',
        'url' => 'documents_dashboard.php?action=upload',
        'color' => 'warning'
    ]
];

// Recent activities
$recent_activities = [
    [
        'title' => 'Task Assignment - New',
        'description' => 'Review project documentation assigned',
        'time' => 'Dec 20, 2024',
        'icon' => 'fas fa-plus-circle text-primary'
    ],
    [
        'title' => 'Task Completed',
        'description' => 'Update progress on ongoing tasks completed',
        'time' => 'Dec 19, 2024',
        'icon' => 'fas fa-check-circle text-success'
    ],
    [
        'title' => 'Meeting Scheduled',
        'description' => 'Daily standup meeting at 10:00 AM',
        'time' => 'Dec 20, 2024',
        'icon' => 'fas fa-calendar text-info'
    ]
];

echo generateUniversalDashboard('employee', $stats, $quick_actions, $recent_activities);
