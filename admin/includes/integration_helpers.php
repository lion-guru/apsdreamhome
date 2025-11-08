<?php
// integration_helpers.php: Helper functions for external integrations
require_once __DIR__ . '/../../includes/db_config.php';

function get_integration_settings() {
    $conn = getDbConnection();
    $settings = $conn->query("SELECT * FROM integration_settings WHERE id=1")->fetch_assoc();
    return $settings ?: [];
}

function send_whatsapp($to, $message) {
    $settings = get_integration_settings();
    if (empty($settings['whatsapp_api'])) return false;
    // Example: Use WhatsApp API endpoint (pseudo code)
    // file_get_contents('https://api.whatsapp.com/send?apikey=' . $settings['whatsapp_api'] . '&to=' . urlencode($to) . '&msg=' . urlencode($message));
    return true;
}

function export_to_google_sheets($data) {
    $settings = get_integration_settings();
    if (empty($settings['google_sheets_key'])) return false;
    // Example: Use Google Sheets API (pseudo code)
    // ...
    return true;
}

function send_email($to, $subject, $body) {
    $settings = get_integration_settings();
    if (empty($settings['email_host']) || empty($settings['email_user']) || empty($settings['email_pass'])) return false;
    // Example: Use PHPMailer or mail() (pseudo code)
    // ...
    return true;
}

function send_sms($to, $message) {
    $settings = get_integration_settings();
    if (empty($settings['sms_api'])) return false;
    // Example: Use SMS API (pseudo code)
    // ...
    return true;
}

function sync_with_crm($lead) {
    $settings = get_integration_settings();
    if (empty($settings['crm_api'])) return false;
    // Example: Use CRM API (pseudo code)
    // ...
    return true;
}

function upload_to_google_drive($localFilePath, $driveFolderId = null, $fileName = null) {
    $settings = get_integration_settings();
    if (empty($settings['google_drive_client_id']) || empty($settings['google_drive_client_secret']) || empty($settings['google_drive_refresh_token'])) {
        return false;
    }
    // Require Google Client library (assume installed via Composer, or add your own loader)
    if (!class_exists('Google_Client')) {
        require_once __DIR__ . '/../../../vendor/autoload.php';
    }

    if (!class_exists('Google_Client')) {
        error_log('Google Client library not available');
        return false;
    }
    
    $client = new Google_Client();
    $client->setClientId($settings['google_drive_client_id']);
    $client->setClientSecret($settings['google_drive_client_secret']);
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');
    $client->setScopes(['https://www.googleapis.com/auth/drive.file']);
    $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
    $client->refreshToken($settings['google_drive_refresh_token']);

    // Check if Google API client is available
    if (!class_exists('Google_Service_Drive')) {
        error_log('Google API client not available for Drive upload');
        return false;
    }

    $service = new Google_Service_Drive($client);
    $fileMetadata = [
        'name' => $fileName ?: basename($localFilePath)
    ];
    if ($driveFolderId) {
        $fileMetadata['parents'] = [$driveFolderId];
    }
    $content = file_get_contents($localFilePath);
    $file = new Google_Service_Drive_DriveFile($fileMetadata);
    try {
        $uploadedFile = $service->files->create($file, [
            'data' => $content,
            'mimeType' => mime_content_type($localFilePath),
            'uploadType' => 'multipart',
            'fields' => 'id,webViewLink,name'
        ]);
        return $uploadedFile->id;
    } catch (Exception $e) {
        error_log('Google Drive upload error: ' . $e->getMessage());
        return false;
    }
}

// Store Google Drive file ID in the DB after upload
function upload_to_google_drive_and_save_id($localFilePath, $dbTable, $dbIdField, $dbIdValue, $dbFileIdField, $driveFolderId = null, $fileName = null) {
    $driveId = upload_to_google_drive($localFilePath, $driveFolderId, $fileName);
    if ($driveId) {
        $conn = getDbConnection();
        $stmt = $conn->prepare("UPDATE $dbTable SET $dbFileIdField = ? WHERE $dbIdField = ?");
        $stmt->bind_param('ss', $driveId, $dbIdValue);
        $stmt->execute();
        $stmt->close();
        return $driveId;
    }
    return false;
}

// Send a message to a Slack channel using webhook URL
function send_slack_notification($message, $webhook_url = null) {
    if (!$webhook_url) {
        $settings = get_integration_settings();
        if (empty($settings['slack_webhook_url'])) return false;
        $webhook_url = $settings['slack_webhook_url'];
    }
    $payload = json_encode(["text" => $message]);
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

// Send a message to a Telegram chat using bot token and chat ID
function send_telegram_notification($message, $bot_token = null, $chat_id = null) {
    if (!$bot_token || !$chat_id) {
        $settings = get_integration_settings();
        if (empty($settings['telegram_bot_token']) || empty($settings['telegram_chat_id'])) return false;
        $bot_token = $settings['telegram_bot_token'];
        $chat_id = $settings['telegram_chat_id'];
    }
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
