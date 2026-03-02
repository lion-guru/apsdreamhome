<?php
/**
 * APS Dream Home - WhatsApp & AI Management Dashboard
 * Comprehensive management interface for all integrations
 */

require_once 'includes/config.php';
require_once 'includes/ai_integration.php';
require_once 'includes/whatsapp_integration.php';
require_once 'includes/email_system.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>APS Dream Home - Management Dashboard</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        .dashboard-card { margin: 15px 0; border-radius: 10px; transition: all 0.3s ease; }
        .dashboard-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .status-badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8em; }
        .success-badge { background: #d4edda; color: #155724; }
        .warning-badge { background: #fff3cd; color: #856404; }
        .danger-badge { background: #f8d7da; color: #721c24; }
        .info-badge { background: #d1ecf1; color: #0c5460; }
        .metric-card { text-align: center; padding: 20px; }
        .metric-value { font-size: 2em; font-weight: bold; }
        .metric-label { color: #6c757d; font-size: 0.9em; }
        .nav-tabs .nav-link { border: none; background: none; color: #6c757d; }
        .nav-tabs .nav-link.active { background: #007bff; color: white; border-radius: 5px 5px 0 0; }
        .tab-content { background: white; border-radius: 0 0 10px 10px; padding: 20px; }
        .config-item { padding: 15px; margin: 10px 0; background: #f8f9fa; border-radius: 5px; }
        .log-entry { padding: 10px; margin: 5px 0; background: #f8f9fa; border-radius: 3px; font-family: monospace; font-size: 0.9em; }
        .chart-container { height: 300px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class='container-fluid py-4'>
        <!-- Header -->
        <div class='row mb-4'>
            <div class='col-12'>
                <div class='card dashboard-card' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;'>
                    <div class='card-body text-center'>
                        <h1><i class='fas fa-cogs me-3'></i>APS Dream Home - Management Dashboard</h1>
                        <p class='mb-0'>Comprehensive Control Panel for AI, WhatsApp & Email Integrations</p>
                        <small>Last Updated: " . date('Y-m-d H:i:s') . "</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class='nav nav-tabs mb-4' id='dashboardTabs' role='tablist'>
            <li class='nav-item' role='presentation'>
                <button class='nav-link active' id='overview-tab' data-bs-toggle='tab' data-bs-target='#overview' type='button' role='tab'>
                    <i class='fas fa-tachometer-alt me-2'></i>Overview
                </button>
            </li>
            <li class='nav-item' role='presentation'>
                <button class='nav-link' id='ai-tab' data-bs-toggle='tab' data-bs-target='#ai' type='button' role='tab'>
                    <i class='fas fa-robot me-2'></i>AI Management
                </button>
            </li>
            <li class='nav-item' role='presentation'>
                <button class='nav-link' id='whatsapp-tab' data-bs-toggle='tab' data-bs-target='#whatsapp' type='button' role='tab'>
                    <i class='fas fa-mobile-alt me-2'></i>WhatsApp Management
                </button>
            </li>
            <li class='nav-item' role='presentation'>
                <button class='nav-link' id='email-tab' data-bs-toggle='tab' data-bs-target='#email' type='button' role='tab'>
                    <i class='fas fa-envelope me-2'></i>Email Management
                </button>
            </li>
            <li class='nav-item' role='presentation'>
                <button class='nav-link' id='logs-tab' data-bs-toggle='tab' data-bs-target='#logs' type='button' role='tab'>
                    <i class='fas fa-file-alt me-2'></i>System Logs
                </button>
            </li>
            <li class='nav-item' role='presentation'>
                <button class='nav-link' id='templates-tab' data-bs-toggle='tab' data-bs-target='#templates' type='button' role='tab'>
                    <i class='fas fa-edit me-2'></i>WhatsApp Templates
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class='tab-content' id='dashboardTabContent'>";

// OVERVIEW TAB
echo "<div class='tab-pane fade show active' id='overview' role='tabpanel'>
    <div class='row'>
        <!-- System Status -->
        <div class='col-md-6'>
            <div class='card dashboard-card'>
                <div class='card-header'>
                    <h4><i class='fas fa-heartbeat me-2'></i>System Status</h4>
                </div>
                <div class='card-body'>";

$status_checks = [
    'AI System' => $config['ai']['enabled'] ?? false,
    'WhatsApp Integration' => $config['whatsapp']['enabled'] ?? false,
    'Email System' => $config['email']['enabled'] ?? false,
    'Database Connection' => true
];

foreach ($status_checks as $service => $enabled) {
    $status = $enabled ? 'success' : 'danger';
    $icon = $enabled ? 'check-circle' : 'times-circle';
    $badge_class = $enabled ? 'success-badge' : 'danger-badge';

    echo "<div class='d-flex justify-content-between align-items-center mb-2'>";
    echo "<span>{$service}</span>";
    echo "<span class='status-badge {$badge_class}'><i class='fas fa-{$icon} me-1'></i>" . ($enabled ? 'Active' : 'Inactive') . "</span>";
    echo "</div>";
}

echo "</div></div></div>";

        // Quick Metrics
echo "<div class='col-md-6'>
    <div class='card dashboard-card'>
        <div class='card-header'>
            <h4><i class='fas fa-chart-line me-2'></i>Quick Metrics</h4>
        </div>
        <div class='card-body'>";

try {
    // AI Stats (placeholder - implement actual stats)
    $ai_interactions = 0;
    $whatsapp_messages = 0;
    $email_sent = 0;

    echo "<div class='row text-center'>";
    echo "<div class='col-4'><div class='metric-card'>";
    echo "<div class='metric-value text-primary'>{$ai_interactions}</div>";
    echo "<div class='metric-label'>AI Interactions</div>";
    echo "</div></div>";
    echo "<div class='col-4'><div class='metric-card'>";
    echo "<div class='metric-value text-success'>{$whatsapp_messages}</div>";
    echo "<div class='metric-label'>WhatsApp Sent</div>";
    echo "</div></div>";
    echo "<div class='col-4'><div class='metric-card'>";
    echo "<div class='metric-value text-info'>{$email_sent}</div>";
    echo "<div class='metric-label'>Emails Sent</div>";
    echo "</div></div>";
    echo "</div>";

} catch (Exception $e) {
    echo "<p class='text-danger'>Error loading metrics: " . $e->getMessage() . "</p>";
}

echo "</div></div></div></div>";

// Quick Actions
echo "<div class='row mt-4'>
    <div class='col-12'>
        <div class='card dashboard-card'>
            <div class='card-header'>
                <h4><i class='fas fa-rocket me-2'></i>Quick Actions</h4>
            </div>
            <div class='card-body text-center'>
                <a href='comprehensive_system_test.php' class='btn btn-primary me-2'><i class='fas fa-vial me-2'></i>Run System Tests</a>
                <a href='ai_demo.php' class='btn btn-success me-2'><i class='fas fa-robot me-2'></i>Test AI Features</a>
                <a href='test_whatsapp_integration.php' class='btn btn-warning me-2'><i class='fas fa-mobile-alt me-2'></i>Test WhatsApp</a>
                <a href='test_email_system.php' class='btn btn-info me-2'><i class='fas fa-envelope me-2'></i>Test Email</a>
                <button onclick='refreshDashboard()' class='btn btn-secondary'><i class='fas fa-redo me-2'></i>Refresh</button>
            </div>
        </div>
    </div>
</div>";

echo "</div>";

// AI MANAGEMENT TAB
echo "<div class='tab-pane fade' id='ai' role='tabpanel'>
    <div class='row'>
        <div class='col-md-8'>
            <div class='card dashboard-card'>
                <div class='card-header'>
                    <h4><i class='fas fa-robot me-2'></i>AI Configuration</h4>
                </div>
                <div class='card-body'>
                    <div class='config-item'>
                        <strong>Status:</strong> " . ($config['ai']['enabled'] ? '<span class="status-badge success-badge">Enabled</span>' : '<span class="status-badge danger-badge">Disabled</span>') . "<br>
                        <strong>Provider:</strong> " . ($config['ai']['provider'] ?? 'Not configured') . "<br>
                        <strong>Model:</strong> " . ($config['ai']['model'] ?? 'Not configured') . "<br>
                        <strong>API Key:</strong> " . (strlen($config['ai']['api_key'] ?? '') > 20 ? substr($config['ai']['api_key'], 0, 20) . '...' : 'Not configured') . "
                    </div>

                    <h5>AI Features:</h5>
                    <div class='row'>";
$ai_features = $config['ai']['features'] ?? [];
foreach (['property_descriptions' => 'Property Descriptions', 'chatbot' => 'Chatbot', 'code_analysis' => 'Code Analysis', 'recommendations' => 'Recommendations'] as $feature => $label) {
    $enabled = $ai_features[$feature] ?? false;
    echo "<div class='col-md-6'>";
    echo "<div class='config-item'>";
    echo "<i class='fas fa-" . ($enabled ? 'check-circle text-success' : 'times-circle text-danger') . " me-2'></i>";
    echo "{$label}: " . ($enabled ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>');
    echo "</div></div>";
}
echo "</div></div></div></div>";

        // AI Test Interface
echo "<div class='col-md-4'>
    <div class='card dashboard-card'>
        <div class='card-header'>
            <h4><i class='fas fa-flask me-2'></i>AI Test</h4>
        </div>
        <div class='card-body'>
            <div class='mb-3'>
                <label for='aiTestInput' class='form-label'>Test Prompt:</label>
                <textarea class='form-control' id='aiTestInput' rows='3' placeholder='Enter a test prompt for AI...'>Hello, can you help me analyze this PHP code?</textarea>
            </div>
            <button onclick='testAI()' class='btn btn-primary w-100'><i class='fas fa-play me-2'></i>Test AI Response</button>
            <div id='aiTestResult' class='mt-3' style='display: none;'>
                <h6>AI Response:</h6>
                <div class='alert alert-info' id='aiResponse'></div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>";

// WHATSAPP MANAGEMENT TAB
echo "<div class='tab-pane fade' id='whatsapp' role='tabpanel'>
    <div class='row'>
        <div class='col-md-8'>
            <div class='card dashboard-card'>
                <div class='card-header'>
                    <h4><i class='fas fa-mobile-alt me-2'></i>WhatsApp Configuration</h4>
                </div>
                <div class='card-body'>
                    <div class='config-item'>
                        <strong>Status:</strong> " . ($config['whatsapp']['enabled'] ? '<span class="status-badge success-badge">Enabled</span>' : '<span class="status-badge danger-badge">Disabled</span>') . "<br>
                        <strong>Phone Number:</strong> " . ($config['whatsapp']['phone_number'] ?? 'Not configured') . "<br>
                        <strong>Country Code:</strong> " . ($config['whatsapp']['country_code'] ?? 'Not configured') . "<br>
                        <strong>API Provider:</strong> " . ($config['whatsapp']['api_provider'] ?? 'Not configured') . "
                    </div>

                    <h5>WhatsApp Features:</h5>
                    <div class='row'>";
$whatsapp_features = $config['whatsapp']['notification_types'] ?? [];
foreach (['welcome_message' => 'Welcome Messages', 'property_inquiry' => 'Property Inquiries', 'booking_confirmation' => 'Booking Confirmations', 'commission_alert' => 'Commission Alerts'] as $feature => $label) {
    $enabled = $whatsapp_features[$feature] ?? false;
    echo "<div class='col-md-6'>";
    echo "<div class='config-item'>";
    echo "<i class='fas fa-" . ($enabled ? 'check-circle text-success' : 'times-circle text-danger') . " me-2'></i>";
    echo "{$label}: " . ($enabled ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>');
    echo "</div></div>";
}
echo "</div></div></div></div>";

        // WhatsApp Test Interface
echo "<div class='col-md-4'>
    <div class='card dashboard-card'>
        <div class='card-header'>
            <h4><i class='fas fa-paper-plane me-2'></i>WhatsApp Test</h4>
        </div>
        <div class='card-body'>
            <div class='mb-3'>
                <label for='whatsappTestPhone' class='form-label'>Phone Number:</label>
                <input type='text' class='form-control' id='whatsappTestPhone' placeholder='9876543210' value='9876543210'>
            </div>
            <div class='mb-3'>
                <label for='whatsappTestMessage' class='form-label'>Message:</label>
                <textarea class='form-control' id='whatsappTestMessage' rows='3' placeholder='Enter test message...'>Hello! This is a test message from APS Dream Home WhatsApp integration.</textarea>
            </div>
            <button onclick='testWhatsApp()' class='btn btn-success w-100'><i class='fas fa-paper-plane me-2'></i>Send Test Message</button>
            <div id='whatsappTestResult' class='mt-3' style='display: none;'>
                <div class='alert' id='whatsappResponse'></div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>";

// EMAIL MANAGEMENT TAB
echo "<div class='tab-pane fade' id='email' role='tabpanel'>
    <div class='row'>
        <div class='col-md-8'>
            <div class='card dashboard-card'>
                <div class='card-header'>
                    <h4><i class='fas fa-envelope me-2'></i>Email Configuration</h4>
                </div>
                <div class='card-body'>
                    <div class='config-item'>
                        <strong>Status:</strong> " . ($config['email']['enabled'] ? '<span class="status-badge success-badge">Enabled</span>' : '<span class="status-badge danger-badge">Disabled</span>') . "<br>
                        <strong>SMTP Host:</strong> " . ($config['email']['smtp_host'] ?? 'Not configured') . "<br>
                        <strong>SMTP Port:</strong> " . ($config['email']['smtp_port'] ?? 'Not configured') . "<br>
                        <strong>From Email:</strong> " . ($config['email']['from_email'] ?? 'Not configured') . "
                    </div>
                </div>
            </div>
        </div>

        <div class='col-md-4'>
            <div class='card dashboard-card'>
                <div class='card-header'>
                    <h4><i class='fas fa-cog me-2'></i>Email Test</h4>
                </div>
                <div class='card-body'>
                    <div class='mb-3'>
                        <label for='emailTestAddress' class='form-label'>Email Address:</label>
                        <input type='email' class='form-control' id='emailTestAddress' placeholder='test@example.com' value='test@example.com'>
                    </div>
                    <button onclick='testEmail()' class='btn btn-info w-100'><i class='fas fa-envelope me-2'></i>Send Test Email</button>
                    <div id='emailTestResult' class='mt-3' style='display: none;'>
                        <div class='alert' id='emailResponse'></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>";

// LOGS TAB
echo "<div class='tab-pane fade' id='logs' role='tabpanel'>
    <div class='row'>
        <div class='col-12'>
            <div class='card dashboard-card'>
                <div class='card-header d-flex justify-content-between align-items-center'>
                    <h4><i class='fas fa-file-alt me-2'></i>System Logs</h4>
                    <button onclick='refreshLogs()' class='btn btn-sm btn-outline-primary'><i class='fas fa-redo me-2'></i>Refresh</button>
                </div>
                <div class='card-body'>
                    <div class='log-output' id='systemLogs'>
                        <div class='text-center text-muted'>
                            <i class='fas fa-spinner fa-spin me-2'></i>Loading logs...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>";

// WHATSAPP TEMPLATES TAB
echo "<div class='tab-pane fade' id='templates' role='tabpanel'>";
echo "<div class='card template-card'>";
echo "<div class='card-header d-flex justify-content-between align-items-center'>";
echo "<h4><i class='fas fa-edit me-2'></i>WhatsApp Templates</h4>";
echo "<a href='whatsapp_template_manager.php' class='btn btn-success btn-sm'><i class='fas fa-external-link-alt me-2'></i>Open Template Manager</a>";
echo "</div>";
echo "<div class='card-body'>";
echo "<div class='row'>";
echo "<div class='col-md-8'>";
echo "<h5>Available Templates:</h5>";
echo "<div class='list-group'>";

$templates = getWhatsAppTemplates();
foreach ($templates as $name => $template) {
    echo "<div class='list-group-item d-flex justify-content-between align-items-center'>";
    echo "<div>";
    echo "<strong>{$name}</strong><br>";
    echo "<small class='text-muted'>{$template['category']} • {$template['language']} • " . count($template['variables']) . " variables</small>";
    echo "</div>";
    echo "<span class='badge bg-primary'>{$template['category']}</span>";
    echo "</div>";
}

echo "</div>";
echo "</div>";
echo "<div class='col-md-4'>";
echo "<h5>Quick Actions:</h5>";
echo "<div class='d-grid gap-2'>";
echo "<a href='whatsapp_template_manager.php' class='btn btn-primary'><i class='fas fa-plus me-2'></i>Create New Template</a>";
echo "<a href='whatsapp_template_manager.php' class='btn btn-outline-primary'><i class='fas fa-edit me-2'></i>Manage Templates</a>";
echo "<a href='whatsapp_template_manager.php#test' class='btn btn-outline-success'><i class='fas fa-vial me-2'></i>Test Templates</a>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "</div></div>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
<script src='assets/js/ai_client.js'></script>
<script>
async function testAI() {
    const prompt = document.getElementById('aiTestInput').value;
    const resultDiv = document.getElementById('aiTestResult');
    const responseDiv = document.getElementById('aiResponse');

    try {
        resultDiv.style.display = 'block';
        responseDiv.innerHTML = '<i class=\"fas fa-spinner fa-spin me-2\"></i>Testing AI...';

        // Use the AI client for testing
        if (typeof window.apsAI !== 'undefined') {
            const result = await apsAI.generateResponse([{'role': 'user', 'content': prompt}]);
            responseDiv.innerHTML = result.response || 'No response received';
            responseDiv.className = 'alert alert-success';
        } else {
            responseDiv.innerHTML = 'AI client not loaded';
            responseDiv.className = 'alert alert-warning';
        }
    } catch (error) {
        responseDiv.innerHTML = 'Error: ' + error.message;
        responseDiv.className = 'alert alert-danger';
    }
}

async function testWhatsApp() {
    const phone = document.getElementById('whatsappTestPhone').value;
    const message = document.getElementById('whatsappTestMessage').value;
    const resultDiv = document.getElementById('whatsappTestResult');
    const responseDiv = document.getElementById('whatsappResponse');

    try {
        resultDiv.style.display = 'block';
        responseDiv.innerHTML = '<i class=\"fas fa-spinner fa-spin me-2\"></i>Sending WhatsApp message...';

        const response = await fetch('api/test_whatsapp.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ phone, message })
        });

        const result = await response.json();

        if (result.success) {
            responseDiv.innerHTML = '✅ WhatsApp message sent successfully!';
            responseDiv.className = 'alert alert-success';
        } else {
            responseDiv.innerHTML = '❌ Error: ' + result.error;
            responseDiv.className = 'alert alert-danger';
        }
    } catch (error) {
        responseDiv.innerHTML = '❌ Error: ' + error.message;
        responseDiv.className = 'alert alert-danger';
    }
}

async function testEmail() {
    const email = document.getElementById('emailTestAddress').value;
    const resultDiv = document.getElementById('emailTestResult');
    const responseDiv = document.getElementById('emailResponse');

    try {
        resultDiv.style.display = 'block';
        responseDiv.innerHTML = '<i class=\"fas fa-spinner fa-spin me-2\"></i>Sending email...';

        const response = await fetch('api/test_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email })
        });

        const result = await response.json();

        if (result.success) {
            responseDiv.innerHTML = '✅ Email sent successfully!';
            responseDiv.className = 'alert alert-success';
        } else {
            responseDiv.innerHTML = '❌ Error: ' + result.error;
            responseDiv.className = 'alert alert-danger';
        }
    } catch (error) {
        responseDiv.innerHTML = '❌ Error: ' + error.message;
        responseDiv.className = 'alert alert-danger';
    }
}

function refreshDashboard() {
    location.reload();
}

async function refreshLogs() {
    const logsDiv = document.getElementById('systemLogs');
    logsDiv.innerHTML = '<div class=\"text-center text-muted\"><i class=\"fas fa-spinner fa-spin me-2\"></i>Loading logs...</div>';

    try {
        const response = await fetch('api/get_system_logs.php');
        const data = await response.json();

        if (data.success) {
            logsDiv.innerHTML = data.logs.map(log =>
                '<div class=\"log-entry\"><strong>[' + log.timestamp + ']</strong> ' + log.message + '</div>'
            ).join('');
        } else {
            logsDiv.innerHTML = '<div class=\"text-danger\">Error loading logs: ' + data.error + '</div>';
        }
    } catch (error) {
        logsDiv.innerHTML = '<div class=\"text-danger\">Error: ' + error.message + '</div>';
    }
}

// Load logs on page load
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh logs every 30 seconds
    setInterval(refreshLogs, 30000);
});
</script>
</body>
</html>";
