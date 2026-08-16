<?php
/**
 * Voice Booking Widget
 * Public-facing AI voice assistant: customer speaks (Hindi/English) via the
 * Web Speech API, the transcript is sent to /api/v2/mobile/voice-chat, and the
 * AI reply (plus a CRM lead + site-visit) comes back. Hindi TTS via SpeechSynthesis.
 */
if (!defined('BASE_URL')) { define('BASE_URL', ''); }
?>
<div id="voiceBookingWidget" class="vbw" aria-live="polite">
    <button id="vbwToggle" class="vbw-toggle" type="button" aria-label="Voice booking assistant" title="बोलकर बुक करें / Book via voice">
        <i class="fas fa-microphone"></i>
    </button>

    <div id="vbwPanel" class="vbw-panel" hidden>
        <div class="vbw-header">
            <div class="vbw-title">
                <i class="fas fa-assistant"></i>
                <span>AI Voice Booking</span>
            </div>
            <button id="vbwClose" class="vbw-close" type="button" aria-label="Close">&times;</button>
        </div>

        <div id="vbwTranscript" class="vbw-transcript">
            <div class="vbw-msg vbw-bot">
                नमस्ते! मैं आपकी आवाज़ से प्लॉट बुकिंग और साइट विज़िट में मदद कर सकती हूँ।
                माइक दबाएँ और बोलें — जैसे "मुझे प्लॉट बुक करना है"।
            </div>
        </div>

        <div id="vbwStatus" class="vbw-status">माइक दबाकर बोलें</div>

        <div class="vbw-controls">
            <button id="vbwMic" class="vbw-mic" type="button" aria-label="Start speaking">
                <i class="fas fa-microphone"></i>
            </button>
            <button id="vbwSpeak" class="vbw-speak-btn" type="button" title="Read reply aloud" disabled>
                <i class="fas fa-volume-up"></i> बोलें
            </button>
        </div>

        <div id="vbwFallback" class="vbw-fallback" hidden>
            <p>आपका ब्राउज़र voice input support नहीं करता। नीचे लिखकर भी बुक कर सकते हैं:</p>
            <textarea id="vbwText" rows="2" placeholder="अपना संदेश लिखें... (जैसे: मुझे प्लॉट बुक करना है, मेरा नंबर 98xxxxxxxx है)"></textarea>
            <button id="vbwSend" class="vbw-send" type="button">भेजें</button>
        </div>
    </div>
</div>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.vbw { position: fixed; left: 18px; bottom: 18px; z-index: 99990; font-family: 'Inter', system-ui, sans-serif; }
.vbw-toggle {
    width: 60px; height: 60px; border-radius: 50%; border: none; cursor: pointer;
    background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff; font-size: 1.4rem;
    box-shadow: 0 8px 24px rgba(13,148,136,0.45); transition: transform .2s, box-shadow .2s;
    animation: vbwPulse 2.4s infinite;
}
.vbw-toggle:hover { transform: scale(1.08); }
@keyframes vbwPulse {
    0%,100% { box-shadow: 0 8px 24px rgba(13,148,136,0.45); }
    50% { box-shadow: 0 8px 34px rgba(13,148,136,0.75); }
}
    .vbw-panel {
        position: absolute; bottom: 76px; left: 0; width: 330px; max-width: 90vw;
        background: #fff; border-radius: 18px; box-shadow: 0 16px 50px rgba(0,0,0,0.22);
        overflow: hidden; display: flex; flex-direction: column; max-height: 78vh;
    }
    .vbw-panel[hidden] {
        display: none !important;
    }
.vbw-header {
    display: flex; align-items: center; justify-content: space-between;
    background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff; padding: 12px 16px;
}
.vbw-title { display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.95rem; }
.vbw-close { background: rgba(0,0,0,0.3); border: none; color: #fff; width: 28px; height: 28px;
    border-radius: 8px; font-size: 1.1rem; cursor: pointer; line-height: 1; }
