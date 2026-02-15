<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-2">प्रॉपर्टीज खोजें</h4>
                            <p class="card-text mb-0">अपने सपनों का घर खोजने के लिए नीचे दिए गए फिल्टर का उपयोग करें।</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="/customer/favorites" class="btn btn-light">
                                <i class="fas fa-heart text-danger mr-1"></i> मेरी पसंद
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
                    <form action="/customer/properties" method="GET" class="row">
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">सर्च करें</label>
                            <input type="text" name="search" class="form-control" placeholder="नाम या लोकेशन..." value="<?= h($filters['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-2 mb-3">
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
                        <div class="col-md-2 mb-3">
                            <label class="small font-weight-bold">लोकेशन</label>
                            <select name="city" class="form-control">
                                <option value="">सभी शहर</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= h($location['city']) ?>" <?= ($filters['city'] ?? '') == $location['city'] ? 'selected' : '' ?>>
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
                        <div class="col-md-1 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Properties Grid -->
    <div class="row">
        <?php if (!empty($properties)): ?>
            <?php foreach ($properties as $property): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="card property-card h-100 shadow-sm border-0">
                        <div class="property-image-wrapper position-relative">
                            <img src="<?= !empty($property['image']) ? $property['image'] : '/assets/img/property-placeholder.jpg' ?>"
                                 class="card-img-top property-thumbnail" alt="<?= h($property['title']) ?>">
                            <div class="property-status-badge position-absolute" style="top: 10px; left: 10px;">
                                <span class="badge badge-<?= $property['status'] == 'available' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($property['status']) ?>
                                </span>
                            </div>
                            <button class="btn btn-sm btn-light position-absolute favorite-toggle"
                                    style="top: 10px; right: 10px;"
                                    onclick="event.preventDefault(); toggleFavorite(<?= $property['id'] ?>)">
                                <i class="<?= ($property['is_favorite'] ?? false) ? 'fas' : 'far' ?> fa-heart text-danger"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?= h($property['type_name'] ?? 'Property') ?>
                            </div>
                            <h5 class="card-title font-weight-bold mb-2">
                                <a href="/customer/property/<?= $property['id'] ?>" class="text-gray-900 text-decoration-none">
                                    <?= h($property['title']) ?>
                                </a>
                            </h5>
                            <p class="card-text small text-gray-600 mb-2">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <?= h($property['city']) ?>, <?= h($property['state']) ?>
                            </p>
                            <div class="property-features d-flex justify-content-between mb-3">
                                <?php if (isset($property['bedrooms'])): ?>
                                    <span class="small text-muted"><i class="fas fa-bed mr-1"></i> <?= $property['bedrooms'] ?> BHK</span>
                                <?php endif; ?>
                                <?php if (isset($property['area_sqft'])): ?>
                                    <span class="small text-muted"><i class="fas fa-ruler-combined mr-1"></i> <?= number_format($property['area_sqft']) ?> Sqft</span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-auto">
                                <div class="h5 mb-0 font-weight-bold text-success">
                                    ₹<?= number_format($property['price']) ?>
                                </div>
                                <a href="/customer/property/<?= $property['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    डिटेल्स देखें
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="empty-state mb-4">
                    <i class="fas fa-home fa-4x text-gray-300"></i>
                </div>
                <h4>कोई प्रॉपर्टी नहीं मिली</h4>
                <p class="text-muted">कृपया अपनी सर्च क्राइटेरिया बदलें या बाद में प्रयास करें।</p>
                <a href="/customer/properties" class="btn btn-primary mt-3">सभी प्रॉपर्टीज देखें</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.property-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
.property-thumbnail {
    height: 180px;
    object-fit: cover;
}
.property-image-wrapper {
    overflow: hidden;
}
.favorite-toggle {
    border-radius: 50%;
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.9;
}
.favorite-toggle:hover {
    opacity: 1;
    transform: scale(1.1);
}
</style>

<script>
function toggleFavorite(propertyId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/customer/toggle-favorite/' + propertyId;
    document.body.appendChild(form);
    form.submit();
}
</script>
