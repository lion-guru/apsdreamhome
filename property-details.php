<?php
// Page metadata
$page_title = 'Property Details - APS Dream Homes';
$meta_description = 'Detailed information about our premium properties. View high-quality images, specifications, and amenities.';
$additional_css = '<link href="/apsdreamhomefinal/assets/css/property-details.css" rel="stylesheet">';

// Include required files
require_once __DIR__ . '/includes/db_settings.php';
require_once __DIR__ . '/includes/templates/dynamic_header.php';

// Initialize variables
// Get property ID from URL
$property_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

// Initialize error message
$error_message = '';

// Set default property values
$default_property = array(
    'id' => 0,
    'title' => 'Property Not Found',
    'description' => 'The requested property could not be found.',
    'price' => 0,
    'bedrooms' => 0,
    'bathrooms' => 0,
    'area' => 0,
    'location' => 'N/A',
    'type_id' => 0,
    'project_id' => 0,
    'owner_id' => 0,
    'property_type' => 'N/A',
    'amenities' => array(),
    'gallery_images' => array(),
    'ai_valuation' => 0,
    'status' => 'available'
);

// Initialize property to default values
$property = $default_property;

// Try to fetch property from database
if ($property_id > 0) {
    try {
        // Get database connection
        $conn = get_db_connection();
        if (!$conn) {
            throw new Exception('Database connection failed');
        }

        // Main query for property details
        $sql = 'SELECT p.*, pt.name as property_type, '
             . 'pr.project_name, pr.description as project_description, '
             . 'CONCAT(u.first_name, " ", u.last_name) as owner_name '
             . 'FROM properties p '
             . 'LEFT JOIN property_types pt ON p.type_id = pt.id '
             . 'LEFT JOIN projects pr ON p.project_id = pr.id '
             . 'LEFT JOIN users u ON p.owner_id = u.id '
             . 'WHERE p.id = ? AND p.status = "available"';

        // Prepare and execute query
        if (!($stmt = $conn->prepare($sql))) {
            throw new Exception('Failed to prepare statement: ' . $conn->error);
        }
        if (!$stmt->bind_param('i', $property_id)) {
            throw new Exception('Failed to bind parameters: ' . $stmt->error);
        }
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute query: ' . $stmt->error);
        }

        // Get result
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception('Failed to get result: ' . $stmt->error);
        }

        // Fetch property data
        $property = $result->fetch_assoc();
        if (!$property) {
            throw new Exception('Property not found');
        }

        // Close statement
        $stmt->close();

        // Get additional data
        // Amenities
        $amenities_sql = 'SELECT GROUP_CONCAT(amenity_name) as amenities FROM project_amenities WHERE project_id = ?';
        $stmt = $conn->prepare($amenities_sql);
        $stmt->bind_param('i', $property['project_id']);
        $stmt->execute();
        $amenities_result = $stmt->get_result()->fetch_assoc();
        $property['amenities'] = $amenities_result ? explode(',', $amenities_result['amenities']) : array();

        // Gallery images
        $gallery_sql = 'SELECT image_path FROM project_gallery WHERE project_id = ? ORDER BY display_order ASC';
        $stmt = $conn->prepare($gallery_sql);
        $stmt->bind_param('i', $property['project_id']);
        $stmt->execute();
        $gallery_result = $stmt->get_result();
        $property['gallery_images'] = array();
        while ($row = $gallery_result->fetch_assoc()) {
            $property['gallery_images'][] = $row['image_path'];
        }

        // AI Valuation
        $valuation_sql = 'SELECT predicted_value FROM ai_property_valuation WHERE property_id = ? ORDER BY created_at DESC LIMIT 1';
        $stmt = $conn->prepare($valuation_sql);
        $stmt->bind_param('i', $property_id);
        $stmt->execute();
        $valuation_result = $stmt->get_result()->fetch_assoc();
        $property['ai_valuation'] = $valuation_result ? $valuation_result['predicted_value'] : 0;

        // Close statement
        $stmt->close();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        $property = $default_property;
    } finally {
        if ($conn) {
            $conn->close();
        }
    }
}
?>

