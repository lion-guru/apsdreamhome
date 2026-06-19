<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>"><?= __('blog_home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/blog"><?= __('blog_blog') ?></a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($post['title'] ?? ''); ?></li>
        </ol>
    </nav>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if (!empty($post['featured_image'])): ?>
                <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="img-fluid rounded mb-4 w-100" alt="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" style="max-height: 400px; object-fit: cover;">
            <?php endif; ?>
            <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($post['title'] ?? ''); ?></h1>
            <p class="text-muted mb-4">
                <i class="far fa-calendar-alt me-1"></i> <?php echo date('F d, Y', strtotime($post['created_at'] ?? 'now')); ?>
                <?php if (!empty($post['category'])): ?>
                    <span class="ms-3"><i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($post['category']); ?></span>
                <?php endif; ?>
            </p>
            <div class="blog-content fs-5 lh-lg">
                <?php echo nl2br(htmlspecialchars($post['content'] ?? '')); ?>
            </div>
            <div class="mt-5">
                <a href="<?php echo BASE_URL; ?>/blog" class="btn btn-outline-secondary">&larr; <?= __('blog_back_to') ?></a>
            </div>
        </div>
    </div>
</div>
