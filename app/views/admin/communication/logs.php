<?php

/**
 * Communication Logs
 */
$page_title = $page_title ?? 'Communication Logs';
$logs = $logs ?? [];
$channels = $channels ?? [];
$pagination = $pagination ?? ['page' => 1, 'total_pages' => 1, 'total' => 0, 'per_page' => 50];
$filters = $filters ?? ['channel' => '', 'direction' => '', 'date_from' => '', 'date_to' => ''];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1"><i class="fas fa-history me-2 text-primary"></i>Communication Logs</h1>
                <p class="text-muted">View all inbound and outbound messages across channels</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-3">
                    <label class="form-label">Channel</label>
                    <select name="channel" class="form-select">
                        <option value="">All Channels</option>
                        <?php foreach ($channels as $ch): ?>
                            <option value="<?= htmlspecialchars($ch ?? '') ?>" <?= $filters['channel'] === $ch ? 'selected' : '' ?>><?= ucfirst($ch) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Direction</label>
                    <select name="direction" class="form-select">
                        <option value="">All</option>
                        <option value="inbound" <?= $filters['direction'] === 'inbound' ? 'selected' : '' ?>>Inbound</option>
                        <option value="outbound" <?= $filters['direction'] === 'outbound' ? 'selected' : '' ?>>Outbound</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="<?= BASE_URL ?>/admin/communication/logs" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i> Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title h6 fw-bold mb-0">Logs (<?= number_format($pagination['total']) ?> total)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($logs)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x mb-3" class="style-39608"></i>
                    <h5 class="text-muted">No logs found</h5>
                    <p class="text-muted mb-0">Try adjusting your filters or wait for messages to arrive.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>Channel</th>
                                <th>Direction</th>
                                <th>Contact</th>
                                <th>Message</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><small class="text-muted"><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></small></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $log['channel'] === 'whatsapp' ? 'success' : 
                                            ($log['channel'] === 'telegram' ? 'primary' : 
                                            ($log['channel'] === 'sms' ? 'warning' : 'info')) ?>">
                                            <i class="fa-<?= 
                                                $log['channel'] === 'whatsapp' ? 'fab fa-whatsapp' : 
                                                ($log['channel'] === 'telegram' ? 'fab fa-telegram' : 
                                                ($log['channel'] === 'sms' ? 'fas fa-sms' : 'fas fa-envelope')) ?> me-1"></i>
                                            <?= ucfirst($log['channel']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $log['direction'] === 'inbound' ? 'info' : 'success' ?>">
                                            <i class="fas fa-<?= $log['direction'] === 'inbound' ? 'arrow-down' : 'arrow-up' ?> me-1"></i>
                                            <?= ucfirst($log['direction']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($log['contact_identifier'] ?? '') ?></strong>
                                        <?php if (!empty($log['message_id'])): ?>
                                            <br><small class="text-muted">ID: <?= htmlspecialchars($log['message_id'] ?? '') ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="text-truncate" class="style-33818">
                                            <small class="text-muted"><?= htmlspecialchars(mb_strimwidth($log['message_text'], 0, 100, '...')) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $log['status'] === 'sent' ? 'success' : 
                                            ($log['status'] === 'delivered' ? 'primary' : 
                                            ($log['status'] === 'read' ? 'info' : 'danger')) ?>">
                                            <?= ucfirst($log['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="card-footer bg-white border-0">
                        <nav aria-label="Logs pagination">
                            <ul class="pagination pagination-sm mb-0 justify-content-center">
                                <?php if ($pagination['page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['page'] - 1])) ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php 
                                $start = max(1, $pagination['page'] - 2);
                                $end = min($pagination['total_pages'], $pagination['page'] + 2);
                                for ($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['page'] + 1])) ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>