<?php
// Admin interface for gallery image management

// Include necessary files and initialize database connection
include_once 'config.php';
include_once 'admin-functions.php';

// Check if user is logged in as admin
if (!is_admin_logged_in()) {
    header('Location: login.php');
    exit();
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
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
            $filename = uniqid() . '.' . $imageFileType;
            $target_file = $target_dir . $filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Save to database
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

// Handle image deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get image path before deletion
    $sql = "SELECT image_path FROM gallery WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $image_path = "../" . $row['image_path'];
        // Delete from database
        $sql = "DELETE FROM gallery WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
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

// Fetch all images
$images = [];
$sql = "SELECT * FROM gallery ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $images[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'path' => $row['image_path']
        ];
    }
}
$limit = 10; // Number of images per page
$images = getGalleryImages();
$start = isset($_GET['page']) ? $_GET['page'] * $limit : 0;
$images = getGalleryImages();
$start = isset($_GET['page']) ? $_GET['page'] * $limit : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Admin</title>
    <link rel="stylesheet" href="../css/gallery.css">
</head>
<body>
    <h1>Gallery Admin Panel</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="file" name="image" required>
        <input type="text" name="title" placeholder="Title" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <button type="submit">Upload Image</button>
    </form>

    <h2>Gallery Images</h2>
    <ul>
        <?php foreach (array_slice($images, $start, $limit) as $image): ?>
            <li>
                <img src="<?php echo $image['path']; ?>" alt="<?php echo $image['title']; ?>">
                <h3><?php echo $image['title']; ?></h3>
                <p><?php echo $image['description']; ?></p>
                <a href="?delete=<?php echo $image['id']; ?>">Delete</a>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="pagination">
        <?php for ($i = 0; $i < ceil(count($images) / $limit); $i++): ?>
            <a href="?page=<?php echo $i; ?>">Page <?php echo $i + 1; ?></a>
        <?php endfor; ?>
    </div>
</body>
</html>