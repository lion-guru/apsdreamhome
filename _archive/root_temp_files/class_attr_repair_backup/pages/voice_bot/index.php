<?php
/**
 * APS Dream Homes — Browser Voice Bot
 * Option A: Customer opens link â†’ speaks in Hindi â†’ AI responds
 * 100% free: Web Speech API (STT+TTS) + Groq/Llama (LLM)
 */
$customer_name = $customer_name ?? '';
$customer_phone = $customer_phone ?? '';
$language = $language ?? 'hi';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>APS Dream Homes — Voice Assistant</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #05080e 0%, #0f172a 50%, #0f2027 100%);
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .bot-container {
            width: 100%;
            max-width: 420px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .bot-header {
            padding: 20px;
            text-align: center;
            background: linear-gradient(180deg, rgba(13,148,136,0.15) 0%, transparent 100%);
            border-bottom: 1px solid rgba(13,148,136,0.2);
        }
        .bot-header .logo {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #0d9488, #0f766e);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 10px;
            box-shadow: 0 4px 20px rgba(13,148,136,0.4);
            animation: pulse-glow 2s ease-in-out infinite;
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 4px 20px rgba(13,148,136,0.4); }
            50% { box-shadow: 0 4px 30px rgba(13,148,136,0.7); }
        }
        .bot-header .logo i { font-size: 1.5rem; color: #fff; }
        .bot-header h2 { font-size: 1.1rem; font-weight: 600; color: #5eead4; margin-bottom: 4px; }
        .bot-header p { font-size: 0.75rem; color: #64748b; }
        .greeting-badge {
            display: inline-block;
            background: rgba(13,148,136,0.15);
            border: 1px solid rgba(13,148,136,0.3);
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 0.75rem;
            color: #5eead4;
            margin-top: 8px;
        }

        .chat-area {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .msg {
            max-width: 85%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 0.9rem;
            line-height: 1.5;
            animation: fadeInUp 0.3s ease;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .msg.bot {
            align-self: flex-start;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(13,148,136,0.2);
            border-bottom-left-radius: 4px;
            color: #e2e8f0;
        }
        .msg.user {
            align-self: flex-end;
            background: linear-gradient(135deg, #0d9488, #0f766e);
            color: #fff;
            border-bottom-right-radius: 4px;
        }
        .msg .typing-dots span {
            display: inline-block;
            width: 6px; height: 6px;
            background: #5eead4;
            border-radius: 50%;
            margin: 0 2px;
            animation: dotBounce 1.2s ease-in-out infinite;
        }
        .msg .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .msg .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes dotBounce {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-6px); }
        }

        .voice-area {
            padding: 20px;
            text-align: center;
            background: linear-gradient(0deg, rgba(13,148,136,0.1) 0%, transparent 100%);
            border-top: 1px solid rgba(13,148,136,0.2);
        }
        .voice-btn {
            width: 80px; height: 80px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, #0d9488, #0f766e);
            color: #fff;
            font-size: 2rem;
            cursor: pointer;
            box-shadow: 0 4px 25px rgba(13,148,136,0.5);
            transition: all 0.3s;
            position: relative;
        }
        .voice-btn:hover { transform: scale(1.05); }
        .voice-btn.listening {
            animation: pulse-listen 1s ease-in-out infinite;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 4px 25px rgba(239,68,68,0.5);
        }
        @keyframes pulse-listen {
            0%, 100% { transform: scale(1); box-shadow: 0 4px 25px rgba(239,68,68,0.5); }
            50% { transform: scale(1.08); box-shadow: 0 4px 40px rgba(239,68,68,0.7); }
        }
        .voice-btn.speaking {
            animation: pulse-speak 0.8s ease-in-out infinite;
        }
        @keyframes pulse-speak {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .voice-label {
            margin-top: 10px;
            font-size: 0.8rem;
            color: #64748b;
        }
        .voice-label.active { color: #5eead4; }
        .voice-label.error { color: #ef4444; }

        .quick-replies {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 12px;
        }
        .quick-reply {
            background: rgba(13,148,136,0.15);
            border: 1px solid rgba(13,148,136,0.3);
            color: #5eead4;
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Poppins', sans-serif;
        }
        .quick-reply:hover {
            background: rgba(13,148,136,0.3);
            transform: translateY(-1px);
        }

        .text-input-row {
            display: flex;
            gap: 8px;
            margin-top: 10px;
            padding: 0 4px;
        }
        .text-input-row input {
            flex: 1;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(13,148,136,0.3);
            border-radius: 20px;
            padding: 8px 16px;
            color: #e2e8f0;
            font-size: 0.85rem;
            outline: none;
            font-family: 'Poppins', sans-serif;
        }
        .text-input-row input::placeholder { color: #475569; }
        .text-input-row button {
            width: 36px; height: 36px;
            border-radius: 50%;
            border: none;
            background: #0d9488;
            color: #fff;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }

        .wave-bars {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            height: 30px;
            margin-bottom: 10px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .wave-bars.active { opacity: 1; }
        .wave-bars .bar {
            width: 3px;
            background: #0d9488;
            border-radius: 2px;
            animation: wave 0.8s ease-in-out infinite;
        }
        .wave-bars .bar:nth-child(1) { animation-delay: 0s; height: 10px; }
        .wave-bars .bar:nth-child(2) { animation-delay: 0.1s; height: 16px; }
        .wave-bars .bar:nth-child(3) { animation-delay: 0.2s; height: 22px; }
        .wave-bars .bar:nth-child(4) { animation-delay: 0.3s; height: 14px; }
        .wave-bars .bar:nth-child(5) { animation-delay: 0.4s; height: 18px; }
        .wave-bars.listening .bar { background: #ef4444; }
        .wave-bars.speaking .bar { background: #0d9488; animation-duration: 0.4s; }
        @keyframes wave {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(2.5); }
        }

        .end-call-btn {
            margin-top: 12px;
            background: transparent;
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
            border-radius: 8px;
            padding: 6px 16px;
            font-size: 0.75rem;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }
        .end-call-btn:hover {
            background: rgba(239,68,68,0.15);
            border-color: #ef4444;
        }
    </style>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
<div class="bot-container">
    <div class="bot-header">
        <div class="logo"><i class="fas fa-home"></i></div>
        <h2>APS Dream Homes</h2>
        <p>AI Voice Assistant</p>
        <?php if ($customer_name): ?>
            <div class="greeting-badge">Namaste, <?= htmlspecialchars($customer_name ?? '') ?>!</div>
        <?php endif; ?>
    </div>

    <div class="chat-area" id="chatArea">
        <div class="msg bot" id="welcomeMsg">
            <?php if ($customer_name): ?>
                Namaste <?= htmlspecialchars($customer_name ?? '') ?>! Main APS Dream Homes ka AI assistant hoon. Aap mujhse property, booking, EMI, ya kisi bhi cheez ke baare mein baat kar sakte hain. Bolna shuru karein ya niche type karein!
            <?php else: ?>
                Namaste! Main APS Dream Homes ka AI assistant hoon. Aap mujhse property, booking, EMI, ya kisi bhi cheez ke baare mein baat kar sakte hain. Bolna shuru karein ya niche type karein!
            <?php endif; ?>
        </div>
    </div>

    <div class="voice-area">
        <div class="wave-bars" id="waveBars">
            <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
        </div>
        <button class="voice-btn" id="voiceBtn" title="Bolna shuru karein">
            <i class="fas fa-microphone"></i>
        </button>
        <div class="voice-label" id="voiceLabel">Tap to speak</div>

        <div class="quick-replies" id="quickReplies">
            <button class="quick-reply" onclick="sendText('Property dekhna hai')">Property dekhna hai</button>
            <button class="quick-reply" onclick="sendText('Price kya hai?')">Price kya hai?</button>
            <button class="quick-reply" onclick="sendText('Booking kaise karein?')">Booking kaise karein?</button>
            <button class="quick-reply" onclick="sendText('EMI options?')">EMI options?</button>
        </div>

        <div class="text-input-row">
            <input type="text" id="textInput" placeholder="Type karein ya bolein..." onkeypress="if(event.key==='Enter')sendFromInput()">
            <button onclick="sendFromInput()"><i class="fas fa-paper-plane" class="style-64777"></i></button>
        </div>

        <button class="end-call-btn" onclick="endSession()">End Session</button>
    </div>
</div>

<script>
const SESSION = 'vb_' + Date.now();
const LANG = '<?= $language ?>';
const CUSTOMER_NAME = '<?= addslashes($customer_name) ?>';
const CUSTOMER_PHONE = '<?= addslashes($customer_phone) ?>';
const BASE_URL = '<?= defined('BASE_URL') ? BASE_URL : '' ?>';

const chatArea = document.getElementById('chatArea');
const voiceBtn = document.getElementById('voiceBtn');
const voiceLabel = document.getElementById('voiceLabel');
const waveBars = document.getElementById('waveBars');
const textInput = document.getElementById('textInput');

let recognition = null;
let isListening = false;
let isSpeaking = false;

// === Web Speech API STT ===
function initRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        voiceLabel.textContent = 'Speech not supported — type instead';
        voiceLabel.className = 'voice-label error';
        return null;
    }
    const r = new SpeechRecognition();
    r.lang = LANG === 'hi' ? 'hi-IN' : 'en-IN';
    r.interimResults = false;
    r.maxAlternatives = 1;
    r.continuous = false;

    r.onstart = () => {
        isListening = true;
        voiceBtn.classList.add('listening');
        voiceBtn.innerHTML = '<i class="fas fa-stop"></i>';
        voiceLabel.textContent = 'Listening...';
        voiceLabel.className = 'voice-label active';
        waveBars.className = 'wave-bars active listening';
    };

    r.onresult = (e) => {
        const transcript = e.results[0][0].transcript;
        addUserMsg(transcript);
        sendToAI(transcript);
    };

    r.onerror = (e) => {
        console.error('STT error:', e.error);
        if (e.error !== 'no-speech') {
            voiceLabel.textContent = 'Error: ' + e.error;
            voiceLabel.className = 'voice-label error';
        }
        resetVoiceBtn();
    };

    r.onend = () => {
        isListening = false;
        resetVoiceBtn();
    };

    return r;
}

voiceBtn.addEventListener('click', () => {
    if (isSpeaking) {
        window.speechSynthesis.cancel();
        isSpeaking = false;
        resetVoiceBtn();
        return;
    }
    if (isListening && recognition) {
        recognition.stop();
        return;
    }
    if (!recognition) recognition = initRecognition();
    if (recognition) recognition.start();
});

function resetVoiceBtn() {
    voiceBtn.classList.remove('listening', 'speaking');
    voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
    voiceLabel.textContent = 'Tap to speak';
    voiceLabel.className = 'voice-label';
    waveBars.className = 'wave-bars';
}

// === Send to AI Backend ===
async function sendToAI(text) {
    showTyping();
    try {
        const resp = await fetch(BASE_URL + '/api/voice-bot/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: text,
                session: SESSION,
                lang: LANG,
                name: CUSTOMER_NAME,
                phone: CUSTOMER_PHONE,
            })
        });
        const data = await resp.json();
        removeTyping();
        if (data.reply) {
            addBotMsg(data.reply);
            speakText(data.reply);
        } else {
            addBotMsg('Maafi chahta hoon, kuch gadbad ho gayi. Dobara koshish karein.');
        }
    } catch (err) {
        removeTyping();
        addBotMsg('Connection problem hai. Internet check karein aur dobara koshish karein.');
    }
}

function sendText(text) {
    addUserMsg(text);
    sendToAI(text);
}

function sendFromInput() {
    const val = textInput.value.trim();
    if (!val) return;
    textInput.value = '';
    sendText(val);
}

// === TTS ===
function speakText(text) {
    if (!window.speechSynthesis) return;
    window.speechSynthesis.cancel();

    const clean = text
        .replace(/[\*#_`]/g, ' ')
        .replace(/https?:\/\/\S+/g, ' ')
        .replace(/[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{1F1E0}-\u{1F1FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}]/gu, ' ')
        .replace(/\n+/g, '. ')
        .replace(/\s+/g, ' ')
        .trim();
    const utter = new SpeechSynthesisUtterance(clean);
    utter.lang = LANG === 'hi' ? 'hi-IN' : 'en-IN';
    utter.rate = 0.95;
    utter.pitch = 1.0;

    const voices = window.speechSynthesis.getVoices();
    const hindiVoice = voices.find(v => v.lang.startsWith('hi')) || voices.find(v => v.lang.startsWith('en'));
    if (hindiVoice) utter.voice = hindiVoice;

    utter.onstart = () => {
        isSpeaking = true;
        voiceBtn.classList.add('speaking');
        voiceLabel.textContent = 'Speaking...';
        voiceLabel.className = 'voice-label active';
        waveBars.className = 'wave-bars active speaking';
    };
    utter.onend = () => {
        isSpeaking = false;
        resetVoiceBtn();
    };
    utter.onerror = () => { isSpeaking = false; resetVoiceBtn(); };

    window.speechSynthesis.speak(utter);
}

// === Chat UI ===
function addUserMsg(text) {
    const div = document.createElement('div');
    div.className = 'msg user';
    div.textContent = text;
    chatArea.appendChild(div);
    chatArea.scrollTop = chatArea.scrollHeight;
}

function addBotMsg(text) {
    const div = document.createElement('div');
    div.className = 'msg bot';
    div.innerHTML = text.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    chatArea.appendChild(div);
    chatArea.scrollTop = chatArea.scrollHeight;
}

function showTyping() {
    const div = document.createElement('div');
    div.className = 'msg bot';
    div.id = 'typingIndicator';
    div.innerHTML = '<div class="typing-dots"><span></span><span></span><span></span></div>';
    chatArea.appendChild(div);
    chatArea.scrollTop = chatArea.scrollHeight;
}

function removeTyping() {
    const el = document.getElementById('typingIndicator');
    if (el) el.remove();
}

function endSession() {
    addBotMsg('Dhanyavaad! Aapka din shubh ho. Phir milenge! ðŸ™�');
    if (window.speechSynthesis) {
        const utter = new SpeechSynthesisUtterance('Dhanyavaad! Aapka din shubh ho.');
        utter.lang = 'hi-IN';
        window.speechSynthesis.speak(utter);
    }
    voiceBtn.disabled = true;
    document.querySelectorAll('.quick-reply, .text-input-row button, .text-input-row input').forEach(el => el.disabled = true);
}

// Preload voices
window.speechSynthesis?.getVoices();
window.speechSynthesis?.addEventListener('voiceschanged', () => {});
</script>
</body>
</html>
