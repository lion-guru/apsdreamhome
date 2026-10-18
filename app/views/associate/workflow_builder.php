<?php
/**
 * Visual Workflow Builder (Gap 21) — drag-drop trigger→condition→action canvas.
 * Posts the assembled rule to the existing /associate/workflows/store endpoint.
 */
$triggers = ['lead_created' => 'New lead added', 'status_changed' => 'Status changes', 'note_added' => 'Note added'];
$actions = ['add_note' => 'Add a note', 'set_status' => 'Change status', 'create_followup' => 'Schedule follow-up'];
$statuses = ['new', 'contacted', 'qualified', 'site_visit', 'proposal', 'negotiation', 'closed_won', 'closed_lost', 'nurture'];
?>
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><i class="fas fa-project-diagram text-primary me-2"></i>Visual Workflow Builder</h4>
            <small class="text-muted">Drag blocks from the palette, drop them on the canvas in order, then save.</small>
        </div>
        <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Workflows</a>
    </div>

    <div class="row">
        <!-- Palette -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-shapes me-2"></i>Blocks</div>
                <div class="card-body">
                    <div class="fw-bold text-primary small mb-2">TRIGGERS</div>
                    <?php foreach ($triggers as $k=>$v): ?>
                    <div class="block-item mb-2 p-2 border rounded" draggable="true" data-type="trigger" data-value="<?= $k ?>" data-label="<?= htmlspecialchars($v) ?>">
                        <i class="fas fa-bolt text-warning me-1"></i><?= htmlspecialchars($v) ?>
                    </div>
                    <?php endforeach; ?>
                    <div class="fw-bold text-info small mb-2 mt-3">CONDITIONS</div>
                    <div class="block-item mb-2 p-2 border rounded" draggable="true" data-type="condition" data-value="status_is" data-label="Status is...">
                        <i class="fas fa-filter text-info me-1"></i>Status is...
                    </div>
                    <div class="fw-bold text-success small mb-2 mt-3">ACTIONS</div>
                    <?php foreach ($actions as $k=>$v): ?>
                    <div class="block-item mb-2 p-2 border rounded" draggable="true" data-type="action" data-value="<?= $k ?>" data-label="<?= htmlspecialchars($v) ?>">
                        <i class="fas fa-play text-success me-1"></i><?= htmlspecialchars($v) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Canvas -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-palette me-2"></i>Canvas</div>
                <div class="card-body">
                    <div id="canvas" class="canvas-area border rounded p-3 mb-3" style="min-height:300px;background:#f8f9fa">
                        <div class="text-muted text-center py-5" id="canvas-empty">Drag blocks here to build your workflow</div>
                    </div>

                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small">Rule name *</label>
                            <input type="text" id="rule_name" class="form-control" required placeholder="e.g. Welcome new leads">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Condition value (if Status is)</label>
                            <select id="cond_status" class="form-select"><option value="">— any —</option><?php foreach ($statuses as $s): ?><option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Action param (note text / status / days)</label>
                            <input type="text" id="param_text" class="form-control" placeholder="Note text / status / days">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100" onclick="saveWorkflow()"><i class="fas fa-save me-1"></i>Save Rule</button>
                        </div>
                    </div>
                    <div id="save-status" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let canvasBlocks = [];
const canvas = document.getElementById('canvas');
const emptyMsg = document.getElementById('canvas-empty');

// Palette drag start
document.querySelectorAll('.block-item').forEach(el => {
    el.addEventListener('dragstart', e => {
        e.dataTransfer.setData('text/plain', JSON.stringify({
            type: el.dataset.type, value: el.dataset.value, label: el.dataset.label
        }));
    });
});

// Canvas drop
canvas.addEventListener('dragover', e => { e.preventDefault(); canvas.style.borderColor = '#0d6efd'; });
canvas.addEventListener('dragleave', () => { canvas.style.borderColor = ''; });
canvas.addEventListener('drop', e => {
    e.preventDefault();
    canvas.style.borderColor = '';
    const data = JSON.parse(e.dataTransfer.getData('text/plain'));
    addBlockToCanvas(data);
});

