<?php
// Customer Reviews Page - APS Dream Homes
$page_title = $page_title ?? 'Customer Reviews - APS Dream Homes';
$reviews = $reviews ?? [];
$avg_rating = $avg_rating ?? 4.8;
$total_reviews = $total_reviews ?? 0;
$happy_users = $happy_users ?? 100;
$satisfaction = $satisfaction ?? 98;
?>

<section class="py-5 bg-primary text-white" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3"><?= __('reviews_hero_title') ?></h1>
        <p class="lead"><?= __('reviews_hero_desc') ?></p>
        <div class="d-flex justify-content-center gap-4 mt-4">
            <div class="text-center">
                <div class="h2 fw-bold"><?= number_format($avg_rating, 1) ?></div>
                <small><?= __('reviews_avg_rating') ?></small>
            </div>
            <div class="text-center">
                <div class="h2 fw-bold"><?= number_format($happy_users) ?>+</div>
                <small><?= __('reviews_happy_users') ?></small>
            </div>
            <div class="text-center">
                <div class="h2 fw-bold"><?= $satisfaction ?>%</div>
                <small><?= __('reviews_satisfaction') ?></small>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row">
            <?php foreach ($reviews as $review): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-muted fst-italic">"<?php echo htmlspecialchars($review['text']); ?>"</p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:45px;height:45px;font-weight:bold;">
                                <?= strtoupper(substr($review['name'], 0, 1)) ?>
                            </div>
                            <div class="ms-3">
                                <strong><?= htmlspecialchars($review['name']) ?></strong>
                                <div class="small text-muted"><?= htmlspecialchars($review['property']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow border-0">
                    <div class="card-body p-5">
                        <h3 class="text-center mb-4"><?= __('reviews_share_experience') ?></h3>
                        <form method="POST" action="<?= BASE_URL ?>/contact">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><?= __('reviews_your_name') ?></label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><?= __('contact_form_email') ?></label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= __('reviews_property') ?></label>
                                <select class="form-select" name="property">
                                    <option value=""><?= __('reviews_select_property') ?></option>
                                    <option>APS Suryoday</option>
                                    <option>Braj Radha Nagri</option>
                                    <option>Raghunath Nagri</option>
                                    <option>Budh Bihar</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= __('reviews_rating') ?></label>
                                <div>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star fs-4 text-muted rating-star" data-rating="<?= htmlspecialchars($i, ENT_QUOTES, 'UTF-8') ?>" style="cursor:pointer"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= __('reviews_your_review') ?></label>
                                <textarea class="form-control" rows="4" name="review" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100"><?= __('reviews_submit') ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('.rating-star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.dataset.rating;
        document.querySelectorAll('.rating-star').forEach((s, i) => {
            s.classList.toggle('text-warning', i < rating);
            s.classList.toggle('text-muted', i >= rating);
        });
    });
});
</script>
