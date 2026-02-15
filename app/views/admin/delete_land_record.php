<?php
require_once __DIR__ . "/core/init.php";

if (!isAuthenticated()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$db = \App\Core\App::database();

if (!isAdmin()) {
    echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
        exit();
    }

    if (isset($_POST['id'])) {
        $delete_id = intval($_POST['id']);
        
        // Execute the delete
        $success_exec = $db->execute("DELETE FROM kisaan_land_management WHERE id = ?", [$delete_id]);

        if ($success_exec) {
            // Add notification for audit log
            $admin_name = getAuthUsername();
            
            require_once __DIR__ . '/../includes/notification_manager.php';
            require_once __DIR__ . '/../includes/email_service.php';
            $nm = new NotificationManager(null, new EmailService());
            $nm->send([
                'user_id' => 1,
                'template' => 'LAND_RECORD_DELETED',
                'data' => [
                    'farmer_name' => 'N/A', 
                    'id' => $delete_id,
                    'admin_name' => $admin_name
                ],
                'channels' => ['db']
            ]);
            
            echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error deleting record']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>

