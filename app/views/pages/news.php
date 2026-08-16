<!-- Hero Section -->
<section class="news-hero-section text-white py-5" style="background: linear-gradient(135deg, #0f172a, #1e3a5f);">
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
                            <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title']) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('breadcrumb_home') ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= __('nav_news') ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- News Content -->
<main id="main-content" class="py-5">
    <div class="container">
        <!-- Category Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="news-filter text-center">
                    <a href="<?= BASE_URL ?>/news" class="btn btn-primary me-2 mb-2"><?= __('news_all') ?></a>
                    <?php foreach (($categories ?? []) as $cat): ?>
                        <a href="<?= BASE_URL ?>/news?category=<?= urlencode($cat) ?>"
                            class="btn btn-outline-primary me-2 mb-2">
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
                        <h3><?= __('news_not_found') ?></h3>
                        <p class="text-muted"><?= __('news_empty_desc') ?></p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($news_items as $news): ?>
                    <div class="col-lg-4 col-md-6">
                        <article class="news-card h-100 shadow-sm rounded overflow-hidden bg-white">
                            <div class="news-image position-relative">
                                <?php
                                $imagePath = !empty($news['image']) ? $news['image'] : 'assets/images/property-placeholder.jpg';
                                $imageUrl = (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0)
                                    ? $imagePath
                                    : get_asset_url($imagePath);
                                ?>
                                <img src="<?= htmlspecialchars($imageUrl) ?>"
                                    alt="<?= htmlspecialchars($news['title'] ?? '') ?>"
                                    class="img-fluid w-100" class="style-27608" loading="lazy">
                                <?php if (!empty($news['category'])): ?>
                                <div class="news-category position-absolute top-0 start-0 m-3">
                                    <span class="badge bg-primary"><?= htmlspecialchars($news['category']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="news-content p-4">
                                <h3 class="news-title h5 mb-3">
                                    <a href="<?= BASE_URL ?>/news/view/<?= htmlspecialchars($news['id'] ?? '') ?>" class="text-decoration-none text-dark stretched-link">
                                        <?= htmlspecialchars($news['title'] ?? '') ?>
                                    </a>
                                </h3>
                                <div class="news-meta text-muted small mb-3">
                                    <span class="news-date me-3">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?= date('M d, Y', strtotime($news['created_at'] ?? $news['date'] ?? 'now')) ?>
                                    </span>
                                </div>
                                <p class="news-excerpt text-muted mb-4">
                                    <?= htmlspecialchars(mb_substr(strip_tags($news['summary'] ?? $news['content'] ?? ''), 0, 150)) ?>...
                                </p>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Newsletter CTA -->
        <div class="newsletter-cta text-center mt-5 py-5 bg-light rounded-3">
            <h3><?= __('news_subscribe_title') ?></h3>
            <p class="text-muted mb-4"><?= __('news_subscribe_desc') ?></p>
            <form class="newsletter-form" id="newsletterForm">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="email" class="form-control" name="email" placeholder="<?= __('newsletter_email_placeholder') ?>" required>
                            <button class="btn btn-primary" type="submit"><?= __('newsletter_subscribe_btn') ?></button>
                        </div>
                        <div id="newsletterMsg" class="mt-2 small" class="style-2248"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>
<script>
document.getElementById('newsletterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = this.querySelector('button');
    var msg = document.getElementById('newsletterMsg');
    var email = this.querySelector('input[name="email"]').value;
    btn.disabled = true;
    btn.textContent = '...';
    fetch('<?= BASE_URL ?>/api/newsletter', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({email: email})
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        msg.style.display = 'block';
        if (d.success) {
            msg.className = 'mt-2 small text-success';
            msg.textContent = 'âœ" Subscribed successfully!';
            btn.textContent = 'Subscribed!';
            btn.classList.replace('btn-primary', 'btn-success');
            this.reset();
        } else {
            msg.className = 'mt-2 small text-danger';
            msg.textContent = d.message || 'Already subscribed or invalid email.';
            btn.disabled = false;
            btn.textContent = 'Subscribe';
        }
    }.bind(this))
    .catch(function() {
        msg.style.display = 'block';
        msg.className = 'mt-2 small text-danger';
        msg.textContent = 'Network error. Please try again.';
        btn.disabled = false;
        btn.textContent = 'Subscribe';
    });
});
</script>
