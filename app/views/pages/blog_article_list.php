<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold"><?php echo $page_title ?? 'Blog'; ?></h1>
            <p class="text-muted">Stay updated with the latest real estate insights and tips</p>
        </div>
    </div>
    <?php if (!empty($posts)): ?>
        <div class="row">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <?php if (!empty($post['featured_image'])): ?>
                            <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="card-img-top" alt="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-newspaper fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <?php if (!empty($post['category'])): ?>
                                <span class="badge bg-primary mb-2 align-self-start"><?php echo htmlspecialchars($post['category']); ?></span>
                            <?php endif; ?>
                            <h5 class="card-title"><?php echo htmlspecialchars($post['title'] ?? ''); ?></h5>
                            <p class="card-text text-muted small">
                                <i class="far fa-calendar-alt me-1"></i><?php echo date('M d, Y', strtotime($post['created_at'] ?? 'now')); ?>
                            </p>
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars(!empty($post['excerpt']) ? $post['excerpt'] : substr($post['content'] ?? '', 0, 200) . '...'); ?></p>
                            <a href="<?php echo BASE_URL; ?>/blog/<?php echo urlencode($post['slug'] ?? $post['id']); ?>" class="btn btn-outline-primary">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
            <h4>No Blog Articles Yet</h4>
            <p class="text-muted">Check back soon for updates and insights.</p>
        </div>
    <?php endif; ?>
</div>
