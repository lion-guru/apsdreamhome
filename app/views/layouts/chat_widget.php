<?php if (!isset($sc)) {
    $sc = function ($k, $d = '') {
        return $GLOBALS['_site_settings_cache'][$k] ?? $d;
    };
} ?>
<!-- AI Chat Bot Widget -->
<style>
/* Chat Widget */
.chat-widget {
    position: fixed;
    bottom: 90px;
    right: 20px;
    z-index: 9999;
    font-family: 'Segoe UI', sans-serif;
}

.chat-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.chat-toggle:hover {
    transform: scale(1.1);
}

.chat-toggle i {
    transition: transform 0.3s ease;
}

.chat-toggle.active i {
    transform: rotate(180deg);
}

.chat-box {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 380px;
    height: 500px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    display: none;
    flex-direction: column;
    overflow: hidden;
}

.chat-box.show {
    display: flex;
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

.chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chat-header-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-close {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    padding: 5px;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    background: #f8f9fa;
}

.chat-message {
    margin-bottom: 12px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

.chat-message.bot {
    text-align: left;
}

.chat-message.user {
    text-align: right;
}

.chat-message .bubble {
    display: inline-block;
    max-width: 85%;
    padding: 10px 15px;
    border-radius: 16px;
    font-size: 14px;
    line-height: 1.4;
    white-space: pre-wrap;
}

.chat-message.bot .bubble {
    background: white;
    color: #333;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chat-message.user .bubble {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.quick-replies {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.quick-reply {
    background: white;
    border: 1px solid #667eea;
    color: #667eea;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.quick-reply:hover {
    background: #667eea;
    color: white;
}

.chat-input-area {
    padding: 15px;
    background: white;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
}

.chat-input {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 24px;
    padding: 10px 15px;
    font-size: 14px;
    outline: none;
}

.chat-input:focus {
    border-color: #667eea;
}

.chat-send {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-send:hover {
    opacity: 0.9;
}

.typing-indicator {
    display: none;
    padding: 10px 15px;
}

.typing-indicator span {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #999;
    border-radius: 50%;
    margin: 0 2px;
    animation: typing 1s infinite;
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
        transform: translateY(-5px);
    }
}

@media (max-width: 480px) {
    .chat-box {
        width: calc(100vw - 40px);
        height: calc(100vh - 150px);
        right: -10px;
    }
}
</style>

<div class="chat-widget" id="chatWidget">
    <div class="chat-box" id="chatBox">
        <div class="chat-header">
            <div class="chat-header-info">
                <div class="chat-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <div style="font-weight: 600;">APS Dream Home Bot</div>
                    <small style="opacity: 0.8;">Online • Hindi/English</small>
                </div>
            </div>
            <button class="chat-close" onclick="toggleChat()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div class="chat-message bot">
                <div class="bubble">🙏 Namaste! APS Dream Home mein swagat hai!

                    How can I help you today?

                    Select an option or type your query:</div>
                <div class="quick-replies">
                    <button class="quick-reply" onclick="sendQuickReply('I want to buy a plot')">🏠 Buy
                        Property</button>
                    <button class="quick-reply" onclick="sendQuickReply('I want to sell my property')">🏷️ Sell
                        Property</button>
                    <button class="quick-reply" onclick="sendQuickReply('Need home loan help')">🏦 Home Loan</button>
                    <button class="quick-reply" onclick="sendQuickReply('Need legal help')">📋 Legal Help</button>
                    <button class="quick-reply" onclick="sendQuickReply('Contact details')">📞 Contact</button>
                    <button class="quick-reply" onclick="requestHumanAgent()"
                        style="border-color:#e11d48; color:#e11d48;">👤 Talk to Human</button>
                </div>
            </div>
        </div>
        <div class="chat-input-area">
            <input type="text" class="chat-input" id="chatInput" placeholder="Type your message..."
                onkeypress="if(event.key==='Enter')sendMessage()">
            <button class="chat-send" onclick="sendMessage()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
    <button class="chat-toggle" id="chatToggle" onclick="toggleChat()">
        <i class="fas fa-comment-dots"></i>
    </button>
</div>

<script>
let sessionId = 'web_' + Date.now();
let isChatOpen = false;

function toggleChat() {
    isChatOpen = !isChatOpen;
    const chatBox = document.getElementById('chatBox');
    const toggle = document.getElementById('chatToggle');

    if (isChatOpen) {
        chatBox.classList.add('show');
        toggle.classList.add('active');
    } else {
        chatBox.classList.remove('show');
        toggle.classList.remove('active');
    }
}

function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();

    // Fetch role from notification system context to send to AI
    let userRole = (window.NOTIFY_USER && window.NOTIFY_USER.role) ? window.NOTIFY_USER.role : 'guest';
    let userId = (window.NOTIFY_USER && window.NOTIFY_USER.id) ? window.NOTIFY_USER.id : '';

    if (!message) return;

    // Add user message
    addMessage(message, 'user');
    input.value = '';

    // Show typing indicator
    showTyping();

    // Send to server
    fetch('<?php echo BASE_URL; ?>/api/gemini/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'message=' + encodeURIComponent(message) + '&session_id=' + sessionId + '&role=' +
                encodeURIComponent(userRole) + '&user_id=' + encodeURIComponent(userId)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.response) {
                hideTyping();
                addMessage(data.response, 'bot');
            } else {
                // Trigger Seamless Local Fallback Bot
                localFallbackBot(message);
            }
        })
        .catch(err => {
            // Trigger Seamless Local Fallback Bot on Network Error
            localFallbackBot(message);
        });
}

