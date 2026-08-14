<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><?= htmlspecialchars($page_heading ?? 'Booking Notification Log') ?></h1>
            <p class="text-muted mb-0">Email & SMS notifications sent for booking events</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/notifications" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Notifications
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                            <i class="fas fa-envelope text-primary fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Sent</div>
                            <div class="fs-4 fw-bold"><?= number_format($stats['total'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="fas fa-envelope-open text-success fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Email Sent</div>
                            <div class="fs-4 fw-bold"><?= number_format($stats['email_sent'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                            <i class="fas fa-sms text-info fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">SMS Sent</div>
                            <div class="fs-4 fw-bold"><?= number_format($stats['sms_sent'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                            <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Failed</div>
                            <div class="fs-4 fw-bold text-danger"><?= number_format($stats['failed'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/notifications/booking-log" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="booking_confirmation" <?= ($filters['type'] ?? '') === 'booking_confirmation' ? 'selected' : '' ?>>Booking Confirmation</option>
                        <option value="payment_receipt" <?= ($filters['type'] ?? '') === 'payment_receipt' ? 'selected' : '' ?>>Payment Receipt</option>
                        <option value="status_change" <?= ($filters['type'] ?? '') === 'status_change' ? 'selected' : '' ?>>Status Change</option>
                        <option value="demand_letter" <?= ($filters['type'] ?? '') === 'demand_letter' ? 'selected' : '' ?>>Demand Letter</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Channel</label>
                    <select name="channel" class="form-select form-select-sm">
                        <option value="">All Channels</option>
                        <option value="email" <?= ($filters['channel'] ?? '') === 'email' ? 'selected' : '' ?>>Email</option>
                        <option value="sms" <?= ($filters['channel'] ?? '') === 'sms' ? 'selected' : '' ?>>SMS</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="sent" <?= ($filters['status'] ?? '') === 'sent' ? 'selected' : '' ?>>Sent</option>
                        <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search me-1"></i>Filter</button>
                    <a href="<?= BASE_URL ?>/admin/notifications/booking-log" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Log Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (!empty($logs)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 ps-3">Date & Time</th>
                                <th class="border-0">Customer</th>
                                <th class="border-0">Type</th>
                                <th class="border-0">Channel</th>
                                <th class="border-0">Status</th>
                                <th class="border-0">Subject</th>
                                <th class="border-0 pe-3">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <?php
                                    $typeBadge = match($log['related_entity_type'] ?? '') {
                                        'booking_confirmation' => 'bg-primary',
                                        'payment_receipt' => 'bg-success',
                                        'status_change' => 'bg-info',
                                        'demand_letter' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    $typeLabel = ucwords(str_replace('_', ' ', $log['related_entity_type'] ?? 'unknown'));
                                    $channelIcon = ($log['channel'] ?? '') === 'email' ? 'fa-envelope' : 'fa-sms';
                                    $statusBadge = ($log['status'] ?? '') === 'sent' ? 'bg-success' : 'bg-danger';
                                ?>
                                <tr>
                                    <td class="ps-3 small text-muted">
                                        <?= date('d M Y', strtotime($log['created_at'] ?? '')) ?>
                                        <br><small><?= date('h:i A', strtotime($log['created_at'] ?? '')) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($log['user_name'] ?? 'N/A') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($log['user_email'] ?? '') ?></small>
                                    </td>
                                    <td><span class="badge <?= $typeBadge ?>"><?= $typeLabel ?></span></td>
                                    <td><i class="fas <?= $channelIcon ?> me-1"></i><?= ucfirst($log['channel'] ?? '') ?></td>
                                    <td><span class="badge <?= $statusBadge ?>"><?= ucfirst($log['status'] ?? '') ?></span></td>
                                    <td class="small"><?= htmlspecialchars(mb_substr($log['subject'] ?? '', 0, 40)) ?></td>
                                    <td class="pe-3">
                                        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal<?= $log['id'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Detail Modal -->
                                <div class="modal fade" id="detailModal<?= $log['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6 class="modal-title">Notification Detail</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <table class="table table-sm">
                                                    <tr><td class="text-muted" class="style-72730">Customer</td><td><?= htmlspecialchars($log['user_name'] ?? '') ?> (<?= htmlspecialchars($log['user_email'] ?? '') ?>)</td></tr>
                                                    <tr><td class="text-muted">Phone</td><td><?= htmlspecialchars($log['user_phone'] ?? 'N/A') ?></td></tr>
                                                    <tr><td class="text-muted">Channel</td><td><?= ucfirst($log['channel'] ?? '') ?></td></tr>
                                                    <tr><td class="text-muted">Type</td><td><?= $typeLabel ?></td></tr>
                                                    <tr><td class="text-muted">Status</td><td><span class="badge <?= $statusBadge ?>"><?= ucfirst($log['status'] ?? '') ?></span></td></tr>
                                                    <tr><td class="text-muted">Subject</td><td><?= htmlspecialchars($log['subject'] ?? '') ?></td></tr>
                                                    <tr><td class="text-muted">Sent At</td><td><?= $log['sent_at'] ? date('d M Y h:i A', strtotime($log['sent_at'])) : 'N/A' ?></td></tr>
                                                </table>
                                                <div class="mt-2">
                                                    <label class="form-label small text-muted">Message Content</label>
                                                    <div class="bg-light p-3 rounded small" class="style-86734"><?= htmlspecialchars($log['message'] ?? '') ?></div>
                                                </div>
                                                <?php if (!empty($log['error_message'])): ?>
                                                    <div class="alert alert-danger mt-2 mb-0 small">
                                                        <strong>Error:</strong> <?= htmlspecialchars($log['error_message']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No notification logs found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
