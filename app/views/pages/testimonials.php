
<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">Client Testimonials</h1>
                <p class="lead">See what our valued clients have to say about their experience with APS Dream Homes.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Grid -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <?php if (!empty($testimonials)): ?>
                <?php foreach ($testimonials as $testimonial): ?>
                    <?php 
                        $name = is_object($testimonial) ? $testimonial->name : $testimonial['name'];
                        $rating = is_object($testimonial) ? $testimonial->rating : $testimonial['rating'];
                        $message = is_object($testimonial) ? $testimonial->message : $testimonial['message'];
                        $date = is_object($testimonial) ? $testimonial->created_at : $testimonial['created_at'];
                        $designation = is_object($testimonial) ? ($testimonial->designation ?? 'Client') : ($testimonial['designation'] ?? 'Client');
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="mb-3 text-warning">
                                    <?php for($i = 0; $i < 5; $i++): ?>
                                        <?php if($i < $rating): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <p class="card-text text-muted mb-4">
                                    <i class="fas fa-quote-left fa-lg text-primary opacity-25 me-2"></i>
                                    <?= htmlspecialchars($message) ?>
                                </p>
                                <div class="d-flex align-items-center mt-auto">
                                    <div class="flex-shrink-0">
                                        <div class="avatar bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                            <?= strtoupper(substr($name, 0, 1)) ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($name) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($designation) ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 text-muted small text-end">
                                <?= date('M d, Y', strtotime($date)) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="empty-state">
                        <i class="far fa-comments fa-4x text-muted mb-3"></i>
                        <h3>No testimonials yet</h3>
                        <p class="text-muted">Be the first to share your experience with us!</p>
                        <a href="<?= BASE_URL ?>contact" class="btn btn-primary mt-3">Share Feedback</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="bg-light py-5 mt-5">
    <div class="container text-center">
        <h2 class="mb-4">Share Your Experience</h2>
        <p class="lead text-muted mb-4">We value your feedback. Let us know how we did.</p>
        <a href="<?= BASE_URL ?>contact" class="btn btn-primary btn-lg">Write a Review</a>
    </div>
</section>
