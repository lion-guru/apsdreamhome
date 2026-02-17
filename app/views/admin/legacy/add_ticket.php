<?php
/**
 * Add Support Ticket - Updated with Session Management
 */

require_once __DIR__ . '/core/init.php';

// Generate CSRF token
$csrf_token = generateCSRFToken();

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verifyCSRFToken($_POST["csrf_token"] ?? "")) {
        $message = "Security validation failed. Please try again.";
        $message_type = "danger";
    } else {
        $user_id = getAuthUserId();
        $subject = trim($_POST["subject"]);
        $ticket_message = trim($_POST["message"]);
        
        // Validate inputs
        if (empty($subject)) {
            $message = "Subject is required.";
            $message_type = "danger";
        } elseif (empty($ticket_message)) {
            $message = "Message is required.";
            $message_type = "danger";
        } elseif (strlen($subject) > 255) {
            $message = "Subject is too long (max 255 characters).";
            $message_type = "danger";
        } else {
            $db = \App\Core\App::database();
            $success = $db->execute(
                "INSERT INTO support_tickets (user_id, subject, message) VALUES (?, ?, ?)",
                [$user_id, $subject, $ticket_message]
            );
            
            if ($success) {
                log_admin_activity($user_id, "Support Ticket Created", "Created support ticket: $subject");
                
                // Send notification using NotificationManager
                require_once __DIR__ . "/../includes/notification_manager.php";
                $nm = new NotificationManager($db->getConnection());
                
                // Notify User
                $nm->send([
                    'user_id' => $user_id,
                    'template' => 'TICKET_CREATED',
                    'data' => [
                        'subject' => $subject
                    ],
                    'channels' => ['db', 'email']
                ]);

                // Internal Notification for Admin
                $nm->send([
                    'user_id' => 1, // Admin
                    'type' => 'info',
                    'title' => 'New Support Ticket',
                    'message' => "A new support ticket was created by user ID $user_id: $subject",
                    'channels' => ['db']
                ]);
                
                $message = "Ticket submitted successfully!";
                $message_type = "success";
                
                header("Location: support_tickets.php?msg=" . urlencode($message));
                exit();
            } else {
                $message = "Error creating ticket.";
                $message_type = "danger";
            }
        }
    }
}
?>

