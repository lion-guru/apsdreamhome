<?php
// Secure admin edit news page
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login.php');
    exit;
}

// Get news ID from query parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$news_items = get_latest_news(100, true);
$news = ($id > 0 && isset($news_items[$id - 1])) ? $news_items[$id - 1] : null;

$page_title = 'Edit News Article - APS Dream Homes';
$meta_description = 'Admin interface for editing news articles.';
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php echo $additional_css; ?>
</head>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>

<section class="admin-news-edit-section py-5 bg-light position-relative">
    <div class="container position-relative" style="z-index:3;">
        <div class="section-header text-center mb-5">
            <h2 class="fw-bold">Edit News Article</h2>
            <p class="mb-4">This form is a demo. To enable saving, connect to a database.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($news): ?>
                <form method="post" action="/admin/admin-news-action.php" autocomplete="off" class="bg-white p-5 rounded-4 shadow-sm needs-validation" novalidate>
                    <div class="form-floating mb-3 position-relative">
                        <input type="text" class="form-control" id="newsTitle" name="title" maxlength="150" required placeholder="Title" value="<?php echo htmlspecialchars($news['title']); ?>">
                        <label for="newsTitle"><i class="fa fa-heading"></i> Title</label>
                        <div class="invalid-feedback">Please enter the news title.</div>
                    </div>
                    <div class="form-floating mb-3 position-relative">
                        <input type="date" class="form-control" id="newsDate" name="date" required placeholder="Date" value="<?php echo htmlspecialchars($news['date']); ?>">
                        <label for="newsDate"><i class="fa fa-calendar"></i> Date</label>
                        <div class="invalid-feedback">Please select a date.</div>
                    </div>
                    <div class="form-floating mb-3 position-relative">
                        <textarea class="form-control" id="newsSummary" name="summary" rows="2" maxlength="300" required placeholder="Summary" style="height: 70px"><?php echo htmlspecialchars($news['summary']); ?></textarea>
                        <label for="newsSummary"><i class="fa fa-align-left"></i> Summary</label>
                        <div class="invalid-feedback">Please enter a summary.</div>
                    </div>
                    <div class="form-floating mb-3 position-relative">
                        <input type="text" class="form-control" id="newsImage" name="image" maxlength="255" placeholder="/assets/images/news/example.jpg" value="<?php echo htmlspecialchars($news['image']); ?>">
                        <label for="newsImage"><i class="fa fa-image"></i> Image URL</label>
                    </div>
                    <div class="form-floating mb-3 position-relative">
                        <textarea class="form-control" id="newsContent" name="content" rows="6" maxlength="4000" required placeholder="Content (HTML allowed)" style="height: 150px"><?php echo htmlspecialchars($news['content']); ?></textarea>
                        <label for="newsContent"><i class="fa fa-file-alt"></i> Content (HTML allowed)</label>
                        <div class="invalid-feedback">Please enter the content.</div>
                    </div>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="hidden" name="action" value="edit">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill"><i class="fa fa-save"></i> Save Changes</button>
                    </div>
                    <div class="alert alert-info mt-4">
                        <strong>Note:</strong> This form now updates news in the database. All fields are required.
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-danger p-5 text-center rounded-4 shadow-sm">
                    <h2 class="mb-3">News Not Found</h2>
                    <p>The news article you are trying to edit does not exist.</p>
                    <a href="/admin/admin-news.php" class="btn btn-outline-primary mt-3">&larr; Back to News Admin</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>

<?php 
if ($stmt->execute()) {
    header("Location: admin-news.php?msg=".urlencode('News updated successfully.'));
    exit();
} else {
    echo "Error: " . htmlspecialchars($stmt->error);
}
?>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
