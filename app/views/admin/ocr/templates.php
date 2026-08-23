<?php
$page_title = $page_title ?? 'OCR Templates';
$templates = $templates ?? [];
$doc_types = $doc_types ?? [];
$doc_type_labels = $doc_type_labels ?? [];
?>

<style>
.ocr-page{background:#0f172a;min-height:100vh;color:#e2e8f0;padding:0 0 40px}
.ocr-header{background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);border-bottom:1px solid #334155;padding:28px 0 24px}
.ocr-card{background:#1e293b;border:1px solid #334155;border-radius:14px;overflow:hidden}
.ocr-card-body{padding:24px}
.ocr-card-header{background:#0f172a;padding:16px 20px;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center}
.ocr-card-header h6{color:#e2e8f0;font-weight:700;margin:0;font-size:14px}
.ocr-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.ocr-badge-active{background:rgba(16,185,129,.15);color:#34d399}
.ocr-badge-inactive{background:rgba(239,68,68,.15);color:#f87171}
.ocr-btn{border-radius:10px;padding:9px 18px;font-weight:600;font-size:13px;border:none;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.ocr-btn:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.3)}
.ocr-btn-primary{background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff}
.ocr-btn-danger{background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff}
.ocr-btn-outline{background:transparent;border:1px solid #475569;color:#94a3b8}
.ocr-template-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px}
.ocr-template-item{background:#0f172a;border:1px solid #334155;border-radius:12px;padding:20px;transition:border-color .2s}
.ocr-template-item:hover{border-color:#3b82f6}
.ocr-template-name{font-size:15px;font-weight:700;color:#e2e8f0;margin-bottom:4px}
.ocr-template-type{font-size:12px;color:#64748b;margin-bottom:12px}
.ocr-template-fields{display:flex;flex-wrap:wrap;gap:4px;margin-bottom:14px}
.ocr-template-fields span{background:#1e293b;border:1px solid #334155;border-radius:6px;padding:3px 8px;font-size:11px;color:#94a3b8}
.ocr-template-actions{display:flex;gap:8px}
.ocr-empty{text-align:center;padding:60px 20px;color:#64748b}
.ocr-empty i{font-size:48px;margin-bottom:16px;opacity:.5}
</style>

<div class="ocr-page">
    <div class="ocr-header">
        <div class="container-fluid px-4 style-84072">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-1 fw-bold text-white"><i class="fas fa-cogs me-2"></i>OCR Templates</h4>
                    <p class="mb-0 style-29848">Manage field extraction templates for each document type</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/admin/ocr" class="ocr-btn ocr-btn-outline"><i class="fas fa-arrow-left"></i>Back</a>
                    <a href="<?= BASE_URL ?>/admin/ocr/templates/create" class="ocr-btn ocr-btn-primary"><i class="fas fa-plus"></i>New Template</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 style-86238">
        <?php if (empty($templates)): ?>
            <div class="ocr-card">
                <div class="ocr-empty">
                    <i class="fas fa-cogs d-block"></i>
                    <h6 class="mb-2">No templates created yet</h6>
                    <p class="style-83988">Templates define which fields to extract from each document type</p>
                    <a href="<?= BASE_URL ?>/admin/ocr/templates/create" class="ocr-btn ocr-btn-primary"><i class="fas fa-plus me-1"></i>Create Template</a>
                </div>
            </div>
        <?php else: ?>
            <div class="ocr-template-grid">
                <?php foreach ($templates as $t):
                    $fields = $t['field_definitions'] ?? [];
                    if (is_string($fields)) {
                        $fields = json_decode($fields, true) ?? [];
                    }
                ?>
                    <div class="ocr-template-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="ocr-template-name"><?= htmlspecialchars($t['template_name'] ?? '') ?></div>
                                <div class="ocr-template-type">
                                    <?= $doc_type_labels[$t['document_type']] ?? $t['document_type'] ?>
                                </div>
                            </div>
                            <span class="ocr-badge <?= $t['is_active'] ? 'ocr-badge-active' : 'ocr-badge-inactive' ?>">
                                <?= $t['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>

                        <?php if (!empty($fields)): ?>
                            <div class="ocr-template-fields">
                                <?php foreach ($fields as $f):
                                    $fName = $f['name'] ?? $f['field_name'] ?? 'unknown';
                                ?>
                                    <span><?= htmlspecialchars($fName ?? '') ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="style-82830">No fields defined</p>
                        <?php endif; ?>

                        <div class="ocr-template-actions">
                            <a href="<?= BASE_URL ?>/admin/ocr/templates/edit/<?= $t['id'] ?>" class="ocr-btn ocr-btn-outline style-95261"><i class="fas fa-edit me-1"></i>Edit</a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/ocr/templates/delete/<?= $t['id'] ?>" class="style-71727" data-aps-confirm="Delete this template?">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                                <button type="submit" class="ocr-btn ocr-btn-danger style-95261"><i class="fas fa-trash me-1"></i>Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
