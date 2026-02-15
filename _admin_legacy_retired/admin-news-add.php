<?php
// Secure admin add news page
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login.php');
    exit;
}

$page_title = 'Add News Article - APS Dream Homes';
$meta_description = 'Admin interface for adding news articles.';
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
    <?php if ($additional_css) : ?>
    <link rel="stylesheet" href="<?php echo $additional_css; ?>">
    <?php endif; ?>
</head>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>

<section class="admin-news-add-section py-5 bg-light position-relative">
    <div class="container position-relative" style="z-index:3;">
        <div class="section-header text-center mb-5">
            <h2 class="fw-bold">Add News Article</h2>
            <p class="mb-4">This form is a demo. To enable saving, connect to a database.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form method="post" action="/admin/admin-news-action.php" autocomplete="off" class="bg-white p-5 rounded-4 shadow-sm needs-validation" novalidate>
                    <div class="form-floating mb-3 position-relative">
                        <input type="text" class="form-control" id="newsTitle" name="title" maxlength="150" required placeholder="Title">
                        <label for="newsTitle"><i class="fa fa-heading"></i> Title</label>
                        <div class="invalid-feedback">Please enter the news title.</div>
                    </div>
                    <div class="form-floating mb-3 position-relative">
                        <input type="date" class="form-control" id="newsDate" name="date" required placeholder="Date" value="<?php echo date('Y-m-d'); ?>">
                        <label for="newsDate"><i class="fa fa-calendar"></i> Date</label>
                        <div class="invalid-feedback">Please select a date.</div>
                    </div>
                    <div class="form-floating mb-3 position-relative">
                        <textarea class="form-control" id="newsSummary" name="summary" rows="2" maxlength="300" required placeholder="Summary" style="height: 70px"></textarea>
                        <label for="newsSummary"><i class="fa fa-align-left"></i> Summary</label>
                        <div class="invalid-feedback">Please enter a summary.</div>
                    </div>
                    <div class="form-floating mb-3 position-relative">
                        <input type="text" class="form-control" id="newsImage" name="image" maxlength="255" placeholder="/assets/images/news/example.jpg">
                        <label for="newsImage"><i class="fa fa-image"></i> Image URL</label>
                    </div>
                    <div class="form-floating mb-3 position-relative">
                        <textarea class="form-control" id="newsContent" name="content" rows="6" maxlength="4000" required placeholder="Content (HTML allowed)" style="height: 150px"></textarea>
                        <label for="newsContent"><i class="fa fa-file-alt"></i> Content (HTML allowed)</label>
                        <div class="invalid-feedback">Please enter the content.</div>
                    </div>
                    <input type="hidden" name="action" value="add">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill"><i class="fa fa-plus"></i> Save News</button>
                    </div>
                    <div class="alert alert-info mt-4">
                        <strong>Note:</strong> This form now saves news to the database. All fields are required.
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php 
if ($stmt->execute()) {
    header("Location: admin-news.php?msg=".urlencode('News added successfully.'));
    exit();
} else {
    echo "Error: " . htmlspecialchars($stmt->error);
}
?>

<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>

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

</body>
</html>
