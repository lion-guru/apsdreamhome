<?php
/**
 * Ticket Detail
 */

$page_title = $page_title ?? 'Ticket Details';
$ticket = $ticket ?? [];
$replies = $ticket['replies'] ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold"><i class="fas fa-headset me-2 text-primary"></i>Ticket Details</h4>
                    <p class="text-muted mb-0 small">Ticket: <strong><?= htmlspecialchars($ticket['ticket_number'] ?? 'N/A') ?></strong></p>
                </div>
                <a href="<?= BASE_URL ?>/employee/self-service/helpdesk" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Tickets
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <!-- Ticket Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i><?= htmlspecialchars($ticket['subject'] ?? 'No Subject') ?></h5>
                    <div>
                        <span class="badge bg-<?= $ticket['category'] === 'it' ? 'primary' : ($ticket['category'] === 'hr' ? 'success' : ($ticket['category'] === 'finance' ? 'warning text-dark' : 'secondary')) ?> me-1">
                            <?= ucfirst($ticket['category'] ?? 'general') ?>
                        </span>
                        <span class="badge bg-<?= $ticket['priority'] === 'urgent' ? 'danger' : ($ticket['priority'] === 'high' ? 'warning text-dark' : ($ticket['priority'] === 'medium' ? 'info' : 'secondary')) ?> me-1">
                            <?= ucfirst($ticket['priority'] ?? 'medium') ?>
                        </span>
                        <span class="badge bg-<?= $ticket['status'] === 'open' ? 'primary' : ($ticket['status'] === 'in_progress' ? 'info' : ($ticket['status'] === 'resolved' ? 'success' : 'secondary')) ?> ">
                            <?= ucfirst(str_replace('_', ' ', $ticket['status'] ?? 'open')) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Ticket #:</strong> <code><?= htmlspecialchars($ticket['ticket_number'] ?? 'N/A') ?></code>
                        </div>
                        <div class="col-md-4">
                            <strong>Created:</strong> <?= date('d M Y H:i', strtotime($ticket['created_at'] ?? 'now')) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Updated:</strong> <?= $ticket['updated_at'] ? date('d M Y H:i', strtotime($ticket['updated_at'])) : '-' ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Assigned To:</strong> <?= htmlspecialchars($ticket['assigned_to_name'] ?? 'Unassigned') ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Resolution:</strong> <?= $ticket['resolved_at'] ? date('d M Y', strtotime($ticket['resolved_at'])) : 'Not resolved' ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Description:</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                <?= nl2br(htmlspecialchars($ticket['description'] ?? '')) ?>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($ticket['attachment'])): ?>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Attachment:</strong>
                            <a href="<?= BASE_URL ?>/uploads/tickets/<?= htmlspecialchars($ticket['attachment']) ?>" target="_blank" class="ms-2">
                                <i class="fas fa-paperclip me-1"></i> <?= htmlspecialchars($ticket['attachment']) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Replies / Conversation -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Conversation (<?= count($replies) ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($replies)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-comment-slash fa-2x mb-2"></i>
                            <p>No replies yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="conversation-thread">
                            <?php foreach ($replies as $r): ?>
                            <div class="message-card mb-3 p-3 border rounded <?= $r['is_staff'] ? 'bg-light' : '' ?>">
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <strong><?= htmlspecialchars($r['sender_name'] ?? 'Unknown') ?></strong>
                                        <span class="badge bg-<?= $r['is_staff'] ? 'primary' : 'secondary' ?> ms-2">
                                            <?= $r['is_staff'] ? 'Support Staff' : 'You' ?>
                                        </span>
                                    </div>
                                    <small class="text-muted"><?= date('d M Y H:i', strtotime($r['created_at'] ?? 'now')) ?></small>
                                </div>
                                <div class="message-content">
                                    <?= nl2br(htmlspecialchars($r['message'] ?? '')) ?>
                                </div>
                                <?php if (!empty($r['attachment'])): ?>
                                <div class="mt-2">
                                    <a href="<?= BASE_URL ?>/uploads/tickets/<?= htmlspecialchars($r['attachment']) ?>" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-paperclip me-1"></i> <?= htmlspecialchars($r['attachment']) ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add Reply (if ticket not closed) -->
            <?php if (($ticket['status'] ?? '') !== 'closed'): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-reply me-2"></i>Add Reply</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/employee/self-service/helpdesk/<?= $ticket['id'] ?>/reply" id="addReplyForm" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="row mb-3">
                            <div class="col-12">
                                <textarea class="form-control" name="message" rows="4" required placeholder="Type your reply..."></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Attachment</label>
                            <div class="col-sm-10">
                                <input type="file" class="form-control" name="attachment" accept=".pdf,.doc,.docx,.jpg,.png">
                                <div class="form-text">Max 5MB.</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Send Reply
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Close Ticket (if resolved) -->
            <?php if (($ticket['status'] ?? '') === 'resolved'): ?>
            <div class="card mt-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Resolution</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">If this issue is resolved to your satisfaction, please confirm closure.</p>
                    <form method="POST" action="<?= BASE_URL ?>/employee/self-service/helpdesk/<?= $ticket['id'] ?>/close" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Confirm closing this ticket?')">
                            <i class="fas fa-check-circle me-1"></i> Confirm & Close
                        </button>
                    </form>
                    <a href="<?= BASE_URL ?>/employee/self-service/helpdesk/<?= $ticket['id'] ?>/reopen" class="btn btn-outline-secondary ms-2" onclick="return confirm('Reopen this ticket?')">
                        <i class="fas fa-redo me-1"></i> Reopen
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>