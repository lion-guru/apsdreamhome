<?php

namespace App\Services\Legacy;
// Include database connection
$db = \App\Core\App::database();

// Check if project ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: projects.php');
    exit();
}

$projectId = (int)$_GET['id'];

try {
    // Get project details
    $sql = "SELECT * FROM projects WHERE id = ? AND status = 'active'";
    $project = $db->fetch($sql, [$projectId]);

    if (!$project) {
        header('Location: projects.php');
        exit();
    }

    // Get related projects (same location)
    $sql = "SELECT id, name, image_path, location, status FROM projects
            WHERE location = ? AND id != ? AND status = 'active' LIMIT 3";
    $relatedProjects = $db->fetchAll($sql, [$project['location'], $projectId]);

} catch (Exception $e) {
    $error = "Error loading project details. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($project['name'] ?? 'Project Details'); ?> - APS Dream Homes</title>
    <meta name="description" content="<?php echo h($project['meta_description'] ?? 'Explore this premium project by APS Dream Homes.'); ?>">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        .project-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
                        url('<?php echo h($project['banner_image'] ?? $project['image_path'] ?? 'assets/images/project-banner.jpg'); ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 150px 0 100px;
            margin-top: -20px;
            position: relative;
        }

        .project-highlight {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .highlight-item {
            text-align: center;
            padding: 15px;
        }

        .highlight-icon {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 15px;
        }

        .gallery-item {
            margin-bottom: 20px;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
        }

        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .amenity-card {
            text-align: center;
            padding: 30px 20px;
            border-radius: 15px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 2px solid transparent;
            height: 100%;
        }

        .amenity-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.15);
            border-color: #667eea;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }

        .amenity-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .amenity-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a237e;
            margin-bottom: 10px;
        }

        .amenity-description {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/components/header.php'; ?>

    <!-- Project Hero Section -->
    <section class="project-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-3"><?php echo h($project['name']); ?></h1>
                    <p class="lead mb-4"><?php echo h($project['tagline'] ?? 'Premium Living Redefined'); ?></p>
                    <div class="d-flex justify-content-center gap-3">
                        <span class="badge bg-primary px-3 py-2">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            <?php echo h($project['location']); ?>
                        </span>
                        <span class="badge bg-success px-3 py-2">
                            <?php echo ucfirst(h($project['status'])); ?>
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
                                <?php echo nl2br(h($project['description'] ?? 'No description available.')); ?>
                            </div>

                            <h3 class="mt-5 mb-4">Project Highlights</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="highlight-item">
                                        <div class="highlight-icon">
                                            <i class="fas fa-home"></i>
                                        </div>
                                        <h5>Project Type</h5>
                                        <p class="text-muted"><?php echo h($project['type'] ?? 'Residential'); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="highlight-item">
                                        <div class="highlight-icon">
                                            <i class="fas fa-map-marked-alt"></i>
                                        </div>
                                        <h5>Location</h5>
                                        <p class="text-muted"><?php echo h($project['location']); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="highlight-item">
                                        <div class="highlight-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <h5>Possession</h5>
                                        <p class="text-muted"><?php echo h($project['possession_date'] ?? 'To be announced'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <h3 class="mt-5 mb-4">Premium Amenities</h3>
                            <div class="row g-4">
                                <!-- Row 1 -->
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="amenity-card">
                                        <div class="amenity-icon">
                                            <i class="fas fa-bolt"></i>
                                        </div>
                                        <h6 class="amenity-title">24/7 Power Backup</h6>
                                        <p class="amenity-description">Uninterrupted power supply for all residents</p>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="amenity-card">
                                        <div class="amenity-icon">
                                            <i class="fas fa-cloud-rain"></i>
                                        </div>
                                        <h6 class="amenity-title">Rainwater Harvesting</h6>
                                        <p class="amenity-description">Sustainable water conservation system</p>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="amenity-card">
                                        <div class="amenity-icon">
                                            <i class="fas fa-swimming-pool"></i>
                                        </div>
                                        <h6 class="amenity-title">Swimming Pool</h6>
                                        <p class="amenity-description">Temperature controlled swimming facility</p>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="amenity-card">
                                        <div class="amenity-icon">
                                            <i class="fas fa-seedling"></i>
                                        </div>
                                        <h6 class="amenity-title">Landscaped Gardens</h6>
                                        <p class="amenity-description">Beautifully maintained green spaces</p>
                                    </div>
                                </div>

                                <!-- Row 2 -->
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="amenity-card">
                                        <div class="amenity-icon">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <h6 class="amenity-title">Club House</h6>
                                        <p class="amenity-description">Premium community and recreation center</p>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="amenity-card">
                                        <div class="amenity-icon">
                                            <i class="fas fa-dumbbell"></i>
                                        </div>
                                        <h6 class="amenity-title">Gymnasium</h6>
                                        <p class="amenity-description">State-of-the-art fitness equipment</p>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="amenity-card">
                                        <div class="amenity-icon">
                                            <i class="fas fa-child"></i>
                                        </div>
                                        <h6 class="amenity-title">Children's Play Area</h6>
                                        <p class="amenity-description">Safe and fun play zone for kids</p>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="amenity-card">
                                        <div class="amenity-icon">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                        <h6 class="amenity-title">Security & CCTV</h6>
                                        <p class="amenity-description">24/7 surveillance and security</p>
                                    </div>
                                </div>

                                <!-- Row 3 -->
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="amenity-card">
                                        <div class="amenity-icon">
                                            <i class="fas fa-elevator"></i>
                                        </div>
                                        <h6 class="amenity-title">Lift in all Towers</h6>
                                        <p class="amenity-description">High-speed elevators in every building</p>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="amenity-card">
                                        <div class="amenity-icon">
                                            <i class="fas fa-car"></i>
                                        </div>
                                        <h6 class="amenity-title">Parking Space</h6>
                                        <p class="amenity-description">Ample covered parking for residents</p>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="amenity-card">
                                        <div class="amenity-icon">
                                            <i class="fas fa-water"></i>
                                        </div>
                                        <h6 class="amenity-title">Water Treatment</h6>
                                        <p class="amenity-description">Advanced water purification system</p>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="amenity-card">
                                        <div class="amenity-icon">
                                            <i class="fas fa-wifi"></i>
                                        </div>
                                        <h6 class="amenity-title">High-Speed Internet</h6>
                                        <p class="amenity-description">Fiber optic connectivity throughout</p>
                                    </div>
                                </div>
                            </div>

                            <h3 class="mt-5 mb-4">Gallery</h3>
                            <div class="row">
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <div class="col-md-4 col-6">
                                        <div class="gallery-item">
                                            <img src="<?php echo h($project['image_path'] ?? 'assets/images/project-' . $i . '.jpg'); ?>"
                                                 alt="<?php echo h($project['name'] . ' - Image ' . $i); ?>"
                                                 class="img-fluid">
                                        </div>
                                    </div>
                                <?php endfor; ?>
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
                                    ₹<?php
                                    $startingPrice = $project['starting_price'] ?? 0;
                                    echo is_numeric($startingPrice) ? number_format($startingPrice) : $startingPrice;
                                    ?>+
                                </span>
                            </li>
                            <li class="mb-3">
                                <strong>Project Area:</strong>
                                <span class="float-end">
                                    <?php
                                    $area = $project['area'] ?? 0;
                                    echo is_numeric($area) ? number_format($area) : $area;
                                    ?> sq.ft.
                                </span>
                            </li>
                            <li class="mb-3">
                                <strong>Total Units:</strong>
                                <span class="float-end">
                                    <?php
                                    $totalUnits = $project['total_units'] ?? 'N/A';
                                    echo is_numeric($totalUnits) ? number_format($totalUnits) : $totalUnits;
                                    ?>
                                </span>
                            </li>
                            <li class="mb-3">
                                <strong>Possession:</strong>
                                <span class="float-end">
                                    <?php echo $project['possession_date'] ?? 'To be announced'; ?>
                                </span>
                            </li>
                            <li class="mb-3">
                                <strong>RERA No:</strong>
                                <span class="float-end">
                                    <?php echo $project['rera_number'] ?? 'Available Soon'; ?>
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

                        <div class="mt-4">
                            <h5>Share this project:</h5>
                            <div class="d-flex gap-2">
                                <a href="#" class="btn btn-sm btn-outline-secondary">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-secondary">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-secondary">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Projects -->
            <?php if (!empty($relatedProjects)): ?>
                <div class="row mt-5">
                    <div class="col-12">
                        <h3 class="mb-4">Similar Projects in <?php echo h($project['location']); ?></h3>
                    </div>
                    <?php foreach ($relatedProjects as $related): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <img src="<?php echo h($related['image_path'] ?? 'assets/images/project-placeholder.jpg'); ?>"
                                     class="card-img-top"
                                     alt="<?php echo h($related['name']); ?>">
                                <div class="card-body">
                                    <span class="badge bg-primary mb-2">
                                        <?php echo ucfirst(h($related['status'])); ?>
                                    </span>
                                    <h5 class="card-title"><?php echo h($related['name']); ?></h5>
                                    <p class="card-text text-muted">
                                        <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                        <?php echo h($related['location']); ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <a href="project-details.php?id=<?php echo $related['id']; ?>" class="btn btn-outline-primary w-100">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Enquiry Modal -->
    <div class="modal fade" id="enquiryModal" tabindex="-1" aria-labelledby="enquiryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enquiryModalLabel">Enquire About <?php echo h($project['name']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="enquiryForm">
                        <input type="hidden" name="project_id" value="<?php echo $projectId; ?>">
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
                            <textarea class="form-control" id="message" name="message" rows="3">I'm interested in <?php echo h($project['name']); ?>. Please contact me with more details.</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit Enquiry</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/components/footer.php'; ?>

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
            // Add your form submission logic here
            alert('Thank you for your enquiry. We will contact you shortly.');
            var modal = bootstrap.Modal.getInstance(document.getElementById('enquiryModal'));
            modal.hide();
        });
    </script>
</body>
</html>
