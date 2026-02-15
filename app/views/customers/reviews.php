<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-2">मेरी समीक्षाएं (My Reviews)</h4>
                            <p class="card-text mb-0">आपके द्वारा दी गई सभी प्रॉपर्टी समीक्षाएं यहाँ देखें।</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form action="/customer/reviews" method="GET" class="row">
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">रेटिंग</label>
                            <select name="rating" class="form-control">
                                <option value="">सभी रेटिंग</option>
                                <option value="5" <?= ($filters['rating'] ?? '') == '5' ? 'selected' : '' ?>>5 सितारे</option>
                                <option value="4" <?= ($filters['rating'] ?? '') == '4' ? 'selected' : '' ?>>4 सितारे</option>
                                <option value="3" <?= ($filters['rating'] ?? '') == '3' ? 'selected' : '' ?>>3 सितारे</option>
                                <option value="2" <?= ($filters['rating'] ?? '') == '2' ? 'selected' : '' ?>>2 सितारे</option>
                                <option value="1" <?= ($filters['rating'] ?? '') == '1' ? 'selected' : '' ?>>1 सितारा</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">डेट (से)</label>
                            <input type="date" name="date_from" class="form-control" value="<?= h($filters['date_from'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">डेट (तक)</label>
                            <input type="date" name="date_to" class="form-control" value="<?= h($filters['date_to'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-info mr-2">फिल्टर</button>
                            <a href="/customer/reviews" class="btn btn-secondary">रीसेट</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="row">
        <div class="col-12">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="card shadow mb-4 border-left-<?= $review['status'] == 'approved' ? 'success' : 'warning' ?>">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center border-right">
                                    <img src="<?= $review['main_image'] ?? '/assets/img/property-placeholder.jpg' ?>"
                                         class="img-fluid rounded mb-2" style="max-height: 100px; object-fit: cover;" alt="Property">
                                    <div class="text-xs font-weight-bold text-uppercase"><?= h($review['property_type']) ?></div>
                                </div>
                                <div class="col-md-10">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="font-weight-bold mb-1">
                                                <a href="/customer/property/<?= $review['property_id'] ?>" class="text-gray-900 text-decoration-none">
                                                    <?= h($review['property_title']) ?>
                                                </a>
                                            </h5>
                                            <div class="text-xs text-muted">
                                                <i class="fas fa-map-marker-alt mr-1"></i> <?= h($review['property_address']) ?>, <?= h($review['city']) ?>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-<?= $review['status'] == 'approved' ? 'success' : 'warning' ?> mb-2">
                                                <?= $review['status'] == 'approved' ? 'प्रकाशित' : 'अनुमोदन के लिए लंबित' ?>
                                            </span>
                                            <div class="text-xs text-muted"><?= date('d M Y', strtotime($review['review_date'])) ?></div>
                                        </div>
                                    </div>
                                    <div class="text-warning mb-2 h5">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="<?= $i <= $review['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-gray-800 mb-0 italic">"<?= h($review['review_text']) ?>"</p>

                                    <?php if ($review['anonymous']): ?>
                                        <div class="mt-2 small text-muted">
                                            <i class="fas fa-user-secret mr-1"></i> यह समीक्षा अनाम (Anonymous) रूप से भेजी गई है।
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-comment-slash fa-4x text-muted mb-4"></i>
                        <h4 class="text-gray-800">अभी तक कोई समीक्षा नहीं दी गई है!</h4>
                        <p class="text-muted">अपनी खरीदी गई या देखी गई प्रॉपर्टीज के बारे में अपनी राय साझा करें।</p>
                        <a href="/customer/properties" class="btn btn-primary">प्रॉपर्टीज देखें</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
