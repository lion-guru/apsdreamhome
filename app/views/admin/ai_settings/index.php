<?php
include APP_PATH . '/views/admin/layouts/header.php';

// Variables passed from controller via admin layout
$page_title = $page_title ?? 'AI Settings';
$stats = $stats ?? ['requests_today' => 0, 'requests_this_month' => 0, 'error_count' => 0];
$recentLogs = $recentLogs ?? [];
$baseUrl = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">AI Settings</h1>
        <p class="text-muted mb-0">Manage Gemini AI integration</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-robot"></i></div>
            <div class="stat-content">
                <div class="stat-label">API Status</div>
                <div class="stat-value text-success">Active</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-chart-line"></i></div>
            <div class="stat-content">
                <div class="stat-label">Requests Today</div>
                <div class="stat-value"><?= $stats['requests_today'] ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-calendar"></i></div>
            <div class="stat-content">
                <div class="stat-label">This Month</div>
                <div class="stat-value"><?= $stats['requests_this_month'] ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon danger"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-content">
                <div class="stat-label">Errors</div>
                <div class="stat-value"><?= $stats['error_count'] ?? 0 ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-3"><i class="fas fa-key me-2"></i>Gemini API Key</h5>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>Configure your Gemini API key in settings to enable AI features.
        </div>
        <p class="text-muted">Get your API key from <a href="https://aistudio.google.com/apikey" target="_blank">Google AI Studio</a></p>
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
include APP_PATH . '/views/admin/layouts/footer.php';
?>