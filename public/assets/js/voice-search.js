/**
 * Voice Search - Web Speech API
 * Enables voice-based search for properties, leads, and plots
 */

class VoiceSearch {
    constructor(options = {}) {
        this.options = {
            lang: options.lang || 'en-IN',
            continuous: false,
            interimResults: false,
            ...options
        };
        
        this.recognition = null;
        this.isListening = false;
        this.onResult = options.onResult || (() => {});
        this.onError = options.onError || (() => {});
        this.onStart = options.onStart || (() => {});
        this.onEnd = options.onEnd || (() => {});

        this.init();
    }

    init() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            console.warn('Speech Recognition API not supported in this browser');
            this.onError('Speech Recognition not supported');
            return;
        }

        this.recognition = new SpeechRecognition();
        this.recognition.lang = this.options.lang;
        this.recognition.continuous = this.options.continuous;
        this.recognition.interimResults = this.options.interimResults;

        this.recognition.onstart = () => {
            this.isListening = true;
            this.onStart();
        };

        this.recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            const confidence = event.results[0][0].confidence;
            this.onResult(transcript, confidence);
        };

        this.recognition.onerror = (event) => {
            this.isListening = false;
            this.onError(event.error);
        };

        this.recognition.onend = () => {
            this.isListening = false;
            this.onEnd();
        };
    }

    start() {
        if (!this.recognition) {
            this.onError('Speech Recognition not initialized');
            return;
        }
        if (!this.isListening) {
            try {
                this.recognition.start();
            } catch (e) {
                this.onError(e.message);
            }
        }
    }

    stop() {
        if (this.recognition && this.isListening) {
            this.recognition.stop();
        }
    }

    toggle() {
        if (this.isListening) {
            this.stop();
        } else {
            this.start();
        }
    }

    static isSupported() {
        return !!(window.SpeechRecognition || window.webkitSpeechRecognition);
    }
}

// Voice Search UI Integration
class VoiceSearchUI {
    constructor(inputSelector, options = {}) {
        this.inputSelector = inputSelector;
        this.input = document.querySelector(inputSelector);
        this.options = options;
        this.voiceButton = null;

        if (this.input && VoiceSearch.isSupported()) {
            this.createButton();
            this.initVoiceSearch();
        }
    }

    createButton() {
        this.voiceButton = document.createElement('button');
        this.voiceButton.type = 'button';
        this.voiceButton.className = 'btn btn-outline-secondary voice-search-btn';
        this.voiceButton.innerHTML = '<i class="fas fa-microphone"></i>';
        this.voiceButton.title = 'Voice Search';
        this.voiceButton.setAttribute('aria-label', 'Voice Search');

        // Insert after input
        this.input.parentNode.insertBefore(this.voiceButton, this.input.nextSibling);

        // Style the button
        this.voiceButton.style.marginLeft = '5px';
        this.voiceButton.style.borderRadius = '50%';
        this.voiceButton.style.width = '40px';
        this.voiceButton.style.height = '40px';
    }

    initVoiceSearch() {
        this.voiceSearch = new VoiceSearch({
            lang: this.options.lang || 'en-IN',
            onStart: () => {
                this.voiceButton.classList.add('listening');
                this.voiceButton.innerHTML = '<i class="fas fa-microphone-slash text-danger"></i>';
            },
            onResult: (transcript, confidence) => {
                this.input.value = transcript;
                this.input.dispatchEvent(new Event('input', { bubbles: true }));
                if (this.options.autoSubmit) {
                    this.input.closest('form')?.submit();
                }
            },
            onError: (error) => {
                console.warn('Voice search error:', error);
            },
            onEnd: () => {
                this.voiceButton.classList.remove('listening');
                this.voiceButton.innerHTML = '<i class="fas fa-microphone"></i>';
            },
        });

        this.voiceButton.addEventListener('click', () => {
            this.voiceSearch.toggle();
        });
    }
}

// Auto-initialize voice search on elements with data-voice-search attribute
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-voice-search]').forEach((input) => {
        new VoiceSearchUI(`#${input.id}`, {
            lang: input.dataset.voiceLang || 'en-IN',
            autoSubmit: input.dataset.voiceAutoSubmit === 'true',
        });
    });
});
