<?php
// Include database connection
require_once 'includes/db_connection.php';

// Get all unique locations for filter
try {
    $locationStmt = $pdo->query("SELECT DISTINCT location FROM projects WHERE status = 'active' ORDER BY location");
    $locations = $locationStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get projects based on filter
    $locationFilter = isset($_GET['location']) ? $_GET['location'] : '';
    $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
    
    $query = "SELECT * FROM projects WHERE status = 'active'";
    $params = [];
    
    if ($locationFilter) {
        $query .= " AND location = ?";
        $params[] = $locationFilter;
    }
    
    $query .= " ORDER BY name";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error loading projects. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Projects - APS Dream Homes</title>
    <meta name="description" content="Explore our premium real estate projects in Gorakhpur, Lucknow, and other prime locations. Find your dream home with APS Dream Homes.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        .project-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        
        .project-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .project-image {
            height: 250px;
            object-fit: cover;
            width: 100%;
            transition: transform 0.3s ease;
        }
        
        .project-card:hover .project-image {
            transform: scale(1.05);
        }
        
        .location-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .project-status {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="bg-primary text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold">Our Premium Projects</h1>
                    <p class="lead">Discover our exclusive residential and commercial projects in prime locations</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section class="py-5">
        <div class="container">
            <!-- Filter Section -->
            <div class="filter-section">
                <form action="" method="get" class="row g-3">
                    <div class="col-md-5">
                        <label for="location" class="form-label">Location</label>
                        <select name="location" id="location" class="form-select">
                            <option value="">All Locations</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?php echo htmlspecialchars($loc); ?>" <?php echo ($locationFilter === $loc) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loc); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="upcoming" <?php echo ($statusFilter === 'upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="ongoing" <?php echo ($statusFilter === 'ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="completed" <?php echo ($statusFilter === 'completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
            
            <!-- Projects Grid -->
            <div class="row">
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $project): ?>
                        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                            <div class="card project-card h-100">
                                <div class="position-relative">
                                    <img src="<?php echo htmlspecialchars($project['image_path'] ?? 'assets/images/project-placeholder.jpg'); ?>" 
                                         class="project-image" 
                                         alt="<?php echo htmlspecialchars($project['name']); ?>"
                                         loading="lazy">
                                    <div class="location-badge">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($project['location']); ?>
                                    </div>
                                    <div class="project-status">
                                        <?php echo ucfirst(htmlspecialchars($project['status'])); ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($project['name']); ?></h5>
                                    <p class="card-text text-muted">
                                        <?php 
                                            $desc = $project['description'] ?? 'No description available.';
                                            echo strlen($desc) > 150 ? substr($desc, 0, 150) . '...' : $desc;
                                        ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <a href="project-details.php?id=<?php echo $project['id']; ?>" class="btn btn-outline-primary w-100">
                                        View Details <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-info">
                            No projects found matching your criteria. Please try different filters.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <h2 class="mb-4">Can't Find What You're Looking For?</h2>
            <p class="lead mb-4">Contact our sales team for more information about upcoming projects and exclusive offers.</p>
            <a href="contact.php" class="btn btn-primary btn-lg">Contact Us</a>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

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
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>
