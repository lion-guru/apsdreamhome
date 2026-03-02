<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-danger text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-2">मेरी पसंद (Favorites)</h4>
                            <p class="card-text mb-0">आपकी पसंद की गई सभी प्रॉपर्टीज की सूची यहाँ है।</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="/customer/properties" class="btn btn-light text-danger">
                                <i class="fas fa-search mr-1"></i> और प्रॉपर्टीज खोजें
                            </a>
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
                    <form action="/customer/favorites" method="GET" class="row">
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">प्रॉपर्टी टाइप</label>
                            <select name="property_type" class="form-control">
                                <option value="">सभी टाइप्स</option>
                                <?php foreach ($property_types as $type): ?>
                                    <option value="<?= $type['id'] ?>" <?= ($filters['property_type'] ?? '') == $type['id'] ? 'selected' : '' ?>>
                                        <?= h($type['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">लोकेशन</label>
                            <select name="city" class="form-control">
                                <option value="">सभी शहर</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['city'] ?>" <?= ($filters['city'] ?? '') == $location['city'] ? 'selected' : '' ?>>
                                        <?= h($location['city']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="small font-weight-bold">बजट (से)</label>
                            <input type="number" name="min_price" class="form-control" placeholder="मिनिमम" value="<?= h($filters['min_price'] ?? '') ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="small font-weight-bold">बजट (तक)</label>
                            <input type="number" name="max_price" class="form-control" placeholder="मैक्सिमम" value="<?= h($filters['max_price'] ?? '') ?>">
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-filter mr-1"></i> फिल्टर
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Favorites Grid -->
    <div class="row">
        <?php if (!empty($favorites)): ?>
            <?php foreach ($favorites as $property): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="card property-card h-100 shadow-sm border-0 position-relative">
                        <div class="property-image-wrapper">
                            <img src="<?= !empty($property['main_image']) ? $property['main_image'] : '/assets/img/property-placeholder.jpg' ?>"
                                 class="card-img-top property-thumbnail" style="height: 200px; object-fit: cover;" alt="<?= h($property['title']) ?>">
                            <div class="position-absolute" style="top: 10px; right: 10px;">
                                <form action="/customer/toggle-favorite/<?= $property['id'] ?>" method="POST" class="d-inline">
                                    <button type="submit" class="btn btn-sm btn-danger shadow">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="position-absolute" style="top: 10px; left: 10px;">
                                <span class="badge badge-<?= $property['status'] == 'available' ? 'success' : 'warning' ?>">
                                    <?= $property['status'] == 'available' ? 'उपलब्ध' : 'बुक किया गया' ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                <?= h($property['property_type_name'] ?? 'Property') ?>
                            </div>
                            <h6 class="card-title font-weight-bold mb-2">
                                <a href="/customer/property/<?= $property['id'] ?>" class="text-gray-900 text-decoration-none text-truncate d-block">
                                    <?= h($property['title']) ?>
                                </a>
                            </h6>
                            <p class="text-muted small mb-3 text-truncate">
                                <i class="fas fa-map-marker-alt mr-1"></i> <?= h($property['city']) ?>, <?= h($property['state']) ?>
                            </p>
                            <div class="row no-gutters mb-3">
                                <div class="col-4 border-right text-center">
                                    <div class="text-xs text-muted">बेडरूम</div>
                                    <div class="font-weight-bold small"><?= $property['bedrooms'] ?> BHK</div>
                                </div>
                                <div class="col-4 border-right text-center">
                                    <div class="text-xs text-muted">एरिया</div>
                                    <div class="font-weight-bold small"><?= number_format($property['area_sqft']) ?></div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="text-xs text-muted">पसंद किया</div>
                                    <div class="font-weight-bold small"><?= date('d M', strtotime($property['favorited_at'])) ?></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="h6 font-weight-bold text-success mb-0">₹<?= number_format($property['price']) ?></div>
                                <a href="/customer/property/<?= $property['id'] ?>" class="btn btn-sm btn-outline-danger">विवरण देखें</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="mb-4">
                    <i class="far fa-heart fa-4x text-muted"></i>
                </div>
                <h4 class="text-gray-800">अभी तक कोई पसंदीदा प्रॉपर्टी नहीं है!</h4>
                <p class="text-muted">अपनी पसंद की प्रॉपर्टीज को यहाँ देखने के लिए उन्हें हार्ट आइकन से मार्क करें।</p>
                <a href="/customer/properties" class="btn btn-primary mt-3">प्रॉपर्टीज खोजें</a>
            </div>
        <?php endif; ?>
    </div>
</div>
