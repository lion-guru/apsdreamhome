<link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
<style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:#0a0a1a;color:#e0e0e0;font-family:'Segoe UI',system-ui,-apple-system,sans-serif;min-height:100dvh;display:flex;flex-direction:column}
    ::-webkit-scrollbar{width:5px}
    ::-webkit-scrollbar-track{background:transparent}
    ::-webkit-scrollbar-thumb{background:#333;border-radius:4px}

    .ai-header{background:linear-gradient(135deg,#0d9488 0%,#0f766e 50%,#134e4a 100%);padding:16px 20px;display:flex;align-items:center;gap:14px;position:relative;overflow:hidden;flex-shrink:0}
    .ai-header::before{content:'';position:absolute;top:-60%;right:-20%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,0.08) 0%,transparent 60%);pointer-events:none}
    .ai-header::after{content:'';position:absolute;bottom:-40%;left:-10%;width:40%;height:120%;background:radial-gradient(circle,rgba(255,255,255,0.04) 0%,transparent 60%);pointer-events:none}
    .ai-avatar{width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,0.15);backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;position:relative;z-index:1;border:2px solid rgba(255,255,255,0.2);animation:avatarGlow 3s ease-in-out infinite}
    @keyframes avatarGlow{0%,100%{box-shadow:0 0 0 0 rgba(255,255,255,0.1)}50%{box-shadow:0 0 20px 4px rgba(255,255,255,0.15)}}
    .ai-info{flex:1;min-width:0;position:relative;z-index:1}
    .ai-info h5{margin:0;color:#fff;font-size:1rem;font-weight:700}
    .ai-info small{color:rgba(255,255,255,0.7);font-size:0.78rem;display:flex;align-items:center;gap:6px}
    .ai-dot{width:7px;height:7px;background:#10b981;border-radius:50%;animation:dotPulse 2s infinite}
    @keyframes dotPulse{0%,100%{opacity:1}50%{opacity:0.3}}
    .ai-header-actions{display:flex;gap:8px;position:relative;z-index:1}
    .ai-header-btn{background:rgba(255,255,255,0.12);border:none;color:#fff;width:36px;height:36px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;text-decoration:none}
    .ai-header-btn:hover{background:rgba(255,255,255,0.25);color:#fff}
    .model-badge{font-size:10px;background:rgba(255,255,255,0.15);color:rgba(255,255,255,0.8);padding:3px 10px;border-radius:8px;display:none}
    .model-badge.show{display:inline-flex}

    .ai-messages{flex:1;overflow-y:auto;padding:20px 16px;display:flex;flex-direction:column;gap:14px;background:linear-gradient(180deg,#0a0a1a 0%,#0f0f2a 50%,#0a0a1a 100%)}
    .ai-msg{max-width:82%;display:flex;gap:8px;animation:msgIn 0.3s ease}
    @keyframes msgIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
    .ai-msg.ai-bot{align-self:flex-start}
    .ai-msg.ai-user{align-self:flex-end;flex-direction:row-reverse}
    .ai-msg-av{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;margin-top:2px}
    .ai-bot .ai-msg-av{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff}
    .ai-user .ai-msg-av{background:linear-gradient(135deg,#10b981,#059669);color:#fff}
    .ai-bubble{padding:12px 16px;border-radius:18px;font-size:0.88rem;line-height:1.55;white-space:pre-wrap;word-break:break-word}
    .ai-bot .ai-bubble{background:rgba(255,255,255,0.06);color:#e8e8f0;border:1px solid rgba(255,255,255,0.06);border-bottom-left-radius:4px;backdrop-filter:blur(10px)}
    .ai-user .ai-bubble{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;border-bottom-right-radius:4px;box-shadow:0 4px 16px rgba(13,148,136,0.3)}
    .ai-msg-header{font-size:10px;color:#14b8a6;margin-bottom:4px;display:flex;align-items:center;gap:5px;font-weight:600}
    .ai-msg-header .model-badge{font-size:9px}

    .ai-feedback{margin-top:8px;display:flex;gap:5px}
    .ai-fb-btn{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#666;border-radius:10px;padding:3px 10px;font-size:12px;cursor:pointer;transition:all 0.2s}
    .ai-fb-btn:hover{background:rgba(13,148,136,0.2);color:#14b8a6;border-color:#0d9488}
    .ai-fb-btn.active{background:#0d9488;color:#fff;border-color:#0d9488}

    .ai-suggestions{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;padding-left:38px}
    .ai-sug-btn{background:rgba(13,148,136,0.1);border:1px solid rgba(13,148,136,0.25);color:#14b8a6;border-radius:18px;padding:6px 14px;font-size:0.75rem;font-weight:600;cursor:pointer;transition:all 0.25s;white-space:nowrap}
    .ai-sug-btn:hover{background:#0d9488;color:#fff;border-color:#0d9488;transform:translateY(-1px);box-shadow:0 4px 12px rgba(13,148,136,0.3)}

    .ai-typing{display:flex;gap:8px;align-self:flex-start;padding-left:0}
    .ai-typing-bubble{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.06);border-radius:18px;border-bottom-left-radius:4px;padding:14px 18px;display:flex;gap:5px}
    .ai-typing-dot{width:7px;height:7px;background:#0d9488;border-radius:50%;animation:typingBounce 1.4s infinite}
    .ai-typing-dot:nth-child(2){animation-delay:0.2s}
    .ai-typing-dot:nth-child(3){animation-delay:0.4s}
    @keyframes typingBounce{0%,60%,100%{transform:translateY(0);opacity:0.4}30%{transform:translateY(-7px);opacity:1}}

    .ai-input-area{padding:14px 16px;background:rgba(15,15,42,0.95);backdrop-filter:blur(20px);border-top:1px solid rgba(255,255,255,0.06);display:flex;gap:10px;flex-shrink:0}
    .ai-input{flex:1;background:rgba(255,255,255,0.06);border:1.5px solid rgba(255,255,255,0.1);border-radius:24px;padding:12px 18px;color:#fff;font-size:0.88rem;outline:none;transition:all 0.2s}
    .ai-input:focus{border-color:#0d9488;background:rgba(255,255,255,0.08);box-shadow:0 0 0 3px rgba(13,148,136,0.15)}
    .ai-input::placeholder{color:#555}
    .ai-send{width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#0f766e);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.25s;flex-shrink:0;font-size:16px}
    .ai-send:hover{transform:scale(1.08);box-shadow:0 6px 20px rgba(13,148,136,0.4)}
    .ai-send:active{transform:scale(0.95)}
    .ai-send:disabled{opacity:0.3;cursor:not-allowed;transform:none}

    @media(max-width:600px){
        .ai-msg{max-width:90%}
        .ai-messages{padding:14px 10px}
        .ai-input-area{padding:10px 12px}
        .ai-suggestions{padding-left:0}
    }
</style>

<div class="style-47021">
    <div class="ai-header">
        <div class="ai-avatar"><i class="fas fa-robot"></i></div>
        <div class="ai-info">
            <h5>APS AI Assistant <span class="ai-dot"></span></h5>
            <small>Self-learning property expert &middot; Always here to help <span class="model-badge" id="modelBadge"></span></small>
        </div>
        <div class="ai-header-actions">
            <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="ai-header-btn" title="Home"><i class="fas fa-home"></i></a>
        </div>
    </div>

    <div class="ai-messages" id="chatMessages">
        <div class="ai-msg ai-bot">
            <div class="ai-msg-av"><i class="fas fa-robot"></i></div>
            <div>
                <div class="ai-bubble">Hello! I'm your AI property assistant. I can help you find plots, check prices, schedule site visits, and more. What would you like to know?</div>
                <div class="ai-suggestions" id="welcomeSuggestions">
                    <button class="ai-sug-btn" onclick="sendQuick('Show me available properties')">Properties</button>
                    <button class="ai-sug-btn" onclick="sendQuick('What are the prices?')">Prices</button>
                    <button class="ai-sug-btn" onclick="sendQuick('I want to schedule a site visit')">Site Visit</button>
                    <button class="ai-sug-btn" onclick="sendQuick('How can I earn commission?')">Earning</button>
                    <button class="ai-sug-btn" onclick="sendQuick('I need home loan help')">Home Loan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="ai-input-area">
        <input type="text" class="ai-input" id="chatInput" placeholder="Type your message... (Hindi or English)" autocomplete="off">
        <button class="ai-send" id="sendBtn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<script>
    var BASE_URL = '<?= defined('BASE_URL') ? BASE_URL : '/' ?>';
    var chatMessages = document.getElementById('chatMessages');
    var chatInput = document.getElementById('chatInput');
    var sendBtn = document.getElementById('sendBtn');
    var modelBadge = document.getElementById('modelBadge');
    var sessionId = '<?= session_id() ?? "sess_" . time() ?>';
    var messageCount = 0;

    function sendQuick(text) {
        chatInput.value = text;
        sendMessage();
    }

    function sendMessage() {
        var message = chatInput.value.trim();
        if (!message) return;
        var ws = document.getElementById('welcomeSuggestions');
        if (ws) ws.remove();
        appendMsg('user', message);
        chatInput.value = '';
        sendBtn.disabled = true;
        var typingEl = appendTyping();

        fetch(BASE_URL + '/api/ai/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ message: message, session_id: sessionId })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            typingEl.remove();
            sendBtn.disabled = false;
            if (data.success || data.response) {
                var msgId = ++messageCount;
                appendMsg('bot', data.response, msgId, data.model);
                if (data.model) { modelBadge.textContent = data.model; modelBadge.classList.add('show'); }
            } else {
                appendMsg('bot', 'Sorry, something went wrong. Please try again.');
            }
        })
        .catch(function() {
            typingEl.remove();
            sendBtn.disabled = false;
            appendMsg('bot', 'Connection error. Please check your internet and try again.');
        });
        chatInput.focus();
    }

    function appendMsg(sender, text, msgId, model) {
        var div = document.createElement('div');
        div.className = 'ai-msg ai-' + sender;
        if (sender === 'bot') {
            var av = '<div class="ai-msg-av"><i class="fas fa-robot"></i></div>';
            var hdr = '<div class="ai-msg-header"><i class="fas fa-robot"></i> APS AI';
            if (model) hdr += ' <span class="model-badge">' + model + '</span>';
            hdr += '</div>';
            var fb = '';
            if (msgId) {
                fb = '<div class="ai-feedback"><button class="ai-fb-btn" onclick="giveFeedback(' + msgId + ',true,this)" title="Helpful">Helpful</button><button class="ai-fb-btn" onclick="giveFeedback(' + msgId + ',false,this)" title="Not helpful">Not helpful</button></div>';
            }
            div.innerHTML = av + '<div>' + hdr + '<div class="ai-bubble">' + formatBotText(text) + '</div>' + fb + '</div>';
        } else {
            div.innerHTML = '<div class="ai-msg-av"><i class="fas fa-user"></i></div><div class="ai-bubble">' + escapeHtml(text) + '</div>';
        }
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return div;
    }

    function appendTyping() {
        var div = document.createElement('div');
        div.id = 'typingIndicator';
        div.className = 'ai-typing';
        div.innerHTML = '<div class="ai-msg-av style-87578"><i class="fas fa-robot"></i></div><div class="ai-typing-bubble"><span class="ai-typing-dot"></span><span class="ai-typing-dot"></span><span class="ai-typing-dot"></span></div>';
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return div;
    }

    function giveFeedback(msgId, positive, btn) {
        var siblings = btn.parentElement.querySelectorAll('.ai-fb-btn');
        siblings.forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        fetch(BASE_URL + '/api/ai/feedback', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ message_id: msgId, positive: positive, session_id: sessionId })
        });
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function linkifyUrls(text) {
        var urlRegex = /(\bhttps?:\/\/[^\s<>'")\]]+)/gi;
        return text.replace(urlRegex, function(url) {
            var cleanUrl = url.replace(/[.,;:!?)}\]]+$/, '');
            var suffix = url.substring(cleanUrl.length);
            return '<a href="' + cleanUrl + '" target="_blank" rel="noopener" class="style-95271">' + cleanUrl + '</a>' + suffix;
        });
    }

    function formatBotText(text) {
        var html = escapeHtml(text);
        html = linkifyUrls(html);
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        return html;
    }

    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !sendBtn.disabled) sendMessage();
    });
    chatInput.focus();
</script>
