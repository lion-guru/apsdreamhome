<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Add New Property') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Admin Panel</span>
                    </h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/sites">
                                <i class="fas fa-map-marked-alt"></i> Sites
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="<?php echo BASE_URL; ?>/admin/properties">
                                <i class="fas fa-building"></i> Properties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/plots">
                                <i class="fas fa-th"></i> Plots
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/bookings">
                                <i class="fas fa-calendar-check"></i> Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/users">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Add New Property</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="<?php echo BASE_URL; ?>/admin/properties" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Properties
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Property Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="<?php echo BASE_URL; ?>/admin/properties" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <h5 class="mb-3"><i class="fas fa-info-circle"></i> Basic Information</h5>
                                    
                                    <div class="mb-3">
                                        <label for="site_id" class="form-label">Site *</label>
                                        <select class="form-select" id="site_id" name="site_id" required>
                                            <option value="">Select Site</option>
                                            <?php foreach ($sites as $site): ?>
                                                <option value="<?= $site['id'] ?>" <?= ($selected_site_id ?? '') == $site['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($site['site_name']) ?> - <?= htmlspecialchars($site['location']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Select the site where this property is located</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="title" class="form-label">Property Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                        <div class="form-text">Descriptive title for the property</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="type" class="form-label">Property Type *</label>
                                        <select class="form-select" id="type" name="type" required>
                                            <?php foreach ($property_types as $type): ?>
                                                <option value="<?= $type['name'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="bedrooms" class="form-label">Bedrooms</label>
                                                <input type="number" class="form-control" id="bedrooms" name="bedrooms" min="0" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="bathrooms" class="form-label">Bathrooms</label>
                                                <input type="number" class="form-control" id="bathrooms" name="bathrooms" min="0" value="0">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">Property Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" selected>Active</option>
                                            <option value="sold">Sold</option>
                                            <option value="rented">Rented</option>
                                            <option value="pending">Pending</option>
                                            <option value="draft">Draft</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Pricing & Location -->
                                <div class="col-md-6">
                                    <h5 class="mb-3"><i class="fas fa-rupee-sign"></i> Pricing & Location</h5>
                                    
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price (₹) *</label>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                        <div class="form-text">Property price in Indian Rupees</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="location" class="form-label">Location *</label>
                                        <textarea class="form-control" id="location" name="location" rows="2" required></textarea>
                                        <div class="form-text">Full address or location description</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="city" class="form-label">City</label>
                                                <input type="text" class="form-control" id="city" name="city">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="state" class="form-label">State</label>
                                                <input type="text" class="form-control" id="state" name="state">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="pincode" class="form-label">Pincode</label>
                                                <input type="text" class="form-control" id="pincode" name="pincode">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="area" class="form-label">Area (sq ft) *</label>
                                                <input type="number" class="form-control" id="area" name="area" step="0.01" required>
                                                <div class="form-text">Total area in square feet</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="area_unit" class="form-label">Area Unit</label>
                                                <select class="form-select" id="area_unit" name="area_unit">
                                                    <option value="sqft" selected>Square Feet</option>
                                                    <option value="sqm">Square Meters</option>
                                                    <option value="acre">Acres</option>
                                                    <option value="bigha">Bigha</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="featured" class="form-label">Featured Property</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1">
                                            <label class="form-check-label" for="featured">
                                                Mark as featured property (show on homepage)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description and Features -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <h5 class="mb-3"><i class="fas fa-file-alt"></i> Description</h5>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Property Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                                        <div class="form-text">Detailed description of property, features, and highlights</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-3"><i class="fas fa-star"></i> Features & Amenities</h5>
                                    <div class="mb-3">
                                        <label for="features" class="form-label">Property Features</label>
                                        <textarea class="form-control" id="features" name="features" rows="2"></textarea>
                                        <div class="form-text">List of property features (one per line)</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="amenities" class="form-label">Amenities</label>
                                        <textarea class="form-control" id="amenities" name="amenities" rows="2"></textarea>
                                        <div class="form-text">List of amenities available at property</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Location Coordinates -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5 class="mb-3"><i class="fas fa-map-marker-alt"></i> Location Coordinates</h5>
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
                                </div>
                            </div>

                            <!-- Property Images -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5 class="mb-3"><i class="fas fa-images"></i> Property Images</h5>
                                    <div class="mb-3">
                                        <label for="images" class="form-label">Property Images</label>
                                        <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                                        <div class="form-text">Upload multiple images of the property (JPG, PNG, GIF)</div>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Image Guidelines:</strong> Maximum file size: 5MB per image. Recommended dimensions: 1200x800px.
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="<?php echo BASE_URL; ?>/admin/properties" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                        <div>
                                            <button type="reset" class="btn btn-outline-warning me-2">
                                                <i class="fas fa-redo"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Create Property
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const siteId = document.getElementById('site_id').value;
            const title = document.getElementById('title').value.trim();
            const price = parseFloat(document.getElementById('price').value);
            const location = document.getElementById('location').value.trim();
            const area = parseFloat(document.getElementById('area').value);

            if (!siteId || !title || isNaN(price) || price <= 0 || !location || isNaN(area) || area <= 0) {
                e.preventDefault();
                alert('Please fill in all required fields with valid values.');
                return false;
            }
        });

        // Load site details (placeholder for future enhancement)
        document.getElementById('site_id').addEventListener('change', function() {
            const siteId = this.value;
            if (siteId) {
                // This could be enhanced to load site-specific information
                console.log('Selected site ID:', siteId);
            }
        });

        // Image preview functionality
        document.getElementById('images').addEventListener('change', function(e) {
            const files = e.target.files;
            let previewHtml = '';

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewHtml += `
                            <div class="col-md-3 mb-2">
                                <img src="${e.target.result}" class="img-fluid img-thumbnail" style="max-height: 150px;">
                                <div class="small text-muted">${file.name}</div>
                            </div>
                        `;
                    };
                    
                    reader.readAsDataURL(file);
                }
            }

            if (previewHtml) {
                const previewContainer = document.createElement('div');
                previewContainer.className = 'row mt-3 border-top pt-3';
                previewContainer.innerHTML = '<h6>Image Preview:</h6>' + previewHtml;
                
                // Insert preview after the images section
                const imagesSection = document.querySelector('[for="images"]').closest('.row');
                imagesSection.parentNode.insertBefore(previewContainer, imagesSection.nextSibling);
            }
        });

        // Auto-calculate price per square foot
        document.getElementById('price').addEventListener('input', function() {
            const price = parseFloat(this.value) || 0;
            const area = parseFloat(document.getElementById('area').value) || 1;
            const pricePerSqft = (price / area).toFixed(2);
            
            // You could display this information to the user
            console.log('Price per sq ft:', pricePerSqft);
        });

        // Featured property warning
        document.getElementById('featured').addEventListener('change', function() {
            if (this.checked) {
                const confirmed = confirm('Marking this property as featured will display it prominently on the website. Continue?');
                if (!confirmed) {
                    this.checked = false;
                }
            }
        });
    </script>
</body>
</html>
