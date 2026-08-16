ï»¿<?php $page_title = $page_title ?? 'SLA Rules'; $rules = $rules ?? []; ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-clock me-2 text-primary"></i>SLA Rules</h4>
        <a href="<?= BASE_URL ?>/admin/crm/sla" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
    </div>
    <div class="card border-0 shadow-sm" class="style-56956"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Name</th><th>Type</th><th>Target</th><th>Applies To (Roles)</th><th>Stages</th><th>Status</th></tr></thead><tbody>
    <?php if (empty($rules)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No SLA rules configured</td></tr>
    <?php else: foreach ($rules as $r): ?><tr><td class="fw-bold"><?= htmlspecialchars($r['name'] ?? '') ?></td><td><span class="badge bg-primary"><?= $r['rule_type'] ?></span></td><td><?= $r['target_minutes'] ?> minutes</td><td><small><?= htmlspecialchars($r['applies_to_roles'] ?? '') ?></small></td><td><small><?= htmlspecialchars($r['applies_to_stages'] ?? '') ?></small></td><td><span class="badge bg-<?= $r['is_active'] ? 'success' : 'secondary' ?>"><?= $r['is_active'] ? 'Active' : 'Inactive' ?></span></td></tr><?php endforeach; endif; ?>
    </tbody></table></div></div></div>
</div>
