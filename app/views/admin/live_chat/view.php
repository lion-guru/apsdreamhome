<?php
$page_title = $page_title ?? 'Chat';
$page_heading = $page_heading ?? 'Chat Session';
$content = $content ?? '';
$session = $session ?? [];
$messages = $messages ?? [];
$quick_replies = $quick_replies ?? [];
ob_start();
?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-comments me-2"></i>
                <?= htmlspecialchars($session['visitor_name'] ?: $session['user_name'] ?: 'Visitor') ?>
            </h4>
            <p class="text-muted small mb-0">
                <?= htmlspecialchars($session['visitor_email'] ?? '') ?>
                <?php if ($session['visitor_phone']): ?>
                    Â· <?= htmlspecialchars($session['visitor_phone'] ?? '') ?>
                <?php endif; ?>
                Â· <?= htmlspecialchars($session['subject'] ?: 'No subject') ?>
                Â· <span class="badge bg-<?= ['open'=>'primary','assigned'=>'info','active'=>'success','on_hold'=>'warning','closed'=>'secondary','missed'=>'danger'][$session['status']] ?? 'secondary' ?>"><?= ucfirst($session['status']) ?></span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($session['status'] !== 'closed'): ?>
                <a href="<?= BASE_URL ?>/admin/live-chat/close?id=<?= $session['id'] ?>&reason=resolved" class="btn btn-outline-secondary" data-aps-confirm="Close this chat?">
                    <i class="fas fa-times-circle me-1"></i> Close
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/live-chat" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body" id="messagesContainer" class="style-67427">
                    <?php if (empty($messages)): ?>
                        <p class="text-center text-muted py-5">No messages yet</p>
                    <?php else: ?>
                        <?php foreach ($messages as $m):
                            $isAgent = $m['sender_type'] === 'agent';
                            $isSystem = in_array($m['sender_type'], ['system','bot']);
                            $isInternal = !empty($m['is_internal_note']);
                        ?>
                            <?php if ($isSystem && !$isInternal): ?>
                                <div class="text-center my-2">
                                    <small class="badge bg-light text-muted"><?= htmlspecialchars($m['message'] ?? '') ?></small>
                                </div>
                            <?php elseif ($isInternal): ?>
                                <div class="alert alert-warning py-1 px-2 my-1 small">
                                    <i class="fas fa-sticky-note me-1"></i>
                                    <strong>Internal:</strong> <?= htmlspecialchars($m['message'] ?? '') ?>
                                </div>
                            <?php else: ?>
                                <div class="d-flex mb-2 <?= $isAgent ? 'justify-content-end' : '' ?>">
                                    <div class="p-2 px-3 rounded shadow-sm style-98554">
                                        <?php if ($m['sender_type'] !== 'agent' && $m['sender_name']): ?>
                                            <small class="d-block <?= $isAgent ? 'text-white-50' : 'text-muted' ?> style-62191"><?= htmlspecialchars($m['sender_name'] ?? '') ?></small>
                                        <?php endif; ?>
                                        <div class="style-19219"><?= htmlspecialchars($m['message'] ?? '') ?></div>
                                        <small class="d-block <?= $isAgent ? 'text-white-50' : 'text-muted' ?> style-32173">
                                            <?= date('H:i', strtotime($m['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white">
                    <form id="chatForm" class="d-flex gap-2 mb-2">
    <?php echo CSRFProtection::csrfField(); ?>
                        <input type="hidden" id="sessionId" value="<?= $session['id'] ?>">
                        <input type="text" id="messageInput" class="form-control" placeholder="Type your reply..." autocomplete="off" required>
                        <button type="submit" class="btn btn-primary" aria-label="Send message"><i class="fas fa-paper-plane"></i></button>
                    </form>
                    <form id="internalForm" class="d-flex gap-2 mb-2">
    <?php echo CSRFProtection::csrfField(); ?>
                        <input type="hidden" name="is_internal" value="1">
                        <input type="text" name="message" class="form-control form-control-sm" placeholder="Internal note (not visible to visitor)...">
                        <button type="submit" class="btn btn-sm btn-warning" aria-label="Add internal note"><i class="fas fa-sticky-note"></i></button>
                    </form>
                    <div class="d-flex gap-1 flex-wrap">
                        <?php foreach ($quick_replies as $qr): ?>
                            <button class="btn btn-sm btn-outline-secondary quick-reply-btn" data-msg="<?= htmlspecialchars($qr['message'] ?? '') ?>">
                                <?= htmlspecialchars($qr['title'] ?? '') ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body aps-cp-card-body">
                    <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Session Info</h6>
                    <p class="small mb-1"><strong>Created:</strong> <?= date('M j, H:i', strtotime($session['created_at'])) ?></p>
                    <p class="small mb-1"><strong>Source:</strong> <?= htmlspecialchars($session['source'] ?? '') ?></p>
                    <?php if ($session['page_url']): ?>
                        <p class="small mb-1"><strong>Page:</strong> <a href="<?= htmlspecialchars($session['page_url'] ?? '') ?>" target="_blank" class="text-truncate d-block style-65684"><?= htmlspecialchars($session['page_url'] ?? '') ?></a></p>
                    <?php endif; ?>
                    <p class="small mb-1"><strong>IP:</strong> <?= htmlspecialchars($session['ip_address'] ?: '—') ?></p>
                    <p class="small mb-1"><strong>Country:</strong> <?= htmlspecialchars($session['country'] ?: '—') ?></p>
                    <p class="small mb-1"><strong>Agent:</strong> <?= htmlspecialchars($session['agent_name'] ?? 'Unassigned') ?></p>
                    <p class="small mb-0"><strong>Messages:</strong> <?= $session['message_count'] ?></p>
                </div>
            </div>
            <?php if ($session['status'] === 'open' && !$session['agent_name']): ?>
                <a href="<?= BASE_URL ?>/admin/live-chat/assign?id=<?= $session['id'] ?>" class="btn btn-success w-100">
                    <i class="fas fa-hand-paper me-1"></i> Assign to Me
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const sessionId = document.getElementById('sessionId').value;
const messagesContainer = document.getElementById('messagesContainer');
let lastMessageId = <?= !empty($messages) ? max(array_column($messages, 'id')) : 0 ?>;

document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const msg = document.getElementById('messageInput').value.trim();
    if (!msg) return;
    sendMessage(msg, false);
    document.getElementById('messageInput').value = '';
});

document.getElementById('internalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const msg = formData.get('message');
    if (!msg.trim()) return;
    sendMessage(msg, true);
    e.target.reset();
});

