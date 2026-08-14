<?php
$clauses = $clauses ?? [];
$categories = $categories ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>Clause Library</h2>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createClauseModal"><i class="fas fa-plus me-1"></i>New Clause</button>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3"><label class="form-label small">Category</label><select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()"><option value="">All</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($_GET['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name'] ?? '') ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label small">Tag</label><input type="text" name="tag" class="form-control form-control-sm" placeholder="Filter by tag..." value="<?= htmlspecialchars($_GET['tag'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label small">Search</label><input type="text" name="search" class="form-control form-control-sm" placeholder="Search clauses..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"></div>
                <div class="col-md-1"><button class="btn btn-sm btn-outline-primary w-100">Filter</button></div>
            </form>
        </div>
    </div>

    <?php if (empty($clauses)): ?>
        <div class="text-center text-muted py-5"><i class="fas fa-list fa-3x mb-3"></i><p>No clauses found</p></div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($clauses as $cl): ?>
                <div class="col-md-6">
                    <div class="aps-cp-card h-100">
                        <div class="aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0"><?= htmlspecialchars($cl['title'] ?? '') ?></h6>
                                <span class="badge bg-light text-dark"><?= htmlspecialchars($cl['category_name'] ?? 'Uncategorized') ?></span>
                            </div>
                            <div class="small text-muted mb-2 border-start border-3 border-primary ps-2" class="style-43650"><?= nl2br(htmlspecialchars(substr($cl['content'] ?? '', 0, 300))) ?></div>
                            <?php if (!empty($cl['tags'])): ?>
                                <div class="mb-2"><?php foreach (explode(',', $cl['tags']) as $tag): ?><span class="badge bg-secondary me-1"><?= htmlspecialchars(trim($tag)) ?></span><?php endforeach; ?></div>
                            <?php endif; ?>
                            <div class="mt-2 pt-2 border-top d-flex justify-content-between">
                                <small class="text-muted">Order: <?= (int)($cl['sort_order'] ?? 0) ?></small>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editClauseModal<?= $cl['id'] ?>"><i class="fas fa-edit"></i></button>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/legal/clauses/<?= $cl['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this clause?')">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editClauseModal<?= $cl['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="<?= BASE_URL ?>/admin/legal/clauses/<?= $cl['id'] ?>/update">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <div class="modal-header"><h5 class="modal-title">Edit Clause</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($cl['title'] ?? '') ?>" required></div>
                                        <div class="col-md-6"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="">None</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($cl['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name'] ?? '') ?></option><?php endforeach; ?></select></div>
                                        <div class="col-md-6"><label class="form-label">Tags (comma separated)</label><input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($cl['tags'] ?? '') ?>"></div>
                                        <div class="col-md-6"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)($cl['sort_order'] ?? 0) ?>"></div>
                                        <div class="col-12"><label class="form-label">Content</label><textarea name="content" class="form-control" rows="6" required><?= htmlspecialchars($cl['content'] ?? '') ?></textarea></div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createClauseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/legal/clauses/create">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-header"><h5 class="modal-title">New Clause</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="">None</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Tags (comma separated)</label><input type="text" name="tags" class="form-control" placeholder="force-majeure,delay,liability"></div>
                        <div class="col-md-6"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="0"></div>
                        <div class="col-12"><label class="form-label">Content</label><textarea name="content" class="form-control" rows="6" required></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
