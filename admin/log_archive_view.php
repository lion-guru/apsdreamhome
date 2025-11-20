<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser']) || $_SESSION['auser'] !== 'superadmin') { http_response_code(403); exit('Access denied.'); }
if (!is_dir($LOG_ARCHIVE_DIR)) mkdir($LOG_ARCHIVE_DIR, 0700, true);
$archives = glob($LOG_ARCHIVE_DIR . '/*.pw.zip');
usort($archives, function($a, $b) { return filemtime($b) - filemtime($a); });

// Log archive downloads for compliance
if (isset($_GET['download']) && $_SESSION['auser'] === 'superadmin') {
    $file = basename($_GET['download']);
    $path = $LOG_ARCHIVE_DIR . '/' . $file;
    if (is_file($path)) {
        // Log the download event
        global $con;
        $conn = $con;
        $user = $_SESSION['auser'];
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $stmt = $conn->prepare("INSERT INTO audit_access_log (admin_user, action, details, ip_address) VALUES (?, 'archive_download', ?, ?)");
        $details = json_encode(['archive'=>$file]);
        $stmt->bind_param('sss', $user, $details, $ip);
        $stmt->execute();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        readfile($path);
        exit;
    }
}

// Test cloud download and alert on failure
if (isset($_GET['test_cloud']) && $_SESSION['auser'] === 'superadmin') {
    $file = basename($_GET['test_cloud']);
    $cloud = get_cloud_url($LOG_ARCHIVE_DIR . '/' . $file);
    if ($cloud) {
        $ch = curl_init($cloud);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code !== 200) {
            // Log and alert
            global $con;
            $conn = $con;
            $user = $_SESSION['auser'];
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $stmt = $conn->prepare("INSERT INTO audit_access_log (admin_user, action, details, ip_address) VALUES (?, 'cloud_download_failed', ?, ?)");
            $details = json_encode(['archive'=>$file, 'cloud_url'=>$cloud, 'http_code'=>$http_code]);
            $stmt->bind_param('sss', $user, $details, $ip);
            $stmt->execute();
            if (!empty($INCIDENT_WEBHOOK_URL)) {
                $payload = [
                    'event' => 'cloud_download_failed',
                    'archive' => $file,
                    'cloud_url' => $cloud,
                    'http_code' => $http_code,
                    'user' => $user,
                    'ip' => $ip,
                    'timestamp' => date('c'),
                    'summary' => "Cloud download failed for $file by $user ($ip), HTTP $http_code"
                ];
                $ch2 = curl_init($INCIDENT_WEBHOOK_URL);
                curl_setopt($ch2, CURLOPT_POST, 1);
                curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch2);
                curl_close($ch2);
            }
            echo '<div class="alert alert-danger">Cloud download failed (HTTP ' . $http_code . '). Superadmin has been alerted.</div>';
        } else {
            echo '<div class="alert alert-success">Cloud download test succeeded (HTTP 200).</div>';
        }
    }
}

function get_cloud_url($file) {
    $marker = $file . '.uploaded';
    if (is_file($marker)) {
        $url = trim(@file_get_contents($marker));
        if (filter_var($url, FILTER_VALIDATE_URL)) return $url;
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Log Archives</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container py-4">
    <h2>Audit Log Archives</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Archive</th>
                <th>Size</th>
                <th>Last Modified</th>
                <th>Cloud Status</th>
                <th>SHA256 Hash</th>
                <th>Download</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($archives as $file): $cloud = get_cloud_url($file); $sha = @file_get_contents($file.'.sha256'); ?>
            <tr>
                <td><?= basename($file) ?></td>
                <td><?= round(filesize($file)/1024, 1) ?> KB</td>
                <td><?= date('Y-m-d H:i:s', filemtime($file)) ?></td>
                <td><?= $cloud ? '<span class="badge bg-success">Uploaded</span>' : '<span class="badge bg-secondary">Local Only</span>' ?></td>
                <td>
                    <?php if ($sha): ?>
                        <code><?= htmlspecialchars($sha) ?></code>
                        <a href="verify_archive.php?file=<?= urlencode(basename($file)) ?>" class="btn btn-outline-info btn-sm ms-2">Verify</a>
                    <?php else: ?>
                        <span class="text-muted">N/A</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="log_archive_view.php?download=<?= urlencode(basename($file)) ?>" class="btn btn-primary btn-sm">Download</a>
                    <?php if ($cloud): ?>
                        <a href="<?= htmlspecialchars($cloud) ?>" class="btn btn-success btn-sm ms-2" target="_blank">Download from Cloud</a>
                        <a href="log_archive_view.php?test_cloud=<?= urlencode(basename($file)) ?>" class="btn btn-outline-warning btn-sm ms-2">Test Cloud Download</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="mt-3 text-muted">Archives are password-protected ZIPs. Password: <code><?= htmlspecialchars($LOG_ARCHIVE_PASSWORD) ?></code></p>
</div>
</body>
</html>
