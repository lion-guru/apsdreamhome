<?php
/**
 * APS Dream Home - AI Chat Widget
 * Floating chat button and popup for any page
 */

// Check if AI is configured
$api_configured = !empty($this->config['api_key']) ?? false;
$user_role = $this->getUserRole() ?? 'customer';
?>

<!-- AI Chat Widget -->
<div id="ai-chat-widget" class="ai-chat-widget">
    <!-- Floating Chat Button -->
    <button id="ai-chat-button" class="ai-chat-button" onclick="toggleAIChat()">
        <i class="fas fa-robot"></i>
        <span class="ai-chat-tooltip">AI Assistant</span>
    </button>
    
    <!-- Chat Popup -->
    <div id="ai-chat-popup" class="ai-chat-popup" style="display: none;">
        <!-- Chat Header -->
        <div class="ai-chat-header">
            <div class="ai-chat-info">
                <div class="ai-chat-avatar">🤖</div>
                <div class="ai-chat-details">
                    <h4>APS AI Assistant</h4>
                    <p id="ai-status-text">Ready to help</p>
                </div>
            </div>
            <div class="ai-chat-actions">
                <button onclick="minimizeAIChat()" class="ai-action-btn">
                    <i class="fas fa-minus"></i>
                </button>
                <button onclick="closeAIChat()" class="ai-action-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <!-- Chat Messages -->
        <div id="ai-chat-messages" class="ai-chat-messages">
            <div class="ai-message ai-welcome">
                <div class="ai-message-content">
                    <strong>🙏 Namaste!</strong><br>
                    Main APS Dream Home ki AI hoon. Aapki kya madad kar sakta hoon?<br><br>
                    <strong>Available help:</strong><br>
                    • Property information<br>
                    • Sales guidance<br>
                    • Technical support<br>
                    • General assistance<br><br>
                    <small>Type your message below...</small>
                </div>
            </div>
        </div>
        
        <!-- Chat Input -->
        <div class="ai-chat-input">
            <div class="ai-input-wrapper">
                <input type="text" id="ai-chat-input-field" placeholder="Type your message..." 
                       onkeypress="handleAIChatKeyPress(event)">
                <button onclick="sendAIMessage()" class="ai-send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
        
        <!-- Role Selector (if multiple roles available) -->
        <?php if (isset($available_roles) && count($available_roles) > 1): ?>
        <div class="ai-role-selector">
            <select id="ai-role-select" onchange="changeAIRole()">
                <?php foreach ($available_roles as $role_key => $role_name): ?>
                    <option value="<?php echo $role_key; ?>" 
                            <?php echo $role_key === $user_role ? 'selected' : ''; ?>>
                        <?php echo $role_name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- AI Chat Styles -->
<style>
.ai-chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.ai-chat-button {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-chat-button:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.ai-chat-tooltip {
    position: absolute;
    right: 70px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

.ai-chat-button:hover .ai-chat-tooltip {
    opacity: 1;
}

.ai-chat-popup {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 350px;
    height: 500px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #e0e0e0;
}

.ai-chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ai-chat-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ai-chat-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.ai-chat-details h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
}

.ai-chat-details p {
    margin: 0;
    font-size: 12px;
    opacity: 0.9;
}

.ai-chat-actions {
    display: flex;
    gap: 8px;
}

.ai-action-btn {
    width: 25px;
    height: 25px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: background 0.3s ease;
}

.ai-action-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.ai-chat-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    background: #f8f9fa;
}

.ai-message {
    margin-bottom: 15px;
    animation: fadeIn 0.3s ease;
}

.ai-message.ai-welcome {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px;
    border-radius: 8px;
    font-size: 13px;
    line-height: 1.4;
}

.ai-message.ai-user {
    background: #e3f2fd;
    color: #1976d2;
    padding: 10px;
    border-radius: 8px;
    margin-left: 20px;
    font-size: 13px;
}

.ai-message.ai-response {
    background: white;
    border: 1px solid #e0e0e0;
    padding: 10px;
    border-radius: 8px;
    margin-right: 20px;
    font-size: 13px;
    line-height: 1.4;
}

.ai-message-content {
    margin-bottom: 5px;
}

.ai-message-time {
    font-size: 10px;
    opacity: 0.7;
}

.ai-chat-input {
    padding: 15px;
    background: white;
    border-top: 1px solid #e0e0e0;
}

.ai-input-wrapper {
    display: flex;
    gap: 8px;
}

.ai-chat-input input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 20px;
    outline: none;
    font-size: 13px;
}

.ai-chat-input input:focus {
    border-color: #667eea;
}

.ai-send-btn {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
}

.ai-send-btn:hover {
    transform: scale(1.1);
}

.ai-role-selector {
    padding: 10px 15px;
    background: #f8f9fa;
    border-top: 1px solid #e0e0e0;
}

.ai-role-selector select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: white;
    font-size: 12px;
    outline: none;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .ai-chat-widget {
        bottom: 15px;
        right: 15px;
    }
    
    .ai-chat-button {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .ai-chat-popup {
        width: 300px;
        height: 450px;
        bottom: 70px;
    }
    
    .ai-chat-tooltip {
        display: none;
    }
}

