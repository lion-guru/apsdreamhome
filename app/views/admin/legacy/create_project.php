<?php
/**
 * Admin Create Project View
 * Form for creating new projects
 */
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects">Projects</a></li>
                    <li class="breadcrumb-item active">Add New Project</li>
                </ol>
            </nav>
            <h2><i class="fas fa-plus me-2"></i>Add New Project</h2>
            <p class="text-muted">Create a new project with all necessary details</p>
        </div>
    </div>

    <!-- Project Creation Form -->
    <div class="row">
        <div class="col-lg-8">
            <form action="/admin/projects" method="POST" enctype="multipart/form-data" id="projectForm">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle me-2"></i>Project Information</h5>
                    </div>
                    <div class="card-body">
                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="projectName" class="form-label">Project Name *</label>
                                    <input type="text" class="form-control" id="projectName" name="project_name"
                                           placeholder="Enter project name" required>
                                    <div class="form-text">Choose a unique and descriptive name</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="projectCode" class="form-label">Project Code *</label>
                                    <input type="text" class="form-control" id="projectCode" name="project_code"
                                           placeholder="Enter project code" required>
                                    <div class="form-text">Unique code for project identification (e.g., APS-001)</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="projectType" class="form-label">Project Type *</label>
                                    <select class="form-select" id="projectType" name="project_type" required>
                                        <option value="">Select Project Type</option>
                                        <option value="residential">Residential</option>
                                        <option value="commercial">Commercial</option>
                                        <option value="mixed">Mixed Use</option>
                                        <option value="plotting">Plotting</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="projectStatus" class="form-label">Project Status *</label>
                                    <select class="form-select" id="projectStatus" name="project_status" required>
                                        <option value="planning">Planning</option>
                                        <option value="ongoing" selected>Ongoing</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="possessionDate" class="form-label">Possession Date</label>
                                    <input type="date" class="form-control" id="possessionDate" name="possession_date">
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location/Area *</label>
                                    <input type="text" class="form-control" id="location" name="location"
                                           placeholder="Enter location/area name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City *</label>
                                    <select class="form-select" id="city" name="city" required>
                                        <option value="">Select City</option>
                                        <option value="Gorakhpur">Gorakhpur</option>
                                        <option value="Lucknow">Lucknow</option>
                                        <option value="Varanasi">Varanasi</option>
                                        <option value="Kanpur">Kanpur</option>
                                        <option value="Agra">Agra</option>
                                        <option value="Allahabad">Allahabad</option>
                                        <option value="Noida">Noida</option>
                                        <option value="Ghaziabad">Ghaziabad</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" class="form-control" id="state" name="state"
                                           value="Uttar Pradesh" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pincode" class="form-label">Pincode</label>
                                    <input type="text" class="form-control" id="pincode" name="pincode"
                                           placeholder="Enter pincode">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Full Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"
                                      placeholder="Enter complete address with landmarks"></textarea>
                        </div>

                        <!-- Pricing Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="basePrice" class="form-label">Base Price (₹) *</label>
                                    <input type="number" class="form-control" id="basePrice" name="base_price"
                                           placeholder="Enter base price" required>
                                    <div class="form-text">Starting price for the project</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pricePerSqft" class="form-label">Price per sqft (₹) *</label>
                                    <input type="number" class="form-control" id="pricePerSqft" name="price_per_sqft"
                                           placeholder="Enter price per sqft" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bookingAmount" class="form-label">Booking Amount (₹)</label>
                                    <input type="number" class="form-control" id="bookingAmount" name="booking_amount"
                                           placeholder="Enter booking amount">
                                    <div class="form-text">Amount required for booking</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reraNumber" class="form-label">RERA Number</label>
                                    <input type="text" class="form-control" id="reraNumber" name="rera_number"
                                           placeholder="Enter RERA registration number">
                                </div>
                            </div>
                        </div>

                        <!-- Inventory Information -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="totalArea" class="form-label">Total Area (sq ft)</label>
                                    <input type="number" class="form-control" id="totalArea" name="total_area"
                                           placeholder="Enter total area">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="totalPlots" class="form-label">Total Plots *</label>
                                    <input type="number" class="form-control" id="totalPlots" name="total_plots"
                                           placeholder="Enter total plots" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="availablePlots" class="form-label">Available Plots *</label>
                                    <input type="number" class="form-control" id="availablePlots" name="available_plots"
                                           placeholder="Enter available plots" required>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shortDescription" class="form-label">Short Description</label>
                                    <textarea class="form-control" id="shortDescription" name="short_description"
                                              rows="3" placeholder="Brief description (max 150 characters)"></textarea>
                                    <div class="form-text">Appears in project listings and cards</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Full Description</label>
                                    <textarea class="form-control" id="description" name="description"
                                              rows="3" placeholder="Detailed project description"></textarea>
                                    <div class="form-text">Complete information about the project</div>
                                </div>
                            </div>
                        </div>

                        <!-- Developer Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="developerName" class="form-label">Developer Name</label>
                                    <input type="text" class="form-control" id="developerName" name="developer_name"
                                           value="APS Dream Homes Pvt Ltd">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="developerContact" class="form-label">Developer Contact</label>
                                    <input type="text" class="form-control" id="developerContact" name="developer_contact"
                                           placeholder="Enter contact number">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="developerEmail" class="form-label">Developer Email</label>
                                    <input type="email" class="form-control" id="developerEmail" name="developer_email"
                                           placeholder="Enter email address">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contactNumber" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="contactNumber" name="contact_number"
                                           placeholder="Enter contact number">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contactEmail" class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" id="contactEmail" name="contact_email"
                                           placeholder="Enter contact email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="url" class="form-control" id="website" name="website"
                                           placeholder="Enter website URL">
                                </div>
                            </div>
                        </div>

                        <!-- Project Management -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="projectHead" class="form-label">Project Head</label>
                                    <input type="text" class="form-control" id="projectHead" name="project_head"
                                           placeholder="Enter project head name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="projectManager" class="form-label">Project Manager</label>
                                    <input type="text" class="form-control" id="projectManager" name="project_manager"
                                           placeholder="Enter project manager name">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="salesManager" class="form-label">Sales Manager</label>
                                    <input type="text" class="form-control" id="salesManager" name="sales_manager"
                                           placeholder="Enter sales manager name">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features and Amenities -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-star me-2"></i>Features & Amenities</h5>
                    </div>
                    <div class="card-body">
                        <!-- Highlights -->
                        <div class="mb-4">
                            <label class="form-label">Project Highlights</label>
                            <div class="border p-3 rounded">
                                <?php
                                $highlights = [
                                    'Prime Location', 'Best Investment Opportunity', 'High Appreciation Potential',
                                    'Excellent Connectivity', 'Modern Infrastructure', 'Quality Construction',
                                    'Timely Possession', 'Reputed Developer', 'Eco-Friendly Design', 'Smart Home Features'
                                ];
                                foreach ($highlights as $highlight):
                                ?>
                                    <div class="form-check form-check-inline mb-2 me-3">
                                        <input class="form-check-input" type="checkbox" name="highlights[]" value="<?= htmlspecialchars($highlight) ?>" id="highlight<?= array_search($highlight, $highlights) ?>">
                                        <label class="form-check-label" for="highlight<?= array_search($highlight, $highlights) ?>">
                                            <?= htmlspecialchars($highlight) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <div class="mb-4">
                            <label class="form-label">Amenities</label>
                            <div class="border p-3 rounded">
                                <?php
                                $amenities = [
                                    '24/7 Security', 'Swimming Pool', 'Gymnasium', 'Children Play Area',
                                    'Jogging Track', 'Club House', 'Landscaped Gardens', 'Power Backup',
                                    'Water Supply', 'Sewage Treatment', 'Car Parking', 'Elevator',
                                    'Intercom', 'CCTV Surveillance', 'Fire Fighting System', 'Rain Water Harvesting'
                                ];
                                foreach ($amenities as $amenity):
                                ?>
                                    <div class="form-check form-check-inline mb-2 me-3">
                                        <input class="form-check-input" type="checkbox" name="amenities[]" value="<?= htmlspecialchars($amenity) ?>" id="amenity<?= array_search($amenity, $amenities) ?>">
                                        <label class="form-check-label" for="amenity<?= array_search($amenity, $amenities) ?>">
                                            <?= htmlspecialchars($amenity) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media and Files -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-images me-2"></i>Media & Documents</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="layoutMap" class="form-label">Layout Map</label>
                                    <input type="file" class="form-control" id="layoutMap" name="layout_map"
                                           accept="image/*,.pdf">
                                    <div class="form-text">Upload project layout map (JPG, PNG, PDF)</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="brochure" class="form-label">Brochure</label>
                                    <input type="file" class="form-control" id="brochure" name="brochure"
                                           accept=".pdf">
                                    <div class="form-text">Upload project brochure (PDF format)</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="galleryImages" class="form-label">Gallery Images</label>
                            <input type="file" class="form-control" id="galleryImages" name="gallery_images[]"
                                   accept="image/*" multiple>
                            <div class="form-text">Upload multiple project images (JPG, PNG)</div>
                        </div>

                        <div class="mb-3">
                            <label for="virtualTour" class="form-label">Virtual Tour URL</label>
                            <input type="url" class="form-control" id="virtualTour" name="virtual_tour"
                                   placeholder="Enter virtual tour URL (YouTube, 360° tour, etc.)">
                        </div>
                    </div>
                </div>

                <!-- SEO and Settings -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-cog me-2"></i>Settings & SEO</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="seoTitle" class="form-label">SEO Title</label>
                                    <input type="text" class="form-control" id="seoTitle" name="seo_title"
                                           placeholder="Enter SEO title">
                                    <div class="form-text">Appears in search engine results</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="metaImage" class="form-label">Meta Image</label>
                                    <input type="file" class="form-control" id="metaImage" name="meta_image"
                                           accept="image/*">
                                    <div class="form-text">Image for social media sharing</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="seoDescription" class="form-label">SEO Description</label>
                            <textarea class="form-control" id="seoDescription" name="seo_description"
                                      rows="2" placeholder="Enter SEO description"></textarea>
                            <div class="form-text">Appears in search engine results (max 160 characters)</div>
                        </div>

                        <div class="mb-3">
                            <label for="seoKeywords" class="form-label">SEO Keywords</label>
                            <input type="text" class="form-control" id="seoKeywords" name="seo_keywords"
                                   placeholder="Enter keywords separated by commas">
                            <div class="form-text">Keywords for better search visibility</div>
                        </div>

                        <!-- Settings -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="isFeatured" name="is_featured">
                                        <label class="form-check-label" for="isFeatured">
                                            Mark as Featured Project
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="emiAvailable" name="emi_available">
                                        <label class="form-check-label" for="emiAvailable">
                                            EMI Available
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card shadow-sm mt-4">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Create Project
                        </button>
                        <a href="/admin/projects" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Tips -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6><i class="fas fa-lightbulb me-2"></i>Quick Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <i class="fas fa-check text-success me-2"></i>
                            Use unique project codes for easy identification
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check text-success me-2"></i>
                            Add high-quality images for better presentation
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check text-success me-2"></i>
                            Include RERA number for authenticity
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check text-success me-2"></i>
                            Mark featured projects for homepage display
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check text-success me-2"></i>
                            Add amenities and highlights for better SEO
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Project Preview -->
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h6><i class="fas fa-eye me-2"></i>Project Preview</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="bg-light rounded p-3 mb-3">
                            <i class="fas fa-image fa-3x text-muted"></i>
                            <p class="text-muted mt-2">Preview will appear here</p>
                        </div>
                        <small class="text-muted">Project preview updates as you fill the form</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-generate project code based on project name
