<?php
$auditLog = $auditLog ?? [];
$plans = $plans ?? [];
$planId = $planId ?? 0;
$csrf_token = $_SESSION['csrf_token'] ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$actionBadge = fn($a) => match($a) {
    'create' => 'bg-success', 'update' => 'bg-info', 'activate' => 'bg-primary',
    'deactivate' => 'bg-warning text-dark', 'delete' => 'bg-danger', 'clone' => 'bg-purple',
    default => 'bg-secondary'
};
$actionIcon = fn($a) => match($a) {
    'create' => 'fa-plus-circle', 'update' => 'fa-edit', 'activate' => 'fa-power-off',
    'deactivate' => 'fa-pause', 'delete' => 'fa-trash', 'clone' => 'fa-copy',
    default => 'fa-circle'
};
?>
<style>
.cp-card{background:#1a1f36;border:1px solid #2a2f4a;border-radius:12px;color:#e0e0e0;margin-bottom:1.5rem}
.cp-card-header{background:linear-gradient(135deg,#141829,#1e2340);padding:1rem 1.5rem;border-bottom:1px solid #2a2f4a;display:flex;justify-content:space-between;align-items:center}
.cp-card-body{padding:1.5rem}
.cp-btn{padding:8px 20px;border-radius:8px;font-size:.85rem;font-weight:500;border:none;cursor:pointer;transition:all .2s}
.cp-btn-outline{background:transparent;border:1px solid #4f8cff44;color:#4f8cff;text-decoration:none;display:inline-block}
.cp-input{background:#0f1225;border:1px solid #2a2f4a;border-radius:8px;color:#e0e0e0;padding:8px 12px;width:100%;font-size:.85rem}
.cp-version{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:.7rem;font-weight:600;background:#1e2340;color:#a855f7;border:1px solid #a855f733}
.timeline-item{display:flex;gap:16px;padding:14px 0;border-bottom:1px solid #1e2340}
.timeline-icon{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
.timeline-content{flex:1}
.timeline-content strong{color:#e0e0e0}
.timeline-content .time{color:#8892b0;font-size:.75rem}
.timeline-content .detail{color:#8892b0;font-size:.82rem;margin-top:4px}
.bg-purple{background:#a855f7;color:#fff}
</style>

<div class="cp-card">
    <div class="cp-card-header">
        <h5 class="m-0 style-43926"><i class="fas fa-history me-2 style-20955"></i>Plan Change History</h5>
        <a href="<?= $base ?>/admin/commission-plans" class="cp-btn cp-btn-outline"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="cp-card-body">
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label class="style-33220">Filter by Plan</label>
                    <select name="plan_id" class="cp-input" onchange="this.form.submit()">
                        <option value="0">All Plans</option>
                        <?php foreach ($plans as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $planId == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['plan_name'] ?? '') ?> v<?= $p['version'] ?> (<?= $p['plan_code'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <?php if (empty($auditLog)): ?>
            <div class="style-52260">
                <i class="fas fa-history style-10910"></i>
                No audit entries found.
            </div>
        <?php else: ?>
            <?php foreach ($auditLog as $entry): ?>
                <div class="timeline-item">
                    <div class="timeline-icon <?= $actionBadge($entry['action']) ?>">
                        <i class="fas <?= $actionIcon($entry['action']) ?>"></i>
                    </div>
                    <div class="timeline-content">
                        <div>
                            <strong><?= htmlspecialchars($entry['plan_name'] ?? '') ?></strong>
                            <span class="cp-version style-44520">v<?= $entry['version'] ?></span>
                            <span class="style-92023">
                                <?= ucfirst($entry['action']) ?> by
                                <?= htmlspecialchars($entry['changer_name'] ?? 'System') ?>
                            </span>
                            <span class="time style-11248"><?= date('M d, Y H:i', strtotime($entry['created_at'])) ?></span>
                        </div>
                        <?php if ($entry['changed_fields']): ?>
                            <div class="detail">
                                Changes: <?= htmlspecialchars($entry['changed_fields'] ?? '') ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($entry['ip_address']): ?>
                            <div class="detail">IP: <?= htmlspecialchars($entry['ip_address'] ?? '') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
