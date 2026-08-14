<?php
/**
 * AI Chatbot Widget
 * Floating chat widget with Gemini AI integration
 */
?>

<!-- Chatbot Widget -->
<div id="chatbot-widget" class="chatbot-widget">
    <!-- Chat Button -->
    <button id="chatbot-toggle" class="chatbot-toggle" onclick="toggleChatbot()">
        <i class="fas fa-robot"></i>
        <span class="chatbot-label">__('component_ask_ai', 'Ask AI')</span>
    </button>
    
    <!-- Chat Window -->
    <div id="chatbot-window" class="chatbot-window" class="style-54390">
        <!-- Header -->
        <div class="chatbot-header">
            <div class="chatbot-title">
                <i class="fas fa-robot"></i>
                <span>__('component_aps_ai_assistant', 'APS AI Assistant')</span>
            </div>
            <div class="chatbot-actions">
                <button onclick="clearChat()" title="htmlspecialchars(__('component_clear_chat_lower', 'Clear chat'))">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button onclick="toggleChatbot()" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <!-- Messages Area -->
        <div id="chatbot-messages" class="chatbot-messages">
            <div class="chatbot-message bot">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <p>Namaste! ðŸ™� I'm your APS Dream Home AI assistant.</p>
                    <p>I can help you with:</p>
                    <ul>
                        <li>ðŸ�  Buying/Selling properties</li>
                        <li>ðŸ”‘ Rental properties</li>
                        <li>ðŸ’° Home loans</li>
                        <li>ðŸ¤� Joining as associate</li>
                    </ul>
                    <p>How can I help you today?</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Suggestions -->
        <div id="chatbot-suggestions" class="chatbot-suggestions">
            <button onclick="sendQuickMessage('I want to buy a property')">Buy Property</button>
            <button onclick="sendQuickMessage('Show me rental properties')">Rent</button>
            <button onclick="sendQuickMessage('Home loan information')">Loan</button>
            <button onclick="sendQuickMessage('Join as associate')">Join</button>
        </div>
        
        <!-- Input Area -->
        <div class="chatbot-input-area">
            <input type="text" id="chatbot-input" 
                   placeholder="Type your message in Hindi or English..."
                   onkeypress="handleChatKeypress(event)">
            <button onclick="sendMessage()" class="send-btn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
        
        <!-- Language Toggle -->
        <div class="chatbot-footer">
            <span class="powered-by">__('component_powered_by_gemini', 'Powered by Gemini AI')</span>
            <button onclick="toggleLanguage()" class="lang-btn" id="lang-toggle">
                ðŸ‡¬ðŸ‡§ English
            </button>
        </div>
    </div>
</div>

<style>
/* Chatbot Widget Styles */
.chatbot-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.chatbot-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6B4EE6 0%, #8B5CF6 100%);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(107, 78, 230, 0.4);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.chatbot-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(107, 78, 230, 0.5);
}

.chatbot-label {
    font-size: 10px;
    margin-top: 2px;
}

.chatbot-window {
    position: absolute;
    bottom: 75px;
    right: 0;
    width: 380px;
    height: 500px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chatbot-header {
    background: linear-gradient(135deg, #6B4EE6 0%, #8B5CF6 100%);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chatbot-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
}

.chatbot-title i {
    font-size: 20px;
}

.chatbot-actions button {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    cursor: pointer;
    margin-left: 5px;
    transition: background 0.2s;
}

.chatbot-actions button:hover {
    background: rgba(255,255,255,0.3);
}

.chatbot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
}

.chatbot-message {
    display: flex;
    gap: 12px;
    margin-bottom: 15px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.chatbot-message.user {
    flex-direction: row-reverse;
}

.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6B4EE6 0%, #8B5CF6 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.chatbot-message.user .message-avatar {
    background: #00C9A7;
}

.message-content {
    background: white;
    padding: 12px 16px;
    border-radius: 16px;
    max-width: 75%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.chatbot-message.user .message-content {
    background: #6B4EE6;
    color: white;
}

.message-content p {
    margin: 0 0 8px 0;
    line-height: 1.5;
}

.message-content p:last-child {
    margin-bottom: 0;
}

.message-content ul {
    margin: 8px 0;
    padding-left: 20px;
}

.message-content li {
    margin: 4px 0;
}

.chatbot-suggestions {
    padding: 10px 15px;
    background: white;
    border-top: 1px solid #e9ecef;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.chatbot-suggestions button {
    background: #f0f0f0;
    border: none;
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    color: #6B4EE6;
}

.chatbot-suggestions button:hover {
    background: #6B4EE6;
    color: white;
}

.chatbot-input-area {
    padding: 15px;
    background: white;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 10px;
}

.chatbot-input-area input {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 24px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}

.chatbot-input-area input:focus {
    border-color: #6B4EE6;
}

.send-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6B4EE6 0%, #8B5CF6 100%);
    border: none;
    color: white;
    cursor: pointer;
    transition: transform 0.2s;
}

.send-btn:hover {
    transform: scale(1.05);
}

.chatbot-footer {
    padding: 10px 15px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
}

.powered-by {
    color: #6c757d;
}

.lang-btn {
    background: white;
    border: 1px solid #dee2e6;
    padding: 4px 10px;
    border-radius: 12px;
    cursor: pointer;
    font-size: 11px;
    transition: all 0.2s;
}

.lang-btn:hover {
    background: #6B4EE6;
    color: white;
    border-color: #6B4EE6;
}

.typing-indicator {
    display: flex;
    gap: 4px;
    padding: 12px 16px;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background: #6B4EE6;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .chatbot-window {
        width: calc(100vw - 40px);
        right: -10px;
    }
}
</style>

