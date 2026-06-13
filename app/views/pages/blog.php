<!-- Hero Section -->
<section class="page-hero" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?= get_asset_url('assets/images/hero-2.jpg') ?>'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold mb-4"><?= __('blog_hero_title') ?></h1>
                <p class="lead mb-4"><?= __('blog_hero_lead') ?></p>
                <p class="mb-0"><?= __('blog_hero_desc') ?></p>
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
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('home') ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= __('blog') ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h3 class="mb-3"><?= __('blog_newsletter_title') ?></h3>
                <p class="mb-4"><?= __('blog_newsletter_desc') ?></p>
                <form class="d-flex gap-2">
                    <input type="email" class="form-control" placeholder="<?= __('blog_newsletter_ph_email') ?>" required>
                    <button type="submit" class="btn btn-light">
                        <i class="fas fa-envelope me-1"></i><?= __('subscribe') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="section-padding">
    <div class="container">
        <!-- Filter Buttons -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="text-center">
                    <button class="filter-btn active" data-category="all"><?= __('blog_filter_all') ?></button>
                    <?php foreach ($categories as $category): ?>
                        <button class="filter-btn" data-category="<?php echo htmlspecialchars($category['category']); ?>">
                            <?php echo ucfirst(htmlspecialchars($category['category'])); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Featured Post -->
        <?php if (!empty($blog_posts)): ?>
            <div class="row mb-5">
                <div class="col-12">
                    <h3 class="mb-4"><?= __('blog_featured') ?></h3>
                </div>
                <div class="col-12">
                    <div class="card blog-card shadow-sm">
                        <div class="position-relative">
                            <?php
                            $featuredImage = !empty($blog_posts[0]['featured_image']) ? $blog_posts[0]['featured_image'] : 'assets/images/blog-placeholder.jpg';
                            $featuredImageUrl = get_asset_url($featuredImage);
                            ?>
                            <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="img-fluid"
                                class="card-img-top blog-image" alt="Featured Post" />
                            <div class="category-badge">
                                <?php echo ucfirst(htmlspecialchars($blog_posts[0]['category'])); ?>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('M d, Y', strtotime($blog_posts[0]['created_at'])); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo htmlspecialchars($blog_posts[0]['read_time']); ?> min read
                                </small>
                            </div>
                            <h3 class="card-title mb-3"><?php echo htmlspecialchars($blog_posts[0]['title']); ?></h3>
                            <p class="card-text mb-3"><?php echo htmlspecialchars(substr($blog_posts[0]['excerpt'], 0, 200)) . '...'; ?></p>
                            <a href="blog-post.php?id=<?php echo $blog_posts[0]['id']; ?>" class="read-more-btn">
                                <i class="fas fa-arrow-right me-1"></i><?= __('blog_read_more') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- All Posts -->
        <div class="row">
            <div class="col-12 mb-4">
                <h3><?= __('blog_latest') ?></h3>
            </div>

            <?php for ($i = 1; $i < count($blog_posts); $i++): ?>
                <div class="col-lg-4 col-md-6" data-category="<?php echo htmlspecialchars($blog_posts[$i]['category']); ?>">
                    <div class="card blog-card shadow-sm h-100">
                        <div class="position-relative">
                            <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="img-fluid"
                                class="card-img-top blog-image" alt="Blog Post" />
                            <div class="category-badge">
                                <?php echo ucfirst(htmlspecialchars($blog_posts[$i]['category'])); ?>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('M d, Y', strtotime($blog_posts[$i]['created_at'])); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo htmlspecialchars($blog_posts[$i]['read_time']); ?> min
                                </small>
                            </div>
                            <h6 class="card-title mb-2"><?php echo htmlspecialchars($blog_posts[$i]['title']); ?></h6>
                            <p class="card-text small text-muted mb-3">
                                <?php echo htmlspecialchars(substr($blog_posts[$i]['excerpt'], 0, 100)) . '...'; ?>
                            </p>
                            <a href="blog-post.php?id=<?php echo $blog_posts[$i]['id']; ?>" class="read-more-btn btn-sm">
                                <i class="fas fa-arrow-right me-1"></i><?= __('blog_read_more') ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Load More Button -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <button class="btn btn-outline-primary btn-lg" id="loadMore">
                    <i class="fas fa-plus me-2"></i><?= __('blog_load_more') ?>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h3 class="fw-bold"><?= __('blog_explore_title') ?></h3>
                <p class="lead text-muted"><?= __('blog_explore_desc') ?></p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="card text-center p-4 h-100">
                    <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                    <h6><?= __('blog_cat_market') ?></h6>
                    <p class="small text-muted"><?= __('blog_cat_market_desc') ?></p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card text-center p-4 h-100">
                    <i class="fas fa-coins fa-3x text-success mb-3"></i>
                    <h6><?= __('blog_cat_investment') ?></h6>
                    <p class="small text-muted"><?= __('blog_cat_investment_desc') ?></p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card text-center p-4 h-100">
                    <i class="fas fa-home fa-3x text-info mb-3"></i>
                    <h6><?= __('blog_cat_buying') ?></h6>
                    <p class="small text-muted"><?= __('blog_cat_buying_desc') ?></p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card text-center p-4 h-100">
                    <i class="fas fa-balance-scale fa-3x text-warning mb-3"></i>
                    <h6><?= __('blog_cat_legal') ?></h6>
                    <p class="small text-muted"><?= __('blog_cat_legal_desc') ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const blogCards = document.querySelectorAll('[data-category]');

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const category = this.getAttribute('data-category');

                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Filter posts
                blogCards.forEach(card => {
                    if (category === 'all' || card.getAttribute('data-category') === category) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Load more functionality
        document.getElementById('loadMore').addEventListener('click', function() {
            // In a real application, this would load more posts via AJAX
            alert('Load more functionality would be implemented here');
        });
    });
</script>