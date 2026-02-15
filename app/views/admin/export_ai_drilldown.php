<?php
/**
 * Export drilldown analytics as CSV - Secured version
 */
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/SecurityUtility.php';

// Access control
if (!SecurityUtility::hasRole(['admin', 'superadmin'])) {
    header('Location: ../index.php?msg=' . urlencode('Access denied.'));
    exit();
}

// Get and sanitize filters
$from = SecurityUtility::sanitizeInput($_GET['from'] ?? date('Y-m-d', strtotime('-30 days')), 'string');
$to = SecurityUtility::sanitizeInput($_GET['to'] ?? date('Y-m-d'), 'string');
$role_filter = isset($_GET['role']) && $_GET['role'] !== '' ? SecurityUtility::sanitizeInput($_GET['role'], 'string') : null;
$suggestion_filter = isset($_GET['suggestion']) && trim($_GET['suggestion']) !== '' ? SecurityUtility::sanitizeInput(trim($_GET['suggestion']), 'string') : null;

// Validate dates
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-d', strtotime('-30 days'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) $to = date('Y-m-d');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="ai_drilldown_export_' . date('Ymd_His') . '.csv"');

$out = fopen('php://output', 'w');

// Header
fputcsv($out, ['id', 'user_id', 'role', 'action', 'suggestion_text', 'feedback', 'notes', 'created_at']);

// Build query with prepared statements
$sql = "SELECT id, user_id, role, action, suggestion_text, feedback, notes, created_at 
        FROM ai_interactions 
        WHERE created_at >= ? AND created_at <= ? ";
$params = [$from . ' 00:00:00', $to . ' 23:59:59'];
$types = "ss";

if ($role_filter) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

if ($suggestion_filter) {
    $sql .= " AND suggestion_text LIKE ?";
    $params[] = "%" . $suggestion_filter . "%";
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";

$db = \App\Core\App::database();
$results = $db->fetchAll($sql, $params);

foreach ($results as $row) {
    fputcsv($out, $row);
}

fclose($out);
exit;
?>
