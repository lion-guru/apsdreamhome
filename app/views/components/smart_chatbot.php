<?php

/**
 * Smart AI Chatbot Widget
 * Floating chat interface with RBAC awareness
 */
if (session_status() === PHP_SESSION_NONE) session_start();

// Detect user context for personalized greeting
$isLoggedIn = false;
$userName = 'Guest';
$userRole = 'guest';

if (isset($_SESSION['associate_id'])) {
    $isLoggedIn = true;
    $userName = $_SESSION['associate_name'] ?? 'Associate';
    $userRole = 'associate';
} elseif (isset($_SESSION['agent_id'])) {
    $isLoggedIn = true;
    $userName = $_SESSION['agent_name'] ?? 'Agent';
    $userRole = 'agent';
} elseif (isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
    $userName = $_SESSION['user_name'] ?? 'Customer';
    $userRole = 'customer';
}
?>

<!-- Smart AI Chatbot Widget -->
<div id="aps-chatbot-widget" class="chatbot-widget">
    <!-- Chat Button -->
    <button id="chatbot-toggle" class="chatbot-toggle" onclick="toggleChatbot()">
        <i class="fas fa-robot"></i>
        <span class="chatbot-label">APS AI</span>
    </button>

    <!-- Chat Window -->
    <div id="chatbot-window" class="chatbot-window">
        <!-- Header -->
        <div class="chatbot-header">
            <div class="chatbot-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="chatbot-info">
                <h5>APS AI Assistant</h5>
                <span class="status"><i class="fas fa-circle"></i> Online</span>
            </div>
            <div class="chatbot-actions">
                <button onclick="clearChat()" title="Clear Chat"><i class="fas fa-trash"></i></button>
                <button onclick="toggleChatbot()" title="Close"><i class="fas fa-times"></i></button>
            </div>
        </div>

        <!-- Messages Area -->
        <div id="chatbot-messages" class="chatbot-messages">
            <!-- Welcome Message -->
            <div class="message bot-message">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <p>👋 Namaste <?php echo htmlspecialchars($userName); ?>! <br><br>
                        Main APS AI hoon, aapka smart real estate assistant! 🏠<br><br>
                        💬 Aap mujhse pooch sakte hain:<br>
                        • Property buy/sell/rent<br>
                        • Prices & locations<br>
                        • Home loan info<br>
                        • Project details<br><br>
                        Kya main aapki madad kar sakta hoon? 😊</p>
                    <span class="message-time"><?php echo date('h:i A'); ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Suggestions -->
        <div class="chatbot-suggestions">
            <button onclick="sendQuickMessage('Plot kharidna hai')">🏠 Buy Plot</button>
            <button onclick="sendQuickMessage('Property bechni hai')">💰 Sell Property</button>
            <button onclick="sendQuickMessage('Price kya hai?')">💵 Pricing</button>
            <button onclick="sendQuickMessage('Home loan chahiye')">🏦 Home Loan</button>
        </div>

        <!-- Input Area -->
        <div class="chatbot-input-area">
            <input type="text"
                id="chatbot-input"
                placeholder="Type message in Hindi or English..."
                onkeypress="handleKeyPress(event)"
                autocomplete="off">
            <button onclick="sendMessage()" class="send-btn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<style>
    /* Chatbot Widget Styles */
    .chatbot-widget {
        position: fixed;
        bottom: 20px;
        left: 20px;
        z-index: 9999;
        font-family: 'Segoe UI', system-ui, sans-serif;
    }

    .chatbot-toggle {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 15px 25px;
        font-size: 16px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chatbot-toggle:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    .chatbot-toggle i {
        font-size: 20px;
    }

    .chatbot-window {
        position: absolute;
        bottom: 70px;
        left: 0;
        width: 380px;
        height: 550px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        display: none;
        flex-direction: column;
        overflow: hidden;
        animation: slideUp 0.3s ease;
    }

    .chatbot-window.active {
        display: flex;
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .chatbot-avatar {
        width: 45px;
        height: 45px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .chatbot-info {
        flex: 1;
    }

    .chatbot-info h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }

    .chatbot-info .status {
        font-size: 12px;
        opacity: 0.9;
    }

    .chatbot-info .status i {
        color: #4ade80;
        font-size: 8px;
        margin-right: 5px;
    }

    .chatbot-actions {
        display: flex;
        gap: 8px;
    }

    .chatbot-actions button {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .chatbot-actions button:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .chatbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8fafc;
    }

    .message {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message-avatar {
        width: 35px;
        height: 35px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 14px;
        flex-shrink: 0;
    }

    .user-message .message-avatar {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .message-content {
        background: white;
        padding: 12px 16px;
        border-radius: 16px;
        max-width: 280px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .user-message {
        flex-direction: row-reverse;
    }

    .user-message .message-content {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .message-content p {
        margin: 0;
        line-height: 1.5;
        font-size: 14px;
    }

    .message-time {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 5px;
        display: block;
    }

    .user-message .message-time {
        color: rgba(255, 255, 255, 0.8);
    }

    .chatbot-suggestions {
        padding: 10px 15px;
        background: white;
        border-top: 1px solid #e2e8f0;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .chatbot-suggestions button {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
        color: #475569;
    }

    .chatbot-suggestions button:hover {
        background: #e2e8f0;
        border-color: #cbd5e1;
    }

    .chatbot-input-area {
        padding: 15px;
        background: white;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 10px;
    }

    #chatbot-input {
        flex: 1;
        border: 1px solid #e2e8f0;
        border-radius: 25px;
        padding: 12px 20px;
        font-size: 14px;
        outline: none;
        transition: all 0.2s;
    }

    #chatbot-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .send-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .send-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .typing-indicator {
        display: flex;
        gap: 5px;
        padding: 10px 15px;
        background: white;
        border-radius: 16px;
        width: fit-content;
    }

    .typing-indicator span {
        width: 8px;
        height: 8px;
        background: #667eea;
        border-radius: 50%;
        animation: typing 1.4s infinite;
    }

    .typing-indicator span:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-indicator span:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing {

        0%,
        60%,
        100% {
            transform: translateY(0);
        }

        30% {
            transform: translateY(-10px);
        }
    }

    /* Mobile Responsive */
    @media (max-width: 480px) {
        .chatbot-widget {
            bottom: 10px;
            right: 10px;
        }

        .chatbot-window {
            width: calc(100vw - 20px);
            height: 500px;
            right: -10px;
        }

        .chatbot-label {
            display: none;
        }
    }
</style>

<script>
    const CHAT_API_URL = '<?php echo BASE_URL; ?>/api/ai/chat';
    let sessionId = '<?php echo session_id(); ?>';

    function toggleChatbot() {
        const window = document.getElementById('chatbot-window');
        window.classList.toggle('active');

        if (window.classList.contains('active')) {
            document.getElementById('chatbot-input').focus();
        }
    }

    function handleKeyPress(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    }

    function sendQuickMessage(message) {
        document.getElementById('chatbot-input').value = message;
        sendMessage();
    }

    function sendMessage() {
        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();

        if (!message) return;

        // Add user message
        addMessage(message, 'user');
        input.value = '';

        // Show typing indicator
        showTyping();

        // Send to API
        fetch(CHAT_API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `message=${encodeURIComponent(message)}&session_id=${sessionId}`
            })
            .then(res => res.json())
            .then(data => {
                hideTyping();
                if (data.success) {
                    addMessage(data.response, 'bot');
                } else {
                    addMessage('Sorry, kuch problem ho gayi. Please try again! 🙏', 'bot');
                }
            })
            .catch(err => {
                hideTyping();
                addMessage('Network error. Please check your connection! 📡', 'bot');
                console.error(err);
            });
    }

    function addMessage(text, sender) {
        const messagesDiv = document.getElementById('chatbot-messages');
        const time = new Date().toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });

        const messageHTML = `
        <div class="message ${sender}-message">
            <div class="message-avatar">
                <i class="fas ${sender === 'bot' ? 'fa-robot' : 'fa-user'}"></i>
            </div>
            <div class="message-content">
                <p>${formatMessage(text)}</p>
                <span class="message-time">${time}</span>
            </div>
        </div>
    `;

        messagesDiv.insertAdjacentHTML('beforeend', messageHTML);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function formatMessage(text) {
        // Convert URLs to links
        text = text.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" style="color: inherit; text-decoration: underline;">$1</a>');
        // Convert newlines to breaks
        text = text.replace(/\n/g, '<br>');
        return text;
    }

    function showTyping() {
        const messagesDiv = document.getElementById('chatbot-messages');
        const typingHTML = `
        <div id="typing-indicator" class="message bot-message">
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    `;
        messagesDiv.insertAdjacentHTML('beforeend', typingHTML);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function hideTyping() {
        const typing = document.getElementById('typing-indicator');
        if (typing) typing.remove();
    }

    function clearChat() {
        const messagesDiv = document.getElementById('chatbot-messages');
        messagesDiv.innerHTML = `
        <div class="message bot-message">
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <p>👋 Chat cleared! Kya main aapki madad kar sakta hoon?</p>
                <span class="message-time">${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</span>
            </div>
        </div>
    `;
    }

    // Auto-open on first visit (optional)
    // setTimeout(() => toggleChatbot(), 3000);
</script>