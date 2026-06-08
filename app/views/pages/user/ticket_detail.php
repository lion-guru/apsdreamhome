<?php
$page_title = $page_title ?? 'Ticket Detail';
$ticket = $ticket ?? [];
$replies = $ticket['replies'] ?? [];

$statusBadge = match($ticket['status'] ?? 'open') {
    'open' => 'success',
    'in_progress' => 'primary',
    'waiting_customer' => 'warning',
    'resolved' => 'secondary',
    'closed' => 'danger',
    default => 'secondary'
};
$priorityBadge = match($ticket['priority'] ?? 'medium') {
    'urgent' => 'danger',
    'high' => 'warning',
    'medium' => 'primary',
    'low' => 'secondary',
    default => 'secondary'
};
$isClosed = in_array($ticket['status'] ?? '', ['resolved', 'closed']);
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-headset me-2"></i><?= htmlspecialchars($ticket['ticket_number'] ?? 'Ticket') ?></h2>
            <p class="mb-0"><?= htmlspecialchars($ticket['subject'] ?? '') ?></p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/support" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>All Tickets
            </a>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-<?= $statusBadge ?> fs-6"><?= str_replace('_', ' ', ucfirst($ticket['status'] ?? 'open')) ?></span>
                    <span class="badge bg-<?= $priorityBadge ?> fs-6"><?= ucfirst($ticket['priority'] ?? 'medium') ?> Priority</span>
                    <span class="badge bg-light text-dark fs-6"><?= ucfirst($ticket['category'] ?? 'general') ?></span>
                </div>
                <div class="text-muted small mb-3">
                    <i class="fas fa-calendar me-1"></i>Created: <?= date('d M Y, h:i A', strtotime($ticket['created_at'])) ?>
                    <?php if (!empty($ticket['last_reply_at'])): ?>
                        <span class="ms-3"><i class="fas fa-reply me-1"></i>Last reply: <?= date('d M Y, h:i A', strtotime($ticket['last_reply_at'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-comments me-2 text-primary"></i>Conversation (<?= count($replies) ?> messages)</h5>
            </div>
            <div class="aps-cp-card-body">
                <?php if (empty($replies)): ?>
                    <p class="text-muted text-center py-3">No messages yet.</p>
                <?php else: ?>
                    <?php foreach ($replies as $idx => $r):
                        $isAdmin = !empty($r['is_admin']);
                        $isOwn = !$isAdmin && (int)($r['user_id'] ?? 0) === (int)($_SESSION['user_id'] ?? 0);
                        $bgClass = $isAdmin ? 'bg-success bg-opacity-10 border-start border-success border-4' : 'bg-primary bg-opacity-10 border-start border-primary border-4';
                    ?>
                    <div class="p-3 mb-3 rounded <?= $bgClass ?>">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong class="<?= $isAdmin ? 'text-success' : 'text-primary' ?>">
                                    <i class="fas fa-<?= $isAdmin ? 'shield-alt' : 'user' ?> me-1"></i>
                                    <?= htmlspecialchars($r['user_name'] ?? ($isAdmin ? 'Support Team' : 'You')) ?>
                                </strong>
                                <?php if ($isAdmin): ?>
                                    <span class="badge bg-success ms-1">Staff</span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted"><?= date('d M Y, h:i A', strtotime($r['created_at'])) ?></small>
                        </div>
                        <div class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($r['message'] ?? '') ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($isClosed): ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        This ticket is <?= $ticket['status'] ?>. No new replies can be added.
                    </div>
                <?php else: ?>
                    <hr>
                    <form method="post" action="<?= BASE_URL ?>/user/support/<?= (int)$ticket['id'] ?>/reply">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Your Reply</label>
                            <textarea name="message" class="form-control" rows="4" required minlength="2"
                                      placeholder="Type your reply here..."></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>Send Reply
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Ticket Info</h5>
            </div>
            <div class="aps-cp-card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted">Number</td>
                        <td><strong><?= htmlspecialchars($ticket['ticket_number'] ?? '') ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td><span class="badge bg-<?= $statusBadge ?>"><?= str_replace('_', ' ', ucfirst($ticket['status'])) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Priority</td>
                        <td><span class="badge bg-<?= $priorityBadge ?>"><?= ucfirst($ticket['priority']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Category</td>
                        <td><?= ucfirst($ticket['category']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Replies</td>
                        <td><?= (int)($ticket['reply_count'] ?? 0) ?></td>
                    </tr>
                    <?php if (!empty($ticket['assigned_name'])): ?>
                    <tr>
                        <td class="text-muted">Assigned To</td>
                        <td><?= htmlspecialchars($ticket['assigned_name']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted">Created</td>
                        <td><?= date('d M Y', strtotime($ticket['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if (!empty($ticket['booking_id'])): ?>
        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i>Related Booking</h5>
            </div>
            <div class="aps-cp-card-body">
                <p class="mb-0">Booking #<?= (int)$ticket['booking_id'] ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
