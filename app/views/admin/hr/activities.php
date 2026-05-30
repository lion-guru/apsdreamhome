<?php
$page_title = $page_title ?? 'Employee Activities';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-history me-2"></i>Employee Activities</h4>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Employee</th><th>Activity</th><th>Type</th><th>IP Address</th><th>Date/Time</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($activities ?? [])): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No activities recorded</td></tr>
                    <?php else: ?>
                        <?php foreach ($activities as $a): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($a['employee_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($a['activity'] ?? '') ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($a['activity_type'] ?? '') ?></span></td>
                                <td><code><?= htmlspecialchars($a['ip_address'] ?? '-') ?></code></td>
                                <td><?= htmlspecialchars($a['created_at'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($total_pages ?? 1) > 1): ?>
        <div class="card-footer bg-white">
            <nav><ul class="pagination pagination-sm mb-0 justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === ($page ?? 1) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    <?php endif; ?>
</div>
