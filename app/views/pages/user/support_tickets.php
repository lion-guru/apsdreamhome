<?php
$page_title = $page_title ?? __('user_support_tickets_page_title', 'My Support Tickets');
$tickets = $tickets ?? [];
$userStats = $userStats ?? ['total' => 0, 'open' => 0, 'in_progress' => 0, 'resolved' => 0];
$total = $total ?? 0;
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
$currentStatus = $status ?? null;
$currentCategory = $category ?? null;
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-headset me-2"></i><?= __('user_support_tickets_heading', 'My Support Tickets') ?></h2>
            <p><?= __('user_support_tickets_subtitle', 'Track and manage your support requests with our team.') ?></p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/support/create" class="btn btn-light">
                <i class="fas fa-plus me-2"></i><?= __('user_support_tickets_new_ticket', 'New Ticket') ?>
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

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="aps-cp-card text-center p-3">
            <div class="fw-bold fs-3 text-primary" data-aps-count="<?= $userStats['total'] ?>">0</div>
            <small class="text-muted"><?= __('user_support_tickets_total_tickets', 'Total Tickets') ?></small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aps-cp-card text-center p-3">
            <div class="fw-bold fs-3 text-danger" data-aps-count="<?= $userStats['open'] ?>">0</div>
            <small class="text-muted"><?= __('user_support_tickets_status_open', 'Open') ?></small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aps-cp-card text-center p-3">
            <div class="fw-bold fs-3 text-info" data-aps-count="<?= $userStats['in_progress'] ?>">0</div>
            <small class="text-muted"><?= __('user_support_tickets_status_in_progress', 'In Progress') ?></small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aps-cp-card text-center p-3">
            <div class="fw-bold fs-3 text-success" data-aps-count="<?= $userStats['resolved'] ?>">0</div>
            <small class="text-muted"><?= __('user_support_tickets_status_resolved', 'Resolved') ?></small>
        </div>
    </div>
</div>

<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i><?= __('user_support_tickets_all_tickets', 'All Tickets') ?> (<?= $total ?>)</h5>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" class="style-19078" onchange="filterTickets('status', this.value)">
                <option value=""><?= __('user_support_tickets_all_status', 'All Status') ?></option>
                <option value="open" <?= $currentStatus === 'open' ? 'selected' : '' ?>><?= __('user_support_tickets_filter_open', 'Open') ?></option>
                <option value="in_progress" <?= $currentStatus === 'in_progress' ? 'selected' : '' ?>><?= __('user_support_tickets_filter_in_progress', 'In Progress') ?></option>
                <option value="waiting_customer" <?= $currentStatus === 'waiting_customer' ? 'selected' : '' ?>><?= __('user_support_tickets_filter_awaiting_reply', 'Awaiting Reply') ?></option>
                <option value="resolved" <?= $currentStatus === 'resolved' ? 'selected' : '' ?>><?= __('user_support_tickets_filter_resolved', 'Resolved') ?></option>
                <option value="closed" <?= $currentStatus === 'closed' ? 'selected' : '' ?>><?= __('user_support_tickets_filter_closed', 'Closed') ?></option>
            </select>
            <select class="form-select form-select-sm" class="style-19078" onchange="filterTickets('category', this.value)">
                <option value=""><?= __('user_support_tickets_all_categories', 'All Categories') ?></option>
                <option value="general" <?= $currentCategory === 'general' ? 'selected' : '' ?>><?= __('user_support_tickets_category_general', 'General') ?></option>
                <option value="payment" <?= $currentCategory === 'payment' ? 'selected' : '' ?>><?= __('user_support_tickets_category_payment', 'Payment') ?></option>
                <option value="booking" <?= $currentCategory === 'booking' ? 'selected' : '' ?>><?= __('user_support_tickets_category_booking', 'Booking') ?></option>
                <option value="legal" <?= $currentCategory === 'legal' ? 'selected' : '' ?>><?= __('user_support_tickets_category_legal', 'Legal') ?></option>
                <option value="technical" <?= $currentCategory === 'technical' ? 'selected' : '' ?>><?= __('user_support_tickets_category_technical', 'Technical') ?></option>
                <option value="complaint" <?= $currentCategory === 'complaint' ? 'selected' : '' ?>><?= __('user_support_tickets_category_complaint', 'Complaint') ?></option>
                <option value="other" <?= $currentCategory === 'other' ? 'selected' : '' ?>><?= __('user_support_tickets_category_other', 'Other') ?></option>
            </select>
        </div>
    </div>
    <div class="aps-cp-card-body p-0">
        <?php if (empty($tickets)): ?>
            <div class="aps-cp-empty py-5">
                <div class="aps-cp-empty-icon"><i class="fas fa-ticket-alt"></i></div>
                <h5><?= __('user_support_tickets_empty_heading', 'No support tickets found') ?></h5>
                <p><?= __('user_support_tickets_empty_description', 'Create a ticket and our team will assist you.') ?></p>
                <a href="<?= BASE_URL ?>/user/support/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i><?= __('user_support_tickets_create_first', 'Create Your First Ticket') ?>
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="aps-cp-table">
                    <thead>
                        <tr>
                            <th><?= __('user_support_tickets_col_ticket_number', 'Ticket #') ?></th>
                            <th><?= __('user_support_tickets_col_subject', 'Subject') ?></th>
                            <th><?= __('user_support_tickets_col_category', 'Category') ?></th>
                            <th><?= __('user_support_tickets_col_priority', 'Priority') ?></th>
                            <th><?= __('user_support_tickets_col_status', 'Status') ?></th>
                            <th><?= __('user_support_tickets_col_replies', 'Replies') ?></th>
                            <th><?= __('user_support_tickets_col_created', 'Created') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t):
                            $statusBadge = match($t['status'] ?? 'open') {
                                'open' => 'success',
                                'in_progress' => 'primary',
                                'waiting_customer' => 'warning',
                                'resolved' => 'secondary',
                                'closed' => 'danger',
                                default => 'secondary'
                            };
                            $priorityBadge = match($t['priority'] ?? 'medium') {
                                'urgent' => 'danger',
                                'high' => 'warning',
                                'medium' => 'primary',
                                'low' => 'secondary',
                                default => 'secondary'
                            };
                        ?>
                        <tr class="style-75920" onclick="window.location='<?= BASE_URL ?>/user/support/<?= (int)$t['id'] ?>'">
                            <td><strong><?= htmlspecialchars($t['ticket_number'] ?? 'T' . $t['id']) ?></strong></td>
                            <td><?= htmlspecialchars($t['subject'] ?? '') ?></td>
                            <td><span class="badge bg-light text-dark"><?= ucfirst($t['category'] ?? 'general') ?></span></td>
                            <td><span class="badge bg-<?= $priorityBadge ?>"><?= ucfirst($t['priority'] ?? 'medium') ?></span></td>
                            <td><span class="badge bg-<?= $statusBadge ?>"><?= str_replace('_', ' ', ucfirst($t['status'] ?? 'open')) ?></span></td>
                            <td><?= (int)($t['reply_count'] ?? 0) ?></td>
                            <td><small><?= date('d M Y', strtotime($t['created_at'])) ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="d-flex justify-content-center py-3">
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/user/support?page=<?= $i ?>&status=<?= urlencode($currentStatus ?? '') ?>&category=<?= urlencode($currentCategory ?? '') ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function filterTickets(type, value) {
    var params = new URLSearchParams(window.location.search);
    if (value) {
        params.set(type, value);
    } else {
        params.delete(type);
    }
    params.delete('page');
    window.location = '<?= BASE_URL ?>/user/support?' + params.toString();
}
</script>