function requestHumanAgent() {
    addMessage("I want to talk to a human agent / Request Callback", 'user');
    showTyping();

    setTimeout(() => {
        hideTyping();
        addMessage(
            "Kripya apna Naam aur Mobile Number darj karein. Hamare executive aapse turant sampark karenge. (Ya aap hume <?= htmlspecialchars($sc('contact_phone', '+91 92771 21112')) ?> par call kar sakte hain).",
            'bot');

        // Inject an interactive mini-form inside the chat
        const container = document.getElementById('chatMessages');
        const formHtml =
            '<div class="chat-message bot"><div class="bubble" style="background:#f8fafc; border:1px solid #cbd5e1; width:85%;"><input type="text" id="leadCaptureName" placeholder="Aapka Naam" class="form-control form-control-sm mb-2"><input type="tel" id="leadCapturePhone" placeholder="Mobile Number" class="form-control form-control-sm mb-2"><button onclick="submitChatLead()" class="btn btn-sm btn-success w-100">Request Callback</button></div></div>';
        container.insertAdjacentHTML('beforeend', formHtml);
        container.scrollTop = container.scrollHeight;
    }, 800);
}

function submitChatLead() {
    const nameInput = document.getElementById('leadCaptureName');
    const phoneInput = document.getElementById('leadCapturePhone');
    const name = nameInput ? nameInput.value.trim() : '';
    const phone = phoneInput ? phoneInput.value.trim() : '';

    if (!name || !phone) {
        alert("Please enter both Name and Phone number.");
        return;
    }

    // Disable inputs so user doesn't submit twice
    if (nameInput) nameInput.disabled = true;
    if (phoneInput) phoneInput.disabled = true;

    addMessage("Name: " + name + ", Phone: " + phone, 'user');
    showTyping();

    // We send this to your existing contact endpoint so it saves in DB / alerts admin
    let formData = new URLSearchParams();
    formData.append('name', name);
    formData.append('phone', phone);
    formData.append('email', 'chatbot_lead@apsdreamhome.com'); // Placeholder
    formData.append('subject', 'general');
    formData.append('message', 'Callback requested via AI Chatbot');
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');

    fetch('<?= BASE_URL ?>/contact', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData.toString()
    }).then(() => {
        hideTyping();
        addMessage("Dhanyawad " + name +
            "! Aapki details hamari Backoffice team ko bhej di gayi hain. Ham jald hi aapse sampark karenge.",
            'bot');
    }).catch(() => {
        hideTyping();
        addMessage("Details saved! We will contact you soon.", 'bot');
    });
}

function localFallbackBot(userMsg) {
    // Keeps typing indicator active slightly longer to simulate AI thinking
    setTimeout(() => {
        hideTyping();
        let msg = userMsg.toLowerCase();
        let reply =
            "Aapki requirement samajh li gayi hai. Behtar jankari ke liye kripya apna contact number share karein, ya hume <?= htmlspecialchars($sc('contact_phone', '+91 92771 21112')) ?> par call karein.";

        if (msg.includes('hi') || msg.includes('hello') || msg.includes('namaste')) {
            reply = "Namaste! 🙏 Mai APS Dream Home ki taraf se aapki kya madad kar sakta hu?";
        } else if (msg.includes('price') || msg.includes('rate') || msg.includes('kitne') || msg.includes(
                'cost') || msg.includes('kimat')) {
            reply = "Hamare premium plots ₹5.5 Lakh se shuru hote hain. Aapka budget kitna hai?";
        } else if (msg.includes('location') || msg.includes('kaha') || msg.includes('address') || msg.includes(
                'jagah')) {
            reply =
                "Hamare projects Gorakhpur, Lucknow, Kushinagar aur Varanasi mein available hain. Aapko kis city mein property chahiye?";
        } else if (msg.includes('loan') || msg.includes('emi') || msg.includes('finance')) {
            reply =
                "Haan, hum home loan aur asaan EMI ki suvidha pradan karte hain (SBI, HDFC, ICICI se). Kya aap EMI details janna chahte hain?";
        } else if (msg.includes('contact') || msg.includes('call') || msg.includes('number')) {
            reply =
                "Aap hume direct is number par call kar sakte hain: <?= htmlspecialchars($sc('contact_phone', '+91 92771 21112')) ?>. Hum aapki puri sahayata karenge.";
        } else if (msg.includes('visit') || msg.includes('dekhna') || msg.includes('site')) {
            reply =
                "Zaroor! Site visit bilkul free hai. Kripya apna number drop karein ya hume call karein visit schedule karne ke liye.";
        } else if (msg.includes('buy') || msg.includes('khareedna') || msg.includes('plot')) {
            reply =
                "Bohot badhiya! Humare paas residential aur commercial dono plots hain. Aap kis city mein dekh rahe hain?";
        }

        addMessage(reply, 'bot');
    }, 800);
}

function sendQuickReply(message) {
    document.getElementById('chatInput').value = message;
    sendMessage();
}

function addMessage(text, type) {
    const container = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'chat-message ' + type;
    div.innerHTML = '<div class="bubble">' + text.replace(/\n/g, '<br>') + '</div>';
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function showTyping() {
    const container = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.id = 'typingIndicator';
    div.className = 'chat-message bot';
    div.innerHTML =
        '<div class="bubble"><span class="typing-indicator"><span></span><span></span><span></span></span></div>';
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function hideTyping() {
    const typing = document.getElementById('typingIndicator');
    if (typing) typing.remove();
}
</script>