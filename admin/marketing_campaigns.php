<?php
session_start();
include 'config.php';
require_role('Admin');
// Add campaign
if (isset($_POST['name'], $_POST['type'], $_POST['message'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $type = $conn->real_escape_string($_POST['type']);
    $msg = $conn->real_escape_string($_POST['message']);
    $scheduled_at = $_POST['scheduled_at'] ? $conn->real_escape_string($_POST['scheduled_at']) : null;
    $conn->query("INSERT INTO marketing_campaigns (name, type, message, scheduled_at, status, created_at) VALUES ('$name', '$type', '$msg', '$scheduled_at', 'scheduled', NOW())");
}
$campaigns = $conn->query("SELECT * FROM marketing_campaigns ORDER BY created_at DESC");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Marketing Campaigns</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Marketing Campaigns</h2><form method='post' class='mb-3'><div class='row'><div class='col'><input name='name' class='form-control' required placeholder='Campaign Name'></div><div class='col'><select name='type' class='form-control'><option value='email'>Email</option><option value='sms'>SMS</option></select></div><div class='col'><input name='scheduled_at' type='datetime-local' class='form-control' placeholder='Schedule (optional)'></div></div><textarea name='message' class='form-control mt-2' required placeholder='Message'></textarea><button class='btn btn-primary mt-2'>Create Campaign</button></form><table class='table table-bordered'><thead><tr><th>Name</th><th>Type</th><th>Message</th><th>Scheduled</th><th>Status</th><th>Created</th></tr></thead><tbody><?php while($c = $campaigns->fetch_assoc()): ?><tr><td><?= htmlspecialchars($c['name']) ?></td><td><?= htmlspecialchars($c['type']) ?></td><td><?= htmlspecialchars($c['message']) ?></td><td><?= htmlspecialchars($c['scheduled_at']) ?></td><td><?= htmlspecialchars($c['status']) ?></td><td><?= htmlspecialchars($c['created_at']) ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
