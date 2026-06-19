<?php
$page_title = $page_title ?? __('tickets_page_title', [], 'My Support Tickets');
$tickets = $tickets ?? [];
$bookings = $bookings ?? [];
$extraHead = '<style>
    .ticket-card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .ticket-reply { border-left: 3px solid #667eea; padding-left: 15px; margin-bottom: 12px; }
    .ticket-reply.admin { border-left-color: #10b981; }
    .priority-badge { font-size: 0.75rem; }
    .expand-btn { cursor: pointer; }
    .expand-btn .fa-chevron-down { transition: transform 0.2s; }
    .expand-btn[aria-expanded="true"] .fa-chevron-down { transform: rotate(180deg); }
</style>';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="fas fa-headset me-2 text-primary"></i><?php echo __('tickets_heading', [], 'My Support Tickets'); ?></h3>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTicketModal">
            <i class="fas fa-plus me-1"></i><?php echo __('tickets_raise', [], 'Raise Ticket'); ?>
        </button>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <?php if (empty($tickets)): ?>
        <div class="card ticket-card">
            <div class="card-body text-center py-5">
                <i class="fas fa-ticket-alt fa-4x text-muted mb-3"></i>
                <h5 class="text-muted"><?php echo __('tickets_empty_title', [], 'No support tickets yet'); ?></h5>
                <p class="text-muted"><?php echo __('tickets_empty_desc', [], 'Need help? Raise a ticket and our team will get back to you.'); ?></p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTicketModal">
                    <i class="fas fa-plus me-1"></i><?php echo __('tickets_raise_first', [], 'Raise Your First Ticket'); ?>
                </button>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($tickets as $t): 
            $statusClass = match($t['status'] ?? 'open') {
                'open' => 'primary',
                'in_progress' => 'info',
                'resolved' => 'success',
                'closed' => 'secondary',
                default => 'warning'
            };
            $priorityClass = match($t['priority'] ?? 'medium') {
                'high' => 'bg-danger',
                'medium' => 'bg-warning text-dark',
                'low' => 'bg-info',
                default => 'bg-secondary'
            };
        ?>
        <div class="card ticket-card mb-3">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <h6 class="mb-0"><?= htmlspecialchars($t['subject'] ?? 'No Subject') ?></h6>
                            <span class="badge bg-<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $t['status'] ?? 'open')) ?></span>
                            <span class="badge <?= $priorityClass ?> priority-badge"><?= ucfirst($t['priority'] ?? 'medium') ?></span>
                        </div>
                        <p class="text-muted small mb-1">
                            <i class="fas fa-calendar me-1"></i><?= date('d M Y, h:i A', strtotime($t['created_at'])) ?>
                            <?php if (!empty($t['last_reply'])): ?>
                                <span class="ms-3"><i class="fas fa-reply me-1"></i>Last update: <?= date('d M Y', strtotime($t['last_reply_at'] ?? $t['updated_at'])) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($t['booking_id'])): ?>
                                <span class="ms-3"><i class="fas fa-file-invoice me-1"></i>Booking #<?= (int)$t['booking_id'] ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary expand-btn" type="button" data-bs-toggle="collapse" data-bs-target="#ticketReplies<?= $t['id'] ?>" aria-expanded="false">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="collapse" id="ticketReplies<?= $t['id'] ?>">
                <div class="card-body border-top pt-3">
                    <p class="fw-bold mb-2"><?php echo __('tickets_original_message', [], 'Original Message:'); ?></p>
                    <p class="text-muted small mb-3"><?= nl2br(htmlspecialchars($t['message'] ?? '')) ?></p>
                    <?php if (!empty($t['replies'])): ?>
                        <hr>
                        <p class="fw-bold mb-2"><?php echo __('tickets_conversation', [], 'Conversation:'); ?></p>
                        <?php foreach ($t['replies'] as $r): ?>
                            <div class="ticket-reply <?= ($r['is_admin'] ?? 0) ? 'admin' : '' ?>">
                                <div class="d-flex justify-content-between">
                                    <small class="fw-bold"><?= htmlspecialchars($r['user_name'] ?? ($r['is_admin'] ? 'Support Team' : 'You')) ?></small>
                                    <small class="text-muted"><?= date('d M Y, h:i A', strtotime($r['created_at'])) ?></small>
                                </div>
                                <p class="mb-0 small"><?= nl2br(htmlspecialchars($r['message'] ?? '')) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- New Ticket Modal -->
    <div class="modal fade" id="newTicketModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" action="<?= BASE_URL ?>/user/tickets/create">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-primary"></i><?php echo __('tickets_modal_title', [], 'Raise a Support Ticket'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><?php echo __('tickets_subject', [], 'Subject'); ?> <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" required placeholder="<?php echo __('tickets_subject_placeholder', [], 'Brief title of your issue'); ?>">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><?php echo __('tickets_priority', [], 'Priority'); ?></label>
                                <select name="priority" class="form-select">
                                    <option value="low"><?php echo __('tickets_priority_low', [], 'Low'); ?></option>
                                    <option value="medium" selected><?php echo __('tickets_priority_medium', [], 'Medium'); ?></option>
                                    <option value="high"><?php echo __('tickets_priority_high', [], 'High'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo __('tickets_related_booking', [], 'Related Booking (optional)'); ?></label>
                                <select name="booking_id" class="form-select">
                                    <option value=""><?php echo __('tickets_none', [], '-- None --'); ?></option>
                                    <?php foreach ($bookings as $b): ?>
                                        <option value="<?= $b['id'] ?>">#<?= htmlspecialchars($b['plot_number'] ?? $b['id']) ?> - <?= htmlspecialchars($b['colony_name'] ?? '') ?> (<?= ucfirst($b['status'] ?? '') ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo __('tickets_message', [], 'Message'); ?> <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="5" required placeholder="<?php echo __('tickets_message_placeholder', [], 'Describe your issue in detail...'); ?>"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel', [], 'Cancel'); ?></button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i><?php echo __('tickets_submit', [], 'Submit Ticket'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
