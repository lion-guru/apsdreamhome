
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="h2 mb-4">
                    <i class="fas fa-cog"></i> Notification Settings
                </h1>
            </div>
            
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <?php include __DIR__ . '/_stat_cards.php'; ?>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Email Configuration</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Template</th>
                                            <th>Status</th>
                                            <th>Last Used</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($email_logs)): ?>
                                            <?php foreach (array_slice($email_logs, 0, 5) as $log): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($log['subject'] ?? '') ?></td>
                                                    <td><span class="badge bg-<?= ($log['status'] ?? '') === 'sent' ? 'success' : 'secondary' ?>"><?= ucfirst(htmlspecialchars($log['status'] ?? '')) ?></span></td>
                                                    <td><?= htmlspecialchars($log['sent_at'] ?? $log['created_at'] ?? '') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center text-muted">No email activity yet</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>SMS Configuration</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Template</th>
                                            <th>Status</th>
                                            <th>Last Used</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($sms_logs)): ?>
                                            <?php foreach (array_slice($sms_logs, 0, 5) as $log): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars(substr($log['message'] ?? '', 0, 50)) ?>...</td>
                                                    <td><span class="badge bg-<?= ($log['status'] ?? '') === 'sent' ? 'success' : 'secondary' ?>"><?= ucfirst(htmlspecialchars($log['status'] ?? '')) ?></span></td>
                                                    <td><?= htmlspecialchars($log['sent_at'] ?? $log['created_at'] ?? '') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center text-muted">No SMS activity yet</td></tr>
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
