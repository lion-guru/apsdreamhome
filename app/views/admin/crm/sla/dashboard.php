<?php $page_title = $page_title ?? 'SLA Dashboard'; $stats = $stats ?? []; $pending = $pending ?? []; $breached = $breached ?? []; $rules = $rules ?? []; ?>
<style>.sla-stat{background:#fff;border-radius:14px;border:1px solid #f0f0f5;padding:20px;text-align:center;transition:.3s}.sla-stat:hover{box-shadow:0 8px 24px rgba(0,0,0,.08)}.sla-stat .stat-val{font-size:32px;font-weight:800}.sla-stat .stat-label{font-size:12px;color:#888;text-transform:uppercase;letter-spacing:1px}</style>

<div class="container-fluid px-4 py-4">
    <h4 class="fw-bold mb-4"><i class="fas fa-clock me-2 text-primary"></i>SLA Dashboard</h4>

    <div class="row g-3 mb-4">
        <div class="col-md-2"><div class="sla-stat"><div class="stat-val text-primary"><?= $stats['total'] ?? 0 ?></div><div class="stat-label">Total SLAs</div></div></div>
        <div class="col-md-2"><div class="sla-stat"><div class="stat-val text-success"><?= $stats['met'] ?? 0 ?></div><div class="stat-label">Met</div></div></div>
        <div class="col-md-2"><div class="sla-stat"><div class="stat-val text-warning"><?= $stats['missed'] ?? 0 ?></div><div class="stat-label">Missed</div></div></div>
        <div class="col-md-2"><div class="sla-stat"><div class="stat-val text-danger"><?= $stats['breached'] ?? 0 ?></div><div class="stat-label">Breached</div></div></div>
        <div class="col-md-2"><div class="sla-stat"><div class="stat-val text-info"><?= $stats['pending'] ?? 0 ?></div><div class="stat-label">Pending</div></div></div>
        <div class="col-md-2"><div class="sla-stat"><div class="stat-val" style="color:#667eea"><?= $stats['compliance_rate'] ?? 0 ?>%</div><div class="stat-label">Compliance</div></div></div>
    </div>

    <?php if (!empty($breached)): ?>
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px"><div class="card-header bg-danger text-white" style="border-radius:14px 14px 0 0"><h6 class="mb-0"><i class="fas fa-exclamation-triangle me-1"></i>Recent Breaches</h6></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Rule</th><th>Lead</th><th>Target</th><th>Actual</th><th>Status</th><th>Time</th></tr></thead><tbody>
        <?php foreach ($breached as $b): ?><tr><td><?= htmlspecialchars($b['rule_name']) ?></td><td><?= htmlspecialchars($b['lead_name'] ?? '-') ?></td><td><?= $b['target_minutes'] ?>m</td><td><?= $b['response_time_seconds'] ? round($b['response_time_seconds']/60, 1).'m' : '-' ?></td><td><span class="badge bg-danger"><?= $b['status'] ?></span></td><td><?= date('d M H:i', strtotime($b['created_at'])) ?></td></tr><?php endforeach; ?>
        </tbody></table></div></div>
    </div>
    <?php endif; ?>

    <?php if (!empty($pending)): ?>
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px"><div class="card-header bg-info text-white" style="border-radius:14px 14px 0 0"><h6 class="mb-0"><i class="fas fa-hourglass-half me-1"></i>Pending SLAs (<?= count($pending) ?>)</h6></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Rule</th><th>Lead</th><th>Target</th><th>Elapsed</th><th>Started</th></tr></thead><tbody>
        <?php foreach (array_slice($pending, 0, 20) as $p): ?><tr class="<?= $p['elapsed_minutes'] > $p['target_minutes'] ? 'table-danger' : '' ?>"><td><?= htmlspecialchars($p['rule_name']) ?></td><td><?= htmlspecialchars($p['lead_name'] ?? '-') ?></td><td><?= $p['target_minutes'] ?>m</td><td><?= $p['elapsed_minutes'] ?>m</td><td><?= date('d M H:i', strtotime($p['started_at'])) ?></td></tr><?php endforeach; ?>
        </tbody></table></div></div>
    </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius:14px"><div class="card-header" style="border-radius:14px 14px 0 0"><h6 class="mb-0"><i class="fas fa-cog me-1"></i>Active Rules</h6></div>
                <div class="card-body"><table class="table table-sm mb-0"><thead><tr><th>Name</th><th>Type</th><th>Target</th><th>Roles</th><th>Status</th></tr></thead><tbody>
                <?php foreach ($rules as $r): ?><tr><td><?= htmlspecialchars($r['name']) ?></td><td><span class="badge bg-primary"><?= $r['rule_type'] ?></span></td><td><?= $r['target_minutes'] ?>m</td><td><small><?= htmlspecialchars($r['applies_to_roles']) ?></small></td><td><span class="badge bg-<?= $r['is_active'] ? 'success' : 'secondary' ?>"><?= $r['is_active'] ? 'Active' : 'Off' ?></span></td></tr><?php endforeach; ?>
                </tbody></table></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius:14px"><div class="card-header" style="border-radius:14px 14px 0 0"><h6 class="mb-0"><i class="fas fa-plus me-1"></i>Add Rule</h6></div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/crm/sla/rules/store"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-2"><input type="text" name="name" class="form-control form-control-sm" placeholder="Rule name" required></div>
                        <div class="mb-2"><select name="rule_type" class="form-select form-select-sm"><option value="first_response">First Response</option><option value="resolution">Resolution</option><option value="follow_up">Follow-up</option></select></div>
                        <div class="mb-2"><input type="number" name="target_minutes" class="form-control form-control-sm" placeholder="Target minutes" value="60" required></div>
                        <div class="mb-2"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">Active</label></div></div>
                        <button class="btn btn-primary btn-sm w-100"><i class="fas fa-save me-1"></i>Create Rule</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
