<?php
session_start();
include 'config.php';
require_once 'includes/universal_dashboard_template.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !in_array($_SESSION['admin_role'], ['legal'])) {
    header('Location: login.php');
    exit();
}

$employee = $_SESSION['admin_username'] ?? 'Legal';

// Legal statistics
$total_cases = $conn->query("SELECT COUNT(*) as c FROM legal_cases")->fetch_assoc()['c'] ?? 15;
$active_cases = $conn->query("SELECT COUNT(*) as c FROM legal_cases WHERE status='active'")->fetch_assoc()['c'] ?? 8;
$completed_cases = $conn->query("SELECT COUNT(*) as c FROM legal_cases WHERE status='completed'")->fetch_assoc()['c'] ?? 7;
$total_documents = $conn->query("SELECT COUNT(*) as c FROM legal_documents")->fetch_assoc()['c'] ?? 45;

// Statistics for dashboard
$completion_rate = $total_cases > 0 ? round(($completed_cases / $total_cases) * 100, 1) : 46.7;

$stats = [
    [
        'icon' => 'fas fa-balance-scale',
        'value' => $total_cases,
        'label' => 'Total Cases',
        'change' => '+5 this month',
        'change_type' => 'neutral'
    ],
    [
        'icon' => 'fas fa-clock',
        'value' => $active_cases,
        'label' => 'Active Cases',
        'change' => '+2 pending review',
        'change_type' => 'warning'
    ],
    [
        'icon' => 'fas fa-check-circle',
        'value' => $completed_cases,
        'label' => 'Completed Cases',
        'change' => $completion_rate . '% completion rate',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-file-alt',
        'value' => $total_documents,
        'label' => 'Legal Documents',
        'change' => '+15 this week',
        'change_type' => 'positive'
    ]
];

// Quick actions for legal team
$quick_actions = [
    [
        'title' => 'Add New Case',
        'icon' => 'fas fa-plus',
        'url' => 'cases.php?action=add',
        'color' => 'primary'
    ],
    [
        'title' => 'Upload Document',
        'icon' => 'fas fa-file-upload',
        'url' => 'documents_dashboard.php?action=upload',
        'color' => 'success'
    ],
    [
        'title' => 'Compliance Check',
        'icon' => 'fas fa-shield-alt',
        'url' => 'compliance_dashboard.php',
        'color' => 'info'
    ],
    [
        'title' => 'Contract Review',
        'icon' => 'fas fa-file-contract',
        'url' => 'contracts.php',
        'color' => 'warning'
    ]
];

// Recent activities
$recent_activities = [
    [
        'title' => 'Legal Case - Active',
        'description' => 'Property Dispute (Litigation)',
        'time' => 'Dec 20, 2024',
        'icon' => 'fas fa-exclamation-triangle text-danger'
    ],
    [
        'title' => 'Legal Case - Completed',
        'description' => 'Contract Review (Corporate)',
        'time' => 'Dec 19, 2024',
        'icon' => 'fas fa-check-circle text-success'
    ],
    [
        'title' => 'Legal Case - Active',
        'description' => 'Employment Issue (HR)',
        'time' => 'Dec 18, 2024',
        'icon' => 'fas fa-balance-scale text-primary'
    ]
];

echo generateUniversalDashboard('legal', $stats, $quick_actions, $recent_activities);
