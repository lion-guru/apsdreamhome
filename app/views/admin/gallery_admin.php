<?php
// Admin interface for gallery image management

// Include necessary files and initialize database connection
require_once __DIR__ . '/core/init.php';

// Check if user is logged in as admin
if (!isAuthenticated() || !isAdmin()) {
    header('Location: index.php');
    exit();
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    if (!validateCsrfToken()) {
        $error_message = "Invalid security token. Please try again.";
    } else {
        $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];

    $target_dir = "../uploads/gallery/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is actual image
    if(isset($_FILES["image"])) {
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            // Generate unique filename
            $filename = \App\Helpers\SecurityHelper::generateRandomString(16, false) . '.' . $imageFileType;
            $target_file = $target_dir . $filename;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Save to database
                $db = \App\Core\App::database();
                $image_path = "uploads/gallery/" . $filename;

                $gallery_id = $db->insert('gallery', [
                    'title' => $title,
                    'description' => $description,
                    'category' => $category,
                    'image_path' => $image_path,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                if ($gallery_id) {
                    $success_message = "Image uploaded successfully!";
                    // Google Drive upload and Slack notification
                    require_once __DIR__ . '/includes/integration_helpers.php';
                    upload_to_google_drive_and_save_id($image_path, 'gallery', 'id', $gallery_id, 'drive_file_id');

                    $drive_result = $db->fetch("SELECT drive_file_id FROM gallery WHERE id = ?", [$gallery_id]);
                    $driveId = $drive_result['drive_file_id'] ?? null;

                    $driveLink = $driveId ? "https://drive.google.com/file/d/$driveId/view" : '';
                    $slackMsg = "🖼️ *New Gallery Image Uploaded*\n" .
                        "Title: $title\n" .
                        ($driveLink ? "[View on Google Drive]($driveLink)" : '');
                    send_slack_notification($slackMsg);
                    send_telegram_notification($slackMsg);
                    require_once __DIR__ . '/includes/upload_audit_log.php';
                    $slack_status = 'sent';
                    $telegram_status = 'sent';
                    log_upload_event('gallery_image', $gallery_id, 'gallery', $filename, $driveId, $title, $slack_status, $telegram_status);
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error_message = "Invalid security token. Please try again.";
    } else {
        $id = intval($_POST['delete_id']);
        $db = \App\Core\App::database();

        // Get image path before deletion
        $row = $db->fetch("SELECT image_path FROM gallery WHERE id = ?", [$id]);

        if ($row) {
            $image_path = "../" . $row['image_path'];
            // Delete from database
            if ($db->delete('gallery', 'id = ?', [$id])) {
                // Delete file from server
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
                $success_message = "Image deleted successfully!";
            } else {
                $error_message = "Error deleting image.";
            }
        }
    }
}

// Fetch all images
$db = \App\Core\App::database();
$sql = "SELECT * FROM gallery ORDER BY created_at DESC";
$gallery_rows = $db->fetchAll($sql);
$images = [];

foreach ($gallery_rows as $row) {
    $images[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'description' => $row['description'],
        'path' => $row['image_path'],
        'drive_file_id' => $row['drive_file_id'] ?? null
    ];
}

$limit = 10; // Number of images per page
$start = isset($_GET['page']) ? $_GET['page'] * $limit : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Admin</title>
    <link rel="stylesheet" href="../css/gallery.css">
    <link rel="stylesheet" href="../assets/vendor/font-awesome/css/all.min.css">
</head>
<body>
    <h1>Gallery Admin Panel</h1>
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo h($success_message); ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo h($error_message); ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <?php echo getCsrfField(); ?>
        <input type="file" name="image" required>
        <input type="text" name="title" placeholder="Title" required>
        <input type="text" name="category" placeholder="Category" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <button type="submit" name="upload">Upload Image</button>
    </form>

    <h2>Gallery Images</h2>
    <ul>
        <?php foreach (array_slice($images, $start, $limit) as $image): ?>
            <li>
                <img src="<?php echo h($image['path']); ?>" alt="<?php echo h($image['title']); ?>" style="max-width: 200px;">
                <h3>
                    <?php echo h($image['title']); ?>
                    <?php if (!empty($image['drive_file_id'])): ?>
                        <a href="https://drive.google.com/file/d/<?php echo h($image['drive_file_id']); ?>/view" target="_blank" title="View on Google Drive">
                            <i class="fab fa-google-drive text-success ms-2"></i>
                        </a>
                    <?php endif; ?>
                </h3>
                <p><?php echo h($image['description']); ?></p>
                <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this image?');">
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="delete_id" value="<?php echo h($image['id']); ?>">
                    <button type="submit" name="delete">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="pagination">
        <?php for ($i = 0; $i < ceil(count($images) / $limit); $i++): ?>
            <a href="?page=<?php echo h($i); ?>">Page <?php echo h($i + 1); ?></a>
        <?php endfor; ?>
    </div>
</body>
</html>
