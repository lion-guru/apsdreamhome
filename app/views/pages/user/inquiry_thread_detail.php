<?php
/**
 * Single buyer-seller thread: inquiry + messages + reply box.
 * @var array $inquiry
 * @var array $messages
 * @var int $other_id
 * @var string|null $error
 */
$inquiry = $inquiry ?? [];
$messages = $messages ?? [];
$other_id = (int)($other_id ?? 0);
$error = $error ?? null;
$base = defined('BASE_URL') ? BASE_URL : '';
$me = (int)($_SESSION['user_id'] ?? 0);
$title = $inquiry['listing_title'] ?? $inquiry['property_title'] ?? ('Inquiry #' . ($inquiry['id'] ?? ''));
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <h5><i class="fas fa-comments text-primary"></i> <?= htmlspecialchars($title) ?></h5>
        <a href="<?= $base ?>/user/inquiries/threads" class="btn btn-sm btn-outline-secondary">All Threads</a>
    </div>
    <div class="aps-cp-card-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="p-3 mb-3 bg-light rounded">
            <div class="small text-muted mb-1">
                Inquiry by <strong><?= htmlspecialchars($inquiry['name'] ?? '') ?></strong>
                (<?= htmlspecialchars($inquiry['phone'] ?? '') ?>)
                · <?= htmlspecialchars(date('d M Y', strtotime($inquiry['created_at'] ?? 'now'))) ?>
            </div>
            <div><?= nl2br(htmlspecialchars($inquiry['message'] ?? '')) ?></div>
        </div>
        <div class="d-flex flex-column gap-2 mb-3" style="max-height:420px;overflow-y:auto">
            <?php if (empty($messages)): ?>
                <p class="text-muted small">No messages yet — say hello below to start the conversation.</p>
            <?php endif; ?>
            <?php foreach ($messages as $m): $mine = ((int)($m['sender_id'] ?? 0)) === $me; ?>
                <div class="d-flex <?= $mine ? 'justify-content-end' : 'justify-content-start' ?>">
                    <div class="p-2 px-3 rounded <?= $mine ? 'bg-primary text-white' : 'bg-light border' ?>" style="max-width:75%">
                        <div class="small fw-semibold"><?= $mine ? 'You' : htmlspecialchars($m['sender_name'] ?? 'Them') ?></div>
                        <div><?= nl2br(htmlspecialchars($m['message'] ?? '')) ?></div>
                        <div class="small <?= $mine ? 'text-white-50' : 'text-muted' ?>"><?= htmlspecialchars(date('d M, h:i A', strtotime($m['created_at'] ?? 'now'))) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($other_id > 0): ?>
            <form method="POST" action="<?= $base ?>/user/inquiries/threads/<?= (int)($inquiry['id'] ?? 0) ?>/reply">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="input-group">
                    <input type="text" name="message" class="form-control" placeholder="Type your reply..." required maxlength="2000">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info mb-0">The other party has no account yet — they were notified by SMS. Replies unlock once they join.</div>
        <?php endif; ?>
    </div>
</div>
