<?php
// APS Dream Homes Automation Cron Script
// To be run by cron (Linux) or Task Scheduler (Windows) at the desired interval

require_once(__DIR__ . '/includes/config/ai_settings.php');
require_once(__DIR__ . '/includes/config/site_settings.php');
require_once(__DIR__ . '/src/Database/Database.php');
$db = new Database();
$con = $db->getConnection();
$ai = include(__DIR__ . '/includes/config/ai_settings.php');
$site = include(__DIR__ . '/includes/config/site_settings.php');

$logdir = __DIR__ . '/logs';
if (!is_dir($logdir)) mkdir($logdir, 0777, true);
$logfile = $logdir . '/automation.log';
$log = [];
$log[] = str_repeat('=',40);
$log[] = 'Automation run at '.date('Y-m-d H:i:s');

// 1. Auto-Reminders
if (($ai['auto_reminders'] ?? 0) == 1) {
    $freq = $ai['reminder_frequency'] ?? 'daily';
    $today = strtolower(date('l'));
    $send = false;
    if ($freq === 'daily') $send = true;
    if ($freq === 'weekly' && $today === 'monday') $send = true;
    if ($freq === 'monthly' && date('j') == 1) $send = true;
    if ($send) {
        $reminded = 0;
        $res = mysqli_query($con, "SELECT email, name FROM users WHERE profile_complete=0 AND status='active'");
        while ($u = mysqli_fetch_assoc($res)) {
            mail($u['email'],
                'Reminder: Complete Your Profile',
                "Dear {$u['name']},\n\nPlease complete your profile on APS Dream Homes to enjoy all features.\n\nThank you!",
                "From: {$site['notification_email']}\r\nContent-type: text/plain; charset=UTF-8");
            $reminded++;
        }
        $log[] = "Auto-Reminders sent: $reminded";

        // 1A. Custom Reminders: Upcoming Property Visits
        $rem_visit = 0;
        $res = mysqli_query($con, "SELECT u.email, u.name, v.date FROM property_visits v JOIN users u ON v.user_id=u.id WHERE v.date >= CURDATE() AND v.date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND v.notified=0");
        while ($row = mysqli_fetch_assoc($res)) {
            mail($row['email'],
                'Upcoming Property Visit',
                "Dear {$row['name']},\n\nThis is a reminder for your scheduled property visit on {$row['date']}.\n\nThank you!",
                "From: {$site['notification_email']}\r\nContent-type: text/plain; charset=UTF-8");
            $rem_visit++;
        }
        $log[] = "Visit reminders sent: $rem_visit";

        // 1B. Custom Reminders: Pending Document Uploads
        $rem_doc = 0;
        $res = mysqli_query($con, "SELECT email, name FROM users WHERE documents_uploaded=0 AND status='active'");
        while ($u = mysqli_fetch_assoc($res)) {
            mail($u['email'],
                'Pending Document Upload',
                "Dear {$u['name']},\n\nPlease upload your required documents to complete your booking or registration.\n\nThank you!",
                "From: {$site['notification_email']}\r\nContent-type: text/plain; charset=UTF-8");
            $rem_doc++;
        }
        $log[] = "Document reminders sent: $rem_doc";

        // 1C. Custom Reminders: Expiring Bookings
        $rem_exp = 0;
        $res = mysqli_query($con, "SELECT u.email, u.name, p.expiry_date FROM property p JOIN users u ON p.user_id=u.id WHERE p.expiry_date >= CURDATE() AND p.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND p.status='booked'");
        while ($row = mysqli_fetch_assoc($res)) {
            mail($row['email'],
                'Booking Expiry Reminder',
                "Dear {$row['name']},\n\nYour property booking will expire on {$row['expiry_date']}. Please complete any pending steps.\n\nThank you!",
                "From: {$site['notification_email']}\r\nContent-type: text/plain; charset=UTF-8");
            $rem_exp++;
        }
        $log[] = "Expiry reminders sent: $rem_exp";

        // 4. Lead Nurturing: Follow up with new leads (no booking/response in 5 days)
        $nurtured = 0;
        $res = mysqli_query($con, "SELECT email, name FROM users WHERE role='lead' AND DATEDIFF(CURDATE(), created_at) = 5 AND id NOT IN (SELECT user_id FROM property WHERE booked=1)");
        while ($u = mysqli_fetch_assoc($res)) {
            mail($u['email'],
                'We Miss You at APS Dream Homes',
                "Dear {$u['name']},\n\nWe noticed you haven't booked a property yet. Let us know if you need help or more information!\n\nBest,\nAPS Dream Homes Team",
                "From: {$site['notification_email']}\r\nContent-type: text/plain; charset=UTF-8");
            $nurtured++;
        }
        $log[] = "Lead nurturing follow-ups sent: $nurtured";

        // 5. Property Visit Follow-up: Ask for feedback or booking
        $followed = 0;
        $res = mysqli_query($con, "SELECT u.email, u.name, v.property_id, v.date FROM property_visits v JOIN users u ON v.user_id=u.id WHERE v.date = DATE_SUB(CURDATE(), INTERVAL 2 DAY) AND v.feedback_given=0 AND v.booked=0");
        while ($row = mysqli_fetch_assoc($res)) {
            mail($row['email'],
                'How Was Your Property Visit?',
                "Dear {$row['name']},\n\nWe hope your visit on {$row['date']} went well. Please provide feedback or proceed to booking if interested!\n\nThank you!",
                "From: {$site['notification_email']}\r\nContent-type: text/plain; charset=UTF-8");
            $followed++;
        }
        $log[] = "Property visit follow-ups sent: $followed";

        // 6. Payment Reminders: Pending or overdue payments
        $pay_rem = 0;
        $res = mysqli_query($con, "SELECT u.email, u.name, p.amount_due, p.due_date FROM payments p JOIN users u ON p.user_id=u.id WHERE p.status='pending' AND p.due_date <= CURDATE()");
        while ($row = mysqli_fetch_assoc($res)) {
            mail($row['email'],
                'Payment Reminder',
                "Dear {$row['name']},\n\nYou have a pending payment of Rs. {$row['amount_due']} due on {$row['due_date']}. Please pay at your earliest convenience.\n\nThank you!",
                "From: {$site['notification_email']}\r\nContent-type: text/plain; charset=UTF-8");
            $pay_rem++;
        }
        $log[] = "Payment reminders sent: $pay_rem";
    } else {
        $log[] = "Auto-Reminders: Not scheduled today.";
    }
}

