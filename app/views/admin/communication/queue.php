<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active">Communication Queue</li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show">
            <?= $_SESSION['flash_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-tasks me-2"></i>Communication Queue</h4>
        <div>
            <a href="<?= BASE_URL ?>admin/communication/test-email" class="btn btn-outline-primary btn-sm me-2"><i class="fas fa-envelope me-1"></i>Test Email</a>
            <a href="<?= BASE_URL ?>admin/communication/test-sms" class="btn btn-outline-success btn-sm me-2"><i class="fas fa-sms me-1"></i>Test SMS</a>
            <form method="post" action="<?= BASE_URL ?>admin/communication/process-queue" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="email_limit" value="20">
                <input type="hidden" name="sms_limit" value="20">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-play me-1"></i>Process Queue</button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Pending Emails</h6>
                    <h3 class="mb-0"><?= $stats['email']['pending'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <h6>Sent Emails</h6>
                    <h3 class="mb-0"><?= $stats['email']['sent'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <h6>Pending SMS</h6>
                    <h3 class="mb-0"><?= $stats['sms']['pending'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body text-center">
                    <h6>Failed</h6>
                    <h3 class="mb-0"><?= ($stats['email']['failed'] ?? 0) + ($stats['sms']['failed'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Queue</h6>
                    <div>
                        <a href="?email_status=pending" class="btn btn-sm <?= $email_filter === 'pending' ? 'btn-primary' : 'btn-outline-secondary' ?>">Pending</a>
                        <a href="?email_status=sent" class="btn btn-sm <?= $email_filter === 'sent' ? 'btn-primary' : 'btn-outline-secondary' ?>">Sent</a>
                        <a href="?email_status=failed" class="btn btn-sm <?= $email_filter === 'failed' ? 'btn-primary' : 'btn-outline-secondary' ?>">Failed</a>
                        <a href="?" class="btn btn-sm btn-outline-secondary">All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>To</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Attempts</th>
                                    <th>Created</th>
                                    <th>Sent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($emails)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No email queue items</td></tr>
                                <?php else: ?>
                                <?php foreach ($emails as $e): ?>
                                <tr>
                                    <td><?= $e['id'] ?></td>
                                    <td><?= htmlspecialchars($e['to_email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(mb_substr($e['subject'] ?? '', 0, 50)) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $e['status'] === 'sent' ? 'success' : ($e['status'] === 'failed' ? 'danger' : ($e['status'] === 'processing' ? 'warning' : 'secondary')) ?>">
                                            <?= $e['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= $e['attempts'] ?></td>
                                    <td><?= $e['created_at'] ? date('d M H:i', strtotime($e['created_at'])) : '-' ?></td>
                                    <td><?= $e['sent_at'] ? date('d M H:i', strtotime($e['sent_at'])) : '-' ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-sms me-2"></i>SMS Queue</h6>
                    <div>
                        <a href="?sms_status=pending" class="btn btn-sm <?= $sms_filter === 'pending' ? 'btn-primary' : 'btn-outline-secondary' ?>">Pending</a>
                        <a href="?sms_status=sent" class="btn btn-sm <?= $sms_filter === 'sent' ? 'btn-primary' : 'btn-outline-secondary' ?>">Sent</a>
                        <a href="?sms_status=failed" class="btn btn-sm <?= $sms_filter === 'failed' ? 'btn-primary' : 'btn-outline-secondary' ?>">Failed</a>
                        <a href="?" class="btn btn-sm btn-outline-secondary">All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Phone</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Attempts</th>
                                    <th>Created</th>
                                    <th>Sent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sms_items)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No SMS queue items</td></tr>
                                <?php else: ?>
                                <?php foreach ($sms_items as $s): ?>
                                <tr>
                                    <td><?= $s['id'] ?></td>
                                    <td><?= htmlspecialchars($s['recipient'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(mb_substr($s['message'] ?? '', 0, 60)) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $s['status'] === 'sent' ? 'success' : ($s['status'] === 'failed' ? 'danger' : 'secondary') ?>">
                                            <?= $s['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= $s['attempts'] ?></td>
                                    <td><?= $s['created_at'] ? date('d M H:i', strtotime($s['created_at'])) : '-' ?></td>
                                    <td><?= $s['sent_at'] ? date('d M H:i', strtotime($s['sent_at'])) : '-' ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
