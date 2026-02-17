<?php
// integration_helpers.php: Helper functions for external integrations
require_once __DIR__ . '/../core/init.php';

function log_integration_activity($type, $status, $payload, $response = null, $error = null)
{
    $db = \App\Core\App::database();
    // Ensure activity log table exists
    try {
        $db->execute("CREATE TABLE IF NOT EXISTS integration_activity_logs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            integration_type VARCHAR(50),
            status VARCHAR(20),
            payload LONGTEXT,
            response LONGTEXT,
            error_message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $payload_str = is_array($payload) ? json_encode($payload) : $payload;
        $response_str = is_array($response) ? json_encode($response) : $response;

        $db->execute(
            "INSERT INTO integration_activity_logs (integration_type, status, payload, response, error_message) VALUES (:type, :status, :payload, :response, :error)",
            ['type' => $type, 'status' => $status, 'payload' => $payload_str, 'response' => $response_str, 'error' => $error]
        );
    } catch (Exception $e) {
        error_log("Integration Activity Log Error: " . $e->getMessage());
    }
}

function get_integration_settings()
{
    $db = \App\Core\App::database();
    try {
        $settings = $db->fetchOne("SELECT * FROM integration_settings WHERE id = :id", ['id' => 1]);
        return $settings ?: [];
    } catch (Exception $e) {
        error_log("Get Integration Settings Error: " . $e->getMessage());
        return [];
    }
}

function send_whatsapp($to, $message)
{
    $settings = get_integration_settings();
    if (empty($settings['whatsapp_api'])) return false;
    // Example: Use WhatsApp API endpoint (pseudo code)
    $url = 'https://api.whatsapp.com/send?apikey=' . $settings['whatsapp_api'] . '&to=' . urlencode($to) . '&msg=' . urlencode($message);
    $result = @file_get_contents($url);
    $success = ($result !== false);

    log_integration_activity('whatsapp', $success ? 'success' : 'failure', ['to' => $to, 'message' => $message], $result);
    return $success;
}

function get_google_client($settings)
{
    if (!class_exists('\Google\Client')) {
        require_once __DIR__ . '/../../../vendor/autoload.php';
    }

    $client = new \Google\Client();
    $client->setClientId($settings['google_drive_client_id'] ?? '');
    $client->setClientSecret($settings['google_drive_client_secret'] ?? '');
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');
    $client->setScopes([
        'https://www.googleapis.com/auth/drive.file',
        'https://www.googleapis.com/auth/spreadsheets'
    ]);
    $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
    if (!empty($settings['google_drive_refresh_token'])) {
        $client->refreshToken($settings['google_drive_refresh_token']);
    }
    return $client;
}

function export_to_google_sheets($data, $spreadsheetId = null)
{
    $settings = get_integration_settings();
    $spreadsheetId = $spreadsheetId ?: ($settings['google_sheets_key'] ?? null);

    if (empty($spreadsheetId) || empty($data)) return false;

    try {
        $client = get_google_client($settings);
        /** @noinspection PhpUndefinedClassInspection */
        $service = new \Google\Service\Sheets($client);

        $range = 'Sheet1!A1'; // Default range
        /** @noinspection PhpUndefinedClassInspection */
        $body = new \Google\Service\Sheets\ValueRange([
            'values' => [$data] // $data should be an array of values
        ]);
        $params = ['valueInputOption' => 'RAW'];

        $result = $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
        $success = $result->getUpdates()->getUpdatedCells() > 0;

        log_integration_activity('google_sheets', $success ? 'success' : 'failure', $data, $result);
        return $success;
    } catch (Exception $e) {
        log_integration_activity('google_sheets', 'error', $data, null, $e->getMessage());
        error_log('Google Sheets export error: ' . $e->getMessage());
        return false;
    }
}

function send_email($to, $subject, $body)
{
    require_once __DIR__ . '/../../includes/notification_manager.php';
    require_once __DIR__ . '/../../includes/email_service.php';

    try {
        $db = \App\Core\App::database();
        $emailService = new EmailService();
        $notificationManager = new NotificationManager($db->getConnection(), $emailService);

        $success = $notificationManager->send([
            'email' => $to,
            'title' => $subject,
            'message' => $body,
            'channels' => ['email']
        ]);

        log_integration_activity('email', $success ? 'success' : 'failure', ['to' => $to, 'subject' => $subject]);
        return $success;
    } catch (Exception $e) {
        log_integration_activity('email', 'error', ['to' => $to, 'subject' => $subject], null, $e->getMessage());
        return false;
    }
}

function send_sms($to, $message)
{
    require_once __DIR__ . '/../../includes/notification_manager.php';

    try {
        $db = \App\Core\App::database();
        $notificationManager = new NotificationManager($db->getConnection());

        $success = $notificationManager->send([
            'phone' => $to,
            'message' => $message,
            'channels' => ['sms']
        ]);

        log_integration_activity('sms', $success ? 'success' : 'failure', ['to' => $to, 'message' => $message]);
        return $success;
    } catch (Exception $e) {
        log_integration_activity('sms', 'error', ['to' => $to, 'message' => $message], null, $e->getMessage());
        return false;
    }
}

function sync_with_crm($lead)
{
    $settings = get_integration_settings();
    if (empty($settings['crm_api']) || empty($lead)) return false;
    // TODO: Implement CRM sync for $lead
    return false;
}

function upload_to_google_drive($localFilePath, $driveFolderId = null, $fileName = null)
{
    $settings = get_integration_settings();
    if (empty($settings['google_drive_client_id']) || empty($settings['google_drive_client_secret']) || empty($settings['google_drive_refresh_token'])) {
        return false;
    }

    try {
        $client = get_google_client($settings);
        /** @noinspection PhpUndefinedClassInspection */
        $service = new \Google\Service\Drive($client);
        $fileMetadata = [
            'name' => $fileName ?: basename($localFilePath)
        ];
        if ($driveFolderId) {
            $fileMetadata['parents'] = [$driveFolderId];
        }
        $content = file_get_contents($localFilePath);
        /** @noinspection PhpUndefinedClassInspection */
        $file = new \Google\Service\Drive\DriveFile($fileMetadata);

        $uploadedFile = $service->files->create($file, [
            'data' => $content,
            'mimeType' => mime_content_type($localFilePath),
            'uploadType' => 'multipart',
            'fields' => 'id,webViewLink,name'
        ]);

        log_integration_activity('google_drive', 'success', ['file' => $fileName ?: basename($localFilePath), 'folder' => $driveFolderId], $uploadedFile);
        return $uploadedFile->id;
    } catch (Exception $e) {
        log_integration_activity('google_drive', 'error', ['file' => $fileName ?: basename($localFilePath)], null, $e->getMessage());
        error_log('Google Drive upload error: ' . $e->getMessage());
        return false;
    }
}

// Store Google Drive file ID in the DB after upload
function upload_to_google_drive_and_save_id($localFilePath, $dbTable, $dbIdField, $dbIdValue, $dbFileIdField, $driveFolderId = null, $fileName = null)
{
    $driveId = upload_to_google_drive($localFilePath, $driveFolderId, $fileName);
    if ($driveId) {
        $db = \App\Core\App::database();
        try {
            $db->execute("UPDATE $dbTable SET $dbFileIdField = :driveId WHERE $dbIdField = :dbIdValue", ['driveId' => $driveId, 'dbIdValue' => $dbIdValue]);
            return $driveId;
        } catch (Exception $e) {
            error_log("Update Google Drive ID Error: " . $e->getMessage());
            return false;
        }
    }
    return false;
}

// Send a message to a Slack channel using webhook URL
function send_slack_notification($message, $webhook_url = null)
{
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
    $error = curl_error($ch);

    $success = ($result !== false);
    log_integration_activity('slack', $success ? 'success' : 'failure', ['message' => $message], $result, $error);
    return $result;
}

// Send a message to a Telegram chat using bot token and chat ID
function send_telegram_notification($message, $bot_token = null, $chat_id = null)
{
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
    $error = curl_error($ch);

    $success = ($result !== false);
    log_integration_activity('telegram', $success ? 'success' : 'failure', ['message' => $message, 'chat_id' => $chat_id], $result, $error);
    return $result;
}
