<?php
$audits = $audits ?? [];
$pagination = $pagination ?? ['page' => 1, 'pages' => 1];
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-bolt me-2 text-primary"></i>Energy Audits</h2>
    <a href="<?= BASE_URL ?>/admin/sustainable/audit/form" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New Audit</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($audits)): ?>
            <p class="text-muted text-center py-4">No audits yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Project</th><th>Audit Date</th><th>Auditor</th><th>Score</th><th>Annual kWh</th><th>Solar (kWp)</th><th>CO₂ (t/yr)</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($audits as $a): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($a['project_name'] ?? 'Unnamed') ?></strong><br><small class="text-muted">ID: <?= $a['project_id'] ?? '—' ?></small></td>
                            <td><?= htmlspecialchars($a['audit_date'] ?? '') ?></td>
                            <td><?= htmlspecialchars($a['auditor_name'] ?? '') ?></td>
                            <td><span class="badge bg-<?= (($a['energy_score'] ?? 0) >= 70) ? 'success' : (($a['energy_score'] ?? 0) >= 40 ? 'warning' : 'danger') ?>"><?= $a['energy_score'] ?? '—' ?></span></td>
                            <td><?= number_format($a['annual_kwh'] ?? 0) ?></td>
                            <td><?= $a['solar_capacity_kwp'] ?? '—' ?></td>
                            <td><?= number_format($a['estimated_co2_tonnes_yr'] ?? 0, 1) ?></td>
                            <td><span class="badge bg-secondary"><?= ucfirst($a['status'] ?? 'draft') ?></span></td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/sustainable/audit/form/<?= $a['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="<?= BASE_URL ?>/admin/sustainable/audit/delete/<?= $a['id'] ?>" class="d-inline" onsubmit="return confirm('Delete audit?')">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php if (($pagination['pages'] ?? 1) > 1): ?>
    <div class="card-footer">
        <nav><ul class="pagination justify-content-center mb-0">
            <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>
