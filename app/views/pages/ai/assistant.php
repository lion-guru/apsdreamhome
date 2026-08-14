<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-white p-4" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-robot fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="mb-0"><?= __('aiast_heading', [], 'AI Assistant') ?></h2>
                            <p class="mb-0 text-white-50 mt-1"><?= __('aiast_subtitle', [], 'Your personal property concierge, available 24/7') ?></p>
                        </div>
                        <div class="ms-auto">
                            <span class="badge bg-success rounded-pill px-3 py-2">
                                <i class="fas fa-circle fa-xs me-1"></i> <?= __('aiast_online', [], 'Online') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="chat-container d-flex flex-column" style="height: 520px; background: #f8f9fa;">
                    <div class="chat-messages p-4" id="chatMessages" style="flex: 1; overflow-y: auto;">
                        <div class="text-center mb-4">
                            <div class="d-inline-block p-3 rounded-circle mb-2" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
                                <i class="fas fa-robot fa-2x text-white"></i>
                            </div>
                            <h4 class="mb-1"><?= __('aiast_greeting', [], 'Namaste!') ?> 🙏</h4>
                            <p class="text-muted mb-0"><?= __('aiast_welcome', [], "I'm your APS Dream Home AI assistant. Ask me anything about properties, pricing, site visits, or loans!") ?></p>
                        </div>
                    </div>

                    <div class="quick-replies p-3 bg-white border-top" id="quickReplies">
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-outline-primary rounded-pill" data-q="I want to view properties">
                                <i class="fas fa-building me-1"></i> <?= __('aiast_q_view', [], 'View Properties') ?>
                            </button>
                            <button class="btn btn-sm btn-outline-success rounded-pill" data-q="What are the price details?">
                                <i class="fas fa-tag me-1"></i> <?= __('aiast_q_price', [], 'Price Details') ?>
                            </button>
                            <button class="btn btn-sm btn-outline-info rounded-pill" data-q="Book a site visit">
                                <i class="fas fa-calendar-check me-1"></i> <?= __('aiast_q_visit', [], 'Book Visit') ?>
                            </button>
                            <button class="btn btn-sm btn-outline-warning rounded-pill" data-q="I need a home loan">
                                <i class="fas fa-hand-holding-usd me-1"></i> <?= __('aiast_q_loan', [], 'Home Loan') ?>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary rounded-pill" data-q="How can I contact you?">
                                <i class="fas fa-phone me-1"></i> <?= __('aiast_q_contact', [], 'Contact') ?>
                            </button>
                        </div>
                    </div>

                    <div class="loading text-center py-2 text-muted small" id="loading" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <em>Typing<span class="dots">...</span></em>
                    </div>

                    <div class="chat-input p-3 bg-white border-top">
                        <form id="chatForm" class="input-group">
    <?php echo CSRFProtection::csrfField(); ?>
                            <input type="text" id="userInput" class="form-control rounded-start-pill border-end-0 py-2"
                                   placeholder="<?= __('aiast_placeholder', [], 'Type your message...') ?>" autocomplete="off" required />
                            <button class="btn btn-primary rounded-end-pill px-4" type="submit" id="sendBtn">
                                <i class="fas fa-paper-plane me-1"></i> <?= __('aiast_send', [], 'Send') ?>
                            </button>
                        </form>
                        <small class="text-muted d-block mt-2 text-center">
                            <i class="fas fa-shield-alt me-1"></i> <?= __('aiast_privacy', [], 'Your conversation is private and secure.') ?>
                        </small>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3 text-muted small">
                <i class="fas fa-info-circle me-1"></i> <?= __('aiast_powered_by', [], 'Powered by APS Dream Home AI Engine') ?>
            </div>
        </div>
    </div>
</div>

<style>
    #chatMessages .message {
        animation: fadeIn 0.3s ease-in;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .dots::after {
        content: '';
        animation: dots 1.2s steps(4, end) infinite;
    }
    @keyframes dots {
        0%, 20%   { content: ''; }
        40%       { content: '.'; }
        60%       { content: '..'; }
        80%, 100% { content: '...'; }
    }
    #chatMessages::-webkit-scrollbar { width: 6px; }
    #chatMessages::-webkit-scrollbar-thumb { background: #c4c4c4; border-radius: 3px; }
</style>

<script>
(function () {
    var messages = document.getElementById('chatMessages');
    var form     = document.getElementById('chatForm');
    var input    = document.getElementById('userInput');
    var loading  = document.getElementById('loading');
    var sendBtn  = document.getElementById('sendBtn');

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function addMessage(text, role) {
        var wrapper = document.createElement('div');
        wrapper.className = 'message mb-3 clearfix';
        if (role === 'user') {
            wrapper.innerHTML =
                '<div class="p-3 rounded-4 shadow-sm" style="max-width: 85%; margin-left: auto; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #fff;">'
                + escapeHtml(text) + '</div>';
        } else {
            wrapper.innerHTML =
                '<div class="p-3 rounded-4 bg-white shadow-sm" style="max-width: 85%; margin-right: auto; border-left: 4px solid #0d9488;">'
                + escapeHtml(text) + '</div>';
        }
        messages.appendChild(wrapper);
        messages.scrollTop = messages.scrollHeight;
    }

    function send(text) {
        text = (text || '').trim();
        if (!text) return;

        addMessage(text, 'user');
        input.value = '';
        loading.style.display = 'block';
        sendBtn.disabled = true;

        fetch('<?= BASE_URL ?>api/ai/chatbot', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ message: text })
        })
        .then(function (r) { return r.json().catch(function () { return {}; }); })
        .then(function (data) {
            var reply = data && (data.response || data.message || data.reply || data.text)
                        || "<?= __('aiast_fallback_reply', [], "I'm sorry, I couldn't process that right now. Please try again.") ?>";
            addMessage(reply, 'bot');
        })
        .catch(function () {
            addMessage("<?= __('aiast_fallback_error', [], "Sorry, I'm having trouble connecting. Please try again in a moment.") ?>", 'bot');
        })
        .finally(function () {
            loading.style.display = 'none';
            sendBtn.disabled = false;
            input.focus();
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        send(input.value);
    });

    document.querySelectorAll('#quickReplies button[data-q]').forEach(function (btn) {
        btn.addEventListener('click', function () { send(btn.getAttribute('data-q')); });
    });

    input.focus();
})();
</script>
