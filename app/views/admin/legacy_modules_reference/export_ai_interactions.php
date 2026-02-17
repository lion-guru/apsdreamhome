<?php
/**
 * Exports all AI interaction logs as CSV - Secured version
 */
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/SecurityUtility.php';

// Access control
if (!SecurityUtility::hasRole(['admin', 'superadmin'])) {
    header('Location: ../index.php');
    exit();
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="ai_interactions_export_'.date('Ymd_His').'.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['id', 'user_id', 'role', 'action', 'suggestion_text', 'feedback', 'notes', 'created_at']);

$db = \App\Core\App::database();
$results = $db->fetchAll("SELECT id, user_id, role, action, suggestion_text, feedback, notes, created_at FROM ai_interactions ORDER BY created_at DESC");

foreach ($results as $row) {
    fputcsv($out, $row);
}

fclose($out);
exit;
