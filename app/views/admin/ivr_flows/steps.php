<?php
/**
 * IVR Flow Steps Management
 * S347
 */
$page_title = $page_title ?? 'IVR Flow Steps';
$flow = $flow ?? [];
$steps = $steps ?? [];
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-list-ol me-2 text-primary"></i>Steps for: <?= htmlspecialchars($flow['name'] ?? '') ?></h4>
            <div>
                <a href="/admin/ivr-flows/<?= $flow['id'] ?>/edit" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-edit me-1"></i>Edit Flow
                </a>
                <a href="/admin/ivr-flows" class="btn btn-secondary">Back to Flows</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Flow Steps</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStepModal">
                    <i class="fas fa-plus me-1"></i>Add Step
                </button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($steps)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-plus-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Steps Defined</h5>
                        <p class="text-muted">Add steps to define the IVR flow logic</p>
                        <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addStepModal">Add First Step</button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Type</th>
                                    <th>Configuration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($steps as $s): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary"><?= $s['step_order'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $s['step_type'] === 'menu' ? 'info' : 
                                            ($s['step_type'] === 'message' ? 'success' : 
                                            ($s['step_type'] === 'transfer' ? 'warning' : 'secondary')) ?>">
                                            <?= ucfirst($s['step_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <pre class="small mb-0"><?= htmlspecialchars(json_encode(json_decode($s['config_json'] ?? '{}'), JSON_PRETTY_PRINT)) ?></pre>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary edit-step-btn" 
                                                    data-id="<?= $s['id'] ?>" 
                                                    data-order="<?= $s['step_order'] ?>"
                                                    data-type="<?= $s['step_type'] ?>"
                                                    data-config='<?= htmlspecialchars($s['config_json'] ?? '{}', ENT_QUOTES) ?>'
                                                    data-bs-toggle="modal" data-bs-target="#editStepModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="/admin/ivr-flows/<?= $s['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this step?')">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Step Modal -->
<div class="modal fade" id="addStepModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add IVR Step</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/admin/ivr-flows/<?= $flow['id'] ?>/steps/store" id="stepForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="flow_id" value="<?= $flow['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Step Order</label>
                        <input type="number" class="form-control" name="step_order" min="1" value="<?= count($steps) + 1 ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Step Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="step_type" id="step_type" required onchange="updateConfigFields()">
                            <option value="">Select Type</option>
                            <option value="message">Play Message</option>
                            <option value="menu">Menu (Press 1, 2, 3...)</option>
                            <option value="transfer">Transfer to Agent/Number</option>
                            <option value="voicemail">Voicemail</option>
                            <option value="hangup">Hang Up</option>
                        </select>
                    </div>
                    <div id="configFields"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Step</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Step Modal -->
<div class="modal fade" id="editStepModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit IVR Step</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/admin/ivr-flows/steps/update" id="editStepForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="step_id" id="edit_step_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Step Order</label>
                        <input type="number" class="form-control" name="step_order" id="edit_step_order" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Step Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="step_type" id="edit_step_type" required onchange="updateEditConfigFields()">
                            <option value="message">Play Message</option>
                            <option value="menu">Menu (Press 1, 2, 3...)</option>
                            <option value="transfer">Transfer to Agent/Number</option>
                            <option value="voicemail">Voicemail</option>
                            <option value="hangup">Hang Up</option>
                        </select>
                    </div>
                    <div id="editConfigFields"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Step</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function getConfigFields(type, prefix = '') {
    const fields = {
        message: `
            <div class="mb-3">
                <label class="form-label">Message Text</label>
                <textarea class="form-control" name="${prefix}config[message_text]" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Voice</label>
                <select class="form-select" name="${prefix}config[voice]">
                    <option value="alice">Alice (Female)</option>
                    <option value="man">Man (Male)</option>
                    <option value="woman">Woman (Female)</option>
                </select>
            </div>
        `,
        menu: `
            <div class="mb-3">
                <label class="form-label">Menu Prompt</label>
                <textarea class="form-control" name="${prefix}config[prompt]" rows="2" required placeholder="Press 1 for Sales, 2 for Support..."></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Options (JSON)</label>
                <textarea class="form-control" name="${prefix}config[options]" rows="4" required>
{
  "1": { "action": "transfer", "target": "sales" },
  "2": { "action": "transfer", "target": "support" }
}</textarea>
            </div>
        `,
        transfer: `
            <div class="mb-3">
                <label class="form-label">Transfer To</label>
                <select class="form-select" name="${prefix}config[transfer_to]" required>
                    <option value="">Select...</option>
                    <option value="agent">Available Agent</option>
                    <option value="number">Phone Number</option>
                    <option value="queue">Call Queue</option>
                </select>
            </div>
            <div id="transferTargetFields"></div>
        `,
        voicemail: `
            <div class="mb-3">
                <label class="form-label">Greeting Message</label>
                <textarea class="form-control" name="${prefix}config[greeting]" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Email for Transcription</label>
                <input type="email" class="form-control" name="${prefix}config[email]">
            </div>
        `,
        hangup: `
            <div class="alert alert-info">Call will be ended. No additional configuration needed.</div>
        `
    };
    return fields[type] || '<div class="text-muted">No configuration for this type</div>';
}

function updateConfigFields() {
    const type = document.getElementById('step_type').value;
    document.getElementById('configFields').innerHTML = getConfigFields(type);
}

function updateEditConfigFields() {
    const type = document.getElementById('edit_step_type').value;
    document.getElementById('editConfigFields').innerHTML = getConfigFields(type, 'edit_');
}

// Populate edit modal
document.querySelectorAll('.edit-step-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const order = this.dataset.order;
        const type = this.dataset.type;
        const config = JSON.parse(this.dataset.config || '{}');

        document.getElementById('edit_step_id').value = id;
        document.getElementById('edit_step_order').value = order;
        document.getElementById('edit_step_type').value = type;
        document.getElementById('editConfigFields').innerHTML = getConfigFields(type, 'edit_');

        // Fill config values after a short delay for DOM to render
        setTimeout(() => {
            Object.keys(config).forEach(key => {
                const input = document.querySelector(`[name="edit_config[${key}]"]`);
                const textarea = document.querySelector(`[name="edit_config[${key}]"]`);
                if (input) input.value = config[key];
                if (textarea) textarea.value = config[key];
            });
        }, 50);
    });
});
</script>