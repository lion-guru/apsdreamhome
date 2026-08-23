<?php
$page_title = $page_title ?? 'AI Assistant';
$popup_mode = $popup_mode ?? true;
$user_role = $user_role ?? 'customer';
$base = $base ?? BASE_URL;
?>

<div id="ai-popup-widget" class="ai-popup-widget">
    <button id="ai-popup-toggle" class="ai-popup-toggle" onclick="togglePopupChat()" aria-label="Open AI Helper chat">
        <i class="fas fa-robot" aria-hidden="true"></i>
    </button>
    <div id="ai-popup-chat" class="ai-popup-chat style-54390">
        <div class="ai-popup-header">
            <div class="d-flex align-items-center">
                <span class="ai-popup-avatar me-2">ðŸ¤–</span>
                <div>
                    <h6 class="mb-0 text-white">APS AI Helper</h6>
                    <small class="text-white-50">Online</small>
                </div>
            </div>
            <div>
                <button class="btn btn-sm text-white" onclick="toggleMinimizePopup()" title="Minimize" aria-label="Minimize chat">
                    <i class="fas fa-minus" aria-hidden="true"></i>
                </button>
                <button class="btn btn-sm text-white" onclick="closePopupChat()" title="Close" aria-label="Close chat">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div id="ai-popup-messages" class="ai-popup-messages">
            <div class="text-center py-4 px-3">
                <div class="mb-2"><span class="style-46757">ðŸ'‹</span></div>
                <p class="mb-1 small fw-bold">Namaste! Main APS Dream Home ki AI hoon.</p>
                <p class="text-muted small mb-0">Aapki kya madad kar sakta hoon?</p>
            </div>
        </div>
        <div class="ai-popup-footer">
            <div class="input-group input-group-sm">
                <input type="text" id="ai-popup-input" class="form-control" placeholder="Type a message..." onkeypress="handlePopupKeyPress(event)">
                <button class="btn btn-primary" onclick="sendPopupMessage()" aria-label="Send message">
                    <i class="fas fa-paper-plane" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let popupMinimized = false;

function togglePopupChat() {
    const chat = document.getElementById('ai-popup-chat');
    const toggle = document.getElementById('ai-popup-toggle');
    const isHidden = chat.style.display === 'none';
    chat.style.display = isHidden ? 'flex' : 'none';
    toggle.innerHTML = isHidden ? '<i class="fas fa-times"></i>' : '<i class="fas fa-robot"></i>';
}

function closePopupChat() {
    document.getElementById('ai-popup-chat').style.display = 'none';
    document.getElementById('ai-popup-toggle').innerHTML = '<i class="fas fa-robot"></i>';
}

function toggleMinimizePopup() {
    const chat = document.getElementById('ai-popup-chat');
    popupMinimized = !popupMinimized;
    chat.style.height = popupMinimized ? '60px' : '420px';
}

function handlePopupKeyPress(e) {
    if (e.key === 'Enter') sendPopupMessage();
}

async function sendPopupMessage() {
    const input = document.getElementById('ai-popup-input');
    const message = input.value.trim();
    if (!message) return;
    input.value = '';
    addPopupMessage('user', message);
    addPopupTyping();
    try {
        const res = await fetch('<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/api/ai-chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message, role: '<?= htmlspecialchars($user_role ?? '') ?>', context: 'popup_chat' })
        });
        const data = await res.json();
        removePopupTyping();
        addPopupMessage('assistant', data.reply ?? 'Kripya punah prayas karein.');
    } catch (e) {
        removePopupTyping();
        addPopupMessage('assistant', 'Connection error. Kripya punah prayas karein.');
    }
}

function addPopupMessage(type, text) {
    const container = document.getElementById('ai-popup-messages');
    container.querySelector('.text-center')?.remove();
    const div = document.createElement('div');
    div.className = 'mb-2 ' + (type === 'user' ? 'text-end' : 'text-start');
    div.innerHTML = `<span class="d-inline-block p-2 rounded-3 small ${type === 'user' ? 'bg-primary text-white' : 'bg-light'}">${text}</span>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function addPopupTyping() {
    const container = document.getElementById('ai-popup-messages');
    const div = document.createElement('div');
    div.id = 'popup-typing';
    div.className = 'text-start mb-2';
    div.innerHTML = `<span class="d-inline-block p-2 rounded-3 bg-light"><span class="popup-dot"></span><span class="popup-dot"></span><span class="popup-dot"></span></span>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function removePopupTyping() {
    document.getElementById('popup-typing')?.remove();
}
</script>

<style>
.ai-popup-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}
.ai-popup-toggle {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(13,148,136,0.4);
    transition: all 0.3s;
}
.ai-popup-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(13,148,136,0.6);
}
.ai-popup-chat {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 320px;
    height: 420px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #e0e0e0;
}
.ai-popup-header {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    padding: 12px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.ai-popup-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}
.ai-popup-messages {
    flex: 1;
    padding: 12px;
    overflow-y: auto;
    background: #f8f9fa;
}
.ai-popup-footer {
    padding: 10px;
    background: white;
    border-top: 1px solid #eee;
}
.popup-dot {
    display: inline-block;
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #0d9488;
    margin: 0 2px;
    animation: popupDot 1.4s infinite;
}
.popup-dot:nth-child(2) { animation-delay: 0.2s; }
.popup-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes popupDot {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-6px); }
}
</style>
