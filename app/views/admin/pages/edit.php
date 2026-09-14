<?php $page = $page ?? []; $versions = $versions ?? []; $page_title = $page_title ?? 'Edit Page'; ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Tabs: Editor / Version History -->
            <ul class="nav nav-tabs mb-3" id="pageEditTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="editor-tab" data-bs-toggle="tab" data-bs-target="#editor-pane" type="button" role="tab">
                        <i class="fas fa-edit me-1"></i>Editor
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane" type="button" role="tab">
                        <i class="fas fa-history me-1"></i>Version History
                        <?php if (count($versions) > 0): ?>
                            <span class="badge bg-secondary ms-1"><?= count($versions) ?></span>
                        <?php endif; ?>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="pageEditTabContent">
                <!-- ═══ EDITOR TAB ═══ -->
                <div class="tab-pane fade show active" id="editor-pane" role="tabpanel">
                    <div class="card aps-cp-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-edit me-2"></i><?= htmlspecialchars($page_title ?? '') ?></h5>
                            <div>
                                <?php
                                // Preview URL mapping
                                $legalSlugs = ['terms-conditions','privacy-policy','refund-policy','disclaimer','cancellation-policy','associate-rules','services','legal-services','legal-documents'];
                                $slug = $page['slug'] ?? '';
                                if (in_array($slug, $legalSlugs)) {
                                    $previewUrl = BASE_URL . '/legal/' . $slug;
                                } elseif ($slug === 'about-us') {
                                    $previewUrl = BASE_URL . '/about';
                                } else {
                                    $previewUrl = BASE_URL . '/' . $slug;
                                }
                                ?>
                                <a href="<?= $previewUrl ?>" class="btn btn-outline-info btn-sm me-2" target="_blank" title="View live page">
                                    <i class="fas fa-external-link-alt me-1"></i>View Live
                                </a>
                                <a href="<?= BASE_URL ?>/admin/pages" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
                            </div>
                        </div>
                        <div class="card-body aps-cp-card-body">
                            <form method="POST" action="<?= BASE_URL ?>/admin/pages/update/<?= $page['id'] ?? 0 ?>" id="pageEditForm">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Page Title</label>
                                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($page['title'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Slug</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($page['slug'] ?? '') ?>" readonly disabled>
                                        <div class="form-text">Slug cannot be changed after creation.</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="draft" <?= ($page['status'] ?? '') == 'draft' ? 'selected' : '' ?>>Draft</option>
                                            <option value="published" <?= ($page['status'] ?? '') == 'published' ? 'selected' : '' ?>>Published</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Page Content (HTML)</label>
                                    <textarea name="content" id="pageContent" class="form-control" rows="20"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
                                    <div class="form-text">HTML supported. Use the toolbar for rich editing.</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Meta Description</label>
                                        <textarea name="meta_description" class="form-control" rows="3"><?= htmlspecialchars($page['meta_description'] ?? '') ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Meta Keywords</label>
                                        <textarea name="meta_keywords" class="form-control" rows="3"><?= htmlspecialchars($page['meta_keywords'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <!-- Change summary (for version tracking) -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-tag me-1 text-primary"></i>What changed? <span class="text-muted fw-normal">(saved with version history)</span>
                                    </label>
                                    <input type="text" name="change_summary" class="form-control" placeholder="e.g. Updated refund deduction tiers, fixed typo in clause 3...">
                                    <div class="form-text">This note is saved with the version snapshot so you can track what changed and when.</div>
                                </div>

                                <div class="text-end">
                                    <a href="<?= BASE_URL ?>/admin/pages" class="btn btn-outline-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary fw-bold">
                                        <i class="fas fa-save me-1"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ═══ VERSION HISTORY TAB ═══ -->
                <div class="tab-pane fade" id="history-pane" role="tabpanel">
                    <div class="card aps-cp-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Version History — <?= htmlspecialchars($page['title'] ?? '') ?></h5>
                        </div>
                        <div class="card-body aps-cp-card-body">
                            <?php if (empty($versions)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-history fa-2x mb-2"></i>
                                    <p>No version history yet. Versions are automatically saved every time you update this page.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Version</th>
                                                <th>Status</th>
                                                <th>Changed By</th>
                                                <th>Date</th>
                                                <th>Summary</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($versions as $i => $v): ?>
                                            <tr class="<?= $i === 0 ? 'table-success' : '' ?>">
                                                <td>
                                                    <strong>#<?= $v['version_number'] ?></strong>
                                                    <?php if ($i === 0): ?>
                                                        <span class="badge bg-success ms-1">Current</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (($v['status'] ?? '') === 'published'): ?>
                                                        <span class="badge bg-success">Published</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Draft</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($v['changed_by_name'] ?? 'System') ?></td>
                                                <td><?= htmlspecialchars($v['created_at'] ?? '') ?></td>
                                                <td>
                                                    <small class="text-muted"><?= htmlspecialchars($v['change_summary'] ?? '-') ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($i > 0): ?>
                                                        <form method="POST" action="<?= BASE_URL ?>/admin/pages/<?= $page['id'] ?? 0 ?>/restore/<?= $v['id'] ?>" style="display:inline;"
                                                              onsubmit="return confirm('Restore this version? Current content will be replaced (a new version will be created automatically).')">
                                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Restore this version">
                                                                <i class="fas fa-undo me-1"></i>Restore
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted small">—</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (count($versions) >= 20): ?>
                                    <div class="text-center text-muted small">Showing latest 20 versions.</div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#pageContent',
    height: 500,
    menubar: true,
    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
    toolbar: 'undo redo | blocks | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | code | help',
    branding: false,
    promotion: false,
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; font-size: 16px; }'
});
</script>
