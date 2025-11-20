<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome/');
}

// Ensure $project is defined to prevent errors
if (!isset($project) || !is_array($project)) {
    $project = [
        'project_name' => '',
        'short_description' => '',
        'description' => '',
        'base_price' => 0,
        'city' => '',
        'available_plots' => 0,
        'gallery_images' => [],
        'project_type' => '',
        'total_area' => 0,
        'total_plots' => 0,
        'price_per_sqft' => 0,
        'possession_date' => '',
        'rera_number' => '',
        'developer_name' => '',
        'developer_contact' => '',
        'developer_email' => '',
        'project_head' => '',
        'sales_manager' => '',
        'amenities' => [],
        'highlights' => [],
        'latitude' => '',
        'longitude' => '',
        'address' => '',
    ];
}

/**
 * Project Detail View
 * Shows complete project information with gallery, amenities, etc.
 */

// Set page title and description for layout
$page_title = htmlspecialchars($project['project_name'] ?? 'Project Details') . ' - APS Dream Home';
$page_description = htmlspecialchars($project['short_description'] ?? $project['description'] ?? 'Discover this exceptional residential project');
?>

<!-- Hero Section -->
<section class="hero-section position-relative" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 60vh;">
    <div class="container-fluid h-100">
        <div class="row h-100 align-items-center">
            <div class="col-lg-6 text-white">
                <h1 class="display-3 fw-bold mb-4">
                    <?= htmlspecialchars($project['project_name']) ?>
                </h1>
                <p class="lead mb-4">
                    <?= htmlspecialchars($project['short_description'] ?? $project['description']) ?>
                </p>

                <!-- Project Stats -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-item text-center">
                            <i class="fas fa-rupee-sign fa-2x mb-2"></i>
                            <h4>₹<?= number_format($project['base_price'], 0) ?></h4>
                            <small>Starting Price</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item text-center">
                            <i class="fas fa-map-marker-alt fa-2x mb-2"></i>
                            <h4><?= htmlspecialchars($project['city']) ?></h4>
                            <small>Location</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item text-center">
                            <i class="fas fa-home fa-2x mb-2"></i>
                            <h4><?= $project['available_plots'] ?? 0 ?></h4>
                            <small>Plots Available</small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-3">
                    <button class="btn btn-light btn-lg" onclick="showBookingModal()">
                        <i class="fas fa-calendar-check me-2"></i>Book Site Visit
                    </button>
                    <button class="btn btn-outline-light btn-lg" onclick="downloadBrochure()">
                        <i class="fas fa-download me-2"></i>Download Brochure
                    </button>
                    <button class="btn btn-outline-light btn-lg" onclick="showVirtualTour()">
                        <i class="fas fa-vr-cardboard me-2"></i>Virtual Tour
                    </button>
                </div>
            </div>

            <div class="col-lg-6">
                <!-- Main Image -->
                <div class="main-image-container">
                    <?php if (!empty($project['gallery_images'])): ?>
                        <img src="/uploads/projects/<?= htmlspecialchars($project['gallery_images'][0]) ?>"
                             class="img-fluid rounded shadow" alt="<?= htmlspecialchars($project['project_name']) ?>"
                             id="mainImage">
                    <?php else: ?>
                        <img src="/assets/images/no-project-image.jpg"
                             class="img-fluid rounded shadow" alt="No Image Available"
                             id="mainImage">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Project Details Section -->
