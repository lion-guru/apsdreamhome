<?php
$page_title = $page_title ?? __('testi_submit_title', [], 'Share Your Review');
$page_heading = __('testi_submit_heading', [], 'Share Your Review');
$errors = $errors ?? [];
$logged_in = $logged_in ?? false;
$properties = $properties ?? [];
$sessionName = $_SESSION['user_name'] ?? '';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0"><i class="fas fa-pen-fancy me-2"></i><?= __('testi_share_experience', [], 'Share Your Experience') ?></h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e ?? '') ?></li><?php endforeach; ?></ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!$logged_in): ?>
                        <div class="text-center py-4">
                            <div class="display-1 text-muted mb-3"><i class="fas fa-lock"></i></div>
                            <h5 class="mb-3"><?= __('testi_signin_required', [], 'Please sign in to leave a review') ?></h5>
                            <p class="text-muted"><?= __('testi_signin_desc', [], 'Reviews shown here are written by verified APS Dream Home customers — sign in to add yours.') ?></p>
                            <a href="<?= BASE_URL ?>/login?redirect=<?= urlencode('/testimonials/submit') ?>" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> <?= __('testi_signin_cta', [], 'Sign In') ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small mb-4">
                            <i class="fas fa-user-check me-1"></i>
                            <?= __('testi_posting_as', [], 'Posting as') ?> <strong><?= htmlspecialchars($sessionName ?: 'your APS Dream Home account') ?></strong>.
                            <?= __('testi_moderation_note', [], 'Reviews are published after moderation.') ?>
                        </p>

                        <form method="POST" action="<?= BASE_URL ?>/testimonials/submit">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="property_id"><?= __('testi_property', [], 'Property or colony this review is about') ?> *</label>
                                    <select aria-label="<?= __('testi_property', [], 'Property') ?>" class="form-select" name="property_id" id="property_id" required>
                                        <option value=""><?= __('testi_choose_property', [], '— Choose your property —') ?></option>
                                        <?php foreach ($properties as $p): ?>
                                            <option value="<?= (int)$p['id'] ?>" <?= (($_POST['property_id'] ?? '') == $p['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(trim($p['title'])) ?><?= !empty($p['location']) ? ' — ' . htmlspecialchars($p['location']) : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (empty($properties)): ?>
                                        <p class="text-muted small mt-1"><?= __('testi_no_properties', [], 'No active listings yet — reviews cannot be submitted right now.') ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label"><?= __('testi_rating', [], 'Rating') ?> *</label>
                                    <div class="rating-input">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input aria-label="<?= $i ?> <?= __('testi_stars', [], 'stars') ?>" type="radio" name="rating" value="<?= $i ?>" id="r<?= $i ?>" <?= ($_POST['rating'] ?? 5) == $i ? 'checked' : '' ?>>
                                            <label for="r<?= $i ?>" class="me-2"><?= $i ?> ★</label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="content"><?= __('testi_your_testimonial', [], 'Your Review') ?> *</label>
                                    <textarea aria-label="<?= __('testi_your_testimonial', [], 'Your Review') ?>" class="form-control" name="content" id="content" rows="6" required
                                              maxlength="2000"
                                              placeholder="<?= __('testi_placeholder', [], 'Share your experience with us...') ?>"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12 form-check ms-2">
                                    <input class="form-check-input" type="checkbox" name="anonymous" id="anonymous" value="1" <?= !empty($_POST['anonymous']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="anonymous"><?= __('testi_anonymous', [], 'Post as "Verified Buyer" — keep my name private') ?></label>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i> <?= __('testi_submit_btn', [], 'Submit Review') ?>
                                    </button>
                                    <a href="<?= BASE_URL ?>/testimonials" class="btn btn-outline-secondary"><?= __('testi_cancel', [], 'Cancel') ?></a>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
