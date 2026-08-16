<?php
$automations = $automations ?? [];
$pagination = $pagination ?? ['page'=>1,'pages'=>1];
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-robot me-2 text-success"></i>IoT Automations</h2>
    <a href="<?= BASE_URL ?>/admin/iot/automation/form" class="btn btn-success"><i class="fas fa-plus me-1"></i> New Automation</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($automations)): ?>
            <p class="text-muted text-center py-4">No automations yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Device</th><th>Trigger</th><th>Action</th><th>Last Triggered</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($automations as $a): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($a['name'] ?? '') ?></strong></td>
                            <td><?= htmlspecialchars($a['device_name'] ?? 'Any') ?></td>
                            <td><span class="badge bg-light text-dark"><?= ucfirst($a['trigger_type']) ?></span></td>
                            <td><span class="badge bg-info"><?= ucfirst($a['action_type']) ?></span></td>
                            <td><small><?= !empty($a['last_triggered_at']) ? date('M d, H:i', strtotime($a['last_triggered_at'])) : '—' ?></small></td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/iot/automation/form/<?= $a['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="<?= BASE_URL ?>/admin/iot/automation/delete/<?= $a['id'] ?>" class="d-inline" onsubmit="return confirm('Delete automation?')"><input type="hidden" name="csrf_token" value="<?= $csrf ?>"><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php if (($pagination['pages'] ?? 1) > 1): ?>
    <div class="card-footer"><nav><ul class="pagination justify-content-center mb-0">
        <?php for ($i=1;$i<=$pagination['pages'];$i++): ?><li class="page-item <?= $i===$pagination['page']?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li><?php endfor; ?>
    </ul></nav></div>
    <?php endif; ?>
</div>
