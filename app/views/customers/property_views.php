<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="font-weight-bold text-gray-900">देखी गई प्रॉपर्टीज (Recently Viewed)</h2>
            <p class="text-muted">उन प्रॉपर्टीज की सूची जिन्हें आपने हाल ही में देखा है।</p>
        </div>
    </div>

    <!-- Viewed Properties Grid -->
    <div class="row">
        <?php if (!empty($property_views)): ?>
            <?php foreach ($property_views as $view): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="card property-card h-100 shadow-sm border-0 position-relative">
                        <div class="property-image-wrapper">
                            <img src="<?= !empty($view['main_image']) ? $view['main_image'] : '/assets/img/property-placeholder.jpg' ?>" 
                                 class="card-img-top property-thumbnail" style="height: 180px; object-fit: cover;" alt="<?= h($view['title']) ?>">
                            <div class="position-absolute px-2 py-1 bg-dark text-white rounded small" style="bottom: 10px; right: 10px; opacity: 0.8;">
                                <i class="fas fa-eye mr-1"></i> <?= date('d M, H:i', strtotime($view['viewed_at'])) ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?= h($view['property_type_name'] ?? 'Property') ?>
                            </div>
                            <h6 class="card-title font-weight-bold mb-2">
                                <a href="/customer/property/<?= $view['property_id'] ?>" class="text-gray-900 text-decoration-none text-truncate d-block">
                                    <?= h($view['title']) ?>
                                </a>
                            </h6>
                            <p class="text-muted small mb-3 text-truncate">
                                <i class="fas fa-map-marker-alt mr-1"></i> <?= h($view['city']) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="h6 font-weight-bold text-success mb-0">₹<?= number_format($view['price']) ?></div>
                                <a href="/customer/property/<?= $view['property_id'] ?>" class="btn btn-sm btn-outline-primary">फिर से देखें</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-history fa-4x text-muted mb-4"></i>
                <h4 class="text-gray-800">अभी तक कोई हिस्ट्री नहीं है!</h4>
                <p class="text-muted">आपके द्वारा देखी गई प्रॉपर्टीज यहाँ दिखाई देंगी।</p>
                <a href="/customer/properties" class="btn btn-primary mt-3">प्रॉपर्टीज खोजें</a>
            </div>
        <?php endif; ?>
    </div>
</div>
