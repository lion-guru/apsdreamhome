<?php
/**
 * APS Dream Home - Collaboration API
 * Backend API for real-time collaboration features
 */

require_once 'CollaborationMonitor.php';

// Initialize collaboration monitor
// Auto-detect user based on hostname or session
$currentUser = $_SESSION['user_id'] ?? $_GET['user'] ?? gethostname();
$monitor = new CollaborationMonitor(null, $currentUser);

// Handle API requests
$action = $_GET['action'] ?? 'get_status';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    switch ($action) {
        case 'get_status':
            $status = $monitor->getCollaborationStatus();
            echo json_encode([
                'success' => true,
                'data' => $status
            ]);
            break;

        case 'update_task':
            $input = json_decode(file_get_contents('php://input'), true);
            $task = $input['task'] ?? '';
            $status = $input['status'] ?? 'active';

            if (empty($task)) {
                throw new Exception('Task is required');
            }

            $monitor->updateCurrentTask($task, $status);
            echo json_encode([
                'success' => true,
                'message' => 'Task updated successfully'
            ]);
            break;

        case 'run_sync':
            // Run the Git sync
            $output = shell_exec('git_auto_sync.php 2>&1');

            echo json_encode([
                'success' => true,
                'message' => 'Git sync completed',
                'output' => $output
            ]);
            break;

        case 'assign_task':
            $input = json_decode(file_get_contents('php://input'), true);
            $taskType = $input['task_type'] ?? '';
            $user = $input['user'] ?? '';

            if (empty($taskType) || empty($user)) {
                throw new Exception('Task type and user are required');
            }

            $monitor->assignTask($taskType, $user);
            echo json_encode([
                'success' => true,
                'message' => "Task '$taskType' assigned to $user"
            ]);
            break;

        case 'get_logs':
            $logFile = __DIR__ . '/collaboration_notifications.log';
            $logs = file_exists($logFile) ? file($logFile) : [];
            $recentLogs = array_slice($logs, -50); // Last 50 entries

            header('Content-Type: text/plain');
            echo implode('', $recentLogs);
            exit;

        case 'check_changes':
            $changes = $monitor->checkForChanges();
            echo json_encode([
                'success' => true,
                'changes' => $changes,
                'has_changes' => !empty($changes)
            ]);
            break;

        case 'get_work_division':
            $division = $monitor->getWorkDivision();
            echo json_encode([
                'success' => true,
                'work_division' => $division
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
