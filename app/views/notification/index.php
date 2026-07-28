
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="h2 mb-4">
                    <i class="fas fa-bell"></i> Notification Dashboard
                </h1>
            </div>
            
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <?php include __DIR__ . '/_stat_cards.php'; ?>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Recent Email Logs</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Recipient</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Sent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($email_logs)): ?>
                                            <?php foreach ($email_logs as $log): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($log['recipient'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($log['subject'] ?? '') ?></td>
                                                    <td><span class="badge bg-<?= ($log['status'] ?? '') === 'sent' ? 'success' : (($log['status'] ?? '') === 'failed' ? 'danger' : 'secondary') ?>"><?= ucfirst(htmlspecialchars($log['status'] ?? 'pending')) ?></span></td>
                                                    <td><?= htmlspecialchars($log['sent_at'] ?? $log['created_at'] ?? '') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center text-muted">No email logs yet</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>Recent SMS Logs</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Recipient</th>
                                            <th>Message</th>
                                            <th>Status</th>
                                            <th>Sent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($sms_logs)): ?>
                                            <?php foreach ($sms_logs as $log): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($log['recipient'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($log['message'] ?? '') ?></td>
                                                    <td><span class="badge bg-<?= ($log['status'] ?? '') === 'sent' ? 'success' : (($log['status'] ?? '') === 'failed' ? 'danger' : 'secondary') ?>"><?= ucfirst(htmlspecialchars($log['status'] ?? 'pending')) ?></span></td>
                                                    <td><?= htmlspecialchars($log['sent_at'] ?? $log['created_at'] ?? '') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center text-muted">No SMS logs yet</td></tr>
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