// 2. Smarter Ticket Routing: Assign to employee/agent with fewest open tickets
if (($ai['smart_ticket_routing'] ?? 0) == 1) {
    $assigned = 0;
    $res = mysqli_query($con, "SELECT id FROM tickets WHERE assigned_to IS NULL");
    while ($t = mysqli_fetch_assoc($res)) {
        $emp_res = mysqli_query($con, "SELECT e.id, COUNT(t.id) as open_tickets FROM employees e LEFT JOIN tickets t ON e.id=t.assigned_to AND t.status='open' WHERE e.status='active' GROUP BY e.id ORDER BY open_tickets ASC LIMIT 1");
        if ($emp = mysqli_fetch_assoc($emp_res)) {
            mysqli_query($con, "UPDATE tickets SET assigned_to={$emp['id']} WHERE id={$t['id']}");
            $assigned++;
        }
    }
    $log[] = "Tickets smart-routed: $assigned";
}

// 3. Auto-Reports
if (($ai['auto_reports'] ?? 0) == 1) {
    $sched = $ai['report_schedule'] ?? 'weekly';
    $today = strtolower(date('l'));
    $send = false;
    if ($sched === 'daily') $send = true;
    if ($sched === 'weekly' && $today === 'monday') $send = true;
    if ($sched === 'monthly' && date('j') == 1) $send = true;
    if ($send) {
        $users = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM users"))['cnt'];
        $bookings = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM property WHERE status='booked' OR booked=1"))['cnt'];
        $msg = "System Analytics Report:\n\nTotal Users: $users\nTotal Bookings: $bookings\n\nAPS Dream Homes";
        mail($site['notification_email'], 'APS Dream Homes Analytics Report', $msg, "From: {$site['notification_email']}\r\nContent-type: text/plain; charset=UTF-8");
        $log[] = "Auto-Report sent to admin.";
    } else {
        $log[] = "Auto-Report: Not scheduled today.";
    }
}

// Optional: Admin notification of automation summary
if (!empty($ai['automation_notify_admin'])) {
    $summary = implode("\n", $log);
    mail($site['notification_email'], 'APS Automation Log Summary', $summary, "From: {$site['notification_email']}\r\nContent-type: text/plain; charset=UTF-8");
    $log[] = "Admin notified by email.";
}

$log[] = 'Automation tasks completed.';
file_put_contents($logfile, implode("\n", $log)."\n", FILE_APPEND);
echo end($log)."\n";
