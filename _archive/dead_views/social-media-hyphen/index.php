<?php
/**
 * Social Media Management View
 * Data: $page_title, $posts
 */
$page_title = $page_title ?? 'Social Media Management';
$posts = $posts ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-share-alt me-2"></i>Social Media Management</h2>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/social-media/schedule" class="btn btn-outline-primary"><i class="fas fa-calendar-plus me-1"></i> Schedule Post</a>
            <a href="<?= BASE_URL ?>/admin/social-media/accounts" class="btn btn-outline-secondary"><i class="fas fa-link me-1"></i> Connected Accounts</a>
            <a href="<?= BASE_URL ?>/admin/social-media/analytics" class="btn btn-outline-info"><i class="fas fa-chart-bar me-1"></i> Analytics</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-file-alt"></i></span></div>
                        <div>
                            <div class="aps-cp-stat-label">Total Posts</div>
                            <div class="aps-cp-stat-value"><?= count($posts) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-check-circle"></i></span></div>
                        <div>
                            <div class="aps-cp-stat-label">Published</div>
                            <div class="aps-cp-stat-value"><?= count(array_filter($posts, fn($p) => ($p['status'] ?? '') === 'published')) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-clock"></i></span></div>
                        <div>
                            <div class="aps-cp-stat-label">Scheduled</div>
                            <div class="aps-cp-stat-value"><?= count(array_filter($posts, fn($p) => ($p['status'] ?? '') === 'scheduled')) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-pen"></i></span></div>
                        <div>
                            <div class="aps-cp-stat-label">Drafts</div>
                            <div class="aps-cp-stat-value"><?= count(array_filter($posts, fn($p) => ($p['status'] ?? '') === 'draft')) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header d-flex justify-content-between align-items-center">
            <i class="fas fa-list me-2"></i>Posts
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPostModal">
                <i class="fas fa-plus me-1"></i> Create Post
            </button>
        </div>
        <div class="card-body p-0">
            <?php if (empty($posts)): ?>
            <div class="text-center py-5">
                <i class="fas fa-share-alt fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Posts Yet</h4>
                <p class="text-muted">Create your first social media post.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                    <i class="fas fa-plus me-1"></i> Create Post
                </button>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Content Preview</th>
                            <th>Platforms</th>
                            <th>Status</th>
                            <th>Scheduled</th>
                            <th>Published</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <div class="fw-medium text-truncate" class="style-33818"><?= htmlspecialchars($post['content'] ?? '') ?></div>
                                <small class="text-muted">ID: <?= $post['id'] ?? '' ?></small>
                            </td>
                            <td>
                                <?php
                                $platforms = $post['platforms'] ?? [];
                                if (is_string($platforms)) $platforms = json_decode($platforms, true) ?: [];
                                foreach ($platforms as $platform): ?>
                                    <span class="aps-cp-badge badge bg-<?= match($platform) { 'facebook' => 'primary', 'twitter' => 'info', 'instagram' => 'danger', 'linkedin' => 'primary', 'whatsapp' => 'success', default => 'secondary' } ?> me-1">
                                        <?= ucfirst($platform) ?>
                                    </span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <span class="aps-cp-badge badge bg-<?= match($post['status'] ?? 'draft') { 'published' => 'success', 'scheduled' => 'warning', 'failed' => 'danger', default => 'secondary' } ?>">
                                    <?= ucfirst($post['status'] ?? 'draft') ?>
                                </span>
                            </td>
                            <td><?= $post['scheduled_at'] ? date('d M Y H:i', strtotime($post['scheduled_at'])) : 'â€”' ?></td>
                            <td><?= $post['published_at'] ? date('d M Y H:i', strtotime($post['published_at'])) : 'â€”' ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/social-media/edit/<?= $post['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/social-media/delete/<?= $post['id'] ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this post?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create Post Modal -->
<div class="modal fade" id="createPostModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/social-media/store">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create Social Media Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="content" rows="4" required placeholder="Enter your post content..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Platforms <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach (['facebook', 'twitter', 'instagram', 'linkedin', 'whatsapp'] as $platform): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="platforms[]" value="<?= $platform ?>" id="platform_<?= $platform ?>">
                                    <label class="form-check-label" for="platform_<?= $platform ?>"><i class="fab fa-<?= $platform === 'twitter' ? 'x-twitter' : $platform ?> me-1"></i><?= ucfirst($platform) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Schedule For</label>
                            <input type="datetime-local" class="form-control" name="scheduled_at">
                            <div class="form-text">Leave empty to publish immediately</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Media (Optional)</label>
                            <input type="file" class="form-control" name="media" accept="image/*,video/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Publish / Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>