<?php
// Admin Dashboard - Modern UI with Analytics
require_once 'includes/config/db_config.php';
require_once 'includes/functions.php';

// Start session and check login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Get dashboard statistics
$stats = [
    'properties' => [
        'total' => 0,
        'sold' => 0,
        'available' => 0,
        'under_contract' => 0
    ],
    'bookings' => [
        'total' => 0,
        'pending' => 0,
        'confirmed' => 0,
        'completed' => 0
    ],
    'customers' => [
        'total' => 0,
        'new_this_month' => 0
    ],
    'leads' => [
        'total' => 0,
        'new' => 0,
        'contacted' => 0,
        'qualified' => 0
    ],
    'revenue' => [
        'total' => 0,
        'this_month' => 0,
        'last_month' => 0
    ]
];

try {
    // Property statistics
    $result = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN status = 'under_contract' THEN 1 ELSE 0 END) as under_contract
        FROM properties
    ");
    if ($row = $result->fetch_assoc()) {
        $stats['properties'] = array_merge($stats['properties'], $row);
    }

    // Booking statistics
    $result = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM bookings
    ");
    if ($row = $result->fetch_assoc()) {
        $stats['bookings'] = array_merge($stats['bookings'], $row);
    }

    // Customer statistics
    $result = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_this_month
        FROM customers
    ");
    if ($row = $result->fetch_assoc()) {
        $stats['customers'] = array_merge($stats['customers'], $row);
    }

    // Lead statistics
    $result = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new,
            SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted,
            SUM(CASE WHEN status = 'qualified' THEN 1 ELSE 0 END) as qualified
        FROM leads
    ");
    if ($row = $result->fetch_assoc()) {
        $stats['leads'] = array_merge($stats['leads'], $row);
    }

    // Revenue statistics
    $result = $conn->query("
        SELECT 
            COALESCE(SUM(amount), 0) as total,
            COALESCE(SUM(CASE WHEN MONTH(transaction_date) = MONTH(CURRENT_DATE()) 
                           AND YEAR(transaction_date) = YEAR(CURRENT_DATE()) 
                           THEN amount ELSE 0 END), 0) as this_month,
            COALESCE(SUM(CASE WHEN MONTH(transaction_date) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
                           AND YEAR(transaction_date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
                           THEN amount ELSE 0 END), 0) as last_month
        FROM transactions
        WHERE status = 'completed'
    ");
    if ($row = $result->fetch_assoc()) {
        $stats['revenue'] = array_merge($stats['revenue'], $row);
    }

    // Get recent activities
    $recent_activities = [];
    $result = $conn->query("
        (SELECT 'booking' as type, id, customer_name, created_at, status, 'New booking received' as description
         FROM bookings 
         ORDER BY created_at DESC LIMIT 3)
        UNION ALL
        (SELECT 'lead' as type, id, name as customer_name, created_at, status, 'New lead received' as description
         FROM leads 
         ORDER BY created_at DESC LIMIT 3)
        ORDER BY created_at DESC LIMIT 5
    ");
    if ($result) {
        $recent_activities = $result->fetch_all(MYSQLI_ASSOC);
    }

} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
}

// Include modern header
include 'includes/modern-header.php';
?>

<!-- Page Content -->
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard Overview</h1>
        <div class="d-flex
            <button class="btn btn-primary me-2">
                <i class="fas fa-download me-2"></i>Generate Report
            </button>
            <button class="btn btn-outline-secondary">
                <i class="fas fa-calendar me-2"></i>This Month
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class=
