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
                <?php
                $detailImage = $post['featured_image'];
                $detailImageUrl = str_starts_with($detailImage, 'http') ? $detailImage : get_asset_url($detailImage);
                ?>
                <img src="<?= htmlspecialchars($detailImageUrl ?? '') ?>" class="img-fluid rounded mb-4 w-100" alt="<?= htmlspecialchars($post['title'] ?? '') ?>" class="style-44644">
            <?php endif; ?>
            <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($post['title'] ?? ''); ?></h1>
            <p class="text-muted mb-4">
                <i class="far fa-calendar-alt me-1"></i> <?= date('F d, Y', strtotime($post['created_at'] ?? 'now')) ?>
                <?php if (!empty($post['category'])): ?>
                    <span class="ms-3"><i class="fas fa-tag me-1"></i> <?= htmlspecialchars($post['category'] ?? '') ?></span>
                <?php endif; ?>
                <?php if (!empty($post['read_time'])): ?>
                    <span class="ms-3"><i class="fas fa-clock me-1"></i> <?= (int)$post['read_time'] ?> min read</span>
                <?php endif; ?>
                <?php if (!empty($post['views'])): ?>
                    <span class="ms-3"><i class="fas fa-eye me-1"></i> <?= number_format($post['views']) ?> views</span>
                <?php endif; ?>
            </p>
            <div class="blog-content fs-5 lh-lg">
                <?= nl2br(htmlspecialchars($post['content'] ?? '')) ?>
            </div>
            <div class="mt-5">
                <a href="<?php echo BASE_URL; ?>/blog" class="btn btn-outline-secondary">&larr; <?= __('blog_back_to') ?></a>
            </div>
        </div>
    </div>
</div>
