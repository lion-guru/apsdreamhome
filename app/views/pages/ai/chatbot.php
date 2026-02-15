<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white p-4">
                    <h2 class="mb-0"><i class="fas fa-robot me-2"></i> Property Assistant</h2>
                    <p class="mb-0 text-white-50 mt-1">आपका स्वागत है! मैं आपकी प्रॉपर्टी से जुड़े सवालों का जवाब देने में मदद कर सकता हूं।</p>
                </div>
                
                <div class="chat-container bg-light" style="height: 500px; display: flex; flex-direction: column;">
                    <div class="chat-messages p-4" id="chatMessages" style="flex: 1; overflow-y: auto;">
                        <div class="message bot-message mb-3 p-3 rounded-4 bg-white shadow-sm" style="max-width: 80%; margin-right: auto;">
                            नमस्ते! मैं APS Dream Homes का AI सहायक हूँ। मैं आपकी कैसे मदद कर सकता हूँ?
                        </div>
                    </div>
                    
                    <div class="loading text-center py-2 text-muted small" id="loading" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        जवाब दे रहा हूं...
                    </div>
                    
                    <div class="chat-input p-4 bg-white border-top">
                        <div class="input-group">
                            <input type="text" id="userInput" class="form-control rounded-pill-start border-end-0 py-3" placeholder="अपना सवाल यहाँ टाइप करें..." />
                            <button class="btn btn-primary rounded-pill-end px-4" onclick="sendMessage()">
                                <i class="fas fa-paper-plane"></i> भेजें
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .user-message {
        background: #007bff !important;
        color: white !important;
        margin-left: auto !important;
        border-bottom-right-radius: 4px !important;
    }
    .bot-message {
        background: #ffffff !important;
        color: #333 !important;
        margin-right: auto !important;
        border-bottom-left-radius: 4px !important;
    }
    .message {
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
async function sendMessage() {
    const userInput = document.getElementById('userInput');
    const message = userInput.value.trim();
    if (!message) return;

    // Add user message to chat
    addMessage(message, true);
    userInput.value = '';

    // Show loading
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
        } else {
            addMessage(`Error: ${data.error || 'Something went wrong'}`, false);
        }
    } catch (error) {
        addMessage(`Error: ${error.message}`, false);
    } finally {
        document.getElementById('loading').style.display = 'none';
    }
}

function addMessage(text, isUser) {
    const messagesDiv = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'} mb-3 p-3 rounded-4 shadow-sm`;
    messageDiv.style.maxWidth = '80%';
    messageDiv.textContent = text;
    messagesDiv.appendChild(messageDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

// Handle Enter key
document.getElementById('userInput').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        sendMessage();
    }
});
</script>
