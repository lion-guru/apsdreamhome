<?php
/**
 * Shared AI Coach view — parameterised by role via AiCoachPacks.
 * Expects: $role, $layoutTitle, $base
 */
use App\Services\Ai\AiCoachPacks;

$role = $role ?? 'employee';
$pack = AiCoachPacks::pack($role);
$base = rtrim(BASE_URL, '/');
$label = AiCoachPacks::roleLabel[$role] ?? 'Employee';
?>

<div class="container-fluid py-4">

    <div class="row align-items-center mb-4 g-3">
        <div class="col-md-8">
            <h4 class="mb-1 fw-bold"><i class="fas fa-robot me-2 text-primary"></i>AI Coach — <?= htmlspecialchars($label) ?></h4>
            <p class="text-muted mb-0 small">Aapki personal AI assistant — tap karein ya type karein.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <?php if ($role === 'agent'): ?>
                <a href="<?= $base ?>/agent/dashboard" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
            <?php elseif ($role === 'associate'): ?>
                <a href="<?= $base ?>/associate/dashboard" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
            <?php elseif ($role === 'customer'): ?>
                <a href="<?= $base ?>/user/profile" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Profile</a>
            <?php else: ?>
                <a href="<?= $base ?>/employee/self-service" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Self-Service</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">

        <!-- Chat pane -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header fw-bold bg-white border-bottom d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-comments me-2 text-primary"></i>Coach Chat</span>
                    <span class="badge bg-success" id="aiCoachStatus"><i class="fas fa-circle me-1" style="font-size:.5rem"></i>Online</span>
                </div>
                <div class="card-body" style="background: linear-gradient(180deg,#f8fafc 0%,#ffffff 30%);">
                    <div id="aiCoachMessages" class="mb-3" style="height: 380px; overflow-y:auto; padding: .25rem;">
                        <div class="d-flex gap-2 mb-3">
                            <div class="flex-shrink-0" style="width:36px;height:36px;border-radius:50%;background:#e0ecff;display:flex;align-items:center;justify-content:center;"><i class="fas fa-robot text-primary"></i></div>
                            <div class="p-3 rounded-4 bg-white border shadow-sm" style="max-width: 78%;">
                                <div class="small fw-bold text-primary mb-1">APS Assistant</div>
                                <div class="small" style="white-space:pre-wrap"><?= $pack['greeting'] ?? '' ?></div>
                            </div>
                        </div>
                    </div>

                    <form id="aiCoachForm" class="border-top pt-3" autocomplete="off">
                        <div class="input-group">
                            <label class="visually-hidden" for="aiCoachInput">Type your message</label>
                            <input type="text" class="form-control form-control-lg" id="aiCoachInput" placeholder="<?= htmlspecialchars($pack['placeholder'] ?? 'Ask your coach…') ?>" autocomplete="off" maxlength="500" aria-label="<?= htmlspecialchars($pack['placeholder'] ?? 'Type your question') ?>">
                            <button class="btn btn-primary btn-lg" type="submit" id="aiCoachSend" aria-label="Send"><i class="fas fa-paper-plane"></i></button>
                        </div>
                        <div class="form-text small text-muted mt-1"><i class="fas fa-lock me-1"></i>Your conversation stays on your work account. Don't share customer PII outside the portal.</div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right column: quick prompts + privacy -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-2"><i class="fas fa-bolt me-1 text-warning"></i><?= htmlspecialchars($pack['title']) ?></h6>
                    <p class="small text-muted mb-3"><?= htmlspecialchars($pack['subtitle']) ?></p>
                    <div class="row g-2">
                        <?php foreach ($pack['pairs'] as $p): ?>
                        <div class="col-12">
                            <button type="button" class="btn btn-outline-primary w-100 text-start ai-quick-prompt" data-prompt="<?= htmlspecialchars($p[2], ENT_QUOTES) ?>">
                                <span class="me-2"><?= $p[0] ?></span><strong><?= htmlspecialchars($p[1]) ?></strong><br><span class="small text-muted"><?= htmlspecialchars($p[2]) ?></span>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-2"><i class="fas fa-shield-alt me-1 text-success"></i>Your Privacy &amp; Ethics</h6>
                    <ul class="small text-muted mb-0 ps-3">
                        <li>Never share passwords, PINs or bank OTPs.</li>
                        <li>Customer phone/email — only for official work.</li>
                        <li>Respect do-not-call and 9 AM – 9 PM limits.</li>
                        <li>Escalate complaints to whoever owns the workflow.</li>
                        <li><a href="<?= $base ?>/employee/self-service/grievances">Employee Helpline</a></li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    const input = document.getElementById('aiCoachInput');
    const sendBtn = document.getElementById('aiCoachSend');
    const form = document.getElementById('aiCoachForm');
    const messages = document.getElementById('aiCoachMessages');
    const statusBadge = document.getElementById('aiCoachStatus');
    const sessionId = '<?= $role ?>_' + Date.now();

    function addBubble(text, role) {
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 mb-3';
        const avatar = document.createElement('div');
        avatar.className = 'flex-shrink-0';
        avatar.style.cssText = 'width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;'
            + (role === 'user' ? 'background:#dbeafe;' : 'background:#e0ecff;');
        avatar.innerHTML = role === 'user' ? '<i class="fas fa-user text-info"></i>' : '<i class="fas fa-robot text-primary"></i>';
        const body = document.createElement('div');
        body.className = 'p-3 rounded-4 border shadow-sm';
        body.style.cssText = 'max-width:78%; background:#ffffff;';
        body.innerHTML = '<div class="small fw-bold ' + (role === 'user' ? 'text-info' : 'text-primary') + ' mb-1">' + (role === 'user' ? 'You' : 'APS Assistant') + '</div>';
        const inner = document.createElement('div');
        inner.className = 'small';
        inner.style.whiteSpace = 'pre-wrap';
        inner.textContent = text;
        body.appendChild(inner);
        row.appendChild(avatar);
        row.appendChild(body);
        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;
    }

    function setStatus(state) {
        if (!statusBadge) return;
        if (state === 'online') { statusBadge.innerHTML = '<i class="fas fa-circle me-1" style="font-size:.5rem"></i>Online'; statusBadge.className = 'badge bg-success'; }
        else if (state === 'thinking') { statusBadge.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Thinking…'; statusBadge.className = 'badge bg-warning text-dark'; }
        else { statusBadge.innerHTML = '<i class="fas fa-circle me-1" style="font-size:.5rem"></i>Offline'; statusBadge.className = 'badge bg-secondary'; }
    }

    async function askCoach(text) {
        if (!text || !text.trim()) return;
        addBubble(text.trim(), 'user');
        setStatus('thinking');
        try {
            const params = new URLSearchParams();
            params.set('message', text.trim());
            params.set('session_id', sessionId);
            const res = await fetch('<?= $base ?>/api/smart-ai/chat', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            });
            const data = await res.json();
            const answer = (data && (data.response || data.responseText)) ? (data.response || data.responseText) : 'Sorry, I could not respond right now. Please try again.';
            addBubble(answer, 'assistant');
            setStatus('online');
        } catch (e) {
            addBubble('Network error. Please check your connection and try again.', 'assistant');
            setStatus('offline');
        }
    }

    document.querySelectorAll('.ai-quick-prompt').forEach(btn => {
        btn.addEventListener('click', () => {
            const prompt = btn.getAttribute('data-prompt');
            input.value = prompt;
            askCoach(prompt);
            input.value = '';
            input.focus();
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const value = input.value.trim();
        if (!value) return;
        input.value = '';
        askCoach(value);
    });
})();
</script>
