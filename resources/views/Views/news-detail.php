<?php
// news-detail.php: Show details for a single news item based on ID
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

// Get news ID from query parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$news_items = get_latest_news(100, true); // Get all news with HTML content
$news = null;
if ($id > 0 && isset($news_items[$id - 1])) {
    $news = $news_items[$id - 1];
}

$page_title = $news ? $news['title'] . ' - APS Dream Homes News' : 'News Not Found - APS Dream Homes';
$meta_description = $news ? $news['summary'] : 'News article not found.';
$additional_css = '';
$additional_js = '';
require_once __DIR__ . '/includes/templates/dynamic_header.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'APS Dream Homes - News'; ?></title>
    <meta name="description" content="<?php echo isset($meta_description) ? htmlspecialchars($meta_description) : 'Latest news from APS Dream Homes.'; ?>">
    <?php if ($news): ?>
    <!-- Structured Data for SEO -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "NewsArticle",
      "headline": "<?php echo htmlspecialchars($news['title']); ?>",
      "datePublished": "<?php echo date('c', strtotime($news['date'])); ?>",
      "image": ["<?php echo htmlspecialchars($news['image']); ?>"],
      "description": "<?php echo htmlspecialchars($news['summary']); ?>",
      "articleBody": "<?php echo htmlspecialchars($news['content']); ?>",
      "author": {
        "@type": "Organization",
        "name": "APS Dream Homes"
      },
      "publisher": {
        "@type": "Organization",
        "name": "APS Dream Homes",
        "logo": {
          "@type": "ImageObject",
          "url": "/assets/images/logo/aps1.png"
        }
      }
    }
    </script>
    <?php endif; ?>
</head>

<section class="news-detail-section py-5 bg-light position-relative">
    <div class="container position-relative" style="z-index:3;">
        <?php if ($news): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="news-detail-card bg-white rounded-4 shadow-sm p-5">
                    <div class="mb-4">
                        <img src="<?php echo htmlspecialchars($news['image']); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>" class="img-fluid rounded-3 w-100" style="max-height:320px;object-fit:cover;" loading="lazy">
                    </div>
                    <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($news['title']); ?></h1>
                    <p class="small text-secondary mb-4"><?php echo date('F j, Y', strtotime($news['date'])); ?></p>
                    <p class="lead"><?php echo htmlspecialchars($news['summary']); ?></p>
                    <div class="news-full-content mt-4" style="font-size:1.1rem;">
                        <?php echo $news['content']; ?>
                    </div>
                    <a href="/news.php" class="btn btn-outline-primary mt-4" aria-label="Back to all news">&larr; Back to All News</a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="alert alert-danger p-5 text-center rounded-4 shadow-sm">
                    <h2 class="mb-3">News Not Found</h2>
                    <p>The news article you are looking for does not exist or has been removed.</p>
                    <a href="/news.php" class="btn btn-outline-primary mt-3" aria-label="Back to all news">&larr; Back to All News</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/templates/dynamic_footer.php'; ?>
