<?php
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = "Invalid CSRF token.";
    } else {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $target_dir = "../uploads/gallery/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $filename = \App\Helpers\SecurityHelper::generateRandomString(16, false) . '.' . $imageFileType;
        $target_file = $target_dir . $filename;
        if (isset($_FILES["image"])) {
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $sql = "INSERT INTO gallery (title, description, category, image_path, created_at) VALUES (?, ?, ?, ?, NOW())";
                    $image_path = "uploads/gallery/" . $filename;

                    if ($db->execute($sql, [$title, $description, $category, $image_path])) {
                        $success_message = "Image uploaded successfully!";
                    } else {
                        $error_message = "Error saving to database.";
                    }
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error_message = "File is not an image.";
            }
        }
    }
}
// Handle image deletion
if (isset($_GET['delete'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        $error_message = "Invalid CSRF token.";
    } else {
        $id = intval($_GET['delete']);
        $img_data = $db->fetchOne("SELECT image_path FROM gallery WHERE id = :id", ['id' => $id]);
        $image_path = $img_data['image_path'] ?? null;

        if ($image_path && file_exists("../" . $image_path)) {
            unlink("../" . $image_path);
        }

        if ($db->execute("DELETE FROM gallery WHERE id = :id", ['id' => $id])) {
            $success_message = "Image deleted successfully!";
        } else {
            $error_message = "Error deleting image from database.";
        }
    }
}
// Fetch gallery images
$images = $db->fetchAll("SELECT * FROM gallery ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Gallery Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .main-content { margin-left: 220px; padding: 2rem 1rem; }
        .gallery-img { max-width: 120px; max-height: 90px; object-fit: cover; border-radius: 6px; }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/admin_sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Gallery Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="fas fa-upload me-1"></i> Upload Image</button>
    </div>
    <?php if (!empty($success_message)) echo '<div class="alert alert-success">' . h($success_message) . '</div>'; ?>
    <?php if (!empty($error_message)) echo '<div class="alert alert-danger">' . h($error_message) . '</div>'; ?>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($images) || !is_array($images)): ?>
                            <tr><td colspan="5" class="text-center">No images found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($images as $img): ?>
                                <tr>
                                    <td><?php echo h($img['id']); ?></td>
                                    <td><?php echo h($img['title']); ?></td>
                                    <td><?php echo h($img['category']); ?></td>
                                    <td><img src="../<?php echo h($img['image_path']); ?>" class="gallery-img" alt=""></td>
                                    <td>
                                        <a href="?delete=<?php echo h($img['id']); ?>&csrf_token=<?php echo h(generateCSRFToken()); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this image?');"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadModalLabel">Upload Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php echo getCsrfField(); ?>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" class="form-control" id="category" name="category">
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Select Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        </div>
                        <input type="hidden" name="upload" value="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
