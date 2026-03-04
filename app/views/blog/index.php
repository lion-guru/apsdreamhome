<?php
/**
 * Blog Page Template
 * Converted from Blade to plain PHP for custom router system
 */

$page_title = $page_title ?? 'Blog - APS Dream Home';
$page_description = $page_description ?? 'Stay updated with the latest real estate trends, property tips, and market insights from our expert team.';
$blog_stats = $blog_stats ?? [];
$blog_categories = $blog_categories ?? [];
$featured_posts = $featured_posts ?? [];
$recent_posts = $recent_posts ?? [];
$popular_tags = $popular_tags ?? [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="real estate Gorakhpur, property for sale, buy house, apartments Lucknow, real estate UP, dream home">
    <meta name="author" content="APS Dream Home">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://localhost/apsdreamhome/public">
    <meta property="og:title" content="APS Dream Home - Premium Real Estate in UP">
    <meta property="og:description" content="Discover exclusive properties with the most trusted real estate platform in Uttar Pradesh.">
    <meta property="og:image" content="http://localhost/apsdreamhome/public/assets/images/logo/apslogo.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="http://localhost/apsdreamhome/public">
    <meta property="twitter:title" content="APS Dream Home - Premium Real Estate">
    <meta property="twitter:description" content="Find your dream home with APS Dream Home - Premium properties in UP">
    <meta property="twitter:image" content="http://localhost/apsdreamhome/public/assets/images/logo/apslogo.png">

    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="http://localhost/apsdreamhome/public/assets/css/style.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            --secondary-gradient: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(0, 0, 0, 0.1);
            --text-dark: #1e293b;
            --text-light: #64748b;
            --accent-color: #d97706;
            --primary-color: #1e40af;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
        }

        .hero-section {
            background: var(--primary-gradient);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .blog-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }

        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .blog-card img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .featured-post {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .featured-post:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .category-badge {
            background: var(--accent-color);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .reading-time {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .blog-stats {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 12px;
            padding: 2rem;
            margin: -2rem 0 2rem 0;
            position: relative;
            z-index: 10;
        }

        .stat-item {
            text-align: center;
        }

        .recent-post {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .recent-post:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .recent-post-image {
            width: 120px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
        }

        .recent-post-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tag-cloud a {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 4px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .tag-cloud a:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .sidebar-widget {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .sidebar-widget h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .category-list {
            list-style: none;
            padding: 0;
        }

        .category-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .category-list li:last-child {
            border-bottom: none;
        }

        .category-list a {
            color: var(--text-dark);
            text-decoration: none;
            display: flex;
            justify-content: between;
            align-items: center;
            transition: color 0.3s ease;
        }

        .category-list a:hover {
            color: var(--primary-color);
        }

        .post-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .search-form {
            position: relative;
        }

        .search-form input {
            padding-right: 45px;
            border-radius: 25px;
            border: 2px solid #e5e7eb;
            transition: border-color 0.3s ease;
        }

        .search-form input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }

        .search-form button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-color);
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: background 0.3s ease;
        }

        .search-form button:hover {
            background: #1e3a8a;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 3rem 0;
            }
            
            .blog-card img {
                height: 150px;
            }
            
            .recent-post-image {
                width: 80px;
                height: 60px;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="http://localhost/apsdreamhome/public">
                <img src="http://localhost/apsdreamhome/public/assets/images/logo/apslogo.png" alt="APS Dream Home" height="40" class="me-2">
                APS Dream Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="http://localhost/apsdreamhome/public">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="http://localhost/apsdreamhome/public/properties">Properties</a></li>
                    <li class="nav-item"><a class="nav-link" href="http://localhost/apsdreamhome/public/projects">Projects</a></li>
                    <li class="nav-item"><a class="nav-link" href="http://localhost/apsdreamhome/public/about">About</a></li>
                    <li class="nav-item"><a class="nav-link active" href="http://localhost/apsdreamhome/public/blog">Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="http://localhost/apsdreamhome/public/contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-white py-5" style="margin-top: 76px;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="display-4 fw-bold mb-4">Blog</h1>
                        <p class="lead mb-4"><?php echo htmlspecialchars($page_description); ?></p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="http://localhost/apsdreamhome/public/contact" class="btn btn-light btn-lg">Get Expert Advice</a>
                            <a href="http://localhost/apsdreamhome/public/properties" class="btn btn-outline-light btn-lg">Browse Properties</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image text-center">
                        <img src="http://localhost/apsdreamhome/public/assets/images/blog-hero.jpg" alt="Blog" class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Statistics -->
    <section class="py-4 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-item">
                        <h3 class="text-primary fw-bold"><?php echo htmlspecialchars($blog_stats['total_posts'] ?? '91'); ?>+</h3>
                        <p class="text-muted mb-0">Articles</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-item">
                        <h3 class="text-success fw-bold"><?php echo htmlspecialchars($blog_stats['total_categories'] ?? '5'); ?></h3>
                        <p class="text-muted mb-0">Categories</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-item">
                        <h3 class="text-info fw-bold"><?php echo htmlspecialchars($blog_stats['total_authors'] ?? '8'); ?></h3>
                        <p class="text-muted mb-0">Expert Authors</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-item">
                        <h3 class="text-warning fw-bold"><?php echo htmlspecialchars(number_format($blog_stats['total_views'] ?? '45670')); ?>+</h3>
                        <p class="text-muted mb-0">Total Views</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Blog Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Blog Posts -->
                <div class="col-lg-8">
                    <?php if (!empty($featured_posts)): ?>
                    <!-- Featured Posts -->
                    <div class="featured-posts mb-5">
                        <h2 class="mb-4 fw-bold">Featured Posts</h2>
                        <div class="row">
                            <?php foreach ($featured_posts as $post): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="featured-post h-100">
                                    <img src="http://localhost/apsdreamhome/public/assets/images/<?php echo htmlspecialchars($post['featured_image'] ?? 'blog/default-featured.jpg'); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="img-fluid">
                                    <div class="p-4">
                                        <div class="mb-3">
                                            <span class="category-badge"><?php echo htmlspecialchars($post['category']); ?></span>
                                            <?php if ($post['featured'] ?? false): ?>
                                            <span class="badge bg-warning text-dark ms-2">
                                                <i class="fas fa-star"></i> Featured
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="h5 mb-3">
                                            <a href="#" class="text-decoration-none text-dark"><?php echo htmlspecialchars($post['title']); ?></a>
                                        </h3>
                                        <p class="text-muted mb-3"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                                        <div class="post-meta">
                                            <span class="reading-time">
                                                <i class="fas fa-clock"></i> <?php echo htmlspecialchars($post['reading_time']); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-eye"></i> <?php echo number_format($post['views'] ?? 0); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-heart"></i> <?php echo number_format($post['likes'] ?? 0); ?>
                                            </span>
                                        </div>
                                        <div class="author-info mt-3">
                                            <img src="http://localhost/apsdreamhome/public/assets/images/<?php echo htmlspecialchars($post['author']['avatar'] ?? 'authors/default.jpg'); ?>" alt="<?php echo htmlspecialchars($post['author']['name']); ?>" class="author-avatar">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($post['author']['name']); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($post['author']['role']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Recent Posts -->
                    <div class="recent-posts">
                        <h2 class="mb-4 fw-bold">Recent Posts</h2>
                        <?php if (!empty($recent_posts)): ?>
                            <?php foreach ($recent_posts as $post): ?>
                            <article class="recent-post d-flex gap-4">
                                <div class="recent-post-image flex-shrink-0">
                                    <img src="http://localhost/apsdreamhome/public/assets/images/<?php echo htmlspecialchars($post['thumbnail'] ?? 'blog/thumbnails/default.jpg'); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="mb-2">
                                        <span class="category-badge"><?php echo htmlspecialchars($post['category']); ?></span>
                                        <span class="text-muted ms-2">
                                            <i class="fas fa-calendar"></i> <?php echo htmlspecialchars($post['published_date']); ?>
                                        </span>
                                    </div>
                                    <h4 class="h5 mb-2">
                                        <a href="#" class="text-decoration-none text-dark"><?php echo htmlspecialchars($post['title']); ?></a>
                                    </h4>
                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                                    <div class="post-meta">
                                        <span class="reading-time">
                                            <i class="fas fa-clock"></i> <?php echo htmlspecialchars($post['reading_time']); ?>
                                        </span>
                                        <span class="ms-3">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($post['author']); ?>
                                        </span>
                                        <span class="ms-3">
                                            <i class="fas fa-eye"></i> <?php echo number_format($post['views'] ?? 0); ?>
                                        </span>
                                    </div>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No blog posts found. Check back soon for updates!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Search Widget -->
                    <div class="sidebar-widget">
                        <h4>Search</h4>
                        <form class="search-form">
                            <input type="text" class="form-control" placeholder="Search articles...">
                            <button type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Categories Widget -->
                    <div class="sidebar-widget">
                        <h4>Categories</h4>
                        <ul class="category-list">
                            <?php foreach ($blog_categories as $category): ?>
                            <li>
                                <a href="#">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                    <span class="badge bg-secondary float-end"><?php echo htmlspecialchars($category['post_count']); ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Popular Tags Widget -->
                    <div class="sidebar-widget">
                        <h4>Popular Tags</h4>
                        <div class="tag-cloud">
                            <?php foreach ($popular_tags as $tag): ?>
                            <a href="#"><?php echo htmlspecialchars($tag['name']); ?> (<?php echo htmlspecialchars($tag['count']); ?>)</a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Newsletter Widget -->
                    <div class="sidebar-widget">
                        <h4>Newsletter</h4>
                        <p class="text-muted">Subscribe to get the latest real estate insights and property updates.</p>
                        <form>
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="Your email" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="mb-3">APS Dream Home</h5>
                    <p class="text-light-muted">Leading real estate developer in Gorakhpur with 8+ years of excellence in property development and customer satisfaction.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-white"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="http://localhost/apsdreamhome/public" class="text-light-muted">Home</a></li>
                        <li><a href="http://localhost/apsdreamhome/public/properties" class="text-light-muted">Properties</a></li>
                        <li><a href="http://localhost/apsdreamhome/public/about" class="text-light-muted">About</a></li>
                        <li><a href="http://localhost/apsdreamhome/public/blog" class="text-light-muted">Blog</a></li>
                        <li><a href="http://localhost/apsdreamhome/public/contact" class="text-light-muted">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="mb-3">Contact Info</h5>
                    <p class="text-light-muted">
                        <i class="fas fa-map-marker-alt"></i> 1st floor singhariya chauraha, Kunraghat, deoria Road, Gorakhpur, UP - 273008<br>
                        <i class="fas fa-phone"></i> +91-9277121112<br>
                        <i class="fas fa-envelope"></i> info@apsdreamhomes.com
                    </p>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center">
                <p class="mb-0">&copy; 2025 APS Dream Homes Pvt Ltd. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Search form handling
        document.querySelector('.search-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = this.querySelector('input').value;
            if (searchTerm) {
                // Implement search functionality
                console.log('Searching for:', searchTerm);
            }
        });

        // Newsletter form handling
        document.querySelector('.sidebar-widget form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            if (email) {
                // Implement newsletter subscription
                alert('Thank you for subscribing! You will receive updates at: ' + email);
                this.reset();
            }
        });
    </script>
</body>
</html>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-warning fw-bold">{{ number_format($blog_stats['total_views'] ?? 45670) }}+</h3>
                    <p class="text-muted mb-0">Total Readers</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Browse by Category</h2>
            <p class="lead text-muted">Explore articles organized by topics that matter to you</p>
        </div>
        
        <div class="row g-4">
            @foreach($blog_categories ?? [] as $category)
            <div class="col-lg-4 col-md-6">
                <div class="category-card card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="category-icon mb-3">
                            <i class="fas fa-folder fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title fw-bold">{{ $category['name'] }}</h5>
                        <p class="text-muted small">{{ $category['description'] }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary">{{ $category['post_count'] }} Articles</span>
                            <a href="#" class="btn btn-outline-primary btn-sm">View All</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Posts -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Featured Articles</h2>
            <p class="lead text-muted">Handpicked articles with valuable insights for homebuyers and investors</p>
        </div>
        
        <div class="row g-4">
            @foreach($featured_posts ?? [] as $post)
            <div class="col-lg-4">
                <article class="featured-post card h-100 shadow-sm">
                    <div class="position-relative">
                        <img src="{{ asset('images/' . ($post['featured_image'] ?? 'blog/default-featured.jpg')) }}" 
                             alt="{{ $post['title'] }}" 
                             class="card-img-top post-image">
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-primary">{{ $post['category'] }}</span>
                        </div>
                        @if($post['featured'] ?? false)
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-warning">
                                <i class="fas fa-star"></i> Featured
                            </span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <div class="post-meta mb-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> {{ date('M d, Y', strtotime($post['published_date'])) }}
                                <span class="mx-2">•</span>
                                <i class="fas fa-clock"></i> {{ $post['reading_time'] }}
                            </small>
                        </div>
                        
                        <h5 class="card-title fw-bold mb-3">
                            <a href="#" class="text-decoration-none text-dark">{{ $post['title'] }}</a>
                        </h5>
                        
                        <p class="text-muted flex-grow-1">{{ $post['excerpt'] }}</p>
                        
                        <div class="author-info d-flex align-items-center mb-3">
                            <img src="{{ asset('images/' . ($post['author']['avatar'] ?? 'authors/default.jpg')) }}" 
                                 alt="{{ $post['author']['name'] }}" 
                                 class="rounded-circle me-2" 
                                 style="width: 32px; height: 32px; object-fit: cover;">
                            <div>
                                <small class="text-dark fw-semibold">{{ $post['author']['name'] }}</small>
                                <br>
                                <small class="text-muted">{{ $post['author']['role'] }}</small>
                            </div>
                        </div>
                        
                        <div class="post-stats d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-3">
                                <small class="text-muted">
                                    <i class="fas fa-eye"></i> {{ number_format($post['views']) }}
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-heart"></i> {{ $post['likes'] }}
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-comment"></i> {{ $post['comments'] }}
                                </small>
                            </div>
                            <a href="#" class="btn btn-primary btn-sm">Read More</a>
                        </div>
                    </div>
                </article>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-5">
            <a href="#" class="btn btn-outline-primary btn-lg">View All Articles</a>
        </div>
    </div>
</section>

<!-- Recent Posts -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Recent Articles</h2>
            <p class="lead text-muted">Latest insights and updates from our expert team</p>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="recent-posts">
                    @foreach($recent_posts ?? [] as $post)
                    <article class="recent-post d-flex gap-4 mb-4 pb-4 border-bottom">
                        <div class="recent-post-image flex-shrink-0">
                            <img src="{{ asset('images/' . ($post['thumbnail'] ?? 'blog/thumbnails/default.jpg')) }}" 
                                 alt="{{ $post['title'] }}" 
                                 class="rounded-3"
                                 style="width: 200px; height: 150px; object-fit: cover;">
                        </div>
                        
                        <div class="recent-post-content flex-grow-1">
                            <div class="post-meta mb-2">
                                <small class="text-muted">
                                    <span class="badge bg-secondary me-2">{{ $post['category'] }}</span>
                                    <i class="fas fa-calendar"></i> {{ date('M d, Y', strtotime($post['published_date'])) }}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock"></i> {{ $post['reading_time'] }}
                                </small>
                            </div>
                            
                            <h5 class="fw-bold mb-2">
                                <a href="#" class="text-decoration-none text-dark">{{ $post['title'] }}</a>
                            </h5>
                            
                            <p class="text-muted mb-3">{{ $post['excerpt'] }}</p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <small class="text-muted me-3">
                                        <i class="fas fa-user"></i> {{ $post['author'] }}
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-eye"></i> {{ number_format($post['views']) }} views
                                    </small>
                                </div>
                                <a href="#" class="btn btn-outline-primary btn-sm">Read More</a>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                
                <div class="text-center mt-4">
                    <button class="btn btn-outline-primary" id="load-more-posts">Load More Articles</button>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Popular Tags -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">Popular Tags</h5>
                        <div class="tag-cloud">
                            @foreach($popular_tags ?? [] as $tag)
                            <a href="#" class="badge bg-light text-dark text-decoration-none me-2 mb-2 d-inline-block">
                                #{{ $tag['name'] }} ({{ $tag['count'] }})
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Newsletter Subscribe -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">Subscribe to Newsletter</h5>
                        <p class="text-muted small mb-3">Get latest articles and market insights delivered to your inbox</p>
                        <form class="newsletter-form">
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="Your email address" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                        </form>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">Quick Links</h5>
                        <div class="list-group list-group-flush">
                            <a href="{{ url('/properties') }}" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-home me-2"></i> Browse Properties
                            </a>
                            <a href="{{ url('/projects') }}" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-building me-2"></i> Our Projects
                            </a>
                            <a href="{{ url('/gallery') }}" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-images me-2"></i> Gallery
                            </a>
                            <a href="{{ url('/contact') }}" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-phone me-2"></i> Contact Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-3">Need Expert Real Estate Advice?</h2>
                <p class="lead mb-0">Our team of experienced advisors is here to help you make informed decisions about your property investments.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ url('/contact') }}" class="btn btn-light btn-lg me-2">Consult Experts</a>
                <a href="tel:+917007444842" class="btn btn-outline-light btn-lg">Call Now</a>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.category-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.post-image {
    height: 200px;
    object-fit: cover;
}

.featured-post {
    transition: transform 0.3s ease;
}

.featured-post:hover {
    transform: translateY(-3px);
}

.recent-post-image img {
    transition: transform 0.3s ease;
}

.recent-post:hover .recent-post-image img {
    transform: scale(1.05);
}

.tag-cloud .badge {
    transition: all 0.3s ease;
}

.tag-cloud .badge:hover {
    background-color: #667eea !important;
    color: white !important;
    transform: translateY(-2px);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

@media (max-width: 768px) {
    .hero-section .display-4 {
        font-size: 2.5rem;
    }
    
    .recent-post {
        flex-direction: column;
    }
    
    .recent-post-image {
        width: 100% !important;
    }
    
    .recent-post-image img {
        width: 100% !important;
        height: 200px !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Newsletter form submission
    document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]').value;
        
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'toast position-fixed bottom-0 end-0 m-3';
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">Newsletter</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                Successfully subscribed with email: ${email}
            </div>
        `;
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        setTimeout(() => toast.remove(), 3000);
        this.reset();
    });
    
    // Load more posts functionality
    document.getElementById('load-more-posts').addEventListener('click', function() {
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
        this.disabled = true;
        
        setTimeout(() => {
            this.innerHTML = 'Load More Articles';
            this.disabled = false;
            
            // Show toast notification
            const toast = document.createElement('div');
            toast.className = 'toast position-fixed bottom-0 end-0 m-3';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="toast-header">
                    <strong class="me-auto">Blog</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    More articles will be available soon!
                </div>
            `;
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            setTimeout(() => toast.remove(), 3000);
        }, 1000);
    });
});
</script>
@endsection
