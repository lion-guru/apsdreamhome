<?php
$pageTitle = $pageTitle ?? 'News Article';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$news = $news ?? ['id' => 0, 'title' => '', 'content' => '', 'author' => '', 'publish_date' => '', 'status' => '', 'image' => '', 'excerpt' => '', 'category' => ''];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-newspaper me-2 text-primary"></i>News Article</h1>
        <div>
            <a href="<?= $base ?>/admin/news" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <a href="<?= $base ?>/admin/news/<?= $news['id'] ?? 0 ?>/edit" class="btn btn-primary"><i class="fas fa-edit me-1"></i>Edit</a>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <?php if (!empty($news['image'])): ?>
                <img src="<?= $base ?>/<?= htmlspecialchars($news['image'] ?? '') ?>" class="card-img-top" alt="<?= htmlspecialchars($news['title'] ?? '') ?>" class="style-85061">
                <?php endif; ?>
                <div class="card-body aps-cp-card-body">
                    <h2 class="card-title"><?= htmlspecialchars($news['title'] ?? '') ?></h2>
                    <div class="mb-3">
                        <?php if (!empty($news['category'])): ?><span class="badge bg-info me-2"><?= htmlspecialchars($news['category'] ?? '') ?></span><?php endif; ?>
                        <span class="badge bg-<?= ($news['status'] ?? 'draft') === 'published' ? 'success' : 'secondary' ?>"><?= ucfirst($news['status'] ?? 'draft') ?></span>
                    </div>
                    <p class="text-muted small">
                        By <strong><?= htmlspecialchars($news['author'] ?? 'Admin') ?></strong>
                        on <?= htmlspecialchars($news['publish_date'] ?? $news['created_at'] ?? '') ?>
                    </p>
                    <hr>
                    <div class="article-content"><?= $news['content'] ?? '<p class="text-muted">No content</p>' ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Article Details</h6></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th>Title</th><td><?= htmlspecialchars($news['title'] ?? '') ?></td></tr>
                        <tr><th>Author</th><td><?= htmlspecialchars($news['author'] ?? '') ?></td></tr>
                        <tr><th>Category</th><td><?= htmlspecialchars($news['category'] ?? 'General') ?></td></tr>
                        <tr><th>Published</th><td><?= htmlspecialchars($news['publish_date'] ?? 'Not published') ?></td></tr>
                        <tr><th>Status</th><td><span class="badge bg-<?= ($news['status'] ?? 'draft') === 'published' ? 'success' : 'secondary' ?>"><?= ucfirst($news['status'] ?? 'draft') ?></span></td></tr>
                        <?php if (!empty($news['updated_at'])): ?>
                        <tr><th>Last Updated</th><td><?= htmlspecialchars($news['updated_at'] ?? '') ?></td></tr>
                        <?php endif; ?>
                    </table></div>
                </div>
            </div>
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-danger">Actions</h6></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= $base ?>/admin/news/<?= $news['id'] ?? 0 ?>/toggle-status" class="mb-2">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-<?= ($news['status'] ?? 'draft') === 'published' ? 'warning' : 'success' ?> w-100">
                            <i class="fas fa-<?= ($news['status'] ?? 'draft') === 'published' ? 'eye-slash' : 'eye' ?> me-1"></i>
                            <?= ($news['status'] ?? 'draft') === 'published' ? 'Unpublish' : 'Publish' ?>
                        </button>
                    </form>
                    <form method="POST" action="<?= $base ?>/admin/news/<?= $news['id'] ?? 0 ?>/delete" data-aps-confirm="Delete this article?">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-danger w-100"><i class="fas fa-trash me-1"></i>Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
