<!-- Hero Section -->
<section class="testimonial-hero text-center" style="background-image: url('<?= get_asset_url('assets/images/hero-1.jpg') ?>');">
    <div class="container">
        <h1 class="display-4 fw-bold">Client Testimonials</h1>
        <p class="lead mb-0">What our clients say about us</p>
    </div>
</section>

<style>
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
    }

    .star-rating input {
        display: none;
    }

    .star-rating label {
        cursor: pointer;
        font-size: 1.5rem;
        color: #ddd;
        margin: 0 2px;
    }

    .star-rating input:checked~label,
    .star-rating label:hover,
    .star-rating label:hover~label {
        color: #ffc107;
    }
</style>
<?php if (isset($breadcrumbs)): ?>
    <?php foreach ($breadcrumbs as $crumb): ?>
        <?php if (empty($crumb['url']) || $crumb === end($breadcrumbs)): ?>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['title']) ?></li>
        <?php else: ?>
            <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title']) ?></a></li>
        <?php endif; ?>
    <?php endforeach; ?>
<?php else: ?>
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Testimonials</li>
<?php endif; ?>
</ol>
</nav>
</div>
</div>

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
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <?php if ($i < $rating): ?>
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

<!-- Submit Testimonial Section -->
<section class="bg-light py-5 mt-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white py-4">
                        <h3 class="mb-0"><i class="fas fa-pen-fancy me-2"></i>Share Your Experience</h3>
                        <p class="mb-0 opacity-75">Your feedback helps others make informed decisions</p>
                    </div>
                    <div class="card-body p-4">
                        <form id="testimonialForm" method="POST" action="<?php echo BASE_URL; ?>/testimonials/submit">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="phone" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control" placeholder="e.g., Gorakhpur">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Property Type</label>
                                    <select name="property_type" class="form-select">
                                        <option value="">Select...</option>
                                        <option value="Residential Plot">Residential Plot</option>
                                        <option value="Villa">Villa</option>
                                        <option value="Apartment">Apartment</option>
                                        <option value="Commercial">Commercial</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Rating *</label>
                                    <div class="star-rating">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo $i === 5 ? 'checked' : ''; ?>>
                                            <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Review Title *</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g., Best Investment Decision!" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Your Experience *</label>
                                <textarea name="testimonial" class="form-control" rows="4" placeholder="Share your experience with APS Dream Homes..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Photo (Optional)</label>
                                <input type="file" name="photo" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-paper-plane me-2"></i>Submit Review
                            </button>
                            <p class="text-muted small mt-2 mb-0">
                                <i class="fas fa-info-circle me-1"></i>Your review will be moderated before appearing publicly.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>