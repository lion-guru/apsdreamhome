<?php
$page_title = $page_title ?? __('testi_submit_title', [], 'Share Your Testimonial');
$page_heading = $page_heading ?? __('testi_submit_heading', [], 'Share Your Testimonial');
$content = $content ?? '';
$errors = $errors ?? [];
$logged_in = $logged_in ?? false;
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
                            <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/testimonials/submit">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('testi_your_name', [], 'Your Name') ?> *</label>
                                <input type="text" class="form-control" name="name" required
                                       value="<?= htmlspecialchars($_POST['name'] ?? ($_SESSION['user_name'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('testi_email', [], 'Email') ?> *</label>
                                <input type="email" class="form-control" name="email" required
                                       value="<?= htmlspecialchars($_POST['email'] ?? ($_SESSION['user_email'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('testi_project_name', [], 'Project Name') ?></label>
                                <input type="text" class="form-control" name="project_name"
                                       value="<?= htmlspecialchars($_POST['project_name'] ?? '') ?>"
                                       placeholder="e.g. Suryoday Heights Phase 1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('testi_location', [], 'Location') ?></label>
                                <input type="text" class="form-control" name="location"
                                       value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
                                       placeholder="e.g. Gorakhpur">
                            </div>
                            <div class="col-12">
                                <label class="form-label"><?= __('testi_rating', [], 'Rating') ?> *</label>
                                <div class="rating-input">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="rating" value="<?= $i ?>" id="r<?= $i ?>" <?= ($_POST['rating'] ?? 5) == $i ? 'checked' : '' ?>>
                                        <label for="r<?= $i ?>" class="me-2" class="style-15810">â˜…</label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><?= __('testi_your_testimonial', [], 'Your Testimonial') ?> *</label>
                                <textarea class="form-control" name="content" rows="6" required
                                          placeholder="<?= __('testi_placeholder', [], 'Share your experience with us...') ?>"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i> <?= __('testi_submit_btn', [], 'Submit Testimonial') ?>
                                </button>
                                <a href="<?= BASE_URL ?>/testimonials" class="btn btn-outline-secondary"><?= __('testi_cancel', [], 'Cancel') ?></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>