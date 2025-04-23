<?php
session_start();
require("config.php");
require_once __DIR__ . '/../includes/log_admin_activity.php';

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Fetch project details
if (isset($_GET['id'])) {
    $project_id = $_GET['id'];
    $stmt = $con->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $project = $stmt->get_result()->fetch_assoc();
}

// Update project
$error = "";
$msg = "";
if (isset($_POST['updateproject'])) {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $pimage = $_FILES['pimage']['name'];
    $temp_name1 = $_FILES['pimage']['tmp_name'];
    $youtube_url = isset($_POST['youtube_url']) ? trim($_POST['youtube_url']) : '';
    $update_fields = "name = ?, location = ?, youtube_url = ?";
    $params = [$name, $location, $youtube_url];
    $types = "sss";
    if ($pimage) {
        if (!file_exists("upload")) { mkdir("upload", 0777, true); }
        move_uploaded_file($temp_name1, "upload/$pimage");
        $update_fields .= ", image = ?";
        $params[] = $pimage;
        $types .= "s";
    }
    $params[] = $project_id;
    $types .= "i";
    $sql = "UPDATE projects SET $update_fields WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        // Handle brochure upload
        if (!empty($_FILES['brochure']['name'])) {
            $brochure_name = time() . '_' . basename($_FILES['brochure']['name']);
            $brochure_path = 'uploads/brochures/' . $brochure_name;
            if (!file_exists('uploads/brochures')) { mkdir('uploads/brochures', 0777, true); }
            move_uploaded_file($_FILES['brochure']['tmp_name'], $brochure_path);
            $con->query("UPDATE projects SET brochure_path='$brochure_path' WHERE id=$project_id");
        }
        // Handle amenities upload
        if (!empty($_FILES['amenity_icons']['name'][0])) {
            foreach ($_FILES['amenity_icons']['name'] as $idx => $icon_name) {
                if ($icon_name) {
                    $icon_file = time() . '_' . $icon_name;
                    $icon_path = 'uploads/amenities/' . $icon_file;
                    if (!file_exists('uploads/amenities')) { mkdir('uploads/amenities', 0777, true); }
                    move_uploaded_file($_FILES['amenity_icons']['tmp_name'][$idx], $icon_path);
                    $label = isset($_POST['amenity_labels'][$idx]) ? $_POST['amenity_labels'][$idx] : '';
                    $con->query("INSERT INTO project_amenities (project_id, icon_path, label) VALUES ($project_id, '$icon_path', '" . $con->real_escape_string($label) . "')");
                }
            }
        }
        // Handle gallery upload
        if (!empty($_FILES['gallery_images']['name'][0])) {
            foreach ($_FILES['gallery_images']['name'] as $idx => $img_name) {
                if ($img_name) {
                    $img_file = time() . '_' . $img_name;
                    $img_path = 'uploads/gallery/' . $img_file;
                    if (!file_exists('uploads/gallery')) { mkdir('uploads/gallery', 0777, true); }
                    move_uploaded_file($_FILES['gallery_images']['tmp_name'][$idx], $img_path);
                    $con->query("INSERT INTO project_gallery (project_id, image_path) VALUES ($project_id, '$img_path')");
                }
            }
        }
        log_admin_activity('edit_project', 'Edited project ID: ' . $project_id);
        header("Location: project.php?msg=" . urlencode('Project Updated Successfully'));
        exit();
    } else {
        $error = "<p class='alert alert-warning'>* Error updating project: " . htmlspecialchars($stmt->error) . "</p>";
    }
}
?>

<?php include("../includes/templates/dynamic_header.php");?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Edit Project</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
</head>

<body>

    <div class="container">
        <h2>Edit Project</h2>
        <?php echo $error; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Project Name</label>
                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($project['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" class="form-control" name="location" value="<?php echo htmlspecialchars($project['location']); ?>" required>
            </div>
            <div class="form-group">
                <label>Image</label>
                <input type="file" class="form-control" name="pimage">
                <small>Leave blank if you do not want to change the image.</small>
            </div>
            <div class="form-group">
                <label>Amenities (multiple, icon + label)</label>
                <input type="file" name="amenity_icons[]" multiple accept="image/*" class="form-control">
                <input type="text" name="amenity_labels[]" multiple placeholder="Amenity Label(s)" class="form-control mt-2">
            </div>
            <div class="form-group">
                <label>Gallery Images (multiple)</label>
                <input type="file" name="gallery_images[]" multiple accept="image/*" class="form-control">
            </div>
            <div class="form-group">
                <label>Brochure (PDF)</label>
                <input type="file" name="brochure" accept="application/pdf" class="form-control">
            </div>
            <div class="form-group">
                <label>YouTube Video URL</label>
                <input type="url" name="youtube_url" class="form-control" value="<?php echo htmlspecialchars($project['youtube_url'] ?? ''); ?>">
            </div>
            <button type="submit" name="updateproject" class="btn btn-primary">Update Project</button>
        </form>
    </div>

    <!-- jQuery -->
    <script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>
    <!-- Bootstrap Core JS -->
    <script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include("../includes/templates/new_footer.php");?>
</body>

</html>
