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
                <img src="<?= htmlspecialchars($detailImageUrl ?? '') ?>" class="img-fluid rounded mb-4 w-100" alt="<?= htmlspecialchars($post['title'] ?? '') ?>" >
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

            <!-- Comments Section -->
            <div class="mt-5 pt-4 border-top">
                <h3 class="mb-4"><i class="fas fa-comments me-2"></i>Comments (<?= $comment_count ?? 0 ?>)</h3>

                <?php if (!empty($comment_success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($comment_success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($comment_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($comment_error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Comment Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Leave a Comment</h5>
                        <form method="POST" action="<?= BASE_URL ?>/blog/comment/<?= (int)($post['id'] ?? 0) ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="author_name" required placeholder="Your name" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="author_email" placeholder="your@email.com (not published)">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comment <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="comment" rows="4" required placeholder="Share your thoughts..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Submit Comment</button>
                            <small class="text-muted ms-2">Your comment will be reviewed before publishing.</small>
                        </form>
                    </div>
                </div>

                <!-- Approved Comments -->
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $c): ?>
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;font-size:1.1rem;">
                                    <?= strtoupper(substr(htmlspecialchars($c['author_name'] ?? $c['user_name'] ?? 'G'), 0, 1)) ?>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex align-items-center mb-1">
                                    <strong><?= htmlspecialchars($c['author_name'] ?? $c['user_name'] ?? 'Guest') ?></strong>
                                    <small class="text-muted ms-2">
                                        <i class="far fa-clock me-1"></i><?= date('M d, Y \a\t g:i A', strtotime($c['created_at'])) ?>
                                    </small>
                                </div>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($c['comment'] ?? '')) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center py-3"><i class="far fa-comment-dots me-2"></i>No comments yet. Be the first to share your thoughts!</p>
                <?php endif; ?>
            </div>

            <div class="mt-5">
                <a href="<?php echo BASE_URL; ?>/blog" class="btn btn-outline-secondary">&larr; <?= __('blog_back_to') ?></a>
            </div>
        </div>
    </div>
</div>
