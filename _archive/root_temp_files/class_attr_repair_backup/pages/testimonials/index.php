<?php
$page_title = $page_title ?? __('testi_page_title', [], 'Customer Testimonials');
$page_heading = $page_heading ?? __('testi_page_heading', [], 'Testimonials');
$content = $content ?? '';
$testimonials = $testimonials ?? [];
$featured = $featured ?? [];
$stats = $stats ?? [];
?>
<style>
.testimonial-hero { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; padding: 60px 0; }
.testimonial-card { background: white; border-radius: 16px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s; position: relative; height: 100%; }
.testimonial-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.12); }
.testimonial-card::before { content: '"'; position: absolute; top: 16px; left: 20px; font-size: 64px; color: #f59e0b; opacity: 0.2; font-family: Georgia, serif; line-height: 1; }
.testimonial-rating { color: #f59e0b; }
.testimonial-avatar { width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 20px; }
</style>

<section class="testimonial-hero">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-quote-left me-2"></i><?= __('testi_hero_heading', [], 'Customer Testimonials') ?></h1>
        <p class="lead mb-0 opacity-90"><?= __('testi_hero_subtitle', [], 'Real stories from real customers who found their dream homes with us') ?></p>
        <?php if (($stats['avg_rating'] ?? 0) > 0): ?>
            <div class="mt-4">
                <div class="testimonial-rating fs-3">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star<?= $i <= round($stats['avg_rating']) ? '' : '-half-alt' ?>"></i>
                    <?php endfor; ?>
                </div>
                <p class="mt-2 mb-0"><strong><?= number_format($stats['avg_rating'], 1) ?></strong> <?= __('testi_avg_rating', [], 'average rating from') ?> <strong><?= $stats['approved_reviews'] ?? 0 ?></strong> <?= __('testi_reviews', [], 'reviews') ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><?= __('testi_featured_stories', [], 'Featured Stories') ?></h2>
        <a href="<?= BASE_URL ?>/testimonials/submit" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i> <?= __('testi_share_your_story', [], 'Share Your Story') ?>
        </a>
    </div>

    <?php if (!empty($featured)): ?>
        <div class="row g-4 mb-5">
            <?php foreach (array_slice($featured, 0, 3) as $t):
                $customerName = $t['customer_name'] ?? 'Happy Customer';
                $initials = strtoupper(substr($customerName, 0, 1));
                if (strpos($customerName, ' ') !== false) {
                    $parts = explode(' ', $customerName);
                    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                }
            ?>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating mb-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?= $i <= ($t['rating'] ?? 5) ? '' : '-half-alt' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="mb-4"><?= htmlspecialchars($t['testimonial'] ?? $t['content'] ?? '') ?></p>
                        <div class="d-flex align-items-center">
                            <div class="testimonial-avatar me-3"><?= $initials ?></div>
                            <div>
                                <strong class="d-block"><?= htmlspecialchars($t['customer_name'] ?? '') ?></strong>
                                <small class="text-muted">
                                    <?php if ($t['project_name']): ?>
                                        <i class="fas fa-building me-1"></i><?= htmlspecialchars($t['project_name'] ?? '') ?>
                                    <?php endif; ?>
                                    <?php if ($t['location']): ?>
                                        <br><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($t['location'] ?? '') ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h3 class="mb-4"><?= __('testi_all_stories', [], 'All Customer Stories') ?></h3>
    <?php if (empty($testimonials)): ?>
        <div class="text-center py-5">
            <div class="display-1 text-muted mb-3"><i class="fas fa-quote-right"></i></div>
            <h4 class="text-muted"><?= __('testi_no_testimonials', [], 'No testimonials yet') ?></h4>
            <p class="text-muted"><?= __('testi_be_first', [], 'Be the first to share your experience!') ?></p>
            <a href="<?= BASE_URL ?>/testimonials/submit" class="btn btn-primary"><?= __('testi_share_your_story', [], 'Share Your Story') ?></a>
        </div>
    <?php else: ?>
        <div class="row g-4">
<?php foreach ($testimonials as $t):
                $customerName = $t['customer_name'] ?? 'Happy Customer';
                $initials = strtoupper(substr($customerName, 0, 1));
                if (strpos($customerName, ' ') !== false) {
                    $parts = explode(' ', $customerName);
                    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                }
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating mb-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?= $i <= ($t['rating'] ?? 5) ? '' : '-half-alt' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="mb-4">"<?= htmlspecialchars($t['testimonial'] ?? $t['content'] ?? '') ?>"</p>
                        <div class="d-flex align-items-center">
                            <div class="testimonial-avatar me-3"><?= $initials ?></div>
                            <div>
                                <strong class="d-block"><?= htmlspecialchars($customerName ?? '') ?></strong>
                                <small class="text-muted">
                                    <?php if ($t['project_name']): ?>
                                        <i class="fas fa-building me-1"></i><?= htmlspecialchars($t['project_name'] ?? '') ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        <?php if (!empty($t['is_featured'])): ?>
                            <span class="position-absolute top-0 end-0 badge bg-warning text-dark m-3">
                                <i class="fas fa-star"></i> <?= __('testi_featured', [], 'Featured') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="text-center py-5 mt-4">
        <div class="card border-0 shadow-sm" class="style-36276">
            <div class="card-body p-5">
                <h2 class="fw-bold mb-3"><?= __('testi_cta_heading', [], 'Bought a property from us?') ?></h2>
                <p class="lead mb-4 opacity-90"><?= __('testi_cta_subtitle', [], 'Share your experience and help other customers make the right decision') ?></p>
                <a href="<?= BASE_URL ?>/testimonials/submit" class="btn btn-light btn-lg">
                    <i class="fas fa-pen-fancy me-2"></i> <?= __('testi_write', [], 'Write a Testimonial') ?>
                </a>
            </div>
        </div>
    </div>
</div>