.vbw-transcript { flex: 1; overflow-y: auto; padding: 14px; display: flex; flex-direction: column; gap: 10px; background: #f8fafc; }
.vbw-msg { max-width: 85%; padding: 9px 13px; border-radius: 14px; font-size: 0.86rem; line-height: 1.45; white-space: pre-wrap; }
.vbw-user { align-self: flex-end; background: #0d9488; color: #fff; border-bottom-right-radius: 4px; }
.vbw-bot { align-self: flex-start; background: #fff; color: #1e293b; border: 1px solid #e2e8f0; border-bottom-left-radius: 4px; }
.vbw-bot.vbw-success { background: #ecfdf5; border-color: #6ee7b7; color: #065f46; font-weight: 600; }
.vbw-status { text-align: center; font-size: 0.78rem; color: #475569; padding: 6px; background: #f1f5f9; }
.vbw-status.listening { color: #dc2626; font-weight: 600; }
.vbw-controls { display: flex; align-items: center; justify-content: center; gap: 14px; padding: 12px; background: #fff; border-top: 1px solid #eef2f7; }
.vbw-mic {
    width: 56px; height: 56px; border-radius: 50%; border: none; cursor: pointer; color: #fff; font-size: 1.3rem;
    background: linear-gradient(135deg, #0d9488, #0f766e); box-shadow: 0 4px 14px rgba(13,148,136,0.4); transition: transform .15s;
}
.vbw-mic:hover { transform: scale(1.05); }
.vbw-mic.recording { background: linear-gradient(135deg, #dc2626, #b91c1c); animation: vbwRec 1s infinite; }
@keyframes vbwRec { 0%,100% { transform: scale(1); } 50% { transform: scale(1.12); } }
.vbw-speak-btn {
    border: 1px solid #0d9488; background: #0d9488; color: #fff; border-radius: 20px; padding: 8px 14px;
    font-size: 0.8rem; font-weight: 600; cursor: pointer;
}
.vbw-speak-btn:disabled { opacity: .85; cursor: not-allowed; background: #0f766e; }
.vbw-fallback { padding: 12px 14px; border-top: 1px solid #eef2f7; font-size: 0.8rem; color: #475569; }
.vbw-fallback textarea { width: 100%; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 8px; font-size: 0.84rem; resize: vertical; font-family: inherit; }
.vbw-send { margin-top: 8px; width: 100%; border: none; background: #0d9488; color: #fff; border-radius: 10px; padding: 9px; font-weight: 600; cursor: pointer; }
.vbw-send:focus { outline: 2px solid #fff; outline-offset: 2px; }
</style>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function () {
    var BASE = window.BASE_URL || '';
    var endpoint = BASE + '/api/v2/mobile/voice-chat';

    var toggle = document.getElementById('vbwToggle');
    var panel = document.getElementById('vbwPanel');
    var closeBtn = document.getElementById('vbwClose');
    var micBtn = document.getElementById('vbwMic');
    var transcript = document.getElementById('vbwTranscript');
    var statusEl = document.getElementById('vbwStatus');
    var speakBtn = document.getElementById('vbwSpeak');
    var fallback = document.getElementById('vbwFallback');
    var textArea = document.getElementById('vbwText');
    var sendBtn = document.getElementById('vbwSend');

    var sessionId = 0;
    var lastReply = '';
    var recognition = null;
    var listening = false;

    function addMsg(text, who, extraClass) {
        var div = document.createElement('div');
        div.className = 'vbw-msg vbw-' + who + (extraClass ? ' ' + extraClass : '');
        div.textContent = text;
        transcript.appendChild(div);
        transcript.scrollTop = transcript.scrollHeight;
        return div;
    }

    function setStatus(text, cls) {
        statusEl.textContent = text;
        statusEl.className = 'vbw-status' + (cls ? ' ' + cls : '');
    }

    function speak(text) {
        if (!('speechSynthesis' in window)) return;
        try {
            window.speechSynthesis.cancel();
            var u = new SpeechSynthesisUtterance(text);
            u.lang = 'hi-IN';
            u.rate = 0.95;
            var voices = window.speechSynthesis.getVoices();
            var hi = voices.filter(function (v) { return /hi/i.test(v.lang); })[0];
            if (hi) u.voice = hi;
            window.speechSynthesis.speak(u);
        } catch (e) {}
    }

    function sendToBot(message) {
        if (!message) return;
        addMsg(message, 'user');
        setStatus('सोच रही हूँ...');
        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: message, session_id: sessionId, lead_id: 0 })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var reply = (data.reply || 'माफ़ कीजिए, कुछ समझ नहीं आया।').replace(/\\u([0-9a-fA-F]{4})/g, function (_, g) {
                return String.fromCharCode(parseInt(g, 16));
            });
            lastReply = reply;
            if (data.session_id) sessionId = data.session_id;
            var isBooking = (data.intent === 'booking' || data.intent === 'site_visit');
            addMsg(reply, 'bot', isBooking ? 'vbw-success' : '');
            setStatus(isBooking ? '✅ आपका रिक्वेस्ट रजिस्टर हो गया!' : 'माइक दबाकर बोलें');
            speakBtn.disabled = false;
            speak(reply);
        })
        .catch(function () {
            addMsg('तकनीकी समस्या है। कृपया थोड़ी देर बाद कोशिश करें।', 'bot');
            setStatus('माइक दबाकर बोलें');
        });
    }

    // ---- Web Speech API (SpeechRecognition) ----
    var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) {
        // No speech recognition -> reveal text fallback
        fallback.hidden = false;
        setStatus('टाइप करके बुक करें');
    } else {
        recognition = new SR();
        recognition.lang = 'hi-IN';
        recognition.interimResults = true;
        recognition.continuous = false;

        recognition.onstart = function () {
            listening = true;
            micBtn.classList.add('recording');
            setStatus('सुन रही हूँ... बोलें', 'listening');
        };

        recognition.onresult = function (e) {
            var interim = '';
            var finalText = '';
            for (var i = e.resultIndex; i < e.results.length; i++) {
                var chunk = e.results[i][0].transcript;
                if (e.results[i].isFinal) finalText += chunk; else interim += chunk;
            }
            if (interim) setStatus('"' + interim + '"');
            if (finalText) {
                setStatus('प्रोसेसिंग...');
                sendToBot(finalText.trim());
            }
        };

        recognition.onerror = function (e) {
            if (e.error === 'not-allowed') setStatus('माइक की अनुमति दें');
            else if (e.error === 'no-speech') setStatus('माइक दबाकर बोलें');
            else setStatus('फिर कोशिश करें');
        };

        recognition.onend = function () {
            listening = false;
            micBtn.classList.remove('recording');
            if (statusEl.className.indexOf('listening') === -1) setStatus('माइक दबाकर बोलें');
        };
    }

    micBtn.addEventListener('click', function () {
        if (!recognition) return;
        if (listening) { recognition.stop(); }
        else {
            try { recognition.start(); }
            catch (e) { /* already started */ }
        }
    });

    speakBtn.addEventListener('click', function () { if (lastReply) speak(lastReply); });

    sendBtn.addEventListener('click', function () {
        var t = (textArea.value || '').trim();
        if (t) { sendToBot(t); textArea.value = ''; }
    });

    toggle.addEventListener('click', function () {
        panel.hidden = !panel.hidden;
        if (!panel.hidden && SR && 'speechSynthesis' in window) {
            // prime voices
            window.speechSynthesis.getVoices();
        }
    });
    closeBtn.addEventListener('click', function () { panel.hidden = true; });
})();
</script>
