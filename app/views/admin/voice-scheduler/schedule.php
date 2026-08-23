<?php $agents = $agents ?? []; $scripts = $scripts ?? []; $leads = $leads ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Schedule a Call</h4>
    <a href="<?= BASE_URL ?>admin/voice-scheduler" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header aps-cp-card-header"><i class="fas fa-phone-alt me-2"></i>New Call Schedule</div>
            <div class="card-body aps-cp-card-body">
                <form method="post" action="<?= BASE_URL ?>admin/voice-scheduler/store">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-3">
                        <label class="form-label">Select Lead <span class="text-danger">*</span></label>
                        <select name="lead_id" class="form-select" id="leadSelect" required>
                            <option value="">-- Select Lead --</option>
                            <?php foreach ($leads as $l): ?>
                            <option value="<?= $l['id'] ?>" data-phone="<?= htmlspecialchars($l['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-name="<?= htmlspecialchars($l['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars(($l['name'] ?? 'Unknown') . ' - ' . ($l['phone'] ?? '') . ' [' . ($l['status'] ?? 'new') . ']', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Lead Name</label>
                            <input type="text" name="lead_name" id="leadName" class="form-control" placeholder="Auto-filled from lead" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="leadPhone" class="form-control" required placeholder="Enter phone number">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">AI Agent <span class="text-danger">*</span></label>
                            <select name="agent_id" class="form-select" required>
                                <option value="">-- Select Agent --</option>
                                <?php foreach ($agents as $a): ?>
                                <option value="<?= htmlspecialchars($a['agent_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= ($a['status'] ?? 'offline') !== 'active' ? 'disabled' : '' ?>>
                                    <?= htmlspecialchars($a['agent_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                    (<?= (int)($a['current_calls'] ?? 0) ?>/<?= (int)($a['max_concurrent_calls'] ?? 1) ?>)
                                    - <?= $a['status'] ?? 'offline' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="scheduled_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Time</label>
                            <input type="time" name="scheduled_time" class="form-control" value="10:00">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Script</label>
                            <select name="script_template" class="form-select">
                                <option value="property_introduction">Default Introduction</option>
                                <?php foreach ($scripts as $sc): ?>
                                <option value="<?= htmlspecialchars($sc['script_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($sc['script_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Schedule Call</button>
                    <a href="<?= BASE_URL ?>admin/voice-scheduler" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header aps-cp-card-header"><i class="fas fa-robot me-2"></i>Available Agents</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($agents as $a): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <span class="badge bg-<?= ($a['status'] ?? 'offline') === 'active' ? 'success' : 'secondary' ?> me-1 style-91572">&nbsp;</span>
                            <?= htmlspecialchars($a['agent_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <small class="text-muted"><?= (int)($a['total_calls_made'] ?? 0) ?> calls</small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header aps-cp-card-header"><i class="fas fa-info-circle me-2"></i>Quick Info</div>
            <div class="card-body aps-cp-card-body">
                <p class="mb-1 small">Calls are auto-assigned to the selected agent.</p>
                <p class="mb-0 small">The system will attempt up to 3 times if the call is not answered.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('leadSelect').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    if (opt.value) {
        document.getElementById('leadName').value = opt.dataset.name || '';
        document.getElementById('leadPhone').value = opt.dataset.phone || '';
    } else {
        document.getElementById('leadName').value = '';
        document.getElementById('leadPhone').value = '';
    }
});
</script>
