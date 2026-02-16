<?php
// app/views/pages/news.php
$extra_css = '
<link rel="stylesheet" href="' . get_asset_url("css/common.css") . '">
<link rel="stylesheet" href="' . get_asset_url("css/news.css") . '">
';

$extra_js = '
<script src="' . get_asset_url("js/common.js") . '"></script>
<script src="' . get_asset_url("js/news.js") . '"></script>
';
?>

<!-- Hero Section -->
<section class="hero-section bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">News & Updates</h1>
                <p class="lead text-muted">Stay informed with the latest real estate news and market trends.</p>
                <div class="mt-4">
                    <form class="search-form" id="newsSearch" action="/news" method="GET">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control" placeholder="Search news..." id="searchInput" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?= get_asset_url('images/news-hero.jpg') ?>" alt="News Hero" class="img-fluid rounded-3 shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <?php foreach ($breadcrumbs as $crumb): ?>
                <?php if (isset($crumb['active']) && $crumb['active']): ?>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['title']) ?></li>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title']) ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

<!-- News Content -->
<main id="main-content" class="py-5">
    <div class="container">
        <!-- Category Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="news-filter text-center">
                    <a href="/news" class="btn <?= $pagination['current_category'] === 'all' ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2">
                        All News
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="/news?category=<?= urlencode($cat) ?>"
                            class="btn <?= $pagination['current_category'] === $cat ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2">
                            <?= htmlspecialchars(ucwords($cat)) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- News Grid -->
        <div class="row g-4">
            <?php if (empty($news_items)): ?>
                <div class="col-12 text-center py-5">
                    <div class="empty-state">
                        <i class="far fa-newspaper fa-3x text-muted mb-3"></i>
                        <h3>No news found</h3>
                        <p class="text-muted">There are no news items to display at this time.</p>
                        <?php if ($pagination['current_category'] !== 'all'): ?>
                            <a href="/news" class="btn btn-primary mt-3">View All News</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($news_items as $news): ?>
                    <div class="col-lg-4 col-md-6">
                        <article class="news-card h-100 shadow-sm rounded overflow-hidden bg-white">
                            <div class="news-image position-relative">
                                <?php
                                $imagePath = !empty($news->image) ? $news->image : 'img/default-news.jpg';
                                $imageUrl = get_asset_url($imagePath);
                                ?>
                                <img src="<?= $imageUrl ?>"
                                    alt="<?= htmlspecialchars($news->title) ?>"
                                    class="img-fluid w-100" style="height: 250px; object-fit: cover;">
                                <div class="news-category position-absolute top-0 start-0 m-3">
                                    <span class="badge bg-primary">News</span>
                                </div>
                            </div>
                            <div class="news-content p-4">
                                <h3 class="news-title h5 mb-3">
                                    <a href="/news/view/<?= htmlspecialchars($news->id) ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($news->title) ?>
                                    </a>
                                </h3>
                                <div class="news-meta text-muted small mb-3">
                                    <span class="news-date me-3">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?= date('M d, Y', strtotime($news->created_at)) ?>
                                    </span>
                                </div>
                                <p class="news-excerpt text-muted mb-4">
                                    <?= htmlspecialchars(substr(strip_tags($news->summary ?? $news->content), 0, 150)) ?>...
                                </p>
                                <a href="/news/view/<?= htmlspecialchars($news->id) ?>" class="btn btn-outline-primary btn-sm stretched-link">
                                    Read More
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page -->
                    <li class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="/news?page=<?= $pagination['current_page'] - 1 ?>&category=<?= urlencode($pagination['current_category']) ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="/news?page=<?= $i ?>&category=<?= urlencode($pagination['current_category']) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Page -->
                    <li class="page-item <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="/news?page=<?= $pagination['current_page'] + 1 ?>&category=<?= urlencode($pagination['current_category']) ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

        <!-- Newsletter CTA -->
        <div class="newsletter-cta text-center mt-5 py-5 bg-light rounded-3">
            <h3>Stay Updated</h3>
            <p class="text-muted mb-4">Subscribe to our newsletter for the latest real estate news and market insights</p>
            <form class="newsletter-form" id="newsletterForm">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Enter your email" required>
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>