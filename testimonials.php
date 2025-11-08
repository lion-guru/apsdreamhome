<?php
/**
 * Testimonials Page - APS Dream Homes
 * Display client testimonials and reviews
 */

require_once 'core/functions.php';
require_once 'includes/db_connection.php';

try {
    $pdo = getDbConnection();

    // Get distinct categories
    $categoryQuery = "SELECT DISTINCT category FROM testimonials WHERE status = 'approved' ORDER BY category";
    $categories = $pdo->query($categoryQuery)->fetchAll(PDO::FETCH_COLUMN);

    // Pagination settings
    $itemsPerPage = 9;
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($currentPage - 1) * $itemsPerPage;

    // Get current category filter
    $currentCategory = $_GET['category'] ?? 'all';
    $whereClause = $currentCategory !== 'all' ? "AND category = :category" : "";

    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM testimonials WHERE status = 'approved' $whereClause";
    $countStmt = $pdo->prepare($countQuery);

    if ($currentCategory !== 'all') {
        $countStmt->bindParam(':category', $currentCategory);
    }

    $countStmt->execute();
    $totalItems = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalItems / $itemsPerPage);

    // Get testimonials
    $testimonialsQuery = "SELECT * FROM testimonials WHERE status = 'approved' $whereClause ORDER BY created_at DESC LIMIT :offset, :limit";
    $testimonialsStmt = $pdo->prepare($testimonialsQuery);

    if ($currentCategory !== 'all') {
        $testimonialsStmt->bindParam(':category', $currentCategory);
    }

    $testimonialsStmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $testimonialsStmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
    $testimonialsStmt->execute();
    $testimonials = $testimonialsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log('Testimonials page database error: ' . $e->getMessage());
    $categories = [];
    $testimonials = [];
    $totalPages = 1;
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
    <title><?php echo getSiteSetting('site_title', 'APS Dream Homes'); ?> - Client Testimonials</title>
    <meta name="description" content="Read what our satisfied clients have to say about their experience with APS Dream Homes. Real stories from real customers.">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        .testimonials-hero {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 100px 0 60px;
            text-align: center;
        }

        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            border-left: 5px solid #667eea;
        }

        .testimonial-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .testimonial-content {
            margin-bottom: 25px;
        }

        .rating {
            margin-bottom: 15px;
        }

        .rating .fas {
            font-size: 1.1rem;
        }

        .testimonial-text {
            font-size: 1rem;
            line-height: 1.6;
            color: #333;
            font-style: italic;
            margin-bottom: 0;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .author-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #667eea;
            flex-shrink: 0;
        }

        .author-info {
            flex-grow: 1;
        }

        .author-name {
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 3px;
        }

        .author-location {
            color: #666;
            font-size: 0.9rem;
        }

        .property-name {
            font-size: 0.85rem;
        }

        .testimonial-filter .btn {
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

        .submit-testimonial-cta {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .pagination .page-link {
            color: #667eea;
            border-color: #667eea;
        }

        .pagination .page-item.active .page-link {
            background-color: #667eea;
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/templates/header.php'; ?>

    <!-- Hero Section -->
    <section class="testimonials-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">Client Testimonials</h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Discover what our valued clients have to say about their experience with APS Dream Homes.
                    </p>
                    <a href="contact.php" class="btn btn-light btn-lg" data-aos="fade-up" data-aos-delay="200">
                        <i class="fas fa-star me-2"></i>Share Your Story
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Breadcrumb -->
    <nav class="bg-light border-bottom py-2" aria-label="breadcrumb">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Testimonials</li>
            </ol>
        </div>
    </nav>

    <!-- Testimonials Content -->
    <main class="py-5">
        <div class="container">
            <!-- Category Filter -->
            <?php if (!empty($categories)): ?>
            <div class="row mb-5">
                <div class="col-12">
                    <div class="testimonial-filter text-center">
                        <a href="testimonials.php" class="btn <?php echo $currentCategory === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            All Reviews
                        </a>
                        <?php foreach ($categories as $category): ?>
                        <a href="testimonials.php?category=<?php echo urlencode($category); ?>"
                           class="btn <?php echo $currentCategory === $category ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $category))); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Testimonials Grid -->
            <?php if (!empty($testimonials)): ?>
            <div class="row g-4">
                <?php foreach ($testimonials as $testimonial): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="testimonial-text"><?php echo htmlspecialchars($testimonial['content']); ?></p>
                        </div>
                        <div class="testimonial-author">
                            <?php if (!empty($testimonial['author_image'])): ?>
                            <img src="<?php echo htmlspecialchars($testimonial['author_image']); ?>"
                                 alt="<?php echo htmlspecialchars($testimonial['author_name']); ?>"
                                 class="author-image">
                            <?php endif; ?>
                            <div class="author-info">
                                <h5 class="author-name"><?php echo htmlspecialchars($testimonial['author_name']); ?></h5>
                                <p class="author-location text-muted mb-0"><?php echo htmlspecialchars($testimonial['author_location']); ?></p>
                                <?php if (!empty($testimonial['property_name'])): ?>
                                <p class="property-name text-primary mb-0">
                                    <i class="fas fa-home me-1"></i>
                                    <?php echo htmlspecialchars($testimonial['property_name']); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-5" aria-label="Testimonials pagination">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                        <a class="page-link" href="testimonials.php?page=<?php echo $i; ?><?php echo $currentCategory !== 'all' ? '&category=' . urlencode($currentCategory) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>

            <?php else: ?>
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-comments fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">No testimonials found</h3>
                    <p class="text-muted mb-4">
                        <?php if ($currentCategory !== 'all'): ?>
                        No testimonials found in the <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $currentCategory))); ?> category.
                        <?php else: ?>
                        No testimonials available at the moment.
                        <?php endif; ?>
                    </p>
                    <a href="testimonials.php" class="btn btn-primary">View All Reviews</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Submit Testimonial CTA -->
            <div class="submit-testimonial-cta text-center mt-5 py-5 rounded-3">
                <div class="container">
                    <h3 class="mb-3">Share Your Experience</h3>
                    <p class="text-muted mb-4">We'd love to hear about your journey with APS Dream Homes</p>
                    <a href="contact.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-star me-2"></i>Submit Your Testimonial
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/templates/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    </script>
</body>
</html>