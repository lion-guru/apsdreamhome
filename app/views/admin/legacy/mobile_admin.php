<?php
/**
 * APS Dream Home - Mobile Admin Interface
 * Mobile-responsive admin panel for WhatsApp and AI management
 */

require_once __DIR__ . '/core/init.php';

// Authentication check
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../includes/ai_integration.php';
require_once __DIR__ . '/../includes/whatsapp_integration.php';
require_once __DIR__ . '/../includes/email_system.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>APS Dream Home - Mobile Admin</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .mobile-card { margin: 10px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .status-indicator { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .status-online { background: #28a745; }
        .status-offline { background: #dc3545; }
        .status-warning { background: #ffc107; }
        .quick-action-btn { margin: 5px; padding: 15px; border-radius: 10px; font-size: 0.9em; }
        .metric-badge { background: rgba(255,255,255,0.2); color: white; padding: 8px 15px; border-radius: 20px; margin: 5px; }
        .mobile-nav { position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #dee2e6; z-index: 1000; }
        .mobile-nav .nav-link { padding: 15px; text-align: center; color: #6c757d; }
        .mobile-nav .nav-link.active { color: #007bff; background: #f8f9fa; }
        .main-content { margin-bottom: 80px; }
        .template-preview { background: #f8f9fa; padding: 10px; border-radius: 8px; margin: 10px 0; font-size: 0.9em; }
        .variable-badge { background: #e9ecef; color: #495057; padding: 2px 6px; border-radius: 12px; font-size: 0.8em; }
        .mobile-form { background: white; padding: 20px; border-radius: 15px; margin: 10px; }
    </style>
</head>
<body>
    <div class='container-fluid main-content'>
        <!-- Header -->
        <div class='row'>
            <div class='col-12'>
                <div class='mobile-card bg-white text-center'>
                    <h2><i class='fas fa-mobile-alt me-2'></i>Mobile Admin</h2>
                    <p class='mb-0'>APS Dream Home Management</p>
                    <small class='text-muted'>" . date('M d, Y H:i') . "</small>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class='row'>
            <div class='col-12'>
                <div class='mobile-card bg-white'>
                    <h5><i class='fas fa-heartbeat me-2'></i>System Status</h5>
                    <div class='row text-center'>";

$status_checks = [
    'AI System' => $config['ai']['enabled'] ?? false,
    'WhatsApp' => $config['whatsapp']['enabled'] ?? false,
    'Email' => $config['email']['enabled'] ?? false,
    'Database' => true
];

foreach ($status_checks as $service => $enabled) {
    $status_class = $enabled ? 'status-online' : 'status-offline';
    $icon = $enabled ? 'check-circle' : 'times-circle';
    echo "<div class='col-6 mb-2'>";
    echo "<div class='metric-badge'>";
    echo "<span class='status-indicator {$status_class}'></span>";
    echo "<i class='fas fa-{$icon} me-1'></i>{$service}";
    echo "</div>";
    echo "</div>";
}

echo "</div></div></div></div>";

        // Quick Actions
echo "<div class='row'>
    <div class='col-12'>
        <div class='mobile-card bg-white'>
            <h5><i class='fas fa-bolt me-2'></i>Quick Actions</h5>
            <div class='row'>
                <div class='col-6'>
                    <button class='btn btn-primary quick-action-btn w-100' onclick='showWhatsAppTest()'>
                        <i class='fas fa-mobile-alt me-2'></i>Test WhatsApp
                    </button>
                </div>
                <div class='col-6'>
                    <button class='btn btn-success quick-action-btn w-100' onclick='showAITest()'>
                        <i class='fas fa-robot me-2'></i>Test AI
                    </button>
                </div>
                <div class='col-6'>
                    <button class='btn btn-info quick-action-btn w-100' onclick='showEmailTest()'>
                        <i class='fas fa-envelope me-2'></i>Test Email
                    </button>
                </div>
                <div class='col-6'>
                    <button class='btn btn-warning quick-action-btn w-100' onclick='showTemplates()'>
                        <i class='fas fa-edit me-2'></i>Templates
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>";

        // WhatsApp Test Modal
echo "<div id='whatsappModal' class='modal fade' tabindex='-1'>
    <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><i class='fas fa-mobile-alt me-2'></i>Test WhatsApp</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
            </div>
            <div class='modal-body'>
                <div class='mobile-form'>
                    <div class='mb-3'>
                        <label class='form-label'>Phone Number:</label>
                        <input type='text' class='form-control' id='testPhone' value='9876543210' placeholder='Enter phone number'>
                    </div>
                    <div class='mb-3'>
                        <label class='form-label'>Message:</label>
                        <textarea class='form-control' id='testMessage' rows='3' placeholder='Enter test message'>Hello! This is a test from APS Dream Home mobile admin.</textarea>
                    </div>
                    <div class='mb-3'>
                        <label class='form-label'>Template (Optional):</label>
                        <select class='form-control' id='testTemplate'>
                            <option value=''>Select template...</option>
                            <option value='welcome_message'>Welcome Message</option>
                            <option value='property_inquiry'>Property Inquiry</option>
                            <option value='booking_confirmation'>Booking Confirmation</option>
                        </select>
                    </div>
                    <button class='btn btn-success w-100' onclick='sendWhatsAppTest()'>
                        <i class='fas fa-paper-plane me-2'></i>Send Message
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>";

        // AI Test Modal
echo "<div id='aiModal' class='modal fade' tabindex='-1'>
    <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><i class='fas fa-robot me-2'></i>Test AI</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
            </div>
            <div class='modal-body'>
                <div class='mobile-form'>
                    <div class='mb-3'>
                        <label class='form-label'>AI Prompt:</label>
                        <textarea class='form-control' id='aiPrompt' rows='3' placeholder='Enter your question or request'>Help me analyze this PHP code for security issues.</textarea>
                    </div>
                    <button class='btn btn-primary w-100' onclick='sendAITest()'>
                        <i class='fas fa-brain me-2'></i>Ask AI
                    </button>
                    <div id='aiResult' class='mt-3' style='display: none;'>
                        <h6>AI Response:</h6>
                        <div class='alert alert-info' id='aiResponse'></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>";

        // Email Test Modal
echo "<div id='emailModal' class='modal fade' tabindex='-1'>
    <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><i class='fas fa-envelope me-2'></i>Test Email</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
            </div>
            <div class='modal-body'>
                <div class='mobile-form'>
                    <div class='mb-3'>
                        <label class='form-label'>Email Address:</label>
                        <input type='email' class='form-control' id='testEmail' value='test@example.com' placeholder='Enter email address'>
                    </div>
                    <button class='btn btn-info w-100' onclick='sendEmailTest()'>
                        <i class='fas fa-paper-plane me-2'></i>Send Test Email
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>";

        // Templates Modal
echo "<div id='templatesModal' class='modal fade' tabindex='-1'>
    <div class='modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><i class='fas fa-edit me-2'></i>WhatsApp Templates</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
            </div>
            <div class='modal-body'>";

$templates = getWhatsAppTemplates();
foreach ($templates as $name => $template) {
    echo "<div class='template-preview'>";
    echo "<h6><i class='fas fa-file-alt me-2'></i>{$name}</h6>";
    if (!empty($template['header'])) {
        echo "<p><strong>Header:</strong> " . h($template['header']) . "</p>";
    }
    echo "<p><strong>Body:</strong> " . h(substr($template['body'], 0, 100)) . "...</p>";
    if (!empty($template['footer'])) {
        echo "<p><strong>Footer:</strong> " . h($template['footer']) . "</p>";
    }
    if (!empty($template['variables'])) {
        echo "<p><strong>Variables:</strong> ";
        foreach ($template['variables'] as $variable) {
            echo "<span class='variable-badge'>{{$variable}}</span> ";
        }
        echo "</p>";
    }
    echo "<button class='btn btn-sm btn-outline-primary' onclick='testTemplate(\"{$name}\")'>Test</button>";
    echo "</div>";
}

echo "</div></div></div></div>";

        // Recent Activity
echo "<div class='row'>
    <div class='col-12'>
        <div class='mobile-card bg-white'>
            <h5><i class='fas fa-clock me-2'></i>Recent Activity</h5>
            <div id='recentActivity'>
                <div class='text-center text-muted'>
                    <i class='fas fa-spinner fa-spin me-2'></i>Loading activity...
                </div>
            </div>
        </div>
    </div>
</div>";

        // Performance Metrics
echo "<div class='row'>
    <div class='col-12'>
        <div class='mobile-card bg-white'>
            <h5><i class='fas fa-chart-line me-2'></i>Performance</h5>
            <div class='row text-center'>";

try {
    // Create WhatsApp integration instance
    require_once 'includes/whatsapp_integration.php';
    $whatsapp = new WhatsAppIntegration();
    $whatsapp_stats = $whatsapp->getWhatsAppStats();
    $ai_enabled = $config['ai']['enabled'] ?? false;
    $email_enabled = $config['email']['enabled'] ?? false;

    echo "<div class='col-6'>";
    echo "<div class='metric-badge'>";
    echo "<div style='font-size: 1.5em; font-weight: bold;'>" . ($whatsapp_stats['total_sent'] ?? 0) . "</div>";
    echo "<small>WhatsApp Sent</small>";
    echo "</div>";
    echo "</div>";

    echo "<div class='col-6'>";
    echo "<div class='metric-badge'>";
    echo "<div style='font-size: 1.5em; font-weight: bold;'>" . ($ai_enabled ? '✅' : '❌') . "</div>";
    echo "<small>AI Status</small>";
    echo "</div>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='col-12'>";
    echo "<p class='text-danger'>Error loading metrics: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div></div></div></div>";
echo "</div>

<!-- Mobile Navigation -->
<nav class='mobile-nav'>
    <div class='row g-0'>
        <div class='col-3'>
            <a href='#status' class='nav-link active' onclick='showSection(\"status\")'>
                <i class='fas fa-home fa-lg mb-1'></i><br>
                <small>Home</small>
            </a>
        </div>
        <div class='col-3'>
            <a href='#whatsapp' class='nav-link' onclick='showSection(\"whatsapp\")'>
                <i class='fas fa-mobile-alt fa-lg mb-1'></i><br>
                <small>WhatsApp</small>
            </a>
        </div>
        <div class='col-3'>
            <a href='#ai' class='nav-link' onclick='showSection(\"ai\")'>
                <i class='fas fa-robot fa-lg mb-1'></i><br>
                <small>AI</small>
            </a>
        </div>
        <div class='col-3'>
            <a href='#settings' class='nav-link' onclick='showSection(\"settings\")'>
                <i class='fas fa-cog fa-lg mb-1'></i><br>
                <small>Settings</small>
            </a>
        </div>
    </div>
</nav>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
<script src='assets/js/ai_client.js'></script>
<script>
function showWhatsAppTest() {
    new bootstrap.Modal(document.getElementById('whatsappModal')).show();
}

function showAITest() {
    new bootstrap.Modal(document.getElementById('aiModal')).show();
}

function showEmailTest() {
    new bootstrap.Modal(document.getElementById('emailModal')).show();
}

function showTemplates() {
    new bootstrap.Modal(document.getElementById('templatesModal')).show();
}

async function sendWhatsAppTest() {
    const phone = document.getElementById('testPhone').value;
    const message = document.getElementById('testMessage').value;
    const template = document.getElementById('testTemplate').value;

    if (!phone || !message) {
        alert('Please fill in phone number and message');
        return;
    }

    try {
        const response = await fetch('api/test_whatsapp.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ phone, message, template })
        });

        const result = await response.json();

        if (result.success) {
            alert('✅ WhatsApp message sent successfully!');
            bootstrap.Modal.getInstance(document.getElementById('whatsappModal')).hide();
        } else {
            alert('❌ Error: ' + result.error);
        }
    } catch (error) {
        alert('❌ Error: ' + error.message);
    }
}

async function sendAITest() {
    const prompt = document.getElementById('aiPrompt').value;
    const resultDiv = document.getElementById('aiResult');
    const responseDiv = document.getElementById('aiResponse');

    if (!prompt) {
        alert('Please enter a prompt');
        return;
    }

    try {
        resultDiv.style.display = 'block';
        responseDiv.innerHTML = '<i class=\"fas fa-spinner fa-spin me-2\"></i>Thinking...';

        if (typeof window.apsAI !== 'undefined') {
            const result = await apsAI.generateResponse([{'role': 'user', 'content': prompt}]);
            responseDiv.innerHTML = result.response || 'No response received';
        } else {
            responseDiv.innerHTML = 'AI client not loaded';
        }
    } catch (error) {
        responseDiv.innerHTML = 'Error: ' + error.message;
    }
}

async function sendEmailTest() {
    const email = document.getElementById('testEmail').value;

    if (!email) {
        alert('Please enter an email address');
        return;
    }

    try {
        const response = await fetch('api/test_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email })
        });

        const result = await response.json();

        if (result.success) {
            alert('✅ Email sent successfully!');
            bootstrap.Modal.getInstance(document.getElementById('emailModal')).hide();
        } else {
            alert('❌ Error: ' + result.error);
        }
    } catch (error) {
        alert('❌ Error: ' + error.message);
    }
}

async function testTemplate(templateName) {
    try {
        const response = await fetch('api/test_whatsapp_template.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                template_name: templateName,
                phone: '9876543210',
                variables: {
                    customer_name: 'Test User',
                    phone_number: '9277121112'
                }
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('✅ Template test sent successfully!');
        } else {
            alert('❌ Error: ' + result.error);
        }
    } catch (error) {
        alert('❌ Error: ' + error.message);
    }
}

async function loadRecentActivity() {
    try {
        const response = await fetch('api/get_system_logs.php');
        const data = await response.json();

        if (data.success) {
            const activityDiv = document.getElementById('recentActivity');
            activityDiv.innerHTML = data.logs.slice(0, 5).map(log =>
                `<div class='mb-2'><small class='text-muted'>[\${log.timestamp}]</small><br><small>\${log.message}</small></div>`
            ).join('');
        }
    } catch (error) {
        document.getElementById('recentActivity').innerHTML = '<div class=\"text-danger\">Error loading activity</div>';
    }
}

function showSection(section) {
    // Section switching logic for mobile navigation
    const sections = ['status', 'whatsapp', 'ai', 'settings'];
    sections.forEach(s => {
        document.querySelectorAll(`[href='#\${s}']`).forEach(el => {
            el.classList.remove('active');
        });
    });

    document.querySelector(`[href='#\${section}']`).classList.add('active');

    // Scroll to top when switching sections
    window.scrollTo(0, 0);
}

// Load recent activity on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRecentActivity();
    setInterval(loadRecentActivity, 30000); // Refresh every 30 seconds
});
</script>
</body>
</html>";
