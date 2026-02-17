<?php
/**
 * Approve Leave - Updated with Session Management
 */
require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();

if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

require_permission("approve_leave");

if (isset($_GET["id"]) && isset($_GET["action"])) {
    $id = intval($_GET["id"]);
    $action = ($_GET["action"] === "approve") ? "approved" : "rejected";
    
    // Get employee user_id for notification
    $leave_data = $db->fetchOne("SELECT user_id FROM leaves WHERE id = :id", ['id' => $id]);

    $success = $db->execute("UPDATE leaves SET status = :status WHERE id = :id", [
        'status' => $action,
        'id' => $id
    ]);
    if ($success) {
        require_once __DIR__ . "/../includes/notification_manager.php";
        $nm = new NotificationManager($db->getConnection());
        
        // Notify Employee
        if ($leave_data && $leave_data['user_id']) {
            $template = ($action === "approved") ? "LEAVE_APPROVED" : "LEAVE_REJECTED";
            $nm->send([
                'user_id' => $leave_data['user_id'],
                'template' => $template,
                'data' => [
                    'leave_id' => $id
                ],
                'channels' => ['db', 'email']
            ]);
        }

        // Audit Log
        $nm->send([
            'user_id' => 1, // Admin
            'type' => ($action === "approved") ? 'success' : 'warning',
            'title' => 'Leave ' . ucfirst($action),
            'message' => "Leave ID $id was $action by admin " . getAuthUsername(),
            'channels' => ['db']
        ]);

        header("Location: leaves.php?msg=" . urlencode("Leave " . $action . " successfully."));
        exit();
    } else {
        echo "Error: There was an issue processing the leave approval.";
    }
}
?>

