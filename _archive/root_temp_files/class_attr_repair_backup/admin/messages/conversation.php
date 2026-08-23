<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= BASE_URL ?>/admin/messages" class="btn btn-outline-secondary btn-sm me-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h1 class="h3 mb-0 d-inline">
                <?= htmlspecialchars($other_user['name'] ?? 'Unknown') ?>
            </h1>
            <small class="text-muted ms-2">
                <span class="badge bg-<?php
                    $role = $other_user['role'] ?? 'user';
                    echo match($role) {'admin' => 'danger', 'associate' => 'success', 'agent' => 'info', 'employee' => 'warning', default => 'secondary'};
                ?>"><?= ucfirst($role) ?></span>
                <?= htmlspecialchars($other_user['email'] ?? '') ?>
                <?php if (!empty($other_user['phone'])): ?>
                    &middot; <?= htmlspecialchars($other_user['phone'] ?? '') ?>
                <?php endif; ?>
            </small>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body style-78382" id="messageContainer">
            <?php if (empty($messages)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-comment-dots fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No messages yet. Start the conversation!</p>
                </div>
            <?php else: ?>
                <?php
                $currentUserId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
                $lastDate = null;
                foreach ($messages as $msg):
                    $msgDate = date('Y-m-d', strtotime($msg['sent_at']));
                    $isMine = (int)$msg['sender_id'] === (int)$currentUserId;
                ?>
                    <?php if ($lastDate !== $msgDate): ?>
                        <?php $lastDate = $msgDate; ?>
                        <div class="text-center mb-3">
                            <span class="badge bg-light text-muted px-3 py-2">
                                <?php
                                $date = new DateTime($msgDate);
                                $now = new DateTime();
                                if ($date->format('Y-m-d') === $now->format('Y-m-d')) {
                                    echo 'Today';
                                } elseif ($date->format('Y-m-d') === $now->modify('-1 day')->format('Y-m-d')) {
                                    echo 'Yesterday';
                                } else {
                                    echo $date->format('d M Y');
                                }
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex mb-3 <?= $isMine ? 'justify-content-end' : 'justify-content-start' ?>">
                        <div class="<?= $isMine ? 'order-1' : '' ?>"
                             class="style-23652">
                            <?php if (!$isMine): ?>
                                <small class="text-muted ms-1 mb-1 d-block">
                                    <?= htmlspecialchars($msg['sender_name'] ?? '') ?>
                                </small>
                            <?php endif; ?>
                            <div class="px-3 py-2 rounded-3 <?= $isMine ? 'bg-primary text-white' : 'bg-light' ?>">
                                <p class="mb-1"><?= nl2br(htmlspecialchars($msg['content'] ?? $msg['message'] ?? '')) ?></p>
                            </div>
                            <small class="text-muted mt-1 d-block <?= $isMine ? 'text-end' : '' ?>">
                                <?= date('h:i A', strtotime($msg['sent_at'])) ?>
                                <?php if ($isMine && !empty($msg['read_at'])): ?>
                                    <i class="fas fa-check-double text-primary ms-1" title="Read"></i>
                                <?php elseif ($isMine): ?>
                                    <i class="fas fa-check text-muted ms-1" title="Sent"></i>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reply Form -->
    <div class="card mt-3">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/messages/send">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="receiver_id" value="<?= $other_user['id'] ?>">
                <div class="input-group">
                    <textarea name="message" class="form-control" rows="2"
                              placeholder="Type your message..." required
                              class="style-6407"></textarea>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('messageContainer');
    container.scrollTop = container.scrollHeight;
});
</script>
