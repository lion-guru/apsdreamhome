<?php

$page_title = 'Withdrawal Requests';
$requests = $requests ?? [];
$stats = $stats ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-money-bill-wave me-2"></i>Withdrawal Requests</h1>
            <p class="text-muted">Manage associate commission withdrawal requests</p>
        </div>
    </div>



    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0"><?= intval($stats['total'] ?? 0) ?></h5>
                    <small>Total</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0"><?= intval($stats['approved'] ?? 0) ?></h5>
                    <small>Approved</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0"><?= intval($stats['rejected'] ?? 0) ?></h5>
                    <small>Rejected</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0"><?= intval($stats['pending'] ?? 0) ?></h5>
                    <small>Pending</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0"><?= intval($stats['processed'] ?? 0) ?></h5>
                    <small>Processed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Withdrawals Table -->
    <div class="card aps-cp-card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Withdrawal Requests</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($requests)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No withdrawal requests yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Associate</th>
                                <th>Amount (₹)</th>
                                <th>Balance (₹)</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Request Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $r): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['associate_name'] ?? 'N/A') ?></strong></td>
                                <td>₹<?= number_format(floatval($r['amount'] ?? 0), 2) ?></td>
                                <td>₹<?= number_format(floatval($r['available_balance'] ?? 0), 2) ?></td>
                                <td>
                                    <span class="badge bg-info"><?= ucfirst(str_replace('_', ' ', htmlspecialchars($r['payment_method'] ?? ''))) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $status = $r['status'] ?? 'pending';
                                    $badge = match($status) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'processed' => 'primary',
                                        default => 'warning'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $badge ?>"><?= ucfirst(htmlspecialchars($status ?? '')) ?></span>
                                </td>
                                <td><?= htmlspecialchars($r['request_date'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if ($status === 'pending'): ?>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-success" title="Approve"
                                                onclick="updateStatus(<?= $r['id'] ?>, 'approved')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" title="Reject"
                                                onclick="updateStatus(<?= $r['id'] ?>, 'rejected')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <?php elseif ($status === 'approved'): ?>
                                    <button type="button" class="btn btn-sm btn-primary"
                                            onclick="updateStatus(<?= $r['id'] ?>, 'processed')">
                                        <i class="fas fa-check-double me-1"></i>Mark Processed
                                    </button>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<form method="POST" id="statusForm" class="style-24280">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="status" id="statusInput">
    <input type="hidden" name="admin_notes" id="notesInput">
</form>

<script>
function updateStatus(id, status) {
    apsConfirm('Are you sure you want to ' + status + ' this withdrawal request?').then(function(ok) {
        if (!ok) return;
    document.getElementById('statusInput').value = status;
    document.getElementById('notesInput').value = prompt('Admin notes (optional):') || '';
    });
    var form = document.getElementById('statusForm');
    form.action = '<?= BASE_URL ?>/admin/mlm/withdrawals/update-status/' + id;
    form.submit();
}
</script>