function addBlockToCanvas(data) {
    emptyMsg.style.display = 'none';
    const idx = canvasBlocks.length;
    canvasBlocks.push(data);
    const el = document.createElement('div');
    el.className = 'canvas-block mb-2 p-2 border rounded d-flex justify-content-between align-items-center';
    el.style.background = data.type === 'trigger' ? '#fff3cd' : (data.type === 'condition' ? '#d1ecf1' : '#d4edda');
    el.innerHTML = `
        <span><span class="badge bg-secondary me-1">${idx+1}</span>
        <strong>${data.type.toUpperCase()}</strong> — ${data.label}</span>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeBlock(${idx})"><i class="fas fa-times"></i></button>
    `;
    canvas.appendChild(el);
}

function removeBlock(idx) {
    canvasBlocks.splice(idx, 1);
    renderCanvas();
}

function renderCanvas() {
    canvas.querySelectorAll('.canvas-block').forEach(el => el.remove());
    if (canvasBlocks.length === 0) { emptyMsg.style.display = ''; return; }
    emptyMsg.style.display = 'none';
    canvasBlocks.forEach((data, idx) => {
        const el = document.createElement('div');
        el.className = 'canvas-block mb-2 p-2 border rounded d-flex justify-content-between align-items-center';
        el.style.background = data.type === 'trigger' ? '#fff3cd' : (data.type === 'condition' ? '#d1ecf1' : '#d4edda');
        el.innerHTML = `
            <span><span class="badge bg-secondary me-1">${idx+1}</span>
            <strong>${data.type.toUpperCase()}</strong> — ${data.label}</span>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeBlock(${idx})"><i class="fas fa-times"></i></button>
        `;
        canvas.appendChild(el);
    });
}

function saveWorkflow() {
    const name = document.getElementById('rule_name').value.trim();
    if (!name) { alert('Rule name is required'); return; }
    if (canvasBlocks.length === 0) { alert('Add at least one block to the canvas'); return; }

    // Extract trigger + action from canvas (first trigger, first action)
    const trigger = canvasBlocks.find(b => b.type === 'trigger');
    const action = canvasBlocks.find(b => b.type === 'action');
    if (!trigger) { alert('Add a trigger block'); return; }
    if (!action) { alert('Add an action block'); return; }

    const condStatus = document.getElementById('cond_status').value;
    const paramText = document.getElementById('param_text').value.trim();

    // Build action_params based on action type
    let actionParams = {};
    if (action.value === 'add_note') actionParams = { text: paramText || 'Auto note from workflow' };
    else if (action.value === 'set_status') actionParams = { status: paramText || 'contacted' };
    else if (action.value === 'create_followup') actionParams = { days: parseInt(paramText) || 3 };

    const payload = new URLSearchParams();
    payload.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
    payload.append('name', name);
    payload.append('trigger', trigger.value);
    payload.append('cond_status', condStatus);
    payload.append('action', action.value);
    payload.append('param_text', paramText);
    payload.append('param_status', condStatus);
    payload.append('param_days', paramText);

    fetch('<?= BASE_URL ?>' + '/associate/workflows/store', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest'},
        body: payload.toString()
    }).then(r => r.json()).then(d => {
        const el = document.getElementById('save-status');
        if (d.success) {
            el.innerHTML = '<div class="alert alert-success py-1">Rule saved! <a href="<?= BASE_URL ?>">View workflows</a></div>';
            canvasBlocks = [];
            renderCanvas();
            document.getElementById('rule_name').value = '';
        } else {
            el.innerHTML = '<div class="alert alert-danger py-1">' + (d.error || 'Failed to save') + '</div>';
        }
    }).catch(() => {
        document.getElementById('save-status').innerHTML = '<div class="alert alert-danger py-1">Error saving rule</div>';
    });
}
</script>
