<?php
// news.php: Display all news items dynamically
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

// Set page specific variables
$page_title = "Latest News & Updates - APS Dream Homes";
$meta_description = "Stay updated with the latest news, events, and announcements from APS Dream Homes, your trusted real estate partner in Uttar Pradesh.";
$additional_css = '<link rel="stylesheet" href="' . get_asset_url('css/home.css', 'assets') . '">';

require_once __DIR__ . '/includes/templates/dynamic_header.php';
?>

<section class="news-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 mb-5">
    <div class="container">
        <h1 class="display-5 fw-bold mb-2">Latest News & Updates</h1>
        <p class="lead mb-0">Stay updated with the latest happenings at APS Dream Homes</p>
    </div>
</section>

<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <form method="get" action="" class="d-flex align-items-center">
                    <input type="text" name="q" class="form-control me-2" placeholder="Search news..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button type="submit" class="btn btn-outline-primary">Search</button>
                </form>
            </div>
        </div>
        <div class="row">
            <?php
            // Pagination and search setup
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $per_page = 6;
            $offset = ($page - 1) * $per_page;
            $q = isset($_GET['q']) ? trim($_GET['q']) : '';

            // Build query for count and news fetch
            $where = '';
            $params = [];
            $types = '';
            if ($q !== '') {
                $where = "WHERE title LIKE ? OR summary LIKE ?";
                $params[] = "%$q%";
                $params[] = "%$q%";
                $types = 'ss';
            }
            // Count total news
            $total_news = 0;
            if (isset($conn) && $conn) {
                $sql = "SELECT COUNT(*) AS cnt FROM news $where";
                if ($stmt = $conn->prepare($sql)) {
                    if ($q !== '') {
                        $stmt->bind_param($types, ...$params);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $total_news = (int)$row['cnt'];
                    }
                    $stmt->close();
                }
            }
            $total_pages = $total_news > 0 ? ceil($total_news / $per_page) : 1;

            // Fetch paginated news with search
            $news_items = [];
            if (isset($conn) && $conn) {
                $sql = "SELECT id, title, date, summary, image, content FROM news $where ORDER BY date DESC, id DESC LIMIT ? OFFSET ?";
                if ($stmt = $conn->prepare($sql)) {
                    if ($q !== '') {
                        // PHP does not allow ...$params after other arguments, so merge all params first
                        $all_params = array_merge($params, [$per_page, $offset]);
                        $all_types = $types . 'ii';
                        $stmt->bind_param($all_types, ...$all_params);
                    } else {
                        $stmt->bind_param('ii', $per_page, $offset);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $news_items[] = [
                            'title' => $row['title'],
                            'date' => $row['date'],
                            'summary' => $row['summary'],
                            'url' => '/news-detail.php?id=' . $row['id'],
                            'image' => $row['image'],
                            'content' => strip_tags($row['content'])
                        ];
                    }
                    $stmt->close();
                }
            }
            foreach ($news_items as $news):
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="news-card shadow-lg rounded-4 bg-white h-100 p-4">
                    <div class="news-image mb-3 rounded-4 overflow-hidden">
                        <img src="<?php echo get_asset_url($news['image'], 'images'); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>" class="img-fluid w-100" style="height:180px; object-fit:cover;">
                    </div>
                    <h4 class="fw-bold text-primary mb-2"><?php echo htmlspecialchars($news['title']); ?></h4>
                    <p class="text-secondary mb-2 small"><i class="fas fa-calendar-alt me-2"></i> <?php echo date('d M Y', strtotime($news['date'])); ?></p>
                    <p class="mb-3"> <?php echo htmlspecialchars(mb_strimwidth($news['content'], 0, 120, '...')); ?> </p>
                    <a href="news-detail.php?nid=<?php echo $news['id']; ?>" class="btn btn-outline-primary rounded-pill">Read More</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if ($total_pages > 1): ?>
        <nav aria-label="News pagination">
            <ul class="pagination justify-content-center mt-4">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item<?php if ($p == $page) echo ' active'; ?>">
                        <a class="page-link" href="?page=<?php echo $p; ?><?php if (isset($_GET['q'])) echo '&q=' . urlencode($_GET['q']); ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</section>

<?php require_once(__DIR__ . '/includes/templates/new_footer.php'); ?>
</body>
</html>