/* Minimized State */
.ai-chat-popup.minimized {
    height: 60px;
}

.ai-chat-popup.minimized .ai-chat-messages,
.ai-chat-popup.minimized .ai-chat-input,
.ai-chat-popup.minimized .ai-role-selector {
    display: none;
}
</style>

<!-- AI Chat JavaScript -->
<script>
let aiChatOpen = false;
let currentAIRole = '<?php echo $user_role; ?>';

function toggleAIChat() {
    const popup = document.getElementById('ai-chat-popup');
    const button = document.getElementById('ai-chat-button');
    
    if (aiChatOpen) {
        popup.style.display = 'none';
        button.innerHTML = '<i class="fas fa-robot"></i><span class="ai-chat-tooltip">AI Assistant</span>';
    } else {
        popup.style.display = 'block';
        button.innerHTML = '<i class="fas fa-times"></i><span class="ai-chat-tooltip">Close Chat</span>';
        // Focus input field
        setTimeout(() => {
            document.getElementById('ai-chat-input-field').focus();
        }, 300);
    }
    
    aiChatOpen = !aiChatOpen;
}

function closeAIChat() {
    const popup = document.getElementById('ai-chat-popup');
    const button = document.getElementById('ai-chat-button');
    
    popup.style.display = 'none';
    button.innerHTML = '<i class="fas fa-robot"></i><span class="ai-chat-tooltip">AI Assistant</span>';
    aiChatOpen = false;
}

function minimizeAIChat() {
    const popup = document.getElementById('ai-chat-popup');
    popup.classList.toggle('minimized');
}

function handleAIChatKeyPress(event) {
    if (event.key === 'Enter') {
        sendAIMessage();
    }
}

async function sendAIMessage() {
    const inputField = document.getElementById('ai-chat-input-field');
    const message = inputField.value.trim();
    
    if (!message) return;
    
    // Add user message
    addAIChatMessage(message, 'user');
    
    // Clear input
    inputField.value = '';
    
    // Add typing indicator
    addAITypingIndicator();
    
    try {
        const response = await fetch('/api/ai-chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: message,
                role: currentAIRole,
                context: 'Widget chat from ' + window.location.pathname
            })
        });
        
        const data = await response.json();
        
        // Remove typing indicator
        removeAITypingIndicator();
        
        if (data.reply) {
            addAIChatMessage(data.reply, 'response');
            
            // Show lead capture if detected
            if (data.leadData) {
                showLeadCaptureNotification(data.leadData);
            }
        } else {
            addAIChatMessage('Sorry, I encountered an error. Please try again.', 'response');
        }
    } catch (error) {
        removeAITypingIndicator();
        addAIChatMessage('Connection error. Please check your internet connection.', 'response');
    }
}

function addAIChatMessage(message, type) {
    const messagesContainer = document.getElementById('ai-chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `ai-message ai-${type}`;
    
    const time = new Date().toLocaleTimeString('hi-IN', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
    
    if (type === 'user') {
        messageDiv.innerHTML = `
            <div class="ai-message-content">${escapeHtml(message)}</div>
            <div class="ai-message-time">${time}</div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="ai-message-content">${message}</div>
            <div class="ai-message-time">${time}</div>
        `;
    }
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function addAITypingIndicator() {
    const messagesContainer = document.getElementById('ai-chat-messages');
    const typingDiv = document.createElement('div');
    typingDiv.className = 'ai-message ai-response';
    typingDiv.id = 'ai-typing-indicator';
    typingDiv.innerHTML = `
        <div class="ai-message-content">
            <div class="ai-typing">
                <span class="ai-typing-dot"></span>
                <span class="ai-typing-dot"></span>
                <span class="ai-typing-dot"></span>
            </div>
        </div>
    `;
    
    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function removeAITypingIndicator() {
    const typingIndicator = document.getElementById('ai-typing-indicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

function changeAIRole() {
    const select = document.getElementById('ai-role-select');
    currentAIRole = select.value;
    
    addAIChatMessage(`Role changed to: ${select.options[select.selectedIndex].text}`, 'response');
}

function showLeadCaptureNotification(leadData) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4caf50;
        color: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 10000;
        max-width: 300px;
    `;
    notification.innerHTML = `
        <strong>🎯 Lead Captured!</strong><br>
        ${leadData.name ? 'Name: ' + leadData.name + '<br>' : ''}
        ${leadData.phone ? 'Phone: ' + leadData.phone + '<br>' : ''}
        <small>Saved to database</small>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Add typing indicator styles
const style = document.createElement('style');
style.textContent = `
.ai-typing {
    display: flex;
    gap: 4px;
    padding: 5px 0;
}

.ai-typing-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #667eea;
    animation: aiTyping 1.4s infinite;
}

.ai-typing-dot:nth-child(2) {
    animation-delay: 0.2s;
}

.ai-typing-dot:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes aiTyping {
    0%, 60%, 100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-8px);
    }
}
`;
document.head.appendChild(style);
</script>
