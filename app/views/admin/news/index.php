<?php
$page_title = 'News Management';
$active_page = 'news';
include APP_PATH . '/views/admin/layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-newspaper me-2"></i>News & Blog Management</h1>
    <a href="/admin/news/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add New Article
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search articles..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="published" <?php echo ($filters['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo ($filters['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($news)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-4">
                                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                <p>No news articles found. <a href="/admin/news/create">Create one</a></p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($news as $article): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($article['title'] ?? ''); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($article['summary'] ?? '', 0, 80)); ?>...</small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($article['created_at'] ?? $article['date'])); ?></td>
                                <td>
                                    <a href="/admin/news/<?php echo $article['id']; ?>/edit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>