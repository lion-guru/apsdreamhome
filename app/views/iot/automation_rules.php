<?php $pageTitle = $page_title ?? 'Smart Home Automation'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-robot me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRuleModal"><i class="fas fa-plus me-1"></i>New Rule</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-rules me-2"></i>Automation Rules</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                    <thead class="table-light"><tr><th>Rule Name</th><th>Trigger</th><th>Action</th><th>Status</th><th>Created</th></tr></thead>
                    <tbody>
                        <?php if (!empty($automation_rules)): ?>
                            <?php foreach ($automation_rules as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['rule_name'] ?? '-') ?></td>
                                    <td><code><?= htmlspecialchars(json_encode($r['trigger_condition'] ?? [])) ?></code></td>
                                    <td><code><?= htmlspecialchars(json_encode($r['action_command'] ?? [])) ?></code></td>
                                    <td><span class="badge bg-<?= ($r['is_active'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($r['is_active'] ?? 0) ? 'Active' : 'Inactive' ?></span></td>
                                    <td><?= htmlspecialchars($r['created_at'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No automation rules defined yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-building me-2"></i>Property</h5></div>
        <div class="card-body aps-cp-card-body">
            <p class="mb-1"><strong><?= htmlspecialchars($property['title'] ?? '-') ?></strong></p>
            <p class="mb-0 text-muted"><?= htmlspecialchars($property['city'] ?? '') ?></p>
        </div>
    </div>
</div>
