<?php
$page_title = $page_title ?? 'Create OCR Template';
$template = $template ?? null;
$fields_json = $fields_json ?? '[]';
$doc_types = $doc_types ?? [];
$doc_type_labels = $doc_type_labels ?? [];
$isEdit = !empty($template);
?>

<style>
.ocr-page{background:#0f172a;min-height:100vh;color:#e2e8f0;padding:0 0 40px}
.ocr-header{background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);border-bottom:1px solid #334155;padding:28px 0 24px}
.ocr-card{background:#1e293b;border:1px solid #334155;border-radius:14px;padding:28px}
.ocr-label{color:#94a3b8;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;display:block}
.ocr-input,.ocr-select,.ocr-textarea{width:100%;background:#0f172a;border:1px solid #334155;color:#e2e8f0;border-radius:10px;padding:12px 16px;font-size:14px;outline:none;transition:border-color .2s}
.ocr-input:focus,.ocr-select:focus,.ocr-textarea:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15)}
.ocr-textarea{font-family:'Courier New',monospace;font-size:13px;min-height:200px;resize:vertical;line-height:1.5}
.ocr-btn{border-radius:10px;padding:12px 24px;font-weight:600;font-size:14px;border:none;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
.ocr-btn:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(0,0,0,.3)}
.ocr-btn-primary{background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff}
.ocr-btn-outline{background:transparent;border:1px solid #475569;color:#94a3b8}
.ocr-btn-success{background:linear-gradient(135deg,#10b981,#059669);color:#fff}
.ocr-json-help{background:#0f172a;border:1px solid #334155;border-radius:10px;padding:16px;margin-top:16px}
.ocr-json-help h6{color:#e2e8f0;font-size:13px;font-weight:700;margin-bottom:8px}
.ocr-json-help pre{background:#1e293b;border-radius:8px;padding:12px;font-size:12px;color:#94a3b8;margin:0;overflow-x:auto;line-height:1.4}
.ocr-form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
</style>

<div class="ocr-page">
    <div class="ocr-header">
        <div class="container-fluid px-4" class="style-84072">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-1 fw-bold text-white">
                        <i class="fas fa-cog me-2"></i><?= $isEdit ? 'Edit Template' : 'Create Template' ?>
                    </h4>
                    <p class="mb-0" class="style-29848">Define field extraction rules for a document type</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/ocr/templates" class="ocr-btn ocr-btn-outline"><i class="fas fa-arrow-left"></i>Back</a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4" class="style-86238">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="ocr-card">
                    <form method="POST" action="<?= BASE_URL ?>/admin/ocr/templates/store">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="template_id" value="<?= $template['id'] ?>">
                        <?php endif; ?>

                        <div class="ocr-form-row mb-4">
                            <div>
                                <label class="ocr-label">Template Name</label>
                                <input type="text" name="template_name" class="ocr-input" placeholder="e.g. Standard Aadhaar Template" value="<?= htmlspecialchars($template['template_name'] ?? '') ?>" required>
                            </div>
                            <div>
                                <label class="ocr-label">Document Type</label>
                                <select name="document_type" class="ocr-select" required>
                                    <?php foreach ($doc_types as $dt): ?>
                                        <option value="<?= $dt ?>" <?= ($template['document_type'] ?? '') === $dt ? 'selected' : '' ?>>
                                            <?= $doc_type_labels[$dt] ?? $dt ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="ocr-label">Active</label>
                            <select name="is_active" class="ocr-select" class="style-72730">
                                <option value="1" <?= ($template['is_active'] ?? 1) ? 'selected' : '' ?>>Yes</option>
                                <option value="0" <?= !($template['is_active'] ?? 1) ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="ocr-label">Field Definitions (JSON)</label>
                            <textarea name="field_definitions_json" class="ocr-textarea" placeholder='[{"name": "full_name", "label": "Full Name", "pattern": "/Name:\\s*(.+)/i"}]' id="fieldsJson"><?= htmlspecialchars($fields_json) ?></textarea>
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="ocr-btn ocr-btn-outline" class="style-45098" onclick="validateJson()"><i class="fas fa-check me-1"></i>Validate JSON</button>
                                <button type="button" class="ocr-btn ocr-btn-outline" class="style-45098" onclick="prettifyJson()"><i class="fas fa-indent me-1"></i>Prettify</button>
                                <button type="button" class="ocr-btn ocr-btn-outline" class="style-45098" onclick="addField()"><i class="fas fa-plus me-1"></i>Add Field</button>
                            </div>
                            <div id="jsonStatus" class="style-30392"></div>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="ocr-btn ocr-btn-primary"><i class="fas fa-save me-1"></i><?= $isEdit ? 'Update Template' : 'Create Template' ?></button>
                            <a href="<?= BASE_URL ?>/admin/ocr/templates" class="ocr-btn ocr-btn-outline">Cancel</a>
                        </div>
                    </form>

                    <div class="ocr-json-help">
                        <h6><i class="fas fa-question-circle me-1"></i>JSON Format Reference</h6>
                        <pre>[
  {
    "name": "full_name",
    "label": "Full Name",
    "pattern": "/(?:Name|à¤¨à¤¾à¤®)\\s*[:\\-]?\\s*([A-Z][A-Za-z\\s\\.]{2,60})/i"
  },
  {
    "name": "document_number",
    "label": "Document Number",
    "pattern": "/\\b([A-Z0-9]{5,20})\\b/"
  },
  {
    "name": "date_of_birth",
    "label": "Date of Birth",
    "pattern": "/(?:DOB|Date of Birth)\\s*[:\\-]?\\s*(\\d{2}\\/\\d{2}\\/\\d{4})/i"
  }
]</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validateJson() {
    const el = document.getElementById('fieldsJson');
    const status = document.getElementById('jsonStatus');
    try {
        const data = JSON.parse(el.value);
        if (Array.isArray(data)) {
            status.style.color = '#34d399';
            status.innerHTML = '<i class="fas fa-check-circle me-1"></i>Valid JSON — ' + data.length + ' field(s) defined';
        } else {
            status.style.color = '#fbbf24';
            status.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>JSON is valid but expected an array';
        }
    } catch (e) {
        status.style.color = '#f87171';
        status.innerHTML = '<i class="fas fa-times-circle me-1"></i>Invalid JSON: ' + e.message;
    }
}

function prettifyJson() {
    const el = document.getElementById('fieldsJson');
    try {
        const data = JSON.parse(el.value);
        el.value = JSON.stringify(data, null, 2);
        document.getElementById('jsonStatus').innerHTML = '<span class="style-49307"><i class="fas fa-check-circle me-1"></i>Prettified</span>';
    } catch (e) {
        document.getElementById('jsonStatus').innerHTML = '<span class="style-37569"><i class="fas fa-times-circle me-1"></i>Cannot prettify invalid JSON</span>';
    }
}

function addField() {
    const el = document.getElementById('fieldsJson');
    let data = [];
    try {
        data = JSON.parse(el.value);
        if (!Array.isArray(data)) data = [];
    } catch (e) {
        data = [];
    }
    const name = prompt('Field name (snake_case, e.g. full_name):');
    if (!name) return;
    const pattern = prompt('Regex pattern (e.g. /Name:\\s*(.+)/i):', '/(' + name + ')\\s*[:\\-]?\\s*(.+)/i');
    if (!pattern) return;
    data.push({ name: name, label: name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()), pattern: pattern });
    el.value = JSON.stringify(data, null, 2);
    document.getElementById('jsonStatus').innerHTML = '<span class="style-49307"><i class="fas fa-plus-circle me-1"></i>Added field: ' + name + '</span>';
}

validateJson();
</script>
