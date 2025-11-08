<?php
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/../includes/db_config.php';
initAdminSession();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
$conn = getDbConnection();
// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Invalid request token";
    } else {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $target_dir = "../uploads/gallery/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $filename = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $filename;
        if (isset($_FILES["image"])) {
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $sql = "INSERT INTO gallery (title, description, category, image_path, created_at) VALUES (?, ?, ?, ?, NOW())";
                    $stmt = $conn->prepare($sql);
                    $image_path = "uploads/gallery/" . $filename;
                    $stmt->bind_param("ssss", $title, $description, $category, $image_path);
                    if ($stmt->execute()) {
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
    $id = intval($_GET['delete']);
    $sql = "SELECT image_path FROM gallery WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($image_path);
    $stmt->fetch();
    $stmt->close();
    if ($image_path && file_exists("../" . $image_path)) {
        unlink("../" . $image_path);
    }
    $sql = "DELETE FROM gallery WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success_message = "Image deleted successfully!";
    } else {
        $error_message = "Error deleting image from database.";
    }
}
// Fetch gallery images
$images = [];
$stmt = $conn->prepare("SELECT * FROM gallery ORDER BY created_at DESC");
if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    $result->close();
}
$stmt->close();
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
    <?php if (!empty($success_message)) echo '<div class="alert alert-success">' . $success_message . '</div>'; ?>
    <?php if (!empty($error_message)) echo '<div class="alert alert-danger">' . $error_message . '</div>'; ?>
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
                            <?php foreach (is_array($images) ? $images : [] as $img): ?>
                                <tr>
                                    <td><?php echo $img['id']; ?></td>
                                    <td><?php echo htmlspecialchars($img['title']); ?></td>
                                    <td><?php echo htmlspecialchars($img['category']); ?></td>
                                    <td><img src="../<?php echo $img['image_path']; ?>" class="gallery-img" alt=""></td>
                                    <td>
                                        <a href="?delete=<?php echo $img['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this image?');"><i class="fas fa-trash-alt"></i></a>
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
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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