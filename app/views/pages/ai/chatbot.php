<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-gradient text-white p-4 style-68644">
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
                
                <div class="chat-container style-54006">
                    <div class="chat-messages p-4" id="chatMessages" class="style-34411">
                        <div class="message bot-message mb-3 p-3 rounded-4 bg-white shadow-sm style-65334">
                            Namaste! ðŸ™� Welcome to <strong>APS Dream Home</strong>! I'm your property assistant.\n\nHow can I help you today? Try these quick options below! ðŸ‘‡
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
                    
                    <div class="loading text-center py-3 text-muted small" id="loading" class="style-54390">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Typing<span class="dots"></span>
                    </div>
                    
                    <div class="chat-input p-3 bg-white border-top">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <button class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center" id="voiceToggle" onclick="toggleVoiceMode()" class="style-54689" title="Voice mode ON/OFF">
                                <i class="fas fa-volume-up" id="voiceToggleIcon"></i>
                            </button>
                            <small class="text-muted" id="voiceModeLabel" class="style-68658">Voice: OFF</small>
                            <div class="ms-auto d-flex align-items-center gap-2">
                                <button class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center" id="micBtn" onclick="toggleMic()" class="style-69226" title="Speak">
                                    <i class="fas fa-microphone"></i>
                                </button>
                            </div>
                        </div>
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
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important;
        color: white !important;
        margin-left: auto !important;
        border-radius: 18px 18px 4px 18px !important;
    }
    .bot-message {
        background: #ffffff !important;
        color: #333 !important;
        margin-right: auto !important;
        border-radius: 18px 18px 18px 4px !important;
        border-left: 4px solid #0d9488;
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
        // Try Gemini-powered AI first
        let response = await fetch('<?= BASE_URL ?>api/ai/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message })
        });

        let data = await response.json();
        
        // If Gemini failed, fall back to rule-based chatbot
        if (!data.gemini) {
            response = await fetch('<?= BASE_URL ?>api/ai/chatbot', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message })
            });
            data = await response.json();
        }

        if (data.success || data.reply) {
            addMessage(data.reply || data.response, false);
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

// ============================================================
// VOICE SYSTEM — STT + TTS (Web Speech API, 100% free)
// ============================================================
let voiceMode = false;
let recognition = null;
let isListening = false;

function toggleVoiceMode() {
    voiceMode = !voiceMode;
    const icon = document.getElementById('voiceToggleIcon');
    const label = document.getElementById('voiceModeLabel');
    const micBtn = document.getElementById('micBtn');
    const toggleBtn = document.getElementById('voiceToggle');

    if (voiceMode) {
        icon.className = 'fas fa-volume-up';
        toggleBtn.style.background = '#0d9488';
        toggleBtn.style.color = '#fff';
        toggleBtn.style.borderColor = '#0d9488';
        label.textContent = 'Voice: ON';
        label.style.color = '#0d9488';
        micBtn.style.display = 'flex';
        initSpeechRecognition();
    } else {
        icon.className = 'fas fa-volume-up';
        toggleBtn.style.background = 'rgba(13,148,136,0.1)';
        toggleBtn.style.color = '#0d9488';
        toggleBtn.style.borderColor = 'rgba(13,148,136,0.3)';
        label.textContent = 'Voice: OFF';
        label.style.color = '';
        micBtn.style.display = 'none';
        if (recognition) { recognition.stop(); recognition = null; }
        window.speechSynthesis?.cancel();
    }
}

function initSpeechRecognition() {
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) {
        document.getElementById('voiceModeLabel').textContent = 'Voice: Not supported';
        document.getElementById('voiceModeLabel').style.color = '#ef4444';
        return;
    }
    recognition = new SR();
    recognition.lang = 'hi-IN';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;
    recognition.continuous = false;

    recognition.onstart = () => {
        isListening = true;
        const micBtn = document.getElementById('micBtn');
        micBtn.style.background = '#ef4444';
        micBtn.innerHTML = '<i class="fas fa-stop"></i>';
    };

    recognition.onresult = (e) => {
        const transcript = e.results[0][0].transcript;
        document.getElementById('userInput').value = transcript;
        sendMessage();
    };

    recognition.onerror = (e) => {
        console.error('STT:', e.error);
        resetMic();
    };

    recognition.onend = () => { isListening = false; resetMic(); };
}

function toggleMic() {
    if (!recognition) initSpeechRecognition();
    if (!recognition) return;

    if (isListening) {
        recognition.stop();
    } else {
        recognition.start();
    }
}

function resetMic() {
    const micBtn = document.getElementById('micBtn');
    micBtn.style.background = '#0d9488';
    micBtn.innerHTML = '<i class="fas fa-microphone"></i>';
}

function speakBotReply(text) {
    if (!voiceMode || !window.speechSynthesis) return;
    window.speechSynthesis.cancel();
    const clean = text.replace(/<[^>]+>/g, '').replace(/[\*#_`]/g, '').replace(/\s+/g, ' ').trim();
    const utter = new SpeechSynthesisUtterance(clean);
    utter.lang = 'hi-IN';
    utter.rate = 0.95;
    utter.pitch = 1.0;
    const voices = window.speechSynthesis.getVoices();
    const hi = voices.find(v => v.lang.startsWith('hi')) || voices.find(v => v.lang.startsWith('en'));
    if (hi) utter.voice = hi;
    window.speechSynthesis.speak(utter);
}

// Auto-speak bot replies
const origAddMessage = addMessage;
window.addMessage = function(text, isUser) {
    origAddMessage(text, isUser);
    if (!isUser && voiceMode) {
        setTimeout(() => speakBotReply(text), 300);
    }
};

// Preload voices
window.speechSynthesis?.getVoices();
window.speechSynthesis?.addEventListener('voiceschanged', () => {});
</script>
