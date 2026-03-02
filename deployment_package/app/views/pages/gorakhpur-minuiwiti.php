<?php
// Include database connection
require_once 'includes/db_connection.php';

// Set project details
$projectName = "Minuiwiti";
$projectLocation = "Gorakhpur";
$projectType = "Residential Plots";
$projectStatus = "Ongoing";
$startingPrice = "₹8,75,000";
$projectArea = "22 Acres";
$totalUnits = "180";
$possessionDate = "October 2024";

// Project description
$projectDescription = "Minuiwiti offers premium residential plots in Gorakhpur with modern infrastructure and excellent connectivity. The project is designed for those seeking a perfect blend of urban convenience and peaceful living.";

// Meta description for SEO
$metaDescription = "Minuiwiti - Premium residential plots in Gorakhpur by APS Dream Homes. Starting at ₹8,75,000. Modern infrastructure and excellent connectivity.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $projectName; ?> - APS Dream Homes</title>
    <meta name="description" content="<?php echo $metaDescription; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        .project-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('assets/images/projects/gorakhpur/minuiwiti-banner.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 150px 0 100px;
            margin-top: -20px;
            position: relative;
        }
        
        /* ... rest of the CSS same as Minua ... */
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/templates/header.php'; ?>

    <!-- Project Hero Section -->
    <section class="project-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-3"><?php echo $projectName; ?></h1>
                    <p class="lead mb-4">Premium Residential Plots in Gorakhpur</p>
                    <div class="d-flex justify-content-center gap-3">
                        <span class="badge bg-primary px-3 py-2">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            <?php echo $projectLocation; ?>
                        </span>
                        <span class="badge bg-success px-3 py-2">
                            <?php echo $projectStatus; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Project Details -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="mb-4">About the Project</h2>
                            <div class="project-description">
                                <?php echo nl2br(htmlspecialchars($projectDescription)); ?>
                            </div>
                            
                            <h3 class="mt-5 mb-4">Project Highlights</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="highlight-item">
                                        <div class="highlight-icon">
                                            <i class="fas fa-home"></i>
                                        </div>
                                        <h5>Project Type</h5>
                                        <p class="text-muted"><?php echo $projectType; ?></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="highlight-item">
                                        <div class="highlight-icon">
                                            <i class="fas fa-map-marked-alt"></i>
                                        </div>
                                        <h5>Location</h5>
                                        <p class="text-muted"><?php echo $projectLocation; ?></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="highlight-item">
                                        <div class="highlight-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <h5>Possession</h5>
                                        <p class="text-muted"><?php echo $possessionDate; ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <h3 class="mt-5 mb-4">Amenities</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="amenities-list">
                                        <li>24/7 Security & CCTV Surveillance</li>
                                        <li>Well-planned Road Network</li>
                                        <li>Underground Electricity Lines</li>
                                        <li>Water Supply System</li>
                                        <li>Drainage & Sewage System</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="amenities-list">
                                        <li>Landscaped Parks & Green Spaces</li>
                                        <li>Children's Play Area</li>
                                        <li>Community Center</li>
                                        <li>Parking Facilities</li>
                                        <li>Easy Access to Main Road</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="project-highlight sticky-top" style="top: 100px;">
                        <h3 class="mb-4">Project Overview</h3>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <strong>Starting Price:</strong>
                                <span class="float-end text-primary fw-bold">
                                    <?php echo $startingPrice; ?>
                                </span>
                            </li>
                            <li class="mb-3">
                                <strong>Project Area:</strong>
                                <span class="float-end">
                                    <?php echo $projectArea; ?>
                                </span>
                            </li>
                            <li class="mb-3">
                                <strong>Total Units:</strong>
                                <span class="float-end">
                                    <?php echo $totalUnits; ?>
                                </span>
                            </li>
                            <li class="mb-3">
                                <strong>Possession:</strong>
                                <span class="float-end">
                                    <?php echo $possessionDate; ?></p>
                                </span>
                            </li>
                        </ul>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#enquiryModal">
                                <i class="fas fa-phone-alt me-2"></i> Request Call Back
                            </button>
                            <a href="#site-plan" class="btn btn-outline-primary">
                                <i class="fas fa-download me-2"></i> Download Brochure
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enquiry Modal -->
    <div class="modal fade" id="enquiryModal" tabindex="-1" aria-labelledby="enquiryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enquiryModalLabel">Enquire About <?php echo $projectName; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="enquiryForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="3">I'm interested in <?php echo $projectName; ?>. Please contact me with more details.</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit Enquiry</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/templates/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Handle enquiry form submission
        document.getElementById('enquiryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your enquiry. We will contact you shortly.');
            var modal = bootstrap.Modal.getInstance(document.getElementById('enquiryModal'));
            modal.hide();
        });
    </script>
</body>
</html>