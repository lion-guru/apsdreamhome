<?php

/**
 * WhatsApp Integration Settings
 * Configure WhatsApp Business API for chatbot
 */

if (!defined('BASE_PATH')) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    if (!isset($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . '/admin/login');
        exit;
    }
}

require_once __DIR__ . '/../../../app/Core/Database/Database.php';
$db = \App\Core\Database\Database::getInstance();

$success = '';
$error = '';

// Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $whatsappNumber = $_POST['whatsapp_number'] ?? '+91 92771 21112';
    $apiKey = $_POST['api_key'] ?? '';
    $webhookUrl = $_POST['webhook_url'] ?? '';
    $welcomeMessage = $_POST['welcome_message'] ?? 'Namaste! APS Dream Home mein aapka swagat hai 🙏';
    $autoReply = isset($_POST['auto_reply']) ? 1 : 0;

    try {
        // Store in ai_settings table
        $db->execute(
            "INSERT INTO ai_settings (setting_key, setting_value, updated_at) 
                      VALUES (?, ?, NOW()) 
                      ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()",
            ['whatsapp_number', $whatsappNumber, $whatsappNumber]
        );

        $db->execute(
            "INSERT INTO ai_settings (setting_key, setting_value, updated_at) 
                      VALUES (?, ?, NOW()) 
                      ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()",
            ['whatsapp_api_key', $apiKey, $apiKey]
        );

        $db->execute(
            "INSERT INTO ai_settings (setting_key, setting_value, updated_at) 
                      VALUES (?, ?, NOW()) 
                      ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()",
            ['whatsapp_welcome', $welcomeMessage, $welcomeMessage]
        );

        $db->execute(
            "INSERT INTO ai_settings (setting_key, setting_value, updated_at) 
                      VALUES (?, ?, NOW()) 
                      ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()",
            ['whatsapp_auto_reply', $autoReply, $autoReply]
        );

        $success = 'WhatsApp settings saved successfully!';
        $_SESSION['notifications'][] = [
            'type' => 'success',
            'message' => 'WhatsApp integration settings updated',
            'time' => date('Y-m-d H:i:s'),
            'link' => '/admin/whatsapp-integration'
        ];
    } catch (Exception $e) {
        $error = 'Error saving settings: ' . $e->getMessage();
    }
}

// Get current settings
$settings = [];
try {
    $rows = $db->fetchAll("SELECT setting_key, setting_value FROM ai_settings WHERE setting_key LIKE 'whatsapp%'");
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Table might not exist
}

$whatsappNumber = $settings['whatsapp_number'] ?? '+91 92771 21112';
$apiKey = $settings['whatsapp_api_key'] ?? '';
$welcomeMessage = $settings['whatsapp_welcome'] ?? 'Namaste! APS Dream Home mein aapka swagat hai 🙏';
$autoReply = ($settings['whatsapp_auto_reply'] ?? '1') === '1';

// Start output buffering for layout
ob_start();
$page_title = 'WhatsApp Integration';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1"><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Integration</h2>
            <p class="text-muted">Connect your chatbot to WhatsApp Business API</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Settings Form -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-cog me-2 text-primary"></i>WhatsApp Settings</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">WhatsApp Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                                    <input type="text" name="whatsapp_number" class="form-control"
                                        value="<?php echo htmlspecialchars($whatsappNumber); ?>" required>
                                </div>
                                <small class="text-muted">Format: +91 92771 21112</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">API Key (Optional)</label>
                                <input type="password" name="api_key" class="form-control"
                                    value="<?php echo htmlspecialchars($apiKey); ?>"
                                    placeholder="WhatsApp Business API Key">
                                <small class="text-muted">For WhatsApp Business API integration</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Welcome Message</label>
                            <textarea name="welcome_message" class="form-control" rows="3" required><?php echo htmlspecialchars($welcomeMessage); ?></textarea>
                            <small class="text-muted">First message sent when user starts chat</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="auto_reply" id="autoReply"
                                    <?php echo $autoReply ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="autoReply">
                                    Enable Auto-Reply
                                </label>
                            </div>
                            <small class="text-muted">Automatically reply to common questions using AI</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                            <a href="https://wa.me/919277121112" target="_blank" class="btn btn-success">
                                <i class="fab fa-whatsapp me-2"></i>Test Connection
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- QR Code Section -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-qrcode me-2 text-primary"></i>QR Code Setup</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Generate QR code to connect WhatsApp Business account</p>
                    <button class="btn btn-outline-primary">
                        <i class="fas fa-qrcode me-2"></i>Generate QR Code
                    </button>
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Setup Guide</h4>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">Get WhatsApp Business API access from Meta</li>
                        <li class="mb-2">Generate API Key and Phone Number ID</li>
                        <li class="mb-2">Configure webhook URL for receiving messages</li>
                        <li class="mb-2">Set up welcome message and auto-reply</li>
                        <li>Test connection with QR code</li>
                    </ol>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Statistics</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Messages Sent:</span>
                        <strong>1,234</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Messages Received:</span>
                        <strong>856</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Active Users:</span>
                        <strong>234</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/admin.php';
?>