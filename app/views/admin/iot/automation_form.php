<?php
$automation = $automation ?? null;
$isEdit = !empty($automation);
$devices = $devices ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
$trigger = $isEdit && is_array($automation['trigger_config'] ?? null) ? $automation['trigger_config'] : ['metric'=>'temperature','op'=>'>','value'=>30];
$action = $isEdit && is_array($automation['action_config'] ?? null) ? $automation['action_config'] : ['target'=>'security','message'=>''];
$ops = ['>'=>'Greater than','<'=>'Less than','>=','<=','=='=>'Equals'];
$actions = ['notify'=>'Send Notification','toggle'=>'Toggle Device','setValue'=>'Set Value','webhook'=>'Call Webhook'];
$targets = ['security'=>'Security Team','owner'=>'Property Owner','admin'=>'Admin','maintenance'=>'Maintenance'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-robot me-2"></i><?= $isEdit ? 'Edit' : 'New' ?> Automation</h2>
    <a href="<?= BASE_URL ?>/admin/iot/automations" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/iot/automation/save">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $automation['id'] ?>"><?php endif; ?>
            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?= $isEdit ? htmlspecialchars($automation['name'] ?? '') : '' ?>" required></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Property ID (optional)</label><input type="number" name="property_id" class="form-control" value="<?= $isEdit ? ($automation['property_id'] ?? '') : '' ?>"></div>
                <div class="col-md-6 mb-3"><label class="form-label">Device (optional)</label><select name="device_id" class="form-select"><option value="">Any device</option><?php foreach ($devices as $d): ?><option value="<?= $d['id'] ?>" <?= $isEdit && ($automation['device_id'] ?? '')==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['name'] ?? '') ?></option><?php endforeach; ?></select></div>
            </div>

            <h6 class="text-primary">Trigger</h6>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Type</label><select name="trigger_type" class="form-select"><option value="threshold" <?= $isEdit && ($automation['trigger_type'] ?? '')==='threshold'?'selected':'' ?>>Threshold</option><option value="schedule" <?= $isEdit && ($automation['trigger_type'] ?? '')==='schedule'?'selected':'' ?>>Schedule</option><option value="event" <?= $isEdit && ($automation['trigger_type'] ?? '')==='event'?'selected':'' ?>>Event</option><option value="manual" <?= $isEdit && ($automation['trigger_type'] ?? '')==='manual'?'selected':'' ?>>Manual</option></select></div>
                <div class="col-md-3 mb-3"><label class="form-label">Metric</label><input type="text" name="trigger_metric" class="form-control" value="<?= htmlspecialchars($trigger['metric'] ?? 'temperature') ?>"></div>
                <div class="col-md-2 mb-3"><label class="form-label">Op</label><select name="trigger_op" class="form-select"><?php foreach (['>','<','>=','<=','=='] as $o): ?><option value="<?= $o ?>" <?= ($trigger['op'] ?? '')===$o?'selected':'' ?>><?= $o ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3 mb-3"><label class="form-label">Value</label><input type="number" step="0.01" name="trigger_value" class="form-control" value="<?= htmlspecialchars($trigger['value'] ?? 30) ?>"></div>
            </div>

            <h6 class="text-success">Action</h6>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Type</label><select name="action_type" class="form-select"><?php foreach ($actions as $k=>$l): ?><option value="<?= $k ?>" <?= $isEdit && ($automation['action_type'] ?? '')===$k?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4 mb-3"><label class="form-label">Target</label><select name="action_target" class="form-select"><?php foreach ($targets as $k=>$l): ?><option value="<?= $k ?>" <?= ($action['target'] ?? '')===$k?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4 mb-3"><label class="form-label">Message</label><input type="text" name="action_message" class="form-control" value="<?= htmlspecialchars($action['message'] ?? '') ?>"></div>
            </div>

            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" <?= (!$isEdit || ($automation['is_active'] ?? 1)) ? 'checked' : '' ?> id="a"><label class="form-check-label" for="a">Active</label></div>
            <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Save</button>
        </form>
    </div>
</div>
