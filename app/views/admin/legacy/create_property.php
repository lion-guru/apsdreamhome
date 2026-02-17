<?php
/**
 * Create Property Form Template
 * Admin form for creating new properties
 */

?>

<!-- Admin Header -->
<section class="admin-header py-4 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="mb-0">
                    <i class="fas fa-plus me-2"></i>
                    Add New Property
                </h1>
                <p class="mb-0 opacity-75">Create a new property listing</p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <a href="<?php echo BASE_URL; ?>admin/properties" class="btn btn-light btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Back to Properties
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Property Form -->
<section class="property-form py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-card">
                    <form action="<?php echo BASE_URL; ?>admin/properties/store" method="POST" enctype="multipart/form-data" class="property-form-content">
                        <!-- Basic Information -->
                        <div class="form-section mb-4">
                            <h4 class="section-title mb-3">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Basic Information
                            </h4>

                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="title" class="form-label fw-medium">Property Title *</label>
                                    <input type="text"
                                           class="form-control"
                                           id="title"
                                           name="title"
                                           placeholder="e.g., Luxury 3BHK Apartment in City Center"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label for="property_type" class="form-label fw-medium">Property Type *</label>
                                    <select class="form-select" id="property_type" name="property_type" required>
                                        <option value="">Select Type</option>
                                        <option value="apartment">Apartment</option>
                                        <option value="villa">Villa</option>
                                        <option value="house">Independent House</option>
                                        <option value="plot">Plot/Land</option>
                                        <option value="commercial">Commercial</option>
                                        <option value="office">Office Space</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label fw-medium">Description</label>
                                    <textarea class="form-control"
                                              id="description"
                                              name="description"
                                              rows="4"
                                              placeholder="Describe the property features, location advantages, amenities, etc."></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label for="price" class="form-label fw-medium">Price (₹) *</label>
                                    <input type="number"
                                           class="form-control"
                                           id="price"
                                           name="price"
                                           placeholder="Enter price in rupees"
                                           min="1"
                                           required>
                                </div>

                                <div class="col-md-3">
                                    <label for="bedrooms" class="form-label fw-medium">Bedrooms</label>
                                    <select class="form-select" id="bedrooms" name="bedrooms">
                                        <option value="">Select</option>
                                        <option value="1">1 BHK</option>
                                        <option value="2">2 BHK</option>
                                        <option value="3">3 BHK</option>
                                        <option value="4">4 BHK</option>
                                        <option value="5">5+ BHK</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="bathrooms" class="form-label fw-medium">Bathrooms</label>
                                    <select class="form-select" id="bathrooms" name="bathrooms">
                                        <option value="">Select</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4+</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="area_sqft" class="form-label fw-medium">Area (sq.ft)</label>
                                    <input type="number"
                                           class="form-control"
                                           id="area_sqft"
                                           name="area_sqft"
                                           placeholder="Enter area in square feet"
                                           min="1">
                                </div>

                                <div class="col-md-6">
                                    <label for="status" class="form-label fw-medium">Status *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="available">Available</option>
                                        <option value="sold">Sold</option>
                                        <option value="rented">Rented</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="form-section mb-4">
                            <h4 class="section-title mb-3">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                Location Information
                            </h4>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="city" class="form-label fw-medium">City *</label>
                                    <input type="text"
                                           class="form-control"
                                           id="city"
                                           name="city"
                                           placeholder="e.g., Gorakhpur"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label for="state" class="form-label fw-medium">State *</label>
                                    <select class="form-select" id="state" name="state" required>
                                        <option value="">Select State</option>
                                        <option value="Uttar Pradesh">Uttar Pradesh</option>
                                        <option value="Maharashtra">Maharashtra</option>
                                        <option value="Delhi">Delhi</option>
                                        <option value="Karnataka">Karnataka</option>
                                        <option value="Tamil Nadu">Tamil Nadu</option>
                                        <option value="West Bengal">West Bengal</option>
                                        <option value="Gujarat">Gujarat</option>
                                        <option value="Rajasthan">Rajasthan</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="pincode" class="form-label fw-medium">Pincode</label>
                                    <input type="text"
                                           class="form-control"
                                           id="pincode"
                                           name="pincode"
                                           placeholder="e.g., 273001"
                                           pattern="[0-9]{6}">
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label fw-medium">Full Address *</label>
                                    <textarea class="form-control"
                                              id="address"
                                              name="address"
                                              rows="3"
                                              placeholder="Complete address including street, locality, city"
                                              required></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label for="latitude" class="form-label fw-medium">Latitude</label>
                                    <input type="text"
                                           class="form-control"
                                           id="latitude"
                                           name="latitude"
                                           placeholder="e.g., 26.7606"
                                           step="any">
                                </div>

                                <div class="col-md-6">
                                    <label for="longitude" class="form-label fw-medium">Longitude</label>
                                    <input type="text"
                                           class="form-control"
                                           id="longitude"
                                           name="longitude"
                                           placeholder="e.g., 83.3732"
                                           step="any">
                                </div>
                            </div>
                        </div>

                        <!-- Property Features -->
                        <div class="form-section mb-4">
                            <h4 class="section-title mb-3">
                                <i class="fas fa-list-ul text-primary me-2"></i>
                                Property Features
                            </h4>

                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="parking" name="features[]" value="parking">
                                        <label class="form-check-label" for="parking">Parking</label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="garden" name="features[]" value="garden">
                                        <label class="form-check-label" for="garden">Garden</label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="security" name="features[]" value="security">
                                        <label class="form-check-label" for="security">Security</label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="gym" name="features[]" value="gym">
                                        <label class="form-check-label" for="gym">Gym</label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="swimming_pool" name="features[]" value="swimming_pool">
                                        <label class="form-check-label" for="swimming_pool">Swimming Pool</label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="lift" name="features[]" value="lift">
                                        <label class="form-check-label" for="lift">Lift/Elevator</label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="power_backup" name="features[]" value="power_backup">
                                        <label class="form-check-label" for="power_backup">Power Backup</label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="water_supply" name="features[]" value="water_supply">
                                        <label class="form-check-label" for="water_supply">24/7 Water</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Images -->
                        <div class="form-section mb-4">
                            <h4 class="section-title mb-3">
                                <i class="fas fa-images text-primary me-2"></i>
                                Property Images
                            </h4>

                            <div class="mb-3">
                                <label for="main_image" class="form-label fw-medium">Main Image *</label>
                                <input type="file"
                                       class="form-control"
                                       id="main_image"
                                       name="main_image"
                                       accept="image/*"
                                       required>
                                <div class="form-text">First image will be used as the main property image</div>
                            </div>

                            <div class="mb-3">
                                <label for="additional_images" class="form-label fw-medium">Additional Images</label>
                                <input type="file"
                                       class="form-control"
                                       id="additional_images"
                                       name="additional_images[]"
                                       accept="image/*"
                                       multiple>
                                <div class="form-text">You can select multiple images (max 10 images, 5MB each)</div>
                            </div>
                        </div>

                        <!-- Agent Information -->
                        <div class="form-section mb-4">
                            <h4 class="section-title mb-3">
                                <i class="fas fa-user-tie text-primary me-2"></i>
                                Agent Information
                            </h4>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="agent_id" class="form-label fw-medium">Assign to Agent</label>
                                    <select class="form-select" id="agent_id" name="agent_id">
                                        <option value="">Select Agent (Optional)</option>
                                        <!-- Agents will be populated from database -->
                                        <option value="1">Rajesh Kumar (Admin)</option>
                                        <option value="2">Priya Sharma</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="featured" class="form-label fw-medium">Featured Property</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1">
                                        <label class="form-check-label" for="featured">
                                            Mark as featured property
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions text-center pt-4 border-top">
                            <button type="submit" class="btn btn-success btn-lg me-3">
                                <i class="fas fa-save me-2"></i>
                                Create Property
                            </button>
                            <a href="<?php echo BASE_URL; ?>admin/properties" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Auto-generate slug from title
document.getElementById('title').addEventListener('input', function() {
    // This would typically generate a slug, but for now we'll leave it for the backend
});

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.property-form-content');

    form.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const price = document.getElementById('price').value;
        const address = document.getElementById('address').value.trim();

        if (!title || !price || !address) {
            e.preventDefault();
            alert('Please fill in all required fields (Title, Price, Address)');
            return false;
        }

        if (price < 1) {
            e.preventDefault();
            alert('Please enter a valid price');
            return false;
        }

        return true;
    });
});
</script>
