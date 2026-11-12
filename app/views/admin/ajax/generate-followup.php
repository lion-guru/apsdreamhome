<?php
/**
 * Admin AJAX: generate a follow-up task for a lead.
 *
 * NOTE (S453): Admin\AjaxController::generateFollowup() requires this view,
 * but the file did not exist -> "Failed opening required
 * app/views/admin/ajax/generate-followup.php" (HTTP 500).
 *
 * POST /admin/ajax/generate-followup
 *   lead_id       (int)    required - the lead to schedule the task for
 *   assigned_to   (int)    optional - defaults to the lead's owner
 *   title         (string) optional - defaults to a generated title
 *   description   (string) optional
 *   priority      (string) optional - low|medium|high (default medium)
 *   due_date      (date)   optional - YYYY-MM-DD (default today)
 *   due_time      (time)   optional - HH:MM:SS
 *
 * Responds with JSON: { success, task_id, ... } or { success:false, error }.
 */

$leadId = (int)($_POST['lead_id'] ?? $_GET['lead_id'] ?? 0);

header('Content-Type: application/json');

if ($leadId <= 0) {
    echo json_encode(['success' => false, 'error' => 'lead_id is required']);
    exit;
}

try {
    $lead = $db->fetchOne(
        "SELECT id, name, phone, assigned_to, created_by, tenant_id
         FROM leads
         WHERE id = ? AND deleted_at IS NULL",
        [$leadId]
    );

    if (!$lead) {
        echo json_encode(['success' => false, 'error' => 'Lead not found']);
        exit;
    }

    $assignedTo = (int)($_POST['assigned_to'] ?? 0);
    if ($assignedTo <= 0) {
        $assignedTo = (int)($lead['assigned_to'] ?: $lead['created_by'] ?: 0);
    }

    $priority = (string)($_POST['priority'] ?? 'medium');
    if (!in_array($priority, ['low', 'medium', 'high'], true)) {
        $priority = 'medium';
    }

    $dueDate = (string)($_POST['due_date'] ?? '');
    if ($dueDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        $dueDate = date('Y-m-d');
    }

    $dueTime = (string)($_POST['due_time'] ?? '');
    if ($dueTime !== '' && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $dueTime)) {
        $dueTime = '';
    }

    $title = trim((string)($_POST['title'] ?? ''));
    if ($title === '') {
        $leadName = trim((string)($lead['name'] ?? ('Lead #' . $leadId)));
        $title = "Follow up with {$leadName}";
    }

    $description = trim((string)($_POST['description'] ?? ''));
    if ($description === '') {
        $description = "Follow-up task generated from the admin lead desk.";
    }

    $service = new \App\Services\CRMService();
    $result = $service->createTask([
        'lead_id'     => $leadId,
        'assigned_to' => $assignedTo,
        'created_by'  => (int)($_SESSION['admin_id'] ?? $assignedTo),
        'task_type'   => 'follow_up',
        'title'       => $title,
        'description' => $description,
        'priority'    => $priority,
        'due_date'    => $dueDate,
        'due_time'    => $dueTime !== '' ? $dueTime : null,
    ]);

    if (empty($result['success'])) {
        echo json_encode([
            'success' => false,
            'error'   => $result['error'] ?? 'Could not create the follow-up task',
        ]);
        exit;
    }

    echo json_encode([
        'success'     => true,
        'task_id'     => (int)($result['task_id'] ?? 0),
        'lead_id'     => $leadId,
        'assigned_to' => $assignedTo,
        'title'       => $title,
        'priority'    => $priority,
        'due_date'    => $dueDate,
        'due_time'    => $dueTime,
        'message'     => 'Follow-up task created',
    ]);
} catch (\Throwable $e) {
    error_log('generate-followup: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Could not create the follow-up task']);
}
