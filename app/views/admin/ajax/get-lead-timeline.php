<?php
/**
 * Admin AJAX: lead timeline.
 *
 * NOTE (S451): Admin\AjaxController::getLeadTimeline() requires this view,
 * but the file did not exist -> "Failed opening required
 * app/views/admin/ajax/get-lead-timeline.php" (HTTP 500).
 *
 * The data is the SAME source the CRM lead-detail timeline uses:
 * lead_activities (activity_type / description / old_value / new_value).
 */
$leadId = (int)($_GET['lead_id'] ?? 0);

if ($leadId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'lead_id is required']);
    exit;
}

try {
    $rows = $db->fetchAll(
        "SELECT id, lead_id, activity_type, description, old_value, new_value,
                created_by, created_at
         FROM lead_activities
         WHERE lead_id = ?
         ORDER BY created_at DESC
         LIMIT 100",
        [$leadId]
    ) ?: [];
} catch (\Throwable $e) {
    $rows = [];
    error_log('get-lead-timeline: ' . $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode([
    'success'   => true,
    'lead_id'   => $leadId,
    'timeline'  => $rows,
    'count'     => count($rows),
]);
