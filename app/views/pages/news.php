<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Initialize variables
$page_title = "News & Updates - APS Dream Homes";
$meta_description = "Stay updated with the latest real estate news, market trends, and company updates from APS Dream Homes.";
$meta_keywords = "real estate news, property market updates, APS Dream Homes news, real estate trends, property investment news";

// Additional CSS
$additional_css = '
<link rel="stylesheet" href="' . get_asset_url("css/common.css") . '">
<link rel="stylesheet" href="' . get_asset_url("css/news.css") . '">
';

// Additional JS
$additional_js = '
<script src="' . get_asset_url("js/common.js") . '"></script>
<script src="' . get_asset_url("js/news.js") . '"></script>
';

// Fetch news categories
    $categories = [];
    $stmt = $conn->prepare("SELECT DISTINCT category FROM news WHERE status = 'published' ORDER BY category");
    $stmt->execute();
    $categories_result = $stmt->get_result();
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    $stmt->close();

// Fetch news with pagination
$items_per_page = 9;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

$current_category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : 'all';
$where_clause = $current_category !== 'all' ? "AND category = '$current_category'" : "";

    $count_query = "SELECT COUNT(*) as total FROM news WHERE status = 'published' ";
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

    $news_query = "SELECT n.*, u.name as author_name, u.profile_image as author_image 
                   FROM news n 
                   LEFT JOIN users u ON n.author_id = u.id 
                   WHERE n.status = 'published' $where_clause 
                   ORDER BY n.created_at DESC 
                   LIMIT ?, ?";
    $stmt = $conn->prepare($news_query);
    if ($current_category !== 'all') {
        $stmt->bind_param("sii", $current_category, $offset, $items_per_page);
    } else {
        $stmt->bind_param("ii", $offset, $items_per_page);
    }
    $stmt->execute();
    $news_result = $stmt->get_result();

// Include header
require_once __DIR__ . '/includes/templates/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">News & Updates</h1>
                <p class="lead text-muted">Stay informed with the latest real estate news and market trends.</p>
                <div class="mt-4">
                    <form class="search-form" id="newsSearch">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search news..." id="searchInput">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?= get_asset_url('images/news-hero.jpg') ?>" alt="News Hero" class="img-fluid rounded-3 shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active">News</li>
        </ol>
    </div>
</nav>

<!-- News Content -->
<main id="main-content" class="py-5">
    <div class="container">
        <!-- Category Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="news-filter text-center">
                    <button class="btn <?= $current_category === 'all' ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2" 
                            onclick="window.location.href='news'">
                        All News
                    </button>
                    <?php foreach ($categories as $category): ?>
                    <button class="btn <?= $current_category === $category ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2"
                            onclick="window.location.href='news?category=<?= urlencode($category) ?>'">
                        <?= htmlspecialchars(ucwords($category)) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- News Grid -->
        <div class="row g-4">
            <?php while ($news = mysqli_fetch_assoc($news_result)): ?>
            <div class="col-lg-4 col-md-6">
                <article class="news-card">
                    <div class="news-image">
                        <img src="<?= get_asset_url($news['image']) ?>" 
                             alt="<?= htmlspecialchars($news['title']) ?>" 
                             class="img-fluid">
                        <div class="news-category">
                            <?= htmlspecialchars(ucwords($news['category'])) ?>
                        </div>
                    </div>
                    <div class="news-content">
                        <h3 class="news-title">
                            <a href="/news/<?= $news['slug'] ?>">
                                <?= htmlspecialchars($news['title']) ?>
                            </a>
                        </h3>
                        <div class="news-meta">
                            <span class="news-date">
                                <i class="far fa-calendar-alt me-1"></i>
                                <?= date('M d, Y', strtotime($news['created_at'])) ?>
                            </span>
                            <?php if ($news['author_name']): ?>
                            <span class="news-author">
                                <i class="far fa-user me-1"></i>
                                <?= htmlspecialchars($news['author_name']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <p class="news-excerpt">
                            <?= htmlspecialchars(substr(strip_tags($news['excerpt']), 0, 150)) ?>...
                        </p>
                        <a href="/news/<?= $news['slug'] ?>" class="btn btn-outline-primary">
                            Read More
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </article>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if (mysqli_num_rows($news_result) === 0): ?>
        <div class="text-center py-5">
            <h3>No news found</h3>
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

        <!-- Newsletter CTA -->
        <div class="newsletter-cta text-center mt-5 py-5 bg-light rounded-3">
            <h3>Stay Updated</h3>
            <p class="text-muted mb-4">Subscribe to our newsletter for the latest real estate news and market insights</p>
            <form class="newsletter-form" id="newsletterForm">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Enter your email" required>
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
// Include footer
require_once __DIR__ . '/includes/templates/footer.php';
?>