document.querySelectorAll('.quick-reply-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('messageInput').value = this.dataset.msg;
        document.getElementById('messageInput').focus();
    });
});

function sendMessage(message, isInternal) {
    const formData = new FormData();
    formData.append('session_id', sessionId);
    formData.append('message', message);
    if (isInternal) formData.append('is_internal', '1');
    fetch('<?= BASE_URL ?>/admin/live-chat/send', { method: 'POST', body: formData, credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                appendMessage('agent', 'You', message, isInternal);
                .catch(err => console.error('Request failed:', err));
                if (isInternal) location.reload();
            }
        });
}

function appendMessage(senderType, senderName, message, isInternal) {
    if (isInternal) {
        messagesContainer.insertAdjacentHTML('beforeend', `<div class="alert alert-warning py-1 px-2 my-1 small"><i class="fas fa-sticky-note me-1"></i><strong>Internal:</strong> ${escapeHtml(message)}</div>`);
    } else {
        const align = senderType === 'agent' ? 'justify-content-end' : '';
        const bg = senderType === 'agent' ? 'background:#007bff;color:white;' : 'background:white;border:1px solid #dee2e6;';
        messagesContainer.insertAdjacentHTML('beforeend', `<div class="d-flex mb-2 ${align}"><div class="p-2 px-3 rounded shadow-sm style-64326"><div class="style-19219">${escapeHtml(message)}</div><small class="d-block text-white-50 style-32173">${new Date().toLocaleTimeString()}</small></div></div>`);
    }
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function escapeHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

function pollMessages() {
    fetch(`<?= BASE_URL ?>/admin/live-chat/poll?session_id=${sessionId}&last_id=${lastMessageId}`, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (data.messages && data.messages.length) {
                data.messages.forEach(m => {
                    .catch(err => console.error('Request failed:', err));
                    appendMessage(m.sender_type, m.sender_name, m.message, false);
                    lastMessageId = Math.max(lastMessageId, m.id);
                });
            }
        })
        .finally(() => setTimeout(pollMessages, 4000));
}
messagesContainer.scrollTop = messagesContainer.scrollHeight;
setTimeout(pollMessages, 4000);
</script>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';
