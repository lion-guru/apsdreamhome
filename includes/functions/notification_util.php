<?php
// Utility functions for notifications
function addNotification($con, $type, $message, $user_id = null) {
    $stmt = $con->prepare("INSERT INTO notifications (type, message, user_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $type, $message, $user_id);
    $stmt->execute();
    $stmt->close();
}
