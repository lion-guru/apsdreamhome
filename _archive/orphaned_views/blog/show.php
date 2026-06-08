<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($post['title'] ?? 'Blog Post'); ?></h5>
                    <div>
                        <a href="<?php echo BASE_URL; ?>/admin/blog/<?php echo $post['id'] ?? ''; ?>/edit" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                        <a href="<?php echo BASE_URL; ?>/admin/blog" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <?php if (isset($post['status']) && $post['status'] == 'published'): ?>
                            <span class="badge bg-success">Published</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Draft</span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <strong>Created:</strong> <?php echo $post['created_at'] ?? '-'; ?>
                    </div>
                    <hr>
                    <div class="blog-content">
                        <?php echo nl2br(htmlspecialchars($post['content'] ?? '')); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
