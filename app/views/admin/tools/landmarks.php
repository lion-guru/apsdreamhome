<?php
$landmarks = $landmarks ?? [];
$colonies = $colonies ?? [];
$distances = $distances ?? [];
$categories = $categories ?? [];
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Landmarks & Distance Config</h4>
            <p class="text-muted mb-0">Manage nearby landmarks and auto-calculate distances to colonies</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Total Landmarks</div>
                            <div class="fs-3 fw-bold"><?= count($landmarks) ?></div>
                        </div>
                        <i class="fas fa-map-marker-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Active Colonies</div>
                            <div class="fs-3 fw-bold"><?= count($colonies) ?></div>
                        </div>
                        <i class="fas fa-building fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Distance Links</div>
                            <div class="fs-3 fw-bold"><?= count($distances) ?></div>
                        </div>
                        <i class="fas fa-route fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Categories</div>
                            <div class="fs-3 fw-bold"><?= count($categories) ?></div>
                        </div>
                        <i class="fas fa-tags fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Landmark Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Landmark</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/tools/landmarks/save">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. AIIMS Gorakhpur" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="school">School</option>
                            <option value="hospital">Hospital</option>
                            <option value="mall">Mall</option>
                            <option value="metro">Metro Station</option>
                            <option value="airport">Airport</option>
                            <option value="temple">Temple</option>
                            <option value="market">Market</option>
                            <option value="bank">Bank</option>
                            <option value="park">Park</option>
                            <option value="government">Government Office</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" placeholder="Full address">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Latitude</label>
                        <input type="number" name="latitude" class="form-control" step="0.000001" placeholder="26.7606">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Longitude</label>
                        <input type="number" name="longitude" class="form-control" step="0.000001" placeholder="83.3732">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" class="form-control" maxlength="6" placeholder="273001">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i></button>
                    </div>
                </div>
                <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i>Distances to all active colonies will be auto-calculated using Haversine formula</div>
            </form>
        </div>
    </div>

    <!-- Landmarks List -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">All Landmarks (<?= count($landmarks) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($landmarks)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No landmarks found</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Linked Colonies</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($landmarks as $l): ?>
                            <tr>
                                <td>
                                    <div class="fw-medium"><?= htmlspecialchars($l['name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($l['address'] ?? '') ?></small>
                                </td>
                                <td><span class="badge bg-info"><?= ucfirst(htmlspecialchars($l['category'])) ?></span></td>
                                <td>
                                    <small class="text-muted">
                                        <?= $l['latitude'] ? round($l['latitude'], 4) : '—' ?>, 
                                        <?= $l['longitude'] ? round($l['longitude'], 4) : '—' ?>
                                    </small>
                                </td>
                                <td><span class="badge bg-success"><?= $l['linked_colonies'] ?> colonies</span></td>
                                <td>
                                    <?php if (($l['is_active'] ?? 0) == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/tools/landmarks/<?= $l['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this landmark?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Distances Matrix -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Distance Matrix (<?= count($distances) ?> entries)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($distances)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-route fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No distance data. Add landmarks to auto-calculate.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Colony</th>
                                <th>Landmark</th>
                                <th>Category</th>
                                <th>Distance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($distances as $d): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($d['colony_name']) ?></strong></td>
                                <td><?= htmlspecialchars($d['landmark_name']) ?></td>
                                <td><span class="badge bg-info"><?= ucfirst(htmlspecialchars($d['landmark_category'])) ?></span></td>
                                <td>
                                    <?php
                                    $km = $d['distance_km'] ?? 0;
                                    $badgeClass = $km <= 2 ? 'success' : ($km <= 5 ? 'warning' : 'danger');
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= number_format($km, 1) ?> km</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
