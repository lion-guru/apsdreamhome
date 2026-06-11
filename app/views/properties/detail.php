<?php
$property = $data['property'] ?? null;
$property_images = $data['property_images'] ?? [];
$related = $data['related_properties'] ?? [];
$reviews = $data['reviews'] ?? [];
$images = !empty($property_images) ? $property_images : [['image_path' => 'https://via.placeholder.com/800x400?text=No+Image']];
?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/properties">Properties</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($property['title'] ?? 'Property'); ?></li>
        </ol>
    </nav>

    <?php if ($property): ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner rounded-top">
                            <?php foreach ($images as $i => $img):
                            ?>
                                <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                                    <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg"
                                        class="d-block w-100 gallery-trigger" style="height: 400px; object-fit: cover; cursor: pointer;"
                                        alt="<?php echo htmlspecialchars($property['title'] ?? ''); ?>"
                                        onclick="openLightbox(<?php echo $i; ?>)">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($images) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        <?php endif; ?>
                    </div>
                    <!-- Thumbnail Gallery -->
                    <?php if (count($images) > 1): ?>
                    <div class="d-flex gap-1 mt-1 overflow-auto">
                        <?php foreach ($images as $i => $img): ?>
                        <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg"
                            class="rounded" style="height: 60px; width: 80px; object-fit: cover; cursor: pointer; border: 2px solid <?php echo $i === 0 ? '#0d6efd' : 'transparent'; ?>;"
                            onclick="$('#propertyCarousel').carousel(<?php echo $i; ?>); openLightbox(<?php echo $i; ?>);"
                            alt="Thumbnail">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Lightbox -->
                    <div id="propertyLightbox" class="lightbox-overlay" style="display:none;" onclick="closeLightbox(event)">
                        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
                        <span class="lightbox-prev" onclick="changeLightbox(-1)">&#10094;</span>
                        <span class="lightbox-next" onclick="changeLightbox(1)">&#10095;</span>
                        <div class="lightbox-content">
                            <img loading="lazy" id="lightboxImage" src="" alt="Full size image">
                            <div id="lightboxCounter" class="lightbox-counter"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="card-title mb-1"><?php echo htmlspecialchars($property['title'] ?? $property['name'] ?? 'Property'); ?></h3>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($property['location'] ?? $property['address'] ?? 'Location not specified'); ?>
                                </p>
                            </div>
                            <span class="badge bg-success fs-6"><?php echo ucfirst($property['status'] ?? 'available'); ?></span>
                        </div>

                        <div class="row mb-4">
                            <?php if (!empty($property['price'])): ?>
                                <div class="col-md-4 text-center">
                                    <h4 class="text-primary mb-0">₹<?php echo number_format($property['price']); ?></h4>
                                    <small class="text-muted"><?php echo $property['price_type'] ?? 'Total Price'; ?></small>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['area']) || !empty($property['size'])): ?>
                                <div class="col-md-4 text-center border-start">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($property['area'] ?? $property['size'] ?? 'N/A'); ?></h5>
                                    <small class="text-muted"><?php echo $property['area_unit'] ?? 'Area'; ?></small>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['property_type'])): ?>
                                <div class="col-md-4 text-center border-start">
                                    <h5 class="mb-0"><?php echo ucfirst($property['property_type']); ?></h5>
                                    <small class="text-muted">Property Type</small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($property['description'])): ?>
                            <h5>Description</h5>
                            <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                        <?php endif; ?>

                        <h5 class="mt-4">Property Details</h5>
                        <div class="row">
                            <?php if (!empty($property['bedrooms'])): ?>
                                <div class="col-md-4 mb-2">
                                    <i class="fas fa-bed text-primary me-2"></i> <?php echo $property['bedrooms']; ?> Bedrooms
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['bathrooms'])): ?>
                                <div class="col-md-4 mb-2">
                                    <i class="fas fa-bath text-primary me-2"></i> <?php echo $property['bathrooms']; ?> Bathrooms
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['parking'])): ?>
                                <div class="col-md-4 mb-2">
                                    <i class="fas fa-car text-primary me-2"></i> <?php echo $property['parking']; ?> Parking
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['furnished'])): ?>
                                <div class="col-md-4 mb-2">
                                    <i class="fas fa-couch text-primary me-2"></i> <?php echo ucfirst($property['furnished']); ?> Furnished
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['facing'])): ?>
                                <div class="col-md-4 mb-2">
                                    <i class="fas fa-compass text-primary me-2"></i> <?php echo ucfirst($property['facing']); ?> Facing
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['floor'])): ?>
                                <div class="col-md-4 mb-2">
                                    <i class="fas fa-building text-primary me-2"></i> Floor: <?php echo $property['floor']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php
                        $amenities = [];
                        if (!empty($property['amenities'])) {
                            if (is_string($property['amenities'])) {
                                $amenities = json_decode($property['amenities'], true) ?? [$property['amenities']];
                            } else {
                                $amenities = (array)$property['amenities'];
                            }
                        }
                        ?>
                        <?php if (!empty($amenities)): ?>
                            <h5 class="mt-4">Amenities</h5>
                            <div class="row">
                                <?php foreach ($amenities as $amenity): ?>
                                    <div class="col-md-4 mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i> <?php echo htmlspecialchars($amenity); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property['rera_number'])): ?>
                            <div class="alert alert-info mt-4">
                                <i class="fas fa-certificate me-2"></i>
                                RERA Registered: <?php echo htmlspecialchars($property['rera_number']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-phone me-2"></i>Enquire About This Property</h5>
                    </div>
                    <div class="card-body">
                        <form action="/contact" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label">Your Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mobile Number</label>
                                <input type="tel" name="mobile" class="form-control" pattern="[0-9]{10}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="3">I'm interested in this property. Please contact me.</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i>Send Enquiry
                            </button>
                        </form>
                        <hr>
                        <div class="d-grid gap-2">
                            <?php if (isset($property) && !empty($property['price'])): ?>
                                <a href="<?= BASE_URL ?>/payment/initiate?property_id=<?= $property['id'] ?? 0 ?>&amount=<?= $property['price'] ?? 0 ?>" class="btn btn-success btn-lg w-100 mb-2">
                                    <i class="fas fa-credit-card me-2"></i>Buy Now - ₹<?= number_format($property['price'] ?? 0) ?>
                                </a>
                            <?php endif; ?>
                            <a href="tel:+919277121112" class="btn btn-success">
                                <i class="fas fa-phone me-2"></i>Call Now
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (!empty($related)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Related Properties</h6>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php foreach (array_slice($related, 0, 3) as $rel): ?>
                                <li class="list-group-item">
                                    <a href="/properties/<?php echo $rel['id']; ?>" class="text-decoration-none">
                                        <div class="d-flex">
                                            <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="img-fluid"
                                                class="rounded me-2" style="width: 60px; height: 45px; object-fit: cover;" />
                                            <div>
                                                <small class="fw-bold"><?php echo htmlspecialchars($rel['title'] ?? $rel['name'] ?? 'Property'); ?></small>
                                                <br><small class="text-primary">₹<?php echo number_format($rel['price'] ?? 0); ?></small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <h4>Property Not Found</h4>
            <p>The property you're looking for doesn't exist or has been removed.</p>
            <a href="/properties" class="btn btn-primary">Browse Properties</a>
        </div>
    <?php endif; ?>

    <!-- Customer Reviews Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Customer Reviews</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong><?php echo htmlspecialchars($review['user_name'] ?? $review['name'] ?? 'Anonymous'); ?></strong>
                                    <small class="text-muted"><?php echo date('d M Y', strtotime($review['created_at'])); ?></small>
                                </div>
                                <div class="mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa<?php echo $i <= $review['rating'] ? 's' : 'r'; ?> fa-star text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-4"><i class="fas fa-info-circle me-2"></i>Be the first to review this property</p>
                    <?php endif; ?>

                    <hr>
                    <h6 class="mb-3">Write a Review</h6>
                    <form action="/property/review" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="property_id" value="<?php echo $property['id'] ?? 0; ?>">
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">Your Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rating <span class="text-danger">*</span></label>
                            <select name="rating" class="form-select" required>
                                <option value="">Select Rating</option>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Poor</option>
                                <option value="1">1 - Terrible</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Review <span class="text-danger">*</span></label>
                            <textarea name="review_text" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Submit Review
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.lightbox-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.92); z-index: 99999;
    display: flex; align-items: center; justify-content: center;
}
.lightbox-content { position: relative; max-width: 90vw; max-height: 90vh; }
.lightbox-content img { max-width: 90vw; max-height: 85vh; object-fit: contain; border-radius: 8px; }
.lightbox-close { position: absolute; top: 20px; right: 35px; color: #fff; font-size: 40px; cursor: pointer; z-index: 10; }
.lightbox-prev, .lightbox-next { position: absolute; top: 50%; transform: translateY(-50%); color: #fff; font-size: 50px; cursor: pointer; padding: 20px; z-index: 10; user-select: none; }
.lightbox-prev:hover, .lightbox-next:hover { color: #0d6efd; }
.lightbox-prev { left: 20px; }
.lightbox-next { right: 20px; }
.lightbox-counter { position: absolute; bottom: -30px; left: 50%; transform: translateX(-50%); color: #aaa; font-size: 14px; }
</style>
<?php if (!empty($property_images)): ?>
<script>
var lightboxImages = <?php echo json_encode(array_map(function($img) { return $img['image_path']; }, $images)); ?>;
var currentLightbox = 0;
function openLightbox(index) {
    currentLightbox = index;
    document.getElementById('lightboxImage').src = lightboxImages[index];
    document.getElementById('propertyLightbox').style.display = 'flex';
    document.getElementById('lightboxCounter').textContent = (index + 1) + ' / ' + lightboxImages.length;
    document.body.style.overflow = 'hidden';
}
function closeLightbox(e) { if (e && e.target !== e.currentTarget) return;
    document.getElementById('propertyLightbox').style.display = 'none';
    document.body.style.overflow = ''; }
function changeLightbox(dir) {
    currentLightbox = (currentLightbox + dir + lightboxImages.length) % lightboxImages.length;
    openLightbox(currentLightbox);
}
document.addEventListener('keydown', function(e) {
    if (document.getElementById('propertyLightbox').style.display !== 'flex') return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') changeLightbox(-1);
    if (e.key === 'ArrowRight') changeLightbox(1);
});
</script>
<?php endif; ?>