<?php
$page_title = 'Add New Site';
$active_page = 'sites';
include APP_PATH . '/views/admin/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New Site</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/admin/sites" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Sites
            </a>
        </div>
    </div>
</div>

<!-- Site Form -->
<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/sites">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="fas fa-info-circle"></i> Basic Information</h5>

                    <div class="mb-3">
                        <label for="site_name" class="form-label">Site Name *</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" required>
                        <div class="form-text">Enter a descriptive name for the site</div>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Location *</label>
                        <textarea class="form-control" id="location" name="location" rows="2" required></textarea>
                        <div class="form-text">Full address or location description</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="pincode" class="form-label">Pincode</label>
                        <input type="text" class="form-control" id="pincode" name="pincode">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_type" class="form-label">Site Type</label>
                                <select class="form-select" id="site_type" name="site_type">
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="mixed">Mixed Use</option>
                                    <option value="industrial">Industrial</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="planning">Planning</option>
                                    <option value="under_development">Under Development</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technical Details -->
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="fas fa-cogs"></i> Technical Details</h5>

                    <div class="mb-3">
                        <label for="total_area" class="form-label">Total Area (sq ft) *</label>
                        <input type="number" class="form-control" id="total_area" name="total_area" step="0.01" required>
                        <div class="form-text">Total area of the site in square feet</div>
                    </div>

                    <div class="mb-3">
                        <label for="developed_area" class="form-label">Developed Area (sq ft)</label>
                        <input type="number" class="form-control" id="developed_area" name="developed_area" step="0.01" value="0">
                        <div class="form-text">Area that has been developed</div>
                    </div>

                    <h5 class="mb-3 mt-4"><i class="fas fa-map-marker-alt"></i> Location Coordinates</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" class="form-control" id="latitude" name="latitude" step="0.00000001" placeholder="28.6139">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" class="form-control" id="longitude" name="longitude" step="0.00000001" placeholder="77.2090">
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3 mt-4"><i class="fas fa-user-tie"></i> Site Management</h5>

                    <div class="mb-3">
                        <label for="manager_id" class="form-label">Site Manager</label>
                        <select class="form-select" id="manager_id" name="manager_id">
                            <option value="">Select Manager</option>
                            <?php if (isset($managers)): ?>
                                <?php foreach ($managers as $manager): ?>
                                    <option value="<?= $manager['id'] ?>"><?= htmlspecialchars(manager['name'] ?? '') ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Description and Amenities -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="fas fa-file-alt"></i> Description</h5>
                    <div class="mb-3">
                        <label for="description" class="form-label">Site Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        <div class="form-text">Detailed description of the site, features, and highlights</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="fas fa-star"></i> Amenities</h5>
                    <div class="mb-3">
                        <label for="amenities" class="form-label">Site Amenities</label>
                        <textarea class="form-control" id="amenities" name="amenities" rows="4"></textarea>
                        <div class="form-text">List of amenities available at the site (one per line)</div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <a href="/admin/sites" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <div>
                            <button type="reset" class="btn btn-outline-warning me-2">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Site
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const siteName = document.getElementById('site_name').value.trim();
        const location = document.getElementById('location').value.trim();
        const totalArea = parseFloat(document.getElementById('total_area').value);

        if (!siteName || !location || isNaN(totalArea) || totalArea <= 0) {
            e.preventDefault();
            alert('Please fill in all required fields with valid values.');
            return false;
        }
    });

    // Auto-calculate developed area if not provided
    document.getElementById('total_area').addEventListener('input', function() {
        const developedArea = document.getElementById('developed_area');
        if (developedArea.value === '0' || developedArea.value === '') {
            developedArea.value = this.value;
        }
    });
</script>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>