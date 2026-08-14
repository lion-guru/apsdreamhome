<?php
$pageTitle = $pageTitle ?? 'Approve Commissions';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$commissions = $commissions ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-check-circle me-2 text-success"></i>Approve Commissions</h1>
        <a href="<?= $base ?>/admin/commission" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Pending Commissions</h6></div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($commissions)): ?>
                <p class="text-muted text-center py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No pending commissions to approve.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Agent</th>
                                <th>Property</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commissions as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['agent_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($c['property_name'] ?? $c['property'] ?? '') ?></td>
                                <td>â‚¹<?= number_format(floatval($c['amount'] ?? 0), 2) ?></td>
                                <td><?= htmlspecialchars($c['created_at'] ?? $c['date'] ?? '') ?></td>
                                <td>
                                    <form method="POST" action="<?= $base ?>/admin/commission/action" class="style-71727">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?? '' ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" onclick="return confirm('Reject this commission?')"><i class="fas fa-times me-1"></i>Reject</button>
                                    </form>
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
