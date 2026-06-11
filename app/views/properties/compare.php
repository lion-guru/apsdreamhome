<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2"><?= htmlspecialchars($page_title ?? 'Compare Properties') ?></h1>
            <p class="text-muted">Select 2 to <?= htmlspecialchars($max_compare, ENT_QUOTES, 'UTF-8') ?> properties to compare side-by-side</p>
        </div>
    </div>

    <!-- Selected Properties Counter -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas fa-info-circle me-2"></i>
                    <strong id="selected-count">0</strong> properties selected
                    <span class="text-muted">(minimum <?= htmlspecialchars($min_compare, ENT_QUOTES, 'UTF-8') ?> required)</span>
                </span>
                <button type="button" class="btn btn-primary" id="btn-compare" disabled onclick="compareProperties()">
                    <i class="fas fa-exchange-alt me-2"></i>Compare Now
                </button>
            </div>
        </div>
    </div>

    <!-- Saved Sessions (for logged-in users) -->
    <?php if (!empty($sessions)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card aps-cp-card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Saved Comparisons</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Properties</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sessions as $session): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($session['name']) ?></td>
                                            <td><?= $session['property_count'] ?> properties</td>
                                            <td><?= date('M d, Y', strtotime($session['created_at'])) ?></td>
                                            <td>
                                                <a href="/compare/load/<?= $session['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSession(<?= $session['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Property Selection Grid -->
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-home me-2"></i>Select Properties</h5>
                    <div class="input-group w-auto">
                        <input type="text" class="form-control form-control-sm" id="search-properties"
                            placeholder="Search properties..." onkeyup="searchProperties()">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row" id="property-grid">
                        <?php foreach ($properties as $property): ?>
                            <div class="col-md-6 col-lg-4 col-xl-3 mb-4 property-card"
                                data-name="<?= strtolower(htmlspecialchars($property['title'])) ?>"
                                data-location="<?= strtolower(htmlspecialchars($property['location'])) ?>">
                                <div class="card h-100 property-select-card" id="property-<?= $property['id'] ?>"
                                    onclick="toggleProperty(<?= $property['id'] ?>)" style="cursor: pointer;">

                                    <!-- Selection Badge -->
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <div class="form-check">
                                            <input class="form-check-input property-checkbox" type="checkbox"
                                                value="<?= $property['id'] ?>" id="checkbox-<?= $property['id'] ?>">
                                        </div>
                                    </div>

                                    <!-- Property Image -->
                                    <div class="property-image-wrapper" style="height: 200px; overflow: hidden;">
                                        <?php if ($property['primary_image']): ?>
                                            <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg"
                                                class="card-img-top" alt="<?= htmlspecialchars($property['title']) ?>"
                                                style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                                <i class="fas fa-home fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="card-body aps-cp-card-body">
                                        <h5 class="card-title text-truncate"><?= htmlspecialchars($property['title']) ?></h5>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($property['location']) ?>
                                        </p>

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h5 text-primary mb-0">
                                                ₹<?= number_format($property['price']) ?>
                                            </span>
                                            <span class="badge bg-<?= $property['status'] === 'available' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($property['status']) ?>
                                            </span>
                                        </div>

                                        <div class="row text-center small text-muted">
                                            <div class="col-4 border-end">
                                                <i class="fas fa-ruler-combined d-block mb-1"></i>
                                                <?= $property['area_sqft'] ?> sqft
                                            </div>
                                            <div class="col-4 border-end">
                                                <i class="fas fa-bed d-block mb-1"></i>
                                                <?= $property['bedrooms'] ?> BHK
                                            </div>
                                            <div class="col-4">
                                                <i class="fas fa-bath d-block mb-1"></i>
                                                <?= $property['bathrooms'] ?> Bath
                                            </div>
                                        </div>

                                        <?php if (isset($property['rera_status']) && $property['rera_status']): ?>
                                            <div class="mt-2">
                                                <span class="badge bg-info">
                                                    <i class="fas fa-certificate me-1"></i>RERA Approved
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>