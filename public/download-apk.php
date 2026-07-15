<?php
/**
 * Bulletproof APK download script for ngrok compatibility.
 * Uses chunked transfer encoding — no Content-Length header.
 * Flushes output aggressively at every level.
 */
$file = __DIR__ . '/downloads/apsdreamhome.apk';
if (!file_exists($file)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'APK not found']);
    exit;
}

$filename = basename($file);
$size = filesize($file);

// Kill ALL output buffering at every level
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Disable compression (already compressed binary)
@ini_set('zlib.output_compression', '0');
@ini_set('output_buffering', '0');
@ini_set('max_execution_time', '300');

// Use octet-stream instead of APK MIME — Chrome on Android blocks APK downloads from HTTPS
header('Content-Type: application/octet-stream');
// No Content-Disposition: attachment — Chrome treats attachment as "dangerous download"
header('Content-Transfer-Encoding: binary');
header('Cache-Control: public, max-age=3600');
header('Pragma: public');
header('X-Accel-Buffering: no');

$chunkSize = 512 * 1024; // 512KB chunks — safer for ngrok
$handle = fopen($file, 'rb');
if (!$handle) {
    http_response_code(500);
    echo 'Error reading file';
    exit;
}

$totalSent = 0;
while (!feof($handle)) {
    $data = fread($handle, $chunkSize);
    if ($data === false) break;
    echo $data;
    $totalSent += strlen($data);
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
    // Small delay to let ngrok flush each chunk
    if ($totalSent % (5 * 1024 * 1024) === 0) {
        usleep(10000); // 10ms pause every 5MB
    }
}
fclose($handle);
exit;
