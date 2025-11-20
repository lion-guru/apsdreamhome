<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Initialize variables
$page_title = "Downloads - APS Dream Homes";
$meta_description = "Download brochures, price lists, floor plans, and other resources from APS Dream Homes.";
$meta_keywords = "property brochures, price lists, floor plans, real estate documents, APS Dream Homes downloads";

// Additional CSS
$additional_css = '
<link rel="stylesheet" href="' . get_asset_url("css/common.css") . '">
<link rel="stylesheet" href="' . get_asset_url("css/downloads.css") . '">
';

// Additional JS
$additional_js = '
<script src="' . get_asset_url("js/common.js") . '"></script>
<script src="' . get_asset_url("js/downloads.js") . '"></script>
';

// Fetch download categories
    $categories = [];
    $stmt = $conn->prepare("SELECT DISTINCT category FROM downloads WHERE status = 'active' ORDER BY category");
    $stmt->execute();
    $categories_result = $stmt->get_result();
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    $stmt->close();

// Fetch downloads with pagination
$items_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

$current_category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : 'all';
$where_clause = $current_category !== 'all' ? "AND category = '$current_category'" : "";

    $count_query = "SELECT COUNT(*) as total FROM downloads WHERE status = 'active' ";
    if ($current_category !== 'all') {
        $count_query .= "AND category = ?";
    }
    $stmt = $conn->prepare($count_query);
    if ($current_category !== 'all') {
        $stmt->bind_param("s", $current_category);
    }
    $stmt->execute();
    $count_result = $stmt->get_result();
    $total_items = $count_result->fetch_assoc()['total'];
    $stmt->close();
$total_pages = ceil($total_items / $items_per_page);

    $downloads_query = "SELECT * FROM downloads WHERE status = 'active' $where_clause ORDER BY priority DESC, created_at DESC LIMIT ?, ?";
    $stmt = $conn->prepare($downloads_query);
    if ($current_category !== 'all') {
        $stmt->bind_param("sii", $current_category, $offset, $items_per_page);
    } else {
        $stmt->bind_param("ii", $offset, $items_per_page);
    }
    $stmt->execute();
    $downloads_result = $stmt->get_result();

// Include header
require_once __DIR__ . '/includes/templates/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">Downloads</h1>
                <p class="lead text-muted">Access our collection of brochures, price lists, floor plans, and other resources.</p>
                <div class="mt-4">
                    <form class="search-form" id="downloadSearch">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search downloads..." id="searchInput">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?= get_asset_url('images/downloads-hero.jpg') ?>" alt="Downloads Hero" class="img-fluid rounded-3 shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active">Downloads</li>
        </ol>
    </div>
</nav>

<!-- Downloads Content -->
<main id="main-content" class="py-5">
    <div class="container">
        <!-- Category Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="downloads-filter text-center">
                    <button class="btn <?= $current_category === 'all' ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2" 
                            onclick="window.location.href='downloads'">
                        All Resources
                    </button>
                    <?php foreach ($categories as $category): ?>
                    <button class="btn <?= $current_category === $category ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2"
                            onclick="window.location.href='downloads?category=<?= urlencode($category) ?>'">
                        <?= htmlspecialchars(ucwords($category)) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Downloads Grid -->
        <div class="row g-4">
            <?php while ($download = mysqli_fetch_assoc($downloads_result)): ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="download-card">
                    <div class="download-icon">
                        <?php
                        $icon_class = 'fa-file';
                        switch (strtolower(pathinfo($download['file_path'], PATHINFO_EXTENSION))) {
                            case 'pdf':
                                $icon_class = 'fa-file-pdf';
                                break;
                            case 'doc':
                            case 'docx':
                                $icon_class = 'fa-file-word';
                                break;
                            case 'xls':
                            case 'xlsx':
                                $icon_class = 'fa-file-excel';
                                break;
                            case 'jpg':
                            case 'jpeg':
                            case 'png':
                            case 'gif':
                                $icon_class = 'fa-file-image';
                                break;
                        }
                        ?>
                        <i class="far <?= $icon_class ?>"></i>
                    </div>
                    <div class="download-content">
                        <h3 class="download-title"><?= htmlspecialchars($download['title']) ?></h3>
                        <p class="download-description"><?= htmlspecialchars($download['description']) ?></p>
                        <div class="download-meta">
                            <span class="file-size">
                                <i class="fas fa-weight-hanging me-1"></i>
                                <?= format_file_size($download['file_size']) ?>
                            </span>
                            <span class="download-count">
                                <i class="fas fa-download me-1"></i>
                                <?= number_format($download['download_count']) ?> downloads
                            </span>
                        </div>
                        <div class="download-actions">
                            <?php if ($download['requires_login'] && !is_logged_in()): ?>
                            <button class="btn btn-primary w-100" onclick="window.location.href='/login'">
                                <i class="fas fa-lock me-2"></i>Login to Download
                            </button>
                            <?php else: ?>
                            <a href="/download/<?= $download['id'] ?>" class="btn btn-primary w-100">
                                <i class="fas fa-download me-2"></i>Download Now
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($download['preview_url'])): ?>
                            <a href="<?= htmlspecialchars($download['preview_url']) ?>" 
                               class="btn btn-outline-primary w-100 mt-2" 
                               target="_blank">
                                <i class="fas fa-eye me-2"></i>Preview
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if (mysqli_num_rows($downloads_result) === 0): ?>
        <div class="text-center py-5">
            <h3>No downloads found</h3>
            <p class="text-muted">Please try selecting a different category or check back later.</p>
        </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav class="mt-5">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= $current_category !== 'all' ? '&category=' . urlencode($current_category) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <!-- Request Resource CTA -->
        <div class="request-resource-cta text-center mt-5 py-5 bg-light rounded-3">
            <h3>Can't Find What You Need?</h3>
            <p class="text-muted mb-4">Contact us to request specific resources or documents</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="/contact" class="btn btn-primary">Contact Us</a>
                <a href="tel:<?= CONTACT_PHONE ?>" class="btn btn-outline-primary">
                    <i class="fas fa-phone me-2"></i>Call Now
                </a>
            </div>
        </div>
    </div>
</main>

<?php
// Include footer
require_once __DIR__ . '/includes/templates/footer.php';

// Helper function to format file size
function format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 1) . ' ' . $units[$pow];
}
?>