<?php
$ticket = $ticket ?? [];
$replies = $replies ?? [];
$staffMembers = $staffMembers ?? [];
$page_title = $page_title ?? 'Ticket Details';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

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
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-headset me-2"></i><?= htmlspecialchars($ticket['ticket_number'] ?? 'Ticket') ?></h2>
            <p class="text-muted mb-0"><?= htmlspecialchars($ticket['subject'] ?? '') ?></p>
        </div>
        <div>
            <a href="<?= $base ?>/admin/support_tickets" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to List
            </a>
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

    <?php if (empty($ticket)): ?>
        <div class="alert alert-warning">Ticket not found.</div>
    <?php else: ?>
    <div class="row">
        <div class="col-md-8">
            <!-- Ticket Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Ticket Information</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-<?= $statusBadge ?> fs-6"><?= str_replace('_', ' ', ucfirst($ticket['status'] ?? 'open')) ?></span>
                        <span class="badge bg-<?= $priorityBadge ?> fs-6"><?= ucfirst($ticket['priority'] ?? 'medium') ?></span>
                        <span class="badge bg-light text-dark fs-6"><?= ucfirst($ticket['category'] ?? 'general') ?></span>
                    </div>
                </div>
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($ticket['message'] ?? $ticket['description'] ?? '')) ?></p>
                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Created</small>
                            <strong><?= date('d M Y, h:i A', strtotime($ticket['created_at'])) ?></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Replies</small>
                            <strong><?= (int)($ticket['reply_count'] ?? count($replies)) ?></strong>
                        </div>
                        <?php if (!empty($ticket['last_reply_at'])): ?>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Last Reply</small>
                            <strong><?= date('d M Y, h:i A', strtotime($ticket['last_reply_at'])) ?></strong>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($ticket['resolved_at'])): ?>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Resolved</small>
                            <strong><?= date('d M Y, h:i A', strtotime($ticket['resolved_at'])) ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Conversation -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Conversation (<?= count($replies) ?> messages)</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($replies)): ?>
                        <p class="text-muted text-center py-3">No messages yet.</p>
                    <?php else: ?>
                        <?php foreach ($replies as $r):
                            $isAdmin = !empty($r['is_admin']);
                        ?>
                        <div class="d-flex mb-3 <?= $isAdmin ? '' : 'flex-row-reverse' ?>">
                            <div class="flex-shrink-0 ms-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" class="style-75348">
                                    <i class="fas fa-<?= $isAdmin ? 'shield-alt' : 'user' ?>"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1" class="style-80611">
                                <div class="p-3 rounded <?= $isAdmin ? 'bg-success bg-opacity-10 border-start border-success border-4' : 'bg-primary bg-opacity-10 border-start border-primary border-4' ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="<?= $isAdmin ? 'text-success' : 'text-primary' ?>">
                                            <?= htmlspecialchars($r['user_name'] ?? ($isAdmin ? 'Support Team' : 'Customer')) ?>
                                            <?php if ($isAdmin): ?>
                                                <span class="badge bg-success ms-1">Staff</span>
                                            <?php endif; ?>
                                        </strong>
                                        <small class="text-muted"><?= date('d M Y, h:i A', strtotime($r['created_at'])) ?></small>
                                    </div>
                                    <div class="style-19219"><?= htmlspecialchars($r['message'] ?? '') ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!in_array($ticket['status'] ?? '', ['resolved', 'closed'])): ?>
                    <hr>
                    <form method="post" action="<?= $base ?>/admin/support-tickets/<?= (int)$ticket['id'] ?>/reply">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Staff Reply</label>
                            <textarea name="message" class="form-control" rows="4" required minlength="2"
                                      placeholder="Type your reply to the customer..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i>Send Reply
                        </button>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Ticket is <?= $ticket['status'] ?>. Reopen it to reply.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Customer Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <h6><?= htmlspecialchars($ticket['customer_name'] ?? 'Unknown') ?></h6>
                    <p class="text-muted small mb-1"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($ticket['customer_email'] ?? '') ?></p>
                    <?php if (!empty($ticket['customer_phone'])): ?>
                        <p class="text-muted small mb-0"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($ticket['customer_phone']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Update Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-sync me-2"></i>Update Status</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= $base ?>/admin/support-tickets/<?= (int)$ticket['id'] ?>/status">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="mb-3">
                            <select name="status" class="form-select">
                                <?php foreach (['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'] as $s): ?>
                                    <option value="<?= $s ?>" <?= ($ticket['status'] ?? '') === $s ? 'selected' : '' ?>>
                                        <?= str_replace('_', ' ', ucfirst($s)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-save me-1"></i>Update Status
                        </button>
                    </form>
                </div>
            </div>

            <!-- Assign Ticket -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Assign To</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= $base ?>/admin/support-tickets/<?= (int)$ticket['id'] ?>/assign">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="mb-3">
                            <select name="assigned_to" class="form-select">
                                <option value="">-- Unassigned --</option>
                                <?php foreach ($staffMembers as $staff): ?>
                                    <option value="<?= (int)$staff['id'] ?>" <?= (int)($ticket['assigned_to'] ?? 0) === (int)$staff['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($staff['name']) ?> (<?= htmlspecialchars($staff['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-user-plus me-1"></i>Assign
                        </button>
                    </form>
                </div>
            </div>

            <!-- Ticket Info -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Details</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <tr><td class="text-muted">Number</td><td><strong><?= htmlspecialchars($ticket['ticket_number'] ?? '') ?></strong></td></tr>
                        <tr><td class="text-muted">Priority</td><td><span class="badge bg-<?= $priorityBadge ?>"><?= ucfirst($ticket['priority']) ?></span></td></tr>
                        <tr><td class="text-muted">Category</td><td><?= ucfirst($ticket['category']) ?></td></tr>
                        <tr><td class="text-muted">Replies</td><td><?= (int)($ticket['reply_count'] ?? 0) ?></td></tr>
                        <tr><td class="text-muted">Created</td><td><?= date('d M Y', strtotime($ticket['created_at'])) ?></td></tr>
                        <?php if (!empty($ticket['booking_id'])): ?>
                        <tr><td class="text-muted">Booking</td><td>#<?= (int)$ticket['booking_id'] ?></td></tr>
                        <?php endif; ?>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