<section class="py-5">
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Project Overview -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h4><i class="fas fa-info-circle me-2"></i>Project Overview</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Project Details</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Project Type:</strong></td>
                                        <td><?= htmlspecialchars(ucfirst($project['project_type'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Area:</strong></td>
                                        <td><?= number_format($project['total_area'] ?? 0) ?> sq ft</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Plots:</strong></td>
                                        <td><?= $project['total_plots'] ?? 0 ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Available Plots:</strong></td>
                                        <td><?= $project['available_plots'] ?? 0 ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Price per sqft:</strong></td>
                                        <td>₹<?= number_format($project['price_per_sqft'] ?? 0) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Possession:</strong></td>
                                        <td><?= $project['possession_date'] ? date('M Y', strtotime($project['possession_date'])) : 'TBD' ?></td>
                                    </tr>
                                    <?php if (!empty($project['rera_number'])): ?>
                                        <tr>
                                            <td><strong>RERA Number:</strong></td>
                                            <td><?= htmlspecialchars($project['rera_number']) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Developer Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Developer:</strong></td>
                                        <td><?= htmlspecialchars($project['developer_name']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Contact:</strong></td>
                                        <td><?= htmlspecialchars($project['developer_contact']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td><?= htmlspecialchars($project['developer_email']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Project Head:</strong></td>
                                        <td><?= htmlspecialchars($project['project_head']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Sales Manager:</strong></td>
                                        <td><?= htmlspecialchars($project['sales_manager']) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Project Description -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h4><i class="fas fa-align-left me-2"></i>Description</h4>
                    </div>
                    <div class="card-body">
                        <p class="lead">
                            <?= nl2br(htmlspecialchars($project['description'])) ?>
                        </p>
                    </div>
                </div>

                <!-- Amenities -->
                <?php if (!empty($project['amenities'])): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-concierge-bell me-2"></i>Amenities & Features</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($project['amenities'] as $amenity): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="amenity-item">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <?= htmlspecialchars($amenity) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Highlights -->
                <?php if (!empty($project['highlights'])): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-star me-2"></i>Project Highlights</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($project['highlights'] as $highlight): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="highlight-item">
                                            <i class="fas fa-star text-warning me-2"></i>
                                            <?= htmlspecialchars($highlight) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Gallery -->
                <?php if (!empty($project['gallery_images'])): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-images me-2"></i>Project Gallery</h4>
                        </div>
                        <div class="card-body">
                            <div class="row gallery-container">
                                <?php foreach ($project['gallery_images'] as $index => $image): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="gallery-item">
                                            <img src="/uploads/projects/<?= htmlspecialchars($image) ?>"
                                                 class="img-fluid rounded cursor-pointer"
                                                 onclick="openGallery(<?= $index ?>)"
                                                 alt="Gallery Image <?= $index + 1 ?>">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Location Map -->
                <?php if (!empty($project['latitude']) && !empty($project['longitude'])): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-map-marked-alt me-2"></i>Location</h4>
                        </div>
                        <div class="card-body">
                            <div class="location-info mb-3">
                                <p><i class="fas fa-map-marker-alt me-2"></i>
                                   <?= htmlspecialchars($project['address']) ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Coordinates:</strong>
                                    <?= $project['latitude'] ?>, <?= $project['longitude'] ?>
                                </p>
                            </div>
                            <div id="map" style="height: 400px; width: 100%;"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Layout Map -->
                <?php if (!empty($project['layout_map'])): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-project-diagram me-2"></i>Project Layout</h4>
                        </div>
                        <div class="card-body text-center">
                            <img src="/uploads/projects/<?= htmlspecialchars($project['layout_map']) ?>"
                                 class="img-fluid" alt="Project Layout"
                                 style="max-height: 600px;">
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Info -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle me-2"></i>Quick Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-item mb-3">
                            <strong>Project Status:</strong>
                            <span class="badge bg-<?= $project['project_status'] === 'completed' ? 'success' : 'info' ?> ms-2">
                                <?= ucfirst($project['project_status']) ?>
                            </span>
                        </div>

                        <div class="info-item mb-3">
                            <strong>Booking Amount:</strong>
                            <span class="text-primary">₹<?= number_format($project['booking_amount'] ?? 0) ?></span>
                        </div>

                        <?php if ($project['emi_available']): ?>
                            <div class="info-item mb-3">
                                <strong>EMI Available:</strong>
                                <span class="badge bg-success">Yes</span>
                            </div>
                        <?php endif; ?>

                        <div class="info-item mb-3">
                            <strong>Contact:</strong>
                            <div class="mt-2">
                                <p class="mb-1">
                                    <i class="fas fa-phone me-2"></i>
                                    <?= htmlspecialchars($project['contact_number']) ?>
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-envelope me-2"></i>
                                    <?= htmlspecialchars($project['contact_email']) ?>
                                </p>
                            </div>
                        </div>

                        <?php if (!empty($project['website'])): ?>
                            <div class="info-item mb-3">
                                <strong>Website:</strong>
                                <a href="<?= htmlspecialchars($project['website']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                    Visit Website
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- EMI Calculator -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-calculator me-2"></i>EMI Calculator</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="loanAmount" class="form-label">Loan Amount (₹)</label>
                            <input type="number" class="form-control" id="loanAmount" placeholder="Enter loan amount">
                        </div>
                        <div class="mb-3">
                            <label for="interestRate" class="form-label">Interest Rate (%)</label>
                            <input type="number" class="form-control" id="interestRate" value="8.5" step="0.1">
                        </div>
                        <div class="mb-3">
                            <label for="loanTenure" class="form-label">Tenure (Years)</label>
                            <select class="form-select" id="loanTenure">
                                <option value="5">5 Years</option>
                                <option value="10">10 Years</option>
                                <option value="15" selected>15 Years</option>
                                <option value="20">20 Years</option>
                                <option value="25">25 Years</option>
                            </select>
                        </div>
                        <button class="btn btn-primary w-100" onclick="calculateEMI()">Calculate EMI</button>
                        <div id="emiResult" class="mt-3 text-center" style="display: none;">
                            <h5>Monthly EMI: <span id="monthlyEMI" class="text-primary"></span></h5>
                            <small class="text-muted">Total Amount: <span id="totalAmount"></span></small>
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <?php if (!empty($project['social_facebook']) || !empty($project['social_instagram']) || !empty($project['social_twitter'])): ?>
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5><i class="fas fa-share-alt me-2"></i>Follow Project</h5>
                        </div>
                        <div class="card-body text-center">
                            <?php if (!empty($project['social_facebook'])): ?>
                                <a href="<?= htmlspecialchars($project['social_facebook']) ?>" target="_blank" class="btn btn-outline-primary me-2">
                                    <i class="fab fa-facebook"></i> Facebook
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($project['social_instagram'])): ?>
                                <a href="<?= htmlspecialchars($project['social_instagram']) ?>" target="_blank" class="btn btn-outline-danger me-2">
                                    <i class="fab fa-instagram"></i> Instagram
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($project['social_twitter'])): ?>
                                <a href="<?= htmlspecialchars($project['social_twitter']) ?>" target="_blank" class="btn btn-outline-info">
                                    <i class="fab fa-twitter"></i> Twitter
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Book Site Visit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bookingForm">
                <div class="modal-body">
                    <input type="hidden" name="project_code" value="<?= htmlspecialchars($project['project_code']) ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="visitorName" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="visitorName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="visitorEmail" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="visitorEmail" name="email" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="visitorPhone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="visitorPhone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="preferredDate" class="form-label">Preferred Date *</label>
                                <input type="date" class="form-control" id="preferredDate" name="preferred_date" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="preferredTime" class="form-label">Preferred Time</label>
                        <select class="form-select" id="preferredTime" name="preferred_time">
                            <option value="">Any Time</option>
                            <option value="morning">Morning (9 AM - 12 PM)</option>
                            <option value="afternoon">Afternoon (12 PM - 5 PM)</option>
                            <option value="evening">Evening (5 PM - 7 PM)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="visitorMessage" class="form-label">Message</label>
                        <textarea class="form-control" id="visitorMessage" name="message" rows="3"
                                  placeholder="Any specific requirements or questions..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Book Visit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Project Gallery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner" id="galleryImages">
                        <!-- Images will be loaded here -->
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentImageIndex = 0;

function showBookingModal() {
    $('#bookingModal').modal('show');
}

function downloadBrochure() {
    <?php if (!empty($project['brochure'])): ?>
        window.open('/uploads/projects/<?= htmlspecialchars($project['brochure']) ?>', '_blank');
    <?php else: ?>
        alert('Brochure not available for this project.');
    <?php endif; ?>
}

function showVirtualTour() {
    <?php if (!empty($project['virtual_tour'])): ?>
        window.open('<?= htmlspecialchars($project['virtual_tour']) ?>', '_blank');
    <?php else: ?>
        alert('Virtual tour not available for this project.');
    <?php endif; ?>
}

function openGallery(index) {
    currentImageIndex = index;

    const images = <?= json_encode($project['gallery_images'] ?? []) ?>;
    const galleryImages = document.getElementById('galleryImages');

    galleryImages.innerHTML = '';

    images.forEach((image, i) => {
        const div = document.createElement('div');
        div.className = `carousel-item ${i === index ? 'active' : ''}`;
        div.innerHTML = `<img src="/uploads/projects/${image}" class="d-block w-100" alt="Gallery Image ${i + 1}">`;
        galleryImages.appendChild(div);
    });

    $('#galleryModal').modal('show');
}

function calculateEMI() {
    const principal = parseFloat(document.getElementById('loanAmount').value);
    const rate = parseFloat(document.getElementById('interestRate').value) / 100 / 12;
    const tenure = parseFloat(document.getElementById('loanTenure').value) * 12;

    if (isNaN(principal) || principal <= 0) {
        alert('Please enter a valid loan amount');
        return;
    }

    const emi = principal * rate * Math.pow(1 + rate, tenure) / (Math.pow(1 + rate, tenure) - 1);
    const totalAmount = emi * tenure;

    document.getElementById('monthlyEMI').textContent = '₹' + Math.round(emi).toLocaleString();
    document.getElementById('totalAmount').textContent = '₹' + Math.round(totalAmount).toLocaleString();
    document.getElementById('emiResult').style.display = 'block';
}

// Form submission
document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    // In a real implementation, you would submit this data to the server
    alert('Site visit booked successfully! We will contact you soon.');

    // Close modal and reset form
    $('#bookingModal').modal('hide');
    this.reset();
});

// Set minimum date for site visit
document.getElementById('preferredDate')?.setAttribute('min', new Date().toISOString().split('T')[0]);

// Initialize map if coordinates are available
<?php if (!empty($project['latitude']) && !empty($project['longitude'])): ?>
// Initialize Google Map (you would need to include Google Maps API)
function initMap() {
    const location = { lat: <?= $project['latitude'] ?>, lng: <?= $project['longitude'] ?> };
    const map = new google.maps.Map(document.getElementById('map'), {
        zoom: 15,
        center: location,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    });

    const marker = new google.maps.Marker({
        position: location,
        map: map,
        title: '<?= htmlspecialchars($project['project_name']) ?>'
    });
}
<?php endif; ?>
</script>

<style>
.hero-section {
    background-attachment: fixed;
    position: relative;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    z-index: 1;
}

.hero-section .container-fluid {
    position: relative;
    z-index: 2;
}

.stat-item {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.main-image-container {
    position: relative;
    overflow: hidden;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.main-image-container img {
    width: 100%;
    height: 400px;
    object-fit: cover;
}

.amenity-item, .highlight-item {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    margin-bottom: 5px;
}

.gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.gallery-item:hover {
    transform: scale(1.05);
}

.cursor-pointer {
    cursor: pointer;
}

.info-item {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.info-item:last-child {
    border-bottom: none;
}

.carousel-item img {
    max-height: 500px;
    object-fit: contain;
}

@media (max-width: 768px) {
    .hero-section {
        min-height: 80vh;
        text-align: center;
    }

    .display-3 {
        font-size: 2.5rem;
    }

    .main-image-container img {
        height: 250px;
    }

    .stat-item {
        margin-bottom: 20px;
    }
}
</style>
