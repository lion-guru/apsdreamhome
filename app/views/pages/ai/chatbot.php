<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-gradient text-white p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-robot fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="mb-0">Property Assistant</h2>
                            <p class="mb-0 text-white-50 mt-1">Ask me anything about properties!</p>
                        </div>
                    </div>
                </div>
                
                <div class="chat-container" style="height: 450px; display: flex; flex-direction: column; background: #f8f9fa;">
                    <div class="chat-messages p-4" id="chatMessages" style="flex: 1; overflow-y: auto;">
                        <div class="message bot-message mb-3 p-3 rounded-4 bg-white shadow-sm" style="max-width: 85%; margin-right: auto; border-left: 4px solid #667eea;">
                            Namaste! 🙏 Welcome to <strong>APS Dream Home</strong>! I'm your property assistant.\n\nHow can I help you today? Try these quick options below! 👇
                        </div>
                    </div>
                    
                    <div class="quick-replies p-3 bg-white border-top" id="quickReplies">
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-outline-primary rounded-pill" onclick="quickReply('View Properties')">
                                <i class="fas fa-building me-1"></i> View Properties
                            </button>
                            <button class="btn btn-sm btn-outline-success rounded-pill" onclick="quickReply('Price Details')">
                                <i class="fas fa-tag me-1"></i> Price Details
                            </button>
                            <button class="btn btn-sm btn-outline-info rounded-pill" onclick="quickReply('Book Site Visit')">
                                <i class="fas fa-calendar-check me-1"></i> Book Visit
                            </button>
                            <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="quickReply('Contact Us')">
                                <i class="fas fa-phone me-1"></i> Contact
                            </button>
                        </div>
                    </div>
                    
                    <div class="loading text-center py-3 text-muted small" id="loading" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Typing<span class="dots"></span>
                    </div>
                    
                    <div class="chat-input p-3 bg-white border-top">
                        <div class="input-group">
                            <input type="text" id="userInput" class="form-control rounded-start-pill border-end-0 py-2" placeholder="Type your message..." />
                            <button class="btn btn-primary rounded-end-pill px-4" onclick="sendMessage()">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3 text-muted small">
                <i class="fas fa-info-circle me-1"></i>
                For immediate assistance, call: <strong>+91 92771 21112</strong>
            </div>
        </div>
    </div>
</div>

<style>
    .user-message {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
        margin-left: auto !important;
        border-radius: 18px 18px 4px 18px !important;
    }
    .bot-message {
        background: #ffffff !important;
        color: #333 !important;
        margin-right: auto !important;
        border-radius: 18px 18px 18px 4px !important;
        border-left: 4px solid #667eea;
    }
    .message {
        animation: fadeIn 0.3s ease;
        line-height: 1.6;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .quick-replies .btn {
        transition: all 0.2s ease;
    }
    .quick-replies .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .dots::after {
        content: '';
        animation: dots 1.5s infinite;
    }
    @keyframes dots {
        0%, 20% { content: ''; }
        40% { content: '.'; }
        60% { content: '..'; }
        80%, 100% { content: '...'; }
    }
</style>

<script>
let quickRepliesShown = true;

async function sendMessage() {
    const userInput = document.getElementById('userInput');
    const message = userInput.value.trim();
    if (!message) return;

    addMessage(message, true);
    userInput.value = '';
    hideQuickReplies();

    document.getElementById('loading').style.display = 'block';

    try {
        const response = await fetch('<?= BASE_URL ?>api/ai/chatbot', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message })
        });

        const data = await response.json();
        if (data.success) {
            addMessage(data.reply, false);
            if (data.quick_replies && data.quick_replies.length > 0) {
                showQuickReplies(data.quick_replies);
            }
        } else {
            addMessage('Sorry, something went wrong. Please try again or call +91 92771 21112.', false);
        }
    } catch (error) {
        addMessage('Connection error. Please check your internet or call us at +91 92771 21112.', false);
    } finally {
        document.getElementById('loading').style.display = 'none';
    }
}

function quickReply(message) {
    document.getElementById('userInput').value = message;
    sendMessage();
}

function addMessage(text, isUser) {
    const messagesDiv = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'} mb-3 p-3 rounded-4 shadow-sm`;
    messageDiv.style.maxWidth = '85%';
    messageDiv.innerHTML = text.replace(/\n/g, '<br>');
    messagesDiv.appendChild(messageDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function showQuickReplies(replies) {
    const container = document.getElementById('quickReplies');
    container.innerHTML = '<div class="d-flex flex-wrap gap-2">' + 
        replies.map(r => `<button class="btn btn-sm btn-outline-primary rounded-pill" onclick="quickReply('${r}')">${r}</button>`)
            .join('') + 
        '</div>';
    container.style.display = 'block';
}

function hideQuickReplies() {
    document.getElementById('quickReplies').style.display = 'none';
}

document.getElementById('userInput').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') sendMessage();
});
</script>