document.getElementById('projectName')?.addEventListener('input', function() {
    const name = this.value;
    const code = name.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10);
    if (code && !document.getElementById('projectCode').value) {
        document.getElementById('projectCode').value = code;
    }
});

// Update available plots when total plots change
document.getElementById('totalPlots')?.addEventListener('input', function() {
    const totalPlots = parseInt(this.value) || 0;
    const availablePlots = document.getElementById('availablePlots');
    if (parseInt(availablePlots.value) > totalPlots) {
        availablePlots.value = totalPlots;
    }
    availablePlots.setAttribute('max', totalPlots);
});

// Form validation
document.getElementById('projectForm')?.addEventListener('submit', function(e) {
    const requiredFields = ['project_name', 'project_code', 'project_type', 'location', 'city', 'base_price', 'price_per_sqft', 'total_plots', 'available_plots'];

    for (const field of requiredFields) {
        const element = document.getElementById(field.replace('_', '').charAt(0).toUpperCase() + field.replace('_', '').slice(1));
        if (!element || !element.value.trim()) {
            e.preventDefault();
            alert(`Please fill in the ${field.replace('_', ' ')} field`);
            element?.focus();
            return;
        }
    }
});

// Character counter for descriptions
document.getElementById('shortDescription')?.addEventListener('input', function() {
    const maxLength = 150;
    const currentLength = this.value.length;

    if (currentLength > maxLength) {
        this.value = this.value.substring(0, maxLength);
    }
});
</script>

<style>
.card {
    border: none;
    border-radius: 10px;
}

.form-label {
    font-weight: 600;
}

.form-text {
    font-size: 0.875rem;
}

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    transition: transform 0.2s;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.amenity-grid {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2rem;
    }
}
</style>
