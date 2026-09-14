<?php
$item = $item ?? null;
$page_title = $page_title ?? 'Feedback Detail';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

if (!$item): ?>
    <div class="container-fluid py-4">
        <div class="text-center py-5">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h5>Feedback not found</h5>
            <a href="<?= e($base) ?>/admin/app-feedback" class="btn btn-primary mt-2"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
        </div>
    </div>
<?php return; endif;

$statusColors = ['new' => 'primary', 'acknowledged' => 'info', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
$platformColors = ['android' => 'success', 'ios' => 'dark', 'web' => 'info'];
$typeColors = ['bug' => 'danger', 'feature' => 'primary', 'improvement' => 'info', 'complaint' => 'warning', 'other' => 'secondary'];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-comment-dots me-2"></i>Feedback #<?= (int)$item['id'] ?></h2>
            <p class="text-muted mb-0">Submitted <?= isset($item['created_at']) ? date('M d, Y \a\t g:i A', strtotime($item['created_at'])) : '' ?></p>
        </div>
        <a href="<?= e($base) ?>/admin/app-feedback" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>User Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Name</label>
                            <span class="fw-semibold"><?= htmlspecialchars($item['user_name'] ?? 'Anonymous') ?></span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Email</label>
                            <span class="fw-semibold"><?= htmlspecialchars($item['user_email'] ?? 'N/A') ?></span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Platform</label>
                            <span class="badge bg-<?= $platformColors[$item['platform'] ?? 'web'] ?? 'secondary' ?> fs-6">
                                <?= ucfirst(htmlspecialchars($item['platform'] ?? 'N/A')) ?>
                            </span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">App Version</label>
                            <span class="fw-semibold"><?= htmlspecialchars($item['app_version'] ?? 'N/A') ?></span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Device</label>
                            <span class="fw-semibold"><?= htmlspecialchars($item['device_info'] ?? 'N/A') ?></span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">OS Version</label>
                            <span class="fw-semibold"><?= htmlspecialchars($item['os_version'] ?? 'N/A') ?></span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Type</label>
                            <span class="badge bg-<?= $typeColors[$item['type'] ?? 'other'] ?? 'secondary' ?>">
                                <?= ucfirst(htmlspecialchars($item['type'] ?? 'other')) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Rating &amp; Description</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">User Rating</label>
                        <div class="fs-4">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= (int)($item['rating'] ?? 0) ? 'text-warning' : 'text-muted' ?>"></i>
                            <?php endfor; ?>
                            <span class="ms-2 text-muted fs-6">(<?= (int)($item['rating'] ?? 0) ?>/5)</span>
                        </div>
                    </div>

                    <?php if (!empty($item['title'])): ?>
                        <div class="mb-3">
                            <label class="text-muted small d-block mb-1">Title</label>
                            <h5 class="mb-0"><?= htmlspecialchars($item['title']) ?></h5>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Description</label>
                        <div class="p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($item['description'] ?? 'No description provided.')) ?>
                        </div>
                    </div>

                    <?php if (!empty($item['screenshot_path'])): ?>
                        <div class="mb-3">
                            <label class="text-muted small d-block mb-1">Screenshot</label>
                            <div class="border rounded p-2 d-inline-block">
                                <img src="<?= htmlspecialchars($item['screenshot_path']) ?>" alt="Screenshot" class="img-fluid rounded" style="max-height:300px">
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label class="text-muted small d-block mb-1">Screenshot</label>
                            <div class="border rounded p-4 text-center bg-light">
                                <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0 small">No screenshot attached</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($item['admin_response'])): ?>
                <div class="card border-0 shadow-sm mb-4 border-start border-primary border-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-reply me-2"></i>Admin Response</h5>
                    </div>
                    <div class="card-body">
                        <div class="p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($item['admin_response'])) ?>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            Responded by user #<?= (int)($item['responded_by'] ?? 0) ?>
                            <?php if (!empty($item['responded_at'])): ?>
                                on <?= date('M d, Y \a\t g:i A', strtotime($item['responded_at'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-semibold small text-muted d-block mb-2">Current Status</label>
                        <span class="badge bg-<?= $statusColors[$item['status'] ?? 'new'] ?? 'secondary' ?> fs-6">
                            <?= ucfirst(str_replace('_', ' ', $item['status'] ?? 'new')) ?>
                        </span>
                        <?php if (!empty($item['responded_at'])): ?>
                            <div class="text-muted small mt-1">
                                Last updated <?= date('M d, g:i A', strtotime($item['responded_at'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form method="POST" action="<?= e($base) ?>/admin/app-feedback/<?= (int)$item['id'] ?>/status" class="mb-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <label class="fw-semibold small text-muted d-block mb-2">Update Status</label>
                        <div class="input-group">
                            <select name="status" class="form-select">
                                <?php foreach (['new', 'acknowledged', 'in_progress', 'resolved', 'closed'] as $s): ?>
                                    <option value="<?= $s ?>" <?= ($item['status'] ?? 'new') === $s ? 'selected' : '' ?>>
                                        <?= ucfirst(str_replace('_', ' ', $s)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i></button>
                        </div>
                    </form>

                    <hr>

                    <form method="POST" action="<?= e($base) ?>/admin/app-feedback/<?= (int)$item['id'] ?>/respond">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <label class="fw-semibold small text-muted d-block mb-2">Admin Response</label>
                        <textarea name="admin_response" class="form-control mb-2" rows="5" placeholder="Type your response to the user..."><?= htmlspecialchars($item['admin_response'] ?? '') ?></textarea>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-paper-plane me-1"></i> Send Response
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Details</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Feedback ID</span>
                        <span class="fw-semibold">#<?= (int)$item['id'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Created</span>
                        <span class="fw-semibold"><?= isset($item['created_at']) ? date('M d, Y', strtotime($item['created_at'])) : 'N/A' ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Updated</span>
                        <span class="fw-semibold"><?= isset($item['updated_at']) ? date('M d, Y', strtotime($item['updated_at'])) : 'N/A' ?></span>
                    </div>
                    <?php if (!empty($item['user_id'])): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">User ID</span>
                            <span class="fw-semibold"><?= (int)$item['user_id'] ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($item['ip_address'])): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">IP Address</span>
                            <span class="fw-semibold"><?= htmlspecialchars($item['ip_address']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm border-danger">
                <div class="card-body">
                    <h6 class="text-danger mb-2"><i class="fas fa-trash me-1"></i> Danger Zone</h6>
                    <p class="text-muted small mb-2">Permanently delete this feedback. This action cannot be undone.</p>
                    <a href="<?= e($base) ?>/admin/app-feedback/<?= (int)$item['id'] ?>/delete" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Are you sure you want to permanently delete this feedback?')">
                        <i class="fas fa-trash me-1"></i> Delete Feedback
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
