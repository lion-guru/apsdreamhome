<?php
/**
 * Apply Leave - Updated with Session Management
 */
require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();

if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$employees = $db->fetchAll("SELECT id, name FROM employees WHERE status=\"active\" ORDER BY name");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }
    
    $employee_id = $_POST["employee_id"];
    $leave_type = $_POST["leave_type"];
    $from_date = $_POST["from_date"];
    $to_date = $_POST["to_date"];
    $remarks = $_POST["remarks"];
    
    $success = $db->execute(
        "INSERT INTO leaves (employee_id, leave_type, from_date, to_date, remarks) VALUES (:employee_id, :leave_type, :from_date, :to_date, :remarks)",
        [
            'employee_id' => $employee_id,
            'leave_type' => $leave_type,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'remarks' => $remarks
        ]
    );
    
    if ($success) {
        require_once __DIR__ . "/../includes/notification_manager.php";
        $nm = new NotificationManager($db->getConnection());
        
        // Get employee name
        $emp_res = $db->fetchOne("SELECT name FROM employees WHERE id = :id", ['id' => $employee_id]);
        $employee_name = $emp_res['name'] ?? "Unknown Employee";

        // Internal Notification for Admin
        $nm->send([
            'user_id' => 1, // Admin
            'template' => 'LEAVE_APPLIED',
            'data' => [
                'employee_name' => $employee_name,
                'from_date' => $from_date,
                'to_date' => $to_date
            ],
            'channels' => ['db']
        ]);

        header("Location: leaves.php?msg=" . urlencode("Leave applied successfully."));
        exit();
    } else {
        echo "Error: There was an issue applying the leave.";
    }
}
?>

