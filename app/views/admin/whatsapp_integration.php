<?php
/**
 * WhatsApp Integration Settings
 * Configure WhatsApp Business API for chatbot
 */

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login');
    exit;
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
        $db->execute("INSERT INTO ai_settings (setting_key, setting_value, updated_at) 
                      VALUES (?, ?, NOW()) 
                      ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()",
            ['whatsapp_number', $whatsappNumber, $whatsappNumber]);
        
        $db->execute("INSERT INTO ai_settings (setting_key, setting_value, updated_at) 
                      VALUES (?, ?, NOW()) 
                      ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()",
            ['whatsapp_api_key', $apiKey, $apiKey]);
            
        $db->execute("INSERT INTO ai_settings (setting_key, setting_value, updated_at) 
                      VALUES (?, ?, NOW()) 
                      ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()",
            ['whatsapp_welcome', $welcomeMessage, $welcomeMessage]);
            
        $db->execute("INSERT INTO ai_settings (setting_key, setting_value, updated_at) 
                      VALUES (?, ?, NOW()) 
                      ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()",
            ['whatsapp_auto_reply', $autoReply, $autoReply]);
        
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

$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Integration | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #10b981;
            --whatsapp: #25D366;
        }
        
        body { background: #f8fafc; font-family: 'Segoe UI', sans-serif; }
        
        .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 260px;
        }
        
        .sidebar-brand { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a {
            display: block; padding: 12px 20px;
            color: rgba(255,255,255,0.7); text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.1); color: white;
            border-left: 3px solid var(--primary);
        }
        
        .main-content { margin-left: 260px; padding: 30px; }
        
        .form-card {
            background: white; border-radius: 15px;
            padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        
        .whatsapp-card {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white; border-radius: 15px;
            padding: 30px;
        }
        
        .btn-whatsapp {
            background: #25D366; color: white;
            border: none; padding: 10px 25px;
            border-radius: 8px;
        }
        .btn-whatsapp:hover { background: #128C7E; color: white; }
        
        .preview-box {
            background: #e5ddd5; border-radius: 10px;
            padding: 20px; max-width: 400px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .chat-bubble {
            background: #dcf8c6; padding: 10px 15px;
            border-radius: 8px; margin-bottom: 10px;
            max-width: 80%; word-wrap: break-word;
        }
        
        .status-badge {
            display: inline-flex; align-items: center;
            padding: 6px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
        }
        .status-connected { background: #d1fae5; color: #065f46; }
        .status-disconnected { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h5 class="mb-0"><i class="fab fa-whatsapp me-2"></i>WhatsApp</h5>
        </div>
        <div class="sidebar-menu">
            <a href="<?php echo $base; ?>/admin/dashboard"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
            <a href="<?php echo $base; ?>/admin/ai-training"><i class="fas fa-robot me-2"></i>AI Training</a>
            <a href="#" class="active"><i class="fab fa-whatsapp me-2"></i>WhatsApp Setup</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Integration</h2>
                <p class="text-muted">Connect your chatbot to WhatsApp Business API</p>
            </div>
            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>
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
                <div class="form-card mb-4">
                    <h4 class="mb-4"><i class="fas fa-cog me-2 text-primary"></i>WhatsApp Settings</h4>
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
                            <a href="https://wa.me/919277121112" target="_blank" class="btn btn-whatsapp">
                                <i class="fab fa-whatsapp me-2"></i>Test Connection
                            </a>
                        </div>
                    </form>
                </div>

                <!-- QR Code Section -->
                <div class="form-card">
                    <h4 class="mb-4"><i class="fas fa-qrcode me-2 text-primary"></i>Quick Connect</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted">Scan this QR code to start WhatsApp chat:</p>
                            <div class="bg-light p-3 rounded text-center">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://wa.me/919277121112" 
                                     alt="WhatsApp QR Code" class="img-fluid">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Direct Links:</h6>
                            <div class="d-grid gap-2">
                                <a href="https://wa.me/919277121112" target="_blank" class="btn btn-outline-success">
                                    <i class="fab fa-whatsapp me-2"></i>Chat on WhatsApp
                                </a>
                                <button class="btn btn-outline-primary" onclick="copyLink()">
                                    <i class="fas fa-copy me-2"></i>Copy WhatsApp Link
                                </button>
                                <a href="https://api.whatsapp.com/send?phone=919277121112&text=Hi%20APS%20Dream%20Home" 
                                   target="_blank" class="btn btn-outline-info">
                                    <i class="fas fa-comment me-2"></i>Send Pre-filled Message
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview & Status -->
            <div class="col-md-4">
                <div class="whatsapp-card mb-4">
                    <h5 class="mb-3"><i class="fab fa-whatsapp me-2"></i>Connection Status</h5>
                    <div class="status-badge status-connected mb-3">
                        <i class="fas fa-circle me-1"></i>Connected
                    </div>
                    <p class="small">Your chatbot is ready to receive messages on:</p>
                    <h5><?php echo htmlspecialchars($whatsappNumber); ?></h5>
                </div>

                <div class="form-card">
                    <h5 class="mb-3"><i class="fas fa-eye me-2 text-primary"></i>Message Preview</h5>
                    <div class="preview-box">
                        <div class="chat-bubble">
                            <?php echo nl2br(htmlspecialchars($welcomeMessage)); ?>
                            <div class="text-end mt-1">
                                <small class="text-muted">10:30 AM ✓✓</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-card mt-4">
                    <h5 class="mb-3"><i class="fas fa-chart-line me-2 text-primary"></i>Stats</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Messages Today</span>
                        <span class="badge bg-primary">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Messages</span>
                        <span class="badge bg-secondary">0</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Auto-Replies</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyLink() {
            navigator.clipboard.writeText('https://wa.me/919277121112');
            alert('WhatsApp link copied to clipboard!');
        }
    </script>
</body>
</html>
