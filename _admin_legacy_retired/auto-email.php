<?php
// Automated Email Alert Script (demo)
// Usage: include or call from booking/approval actions
function send_admin_alert($subject, $message, $to = 'admin@example.com') {
    // Use your real admin email here
    $headers = "From: noreply@apsdreamhomes.com\r\nContent-Type: text/plain; charset=UTF-8";
    return mail($to, $subject, $message, $headers);
}
// Example usage (can be called on new booking, approval, etc.)
// send_admin_alert('New Booking Received', "A new booking has been made by Ravi.");
