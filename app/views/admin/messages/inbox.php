<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Messages</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/messages/compose" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Message
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Inbox</span>
            <?php if ($total_unread > 0): ?>
                <span class="badge bg-danger rounded-pill"><?= $total_unread ?> unread</span>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($conversations)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-envelope-open-text fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No conversations yet.</p>
                    <a href="<?= BASE_URL ?>/admin/messages/compose" class="btn btn-primary">Send your first message</a>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($conversations as $conv): ?>
                        <a href="<?= BASE_URL ?>/admin/messages/conversation/<?= $conv['other_user_id'] ?>"
                           class="list-group-item list-group-item-action d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                     style="width: 48px; height: 48px; border-radius: 50%; font-weight: bold; font-size: 18px;">
                                    <?= strtoupper(substr($conv['other_user_name'] ?? '?', 0, 1)) ?>
                                </div>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 <?= ($conv['unread_count'] ?? 0) > 0 ? 'fw-bold' : '' ?>">
                                        <?= htmlspecialchars($conv['other_user_name'] ?? 'Unknown') ?>
                                    </h6>
                                    <small class="text-muted ms-2" style="white-space: nowrap;">
                                        <?php if (!empty($conv['last_message_time'])): ?>
                                            <?php
                                            $date = new DateTime($conv['last_message_time']);
                                            $now = new DateTime();
                                            $diff = $now->diff($date);
                                            if ($diff->days == 0) {
                                                echo $date->format('h:i A');
                                            } elseif ($diff->days == 1) {
                                                echo 'Yesterday';
                                            } elseif ($diff->days < 7) {
                                                echo $diff->days . 'd ago';
                                            } else {
                                                echo $date->format('d M');
                                            }
                                            ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted text-truncate d-block" style="max-width: 300px;">
                                        <span class="badge bg-<?php
                                            $role = $conv['other_user_role'] ?? 'user';
                                            echo match($role) {'admin' => 'danger', 'associate' => 'success', 'agent' => 'info', 'employee' => 'warning', default => 'secondary'};
                                        ?> me-1" style="font-size: 10px;"><?= ucfirst($role) ?></span>
                                        <?= htmlspecialchars(mb_substr($conv['last_message'] ?? 'No messages', 0, 60)) ?>
                                        <?= strlen($conv['last_message'] ?? '') > 60 ? '...' : '' ?>
                                    </small>
                                    <?php if (($conv['unread_count'] ?? 0) > 0): ?>
                                        <span class="badge bg-danger rounded-pill ms-2"><?= $conv['unread_count'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    font-size: 18px;
}
.min-width-0 {
    min-width: 0;
}
</style>
