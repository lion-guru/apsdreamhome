<?php
$template = $template ?? null;
$versions = $versions ?? [];
$categories = $categories ?? [];
$merge_fields = $merge_fields ?? [];
if (!$template) { echo '<div class="container-fluid py-4"><div class="alert alert-danger">Template not found</div></div>'; return; }
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i>Edit Template: <?= htmlspecialchars($template['name'] ?? '') ?> <small class="text-muted">v<?= (int)($template['version'] ?? 1) ?></small></h4>
        <div>
            <a href="<?= BASE_URL ?>/admin/legal/templates" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <form method="POST" action="<?= BASE_URL ?>/admin/legal/templates/<?= $template['id'] ?>/update">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="aps-cp-card mb-3">
                    <div class="aps-cp-card-header"><i class="fas fa-cog me-2"></i>Template Details</div>
                    <div class="aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($template['name'] ?? '') ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Category</label><select name="category_id" class="form-select"><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($template['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name'] ?? '') ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="draft" <?= $template['status'] === 'draft' ? 'selected' : '' ?>>Draft</option><option value="active" <?= $template['status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="archived" <?= $template['status'] === 'archived' ? 'selected' : '' ?>>Archived</option></select></div>
                            <div class="col-md-4"><label class="form-label">Language</label><select name="language" class="form-select"><option value="en" <?= ($template['language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option><option value="hi" <?= ($template['language'] ?? '') === 'hi' ? 'selected' : '' ?>>Hindi</option></select></div>
                            <div class="col-md-4"><label class="form-label">Customer Facing</label><select name="is_customer_facing" class="form-select"><option value="1" <?= !empty($template['is_customer_facing']) ? 'selected' : '' ?>>Yes</option><option value="0" <?= empty($template['is_customer_facing']) ? 'selected' : '' ?>>No</option></select></div>
                            <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($template['description'] ?? '') ?></textarea></div>
                        </div>
                    </div>
                </div>

                <div class="aps-cp-card mb-3">
                    <div class="aps-cp-card-header"><i class="fas fa-code me-2"></i>Content <small class="text-muted">(HTML with merge fields)</small></div>
                    <div class="aps-cp-card-body">
                        <div class="mb-2">
                            <small class="text-muted">Available merge fields:</small>
                            <div class="dropdown d-inline-block ms-2">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Insert Field</button>
                                <div class="dropdown-menu p-2" class="style-52319">
                                    <?php foreach ($merge_fields as $group => $fields): ?>
                                        <h6 class="dropdown-header"><?= ucfirst($group) ?></h6>
                                        <?php foreach ($fields as $key => $label): ?>
                                            <button type="button" class="dropdown-item small" onclick="insertAtCursor('content', '<?= $key ?>')"><?= htmlspecialchars($key ?? '') ?> - <?= htmlspecialchars($label ?? '') ?></button>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <small class="text-muted ms-2">Change notes: <input type="text" name="change_notes" class="form-control-sm" placeholder="What changed?" class="style-79252"></small>
                        </div>
                        <textarea name="content" id="content" class="form-control font-monospace" rows="20"><?= htmlspecialchars($template['content'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="text-end mb-4">
                    <a href="<?= BASE_URL ?>/admin/legal/templates" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Template</button>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header"><i class="fas fa-history me-2"></i>Version History</div>
                <div class="aps-cp-card-body p-0">
                    <?php if (empty($versions)): ?>
                        <div class="text-center text-muted py-3 small">No version history</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush" class="style-61454">
                            <?php foreach ($versions as $v): ?>
                                <div class="list-group-item p-2">
                                    <div class="d-flex justify-content-between">
                                        <strong class="small">v<?= (int)$v['version_number'] ?></strong>
                                        <small class="text-muted"><?= date('d M Y', strtotime($v['created_at'])) ?></small>
                                    </div>
                                    <small class="text-muted d-block"><?= htmlspecialchars(substr($v['change_notes'] ?? 'No notes', 0, 80)) ?></small>
                                    <div class="mt-1">
                                        <form method="POST" action="<?= BASE_URL ?>/admin/legal/templates/<?= $template['id'] ?>/restore/<?= $v['version_number'] ?>" class="d-inline" onsubmit="return confirm('Restore version v<?= (int)$v['version_number'] ?>?')">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                            <button class="btn btn-sm btn-link p-0 small">Restore</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function insertAtCursor(fieldId, text) {
    var ta = document.getElementById(fieldId);
    if (!ta) return;
    if (document.selection) { ta.focus(); document.selection.createRange().text = text; }
    else if (ta.selectionStart || ta.selectionStart === 0) {
        var s = ta.selectionStart, e = ta.selectionEnd;
        ta.value = ta.value.substring(0, s) + text + ta.value.substring(e);
        ta.selectionStart = ta.selectionEnd = s + text.length;
    } else { ta.value += text; }
    ta.focus();
}
</script>
