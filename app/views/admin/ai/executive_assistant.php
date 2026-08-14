<?php
/**
 * Executive AI Assistant â€” Chat Interface
 * Role-context-aware AI assistant for all executive roles
 */
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
$role = $role ?? 'admin';
$roleTitle = $roleTitle ?? 'Executive';
$userName = $userName ?? 'User';
$focusAreas = $focusAreas ?? [];
?>
<style>
    .ai-assistant-container { display: flex; height: calc(100vh - 120px); gap: 16px; }
    .ai-sidebar { width: 320px; flex-shrink: 0; display: flex; flex-direction: column; gap: 16px; }
    .ai-chat-main { flex: 1; display: flex; flex-direction: column; background: #111827; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.06); }
    
    .ai-role-card { background: linear-gradient(135deg, #0f172a, #1e3a5f); border-radius: 16px; padding: 24px; border: 1px solid rgba(255,255,255,0.08); }
    .ai-role-card h3 { color: #f0fdfa; font-size: 18px; margin: 0 0 4px; }
    .ai-role-card .role-badge { display: inline-block; background: rgba(13,148,136,0.2); color: #0d9488; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    
    .ai-kpi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .ai-kpi-item { background: rgba(255,255,255,0.04); border-radius: 10px; padding: 12px; text-align: center; border: 1px solid rgba(255,255,255,0.06); }
    .ai-kpi-item .value { color: #f0fdfa; font-size: 16px; font-weight: 700; }
    .ai-kpi-item .label { color: #94a3b8; font-size: 11px; margin-top: 2px; }
    
    .ai-quick-queries { background: rgba(255,255,255,0.03); border-radius: 16px; padding: 16px; border: 1px solid rgba(255,255,255,0.06); }
    .ai-quick-queries h4 { color: #94a3b8; font-size: 12px; text-transform: uppercase; margin: 0 0 10px; letter-spacing: 1px; }
    .quick-query-btn { display: block; width: 100%; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 10px 14px; color: #e2e8f0; font-size: 13px; text-align: left; cursor: pointer; margin-bottom: 6px; transition: all 0.2s; }
    .quick-query-btn:hover { background: rgba(13,148,136,0.15); border-color: rgba(13,148,136,0.3); }
    
    .ai-chat-header { padding: 16px 24px; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; gap: 12px; }
    .ai-chat-header .avatar { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #0d9488, #06b6d4); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; }
    .ai-chat-header .info h3 { color: #f0fdfa; margin: 0; font-size: 16px; }
    .ai-chat-header .info p { color: #64748b; margin: 0; font-size: 12px; }
    
    .ai-chat-messages { flex: 1; overflow-y: auto; padding: 24px; display: flex; flex-direction: column; gap: 16px; }
    .ai-message { max-width: 80%; padding: 14px 18px; border-radius: 16px; font-size: 14px; line-height: 1.6; }
    .ai-message.user { background: linear-gradient(135deg, #0d9488, #06b6d4); color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
    .ai-message.assistant { background: rgba(255,255,255,0.06); color: #e2e8f0; align-self: flex-start; border-bottom-left-radius: 4px; }
    .ai-message .engine-tag { font-size: 10px; color: #64748b; margin-top: 6px; }
    
    .ai-chat-actions { display: flex; gap: 8px; margin-top: 8px; }
    .ai-action-chip { background: rgba(13,148,136,0.15); color: #0d9488; padding: 4px 12px; border-radius: 20px; font-size: 12px; cursor: pointer; text-decoration: none; border: 1px solid rgba(13,148,136,0.3); transition: all 0.2s; }
    .ai-action-chip:hover { background: rgba(13,148,136,0.25); }
    
    .ai-chat-input { padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; gap: 12px; align-items: flex-end; }
    .ai-chat-input textarea { flex: 1; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 12px 16px; color: #e2e8f0; font-size: 14px; resize: none; min-height: 48px; max-height: 120px; font-family: inherit; }
    .ai-chat-input textarea::placeholder { color: #64748b; }
    .ai-chat-input textarea:focus { outline: none; border-color: rgba(13,148,136,0.5); }
    .ai-send-btn { width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #0d9488, #06b6d4); border: none; color: #fff; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.2s; flex-shrink: 0; }
    .ai-send-btn:hover { transform: scale(1.05); }
    .ai-send-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
    
    .ai-typing { display: none; align-self: flex-start; padding: 10px 16px; background: rgba(255,255,255,0.04); border-radius: 16px; color: #64748b; font-size: 13px; }
    .ai-typing.active { display: flex; align-items: center; gap: 8px; }
    .typing-dots { display: flex; gap: 4px; }
    .typing-dots span { width: 6px; height: 6px; border-radius: 50%; background: #0d9488; animation: typingBounce 1.4s infinite; }
    .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typingBounce { 0%,60%,100% { transform: translateY(0); } 30% { transform: translateY(-4px); } }
    
    .welcome-message { text-align: center; padding: 60px 24px; }
    .welcome-message .icon { font-size: 48px; margin-bottom: 16px; }
    .welcome-message h2 { color: #f0fdfa; margin: 0 0 8px; font-size: 22px; }
    .welcome-message p { color: #94a3b8; font-size: 14px; max-width: 400px; margin: 0 auto; }
    
    @media (max-width: 1024px) { 
        .ai-assistant-container { flex-direction: column; height: auto; }
        .ai-sidebar { width: 100%; }
        .ai-chat-main { min-height: 500px; }
    }
</style>

<div class="ai-assistant-container">
    <!-- Sidebar: Role Info + KPIs + Quick Queries -->
    <div class="ai-sidebar">
        <div class="ai-role-card">
            <h3><?= htmlspecialchars($userName) ?></h3>
            <span class="role-badge"><?= htmlspecialchars($roleTitle) ?></span>
            <?php if (!empty($focusAreas)): ?>
            <div class="style-17873">
                <div class="style-83709">Focus Areas</div>
                <div class="style-47731">
                    <?php foreach ($focusAreas as $area): ?>
                    <span class="style-56820"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $area))) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="ai-kpi-grid" id="aiKpiGrid">
            <div class="ai-kpi-item"><div class="value">...</div><div class="label">Loading KPIs</div></div>
        </div>
        
        <div class="ai-quick-queries">
            <h4>Quick Queries</h4>
            <button class="quick-query-btn" onclick="sendQuickQuery('What are my top priorities today?')">ðŸŽ¯ Today's Priorities</button>
            <button class="quick-query-btn" onclick="sendQuickQuery('Give me a quick performance summary')">ðŸ“Š Performance Summary</button>
            <button class="quick-query-btn" onclick="sendQuickQuery('What actions should I take this week?')">âš¡ This Week Actions</button>
            <button class="quick-query-btn" onclick="sendQuickQuery('Show me any alerts or issues')">ðŸš¨ Alerts & Issues</button>
            <button class="quick-query-btn" onclick="sendQuickQuery('Compare this month vs last month')">ðŸ“ˆ Month Comparison</button>
        </div>
    </div>
    
    <!-- Main Chat Area -->
    <div class="ai-chat-main">
        <div class="ai-chat-header">
            <div class="avatar"><i class="fas fa-robot"></i></div>
            <div class="info">
                <h3>APS AI Assistant</h3>
                <p>Role-aware â€¢ Real-time data â€¢ Hinglish</p>
            </div>
        </div>
        
        <div class="ai-chat-messages" id="chatMessages">
            <div class="welcome-message">
                <div class="icon">ðŸ¤–</div>
                <h2>Namaste, <?= htmlspecialchars($userName) ?>!</h2>
                <p>Main aapka <?= htmlspecialchars($roleTitle) ?> AI assistant hoon. Ask me anything about your department, KPIs, or business decisions.</p>
            </div>
        </div>
        
        <div class="ai-typing" id="typingIndicator">
            <div class="typing-dots"><span></span><span></span><span></span></div>
            Thinking...
        </div>
        
        <div class="ai-chat-input">
            <textarea id="chatInput" rows="1" placeholder="Type your message... (Enter to send, Shift+Enter for new line)"></textarea>
            <button class="ai-send-btn" id="sendBtn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<script>
const chatMessages = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const sendBtn = document.getElementById('sendBtn');
const typingIndicator = document.getElementById('typingIndicator');
const kpiGrid = document.getElementById('aiKpiGrid');

let welcomeRemoved = false;

// Auto-resize textarea
chatInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

// Enter to send, Shift+Enter for newline
chatInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

function removeWelcome() {
    if (!welcomeRemoved) {
        const welcome = chatMessages.querySelector('.welcome-message');
        if (welcome) welcome.remove();
        welcomeRemoved = true;
    }
}

function addMessage(text, type, engine) {
    removeWelcome();
    const div = document.createElement('div');
    div.className = 'ai-message ' + type;
    div.innerHTML = text.replace(/\n/g, '<br>');
    if (type === 'assistant' && engine) {
        div.innerHTML += '<div class="engine-tag"><i class="fas fa-microchip"></i> Powered by ' + engine + '</div>';
    }
    chatMessages.appendChild(div);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    return div;
}

function addActionChips(actions, container) {
    if (!actions || actions.length === 0) return;
    const chipDiv = document.createElement('div');
    chipDiv.className = 'ai-chat-actions';
    actions.forEach(a => {
        const chip = document.createElement('a');
        chip.className = 'ai-action-chip';
        chip.href = '<?= $base ?>' + a.url;
        chip.textContent = a.label;
        chipDiv.appendChild(chip);
    });
    container.after(chipDiv);
}

async function sendMessage() {
    const msg = chatInput.value.trim();
    if (!msg) return;
    
    chatInput.value = '';
    chatInput.style.height = 'auto';
    sendBtn.disabled = true;
    
    addMessage(msg, 'user');
    typingIndicator.classList.add('active');
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch('<?= $base ?>/admin/ai/executive-assistant/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ message: msg })
        });
        const data = await res.json();
        
        typingIndicator.classList.remove('active');
        
        if (data.success) {
            const msgEl = addMessage(data.response, 'assistant', data.engine);
            if (data.suggested_actions) {
                addActionChips(data.suggested_actions, msgEl);
            }
            if (data.quick_data) {
                updateKPIs(data.quick_data);
            }
        } else {
            addMessage('Sorry, kuch gadbad ho gayi. Please try again.', 'assistant');
        }
    } catch (err) {
        typingIndicator.classList.remove('active');
        addMessage('Network error. Please check your connection.', 'assistant');
    }
    
    sendBtn.disabled = false;
    chatInput.focus();
}

function sendQuickQuery(query) {
    chatInput.value = query;
    sendMessage();
}

function updateKPIs(kpis) {
    if (!kpis || Object.keys(kpis).length === 0) return;
    kpiGrid.innerHTML = '';
    const keys = Object.keys(kpis).slice(0, 6); // Max 6 KPIs
    keys.forEach(key => {
        kpiGrid.innerHTML += '<div class="ai-kpi-item"><div class="value">' + kpis[key] + '</div><div class="label">' + key + '</div></div>';
    });
}

// Load KPIs on page load
async function loadInitialKPIs() {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch('<?= $base ?>/admin/ai/executive-assistant/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ message: 'Give me a quick performance summary' })
        });
        const data = await res.json();
        if (data.success && data.quick_data) {
            updateKPIs(data.quick_data);
        }
    } catch (e) {}
}

loadInitialKPIs();
chatInput.focus();
</script>
