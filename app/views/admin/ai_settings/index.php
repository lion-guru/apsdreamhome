<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/admin/login');
    exit;
}

// Set page variables
$$page_title = 'AI Settings - APS Dream Home';
$active_page = 'ai-settings';

// Get current API key (masked)
$currentKey = $geminiService->getApiKey();
$maskedKey = substr($currentKey, 0, 10) . '...' . substr($currentKey, -10);

// Content for base layout
ob_start();


// Get usage statistics
$stats = $geminiService->getUsageStats();

// Get recent API logs
$recentLogs = $db->fetchAll(
'SELECT * FROM ai_api_logs WHERE service = ? ORDER BY created_at DESC LIMIT 10',
['gemini']
);

// Include admin header
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= BASE_URL ?>/admin/ai-settings">
                            <i class="fas fa-robot"></i> AI Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/properties">
                            <i class="fas fa-building"></i> Properties
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/users">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/legal-pages">
                            <i class="fas fa-gavel"></i> Legal Pages
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-robot me-2"></i>
                    AI Settings & Management
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="testConnection()">
                            <i class="fas fa-plug"></i> Test Connection
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshStats()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- API Key Management -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-key me-2"></i>
                                Gemini API Key Management
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <form id="apiKeyForm">
                                        <div class="mb-3">
                                            <label for="apiKey" class="form-label">Current API Key</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="apiKey"
                                                    value="<?= $maskedKey ?>" placeholder="Enter Gemini API Key">
                                                <button class="btn btn-outline-secondary" type="button" onclick="toggleApiKeyVisibility()">
                                                    <i class="fas fa-eye" id="apiKeyToggle"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">
                                                Format: AIzaSy... (starts with AIzaSy, get from <a href="https://aistudio.google.com/apikey" target="_blank">Google AI Studio</a>)
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Update API Key
                                            </button>
                                            <button type="button" class="btn btn-outline-primary ms-2" onclick="testCurrentKey()">
                                                <i class="fas fa-check-circle me-2"></i>Test Current Key
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle me-2"></i>API Key Information</h6>
                                        <ul class="mb-0">
                                            <li>Get your API key from Google AI Studio</li>
                                            <li>Keep your key secure and private</li>
                                            <li>Regularly rotate your API key</li>
                                            <li>Monitor usage for security</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= $stats['requests_today'] ?? 0 ?></h4>
                                    <p class="card-text">Requests Today</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= $stats['requests_this_month'] ?? 0 ?></h4>
                                    <p class="card-text">This Month</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= $stats['error_count'] ?? 0 ?></h4>
                                    <p class="card-text">Errors</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">Active</h4>
                                    <p class="card-text">Status</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Generation Tools -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-magic me-2"></i>
                                AI Content Generation Tools
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <form id="contentGenerationForm">
                                        <div class="mb-3">
                                            <label for="contentType" class="form-label">Content Type</label>
                                            <select class="form-select" id="contentType">
                                                <option value="property_description">Property Description</option>
                                                <option value="social_media">Social Media Post</option>
                                                <option value="customer_support">Customer Support Response</option>
                                                <option value="market_analysis">Market Analysis</option>
                                                <option value="custom">Custom Content</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="prompt" class="form-label">Prompt / Context</label>
                                            <textarea class="form-control" id="prompt" rows="4"
                                                placeholder="Enter your prompt or context here..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-wand-magic-sparkles me-2"></i>Generate Content
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <div id="generatedContent" class="border rounded p-3" style="min-height: 200px; background-color: #f8f9fa;">
                                        <p class="text-muted text-center">Generated content will appear here...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent API Logs -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-history me-2"></i>
                                Recent API Activity
                            </h5>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearLogs()">
                                    <i class="fas fa-trash me-1"></i>Clear Logs
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportLogs()">
                                    <i class="fas fa-download me-1"></i>Export
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Endpoint</th>
                                            <th>Status</th>
                                            <th>Response Time</th>
                                            <th>User</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recentLogs)): ?>
                                            <?php foreach ($recentLogs as $log): ?>
                                                <tr>
                                                    <td><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></td>
                                                    <td><?= substr($log['endpoint'], 0, 50) ?>...</td>
                                                    <td>
                                                        <?php if ($log['status_code'] == 200): ?>
                                                            <span class="badge bg-success">Success</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Error (<?= $log['status_code'] ?>)</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $log['response_time_ms'] ?? 'N/A' ?> ms</td>
                                                    <td><?= $log['user_id'] ?? 'System' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No API activity yet</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Chat Interface -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-comments me-2"></i>
                                AI Chat Interface
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chatMessages" class="border rounded p-3 mb-3" style="height: 300px; overflow-y: auto; background-color: #f8f9fa;">
                                <div class="text-center text-muted">
                                    <i class="fas fa-robot fa-2x mb-2"></i>
                                    <p>Start a conversation with Gemini AI</p>
                                </div>
                            </div>
                            <div class="input-group">
                                <input type="text" class="form-control" id="chatInput"
                                    placeholder="Type your message here..."
                                    onkeypress="if(event.key === 'Enter') sendMessage()">
                                <button class="btn btn-primary" type="button" onclick="sendMessage()">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .btn-group .btn {
        border-radius: 0.375rem;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
    }

    .badge {
        font-size: 0.75em;
    }

    #generatedContent {
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    #chatMessages {
        border: 1px solid #dee2e6 !important;
    }

    .chat-message {
        margin-bottom: 15px;
        padding: 10px;
        border-radius: 10px;
        max-width: 80%;
    }

    .chat-message.user {
        background-color: #007bff;
        color: white;
        margin-left: auto;
        text-align: right;
    }

    .chat-message.assistant {
        background-color: #e9ecef;
        color: #212529;
        margin-right: auto;
    }

    .chat-message .timestamp {
        font-size: 0.8em;
        opacity: 0.7;
        margin-top: 5px;
    }
