ï»¿<?php $page_title = $page_title ?? 'Custom Fields'; $fields = $fields ?? []; ?>
<style>.cf-card{background:#fff;border-radius:14px;border:1px solid #f0f0f5;padding:24px;transition:.3s}.cf-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08)}.cf-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;text-transform:uppercase}</style>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="fas fa-sliders-h me-2 text-primary"></i>Custom Fields</h4><p class="text-muted mb-0">Configure custom fields for leads</p></div>
        <a href="<?= BASE_URL ?>/admin/crm/custom-fields/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Field</a>
    </div>

    <?php if (empty($fields)): ?>
    <div class="cf-card text-center py-5"><i class="fas fa-sliders-h fa-3x text-muted mb-3"></i><h5>No Custom Fields</h5><p class="text-muted">Create custom fields to capture additional lead data</p></div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($fields as $f): ?>
        <div class="col-md-6 col-lg-4">
            <div class="cf-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div><h6 class="fw-bold mb-0"><?= htmlspecialchars($f['field_label'] ?? '') ?></h6><small class="text-muted"><?= htmlspecialchars($f['field_name'] ?? '') ?></small></div>
                    <span class="cf-badge" class="style-77342"><?= $f['is_active'] ? 'Active' : 'Inactive' ?></span>
                </div>
                <div class="mt-2">
                    <span class="badge bg-light text-dark me-1"><i class="fas fa-tag me-1"></i><?= ucfirst($f['field_type']) ?></span>
                    <span class="badge bg-light text-dark me-1"><i class="fas fa-layer-group me-1"></i><?= ucfirst($f['section']) ?></span>
                    <?php if ($f['is_required']): ?><span class="badge bg-danger">Required</span><?php endif; ?>
                </div>
                <?php if (!empty($f['options_json'])): ?>
                <div class="mt-2"><small class="text-muted">Options:</small><br><?php foreach (json_decode($f['options_json'], true) ?: [] as $opt): ?><span class="badge bg-secondary me-1 mb-1"><?= htmlspecialchars($opt ?? '') ?></span><?php endforeach; ?></div>
                <?php endif; ?>
                <div class="mt-3 d-flex gap-2">
                    <a href="<?= BASE_URL ?>/admin/crm/custom-fields/<?= $f['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="<?= BASE_URL ?>/admin/crm/custom-fields/<?= $f['id'] ?>/delete" data-aps-confirm="Delete this field?"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="fas fa-trash"></i></button></form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
