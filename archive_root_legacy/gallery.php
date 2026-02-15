<?php
/**
 * Gallery Page - APS Dream Homes
 * Display property and project images
 */

require_once 'core/functions.php';
require_once 'includes/db_connection.php';

try {
    $pdo = getMysqliConnection();

    // Get gallery categories
    $categoriesQuery = "SELECT DISTINCT category FROM gallery_images WHERE status = 'active' ORDER BY category";
    $categoriesStmt = $pdo->query($categoriesQuery);
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

    // Get current category filter
    $currentCategory = $_GET['category'] ?? 'all';
    $whereClause = $currentCategory !== 'all' ? "AND category = :category" : "";

    // Get gallery images
    $imagesQuery = "SELECT * FROM gallery_images WHERE status = 'active' $whereClause ORDER BY created_at DESC";
    $imagesStmt = $pdo->prepare($imagesQuery);

    if ($currentCategory !== 'all') {
        $imagesStmt->bindParam(':category', $currentCategory);
    }

    $imagesStmt->execute();
    $images = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log('Gallery page database error: ' . $e->getMessage());
    $categories = [];
    $images = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Include site settings
    require_once 'includes/site_settings.php';
    ?>
    <title><?php echo getSiteSetting('site_title', 'APS Dream Homes'); ?> - Gallery</title>
    <meta name="description" content="Explore our stunning collection of property images, project photos, and real estate developments at APS Dream Homes.">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Lightbox CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">

    <style>
        .gallery-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0 60px;
            text-align: center;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 30px;
        }

        .gallery-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-item-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 20px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover .gallery-item-overlay {
            transform: translateY(0);
        }

        .gallery-filter .btn {
            margin: 5px;
            border-radius: 25px;
            padding: 10px 20px;
        }

        .breadcrumb {
            background: #f8f9fa;
            border-radius: 0;
        }

        .empty-state {
            padding: 60px 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/templates/header.php'; ?>

    <!-- Hero Section -->
    <section class="gallery-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">Our Gallery</h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Explore our stunning collection of properties, projects, and developments through our comprehensive photo gallery.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Breadcrumb -->
    <nav class="bg-light border-bottom py-2" aria-label="breadcrumb">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Gallery</li>
            </ol>
        </div>
    </nav>

    <!-- Gallery Content -->
    <main class="py-5">
        <div class="container">
            <!-- Category Filter -->
            <?php if (!empty($categories)): ?>
            <div class="row mb-5">
                <div class="col-12">
                    <div class="gallery-filter text-center">
                        <a href="gallery.php" class="btn <?php echo $currentCategory === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            All Images
                        </a>
                        <?php foreach ($categories as $category): ?>
                        <a href="gallery.php?category=<?php echo urlencode($category); ?>"
                           class="btn <?php echo $currentCategory === $category ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $category))); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Gallery Grid -->
            <?php if (!empty($images)): ?>
            <div class="row g-4">
                <?php foreach ($images as $image): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="gallery-item">
                        <a href="<?php echo htmlspecialchars($image['image_path']); ?>"
                           data-lightbox="gallery"
                           data-title="<?php echo htmlspecialchars($image['title']); ?>">
                            <img src="<?php echo htmlspecialchars($image['thumbnail_path'] ?? $image['image_path']); ?>"
                                 alt="<?php echo htmlspecialchars($image['title']); ?>">
                            <div class="gallery-item-overlay">
                                <h5><?php echo htmlspecialchars($image['title']); ?></h5>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($image['description'] ?? ''); ?></p>
                            </div>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-images fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">No images found</h3>
                    <p class="text-muted mb-4">
                        <?php if ($currentCategory !== 'all'): ?>
                        No images found in the <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $currentCategory))); ?> category.
                        <?php else: ?>
                        No gallery images available at the moment.
                        <?php endif; ?>
                    </p>
                    <a href="gallery.php" class="btn btn-primary">View All Categories</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/templates/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Lightbox JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Initialize Lightbox
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'albumLabel': 'Image %1 of %2'
        });
    </script>
</body>
</html>