</style>

<script>
    // API Key Management
    document.getElementById('apiKeyForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const apiKey = document.getElementById('apiKey').value;

        if (!apiKey) {
            showAlert('Please enter an API key', 'danger');
            return;
        }

        fetch('<?= BASE_URL ?>/admin/ai-settings/update-key', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    api_key: apiKey
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('API key updated successfully!', 'success');
                    refreshStats();
                } else {
                    showAlert(data.message || 'Failed to update API key', 'danger');
                }
            })
            .catch(error => {
                showAlert('Network error: ' + error.message, 'danger');
            });
    });

    function toggleApiKeyVisibility() {
        const apiKeyInput = document.getElementById('apiKey');
        const toggleIcon = document.getElementById('apiKeyToggle');

        if (apiKeyInput.type === 'password') {
            apiKeyInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            apiKeyInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    function testCurrentKey() {
        fetch('<?= BASE_URL ?>/admin/ai-settings/test-connection', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('API key is working correctly!', 'success');
                } else {
                    showAlert('API key test failed: ' + (data.error || data.message), 'danger');
                }
            })
            .catch(error => {
                showAlert('Network error: ' + error.message, 'danger');
            });
    }

    function testConnection() {
        testCurrentKey();
    }

    // Content Generation
    document.getElementById('contentGenerationForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const contentType = document.getElementById('contentType').value;
        const prompt = document.getElementById('prompt').value;

        if (!prompt) {
            showAlert('Please enter a prompt', 'danger');
            return;
        }

        const contentDiv = document.getElementById('generatedContent');
        contentDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Generating content...</div>';

        fetch('<?= BASE_URL ?>/admin/ai-settings/generate-content', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    content_type: contentType,
                    prompt: prompt
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.candidates) {
                    const content = data.data.candidates[0].content.parts[0].text;
                    contentDiv.innerHTML = '<pre>' + content + '</pre>';
                    showAlert('Content generated successfully!', 'success');
                } else {
                    contentDiv.innerHTML = '<p class="text-danger">Failed to generate content: ' + (data.error || data.message) + '</p>';
                    showAlert('Failed to generate content', 'danger');
                }
            })
            .catch(error => {
                contentDiv.innerHTML = '<p class="text-danger">Network error: ' + error.message + '</p>';
                showAlert('Network error: ' + error.message, 'danger');
            });
    });

    // Chat Interface
    function sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();

        if (!message) return;

        const chatMessages = document.getElementById('chatMessages');

        // Add user message
        const userMessage = document.createElement('div');
        userMessage.className = 'chat-message user';
        userMessage.innerHTML = `
        <div>${message}</div>
        <div class="timestamp">${new Date().toLocaleTimeString()}</div>
    `;
        chatMessages.appendChild(userMessage);

        input.value = '';

        // Add typing indicator
        const typingIndicator = document.createElement('div');
        typingIndicator.className = 'chat-message assistant';
        typingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Thinking...';
        chatMessages.appendChild(typingIndicator);

        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Send to AI
        fetch('<?= BASE_URL ?>/admin/ai-settings/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                typingIndicator.remove();

                if (data.success && data.data && data.data.candidates) {
                    const aiResponse = data.data.candidates[0].content.parts[0].text;

                    const aiMessage = document.createElement('div');
                    aiMessage.className = 'chat-message assistant';
                    aiMessage.innerHTML = `
                <div>${aiResponse}</div>
                <div class="timestamp">${new Date().toLocaleTimeString()}</div>
            `;
                    chatMessages.appendChild(aiMessage);
                } else {
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'chat-message assistant';
                    errorMessage.innerHTML = `
                <div class="text-danger">Sorry, I encountered an error: ${data.error || data.message}</div>
                <div class="timestamp">${new Date().toLocaleTimeString()}</div>
            `;
                    chatMessages.appendChild(errorMessage);
                }

                chatMessages.scrollTop = chatMessages.scrollHeight;
            })
            .catch(error => {
                typingIndicator.remove();

                const errorMessage = document.createElement('div');
                errorMessage.className = 'chat-message assistant';
                errorMessage.innerHTML = `
            <div class="text-danger">Network error: ${error.message}</div>
            <div class="timestamp">${new Date().toLocaleTimeString()}</div>
        `;
                chatMessages.appendChild(errorMessage);

                chatMessages.scrollTop = chatMessages.scrollHeight;
            });
    }

    // Utility Functions
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
        document.body.appendChild(alertDiv);

        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    function refreshStats() {
        location.reload();
    }

    function clearLogs() {
        if (confirm('Are you sure you want to clear API logs? This action cannot be undone.')) {
            fetch('<?= BASE_URL ?>/admin/ai-settings/clear-logs', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        days: 30
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Logs cleared successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert('Failed to clear logs', 'danger');
                    }
                });
        }
    }

    function exportLogs() {
        window.open('<?= BASE_URL ?>/admin/ai-settings/export-usage-report', '_blank');
    }

    // Initialize tooltips and other Bootstrap components
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any Bootstrap components if needed
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/base.php';
echo $content;
?>