<script>
let currentLanguage = 'en';
let chatHistory = [];

function toggleChatbot() {
    const window = document.getElementById('chatbot-window');
    if (window.style.display === 'none') {
        window.style.display = 'flex';
        document.getElementById('chatbot-input').focus();
    } else {
        window.style.display = 'none';
    }
}

function toggleLanguage() {
    currentLanguage = currentLanguage === 'en' ? 'hi' : 'en';
    const btn = document.getElementById('lang-toggle');
    btn.textContent = currentLanguage === 'en' ? 'ðŸ‡¬ðŸ‡§ English' : 'ðŸ‡®ðŸ‡³ à¤¹à¤¿à¤‚à¤¦à¥€';
}

function handleChatKeypress(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

function sendQuickMessage(message) {
    document.getElementById('chatbot-input').value = message;
    sendMessage();
}

async function sendMessage() {
    const input = document.getElementById('chatbot-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Add user message
    addMessage(message, 'user');
    input.value = '';
    
    // Show typing indicator
    showTyping();
    
    try {
        // Call API
        const response = await fetch('<?= BASE_URL ?>/api/chatbot/message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: message,
                user_id: 0,
                context: {}
            })
        });
        
        const data = await response.json();
        
        // Remove typing indicator and add bot response
        hideTyping();
        
        if (data.success) {
            addMessage(data.response, 'bot');
            
            // Handle actions
            if (data.actions && data.actions.length > 0) {
                addActionButtons(data.actions);
            }
        } else {
            addMessage('Sorry, I encountered an error. Please try again.', 'bot');
        }
        
    } catch (error) {
        hideTyping();
        addMessage('Sorry, I\'m having trouble connecting. Please try again later.', 'bot');
    }
}

function addMessage(text, sender) {
    const messages = document.getElementById('chatbot-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chatbot-message ${sender}`;
    
    const avatar = sender === 'bot' 
        ? '<i class="fas fa-robot"></i>' 
        : '<i class="fas fa-user"></i>';
    
    messageDiv.innerHTML = `
        <div class="message-avatar">${avatar}</div>
        <div class="message-content"><p>${escapeHtml(text)}</p></div>
    `;
    
    messages.appendChild(messageDiv);
    messages.scrollTop = messages.scrollHeight;
}

function addActionButtons(actions) {
    const messages = document.getElementById('chatbot-messages');
    const buttonsDiv = document.createElement('div');
    buttonsDiv.className = 'chatbot-message bot';
    
    let buttonsHtml = '<div class="message-avatar"><i class="fas fa-robot"></i></div><div class="message-content">';
    actions.forEach(action => {
        if (action.type === 'link') {
            buttonsHtml += `<a href="${action.url}" class="btn btn-sm btn-primary mt-2">${action.label}</a> `;
        }
    });
    buttonsHtml += '</div>';
    
    buttonsDiv.innerHTML = buttonsHtml;
    messages.appendChild(buttonsDiv);
    messages.scrollTop = messages.scrollHeight;
}

function showTyping() {
    const messages = document.getElementById('chatbot-messages');
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typing-indicator';
    typingDiv.className = 'chatbot-message bot';
    typingDiv.innerHTML = `
        <div class="message-avatar"><i class="fas fa-robot"></i></div>
        <div class="message-content">
            <div class="typing-indicator">
                <span></span><span></span><span></span>
            </div>
        </div>
    `;
    messages.appendChild(typingDiv);
    messages.scrollTop = messages.scrollHeight;
}

function hideTyping() {
    const typing = document.getElementById('typing-indicator');
    if (typing) typing.remove();
}

function clearChat() {
    const messages = document.getElementById('chatbot-messages');
    messages.innerHTML = `
        <div class="chatbot-message bot">
            <div class="message-avatar"><i class="fas fa-robot"></i></div>
            <div class="message-content">
                <p>Chat cleared! ðŸ™� How can I help you today?</p>
            </div>
        </div>
    `;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Auto-open chatbot after 5 seconds (first visit)
setTimeout(() => {
    if (!localStorage.getItem('chatbotOpened')) {
        toggleChatbot();
        localStorage.setItem('chatbotOpened', 'true');
    }
}, 5000);
</script>
