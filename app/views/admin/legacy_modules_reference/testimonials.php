<?php

/**
 * Testimonials Management - Updated with Session Management
 */

require_once __DIR__ . '/core/init.php';

use App\Core\Database;

$db = \App\Core\App::database();

// Check if user has admin privileges
if (!isAdmin()) {
    header("Location: index.php?error=access_denied");
    exit();
}

// Generate CSRF token
$csrf_token = generateCSRFToken();

$message = "";
$message_type = "";

// Handle testimonial actions (approve/reject/delete)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && isset($_POST["id"])) {
    if (!verifyCSRFToken($_POST["csrf_token"] ?? "")) {
        $message = "Security validation failed. Please try again.";
        $message_type = "danger";
    } else {
        $id = intval($_POST["id"]);
        $action = $_POST["action"];

        if ($id <= 0) {
            $message = "Invalid testimonial ID.";
            $message_type = "danger";
        } elseif (!in_array($action, ["approve", "reject", "delete"])) {
            $message = "Invalid action.";
            $message_type = "danger";
        } else {
            try {
                switch ($action) {
                    case "approve":
                        $success = $db->execute("UPDATE testimonials SET status = :status WHERE id = :id", ["status" => "approved", "id" => $id]);
                        break;
                    case "reject":
                        $success = $db->execute("UPDATE testimonials SET status = :status WHERE id = :id", ["status" => "rejected", "id" => $id]);
                        break;
                    case "delete":
                        $success = $db->execute("DELETE FROM testimonials WHERE id = :id", ["id" => $id]);
                        break;
                }

                if ($success) {
                    $message = "Testimonial " . ucfirst($action) . "d successfully";
                    $message_type = "success";

                    // Log testimonial action
                    $user_id = getAuthUserId();
                    log_admin_activity($user_id, "Testimonial " . ucfirst($action), "Testimonial ID: $id");
                } else {
                    $message = "Error updating testimonial.";
                    $message_type = "danger";
                }
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
                $message_type = "danger";
            }
        }
    }
}

// Get all testimonials - Pending first
$testimonials = [];
try {
    $testimonials = $db->fetchAll("SELECT * FROM testimonials ORDER BY CASE WHEN status = 'pending' THEN 0 ELSE 1 END, created_at DESC");
} catch (Exception $e) {
    $message = "Error fetching testimonials: " . $e->getMessage();
    $message_type = "danger";
}
