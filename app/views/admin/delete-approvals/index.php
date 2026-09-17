<?php
/** @var array $approvals */
$approvals = $approvals ?? [];
$isSuper = $isSuper ?? false;
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <h5 class="m-0"><i class="fas fa-shield-alt me-2"></i>Delete Approvals</h5>
        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/bookings" class="btn btn-link btn-sm">Back to Bookings</a>
    </div>
    <div class="aps-cp-card-body">
        <?php if (!$isSuper): ?>
            <div class="alert alert-warning"><i class="fas fa-lock me-1"></i>Only super admins can approve or reject. Your requests appear below once filed.</div>
        <?php endif; ?>
        <?php if (empty($approvals)): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                <p class="mb-0">No pending delete requests. Critical records are safe.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover m-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Record</th>
                            <th>Reason</th>
                            <th>Requested By</th>
                            <th>IP</th>
                            <th>Filed</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approvals as $a): ?>
                            <tr>
                                <td><?= (int)($a['id'] ?? 0) ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars((string)($a['entity_type'] ?? '')) ?></span>
                                    <strong>#<?= (int)($a['entity_id'] ?? 0) ?></strong>
                                    <?php if (!empty($a['entity_label'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars((string)$a['entity_label']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars((string)($a['reason'] ?? '-')) ?></small></td>
                                <td><?= htmlspecialchars((string)($a['requested_by_name'] ?? ('UID ' . ($a['requested_by'] ?? '?')))) ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars((string)($a['request_ip'] ?? '-')) ?></small></td>
                                <td><small><?= htmlspecialchars((string)($a['created_at'] ?? '')) ?></small></td>
                                <td class="text-end">
                                    <?php if ($isSuper): ?>
                                        <form method="POST" action="<?= htmlspecialchars($base ?? '') ?>/admin/delete-approvals/<?= (int)($a['id'] ?? 0) ?>/approve" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                            <button type="submit" class="btn btn-sm btn-success" data-aps-confirm="Approve and PERMANENTLY delete this record?"><i class="fas fa-check me-1"></i>Approve</button>
                                        </form>
                                        <form method="POST" action="<?= htmlspecialchars($base ?? '') ?>/admin/delete-approvals/<?= (int)($a['id'] ?? 0) ?>/reject" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times me-1"></i>Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
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
