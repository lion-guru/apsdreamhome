<div class="container-fluid mt-4">
    <!-- Property Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-2">
                    <li class="breadcrumb-item"><a href="/customer/dashboard">डैशबोर्ड</a></li>
                    <li class="breadcrumb-item"><a href="/customer/properties">प्रॉपर्टीज</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= h($property['title']) ?></li>
                </ol>
            </nav>
            <h2 class="font-weight-bold text-gray-900"><?= h($property['title']) ?></h2>
            <p class="text-muted mb-0"><i class="fas fa-map-marker-alt mr-2 text-primary"></i> <?= h($property['address']) ?>, <?= h($property['city']) ?>, <?= h($property['state']) ?></p>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <div class="h3 font-weight-bold text-success mb-0">₹<?= number_format($property['price']) ?></div>
            <div class="text-xs text-uppercase text-muted">कुल कीमत</div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Images & Details -->
        <div class="col-lg-8">
            <!-- Image Gallery -->
            <div class="card shadow mb-4">
                <div class="card-body p-0 overflow-hidden rounded">
                    <div id="propertyCarousel" class="carousel slide" data-ride="carousel">
                        <div class="carousel-inner">
                            <?php
                            $images = !empty($property['all_images']) ? explode(',', $property['all_images']) : [];
                            if (empty($images)): ?>
                                <div class="carousel-item active">
                                    <img src="/assets/img/property-placeholder.jpg" class="d-block w-100" style="height: 450px; object-fit: cover;" alt="Placeholder">
                                </div>
                            <?php else: ?>
                                <?php foreach ($images as $index => $image): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= h($image) ?>" class="d-block w-100" style="height: 450px; object-fit: cover;" alt="Property Image">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php if (count($images) > 1): ?>
                            <a class="carousel-control-prev" href="#propertyCarousel" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#propertyCarousel" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Key Features -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">बेडरूम</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= h($property['bedrooms']) ?> BHK</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-bed fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">बाथरूम</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= h($property['bathrooms']) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-bath fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">एरिया</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($property['area_sqft']) ?> Sq.Ft.</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-ruler-combined fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">टाइप</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= h($property['property_type_name']) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-building fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">प्रॉपर्टी विवरण</h6>
                </div>
                <div class="card-body">
                    <p class="text-gray-800" style="line-height: 1.8; white-space: pre-line;">
                        <?= h($property['description']) ?>
                    </p>

                    <h6 class="font-weight-bold mt-4">मुख्य सुविधाएं:</h6>
                    <div class="row mt-2">
                        <?php
                        $amenities = !empty($property['amenities']) ? explode(',', $property['amenities']) : ['पार्किंग', 'सुरक्षा', 'बिजली', 'पानी'];
                        foreach ($amenities as $amenity): ?>
                            <div class="col-md-4 mb-2">
                                <i class="fas fa-check-circle text-success mr-2"></i> <?= h(trim($amenity)) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Location Map (Placeholder) -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">लोकेशन मैप</h6>
                </div>
                <div class="card-body p-0">
                    <div style="height: 300px; background: #e9ecef; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                        <i class="fas fa-map-marked-alt fa-3x text-muted mb-2"></i>
                        <p class="text-muted">मैप जल्द ही उपलब्ध होगा।</p>
                    </div>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">ग्राहक समीक्षाएं (<?= count($reviews) ?>)</h6>
                    <div class="text-warning">
                        <?php
                        $avgRating = $property['avg_rating'] ?? 0;
                        for ($i = 1; $i <= 5; $i++): ?>
                            <i class="<?= $i <= $avgRating ? 'fas' : 'far' ?> fa-star"></i>
                        <?php endfor; ?>
                        <span class="ml-1 text-dark font-weight-bold"><?= number_format($avgRating, 1) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                        <p class="text-center text-muted py-4">अभी तक कोई समीक्षा नहीं दी गई है।</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="media mb-4 pb-4 border-bottom">
                                <img src="<?= $review['customer_image'] ?? '/assets/img/user-placeholder.jpg' ?>"
                                     class="mr-3 rounded-circle" style="width: 50px; height: 50px; object-fit: cover;"
                                     alt="<?= $review['anonymous'] ? 'Anonymous' : h($review['customer_name']) ?>">
                                <div class="media-body">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mt-0 font-weight-bold"><?= $review['anonymous'] ? 'एक ग्राहक' : h($review['customer_name']) ?></h6>
                                        <small class="text-muted"><?= date('d M Y', strtotime($review['created_at'])) ?></small>
                                    </div>
                                    <div class="text-warning mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="<?= $i <= $review['rating'] ? 'fas' : 'far' ?> fa-star fa-xs"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-gray-800 small mb-0"><?= h($review['review_text']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="mt-4 text-center">
                        <button class="btn btn-outline-primary" data-toggle="modal" data-target="#reviewModal">
                            अपनी समीक्षा लिखें
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Contact & Actions -->
        <div class="col-lg-4">
            <!-- Action Card -->
            <div class="card shadow mb-4 sticky-top" style="top: 20px;">
                <div class="card-body">
                    <div class="mb-4">
                        <form action="/customer/toggle-favorite/<?= $property['id'] ?>" method="POST">
                            <button type="submit" class="btn btn-<?= $property['is_favorited'] ? 'danger' : 'outline-danger' ?> btn-block mb-3">
                                <i class="<?= $property['is_favorited'] ? 'fas' : 'far' ?> fa-heart mr-2"></i>
                                <?= $property['is_favorited'] ? 'पसंद से हटाएं' : 'पसंद करें (Favorite)' ?>
                            </button>
                        </form>
                        <a href="/customer/emi-calculator?property_id=<?= $property['id'] ?>" class="btn btn-outline-info btn-block mb-3">
                            <i class="fas fa-calculator mr-2"></i> EMI कैलकुलेटर
                        </a>
                    </div>

                    <hr>

                    <h6 class="font-weight-bold text-gray-900 mb-3">संपर्क करें</h6>
                    <div class="media mb-3">
                        <img src="<?= $property['agent_image'] ?? '/assets/img/user-placeholder.jpg' ?>"
                             class="mr-3 rounded-circle" style="width: 60px; height: 60px; object-fit: cover;"
                             alt="<?= h($property['agent_name']) ?>">
                        <div class="media-body">
                            <h6 class="mt-0 font-weight-bold mb-1"><?= h($property['agent_name']) ?></h6>
                            <p class="text-muted small mb-0">प्रॉपर्टी कंसलटेंट</p>
                            <div class="mt-2">
                                <a href="tel:<?= $property['agent_phone'] ?>" class="btn btn-sm btn-primary mr-1">
                                    <i class="fas fa-phone-alt"></i>
                                </a>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $property['agent_phone']) ?>" target="_blank" class="btn btn-sm btn-success mr-1">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <a href="mailto:<?= $property['agent_email'] ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="font-weight-bold text-gray-900 mb-3">साइट विजिट / बुकिंग</h6>
                    <form action="/customer/book-visit" method="POST">
                        <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                        <div class="form-group">
                            <label class="small">पसंदीदा तारीख</label>
                            <input type="date" name="visit_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label class="small">पसंदीदा समय</label>
                            <select name="visit_time" class="form-control" required>
                                <option value="10:00 AM">10:00 AM</option>
                                <option value="11:00 AM">11:00 AM</option>
                                <option value="12:00 PM">12:00 PM</option>
                                <option value="02:00 PM">02:00 PM</option>
                                <option value="03:00 PM">03:00 PM</option>
                                <option value="04:00 PM">04:00 PM</option>
                                <option value="05:00 PM">05:00 PM</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success btn-block font-weight-bold">
                            विजिट बुक करें
                        </button>
                    </form>
                </div>
            </div>

            <!-- Related Properties -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">मिलती-जुलती प्रॉपर्टीज</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (empty($related_properties)): ?>
                            <div class="p-4 text-center text-muted small">कोई और प्रॉपर्टी नहीं मिली।</div>
                        <?php else: ?>
                            <?php foreach ($related_properties as $related): ?>
                                <a href="/customer/property/<?= $related['id'] ?>" class="list-group-item list-group-item-action">
                                    <div class="row no-gutters">
                                        <div class="col-4 pr-2">
                                            <img src="<?= $related['main_image'] ?? '/assets/img/property-placeholder.jpg' ?>"
                                                 class="img-fluid rounded" alt="<?= h($related['title']) ?>">
                                        </div>
                                        <div class="col-8">
                                            <div class="font-weight-bold text-gray-900 text-truncate"><?= h($related['title']) ?></div>
                                            <div class="text-xs text-muted"><?= h($related['city']) ?></div>
                                            <div class="text-sm font-weight-bold text-success mt-1">₹<?= number_format($related['price']) ?></div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">समीक्षा लिखें</h5>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/customer/submit-review/<?= $property['id'] ?>" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>रेटिंग</label>
                        <div class="rating-input h3 text-warning">
                            <i class="far fa-star rating-star" data-rating="1"></i>
                            <i class="far fa-star rating-star" data-rating="2"></i>
                            <i class="far fa-star rating-star" data-rating="3"></i>
                            <i class="far fa-star rating-star" data-rating="4"></i>
                            <i class="far fa-star rating-star" data-rating="5"></i>
                            <input type="hidden" name="rating" id="selected-rating" value="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>आपका अनुभव</label>
                        <textarea name="review_text" class="form-control" rows="4" placeholder="यहाँ अपना अनुभव लिखें..." required></textarea>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="anonymous" name="anonymous" value="1">
                        <label class="custom-control-label small" for="anonymous">अनाम (Anonymous) रूप से समीक्षा भेजें</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">बंद करें</button>
                    <button type="submit" class="btn btn-primary">समीक्षा सबमिट करें</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extra_js = "
<script>
    $(document).ready(function() {
        $('.rating-star').hover(function() {
            var rating = $(this).data('rating');
            updateStars(rating);
        }, function() {
            var rating = $('#selected-rating').val();
            updateStars(rating);
        });

        $('.rating-star').click(function() {
            var rating = $(this).data('rating');
            $('#selected-rating').val(rating);
            updateStars(rating);
        });

        function updateStars(rating) {
            $('.rating-star').each(function() {
                if ($(this).data('rating') <= rating) {
                    $(this).removeClass('far').addClass('fas');
                } else {
                    $(this).removeClass('fas').addClass('far');
                }
            });
        }
    });
</script>
";
?>
