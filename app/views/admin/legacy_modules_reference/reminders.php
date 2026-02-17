<?php
/**
 * Payment Reminders - Updated with Session Management
 */

require_once 'admin-functions.php';
use App\Core\Database;

if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$db = \App\Core\App::database();

function fetchPaymentReminders($db) {
    try {
        return $db->fetchAll("SELECT * FROM payment_reminders ORDER BY reminder_date DESC");
    } catch (Exception $e) {
        error_log("Error fetching payment reminders: " . $e->getMessage());
        return [];
    }
}

// Fetch payment reminders from the database
$paymentReminders = fetchPaymentReminders($db);
?>
