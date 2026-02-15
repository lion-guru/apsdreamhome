<?php
/**
 * APS Dream Home - Media Library Demo Page
 * Showcase media library integration with dynamic templates
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/media_integration.php';
require_once __DIR__ . '/../../includes/dynamic_templates.php';

// Initialize media integration
$mediaIntegration = new MediaLibraryIntegration($GLOBALS['conn'] ?? $GLOBALS['con'] ?? null);

// Get media statistics
$mediaStats = [];
$conn = $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;
if ($conn) {
    require_once 'includes/media_library_manager.php';
    $mediaManager = new MediaLibraryManager($conn);
    $mediaStats = $mediaManager->getMediaStats();
}

$pageTitle = "Media Library Demo - APS Dream Home";
$pageDescription = "Explore our comprehensive media library with dynamic integration";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    <meta name="description" content="<?= h($pageDescription) ?>">

    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/font-awesome.min.css" rel="stylesheet">

    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .media-item {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .media-item:hover {
            transform: translateY(-5px);
        }
        .media-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>

<!-- Dynamic Header -->
<?php
// Render Dynamic Header
renderDynamicHeader('main');
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">
            <i class="fas fa-images me-3"></i>Media Library Integration
        </h1>
        <p class="lead mb-4">Complete file management system with seamless dynamic template integration</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?= BASE_URL ?>admin/media_library.php" class="btn btn-light btn-lg">
                <i class="fas fa-cog me-2"></i>Admin Panel
            </a>
            <a href="<?= BASE_URL ?>dynamic_integration_guide.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-book me-2"></i>Documentation
            </a>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Media Library Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-file fa-2x text-primary mb-2"></i>
                <h3><?= number_format($mediaStats['total_files'] ?? 0) ?></h3>
                <p class="mb-0">Total Files</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-hdd fa-2x text-success mb-2"></i>
                <h3><?= number_format(($mediaStats['total_size'] ?? 0) / 1024 / 1024, 2) ?> MB</h3>
                <p class="mb-0">Total Size</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock fa-2x text-info mb-2"></i>
                <h3><?= $mediaStats['recent_uploads'] ?? 0 ?></h3>
                <p class="mb-0">Recent (7 days)</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-tags fa-2x text-warning mb-2"></i>
                <h3><?= count($mediaStats['by_category'] ?? []) ?></h3>
                <p class="mb-0">Categories</p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Key Features</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-upload fa-3x text-primary mb-3"></i>
                    <h4>Easy Upload</h4>
                    <p>Drag and drop interface with automatic file organization and validation</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-folder fa-3x text-success mb-3"></i>
                    <h4>Smart Categories</h4>
                    <p>Automatic categorization with flexible tagging system</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-search fa-3x text-info mb-3"></i>
                    <h4>Advanced Search</h4>
                    <p>Powerful search and filtering capabilities</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-palette fa-3x text-warning mb-3"></i>
                    <h4>Template Integration</h4>
                    <p>Seamless integration with dynamic templates</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-shield-alt fa-3x text-danger mb-3"></i>
                    <h4>Secure</h4>
                    <p>File validation and secure storage</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-mobile-alt fa-3x text-secondary mb-3"></i>
                    <h4>Responsive</h4>
                    <p>Mobile-friendly interface and galleries</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Integration Examples Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Integration Examples</h2>

        <div class="row">
            <div class="col-md-6">
                <div class="feature-card">
                    <h4><i class="fas fa-code me-2"></i>Basic Integration</h4>
                    <p>Simple one-line integration for media galleries:</p>
                    <div class="code-block">
                        &lt;?php getMediaGallery('property', 4, 8); ?&gt;
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-card">
                    <h4><i class="fas fa-users me-2"></i>Team Section</h4>
                    <p>Automatic team member display:</p>
                    <div class="code-block">
                        &lt;?php getTeamSection(); ?&gt;
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-card">
                    <h4><i class="fas fa-building me-2"></i>Property Showcase</h4>
                    <p>Dynamic property galleries:</p>
                    <div class="code-block">
                        &lt;?php getPropertyShowcase(); ?&gt;
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-card">
                    <h4><i class="fas fa-link me-2"></i>Direct URL Access</h4>
                    <p>Get media URL by ID:</p>
                    <div class="code-block">
                        &lt;?php $url = getMediaUrl($id); ?&gt;
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Live Demo Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Live Media Gallery Demo</h2>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" id="mediaTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                    <i class="fas fa-images me-2"></i>All Media
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="property-tab" data-bs-toggle="tab" data-bs-target="#property" type="button" role="tab">
                    <i class="fas fa-building me-2"></i>Properties
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="team-tab" data-bs-toggle="tab" data-bs-target="#team" type="button" role="tab">
                    <i class="fas fa-users me-2"></i>Team
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="project-tab" data-bs-toggle="tab" data-bs-target="#project" type="button" role="tab">
                    <i class="fas fa-project-diagram me-2"></i>Projects
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="mediaTabsContent">
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                <?php getMediaGallery(null, 4, 12); ?>
            </div>
            <div class="tab-pane fade" id="property" role="tabpanel">
                <?php getMediaGallery('property', 4, 8); ?>
            </div>
            <div class="tab-pane fade" id="team" role="tabpanel">
                <?php getMediaGallery('team', 4, 8); ?>
            </div>
            <div class="tab-pane fade" id="project" role="tabpanel">
                <?php getMediaGallery('project', 4, 8); ?>
            </div>
        </div>
    </div>
</section>

<!-- Team Section Demo -->
<section class="py-5 bg-light">
    <div class="container">
        <?php getTeamSection(); ?>
    </div>
</section>

<!-- Property Showcase Demo -->
<section class="py-5">
    <div class="container">
        <?php getPropertyShowcase(); ?>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-4">Ready to Enhance Your Website?</h2>
        <p class="lead mb-4">Start using the media library system today</p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="<?= BASE_URL ?>admin/media_library.php" class="btn btn-light btn-lg">
                <i class="fas fa-upload me-2"></i>Upload Media
            </a>
            <a href="<?= BASE_URL ?>deployment_package.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-rocket me-2"></i>View System
            </a>
        </div>
    </div>
</section>

<!-- Dynamic Footer -->
<?php
// Render Dynamic Footer
renderEnhancedDynamicFooter('main');
?>

<!-- Bootstrap JS -->
<script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>

<script>
// Add smooth scrolling and interactive features
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add hover effects to media items
    const mediaItems = document.querySelectorAll('.media-item');
    mediaItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });

        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-5px) scale(1)';
        });
    });

    // Lazy loading for images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
});
</script>

</body>
</html>
