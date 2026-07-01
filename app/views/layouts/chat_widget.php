<?php if (!isset($sc)) {
    $sc = function ($k, $d = '') {
        return $GLOBALS['_site_settings_cache'][$k] ?? $d;
    };
} ?>
<!-- AI Chat Bot Widget — Premium Glass Morphism -->
<style>
.cw-widget{position:fixed;bottom:20px;right:16px;z-index:9999;font-family:'Segoe UI',system-ui,sans-serif}
@media(min-width:768px){.cw-widget{bottom:90px;right:24px}}

/* Toggle Button */
.cw-toggle{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#0f766e);border:none;color:#fff;font-size:22px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.35s cubic-bezier(0.175,0.885,0.32,1.275);box-shadow:0 6px 20px rgba(13,148,136,0.4);position:relative}
@media(min-width:768px){.cw-toggle{width:64px;height:64px;font-size:26px}}
.cw-toggle:hover{transform:scale(1.1);box-shadow:0 8px 28px rgba(13,148,136,0.5)}
.cw-toggle.cw-active i{animation:cwSpin 0.4s ease}
@keyframes cwSpin{from{transform:rotate(0)}to{transform:rotate(180deg)}}
.cw-toggle .cw-pulse{position:absolute;inset:-4px;border-radius:50%;border:2px solid rgba(13,148,136,0.4);animation:cwPulseRing 2s ease-out infinite}
@keyframes cwPulseRing{0%{transform:scale(1);opacity:1}100%{transform:scale(1.5);opacity:0}}
.cw-toggle .cw-badge{position:absolute;top:-2px;right:-2px;width:20px;height:20px;background:#ef4444;border-radius:50%;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;border:2px solid #fff;display:none}
.cw-toggle .cw-badge.cw-show{display:flex;animation:cwBadgePop 0.3s ease}
@keyframes cwBadgePop{from{transform:scale(0)}to{transform:scale(1)}}

/* Chat Box */
.cw-box{position:absolute;bottom:66px;right:0;width:calc(100vw - 32px);max-width:380px;height:calc(100dvh - 120px);max-height:520px;background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.18),0 0 0 1px rgba(0,0,0,0.04);display:none;flex-direction:column;overflow:hidden}
@media(min-width:768px){.cw-box{bottom:74px}}
.cw-box.cw-show{display:flex;animation:cwSlideUp 0.35s cubic-bezier(0.175,0.885,0.32,1.275)}
@keyframes cwSlideUp{from{opacity:0;transform:translateY(16px) scale(0.97)}to{opacity:1;transform:translateY(0) scale(1)}}

/* Header */
.cw-header{background:linear-gradient(135deg,#0d9488 0%,#0f766e 100%);color:#fff;padding:16px 18px;display:flex;align-items:center;gap:12px;position:relative;overflow:hidden;flex-shrink:0}
.cw-header::before{content:'';position:absolute;top:-50%;right:-30%;width:120%;height:120%;background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,transparent 60%);pointer-events:none}
.cw-header-avatar{width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;position:relative;z-index:1}
.cw-header-avatar::after{content:'';position:absolute;inset:-2px;border-radius:50%;border:2px solid rgba(255,255,255,0.3);animation:cwAvatarGlow 3s ease-in-out infinite}
@keyframes cwAvatarGlow{0%,100%{border-color:rgba(255,255,255,0.2)}50%{border-color:rgba(255,255,255,0.5)}}
.cw-header-info{flex:1;min-width:0;position:relative;z-index:1}
.cw-header-info h4{margin:0;font-size:0.95rem;font-weight:700;line-height:1.2}
.cw-header-info small{font-size:0.72rem;opacity:0.85;display:flex;align-items:center;gap:4px}
.cw-header-info .cw-dot{width:6px;height:6px;background:#10b981;border-radius:50%;animation:cwDotPulse 2s infinite}
@keyframes cwDotPulse{0%,100%{opacity:1}50%{opacity:0.4}}
.cw-close{background:rgba(255,255,255,0.15);border:none;color:#fff;width:34px;height:34px;border-radius:10px;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;flex-shrink:0;position:relative;z-index:1}
.cw-close:hover{background:rgba(255,255,255,0.3)}

/* Messages Area */
.cw-messages{flex:1;overflow-y:auto;padding:14px;background:linear-gradient(180deg,#f8fafc 0%,#f1f5f9 100%);display:flex;flex-direction:column;gap:10px;scroll-behavior:smooth}
.cw-messages::-webkit-scrollbar{width:4px}
.cw-messages::-webkit-scrollbar-track{background:transparent}
.cw-messages::-webkit-scrollbar-thumb{background:#d1d5db;border-radius:4px}

/* Messages */
.cw-msg{display:flex;gap:8px;max-width:88%;animation:cwMsgIn 0.3s ease}
@keyframes cwMsgIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.cw-msg.cw-bot{align-self:flex-start}
.cw-msg.cw-user{align-self:flex-end;flex-direction:row-reverse}
.cw-msg-avatar{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;margin-top:2px}
.cw-bot .cw-msg-avatar{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff}
.cw-user .cw-msg-avatar{background:linear-gradient(135deg,#10b981,#059669);color:#fff}
.cw-bubble{padding:10px 14px;border-radius:16px;font-size:0.85rem;line-height:1.5;white-space:pre-wrap;word-break:break-word}
.cw-bot .cw-bubble{background:#fff;color:#1e293b;border-bottom-left-radius:4px;box-shadow:0 1px 4px rgba(0,0,0,0.06)}
.cw-user .cw-bubble{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;border-bottom-right-radius:4px}

/* Quick Replies */
.cw-quick{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;padding-left:36px}
.cw-quick-btn{background:#fff;border:1.5px solid #e0e7ff;color:#0d9488;padding:6px 14px;border-radius:20px;font-size:0.72rem;font-weight:600;cursor:pointer;transition:all 0.25s ease;white-space:nowrap}
.cw-quick-btn:hover{background:#0d9488;color:#fff;border-color:#0d9488;transform:translateY(-1px);box-shadow:0 3px 10px rgba(13,148,136,0.2)}

/* Typing Indicator */
.cw-typing{display:flex;gap:8px;align-self:flex-start;max-width:88%;padding-left:0}
.cw-typing .cw-typing-bubble{background:#fff;border-radius:16px;border-bottom-left-radius:4px;padding:12px 16px;display:flex;gap:4px;box-shadow:0 1px 4px rgba(0,0,0,0.06)}
.cw-typing-dot{width:7px;height:7px;background:#5eead4;border-radius:50%;animation:cwTypingBounce 1.4s infinite}
.cw-typing-dot:nth-child(2){animation-delay:0.2s}
.cw-typing-dot:nth-child(3){animation-delay:0.4s}
@keyframes cwTypingBounce{0%,60%,100%{transform:translateY(0);background:#5eead4}30%{transform:translateY(-6px);background:#0d9488}}

/* Input Area */
.cw-input-area{padding:12px 14px;background:#fff;border-top:1px solid #f1f5f9;display:flex;gap:8px;flex-shrink:0}
.cw-input{flex:1;border:1.5px solid #e2e8f0;border-radius:24px;padding:10px 16px;font-size:0.85rem;outline:none;transition:all 0.2s;background:#f8fafc}
.cw-input:focus{border-color:#0d9488;background:#fff;box-shadow:0 0 0 3px rgba(13,148,136,0.08)}
.cw-input::placeholder{color:#94a3b8}
.cw-send{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#0f766e);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.25s;flex-shrink:0;font-size:15px}
.cw-send:hover{transform:scale(1.08);box-shadow:0 4px 14px rgba(13,148,136,0.3)}
.cw-send:active{transform:scale(0.95)}
.cw-send:disabled{opacity:0.4;cursor:not-allowed;transform:none}

/* Lead Form inside chat */
.cw-lead-form{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:14px;margin:0 0 0 36px}
.cw-lead-form input{width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:0.82rem;margin-bottom:8px;outline:none;transition:border 0.2s}
.cw-lead-form input:focus{border-color:#0d9488}
.cw-lead-form button{width:100%;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:10px;padding:10px;font-size:0.82rem;font-weight:600;cursor:pointer;transition:all 0.2s}
.cw-lead-form button:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(16,185,129,0.3)}

/* Mobile Fullscreen */
@media(max-width:480px){
    .cw-box{width:calc(100vw - 16px);right:-8px;max-height:calc(100dvh - 100px);border-radius:16px}
    .cw-quick{padding-left:0}
    .cw-lead-form{margin-left:0}
}
</style>

<div class="cw-widget" id="cwWidget">
    <div class="cw-box" id="cwBox">
        <div class="cw-header">
            <div class="cw-header-avatar"><i class="fas fa-robot"></i></div>
            <div class="cw-header-info">
                <h4>APS AI Assistant</h4>
                <small><span class="cw-dot"></span> Online &middot; Hindi / English</small>
            </div>
            <button class="cw-close" onclick="cwToggle()" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <div class="cw-messages" id="cwMessages">
            <div class="cw-msg cw-bot">
                <div class="cw-msg-avatar"><i class="fas fa-robot"></i></div>
                <div class="cw-bubble">Namaste! I'm APS Dream Home AI assistant. I can help you find plots, check prices, schedule site visits, and more.

Select an option or type your query:</div>
            </div>
            <div class="cw-quick" id="cwQuickReplies">
                <button class="cw-quick-btn" onclick="cwQuick('I want to buy a plot')">Buy Property</button>
                <button class="cw-quick-btn" onclick="cwQuick('What are the prices?')">Prices</button>
                <button class="cw-quick-btn" onclick="cwQuick('I want to schedule a site visit')">Site Visit</button>
                <button class="cw-quick-btn" onclick="cwQuick('Need home loan help')">Home Loan</button>
                <button class="cw-quick-btn" onclick="cwQuick('Contact details')">Contact</button>
                <button class="cw-quick-btn" onclick="cwRequestHuman()" style="border-color:#fecdd3;color:#e11d48;">Talk to Human</button>
            </div>
        </div>
        <div class="cw-input-area">
            <input type="text" class="cw-input" id="cwInput" placeholder="Type your message..." onkeypress="if(event.key==='Enter')cwSend()">
            <button class="cw-send" id="cwSendBtn" onclick="cwSend()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
    <button class="cw-toggle" id="cwToggle" onclick="cwToggle()">
        <span class="cw-pulse"></span>
        <i class="fas fa-comment-dots"></i>
        <span class="cw-badge" id="cwBadge">1</span>
    </button>
</div>

<script>
(function(){
    var cwSession = 'web_' + Date.now();
    var cwOpen = false;
    var cwMsgCount = 0;

    window.cwToggle = function(){
        cwOpen = !cwOpen;
        var box = document.getElementById('cwBox');
        var tog = document.getElementById('cwToggle');
        var badge = document.getElementById('cwBadge');
        if(cwOpen){
            box.classList.add('cw-show');
            tog.classList.add('cw-active');
            badge.classList.remove('cw-show');
            document.getElementById('cwInput').focus();
        } else {
            box.classList.remove('cw-show');
            tog.classList.remove('cw-active');
        }
    };

    window.cwQuick = function(text){
        document.getElementById('cwInput').value = text;
        cwSend();
    };

    window.cwSend = function(){
        var input = document.getElementById('cwInput');
        var msg = input.value.trim();
        if(!msg) return;

        var qr = document.getElementById('cwQuickReplies');
        if(qr) qr.remove();

        cwAddMsg(msg, 'user');
        input.value = '';
        document.getElementById('cwSendBtn').disabled = true;
        cwShowTyping();

        var userRole = (window.NOTIFY_USER && window.NOTIFY_USER.role) ? window.NOTIFY_USER.role : 'guest';
        var userId = (window.NOTIFY_USER && window.NOTIFY_USER.id) ? window.NOTIFY_USER.id : '';

        fetch('<?= BASE_URL ?>/api/gemini/chat', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'message=' + encodeURIComponent(msg) + '&session_id=' + cwSession + '&role=' + encodeURIComponent(userRole) + '&user_id=' + encodeURIComponent(userId)
        })
        .then(function(r){ return r.json(); })
        .then(function(data){
            cwHideTyping();
            document.getElementById('cwSendBtn').disabled = false;
            if(data.success && data.response){
                cwAddMsg(data.response, 'bot');
            } else {
                cwFallback(msg);
            }
        })
        .catch(function(){
            cwHideTyping();
            document.getElementById('cwSendBtn').disabled = false;
            cwFallback(msg);
        });

        input.focus();
    };

    window.cwRequestHuman = function(){
        cwAddMsg('I want to talk to a human agent', 'user');
        cwShowTyping();
        setTimeout(function(){
            cwHideTyping();
            cwAddMsg('Kripya apna Naam aur Mobile Number darj karein. Hamare executive aapse turant sampark karenge.', 'bot');
            var c = document.getElementById('cwMessages');
            var f = document.createElement('div');
            f.className = 'cw-lead-form';
            f.innerHTML = '<input type="text" id="cwLeadName" placeholder="Aapka Naam"><input type="tel" id="cwLeadPhone" placeholder="Mobile Number"><button onclick="cwSubmitLead()">Request Callback</button>';
            c.appendChild(f);
            c.scrollTop = c.scrollHeight;
        }, 700);
    };

    window.cwSubmitLead = function(){
        var name = (document.getElementById('cwLeadName')||{}).value||'';
        var phone = (document.getElementById('cwLeadPhone')||{}).value||'';
        if(!name||!phone){alert('Please enter Name and Phone');return;}
        var ni=document.getElementById('cwLeadName'),pi=document.getElementById('cwLeadPhone');
        if(ni)ni.disabled=true;if(pi)pi.disabled=true;
        cwAddMsg('Name: '+name+', Phone: '+phone, 'user');
        cwShowTyping();
        var fd = new URLSearchParams();
        fd.append('name',name);fd.append('phone',phone);fd.append('email','chatbot_lead@apsdreamhome.com');
        fd.append('subject','general');fd.append('message','Callback requested via AI Chatbot');
        fd.append('csrf_token','<?= $_SESSION['csrf_token'] ?? '' ?>');
        fetch('<?= BASE_URL ?>/contact',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:fd.toString()})
        .then(function(){cwHideTyping();cwAddMsg('Dhanyawad '+name+'! Aapki details hamari team ko bhej di gayi hain. Ham jald hi aapse sampark karenge.','bot');})
        .catch(function(){cwHideTyping();cwAddMsg('Details saved! We will contact you soon.','bot');});
    };

    function cwFallback(userMsg){
        setTimeout(function(){
            cwHideTyping();
            var m = userMsg.toLowerCase();
            var r = 'Aapki requirement samajh li gayi hai. Behtar jankari ke liye kripya apna contact number share karein, ya hume <?= htmlspecialchars($sc('contact_phone', '+91 92771 21112')) ?> par call karein.';
            if(m.indexOf('hi')!==-1||m.indexOf('hello')!==-1||m.indexOf('namaste')!==-1) r='Namaste! Mai APS Dream Home ki taraf se aapki kya madad kar sakta hu?';
            else if(m.indexOf('price')!==-1||m.indexOf('rate')!==-1||m.indexOf('kitne')!==-1||m.indexOf('cost')!==-1) r='Hamare premium plots Rs 5.5 Lakh se shuru hote hain. Aapka budget kitna hai?';
            else if(m.indexOf('location')!==-1||m.indexOf('kaha')!==-1||m.indexOf('address')!==-1) r='Hamare projects Gorakhpur, Lucknow, Kushinagar mein available hain. Aapko kis city mein property chahiye?';
            else if(m.indexOf('loan')!==-1||m.indexOf('emi')!==-1||m.indexOf('finance')!==-1) r='Haan, hum home loan aur asaan EMI ki suvidha pradan karte hain. Kya aap EMI details janna chahte hain?';
            else if(m.indexOf('contact')!==-1||m.indexOf('call')!==-1||m.indexOf('number')!==-1) r='Aap hume direct is number par call kar sakte hain: <?= htmlspecialchars($sc('contact_phone', '+91 92771 21112')) ?>.';
            else if(m.indexOf('visit')!==-1||m.indexOf('dekhna')!==-1||m.indexOf('site')!==-1) r='Zaroor! Site visit bilkul free hai. Kripya apna number drop karein ya hume call karein.';
            else if(m.indexOf('buy')!==-1||m.indexOf('khareedna')!==-1||m.indexOf('plot')!==-1) r='Bohot badhiya! Humare paas residential aur commercial dono plots hain. Aap kis city mein dekh rahe hain?';
            cwAddMsg(r,'bot');
        },700);
    }

    function cwAddMsg(text, type){
        var c = document.getElementById('cwMessages');
        var d = document.createElement('div');
        d.className = 'cw-msg cw-' + type;
        var av = type==='bot' ? '<div class="cw-msg-avatar"><i class="fas fa-robot"></i></div>' : '<div class="cw-msg-avatar"><i class="fas fa-user"></i></div>';
        d.innerHTML = av + '<div class="cw-bubble">' + text.replace(/\n/g,'<br>') + '</div>';
        c.appendChild(d);
        c.scrollTop = c.scrollHeight;
        cwMsgCount++;
        if(!cwOpen && type==='bot'){
            var b = document.getElementById('cwBadge');
            b.textContent = cwMsgCount;
            b.classList.add('cw-show');
        }
    }

    function cwShowTyping(){
        var c = document.getElementById('cwMessages');
        var d = document.createElement('div');
        d.id = 'cwTyping';
        d.className = 'cw-typing';
        d.innerHTML = '<div class="cw-msg-avatar" style="background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0"><i class="fas fa-robot"></i></div><div class="cw-typing-bubble"><span class="cw-typing-dot"></span><span class="cw-typing-dot"></span><span class="cw-typing-dot"></span></div>';
        c.appendChild(d);
        c.scrollTop = c.scrollHeight;
    }

    function cwHideTyping(){
        var t = document.getElementById('cwTyping');
        if(t) t.remove();
    }
})();
</script>
