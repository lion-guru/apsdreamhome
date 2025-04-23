<?php
// Automated AI Analytics Report Sender
// Sends weekly analytics CSV to configured admin emails
require_once(__DIR__ . '/../includes/classes/Database.php');
require_once(__DIR__ . '/../includes/classes/PHPMailer/PHPMailerAutoload.php');

// CONFIGURATION
$admin_emails = [
    'admin1@example.com', // Replace with real admin emails
    //'admin2@example.com',
];
$from = date('Y-m-d', strtotime('-7 days'));
$to = date('Y-m-d');

// Generate CSV in memory
$db = new Database();
$con = $db->getConnection();
$date_sql = "created_at >= '$from 00:00:00' AND created_at <= '$to 23:59:59'";

$csv = fopen('php://temp', 'w+');
fputcsv($csv, ['id','user_id','role','action','suggestion_text','feedback','notes','created_at']);
$res = mysqli_query($con, "SELECT id,user_id,role,action,suggestion_text,feedback,notes,created_at FROM ai_interactions WHERE $date_sql ORDER BY created_at DESC");
while($row = mysqli_fetch_assoc($res)) {
    fputcsv($csv, $row);
}
rewind($csv);
$csv_content = stream_get_contents($csv);
fclose($csv);

// Prepare email
$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'localhost'; // Adjust as needed
$mail->setFrom('no-reply@yourdomain.com', 'APS Analytics');
$mail->Subject = 'APS AI Analytics Report ('.date('Y-m-d', strtotime('-7 days')).' to '.date('Y-m-d').')';
$mail->Body = "Attached is the weekly APS AI analytics CSV export (".$from." to ".$to.").\n\nThis is an automated message.";
$mail->addStringAttachment($csv_content, 'ai_analytics_report_'.$from.'_to_'.$to.'.csv', 'base64', 'text/csv');
foreach ($admin_emails as $email) {
    $mail->addAddress($email);
}

if ($mail->send()) {
    echo "Report sent to admins.";
} else {
    echo "Failed to send report: ".$mail->ErrorInfo;
}
