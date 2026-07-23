<?php
$page_title = $page_title ?? 'News Categories';
$categories = $categories ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-1"><i class="fas fa-tags me-2 text-primary"></i>News Categories</h2>
        <a href="<?php echo $base; ?>/admin/news" class="btn btn-outline-secondary">Back to News</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!empty($categories)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Category</th><th>Articles</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($cat['category']); ?></strong></td>
                                    <td><span class="badge bg-primary"><?php echo $cat['article_count']; ?></span></td>
                                    <td><a href="<?php echo $base; ?>/admin/news?category=<?php echo urlencode($cat['category']); ?>" class="btn btn-sm btn-outline-primary">View Articles</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No categories found. Categories are auto-created when articles are posted.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
