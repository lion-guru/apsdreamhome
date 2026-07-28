
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="h2 mb-4">
                    <i class="fas fa-eye"></i> Preview Template
                </h1>
            </div>
            
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Select a template to preview how it will look to recipients.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Email Templates</h5>
                            <div class="list-group">
                                <?php if (!empty($email_logs)): ?>
                                    <?php foreach (array_slice($email_logs, 0, 5) as $log): ?>
                                        <div class="list-group-item list-group-item-action">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($log['subject'] ?? '') ?></h6>
                                                    <small class="text-muted">To: <?= htmlspecialchars($log['recipient'] ?? '') ?></small>
                                                </div>
                                                <span class="badge bg-<?= ($log['status'] ?? '') === 'sent' ? 'success' : 'secondary' ?>"><?= ucfirst(htmlspecialchars($log['status'] ?? '')) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="list-group-item text-center text-muted py-3">
                                        No email templates to preview
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>SMS Templates</h5>
                            <div class="list-group">
                                <?php if (!empty($sms_logs)): ?>
                                    <?php foreach (array_slice($sms_logs, 0, 5) as $log): ?>
                                        <div class="list-group-item list-group-item-action">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars(substr($log['message'] ?? '', 0, 60)) ?>...</h6>
                                                    <small class="text-muted">To: <?= htmlspecialchars($log['recipient'] ?? '') ?></small>
                                                </div>
                                                <span class="badge bg-<?= ($log['status'] ?? '') === 'sent' ? 'success' : 'secondary' ?>"><?= ucfirst(htmlspecialchars($log['status'] ?? '')) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="list-group-item text-center text-muted py-3">
                                        No SMS templates to preview
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