<!-- Property Details Hero Section -->
<section class="property-hero py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/apsdreamhomefinal/">Home</a></li>
                        <li class="breadcrumb-item"><a href="/apsdreamhomefinal/properties.php">Properties</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($property['title']); ?></li>
                    </ol>
                </nav>
                <div class="property-title mb-4">
                    <h1 class="mb-2"><?php echo htmlspecialchars($property['title']); ?></h1>
                    <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($property['location']); ?></p>
                    <span class="badge bg-success"><?php echo $property['status'] === 'available' ? 'For Sale' : 'Sold'; ?></span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="price-box">
                    <span class="price-label">Price</span>
                    <h2 class="price mb-3"><?php echo htmlspecialchars($property['price']); ?></h2>
                    <div class="d-flex justify-content-end gap-2 mb-3">
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#inquiryModal">
                            <i class="fas fa-envelope me-2"></i>Inquire Now
                        </button>
                    </div>
                    <button class="btn btn-outline-primary btn-lg" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                        <i class="fas fa-calendar me-2"></i>Schedule Visit
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Property Images Section -->
<section class="property-images py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="property-image-main mb-4">
                    <img id="mainPropertyImage" src="<?php echo !empty($property['gallery_images']) ? htmlspecialchars($property['gallery_images'][0]) : '/apsdreamhomefinal/assets/images/property-placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($property['title']); ?>" 
                         class="img-fluid rounded shadow" 
                         width="1200" 
                         height="800">
                </div>
                <div class="property-image-thumbs d-flex overflow-auto pb-2">
                    <?php foreach ($property['gallery_images'] as $image): ?>
                        <img src="<?php echo htmlspecialchars($image); ?>" 
                             alt="Property thumbnail" 
                             class="thumb-image me-2" 
                             onclick="updateMainImage('<?php echo htmlspecialchars($image); ?>')" 
                             width="150" 
                             height="100" 
                             loading="lazy">
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Property Details Section -->
<section class="property-details py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Overview -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Overview</h3>
                        <div class="row g-4">
                            <div class="col-6 col-md-3">
                                <div class="feature-box text-center">
                                    <i class="fas fa-bed fa-2x mb-2 text-primary"></i>
                                    <p class="text-muted mb-1">Bedrooms</p>
                                    <h4 class="h6 mb-0"><?php echo htmlspecialchars($property['bedrooms']); ?></h4>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="feature-box text-center">
                                    <i class="fas fa-bath fa-2x mb-2 text-primary"></i>
                                    <p class="text-muted mb-1">Bathrooms</p>
                                    <h4 class="h6 mb-0"><?php echo htmlspecialchars($property['bathrooms']); ?></h4>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="feature-box text-center">
                                    <i class="fas fa-home fa-2x mb-2 text-primary"></i>
                                    <p class="text-muted mb-1">Property Type</p>
                                    <h4 class="h6 mb-0"><?php echo htmlspecialchars($property['property_type']); ?></h4>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="feature-box text-center">
                                    <i class="fas fa-ruler-combined fa-2x mb-2 text-primary"></i>
                                    <p class="text-muted mb-1">Area (sq ft)</p>
                                    <h4 class="h6 mb-0"><?php echo htmlspecialchars($property['area']); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Description</h3>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                    </div>
                </div>
                
                <!-- Amenities -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Amenities</h3>
                        <div class="row">
                            <?php foreach ($property['amenities'] as $amenity): ?>
                                <div class="col-md-4 mb-2">
                                    <div class="amenity-item">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <?php echo htmlspecialchars($amenity); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Location -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Location</h3>
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3887.9976945384793!2d77.5945627!3d12.9715987!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae1670c9b44e6d%3A0xf8dfc3e8517e4fe0!2sBangalore%2C%20Karnataka%2C%20India!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" 
                                    width="600" 
                                    height="450" 
                                    style="border:0;" 
                                    allowfullscreen="" 
                                    loading="lazy">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- AI Valuation -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">AI Property Valuation</h3>
                        <div class="text-center mb-3">
                            <span class="display-6 text-primary">₹<?php echo number_format($property['ai_valuation']); ?></span>
                        </div>
                        <p class="card-text">This AI-powered valuation is based on property features, location, and market trends. Actual value may vary.</p>
                    </div>
                </div>
                
                <!-- Contact Agent -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Contact Agent</h3>
                        <div class="text-center mb-3">
                            <img src="/apsdreamhomefinal/assets/images/agent-placeholder.jpg" alt="Agent" class="rounded-circle" width="100" height="100">
                            <h4 class="h5 mt-2"><?php echo htmlspecialchars($property['owner_name'] ?? 'APS Dream Homes Agent'); ?></h4>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="tel:+919876543210" class="btn btn-outline-primary"><i class="fas fa-phone me-2"></i>Call Agent</a>
                            <a href="mailto:agent@apsdreamhomes.com" class="btn btn-outline-primary"><i class="fas fa-envelope me-2"></i>Email Agent</a>
                        </div>
                    </div>
                </div>
                
                <!-- Similar Properties -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Similar Properties</h3>
                        <div class="similar-property mb-3">
                            <img src="/apsdreamhomefinal/assets/images/property-placeholder.jpg" alt="Similar Property" class="img-fluid rounded mb-2">
                            <h4 class="h6 mb-1">Luxury Villa in Whitefield</h4>
                            <p class="text-primary mb-0">₹1,80,00,000</p>
                        </div>
                        <div class="similar-property mb-3">
                            <img src="/apsdreamhomefinal/assets/images/property-placeholder.jpg" alt="Similar Property" class="img-fluid rounded mb-2">
                            <h4 class="h6 mb-1">Modern Apartment in Indiranagar</h4>
                            <p class="text-primary mb-0">₹1,20,00,000</p>
                        </div>
                        <div class="text-center">
                            <a href="/apsdreamhomefinal/properties.php" class="btn btn-link">View More Properties</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Inquiry Modal -->
