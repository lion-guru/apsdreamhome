<?php $page_title = $page_title ?? 'SLA Breach Log'; $breached = $breached ?? []; ?>
<div class="container-fluid px-4 py-4">
    <h4 class="fw-bold mb-4"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>SLA Breach Log</h4>
    <div class="card border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th scope="col">Time</th><th scope="col">Rule</th><th scope="col">Type</th><th scope="col">Lead</th><th scope="col">Phone</th><th scope="col">Target</th><th scope="col">Actual</th><th scope="col">Status</th></tr></thead><tbody>
    <?php if (empty($breached)): ?><tr><td colspan="8" class="text-center py-4 text-muted">No breaches recorded</td></tr>
    <?php else: foreach ($breached as $b): ?><tr><td><?= date('d M Y H:i', strtotime($b['created_at'])) ?></td><td><?= htmlspecialchars($b['rule_name'] ?? '') ?></td><td><span class="badge bg-info"><?= $b['rule_type'] ?></span></td><td><?= htmlspecialchars($b['lead_name'] ?? '-') ?></td><td><?= htmlspecialchars($b['lead_phone'] ?? '-') ?></td><td><?= $b['target_minutes'] ?>m</td><td><?= $b['response_time_seconds'] ? round($b['response_time_seconds']/60, 1).'m' : 'N/A' ?></td><td><span class="badge bg-danger"><?= $b['status'] ?></span></td></tr><?php endforeach; endif; ?>
    </tbody></table></div></div></div>
</div>
