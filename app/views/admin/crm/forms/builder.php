<?php
$page_title = $page_title ?? 'Form Builder';
$form = $form ?? null;
$isEdit = !empty($form['id']);
$fields = json_decode($form['fields'] ?? '[]', true) ?? [];
$settings = json_decode($form['settings'] ?? '{}', true) ?? [];
?>

<div class="container-fluid-container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold"><i class="fas fa-magic me-2 text-primary"></i>Visual Form Builder</h2>
            <p class="text-muted mb-0">Drag and drop fields to build your lead capture form</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/crm/forms" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <a href="<?= BASE_URL ?>/admin/crm/forms/<?= $form['id'] ?? '' ?>/preview" target="_blank" class="btn btn-outline-primary"><i class="fas fa-eye me-1"></i> Preview</a>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/crm/forms/<?= $isEdit ? $form['id'] . '/update' : 'store' ?>" id="formBuilderForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="fields" id="fieldsJson" value='<?= htmlspecialchars(json_encode($fields)) ?>'>

        <div class="row g-4">
            <!-- Field Palette -->
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm sticky-top style-47885">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-palette me-2"></i>Field Palette</h6>
                    </div>
                    <div class="card-body p-3 style-82773">
                        <div class="mb-3">
                            <small class="text-muted d-block mb-2">Basic Fields</small>
                            <div class="palette-item p-2 mb-1 bg-light rounded border" draggable="true" data-type="text" ondragstart="dragStart(event)">
                                <i class="fas fa-font me-2 text-primary"></i> Text Input
                            </div>
                            <div class="palette-item p-2 mb-1 bg-light rounded border" draggable="true" data-type="email" ondragstart="dragStart(event)">
                                <i class="fas fa-envelope me-2 text-success"></i> Email
                            </div>
                            <div class="palette-item p-2 mb-1 bg-light rounded border" draggable="true" data-type="phone" ondragstart="dragStart(event)">
                                <i class="fas fa-phone me-2 text-info"></i> Phone
                            </div>
                            <div class="palette-item p-2 mb-1 bg-light rounded border" draggable="true" data-type="textarea" ondragstart="dragStart(event)">
                                <i class="fas fa-align-left me-2 text-warning"></i> Textarea
                            </div>
                            <div class="palette-item p-2 mb-1 bg-light rounded border" draggable="true" data-type="select" ondragstart="dragStart(event)">
                                <i class="fas fa-list-ul me-2 text-purple"></i> Dropdown
                            </div>
                            <div class="palette-item p-2 mb-1 bg-light rounded border" draggable="true" data-type="checkbox" ondragstart="dragStart(event)">
                                <i class="fas fa-check-square me-2 text-secondary"></i> Checkbox
                            </div>
                            <div class="palette-item p-2 mb-1 bg-light rounded border" draggable="true" data-type="hidden" ondragstart="dragStart(event)">
                                <i class="fas fa-eye-slash me-2 text-muted"></i> Hidden Field
                            </div>
                        </div>
                        <div>
                            <small class="text-muted d-block mb-2">Layout</small>
                            <div class="palette-item p-2 mb-1 bg-light rounded border" draggable="true" data-type="section" ondragstart="dragStart(event)">
                                <i class="fas fa-columns me-2 text-dark"></i> Section Break
                            </div>
                            <div class="palette-item p-2 mb-1 bg-light rounded border" draggable="true" data-type="heading" ondragstart="dragStart(event)">
                                <i class="fas fa-heading me-2 text-dark"></i> Heading
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Canvas -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Form Canvas</h6>
                        <span class="badge bg-primary" id="fieldCount"><?= count($fields) ?> fields</span>
                    </div>
                    <div class="card-body p-3 style-95098">
                        <div id="formCanvas" class="drop-zone p-4 border-2 border-dashed rounded" ondragover="dragOver(event)" ondrop="dropField(event)" ondragleave="dragLeave(event)">
                            <?php if (empty($fields)): ?>
                                <div class="text-center text-muted py-5" id="emptyState">
                                    <i class="fas fa-mouse-pointer fa-3x mb-3"></i>
                                    <h5>Drag fields here to build your form</h5>
                                    <p class="mb-0">Drop fields from the left palette</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($fields as $i => $field): ?>
                                    <div class="form-field-card mb-3 p-3 border rounded bg-white position-relative" data-index="<?= $i ?>" data-type="<?= htmlspecialchars($field['type'] ?? '') ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-<?= match($field['type']) { 'text'=>'primary', 'email'=>'success', 'phone'=>'info', 'textarea'=>'warning', 'select'=>'info', 'checkbox'=>'secondary', 'hidden'=>'dark', default=>'dark' } ?>">
                                                <?= ucfirst($field['type']) ?>
                                            </span>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editField(<?= $i ?>)"><i class="fas fa-edit"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeField(<?= $i ?>)"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </div>
                                        <div class="field-preview">
                                            <label class="form-label fw-bold"><?= htmlspecialchars($field['label'] ?? '') ?> <?= !empty($field['required']) ? '<span class="text-danger">*</span>' : '' ?></label>
                                            <?php if (!empty($field['placeholder'])): ?>
                                                <small class="text-muted">Placeholder: <?= htmlspecialchars($field['placeholder'] ?? '') ?></small>
                                            <?php endif; ?>
                                            <?php if ($field['type'] === 'select' && !empty($field['options'])): ?>
                                                <small class="text-muted">Options: <?= htmlspecialchars(implode(', ', $field['options'])) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Field Settings -->
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm sticky-top style-47885">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Field Settings</h6>
                    </div>
                    <div class="card-body" id="fieldSettingsPanel">
                        <div class="text-center text-muted py-5" id="noFieldSelected">
                            <i class="fas fa-hand-pointer fa-2x mb-2"></i>
                            <p class="mb-0">Click a field to edit its properties</p>
                        </div>
                        <div id="fieldSettingsForm" class="style-24280">
                            <input type="hidden" id="editingFieldIndex">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Label *</label>
                                <input type="text" class="form-control" id="fieldLabel" placeholder="e.g. Full Name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Placeholder</label>
                                <input type="text" class="form-control" id="fieldPlaceholder" placeholder="e.g. Enter your name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Field Type</label>
                                <select class="form-select" id="fieldType" disabled>
                                    <option value="text">Text</option>
                                    <option value="email">Email</option>
                                    <option value="phone">Phone</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="select">Dropdown</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="hidden">Hidden</option>
                                </select>
                            </div>
                            <div class="mb-3" id="selectOptionsContainer" class="style-24280">
                                <label class="form-label fw-bold">Options (one per line)</label>
                                <textarea class="form-control" id="fieldOptions" rows="4" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="fieldRequired">
                                <label class="form-check-label fw-bold" for="fieldRequired">Required</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="fieldHidden">
                                <label class="form-check-label fw-bold" for="fieldHidden">Hidden Field</label>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary flex-grow-1" onclick="saveFieldSettings()"><i class="fas fa-save me-1"></i> Save</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="cancelFieldEdit()" aria-label="Save"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Settings -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Form Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Submit Button Text</label>
                            <input type="text" class="form-control" name="submit_text" value="<?= htmlspecialchars($settings['submit_text'] ?? 'Submit') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Success Message</label>
                            <input type="text" class="form-control" name="success_message" value="<?= htmlspecialchars($settings['success_message'] ?? 'Thank you for your interest!') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Redirect URL (optional)</label>
                            <input type="url" class="form-control" name="redirect_url" value="<?= htmlspecialchars($settings['redirect_url'] ?? '') ?>" placeholder="https://example.com/thank-you">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="auto_assign" id="auto_assign" <?= !empty($settings['auto_assign']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="auto_assign">Auto-assign to Agent</label>
                        </div>
                        <div class="mb-3" id="assignToContainer" class="style-74599">
                            <label class="form-label fw-bold">Assign To</label>
                            <select class="form-select" name="assign_to">
                                <option value="">-- Select --</option>
                                <?php
                                try {
                                    $db = \App\Core\Database\Database::getInstance()->getConnection();
                                    $agents = $db->query("SELECT id, name FROM users WHERE role IN ('associate','employee','agent') AND status = 'active' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                                    foreach ($agents as $a): ?>
                                        <option value="<?= $a['id'] ?>" <?= ($settings['assign_to'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['name'] ?? '') ?></option>
                                    <?php endforeach;
                                } catch (\Throwable $e) { error_log(__METHOD__ . ': ' . $e->getMessage()); }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Drip Campaign (optional)</label>
                            <select class="form-select" name="drip_campaign">
                                <option value="">None</option>
                                <?php
                                try {
                                    $db = \App\Core\Database\Database::getInstance()->getConnection();
                                    $campaigns = $db->query("SELECT id, name FROM campaigns WHERE status = 'active' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                                    foreach ($campaigns as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= ($settings['drip_campaign'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name'] ?? '') ?></option>
                                    <?php endforeach;
                                } catch (\Throwable $e) { error_log(__METHOD__ . ': ' . $e->getMessage()); }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tags (comma-separated)</label>
                            <input type="text" class="form-control" name="tags" value="<?= htmlspecialchars(implode(', ', $settings['tags'] ?? [])) ?>" placeholder="e.g. website, landing-page">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Meta (hidden inputs) -->
        <input type="hidden" name="name" id="formNameInput" value="<?= htmlspecialchars($form['name'] ?? '') ?>">
        <input type="hidden" name="description" id="formDescInput" value="<?= htmlspecialchars($form['description'] ?? '') ?>">

        <div class="mt-4 d-flex gap-2">
            <button type="button" class="btn btn-secondary" onclick="history.back()"><i class="fas fa-arrow-left me-1"></i> Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveAndSubmit()"><i class="fas fa-save me-1"></i> Save Form</button>
        </div>
    </form>
</div>

<style>
.palette-item{cursor:grab;transition:.2s}.palette-item:hover{background:#e8f0fe!important;transform:translateX(4px)}
.drop-zone{transition:.2s}.drop-zone.drag-over{background:#f0f7ff;border-color:#667eea}
.form-field-card{transition:.2s}.form-field-card:hover{box-shadow:0 2px 8px rgba(0,0,0,.1)}
.field-preview{font-size:14px}
</style>

<script>
let fields = <?= json_encode($fields) ?>;
let draggedType = null;

function dragStart(e) { draggedType = e.target.closest('.palette-item').dataset.type; e.dataTransfer.effectAllowed = 'copy'; }
function dragOver(e) { e.preventDefault(); e.currentTarget.classList.add('drag-over'); e.dataTransfer.dropEffect = 'copy'; }
function dragLeave(e) { e.currentTarget.classList.remove('drag-over'); }
function dropField(e) {
    e.preventDefault(); e.currentTarget.classList.remove('drag-over');
    if (!draggedType) return;
    const newField = { type: draggedType, label: '', placeholder: '', required: false, options: [] };
    if (draggedType === 'select') newField.options = ['Option 1', 'Option 2', 'Option 3'];
    fields.push(newField);
    renderCanvas();
    draggedType = null;
}

function renderCanvas() {
    const canvas = document.getElementById('formCanvas');
    const emptyState = document.getElementById('emptyState');
    const count = document.getElementById('fieldCount');
    
    if (fields.length === 0) {
        canvas.innerHTML = '<div class="text-center text-muted py-5" id="emptyState"><i class="fas fa-mouse-pointer fa-3x mb-3"></i><h5>Drag fields here to build your form</h5><p class="mb-0">Drop fields from the left palette</p></div>';
    } else {
        let html = '';
        fields.forEach((f, i) => {
            html += `<div class="form-field-card mb-3 p-3 border rounded bg-white position-relative" data-index="${i}" data-type="${f.type}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-${getTypeColor(f.type)}">${capitalize(f.type)}</span>
                    <div><button type="button" class="btn btn-sm btn-outline-secondary" onclick="editField(${i})" aria-label="Edit"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeField(${i})" aria-label="Edit"><i class="fas fa-trash"></i></button></div>
                </div>
                <div class="field-preview">
                    <label class="form-label fw-bold">${escapeHtml(f.label || '')} ${f.required ? '<span class="text-danger">*</span>' : ''}</label>
                    ${f.placeholder ? `<small class="text-muted">Placeholder: ${escapeHtml(f.placeholder)}</small>` : ''}
                    ${f.type === 'select' && f.options?.length ? `<small class="text-muted">Options: ${escapeHtml(f.options.join(', '))}</small>` : ''}
                </div></div>`;
        });
        canvas.innerHTML = html;
    }
    count.textContent = fields.length + ' fields';
    document.getElementById('fieldsJson').value = JSON.stringify(fields);
}

function getTypeColor(t) { return {text:'primary',email:'success',phone:'info',textarea:'warning',select:'info',checkbox:'secondary',hidden:'dark'}[t] || 'dark'; }
function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
function escapeHtml(s) { return s.replace(/&/g,'&').replace(/</g,'<').replace(/>/g,'>').replace(/"/g,'"'); }

function editField(i) {
        const f = fields[i];
        document.getElementById('noFieldSelected').style.display = 'none';
        document.getElementById('fieldSettingsForm').style.display = 'block';
        document.getElementById('editingFieldIndex').value = i;
        document.getElementById('fieldLabel').value = f.label || '';
        document.getElementById('fieldPlaceholder').value = f.placeholder || '';
        document.getElementById('fieldType').value = f.type;
        document.getElementById('fieldRequired').checked = f.required || false;
        document.getElementById('fieldHidden').checked = f.hidden || false;
        document.getElementById('fieldOptions').value = (f.options || []).join('\n');
        document.getElementById('selectOptionsContainer').style.display = f.type === 'select' ? 'block' : 'none';
    }

    function saveFieldSettings() {
        const i = parseInt(document.getElementById('editingFieldIndex').value);
        fields[i].label = document.getElementById('fieldLabel').value;
        fields[i].placeholder = document.getElementById('fieldPlaceholder').value;
        fields[i].required = document.getElementById('fieldRequired').checked;
        fields[i].hidden = document.getElementById('fieldHidden').checked;
        if (fields[i].type === 'select') {
            fields[i].options = document.getElementById('fieldOptions').value.split('\n').map(s => s.trim()).filter(s => s);
        }
        cancelFieldEdit();
        renderCanvas();
    }

    function cancelFieldEdit() {
        document.getElementById('noFieldSelected').style.display = 'block';
        document.getElementById('fieldSettingsForm').style.display = 'none';
    }

    function removeField(i) {
        apsConfirm('Remove this field?').then(function(ok) {
            if (!ok) return;
            fields.splice(i, 1);
            renderCanvas();
        });
    }

    function saveAndSubmit() {
        const name = document.getElementById('formNameInput').value || prompt('Enter form name:');
        if (!name) return showToast('Form name required', 'info');
        document.getElementById('formNameInput').value = name;
        document.getElementById('fieldsJson').value = JSON.stringify(fields);
        document.getElementById('formBuilderForm').submit();
    }

    // Auto-save form name from URL param
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('name')) {
        document.getElementById('formNameInput').value = urlParams.get('name');
    }

    renderCanvas();
</script>