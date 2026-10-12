<?php
$base = rtrim(BASE_URL, '/');
$csrf = $this->getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-qrcode me-2 text-primary"></i><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= $base ?>/admin/gst/dashboard" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Invoice Date</th>
                            <th>IRN</th>
                            <th>Status</th>
                            <th>ACK No</th>
                            <th>ACK Date</th>
                            <th>Error</th>
                            <th>API Endpoint</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="10" class="text-center py-5 text-muted">No e-invoicing logs found</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($log['invoice_number'] ?? '-') ?></strong></td>
                                    <td><?= $log['invoice_date'] ? date('d M Y', strtotime($log['invoice_date'])) : '-' ?></td>
                                    <td><?= htmlspecialchars($log['irn'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $log['status'] === 'success' ? 'success' : 'danger' ?>">
                                            <?= ucfirst($log['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($log['ack_no'] ?? '-') ?></td>
                                    <td><?= $log['ack_date'] ? date('d M Y H:i', strtotime($log['ack_date'])) : '-' ?></td>
                                    <td class="small text-danger"><?= htmlspecialchars(mb_strimwidth($log['error_message'] ?? '', 0, 60, '...')) ?></td>
                                    <td><?= htmlspecialchars($log['api_endpoint'] ?? '-') ?></td>
                                    <td><?= date('d M Y H:i', strtotime($log['created_at'])) ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-info" onclick="showPayload(<?= htmlspecialchars(json_encode($log['request_payload']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($log['response_payload']), ENT_QUOTES) ?>)">View Payload</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="payloadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-code me-2"></i>E-Invoice API Payload</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Request</h6>
                            <pre class="bg-dark text-light p-3 rounded" id="reqPayload" style="max-height:400px;overflow:auto;font-size:.8rem"></pre>
                        </div>
                        <div class="col-md-6">
                            <h6>Response</h6>
                            <pre class="bg-dark text-light p-3 rounded" id="resPayload" style="max-height:400px;overflow:auto;font-size:.8rem"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
<script>
function showPayload(req, res) {
    document.getElementById('reqPayload').textContent = JSON.stringify(req, null, 2);
    document.getElementById('resPayload').textContent = JSON.stringify(res, null, 2);
    new bootstrap.Modal(document.getElementById('payloadModal')).show();
}
</script>
</body>
</html>