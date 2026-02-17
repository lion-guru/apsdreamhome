
<?php
// cron_upload_archives_offsite.php: Upload new encrypted log archives to S3/cloud storage
define('AWS_SDK_LOAD', true);
require_once __DIR__ . '/core/init.php';

if (!$S3_BUCKET || !$S3_KEY || !$S3_SECRET) {
    echo "S3/cloud backup not configured.\n";
    exit(1);
}

require_once __DIR__ . '/../vendor/autoload.php';

/** @var \Aws\S3\S3Client $s3 */
$s3 = new \Aws\S3\S3Client([
    'version' => 'latest',
    'region' => $S3_REGION,
    'credentials' => [
        'key' => $S3_KEY,
        'secret' => $S3_SECRET,
    ],
]);

if (!is_dir($LOG_ARCHIVE_DIR)) mkdir($LOG_ARCHIVE_DIR, 0700, true);
$archives = glob($LOG_ARCHIVE_DIR . '/*.pw.zip');

foreach ($archives as $file) {
    $key = 'log_archives/' . basename($file);
    // Check if already uploaded (could use a DB or a .uploaded marker file)
    $marker = $file . '.uploaded';
    if (file_exists($marker)) continue;
    try {
        $result = $s3->putObject([
            'Bucket' => $S3_BUCKET,
            'Key'    => $key,
            'SourceFile' => $file,
        ]);
        // Also upload the SHA256 file
        $sha_file = $file . '.sha256';
        if (file_exists($sha_file)) {
            $s3->putObject([
                'Bucket' => $S3_BUCKET,
                'Key'    => 'log_archives/' . basename($sha_file),
                'SourceFile' => $sha_file,
            ]);
        }
        // Mark as uploaded
        file_put_contents($marker, $result['ObjectURL']);
        echo "Uploaded $file to $key\n";
    } catch (Exception $e) {
        // Alert superadmins via webhook/email if upload fails
        // config.php is already included via core/init.php
        if (isset($INCIDENT_WEBHOOK_URL) && $INCIDENT_WEBHOOK_URL) {
            $payload = [
                'event' => 'cloud_upload_failed',
                'archive' => basename($file),
                'error' => $e->getMessage(),
                'timestamp' => date('c'),
            ];
            $ch = curl_init($INCIDENT_WEBHOOK_URL);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
        }
        echo "Failed to upload $file: ", $e->getMessage(), "\n";
    }
}
?>
