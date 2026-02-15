<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser']) || $_SESSION['auser'] !== 'superadmin') { http_response_code(403); exit('Access denied.'); }

$file = isset($_GET['file']) ? basename($_GET['file']) : '';
$path = $LOG_ARCHIVE_DIR . '/' . $file;
$sha_path = $path . '.sha256';
if (!is_file($path) || !is_file($sha_path)) {
    http_response_code(404); exit('Archive or hash not found.');
}
$sha_expected = trim(file_get_contents($sha_path));
$sha_actual = hash_file('sha256', $path);
$ok = hash_equals($sha_expected, $sha_actual);

// Log the verification event
$conn = $con;
$user = $_SESSION['auser'];
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$stmt = $conn->prepare("INSERT INTO audit_access_log (admin_user, action, details, ip_address) VALUES (?, 'archive_verify', ?, ?)");
$details = json_encode(['archive'=>$file, 'expected'=>$sha_expected, 'actual'=>$sha_actual, 'ok'=>$ok]);
$stmt->bind_param('sss', $user, $details, $ip);
$stmt->execute();

// Alert superadmins if verification fails
if (!$ok && !empty($INCIDENT_WEBHOOK_URL)) {
    $payload = [
        'event' => 'archive_verification_failed',
        'archive' => $file,
        'expected' => $sha_expected,
        'actual' => $sha_actual,
        'user' => $user,
        'ip' => $ip,
        'timestamp' => date('c'),
        'summary' => "Archive verification failed for $file by $user ($ip)"
    ];
    $ch = curl_init($INCIDENT_WEBHOOK_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}
?><!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Verify Archive</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4">
<h2>Verify Archive Integrity</h2>
<p><b>Archive:</b> <?= htmlspecialchars($file) ?></p>
<p><b>Expected SHA256:</b> <code><?= htmlspecialchars($sha_expected) ?></code></p>
<p><b>Actual SHA256:</b> <code><?= htmlspecialchars($sha_actual) ?></code></p>
<?php if ($ok): ?>
<div class="alert alert-success">Archive integrity verified (hash matches).</div>
<?php else: ?>
<div class="alert alert-danger">WARNING: Archive integrity check failed (hash mismatch)!</div>
<?php endif; ?>
<a href="log_archive_view.php" class="btn btn-secondary mt-3">Back to Archives</a>
</div></body></html>
