<?php
/**
 * SMS Notifications Management Interface
 * Configure and test SMS notifications
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/notification/sms_manager.php';
require_once __DIR__ . '/../includes/middleware/rate_limit_middleware.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['auser'])) {
    header('location:index.php');
    exit();
}

// Apply rate limiting
$rateLimitMiddleware->handle('admin');

// Initialize SMS manager
$smsManager = new SmsManager();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'test_sms':
            $result = $smsManager->sendTestAlert();
            echo json_encode($result);
            break;
            
        case 'get_status':
            $status = [
                'enabled' => $smsManager->isEnabled(),
                'alert_types' => $smsManager->getAlertTypes()
            ];
            echo json_encode($status);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Notifications - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .alert-type-card {
            margin-bottom: 15px;
        }
        .status-enabled { color: #198754; }
        .status-disabled { color: #dc3545; }
        .cooldown-info { font-size: 0.9em; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">SMS Notifications</h1>
        
        <!-- Status Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">System Status</div>
                    <div class="card-body">
                        <div id="systemStatus">Loading...</div>
                        <div class="mt-3">
                            <button id="testSms" class="btn btn-primary">Send Test SMS</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alert Types -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Alert Types</div>
                    <div class="card-body">
                        <div id="alertTypesList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load system status
        function loadStatus() {
            $.get('sms_notifications.php', { action: 'get_status' }, function(status) {
                // Update system status
                const statusHtml = `
                    <h5 class="mb-3 ${status.enabled ? 'status-enabled' : 'status-disabled'}">
                        <i class="bi bi-${status.enabled ? 'check-circle' : 'x-circle'}"></i>
                        SMS Notifications: ${status.enabled ? 'Enabled' : 'Disabled'}
                    </h5>
                `;
                $('#systemStatus').html(statusHtml);
                $('#testSms').prop('disabled', !status.enabled);
                
                // Update alert types list
                const $alertsList = $('#alertTypesList').empty();
                
                Object.entries(status.alert_types).forEach(([type, cooldown]) => {
                    const formattedType = type.split('_')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ');
                    
                    $alertsList.append(`
                        <div class="card alert-type-card">
                            <div class="card-body">
                                <h5 class="card-title">${formattedType}</h5>
                                <div class="cooldown-info">
                                    Cooldown Period: ${formatCooldown(cooldown)}
                                </div>
                            </div>
                        </div>
                    `);
                });
            });
        }

        // Format cooldown period
        function formatCooldown(seconds) {
            if (seconds < 60) {
                return `${seconds} seconds`;
            } else if (seconds < 3600) {
                return `${Math.floor(seconds / 60)} minutes`;
            } else {
                return `${Math.floor(seconds / 3600)} hours`;
            }
        }

        // Send test SMS
        $('#testSms').click(function() {
            const $btn = $(this);
            $btn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm"></span> Sending...');
            
            $.get('sms_notifications.php', { action: 'test_sms' }, function(result) {
                if (result.success) {
                    alert('Test SMS sent successfully!');
                } else {
                    alert('Failed to send test SMS: ' + (result.error || 'Unknown error'));
                }
            })
            .always(function() {
                $btn.prop('disabled', false)
                    .text('Send Test SMS');
            });
        });

        // Initial load
        loadStatus();
    </script>
</body>
</html>
