
function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  if (meta) return meta.getAttribute('content');
  const m = document.cookie.match(/(^|; )csrf_token=([^;]*)/);
  return m ? decodeURIComponent(m[2]) : '';
}

/**
 * APS Dream Home - Voice Assistant Widget
 * Uses Web Speech API for STT and TTS, interfacing with /api/v2/mobile/voice-chat
 */
(function () {
  'use strict';

  // Check browser support
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (!SpeechRecognition) {
    console.warn('Web Speech API is not supported in this browser.');
    return;
  }

  let recognition = new SpeechRecognition();
  recognition.lang = 'hi-IN'; // Default to Hindi, can be dynamic
  recognition.interimResults = true;
  recognition.maxAlternatives = 1;

  let isListening = false;
  let sessionId = 0;
  const synth = window.speechSynthesis;
  let currentUtterance = null;

  // DOM Elements
  let widget, btn, dialog, statusText, transcriptEl, replyEl, closeBtn;

  function init() {
    // Inject HTML
    const html = `
            <div class="aps-voice-widget" id="apsVoiceWidget">
                <div class="aps-voice-dialog" id="apsVoiceDialog">
                    <i class="fas fa-times aps-voice-close" id="apsVoiceClose"></i>
                    <div class="aps-voice-status">
                        <div class="aps-voice-status-dot"></div>
                        <span id="apsVoiceStatusText">Ready</span>
                        <div class="sound-waves">
                            <div class="sound-wave"></div>
                            <div class="sound-wave"></div>
                            <div class="sound-wave"></div>
                            <div class="sound-wave"></div>
                            <div class="sound-wave"></div>
                        </div>
                    </div>
                    <div class="aps-voice-transcript" id="apsVoiceTranscript">
                        Tap the mic to start speaking...
                    </div>
                    <div class="aps-voice-reply" id="apsVoiceReply"></div>
                </div>
                
                <button class="aps-voice-button" id="apsVoiceBtn" title="Voice Assistant">
                    <i class="fas fa-microphone"></i>
                    <div class="aps-voice-tooltip">AI Voice Booking</div>
                </button>
            </div>
        `;
    document.body.insertAdjacentHTML('beforeend', html);

    widget = document.getElementById('apsVoiceWidget');
    btn = document.getElementById('apsVoiceBtn');
    dialog = document.getElementById('apsVoiceDialog');
    statusText = document.getElementById('apsVoiceStatusText');
    transcriptEl = document.getElementById('apsVoiceTranscript');
    replyEl = document.getElementById('apsVoiceReply');
    closeBtn = document.getElementById('apsVoiceClose');

    bindEvents();
  }

  function bindEvents() {
    if (btn) btn.addEventListener('click', toggleListening);
    if (closeBtn) closeBtn.addEventListener('click', closeDialog);

    // Stop synthesis when dialog closed
    if (closeBtn)
      closeBtn.addEventListener('click', () => {
        if (synth.speaking) synth.cancel();
      });

    recognition.onstart = function () {
      isListening = true;
      updateMode('listening');
      if (transcriptEl) transcriptEl.textContent = 'Listening...';
      if (replyEl) replyEl.classList.remove('show');
      if (synth.speaking) synth.cancel(); // stop talking if user interrupts
    };

    recognition.onresult = function (event) {
      let interimTranscript = '';
      let finalTranscript = '';

      for (let i = event.resultIndex; i < event.results.length; ++i) {
        if (event.results[i].isFinal) {
          finalTranscript += event.results[i][0].transcript;
        } else {
          interimTranscript += event.results[i][0].transcript;
        }
      }

      if (finalTranscript !== '') {
        if (transcriptEl) transcriptEl.textContent = finalTranscript;
        processVoiceCommand(finalTranscript);
      } else {
        if (transcriptEl) transcriptEl.textContent = interimTranscript;
      }
    };

    recognition.onerror = function (event) {
      console.error('Speech recognition error:', event.error);
      updateMode('ready');
      if (transcriptEl) transcriptEl.textContent = 'Error: ' + event.error;
      isListening = false;
    };

    recognition.onend = function () {
      if (isListening) {
        // If it ended naturally without a final result, reset state
        updateMode('ready');
        isListening = false;
      }
    };
  }

  function toggleListening() {
    if (dialog && !dialog.classList.contains('active')) {
      dialog.classList.add('active');
    }

    if (isListening) {
      recognition.stop();
      isListening = false;
      updateMode('ready');
    } else {
      try {
        recognition.start();
      } catch (e) {
        console.error('Already started');
      }
    }
  }

  function closeDialog() {
    if (dialog) dialog.classList.remove('active');
    if (isListening) {
      recognition.stop();
      isListening = false;
    }
    updateMode('ready');
  }

  function updateMode(mode) {
    if (widget) widget.className = 'aps-voice-widget'; // reset
    if (btn) {
      btn.className = 'aps-voice-button'; // reset
      btn.innerHTML = '<i class="fas fa-microphone"></i><div class="aps-voice-tooltip">AI Voice Booking</div>';
    }

    if (mode === 'listening') {
      if (widget) widget.classList.add('mode-listening');
      if (btn) btn.classList.add('listening');
      if (btn) btn.innerHTML = '<i class="fas fa-stop"></i>';
      if (statusText) statusText.textContent = 'Listening...';
    } else if (mode === 'processing') {
      if (widget) widget.classList.add('mode-processing');
      if (statusText) statusText.textContent = 'Thinking...';
    } else if (mode === 'speaking') {
      if (widget) widget.classList.add('mode-speaking');
      if (btn) btn.classList.add('speaking');
      if (statusText) statusText.textContent = 'Speaking...';
    } else {
      if (statusText) statusText.textContent = 'Ready';
    }
  }

  function processVoiceCommand(text) {
    updateMode('processing');
    isListening = false; // we got the final result, mic turns off implicitly

    // Get user_id if logged in, to pass as lead_id (or 0)
    let userIdElement = document.querySelector('meta[name="user-id"]');
    let leadId = userIdElement ? parseInt(userIdElement.getAttribute('content')) : 0;

    fetch((window.BASE_URL || '') + '/api/v2/mobile/voice-chat', {
      method: 'POST',
      headers: {
        'X-CSRF-Token': getCsrfToken(),
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        message: text,
        session_id: sessionId,
        lead_id: leadId,
      }),
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (data.session_id) sessionId = data.session_id;

          if (replyEl) {
            replyEl.textContent = data.reply;
            replyEl.classList.add('show');
          }

          speakText(data.reply);
        } else {
          showError('Error processing request.');
        }
      })
      .catch(error => {
        console.error('Voice API Error:', error);
        showError('Network error. Please try again.');
      });
  }

  function showError(msg) {
    if (replyEl) {
      replyEl.textContent = msg;
      replyEl.classList.add('show');
    }
    updateMode('ready');
  }

  function speakText(text) {
    if (!synth) return;

    synth.cancel(); // Stop any current speech

    currentUtterance = new SpeechSynthesisUtterance(text);

    // Try to select a Hindi voice if available
    let voices = synth.getVoices();
    let hindiVoice = voices.find(v => v.lang === 'hi-IN');
    if (hindiVoice) {
      currentUtterance.voice = hindiVoice;
    } else {
      currentUtterance.lang = 'hi-IN';
    }

    currentUtterance.onstart = function () {
      updateMode('speaking');
    };

    currentUtterance.onend = function () {
      updateMode('ready');
    };

    currentUtterance.onerror = function () {
      updateMode('ready');
    };

    synth.speak(currentUtterance);
  }

  // Ensure voices are loaded before we might need them
  if (speechSynthesis.onvoiceschanged !== undefined) {
    speechSynthesis.onvoiceschanged = () => {
      // Voices are loaded
    };
  }

  // Initialize after DOM load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
