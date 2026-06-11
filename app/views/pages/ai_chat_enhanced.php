<?php
$page_title = $page_title ?? 'AI Assistant - APS Dream Home';
$page_description = $page_description ?? 'Enhanced AI Chat Assistant';
$current_user_role = $current_user_role ?? 'customer';
$user_name = $user_name ?? 'Guest';
$available_roles = $available_roles ?? [];
$api_configured = $api_configured ?? false;
$base = $base ?? BASE_URL;
?>

<section class="py-5 bg-gradient-primary text-white position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #667eea 100%);"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3"><i class="fas fa-robot me-3"></i>Enhanced AI Assistant</h1>
                <p class="lead mb-0">Role-based intelligent assistant with lead management capabilities</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <span class="badge bg-light text-dark fs-6 px-3 py-2">
                    <i class="fas fa-user-shield me-1"></i> Role: <?= htmlspecialchars($current_user_role) ?>
                </span>
            </div>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body aps-cp-card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle bg-primary text-white me-3">
                                <i class="fas fa-user fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($user_name) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars(ucfirst($current_user_role)) ?></small>
                            </div>
                        </div>
                        <?php if (!empty($available_roles)): ?>
                        <label class="form-label small fw-bold">Switch Role</label>
                        <select id="ai-role-select-enhanced" class="form-select form-select-sm mb-3" onchange="changeEnhancedRole(this.value)">
                            <?php foreach ($available_roles as $role_key => $role_name): ?>
                            <option value="<?= htmlspecialchars($role_key) ?>" <?= $role_key === $current_user_role ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role_name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                        <hr>
                        <div class="small">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">API Status</span>
                                <span class="<?= $api_configured ? 'text-success' : 'text-warning' ?>">
                                    <i class="fas <?= $api_configured ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-1"></i>
                                    <?= $api_configured ? 'Connected' : 'Not Configured' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-history me-2"></i>Quick Actions</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <button class="list-group-item list-group-item-action" onclick="sendEnhancedPrompt('Show me today\'s leads')">
                            <i class="fas fa-users me-2 text-primary"></i> Today's Leads
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="sendEnhancedPrompt('Generate a sales report')">
                            <i class="fas fa-chart-bar me-2 text-success"></i> Sales Report
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="sendEnhancedPrompt('What properties are trending?')">
                            <i class="fas fa-fire me-2 text-danger"></i> Trending Properties
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="sendEnhancedPrompt('Help me draft a message')">
                            <i class="fas fa-pen me-2 text-info"></i> Draft Message
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0"><i class="fas fa-comments me-2 text-primary"></i>AI Chat</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" onclick="clearEnhancedChat()" title="Clear Chat">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="enhanced-chat-messages" class="chat-messages p-4" style="height: 450px; overflow-y: auto; background: #f8f9fa;">
                            <div class="text-center py-5" id="enhanced-welcome">
                                <div class="mb-3">
                                    <span class="display-1">🤖</span>
                                </div>
                                <h5>Welcome, <?= htmlspecialchars($user_name) ?>!</h5>
                                <p class="text-muted mb-3">I'm your enhanced AI assistant. Ask me anything about properties, leads, or reports.</p>
                                <div class="row justify-content-center g-2">
                                    <div class="col-auto">
                                        <span class="badge bg-primary bg-opacity-10 text-primary p-2" onclick="sendEnhancedPrompt('Show my properties')" style="cursor:pointer">
                                            <i class="fas fa-building me-1"></i> My Properties
                                        </span>
                                    </div>
                                    <div class="col-auto">
                                        <span class="badge bg-success bg-opacity-10 text-success p-2" onclick="sendEnhancedPrompt('Lead statistics')" style="cursor:pointer">
                                            <i class="fas fa-chart-line me-1"></i> Lead Stats
                                        </span>
                                    </div>
                                    <div class="col-auto">
                                        <span class="badge bg-info bg-opacity-10 text-info p-2" onclick="sendEnhancedPrompt('Team performance')" style="cursor:pointer">
                                            <i class="fas fa-users me-1"></i> Team Performance
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="input-group">
                            <input type="text" id="enhanced-chat-input" class="form-control" placeholder="Type your message..." onkeypress="handleEnhancedKeyPress(event)">
                            <button class="btn btn-primary" onclick="sendEnhancedMessage()">
                                <i class="fas fa-paper-plane me-1"></i> Send
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
let enhancedRole = '<?= htmlspecialchars($current_user_role) ?>';