<div class="modal fade" id="inquiryModal" tabindex="-1" aria-labelledby="inquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inquiryModalLabel">Inquire About This Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="inquiryForm" class="needs-validation" novalidate action="/apsdreamhomefinal/process_lead.php" method="post">
                    <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                    <div class="mb-3">
                        <label for="inquiryName" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="inquiryName" name="name" required>
                        <div class="invalid-feedback">Please provide your name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="inquiryEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="inquiryEmail" name="email" required>
                        <div class="invalid-feedback">Please provide a valid email.</div>
                    </div>
                    <div class="mb-3">
                        <label for="inquiryPhone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="inquiryPhone" name="phone" required>
                        <div class="invalid-feedback">Please provide your phone number.</div>
                    </div>
                    <div class="mb-3">
                        <label for="inquiryMessage" class="form-label">Message</label>
                        <textarea class="form-control" id="inquiryMessage" name="message" rows="3" required></textarea>
                        <div class="invalid-feedback">Please provide a message.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitInquiry()">Submit Inquiry</button>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Visit Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalLabel">Schedule a Visit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="scheduleName" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="scheduleName" required>
                        <div class="invalid-feedback">Please provide your name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="scheduleEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="scheduleEmail" required>
                        <div class="invalid-feedback">Please provide a valid email.</div>
                    </div>
                    <div class="mb-3">
                        <label for="schedulePhone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="schedulePhone" required>
                        <div class="invalid-feedback">Please provide your phone number.</div>
                    </div>
                    <div class="mb-3">
                        <label for="scheduleDate" class="form-label">Preferred Date</label>
                        <input type="date" class="form-control" id="scheduleDate" required>
                        <div class="invalid-feedback">Please select a date.</div>
                    </div>
                    <div class="mb-3">
                        <label for="scheduleTime" class="form-label">Preferred Time</label>
                        <select class="form-select" id="scheduleTime" required>
                            <option value="" selected disabled>Select a time</option>
                            <?php
                            $times = array('10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00');
                            foreach ($times as $time):
                            ?>
                            <option value="<?php echo $time; ?>"><?php echo date('h:i A', strtotime($time)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="scheduleNotes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="scheduleNotes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitSchedule()">Schedule Visit</button>
            </div>
        </div>
    </div>
</div>

<!-- Property Details CSS -->
<style>
    .property-hero {
        background-color: #f8f9fa;
    }
    
    .price-box {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 0.5rem;
        display: inline-block;
    }
    
    .price-label {
        color: #6c757d;
        font-size: 0.9rem;
        display: block;
    }
    
    .price {
        color: #0d6efd;
        font-size: 2rem;
        font-weight: bold;
        margin: 0;
    }
    
    .property-image-main img {
        width: 100%;
        height: 500px;
        object-fit: cover;
    }
    
    .property-image-thumbs {
        scrollbar-width: thin;
        scrollbar-color: #0d6efd #f8f9fa;
    }
    
    .property-image-thumbs::-webkit-scrollbar {
        height: 6px;
    }
    
    .property-image-thumbs::-webkit-scrollbar-track {
        background: #f8f9fa;
    }
    
    .property-image-thumbs::-webkit-scrollbar-thumb {
        background-color: #0d6efd;
        border-radius: 3px;
    }
    
    .thumb-image {
        cursor: pointer;
        border: 2px solid transparent;
        border-radius: 0.25rem;
        transition: border-color 0.3s ease;
    }
    
    .thumb-image:hover {
        border-color: #0d6efd;
    }
    
    .feature-box {
        padding: 1rem;
        border-radius: 0.5rem;
        background: #f8f9fa;
    }
    
    .amenity-item {
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 0.25rem;
        margin-bottom: 0.5rem;
    }
    
    @media (max-width: 768px) {
        .property-image-main img {
            height: 300px;
        }
    }
    
    @media (prefers-reduced-motion: reduce) {
        .thumb-image {
            transition: none;
        }
    }
</style>

<!-- Property Details JavaScript -->
<script>
function updateMainImage(src) {
    document.getElementById('mainPropertyImage').src = src;
}

function submitInquiry() {
    // In a real implementation, this would send the form data to the server
    const form = document.getElementById('inquiryForm');
    if (form.checkValidity()) {
        alert('Thank you for your inquiry. We will contact you soon!');
        $('#inquiryModal').modal('hide');
        form.reset();
    } else {
        form.classList.add('was-validated');
    }
}

function submitSchedule() {
    // In a real implementation, this would send the form data to the server
    const form = document.getElementById('scheduleForm');
    if (form.checkValidity()) {
        alert('Thank you for scheduling a visit. We will confirm your appointment soon!');
        $('#scheduleModal').modal('hide');
        form.reset();
    } else {
        form.classList.add('was-validated');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<?php
require_once __DIR__ . '/includes/templates/dynamic_footer.php';
?>
