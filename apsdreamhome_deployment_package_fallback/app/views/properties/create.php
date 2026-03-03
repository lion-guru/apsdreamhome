<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome/');
}
include __DIR__ . '/../layouts/header.php';
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>properties">Properties</a></li>
                    <li class="breadcrumb-item active">Add Property</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Add New Property</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <form action="<?php echo BASE_URL; ?>properties" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">Property Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                       value="<?php echo htmlspecialchars($_SESSION['form_data']['title'] ?? ''); ?>"
                                       placeholder="e.g., 2BHK Apartment in Downtown">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Property Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="residential" <?php echo (isset($_SESSION['form_data']['type']) && $_SESSION['form_data']['type'] == 'residential') ? 'selected' : ''; ?>>Residential</option>
                                    <option value="commercial" <?php echo (isset($_SESSION['form_data']['type']) && $_SESSION['form_data']['type'] == 'commercial') ? 'selected' : ''; ?>>Commercial</option>
                                    <option value="plot" <?php echo (isset($_SESSION['form_data']['type']) && $_SESSION['form_data']['type'] == 'plot') ? 'selected' : ''; ?>>Plot</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required
                                      placeholder="Describe the property features, amenities, location benefits, etc."><?php echo htmlspecialchars($_SESSION['form_data']['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location *</label>
                                <input type="text" class="form-control" id="location" name="location" required
                                       value="<?php echo htmlspecialchars($_SESSION['form_data']['location'] ?? ''); ?>"
                                       placeholder="e.g., Sector 62, Noida">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price (₹) *</label>
                                <input type="number" class="form-control" id="price" name="price" required
                                       value="<?php echo htmlspecialchars($_SESSION['form_data']['price'] ?? ''); ?>"
                                       placeholder="e.g., 5000000">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="bedrooms" class="form-label">Bedrooms</label>
                                <input type="number" class="form-control" id="bedrooms" name="bedrooms"
                                       value="<?php echo htmlspecialchars($_SESSION['form_data']['bedrooms'] ?? ''); ?>"
                                       placeholder="e.g., 3">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="bathrooms" class="form-label">Bathrooms</label>
                                <input type="number" class="form-control" id="bathrooms" name="bathrooms"
                                       value="<?php echo htmlspecialchars($_SESSION['form_data']['bathrooms'] ?? ''); ?>"
                                       placeholder="e.g., 2">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="area" class="form-label">Area (sq ft)</label>
                                <input type="number" class="form-control" id="area" name="area"
                                       value="<?php echo htmlspecialchars($_SESSION['form_data']['area'] ?? ''); ?>"
                                       placeholder="e.g., 1200">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="images" class="form-label">Property Images</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                            <div class="form-text">You can select multiple images (JPG, PNG, GIF)</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Add Property</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>