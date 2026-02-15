<?php
/**
 * Export Dashboard Data - AJAX Endpoint
 * Exports dashboard data in CSV or PDF format
 */

header('Access-Control-Allow-Origin: *');

// Include admin configuration
require_once __DIR__ . '/../core/init.php';

// Verify admin authentication
if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized'))]);
    exit();
}

// CSRF Validation
$csrf_token = $_GET['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Invalid CSRF token'))]);
    exit();
}

// RBAC Protection
$currentRole = $_SESSION['admin_role'] ?? '';
$allowedRoles = ['superadmin', 'manager'];
if (!in_array($currentRole, $allowedRoles)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can export dashboard data'))]);
    exit();
}

try {
    $format = $_GET['format'] ?? 'csv';
    // Establish database connection
    $db = \App\Core\App::database();

    // Fetch data based on the selected format
    $data = [];

    // Users data
    $data['users'] = $db->fetchAll("SELECT auser as name, email, role, status, created_at FROM admin ORDER BY created_at DESC LIMIT 100");

    // Properties data
    $data['properties'] = $db->fetchAll("SELECT title, location, price, status, created_at FROM properties ORDER BY created_at DESC LIMIT 100");

    // Bookings data
    $data['bookings'] = $db->fetchAll("SELECT customer_name, property_title, total_amount, status, created_at FROM bookings ORDER BY created_at DESC LIMIT 100");

    // Projects data
    $data['projects'] = $db->fetchAll("SELECT project_name, location, status, created_at FROM projects ORDER BY created_at DESC LIMIT 100");

    if ($format === 'csv') {
        exportAsCSV($data);
    } else {
        exportAsPDF($data);
    }

} catch (Exception $e) {
    error_log('Export data error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Error exporting data'))
    ]);
}

function exportAsCSV($data) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="dashboard_export_' . date('Y-m-d_H-i-s') . '.csv"');

    $output = fopen('php://output', 'w');

    // Users CSV
    fputcsv($output, ['=== USERS ===']);
    if (!empty($data['users'])) {
        fputcsv($output, array_keys($data['users'][0]));
        foreach ($data['users'] as $row) {
            fputcsv($output, $row);
        }
    }

    fputcsv($output, ['']);
    fputcsv($output, ['=== PROPERTIES ===']);
    if (!empty($data['properties'])) {
        fputcsv($output, array_keys($data['properties'][0]));
        foreach ($data['properties'] as $row) {
            fputcsv($output, $row);
        }
    }

    fputcsv($output, ['']);
    fputcsv($output, ['=== BOOKINGS ===']);
    if (!empty($data['bookings'])) {
        fputcsv($output, array_keys($data['bookings'][0]));
        foreach ($data['bookings'] as $row) {
            fputcsv($output, $row);
        }
    }

    fputcsv($output, ['']);
    fputcsv($output, ['=== PROJECTS ===']);
    if (!empty($data['projects'])) {
        fputcsv($output, array_keys($data['projects'][0]));
        foreach ($data['projects'] as $row) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit();
}

function exportAsPDF($data) {
    // For PDF export, we'll create a simple HTML that can be printed or converted
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="dashboard_report_' . date('Y-m-d_H-i-s') . '.html"');

    echo '<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Report - ' . date('Y-m-d H:i:s') . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .section { margin: 30px 0; }
        .stats { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>APS Dream Home - Admin Dashboard Report</h1>
    <p><strong>Generated on:</strong> ' . date('Y-m-d H:i:s') . '</p>

    <div class="section">
        <h2>Summary Statistics</h2>
        <div class="stats">
            <p><strong>Total Users:</strong> ' . count($data['users']) . '</p>
            <p><strong>Total Properties:</strong> ' . count($data['properties']) . '</p>
            <p><strong>Total Bookings:</strong> ' . count($data['bookings']) . '</p>
            <p><strong>Total Projects:</strong> ' . count($data['projects']) . '</p>
        </div>
    </div>';

    if (!empty($data['users'])) {
        echo '<div class="section">
            <h2>Recent Users</h2>
            <table>
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr>
                </thead>
                <tbody>';
        foreach (array_slice($data['users'], 0, 10) as $user) {
            echo '<tr>
                <td>' . h($user['name']) . '</td>
                <td>' . h($user['email']) . '</td>
                <td>' . h($user['role']) . '</td>
                <td>' . h($user['status']) . '</td>
                <td>' . h($user['created_at']) . '</td>
            </tr>';
        }
        echo '</tbody></table></div>';
    }

    if (!empty($data['properties'])) {
        echo '<div class="section">
            <h2>Recent Properties</h2>
            <table>
                <thead>
                    <tr><th>Title</th><th>Location</th><th>Price</th><th>Status</th><th>Created</th></tr>
                </thead>
                <tbody>';
        foreach (array_slice($data['properties'], 0, 10) as $property) {
            echo '<tr>
                <td>' . h($property['title']) . '</td>
                <td>' . h($property['location']) . '</td>
                <td>₹' . number_format($property['price']) . '</td>
                <td>' . h($property['status']) . '</td>
                <td>' . h($property['created_at']) . '</td>
            </tr>';
        }
        echo '</tbody></table></div>';
    }

    echo '</body></html>';
    exit();
}
?>

