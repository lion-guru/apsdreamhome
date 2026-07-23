<!-- Hero Section -->
<section class="news-hero-section text-white py-5" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?= get_asset_url('assets/images/hero-2.jpg') ?>'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold mb-4"><?= __('news_hero_title') ?></h1>
                <p class="lead mb-4"><?= __('news_hero_subtitle') ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <?php if (isset($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <?php if (empty($crumb['url']) || $crumb === end($breadcrumbs)): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['title']) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?><?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['title']) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('breadcrumb_home') ?></a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/news"><?= __('nav_news') ?></a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($news['title'] ?? 'Article') ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- Article Content -->
<main class="py-5">
    <div class="container">
        <?php if (empty($news)): ?>
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center py-5">
                    <i class="far fa-newspaper fa-3x text-muted mb-3"></i>
                    <h3><?= __('news_not_found', [], 'Article Not Found') ?></h3>
                    <p class="text-muted mb-4"><?= __('news_not_found_desc', [], 'The article you are looking for does not exist or has been removed.') ?></p>
                    <a href="<?= BASE_URL ?>/news" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i><?= __('news_back_to_news', [], 'Back to News') ?>
                    </a>
                </div>
            </div>
        <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if (!empty($news['image'])): ?>
                    <?php
                    $newsImage = $news['image'];
                    $newsImageUrl = str_starts_with($newsImage, 'http') ? $newsImage : get_asset_url($newsImage);
                    ?>
                    <img src="<?= htmlspecialchars($newsImageUrl) ?>" alt="<?= htmlspecialchars($news['title'] ?? '') ?>" class="img-fluid rounded mb-4 w-100" style="max-height: 450px; object-fit: cover;">
                <?php endif; ?>

                <div class="d-flex align-items-center gap-3 mb-4">
                    <?php if (!empty($news['category'])): ?>
                        <span class="badge bg-primary fs-6"><?= htmlspecialchars($news['category']) ?></span>
                    <?php endif; ?>
                    <small class="text-muted">
                        <i class="far fa-calendar-alt me-1"></i>
                        <?= date('F d, Y', strtotime($news['created_at'] ?? 'now')) ?>
                    </small>
                    <?php if (!empty($news['views'])): ?>
                        <small class="text-muted">
                            <i class="fas fa-eye me-1"></i><?= number_format($news['views']) ?> views
                        </small>
                    <?php endif; ?>
                </div>

                <h1 class="display-6 fw-bold mb-4"><?= htmlspecialchars($news['title'] ?? '') ?></h1>

                <?php if (!empty($news['summary'])): ?>
                    <div class="lead text-muted mb-4 border-start border-primary border-3 ps-3">
                        <?= htmlspecialchars($news['summary']) ?>
                    </div>
                <?php endif; ?>

                <div class="news-article-content fs-5 lh-lg">
                    <?= nl2br(htmlspecialchars($news['content'] ?? '')) ?>
                </div>

                <hr class="my-5">

                <div class="d-flex justify-content-between align-items-center">
                    <a href="<?= BASE_URL ?>/news" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to News
                    </a>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="shareArticle('facebook')">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="shareArticle('twitter')">
                            <i class="fab fa-twitter"></i>
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="shareArticle('whatsapp')">
                            <i class="fab fa-whatsapp"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
function shareArticle(platform) {
    var url = encodeURIComponent(window.location.href);
    var title = encodeURIComponent('<?= addslashes($news['title'] ?? 'APS Dream Home News') ?>');
    var shareUrl = '';
    switch(platform) {
        case 'facebook': shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + url; break;
        case 'twitter': shareUrl = 'https://twitter.com/intent/tweet?url=' + url + '&text=' + title; break;
        case 'whatsapp': shareUrl = 'https://wa.me/?text=' + title + '%20' + url; break;
    }
    if (shareUrl) window.open(shareUrl, '_blank', 'width=600,height=400');
}
</script>
