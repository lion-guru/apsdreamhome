<?php
/**
 * Blog Page - APS Dream Homes
 * Real estate blog and industry insights
 */

session_start();
require_once 'core/functions.php';
require_once 'includes/db_connection.php';

try {
    $pdo = getDbConnection();

    // Get blog posts
    $blogQuery = "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 12";
    $blogStmt = $pdo->query($blogQuery);
    $blog_posts = $blogStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get categories for filter
    $categoriesQuery = "SELECT DISTINCT category FROM blog_posts WHERE status = 'published'";
    $categoriesStmt = $pdo->query($categoriesQuery);
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log('Blog page database error: ' . $e->getMessage());
    $blog_posts = [];
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Real Estate Insights - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0 60px;
            text-align: center;
        }
        .section-padding {
            padding: 80px 0;
        }
        .blog-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
            height: 100%;
        }
        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .blog-image {
            height: 200px;
            object-fit: cover;
        }
        .category-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(102, 126, 234, 0.9);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .read-more-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        .read-more-btn:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .newsletter-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 60px 0;
        }
        .filter-btn {
            border: 2px solid #667eea;
            background: transparent;
            color: #667eea;
            padding: 8px 20px;
            border-radius: 25px;
            margin: 0 5px 10px;
            transition: all 0.3s;
        }
        .filter-btn:hover,
        .filter-btn.active {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/templates/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Real Estate Insights</h1>
                    <p class="lead mb-4">Stay updated with the latest trends, tips, and news in the real estate industry</p>
                    <p class="mb-0">From market analysis to investment strategies, our expert insights help you make informed decisions about your property journey.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 text-center">
                    <h3 class="mb-3">Subscribe to Our Newsletter</h3>
                    <p class="mb-4">Get the latest real estate insights and market updates delivered to your inbox</p>
                    <form class="d-flex gap-2">
                        <input type="email" class="form-control" placeholder="Enter your email" required>
                        <button type="submit" class="btn btn-light">
                            <i class="fas fa-envelope me-1"></i>Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="section-padding">
        <div class="container">
            <!-- Filter Buttons -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="text-center">
                        <button class="filter-btn active" data-category="all">All Posts</button>
                        <?php foreach ($categories as $category): ?>
                        <button class="filter-btn" data-category="<?php echo htmlspecialchars($category['category']); ?>">
                            <?php echo ucfirst(htmlspecialchars($category['category'])); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Featured Post -->
            <?php if (!empty($blog_posts)): ?>
            <div class="row mb-5">
                <div class="col-12">
                    <h3 class="mb-4">Featured Post</h3>
                </div>
                <div class="col-12">
                    <div class="card blog-card shadow-sm">
                        <div class="position-relative">
                            <img src="<?php echo htmlspecialchars($blog_posts[0]['featured_image'] ?? 'assets/images/blog-placeholder.jpg'); ?>"
                                 class="card-img-top blog-image" alt="Featured Post">
                            <div class="category-badge">
                                <?php echo ucfirst(htmlspecialchars($blog_posts[0]['category'])); ?>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('M d, Y', strtotime($blog_posts[0]['created_at'])); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo htmlspecialchars($blog_posts[0]['read_time']); ?> min read
                                </small>
                            </div>
                            <h3 class="card-title mb-3"><?php echo htmlspecialchars($blog_posts[0]['title']); ?></h3>
                            <p class="card-text mb-3"><?php echo htmlspecialchars(substr($blog_posts[0]['excerpt'], 0, 200)) . '...'; ?></p>
                            <a href="blog-post.php?id=<?php echo $blog_posts[0]['id']; ?>" class="read-more-btn">
                                <i class="fas fa-arrow-right me-1"></i>Read More
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- All Posts -->
            <div class="row">
                <div class="col-12 mb-4">
                    <h3>Latest Posts</h3>
                </div>

                <?php for ($i = 1; $i < count($blog_posts); $i++): ?>
                <div class="col-lg-4 col-md-6" data-category="<?php echo htmlspecialchars($blog_posts[$i]['category']); ?>">
                    <div class="card blog-card shadow-sm h-100">
                        <div class="position-relative">
                            <img src="<?php echo htmlspecialchars($blog_posts[$i]['featured_image'] ?? 'assets/images/blog-placeholder.jpg'); ?>"
                                 class="card-img-top blog-image" alt="Blog Post">
                            <div class="category-badge">
                                <?php echo ucfirst(htmlspecialchars($blog_posts[$i]['category'])); ?>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('M d, Y', strtotime($blog_posts[$i]['created_at'])); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo htmlspecialchars($blog_posts[$i]['read_time']); ?> min
                                </small>
                            </div>
                            <h6 class="card-title mb-2"><?php echo htmlspecialchars($blog_posts[$i]['title']); ?></h6>
                            <p class="card-text small text-muted mb-3">
                                <?php echo htmlspecialchars(substr($blog_posts[$i]['excerpt'], 0, 100)) . '...'; ?>
                            </p>
                            <a href="blog-post.php?id=<?php echo $blog_posts[$i]['id']; ?>" class="read-more-btn btn-sm">
                                <i class="fas fa-arrow-right me-1"></i>Read More
                            </a>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>

            <!-- Load More Button -->
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <button class="btn btn-outline-primary btn-lg" id="loadMore">
                        <i class="fas fa-plus me-2"></i>Load More Posts
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h3 class="fw-bold">Explore by Category</h3>
                    <p class="lead text-muted">Find articles that interest you most</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center p-4 h-100">
                        <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                        <h6>Market Trends</h6>
                        <p class="small text-muted">Latest market analysis and price trends</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center p-4 h-100">
                        <i class="fas fa-coins fa-3x text-success mb-3"></i>
                        <h6>Investment Tips</h6>
                        <p class="small text-muted">Smart investment strategies and advice</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center p-4 h-100">
                        <i class="fas fa-home fa-3x text-info mb-3"></i>
                        <h6>Buying Guide</h6>
                        <p class="small text-muted">Complete guides for first-time buyers</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center p-4 h-100">
                        <i class="fas fa-balance-scale fa-3x text-warning mb-3"></i>
                        <h6>Legal & Finance</h6>
                        <p class="small text-muted">Legal aspects and financing options</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const blogCards = document.querySelectorAll('[data-category]');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const category = this.getAttribute('data-category');

                    // Update active button
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    // Filter posts
                    blogCards.forEach(card => {
                        if (category === 'all' || card.getAttribute('data-category') === category) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });

            // Load more functionality
            document.getElementById('loadMore').addEventListener('click', function() {
                // In a real application, this would load more posts via AJAX
                alert('Load more functionality would be implemented here');
            });
        });
    </script>
</body>
</html>
