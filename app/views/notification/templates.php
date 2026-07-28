
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header d-flex justify-content-between align-items-center">
                <h1 class="h2 mb-0">
                    <i class="fas fa-file-alt"></i> Notification Templates
                </h1>
                <a href="/admin/notification-management/templates/create" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create Template
                </a>
            </div>
            
            <div class="card aps-cp-card mt-3">
                <div class="card-body aps-cp-card-body">
                    <?php include __DIR__ . '/_stat_cards.php'; ?>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Recent Activity</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Channel</th>
                                            <th>Subject/Message</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $allLogs = [];
                                        foreach (($email_logs ?? []) as $log) {
                                            $allLogs[] = ['channel' => 'Email', 'subject' => $log['subject'] ?? '', 'status' => $log['status'] ?? '', 'date' => $log['sent_at'] ?? $log['created_at'] ?? ''];
                                        }
                                        foreach (($sms_logs ?? []) as $log) {
                                            $allLogs[] = ['channel' => 'SMS', 'subject' => $log['message'] ?? '', 'status' => $log['status'] ?? '', 'date' => $log['sent_at'] ?? $log['created_at'] ?? ''];
                                        }
                                        usort($allLogs, function($a, $b) { return strtotime($b['date'] ?? 'now') - strtotime($a['date'] ?? 'now'); });
                                        $allLogs = array_slice($allLogs, 0, 10);
                                        ?>
                                        <?php if (!empty($allLogs)): ?>
                                            <?php foreach ($allLogs as $log): ?>
                                                <tr>
                                                    <td><span class="badge bg-<?= $log['channel'] === 'Email' ? 'primary' : 'success' ?>"><?= $log['channel'] ?></span></td>
                                                    <td><?= htmlspecialchars(substr($log['subject'], 0, 60)) ?><?= strlen($log['subject']) > 60 ? '...' : '' ?></td>
                                                    <td><span class="badge bg-<?= $log['status'] === 'sent' ? 'success' : ($log['status'] === 'failed' ? 'danger' : 'secondary') ?>"><?= ucfirst(htmlspecialchars($log['status'])) ?></span></td>
                                                    <td><?= htmlspecialchars($log['date']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center text-muted">No activity yet</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
