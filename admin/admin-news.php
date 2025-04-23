<?php
// Secure admin news management page
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login.php');
    exit;
}

$page_title = 'Manage News - APS Dream Homes';
$meta_description = 'Admin interface for managing news articles.';
$additional_css = '';
$additional_js = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $meta_description; ?>">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <?php if ($additional_css !== ''): ?>
        <link rel="stylesheet" href="<?php echo $additional_css; ?>">
    <?php endif; ?>
</head>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success text-center">
        <?php echo htmlspecialchars($_GET['msg']); ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger text-center">
        <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>

<section class="admin-news-section py-5 bg-light position-relative">
    <div class="container position-relative" style="z-index:3;">
        <div class="section-header text-center mb-5">
            <h2 class="fw-bold">Manage News Articles</h2>
            <p class="mb-4">This simple admin panel lists all news articles. (Editing, deleting, and adding news requires database integration.)</p>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="get" action="" class="d-flex align-items-center">
                    <input type="text" name="q" class="form-control me-2" placeholder="Search news..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button type="submit" class="btn btn-outline-primary">Search</button>
                </form>
            </div>
        </div>
        <?php
        // Pagination and search setup
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per_page = 10;
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
                        'url' => '/admin/admin-news-edit.php?id=' . $row['id'],
                        'image' => $row['image'],
                        'content' => strip_tags($row['content'])
                    ];
                }
                $stmt->close();
            }
        }
        $i = $offset + 1;
        ?>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="table-responsive">
                    <div class="mb-3 text-end">
                        <a href="/admin/admin-news-add.php" class="btn btn-success">+ Add News Article</a>
                    </div>
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Summary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($news_items as $news): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($news['title']); ?></td>
                                <td><?php echo htmlspecialchars($news['date']); ?></td>
                                <td><?php echo htmlspecialchars($news['summary']); ?></td>
                                <td>
                                    <a href="<?php echo $news['url']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <form method="post" action="/admin/admin-news-action.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $i-1; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this news article?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="News pagination">
                        <ul class="pagination justify-content-center mt-4">
                            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                <li class="page-item<?php if ($p == $page) echo ' active'; ?>">
                                    <a class="page-link" href="?page=<?php echo $p; ?><?php if (isset($_GET['q'])) echo '&q=' . $_GET['q']; ?>"><?php echo $p; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
