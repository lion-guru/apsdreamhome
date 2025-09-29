<?php include '../app/views/includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/properties">Properties</a></li>
                    <li class="breadcrumb-item"><a href="/properties/<?php echo $property['id']; ?>">Property Details</a></li>
                    <li class="breadcrumb-item active">Edit Property</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Edit Property</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <form action="/properties/<?php echo $property['id']; ?>" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">Property Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                       value="<?php echo htmlspecialchars($property['title']); ?>"
                                       placeholder="e.g., 2BHK Apartment in Downtown">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Property Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="residential" <?php echo ($property['type'] == 'residential') ? 'selected' : ''; ?>>Residential</option>
                                    <option value="commercial" <?php echo ($property['type'] == 'commercial') ? 'selected' : ''; ?>>Commercial</option>
                                    <option value="plot" <?php echo ($property['type'] == 'plot') ? 'selected' : ''; ?>>Plot</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required
                                      placeholder="Describe the property features, amenities, location benefits, etc."><?php echo htmlspecialchars($property['description']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location *</label>
                                <input type="text" class="form-control" id="location" name="location" required
                                       value="<?php echo htmlspecialchars($property['location']); ?>"
                                       placeholder="e.g., Sector 62, Noida">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price (₹) *</label>
                                <input type="number" class="form-control" id="price" name="price" required
                                       value="<?php echo htmlspecialchars($property['price']); ?>"
                                       placeholder="e.g., 5000000">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="bedrooms" class="form-label">Bedrooms</label>
                                <input type="number" class="form-control" id="bedrooms" name="bedrooms"
                                       value="<?php echo htmlspecialchars($property['bedrooms']); ?>"
                                       placeholder="e.g., 3">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="bathrooms" class="form-label">Bathrooms</label>
                                <input type="number" class="form-control" id="bathrooms" name="bathrooms"
                                       value="<?php echo htmlspecialchars($property['bathrooms']); ?>"
                                       placeholder="e.g., 2">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="area" class="form-label">Area (sq ft)</label>
                                <input type="number" class="form-control" id="area" name="area"
                                       value="<?php echo htmlspecialchars($property['area']); ?>"
                                       placeholder="e.g., 1200">
                            </div>
                        </div>

                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo ($property['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($property['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="sold" <?php echo ($property['status'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Update Property</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../app/views/includes/footer.php'; ?>
