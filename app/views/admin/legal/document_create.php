<?php
$templates = $templates ?? [];
$customers = $customers ?? [];
$bookings = $bookings ?? [];
$plots = $plots ?? [];
$associates = $associates ?? [];
$colonies = $colonies ?? [];
$merge_fields = $merge_fields ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i>Create Document</h4>
        <a href="<?= BASE_URL ?>/admin/legal/documents" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/legal/documents/create" id="docForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="row g-3">
            <div class="col-md-8">
                <div class="aps-cp-card mb-3">
                    <div class="aps-cp-card-header"><i class="fas fa-info-circle me-2"></i>Document Details</div>
                    <div class="aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-12"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required placeholder="e.g. Booking Terms & Conditions - Plot 42"></div>
                            <div class="col-md-6"><label class="form-label">Template (optional)</label><select name="template_id" class="form-select" id="templateSelect"><option value="">No template (enter content manually)</option><?php foreach ($templates as $t): ?><option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name'] ?? '') ?> (v<?= (int)($t['version'] ?? 1) ?>)</option><?php endforeach; ?></select></div>
                            <div class="col-md-6"><label class="form-label">Document Number (leave blank for auto)</label><input type="text" name="document_number" class="form-control" placeholder="LEG-2026-XXXX"></div>
                            <div class="col-md-4"><label class="form-label">Effective Date</label><input type="date" name="effective_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                            <div class="col-md-4"><label class="form-label">Expiry Date</label><input type="date" name="expiry_date" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="draft">Draft</option><option value="final">Final</option></select></div>
                        </div>
                    </div>
                </div>

                <div class="aps-cp-card mb-3">
                    <div class="aps-cp-card-header"><i class="fas fa-link me-2"></i>Linked Entity</div>
                    <div class="aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Entity Type</label><select name="entity_type" class="form-select" id="entityType" onchange="toggleEntityFields()"><option value="general">General</option><option value="booking">Booking</option><option value="customer">Customer</option><option value="associate">Associate</option><option value="colony">Colony</option><option value="plot">Plot</option><option value="loan">Loan</option></select></div>
                            <div class="col-md-4"><label class="form-label">Customer</label><select name="customer_id" class="form-select"><option value="">None</option><?php foreach ($customers as $cu): ?><option value="<?= $cu['id'] ?>"><?= htmlspecialchars($cu['name'] ?? '') ?> (<?= htmlspecialchars($cu['phone'] ?? '') ?>)</option><?php endforeach; ?></select></div>
                            <div class="col-md-4 entity-field" data-type="booking" class="style-24280"><label class="form-label">Booking</label><select name="entity_id" class="form-select"><?php foreach ($bookings as $b): ?><option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['booking_number'] ?? '#'.$b['id']) ?> - <?= htmlspecialchars($b['customer_name'] ?? '') ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-4 entity-field" data-type="plot" class="style-24280"><label class="form-label">Plot</label><select name="entity_id" class="form-select"><?php foreach ($plots as $p): ?><option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['plot_no'] ?? '') ?> - <?= htmlspecialchars($p['colony_name'] ?? '') ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-4 entity-field" data-type="associate" class="style-24280"><label class="form-label">Associate</label><select name="entity_id" class="form-select"><?php foreach ($associates as $a): ?><option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['name'] ?? '') ?> (<?= htmlspecialchars($a['associate_code'] ?? '') ?>)</option><?php endforeach; ?></select></div>
                            <div class="col-md-4 entity-field" data-type="colony" class="style-24280"><label class="form-label">Colony</label><select name="entity_id" class="form-select"><?php foreach ($colonies as $co): ?><option value="<?= $co['id'] ?>"><?= htmlspecialchars($co['name'] ?? '') ?></option><?php endforeach; ?></select></div>
                        </div>
                    </div>
                </div>

                <div class="aps-cp-card mb-3">
                    <div class="aps-cp-card-header d-flex justify-content-between">
                        <span><i class="fas fa-file-alt me-2"></i>Content</span>
                        <span class="small text-muted">Use merge fields like {{customer_name}}, {{plot_no}}</span>
                    </div>
                    <div class="aps-cp-card-body">
                        <div class="mb-2">
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Insert Merge Field</button>
                                <div class="dropdown-menu p-2 style-62849">
                                    <?php foreach ($merge_fields as $group => $fields): ?>
                                        <h6 class="dropdown-header"><?= ucfirst($group) ?></h6>
                                        <?php foreach ($fields as $key => $label): ?>
                                            <button type="button" class="dropdown-item small" onclick="insertAtCursor('content', '<?= $key ?>')"><?= htmlspecialchars($key ?? '') ?></button>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <textarea name="content" id="content" class="form-control font-monospace" rows="15" placeholder="Enter document content in HTML format..."><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="aps-cp-card">
                    <div class="aps-cp-card-header"><i class="fas fa-sticky-note me-2"></i>Notes</div>
                    <div class="aps-cp-card-body">
                        <textarea name="notes" class="form-control" rows="4" placeholder="Internal notes..."></textarea>
                    </div>
                </div>
                <div class="aps-cp-card mt-3">
                    <div class="aps-cp-card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Document</button>
                            <a href="<?= BASE_URL ?>/admin/legal/documents" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function toggleEntityFields() {
    var t = document.getElementById('entityType').value;
    document.querySelectorAll('.entity-field').forEach(function(el) {
        el.style.display = el.dataset.type === t ? '' : 'none';
    });
}
function insertAtCursor(fieldId, text) {
    var ta = document.getElementById(fieldId);
    if (!ta) return;
    if (ta.selectionStart || ta.selectionStart === 0) {
        var s = ta.selectionStart, e = ta.selectionEnd;
        ta.value = ta.value.substring(0, s) + text + ta.value.substring(e);
        ta.selectionStart = ta.selectionEnd = s + text.length;
    } else { ta.value += text; }
    ta.focus();
}
document.addEventListener('DOMContentLoaded', toggleEntityFields);
</script>
