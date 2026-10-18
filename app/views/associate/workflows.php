<?php
$rules = $rules ?? [];
$triggers = ['lead_created' => 'New lead added', 'status_changed' => 'Status changes', 'note_added' => 'Note added'];
$actions = ['add_note' => 'Add a note', 'set_status' => 'Change status', 'create_followup' => 'Schedule follow-up'];
$statuses = ['new', 'contacted', 'qualified', 'site_visit', 'proposal', 'negotiation', 'closed_won', 'closed_lost', 'nurture'];
?>
<div class="container-fluid px-4 py-3">
    <div class="mb-1 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1"><i class="fas fa-robot text-primary me-2"></i><?= __('assoc_wf_title', [], 'Workflow Automation') ?></h4>
            <small class="text-muted"><?= __('assoc_wf_sub', [], 'When this happens → do that, automatically. Only your leads, only your rules.') ?></small>
        </div>
        <a href="<?= BASE_URL . '/associate/workflow-builder' ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-project-diagram me-1"></i>Visual Builder</a>
    </div>
    <div class="card border-0 shadow-sm my-3">
        <div class="card-header bg-white fw-bold"><i class="fas fa-plus me-2"></i><?= __('assoc_wf_new', [], 'New Rule') ?></div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/associate/workflows/store" class="row g-2 align-items-end">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="col-md-3"><label class="form-label small"><?= __('assoc_wf_name', [], 'Rule name *') ?></label><input type="text" name="name" class="form-control" required maxlength="150" placeholder="e.g. Welcome new leads"></div>
                <div class="col-md-2"><label class="form-label small"><?= __('assoc_wf_when', [], 'When *') ?></label><select name="trigger" class="form-select"><?php foreach ($triggers as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><label class="form-label small"><?= __('assoc_wf_ifstatus', [], 'Only if status is') ?></label><select name="cond_status" class="form-select"><option value="">— any —</option><?php foreach ($statuses as $s): ?><option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><label class="form-label small"><?= __('assoc_wf_then', [], 'Then *') ?></label><select name="action" class="form-select"><?php foreach ($actions as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label small"><?= __('assoc_wf_params', [], 'Note text / status / days') ?></label><input type="text" name="param_text" class="form-control" maxlength="500" placeholder="Note text…"></div>
                <div class="col-md-2"><label class="form-label small"><?= __('assoc_wf_pstatus', [], '…or status') ?></label><select name="param_status" class="form-select"><option value="">—</option><?php foreach ($statuses as $s): ?><option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><label class="form-label small"><?= __('assoc_wf_pdays', [], '…or follow-up in N days') ?></label><input type="number" name="param_days" class="form-control" min="1" max="90" value="3"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i><?= __('assoc_wf_create', [], 'Create Rule') ?></button></div>
            </form>
            <small class="text-muted"><?= __('assoc_wf_hint', [], 'Fill only the parameter your action needs. Rules run in order, best-effort — they never block saves.') ?></small>
        </div>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-body p-0">
        <?php if (empty($rules)): ?>
            <div class="text-center py-5 text-muted"><i class="fas fa-robot fa-3x mb-3"></i><h5><?= __('assoc_wf_empty', [], 'No rules yet') ?></h5><p class="small mb-0"><?= __('assoc_wf_empty_desc', [], 'Example: when a lead is added → add note “Called within 5 minutes”.') ?></p></div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="bg-light"><tr><th><?= __('assoc_wf_th_rule', [], 'Rule') ?></th><th><?= __('assoc_wf_th_does', [], 'Does') ?></th><th><?= __('assoc_wf_th_state', [], 'State') ?></th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rules as $r):
                $ap = json_decode($r['action_params'] ?? '', true) ?: [];
                $does = $r['action'] === 'add_note' ? '✎ ' . mb_substr($ap['text'] ?? '', 0, 60) : ($r['action'] === 'set_status' ? '→ ' . ($ap['status'] ?? '') : '+' . (int)($ap['days'] ?? 3) . 'd follow-up');
            ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['name'] ?? '') ?></strong><br><small class="text-muted"><?= htmlspecialchars($triggers[$r['trigger']] ?? $r['trigger']) ?><?php $c = json_decode($r['conditions'] ?? '', true) ?: []; ?><?= !empty($c['status']) ? ' · if ' . htmlspecialchars($c['status']) : '' ?></small></td>
                    <td><small><?= htmlspecialchars($does) ?></small></td>
                    <td><span class="badge bg-<?= $r['is_active'] ? 'success' : 'secondary' ?>"><?= $r['is_active'] ? 'ON' : 'OFF' ?></span></td>
                    <td class="text-end text-nowrap">
                        <form method="POST" action="<?= BASE_URL ?>/associate/workflows/<?= (int)$r['id'] ?>/toggle" class="d-inline"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-power-off"></i></button></form>
                        <form method="POST" action="<?= BASE_URL ?>/associate/workflows/<?= (int)$r['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this rule?')"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div></div>
</div>
