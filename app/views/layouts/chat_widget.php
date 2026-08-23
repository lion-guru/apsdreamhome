<?php if (!isset($sc)) {
    $sc = function ($k, $d = '') {
        return $GLOBALS['_site_settings_cache'][$k] ?? $d;
    };
}
$waPhone = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112'));
$cPhone = htmlspecialchars($sc('contact_phone', '+91 92771 21112'));
$callDigits = preg_replace('/[^0-9]/', '', $cPhone);
$userRole = isset($_SESSION['admin_id']) ? 'admin' : (isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_SESSION['user_id']) ? 'customer' : 'guest'));
$userName = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? '';
?>
<!-- •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
     APS AI Chatbot + WhatsApp + Call + Voice — Unified Widget v3
      Unified widget: AI chatbot + voice + WhatsApp + call — all in one place.
     •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•� -->
<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.cw-wrap{position:fixed;bottom:20px;right:16px;z-index:9999;font-family:'Segoe UI',system-ui,sans-serif;display:flex;flex-direction:column;align-items:flex-end;gap:10px}
@media(min-width:768px){.cw-wrap{bottom:24px;right:24px}}

/* —€—€ Toggle Buttons (Chatbot + WhatsApp side by side) —€—€ */
.cw-toggles{display:flex;gap:10px;align-items:center}
.cw-toggle{width:56px;height:56px;border-radius:50%;border:none;color:#fff;font-size:22px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.35s cubic-bezier(0.175,0.885,0.32,1.275);position:relative;flex-shrink:0}
@media(min-width:768px){.cw-toggle{width:60px;height:60px;font-size:24px}}
.cw-toggle:hover{transform:scale(1.1)}
.cw-toggle.cw-active i{animation:cwSpin 0.4s ease}
@keyframes cwSpin{from{transform:rotate(0)}to{transform:rotate(180deg)}}
.cw-toggle .cw-pulse{position:absolute;inset:-4px;border-radius:50%;border:2px solid rgba(255,255,255,0.3);animation:cwPulseRing 2.5s ease-out infinite;pointer-events:none}
@keyframes cwPulseRing{0%{transform:scale(1);opacity:1}100%{transform:scale(1.5);opacity:0}}
.cw-toggle .cw-badge{position:absolute;top:-3px;right:-3px;width:20px;height:20px;background:#ef4444;border-radius:50%;font-size:10px;font-weight:700;display:none;align-items:center;justify-content:center;border:2px solid #fff}
.cw-toggle .cw-badge.cw-show{display:flex;animation:cwBadgePop 0.3s ease}
@keyframes cwBadgePop{from{transform:scale(0)}to{transform:scale(1)}}
#cwChatToggle{background:linear-gradient(135deg,#0d9488,#0f766e);box-shadow:0 6px 20px rgba(13,148,136,0.4)}
#cwChatToggle:hover{box-shadow:0 8px 28px rgba(13,148,136,0.5)}
#cwWhatsAppToggle{background:#25D366;box-shadow:0 6px 20px rgba(37,211,102,0.4);text-decoration:none;color:#fff}
#cwWhatsAppToggle:hover{box-shadow:0 8px 28px rgba(37,211,102,0.5);color:#fff}
#cwCallToggle{background:linear-gradient(135deg,#6366f1,#4f46e5);box-shadow:0 6px 20px rgba(99,102,241,0.4);text-decoration:none;color:#fff}
#cwCallToggle:hover{box-shadow:0 8px 28px rgba(99,102,241,0.5);color:#fff}

/* —€—€ Chat Box —€—€ */
.cw-box{position:absolute;bottom:72px;right:0;width:calc(100vw - 32px);max-width:380px;height:calc(100dvh - 140px);max-height:540px;background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2),0 0 0 1px rgba(0,0,0,0.04);display:none;flex-direction:column;overflow:hidden}
@media(min-width:768px){.cw-box{bottom:76px}}
.cw-box.cw-show{display:flex;animation:cwSlideUp 0.35s cubic-bezier(0.175,0.885,0.32,1.275)}
@keyframes cwSlideUp{from{opacity:0;transform:translateY(16px) scale(0.97)}to{opacity:1;transform:translateY(0) scale(1)}}

/* —€—€ Header —€—€ */
.cw-header{background:linear-gradient(135deg,#0d9488 0%,#0f766e 100%);color:#fff;padding:14px 16px;display:flex;align-items:center;gap:10px;position:relative;overflow:hidden;flex-shrink:0}
.cw-header::before{content:'';position:absolute;top:-50%;right:-30%;width:120%;height:120%;background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,transparent 60%);pointer-events:none}
.cw-header-avatar{width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;position:relative;z-index:1}
.cw-header-avatar::after{content:'';position:absolute;inset:-2px;border-radius:50%;border:2px solid rgba(255,255,255,0.3);animation:cwAvatarGlow 3s ease-in-out infinite}
@keyframes cwAvatarGlow{0%,100%{border-color:rgba(255,255,255,0.2)}50%{border-color:rgba(255,255,255,0.5)}}
.cw-header-info{flex:1;min-width:0;position:relative;z-index:1}
.cw-header-info h4{margin:0;font-size:0.9rem;font-weight:700;line-height:1.2}
.cw-header-info small{font-size:0.7rem;opacity:0.85;display:flex;align-items:center;gap:4px}
.cw-header-info .cw-dot{width:6px;height:6px;background:#10b981;border-radius:50%;animation:cwDotPulse 2s infinite}
@keyframes cwDotPulse{0%,100%{opacity:1}50%{opacity:0.4}}
.cw-close{background:rgba(255,255,255,0.15);border:none;color:#fff;width:32px;height:32px;border-radius:8px;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;flex-shrink:0;position:relative;z-index:1}
.cw-close:hover{background:rgba(255,255,255,0.3)}

/* —€—€ Messages —€—€ */
.cw-messages{flex:1;overflow-y:auto;padding:12px;background:linear-gradient(180deg,#f8fafc 0%,#f1f5f9 100%);display:flex;flex-direction:column;gap:8px;scroll-behavior:smooth}
.cw-messages::-webkit-scrollbar{width:4px}
.cw-messages::-webkit-scrollbar-thumb{background:#d1d5db;border-radius:4px}
.cw-msg{display:flex;gap:6px;max-width:88%;animation:cwMsgIn 0.3s ease}
@keyframes cwMsgIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
.cw-msg.cw-bot{align-self:flex-start}
.cw-msg.cw-user{align-self:flex-end;flex-direction:row-reverse}
.cw-msg-avatar{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;margin-top:2px}
.cw-bot .cw-msg-avatar{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff}
.cw-user .cw-msg-avatar{background:linear-gradient(135deg,#10b981,#059669);color:#fff}
.cw-bubble{padding:10px 14px;border-radius:14px;font-size:0.84rem;line-height:1.5;white-space:pre-wrap;word-break:break-word;color:#1e293b}
.cw-bot .cw-bubble{background:#fff;border-bottom-left-radius:4px;box-shadow:0 1px 3px rgba(0,0,0,0.06)}
.cw-user .cw-bubble{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;border-bottom-right-radius:4px}

/* —€—€ Quick Replies —€—€ */
.cw-quick{display:flex;flex-wrap:wrap;gap:5px;margin-top:6px;padding-left:32px}
.cw-quick-btn{background:#fff;border:1.5px solid #e0e7ff;color:#0d9488;padding:5px 12px;border-radius:20px;font-size:0.7rem;font-weight:600;cursor:pointer;transition:all 0.25s ease;white-space:nowrap}
.cw-quick-btn:hover{background:#0d9488;color:#fff;border-color:#0d9488;transform:translateY(-1px);box-shadow:0 3px 8px rgba(13,148,136,0.2)}

/* —€—€ Feedback Buttons —€—€ */
.cw-feedback{display:flex;gap:4px;margin-top:4px;padding-left:32px;opacity:0;transition:opacity 0.3s}
.cw-msg:hover .cw-feedback{opacity:1}
.cw-fb-btn{background:none;border:1.5px solid #e2e8f0;width:26px;height:26px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:11px;transition:all 0.2s;color:#94a3b8}
.cw-fb-btn:hover{border-color:#0d9488;color:#0d9488;background:#f0fdfa}
.cw-fb-btn.cw-fb-active{border-color:#0d9488;color:#fff;background:#0d9488}
.cw-fb-btn.cw-fb-active.cw-fb-down{border-color:#ef4444;background:#ef4444}

/* —€—€ Typing —€—€ */
.cw-typing{display:flex;gap:6px;align-self:flex-start;max-width:88%}
.cw-typing .cw-typing-bubble{background:#fff;border-radius:14px;border-bottom-left-radius:4px;padding:10px 14px;display:flex;gap:4px;box-shadow:0 1px 3px rgba(0,0,0,0.06)}
.cw-typing-dot{width:6px;height:6px;background:#5eead4;border-radius:50%;animation:cwTypingBounce 1.4s infinite}
.cw-typing-dot:nth-child(2){animation-delay:0.2s}
.cw-typing-dot:nth-child(3){animation-delay:0.4s}
@keyframes cwTypingBounce{0%,60%,100%{transform:translateY(0);background:#5eead4}30%{transform:translateY(-5px);background:#0d9488}}

/* —€—€ Input Area —€—€ */
.cw-input-area{padding:10px 12px;background:#fff;border-top:1px solid #f1f5f9;display:flex;gap:6px;align-items:center;flex-shrink:0}
.cw-voice-btn{width:38px;height:38px;border-radius:50%;background:#f1f5f9;border:1.5px solid #e2e8f0;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.25s;flex-shrink:0;font-size:14px}
.cw-voice-btn:hover{background:#e0f2fe;border-color:#0ea5e9;color:#0284c7}
.cw-voice-btn.cw-recording{background:#fef2f2;border-color:#ef4444;color:#ef4444;animation:cwPulse 1s infinite}
@keyframes cwPulse{0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,0.4)}50%{box-shadow:0 0 0 8px rgba(239,68,68,0)}}
.cw-input{flex:1;border:1.5px solid #e2e8f0;border-radius:24px;padding:10px 16px;font-size:0.84rem;outline:none;transition:all 0.2s;background:#f8fafc;color:#1e293b}
.cw-input:focus{border-color:#0d9488;background:#fff;box-shadow:0 0 0 3px rgba(13,148,136,0.08)}
.cw-input::placeholder{color:#94a3b8}
.cw-send{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#0f766e);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.25s;flex-shrink:0;font-size:14px}
.cw-send:hover{transform:scale(1.08);box-shadow:0 4px 14px rgba(13,148,136,0.3)}
.cw-send:disabled{opacity:0.4;cursor:not-allowed;transform:none}

/* —€—€ Lead Form —€—€ */
.cw-lead-form{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px;margin:0 0 0 32px}
.cw-lead-form input{width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 10px;font-size:0.8rem;margin-bottom:6px;outline:none;transition:border 0.2s;color:#1e293b}
.cw-lead-form input:focus{border-color:#0d9488}
.cw-lead-form button{width:100%;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:8px;padding:9px;font-size:0.8rem;font-weight:600;cursor:pointer;transition:all 0.2s}
.cw-lead-form button:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(16,185,129,0.3)}

/* —€—€ Conversation Chips (Action Flows) —€—€ */
.cw-chips{display:flex;flex-wrap:wrap;gap:5px;margin-top:6px;padding-left:32px}
.cw-chip{background:linear-gradient(135deg,#f0fdfa,#ccfbf1);border:1.5px solid #99f6e4;color:#0d9488;padding:6px 14px;border-radius:20px;font-size:0.72rem;font-weight:600;cursor:pointer;transition:all 0.25s ease;white-space:nowrap}
.cw-chip:hover{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;border-color:#0d9488;transform:translateY(-1px);box-shadow:0 3px 10px rgba(13,148,136,0.25)}
.cw-chip.cw-chip-action{background:linear-gradient(135deg,#dbeafe,#bfdbfe);border-color:#93c5fd;color:#1d4ed8}
.cw-chip.cw-chip-action:hover{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border-color:#2563eb}
.cw-chip.cw-chip-confirm{background:linear-gradient(135deg,#dcfce7,#bbf7d0);border-color:#86efac;color:#15803d;font-weight:700}
.cw-chip.cw-chip-confirm:hover{background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;border-color:#16a34a}
.cw-chip.cw-chip-cancel{background:#fef2f2;border-color:#fecaca;color:#dc2626}
.cw-chip.cw-chip-cancel:hover{background:#dc2626;color:#fff;border-color:#dc2626}

/* —€—€ Progress Bar —€—€ */
.cw-progress{padding:4px 12px 4px 32px;font-size:0.65rem;color:#64748b;font-weight:500;letter-spacing:0.5px}
.cw-progress-bar{height:3px;background:#e2e8f0;border-radius:3px;margin-top:4px;overflow:hidden}
.cw-progress-fill{height:100%;background:linear-gradient(90deg,#0d9488,#10b981);border-radius:3px;transition:width 0.4s ease}

/* —€—€ WhatsApp Templates Panel —€—€ */
.cw-wa-templates{padding:12px;display:flex;flex-direction:column;gap:8px}
.cw-wa-templates h5{margin:0;font-size:0.85rem;color:#1e293b;font-weight:700}
.cw-wa-tpl{display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;cursor:pointer;transition:all 0.2s;text-decoration:none;color:#1e293b}
.cw-wa-tpl:hover{background:#dcfce7;border-color:#22c55e;transform:translateY(-1px);box-shadow:0 3px 10px rgba(34,197,94,0.15);text-decoration:none;color:#1e293b}
.cw-wa-tpl i{width:36px;height:36px;border-radius:50%;background:#25D366;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.cw-wa-tpl .cw-wa-tpl-info{flex:1}
.cw-wa-tpl .cw-wa-tpl-info strong{font-size:0.8rem;display:block}
.cw-wa-tpl .cw-wa-tpl-info small{font-size:0.7rem;color:#64748b}

/* —€—€ Mobile Fullscreen —€—€ */
@media(max-width:480px){
    .cw-box{width:calc(100vw - 16px);right:-8px;max-height:calc(100dvh - 110px);border-radius:16px}
    .cw-quick{padding-left:0}
    .cw-lead-form{margin-left:0}
    .cw-wa-tpl{padding:8px 10px}
}
/* —€—€ Mobile Improvements —€—€ */
@media(max-width:600px){
    .cw-wrap{bottom:80px;right:12px}
    .cw-toggle{width:52px;height:52px;font-size:20px}
    .cw-box{width:calc(100vw - 24px);right:-12px;bottom:68px;max-height:calc(100dvh - 100px);border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,0.25)}
    .cw-header{padding:12px 14px}
    .cw-header-avatar{width:36px;height:36px;font-size:16px}
    .cw-header-info h4{font-size:0.85rem}
    .cw-messages{padding:10px;gap:6px}
    .cw-bubble{font-size:0.82rem;padding:9px 12px}
    .cw-msg{max-width:92%}
    .cw-input-area{padding:8px 10px}
    .cw-input{padding:9px 14px;font-size:0.82rem}
    .cw-send{width:38px;height:38px}
    .cw-voice-btn{width:36px;height:36px}
    .cw-chips{padding-left:0;gap:4px}
    .cw-chip{padding:5px 10px;font-size:0.7rem}
    .cw-quick-btn{padding:4px 10px;font-size:0.68rem}
    /* Safe area for notched phones */
    .cw-box{padding-bottom:env(safe-area-inset-bottom,0)}
    .cw-input-area{padding-bottom:max(8px,env(safe-area-inset-bottom,0))}
}
/* —€—€ Very small screens (< 360px) —€—€ */
@media(max-width:360px){
    .cw-box{width:calc(100vw - 16px);right:-8px;border-radius:12px}
    .cw-header-info h4{font-size:0.8rem}
    .cw-bubble{font-size:0.78rem;padding:8px 10px}
}
/* —€—€ Landscape mobile —€—€ */
@media(max-height:500px) and (orientation:landscape){
    .cw-box{max-height:calc(100vh - 80px)}
}
</style>

<div class="cw-wrap" id="cwWrap">
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
            <!-- Greeting changes based on role -->
        </div>
        <div class="cw-input-area">
            <button class="cw-voice-btn" id="cwVoiceBtn" onclick="cwToggleVoice()" title="Voice Mode (speak in Hindi/English)">
                <i class="fas fa-microphone"></i>
            </button>
            <input type="text" class="cw-input" id="cwInput" placeholder="Type your message..." autocomplete="off">
            <button class="cw-send" id="cwSendBtn" onclick="cwSend()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <!-- Toggle Buttons: WhatsApp + Call + Chatbot side by side -->
    <div class="cw-toggles">
        <a href="https://wa.me/<?= $waPhone ?>?text=<?= urlencode('Hello APS Dream Homes! I would like to know about your properties.') ?>"
           target="_blank" class="cw-toggle" id="cwWhatsAppToggle"
           onclick="cwTrackWhatsApp('main_button')"
           title="Chat on WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
        <a href="tel:<?= $callDigits ?>"
           class="cw-toggle" id="cwCallToggle"
           onclick="if(typeof ga==='function')ga('send','event','CTA','click','header_call_button');if(window.dataLayer)dataLayer.push({event:'cta_click',cta:'header_call_button',source:'chat_widget'})"
           title="Call Now">
            <i class="fas fa-phone-alt"></i>
        </a>
        <button class="cw-toggle" id="cwChatToggle" onclick="cwToggle()">
            <span class="cw-pulse"></span>
            <i class="fas fa-comment-dots"></i>
            <span class="cw-badge" id="cwBadge">1</span>
        </button>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function(){
    var cwSession = localStorage.getItem('cw_session') || ('web_' + Date.now());
    localStorage.setItem('cw_session', cwSession);
    var cwOpen = false;
    var cwMsgCount = 0;
    var cwVoiceMode = false;
    var cwRecognition = null;
    var cwUserRole = '<?= $userRole ?>';
    var cwUserName = <?= json_encode($userName) ?>;
    var cwWAPhone = '<?= $waPhone ?>';

    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    // RBAC-AWARE GREETING
    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    var cwGreetings = {
        admin: cwUserName ? ('Hello ' + cwUserName + '! Admin dashboard ready.\n\n—¢ View analytics\n—¢ Manage leads\n—¢ Commission reports\n—¢ Team performance\n\nWhat would you like to do?') : 'Hello Admin! Welcome back.\n\n—¢ View analytics\n—¢ Manage leads\n—¢ Commission reports\n—¢ Team performance\n\nChoose an option or type your question!',
        associate: cwUserName ? ('Hello ' + cwUserName + '! Ready to grow your network.\n\n—¢ Add new leads\n—¢ Post properties\n—¢ Check commissions\n—¢ View your team\n\nWhat would you like to do?') : 'Hello! Welcome to APS Dream Homes!\n\n—¢ Add new leads\n—¢ Post properties\n—¢ Check commissions\n—¢ View your team\n\nChoose an option or type your question!',
        agent: cwUserName ? ('Hello ' + cwUserName + '! Property agent dashboard.\n\n—¢ Add leads\n—¢ Post properties\n—¢ Site visits\n—¢ Search properties\n\nWhat would you like to do?') : 'Hello! Welcome to APS Dream Homes!\n\n—¢ Add leads\n—¢ Post properties\n—¢ Site visits\n—¢ Search properties\n\nChoose an option or type your question!',
        employee: cwUserName ? ('Hello ' + cwUserName + '! Employee portal ready.\n\n—¢ My tasks\n—¢ Attendance\n—¢ Add leads\n—¢ Support tickets\n\nWhat would you like to do?') : 'Hello! Welcome to APS Dream Homes!\n\n—¢ My tasks\n—¢ Attendance\n—¢ Add leads\n—¢ Support tickets\n\nChoose an option or type your question!',
        customer: cwUserName ? ('Hello ' + cwUserName + '! Your dashboard is ready.\n\n—¢ Browse properties\n—¢ Check booking status\n—¢ View EMI details\n\nWhat would you like to do?') : 'Hello! Welcome to APS Dream Homes!\n\nI will help you find the perfect property.\n\n—¢ View Plots & Prices\n—¢ Schedule a Site Visit\n—¢ Use the EMI Calculator\n\nChoose an option below or type your question!',
        guest: 'Hello! Welcome to APS Dream Homes!\n\nI will help you find the perfect property.\n\n—¢ View Plots & Prices\n—¢ Schedule a Site Visit\n—¢ Use the EMI Calculator\n\nChoose an option below or type your question!'
    };

    var cwQuickOptions = {
        admin: [
            {text:'Dashboard Stats', msg:'Show me today dashboard stats'},
            {text:'Pending Bookings', msg:'Show pending bookings'},
            {text:'New Leads', msg:'Show new leads today'},
            {text:'Add Lead', msg:'I want to add a new lead'},
            {text:'Post Property', msg:'I want to post a property'},
            {text:'Commission Report', msg:'Show commission report'}
        ],
        associate: [
            {text:'Add Lead', msg:'I want to add a new lead'},
            {text:'Post Property', msg:'I want to post a property'},
            {text:'My Network', msg:'Show my network tree'},
            {text:'My Commission', msg:'Show my commission earnings'},
            {text:'Site Visit', msg:'I want to schedule a site visit'}
        ],
        agent: [
            {text:'Add Lead', msg:'I want to add a new lead'},
            {text:'Post Property', msg:'I want to post a property'},
            {text:'Site Visit', msg:'I want to schedule a site visit'},
            {text:'Search Property', msg:'I want to search for a property'}
        ],
        employee: [
            {text:'My Tasks', msg:'Show my pending tasks'},
            {text:'Attendance', msg:'Show my attendance'},
            {text:'Add Lead', msg:'I want to add a new lead'},
            {text:'Site Visit', msg:'I want to schedule a site visit'},
            {text:'Tickets', msg:'Show open support tickets'}
        ],
        customer: [
            {text:'View Properties', msg:'Show me available properties'},
            {text:'Post Property', msg:'I want to post a property to sell'},
            {text:'Site Visit', msg:'I want to schedule a site visit'},
            {text:'My Booking', msg:'Show my booking status'},
            {text:'EMI Calculator', msg:'Show EMI options'},
            {text:'Check Prices', msg:'What are the current prices?'}
        ],
        guest: [
            {text:'View Properties', msg:'Show me available properties'},
            {text:'Site Visit', msg:'I want to schedule a site visit'},
            {text:'Check Prices', msg:'What are the current prices?'},
            {text:'Register', msg:'I want to register an account'},
            {text:'Home Loan', msg:'Need home loan help'},
            {text:'Talk to Human', msg:'__human__'}
        ]
    };

    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    // INIT GREETING
    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    function cwInitGreeting() {
        // Try to load conversation history first
        cwLoadHistory();
    }

    function cwLoadHistory() {
        fetch('<?= BASE_URL ?>/api/ai/history?session_id=' + encodeURIComponent(cwSession))
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (data.success && data.history && data.history.length > 0) {
                // Render existing messages
                data.history.forEach(function(msg) {
                    if (msg.message) cwAddMsg(msg.message, 'user');
                    if (msg.response) cwAddMsg(msg.response, 'bot');
                });
                // Still show quick options after history
                var quicks = cwQuickOptions[cwUserRole] || cwQuickOptions.guest;
                cwRenderQuickReplies(quicks);
            } else {
                // No history — show fresh greeting
                var greeting = cwGreetings[cwUserRole] || cwGreetings.guest;
                cwAddMsg(greeting, 'bot');
                var quicks = cwQuickOptions[cwUserRole] || cwQuickOptions.guest;
                cwRenderQuickReplies(quicks);
            }
        })
        .catch(function(){
            // Fallback to fresh greeting on error
            var greeting = cwGreetings[cwUserRole] || cwGreetings.guest;
            cwAddMsg(greeting, 'bot');
            var quicks = cwQuickOptions[cwUserRole] || cwQuickOptions.guest;
            cwRenderQuickReplies(quicks);
        });
    }

    function cwRenderQuickReplies(quicks) {
        var msgs = document.getElementById('cwMessages');
        var qrDiv = document.createElement('div');
        qrDiv.className = 'cw-quick';
        qrDiv.id = 'cwQuickReplies';
        quicks.forEach(function(q) {
            var btn = document.createElement('button');
            btn.className = 'cw-quick-btn';
            btn.textContent = q.text;
            btn.onclick = function() {
                if (q.msg === '__human__') { cwRequestHuman(); return; }
                document.getElementById('cwInput').value = q.msg;
                cwSend();
            };
            qrDiv.appendChild(btn);
        });
        msgs.appendChild(qrDiv);
        msgs.scrollTop = msgs.scrollHeight;
    }
    cwInitGreeting();

    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    // TOGGLE CHAT
    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    window.cwToggle = function(){
        cwOpen = !cwOpen;
        var box = document.getElementById('cwBox');
        var tog = document.getElementById('cwChatToggle');
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

    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    // VOICE MODE (STT + TTS)
    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    window.cwToggleVoice = function() {
        cwVoiceMode = !cwVoiceMode;
        var btn = document.getElementById('cwVoiceBtn');
        if (cwVoiceMode) {
            btn.classList.add('cw-recording');
            btn.innerHTML = '<i class="fas fa-stop"></i>';
            cwStartListening();
        } else {
            btn.classList.remove('cw-recording');
            btn.innerHTML = '<i class="fas fa-microphone"></i>';
            if (cwRecognition) { cwRecognition.stop(); cwRecognition = null; }
        }
    };

    function cwStartListening() {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            cwAddMsg('Voice support available in Chrome browser only.', 'bot');
            cwVoiceMode = false;
            document.getElementById('cwVoiceBtn').classList.remove('cw-recording');
            document.getElementById('cwVoiceBtn').innerHTML = '<i class="fas fa-microphone"></i>';
            return;
        }
        var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        cwRecognition = new SpeechRecognition();
        cwRecognition.lang = 'hi-IN';
        cwRecognition.interimResults = false;
        cwRecognition.maxAlternatives = 1;
        cwRecognition.onresult = function(e) {
            var transcript = e.results[0][0].transcript;
            document.getElementById('cwInput').value = transcript;
            cwSend();
        };
        cwRecognition.onend = function() {
            if (cwVoiceMode) cwStartListening();
        };
        cwRecognition.onerror = function() {
            if (cwVoiceMode) setTimeout(cwStartListening, 500);
        };
        cwRecognition.start();
    }

    function cwSpeak(text) {
        if (!('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();
        var clean = text.replace(/[\*\#\@\!\(\)]/g, '').replace(/\n+/g, '. ').substring(0, 500);
        var u = new SpeechSynthesisUtterance(clean);
        u.lang = 'hi-IN';
        u.rate = 0.95;
        u.pitch = 1;
        window.speechSynthesis.speak(u);
    }

    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    // SEND MESSAGE
    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
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

        fetch('<?= BASE_URL ?>/api/ai/chat', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: JSON.stringify({message: msg, session_id: cwSession, role: cwUserRole, user_id: window.NOTIFY_USER && window.NOTIFY_USER.id ? window.NOTIFY_USER.id : ''})
        })
        .then(function(r){ return r.json(); })
        .then(function(data){
            cwHideTyping();
            document.getElementById('cwSendBtn').disabled = false;
            var reply = (data.success && data.response) ? data.response : null;
            if (reply) {
                cwAddMsg(reply, 'bot');
                // Render conversation chips + progress from conversation_state
                var cs = data.conversation_state;
                if (cs) {
                    // Render progress bar
                    if (cs.step !== null && cs.step !== 'done' && cs.step !== 'confirm' && cs.step_count > 0) {
                        cwRenderProgress(cs.step, cs.step_count);
                    }
                    // Render suggestion chips
                    if (cs.suggestions && cs.suggestions.length > 0) {
                        cwRenderChips(cs.suggestions, cs.step, cs.action);
                    }
                }
                if (cwVoiceMode) cwSpeak(reply);
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

    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    // KEYBOARD SUPPORT
    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    document.getElementById('cwInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') cwSend();
    });

    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    // HUMAN REQUEST + LEAD FORM
    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    window.cwRequestHuman = function(){
        cwAddMsg('I want to talk to a human agent', 'user');
        cwShowTyping();
        setTimeout(function(){
            cwHideTyping();
            cwAddMsg('Thank you! Our team has received your details. We will contact you shortly.\n\nOr chat directly on WhatsApp: https://wa.me/<?= $waPhone ?>', 'bot');
            var c = document.getElementById('cwMessages');
            var f = document.createElement('div');
            f.className = 'cw-lead-form';
            f.innerHTML = '<input type="text" id="cwLeadName" placeholder="Your Name" value="' + (cwUserName||'') + '"><input type="tel" id="cwLeadPhone" placeholder="Mobile Number"><button onclick="cwSubmitLead()">Request Callback</button>';
            c.appendChild(f);
            c.scrollTop = c.scrollHeight;
        }, 700);
    };

    window.cwSubmitLead = function(){
        var name = (document.getElementById('cwLeadName')||{}).value||'';
        var phone = (document.getElementById('cwLeadPhone')||{}).value||'';
        if(!name||!phone){alert('Please enter Name and Phone');return;}
        document.getElementById('cwLeadName').disabled = true;
        document.getElementById('cwLeadPhone').disabled = true;
        cwAddMsg('Name: '+name+', Phone: '+phone, 'user');
        cwShowTyping();
        var fd = new URLSearchParams();
        fd.append('name',name);fd.append('phone',phone);fd.append('email','chatbot_lead@apsdreamhome.com');
        fd.append('subject','general');fd.append('message','Callback requested via AI Chatbot');
        fd.append('csrf_token','<?= $_SESSION['csrf_token'] ?? '' ?>');
        fetch('<?= BASE_URL ?>/contact',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:fd.toString()})
        .then(function(){cwHideTyping();cwAddMsg('Thank you '+name+'! Your details have been sent to our team. We will contact you soon.','bot');})
        .catch(function(){cwHideTyping();cwAddMsg('Details saved! We will contact you soon.','bot');});
    };

    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    // WHATSAPP TRACKING
    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    window.cwTrackWhatsApp = function(source) {
        try {
            fetch('<?= BASE_URL ?>/api/track/whatsapp-click', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({source: source, page: window.location.href, ts: Date.now()})
            }).catch(function(){});
        } catch (e) { console.error("Error:", e); }
    };

    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    // WHATSAPP TEMPLATES PANEL
    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    window.cwShowWATemplates = function() {
        var msgs = document.getElementById('cwMessages');
        var qr = document.getElementById('cwQuickReplies');
        if(qr) qr.remove();

        var panel = document.createElement('div');
        panel.className = 'cw-wa-templates';
        panel.innerHTML = '<h5><i class="fab fa-whatsapp style-37777"></i> WhatsApp Templates</h5>';

        var templates = [
            {icon:'fa-home', title:'Property Inquiry', msg:'Hello! I would like to know about available plots. Are there any available?', key:'property'},
            {icon:'fa-tag', title:'Price Check', msg:'Hi! What are the current rates at APS Dream Homes?', key:'price'},
            {icon:'fa-calendar', title:'Site Visit', msg:'Hello! I would like to schedule a site visit. When can I come?', key:'visit'},
            {icon:'fa-credit-card', title:'EMI Details', msg:'Hi! What are the EMI options? How much would the monthly payment be?', key:'emi'},
            {icon:'fa-handshake', title:'Booking', msg:'Hello! I would like to book a plot. What is the process?', key:'booking'},
        ];

        templates.forEach(function(t) {
            var link = document.createElement('a');
            link.className = 'cw-wa-tpl';
            link.target = '_blank';
            link.href = 'https://wa.me/' + cwWAPhone + '?text=' + encodeURIComponent(t.msg);
            link.onclick = function() { cwTrackWhatsApp('template_' + t.key); };
            link.innerHTML = '<i class="fas ' + t.icon + '"></i><div class="cw-wa-tpl-info"><strong>' + t.title + '</strong><small>Click to open WhatsApp</small></div><i class="fas fa-arrow-right style-79086"></i>';
            panel.appendChild(link);
        });

        msgs.appendChild(panel);
        msgs.scrollTop = msgs.scrollHeight;
    };

    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    // FALLBACK RESPONSES (when API fails)
    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    function cwFallback(userMsg){
        setTimeout(function(){
            cwHideTyping();
            var m = userMsg.toLowerCase();
            var r = 'Your requirement has been noted. For better assistance, call us at: <?= $cPhone ?> or WhatsApp us!';
            if(m.indexOf('hi')!==-1||m.indexOf('hello')!==-1||m.indexOf('namaste')!==-1) r='Hello! How can I assist you with APS Dream Homes?';
            else if(m.indexOf('price')!==-1||m.indexOf('rate')!==-1||m.indexOf('kitne')!==-1) r='Our premium plots start from Rs 5.5 Lakh. What is your budget?';
            else if(m.indexOf('location')!==-1||m.indexOf('kaha')!==-1) r='Our projects are available in Gorakhpur. Which area are you looking for?';
            else if(m.indexOf('loan')!==-1||m.indexOf('emi')!==-1) r='Yes, we offer home loan and EMI facilities. Would you like to see EMI details?';
            else if(m.indexOf('visit')!==-1||m.indexOf('site')!==-1) r='Site visits are completely free! Call us or WhatsApp at: <?= $cPhone ?>';
            else if(m.indexOf('buy')!==-1||m.indexOf('plot')!==-1) r='Great! We have residential plots in Gorakhpur. Tell us your budget and I will show you the best options!';
            else if(m.indexOf('whatsapp')!==-1) { cwShowWATemplates(); return; }
            cwAddMsg(r,'bot');
            if (cwVoiceMode) cwSpeak(r);
        },700);
    }

    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    // CONVERSATION CHIPS + PROGRESS (Action Flows)
    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    function cwRenderChips(suggestions, step, action) {
        var c = document.getElementById('cwMessages');
        // Remove old chips
        var old = c.querySelectorAll('.cw-chips');
        old.forEach(function(el){ el.remove(); });

        var div = document.createElement('div');
        div.className = 'cw-chips';

        suggestions.forEach(function(s) {
            var btn = document.createElement('button');
            btn.className = 'cw-chip';
            // Style based on chip type
            if (s.indexOf('âœ…') !== -1 || s.indexOf('Confirm') !== -1) btn.className += ' cw-chip-confirm';
            else if (s.indexOf('â�Œ') !== -1 || s.indexOf('Cancel') !== -1) btn.className += ' cw-chip-cancel';
            else if (s.indexOf('âœ�ï¸�') !== -1 || s.indexOf('Edit') !== -1) btn.className += ' cw-chip-action';
            btn.textContent = s;
            btn.onclick = function() {
                document.getElementById('cwInput').value = s;
                cwSend();
            };
            div.appendChild(btn);
        });

        c.appendChild(div);
        c.scrollTop = c.scrollHeight;
    }

    function cwRenderProgress(currentStep, totalSteps) {
        var c = document.getElementById('cwMessages');
        // Remove old progress
        var old = c.querySelectorAll('.cw-progress');
        old.forEach(function(el){ el.remove(); });

        if (totalSteps <= 1) return;

        var pct = Math.round((currentStep / totalSteps) * 100);
        var div = document.createElement('div');
        div.className = 'cw-progress';
        div.innerHTML = 'Step ' + currentStep + ' of ' + totalSteps +
            '<div class="cw-progress-bar"><div class="cw-progress-fill style-74257"></div></div>';

        c.appendChild(div);
        c.scrollTop = c.scrollHeight;
    }

    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    // HELPER FUNCTIONS
    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    function cwLinkify(t){
        return t.replace(/(https?:\/\/[^\s<]+)/g,'<a href="$1" target="_blank" rel="noopener" class="style-65078">$1</a>');
    }
    function cwAddMsg(text, type, msgId){
        var c = document.getElementById('cwMessages');
        var d = document.createElement('div');
        d.className = 'cw-msg cw-' + type;
        var av = type==='bot' ? '<div class="cw-msg-avatar"><i class="fas fa-robot"></i></div>' : '<div class="cw-msg-avatar"><i class="fas fa-user"></i></div>';
        var fb = '';
        if (type === 'bot') {
            var uid = 'fb_' + Math.random().toString(36).substr(2,6);
            fb = '<div class="cw-feedback" id="' + uid + '">'
                + '<button class="cw-fb-btn" title="Helpful" onclick="cwFeedback(this,1)"><i class="fas fa-thumbs-up"></i></button>'
                + '<button class="cw-fb-btn cw-fb-down" title="Not helpful" onclick="cwFeedback(this,0)"><i class="fas fa-thumbs-down"></i></button>'
                + '</div>';
        }
        d.innerHTML = av + '<div class="cw-bubble">' + cwLinkify(text.replace(/\n/g,'<br>')) + '</div>' + fb;
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
        d.innerHTML = '<div class="cw-msg-avatar style-81721"><i class="fas fa-robot"></i></div><div class="cw-typing-bubble"><span class="cw-typing-dot"></span><span class="cw-typing-dot"></span><span class="cw-typing-dot"></span></div>';
        c.appendChild(d);
        c.scrollTop = c.scrollHeight;
    }

    function cwHideTyping(){
        var t = document.getElementById('cwTyping');
        if(t) t.remove();
    }

    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    // FEEDBACK (Thumbs Up/Down)
    // •�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�•�
    window.cwFeedback = function(btn, positive) {
        var container = btn.closest('.cw-feedback');
        if (!container) return;
        // Mark active
        var buttons = container.querySelectorAll('.cw-fb-btn');
        buttons.forEach(function(b){ b.classList.remove('cw-fb-active'); });
        btn.classList.add('cw-fb-active');
        // Send feedback
        try {
            fetch('<?= BASE_URL ?>/api/ai/feedback', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({session_id: cwSession, positive: positive === 1})
            }).catch(function(){});
        } catch (e) { console.error("Error:", e); }
    };
})();
</script>
