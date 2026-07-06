<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/services">Services Directory</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($listing['category_slug'] ?? '') ?>"><?= htmlspecialchars($listing['category_name'] ?? '') ?></a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($listing['business_name']) ?></li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>"><?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?><?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h1 class="h3 mb-1"><?= htmlspecialchars($listing['business_name']) ?>
                                <?php if ($listing['is_verified']): ?><i class="fas fa-check-circle text-info" title="Verified"></i><?php endif; ?>
                                <?php if ($listing['is_featured']): ?><span class="badge bg-warning text-dark ms-2"><i class="fas fa-crown"></i> Featured</span><?php endif; ?>
                            </h1>
                            <p class="text-muted mb-0"><i class="<?= htmlspecialchars($listing['category_icon'] ?? 'fas fa-tag') ?> me-1"></i><?= htmlspecialchars($listing['category_name'] ?? '') ?></p>
                        </div>
                        <?php if ($listing['rating'] > 0): ?>
                            <div class="text-end">
                                <h2 class="text-warning mb-0"><?= number_format($listing['rating'], 1) ?></h2>
                                <span class="text-warning"><?= str_repeat('★', (int)$listing['rating']) ?><?= str_repeat('☆', 5 - (int)$listing['rating']) ?></span>
                                <br><small class="text-muted"><?= $listing['review_count'] ?> reviews</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($listing['photo']): ?>
                        <img src="<?= htmlspecialchars($listing['photo']) ?>" loading="lazy" alt="<?= htmlspecialchars($listing['business_name']) ?>" class="img-fluid rounded mb-3" style="max-height:300px;width:100%;object-fit:cover;">
                    <?php endif; ?>

                    <?php if ($listing['description']): ?>
                        <h5 class="mt-3">About</h5>
                        <p><?= nl2br(htmlspecialchars($listing['description'])) ?></p>
                    <?php endif; ?>

                    <?php if ($listing['experience_years'] > 0): ?>
                        <p><strong>Experience:</strong> <?= $listing['experience_years'] ?> years</p>
                    <?php endif; ?>

                    <?php if ($listing['price_range']): ?>
                        <p><strong>Price Range:</strong> <span class="badge bg-info"><?= ucfirst($listing['price_range']) ?></span></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reviews -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header aps-cp-card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-star text-warning me-2"></i>Reviews (<?= count($reviews) ?>)</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#reviewForm"><i class="fas fa-plus me-1"></i>Write Review</button>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="collapse mb-3" id="reviewForm">
                        <form method="POST" action="<?= BASE_URL ?>/services/add-review">
                            <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                            <div class="mb-2">
                                <label class="form-label">Your Name</label>
                                <input type="text" name="reviewer_name" class="form-control" required value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Rating</label>
                                <div class="star-rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>>
                                        <label for="star<?= $i ?>" class="fs-4 text-warning" style="cursor:pointer;">★</label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-2">
                                <textarea name="review" class="form-control" rows="3" placeholder="Share your experience..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
                        </form>
                    </div>

                    <?php if (empty($reviews)): ?>
                        <p class="text-muted">No reviews yet. Be the first to review!</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $r): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between">
                                    <strong><?= htmlspecialchars($r['reviewer_name'] ?? 'Anonymous') ?></strong>
                                    <span class="text-warning"><?= str_repeat('★', (int)$r['rating']) ?></span>
                                </div>
                                <?php if ($r['review']): ?>
                                    <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($r['review'])) ?></p>
                                <?php endif; ?>
                                <small class="text-muted"><?= date('d M Y', strtotime($r['created_at'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0">Contact Information</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if ($listing['phone']): ?>
                        <p class="mb-2"><i class="fas fa-phone me-2 text-primary"></i><a href="tel:<?= htmlspecialchars($listing['phone']) ?>"><?= htmlspecialchars($listing['phone']) ?></a></p>
                    <?php endif; ?>
                    <?php if ($listing['whatsapp']): ?>
                        <p class="mb-2"><i class="fab fa-whatsapp me-2 text-success"></i><a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $listing['whatsapp']) ?>" target="_blank"><?= htmlspecialchars($listing['whatsapp']) ?></a></p>
                    <?php endif; ?>
                    <?php if ($listing['email']): ?>
                        <p class="mb-2"><i class="fas fa-envelope me-2 text-danger"></i><a href="mailto:<?= htmlspecialchars($listing['email']) ?>"><?= htmlspecialchars($listing['email']) ?></a></p>
                    <?php endif; ?>
                    <?php if ($listing['website']): ?>
                        <p class="mb-2"><i class="fas fa-globe me-2 text-info"></i><a href="<?= htmlspecialchars($listing['website']) ?>" target="_blank">Visit Website</a></p>
                    <?php endif; ?>
                    <?php if ($listing['address']): ?>
                        <p class="mb-2"><i class="fas fa-map-marker-alt me-2 text-secondary"></i><?= nl2br(htmlspecialchars($listing['address'])) ?><?= $listing['city'] ? ', ' . htmlspecialchars($listing['city']) : '' ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <p class="text-muted small"><?= number_format($listing['views']) ?> views</p>
                    <a href="<?= BASE_URL ?>/services/submit" class="btn btn-outline-primary btn-sm w-100"><i class="fas fa-plus me-1"></i>List Your Business</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.star-rating input { display: none; }
.star-rating label { transition: 0.1s; }
.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label { color: #ffc107 !important; }
</style>
