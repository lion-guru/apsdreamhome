<?php
$page_title = $page_title ?? 'Edit Content';
$section = $section ?? 'about';
$items = $items ?? [];
$grouped = $grouped ?? [];

$groupLabels = [
    'leader_1' => 'Leader 1',
    'leader_2' => 'Leader 2',
    'leader_3' => 'Leader 3',
    'stats'    => 'Company Stats',
    'registration' => 'Registration',
    'hero'     => 'Hero Banner',
    'company'  => 'Company Info',
    'general'  => 'General',
];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= BASE_URL ?>/admin/site-content" class="text-decoration-none me-3"><i class="fas fa-arrow-left"></i> Back</a>
            <h1 class="d-inline h3 mb-0"><i class="fas fa-edit me-2"></i><?= htmlspecialchars($page_title) ?></h1>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/admin/site-content/update/<?= htmlspecialchars($section) ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <?php foreach ($grouped as $groupName => $groupItems): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-layer-group me-2 text-primary"></i>
                    <?= $groupLabels[$groupName] ?? ucfirst(str_replace('_', ' ', $groupName)) ?>
                </h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                <?php foreach ($groupItems as $item): ?>
                    <?php
                    $key = $item['content_key'];
                    $val = $item['content_value'] ?? '';
                    $type = $item['content_type'] ?? 'text';
                    $label = ucfirst(str_replace('_', ' ', $key));
                    ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold"><?= htmlspecialchars($label) ?></label>

                        <?php if ($type === 'textarea'): ?>
                            <textarea name="content[<?= htmlspecialchars($key) ?>]" class="form-control" rows="3"><?= htmlspecialchars($val) ?></textarea>

                        <?php elseif ($type === 'image'): ?>
                            <?php if (!empty($val) && file_exists(dirname(__DIR__, 3) . '/' . $val)): ?>
                                <div class="mb-2">
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($val) ?>" class="img-thumbnail" style="max-height:100px;" alt="<?= htmlspecialchars($label) ?>">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="content_image[<?= htmlspecialchars($key) ?>]" class="form-control" accept="image/*">
                            <input type="hidden" name="content[<?= htmlspecialchars($key) ?>]" value="<?= htmlspecialchars($val) ?>">

                        <?php elseif ($type === 'number'): ?>
                            <input type="number" name="content[<?= htmlspecialchars($key) ?>]" class="form-control" value="<?= htmlspecialchars($val) ?>">

                        <?php else: ?>
                            <input type="text" name="content[<?= htmlspecialchars($key) ?>]" class="form-control" value="<?= htmlspecialchars($val) ?>">
                        <?php endif; ?>

                        <small class="text-muted">Key: <?= htmlspecialchars($key) ?></small>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i> Save Changes</button>
            <a href="<?= BASE_URL ?>/admin/site-content" class="btn btn-outline-secondary btn-lg">Cancel</a>
        </div>
    </form>

    <!-- Add New Entry -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="fas fa-plus me-2 text-success"></i> Add New Content Entry</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <form action="<?= BASE_URL ?>/admin/site-content/create" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Key</label>
                        <input type="text" name="content_key" class="form-control" placeholder="e.g. new_feature_title" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Group</label>
                        <input type="text" name="content_group" class="form-control" placeholder="e.g. hero, leader_1">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Type</label>
                        <select name="content_type" class="form-select">
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                            <option value="image">Image</option>
                            <option value="number">Number</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-plus me-1"></i> Add</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Value</label>
                    <textarea name="content_value" class="form-control" rows="2" placeholder="Content value"></textarea>
                </div>
            </form>
        </div>
    </div>
</div>
