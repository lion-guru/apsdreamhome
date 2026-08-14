<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 mt-4">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0"><i class="fas fa-robot me-2"></i>APS AI Voice Assistant</h4>
                    <small>Ask me anything about APS Dream Home</small>
                </div>
                <div class="card-body">
                    <!-- Chat Container -->
                    <div id="chat-container" class="chat-container mb-3" class="style-57417">
                        <div class="message bot-message mb-2">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-primary text-white rounded-circle me-2" class="style-21880">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="message-content bg-white p-2 rounded shadow-sm" class="style-6955">
                                    <p class="mb-0">Hello! I'm your APS Dream Home assistant. Ask me about plots, bookings, commissions, finance, or anything else!</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="input-group mb-3">
                        <input type="text" id="voice-input" class="form-control" placeholder="Ask me anything..." autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="mic-btn" title="Voice Input">
                            <i class="fas fa-microphone"></i>
                        </button>
                        <button class="btn btn-primary" type="button" id="send-btn">
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions mb-3">
                        <small class="text-muted">Quick Questions:</small>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <button class="btn btn-sm btn-outline-primary quick-q" data-q="How many plots are available?">Plot Availability</button>
                            <button class="btn btn-sm btn-outline-primary quick-q" data-q="Total bookings this month?">Bookings</button>
                            <button class="btn btn-sm btn-outline-primary quick-q" data-q="My wallet balance?">Wallet</button>
                            <button class="btn btn-sm btn-outline-primary quick-q" data-q="Total commission?">Commission</button>
                            <button class="btn btn-sm btn-outline-primary quick-q" data-q="Help">Help</button>
                        </div>
                    </div>

                    <!-- Voice Visualizer -->
                    <div id="voice-visualizer" class="text-center mb-2" class="style-54390">
                        <div class="d-inline-flex align-items-center bg-dark text-white px-4 py-2 rounded-pill">
                            <i class="fas fa-microphone-alt text-danger me-2" class="style-41087"></i>
                            <span>Listening...</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center text-muted">
                    <small><i class="fas fa-shield-alt me-1"></i>RBAC Secure | Role: <strong><?= htmlspecialchars($_SESSION['role'] ?? $_SESSION['admin_role'] ?? 'Guest') ?></strong></small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .message { margin-bottom: 10px; }
    .bot-message .message-content { background: #e3f2fd !important; }
    .user-message .message-content { background: #e8f5e9 !important; }
    .user-message { text-align: right; }
    .user-message .d-flex { flex-direction: row-reverse; }
    .user-message .avatar { background: #4caf50 !important; margin-right: 0 !important; margin-left: 8px; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    .chat-container::-webkit-scrollbar { width: 6px; }
    .chat-container::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
</style>

<script src="/apsdreamhome/assets/js/voice-search.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.getElementById('chat-container');
    const voiceInput = document.getElementById('voice-input');
    const micBtn = document.getElementById('mic-btn');
    const sendBtn = document.getElementById('send-btn');
    const visualizer = document.getElementById('voice-visualizer');

    // Initialize voice search
    let voiceSearch = null;
    if (VoiceSearch.isSupported()) {
        voiceSearch = new VoiceSearch({
            lang: 'en-IN',
            onStart: () => {
                visualizer.style.display = 'block';
                micBtn.innerHTML = '<i class="fas fa-microphone-slash text-danger"></i>';
            },
            onResult: (transcript) => {
                voiceInput.value = transcript;
                visualizer.style.display = 'none';
                sendQuery(transcript);
            },
            onError: (error) => {
                visualizer.style.display = 'none';
                addMessage('bot', 'Voice recognition error: ' + error);
            },
            onEnd: () => {
                visualizer.style.display = 'none';
                micBtn.innerHTML = '<i class="fas fa-microphone"></i>';
            }
        });

        micBtn.addEventListener('click', () => voiceSearch.toggle());
    } else {
        micBtn.style.display = 'none';
    }

    // Send button
    sendBtn.addEventListener('click', () => {
        const query = voiceInput.value.trim();
        if (query) sendQuery(query);
    });

    // Enter key
    voiceInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const query = voiceInput.value.trim();
            if (query) sendQuery(query);
        }
    });

    // Quick questions
    document.querySelectorAll('.quick-q').forEach(btn => {
        btn.addEventListener('click', () => {
            const q = btn.dataset.q;
            voiceInput.value = q;
            sendQuery(q);
        });
    });

    // Send query to API
    function sendQuery(query) {
        addMessage('user', query);
        voiceInput.value = '';

        // Show typing indicator
        const typingId = addTypingIndicator();

        fetch('/api/voice-assistant/query', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query: query })
        })
        .then(r => r.json())
        .then(data => {
            removeTypingIndicator(typingId);
            if (data.success) {
                addMessage('bot', data.message);
                speak(data.message);
            } else {
                addMessage('bot', data.message || 'Sorry, I could not process your request.');
            }
        })
        .catch(err => {
            removeTypingIndicator(typingId);
            addMessage('bot', 'Connection error. Please try again.');
        });
    }

    // Add message to chat
    function addMessage(type, text) {
        const div = document.createElement('div');
        div.className = `message ${type}-message mb-2`;
        const avatar = type === 'bot' 
            ? '<div class="avatar bg-primary text-white rounded-circle me-2" class="style-21880"><i class="fas fa-robot"></i></div>'
            : '<div class="avatar bg-success text-white rounded-circle me-2" class="style-21880"><i class="fas fa-user"></i></div>';
        
        div.innerHTML = `<div class="d-flex align-items-start">${avatar}<div class="message-content bg-white p-2 rounded shadow-sm" class="style-6955"><p class="mb-0">${text}</p></div></div>`;
        chatContainer.appendChild(div);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Add typing indicator
    function addTypingIndicator() {
        const id = 'typing-' + Date.now();
        const div = document.createElement('div');
        div.id = id;
        div.className = 'message bot-message mb-2';
        div.innerHTML = '<div class="d-flex align-items-start"><div class="avatar bg-primary text-white rounded-circle me-2" class="style-21880"><i class="fas fa-robot"></i></div><div class="message-content bg-white p-2 rounded shadow-sm"><p class="mb-0"><i>Typing...</i></p></div></div>';
        chatContainer.appendChild(div);
        chatContainer.scrollTop = chatContainer.scrollHeight;
        return id;
    }

    // Remove typing indicator
    function removeTypingIndicator(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    // Text-to-speech
    function speak(text) {
        if ('speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'en-IN';
            utterance.rate = 1;
            speechSynthesis.speak(utterance);
        }
    }
});
</script>