function handleEnhancedKeyPress(e) {
    if (e.key === 'Enter') sendEnhancedMessage();
}

function changeEnhancedRole(role) {
    enhancedRole = role;
    addEnhancedMessage('system', 'Role switched to: ' + role);
}

async function sendEnhancedMessage() {
    const input = document.getElementById('enhanced-chat-input');
    const message = input.value.trim();
    if (!message) return;
    input.value = '';
    sendEnhancedPrompt(message);
}

async function sendEnhancedPrompt(message) {
    document.getElementById('enhanced-welcome')?.remove();
    addEnhancedMessage('user', message);
    addEnhancedTyping();
    try {
        const res = await fetch('<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/api/ai-chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message, role: enhancedRole, context: 'enhanced_chat' })
        });
        const data = await res.json();
        removeEnhancedTyping();
        addEnhancedMessage('assistant', data.reply ?? 'No response received.');
        if (data.leadData) showEnhancedLeadNotification(data.leadData);
    } catch (e) {
        removeEnhancedTyping();
        addEnhancedMessage('assistant', 'Connection error. Please try again.');
    }
}

function addEnhancedMessage(type, text) {
    const container = document.getElementById('enhanced-chat-messages');
    const div = document.createElement('div');
    const isUser = type === 'user';
    div.className = 'd-flex mb-3 ' + (isUser ? 'justify-content-end' : 'justify-content-start');
    div.innerHTML = `
        <div class="${isUser ? 'bg-primary text-white' : 'bg-white border'} rounded-3 p-3 shadow-sm" style="max-width: 80%;">
            <div class="small">${type === 'system' ? '<em>' : ''}${text}${type === 'system' ? '</em>' : ''}</div>
            <small class="${isUser ? 'text-white-50' : 'text-muted'} d-block mt-1">${new Date().toLocaleTimeString()}</small>
        </div>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function addEnhancedTyping() {
    const container = document.getElementById('enhanced-chat-messages');
    const div = document.createElement('div');
    div.id = 'enhanced-typing';
    div.className = 'd-flex mb-3';
    div.innerHTML = `<div class="bg-white border rounded-3 p-3 shadow-sm"><div class="typing-indicator"><span></span><span></span><span></span></div></div>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function removeEnhancedTyping() {
    document.getElementById('enhanced-typing')?.remove();
}

function clearEnhancedChat() {
    document.getElementById('enhanced-chat-messages').innerHTML = `
        <div class="text-center py-5">
            <div class="mb-3"><span class="display-1">🤖</span></div>
            <h5>Chat cleared</h5>
            <p class="text-muted">Start a new conversation</p>
        </div>`;
}

function showEnhancedLeadNotification(data) {
    const toast = document.createElement('div');
    toast.className = 'position-fixed top-0 end-0 m-3 p-3 bg-success text-white rounded-3 shadow-lg';
    toast.style.zIndex = '9999';
    toast.innerHTML = `<strong>Lead Captured!</strong><br>${data.name ? 'Name: ' + data.name + '<br>' : ''}${data.phone ? 'Phone: ' + data.phone : ''}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}
</script>

<style>
.chat-messages {
    scroll-behavior: smooth;
}
.typing-indicator { display: flex; gap: 4px; padding: 4px 0; }
.typing-indicator span {
    width: 8px; height: 8px; border-radius: 50%; background: #667eea;
    animation: typingBounce 1.4s infinite ease-in-out;
}
.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typingBounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-8px); }
}
.avatar-circle {
    width: 45px; height: 45px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
</style>
