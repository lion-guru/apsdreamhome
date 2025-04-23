<?php
session_start();
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/../includes/db_config.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

initAdminSession();
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $city = trim($_POST['city']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $status = isset($_POST['status']) ? 1 : 0;
    $youtube_url = isset($_POST['youtube_url']) ? trim($_POST['youtube_url']) : '';
    $conn = getDbConnection();
    if ($conn) {
        // Insert project
        $stmt = $conn->prepare("INSERT INTO projects (name, city, location, description, status, youtube_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $name, $city, $location, $description, $status, $youtube_url);
        if ($stmt->execute()) {
            $project_id = $stmt->insert_id;
            // Handle brochure upload
            if (!empty($_FILES['brochure']['name'])) {
                $brochure_name = time() . '_' . basename($_FILES['brochure']['name']);
                $brochure_path = 'uploads/brochures/' . $brochure_name;
                if (!file_exists('uploads/brochures')) { mkdir('uploads/brochures', 0777, true); }
                move_uploaded_file($_FILES['brochure']['tmp_name'], $brochure_path);
                $conn->query("UPDATE projects SET brochure_path='$brochure_path' WHERE id=$project_id");
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
                        $conn->query("INSERT INTO project_amenities (project_id, icon_path, label) VALUES ($project_id, '$icon_path', '" . $conn->real_escape_string($label) . "')");
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
                        $conn->query("INSERT INTO project_gallery (project_id, image_path) VALUES ($project_id, '$img_path')");
                    }
                }
            }
            log_admin_activity('add_project', 'Added project: ' . $name);
            header("Location: projects.php?msg=".urlencode('added'));
            exit();
        } else {
            $error = "Error: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
        $conn->close();
    } else {
        $error = "Database connection error.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Project</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .main-content { margin-left: 220px; padding: 2rem 1rem; }
        .form-floating > .fa { position: absolute; left: 20px; top: 22px; color: #aaa; pointer-events: none; }
        .form-floating input, .form-floating textarea { padding-left: 2.5rem; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
<div class="main-content">
    <h1 class="mb-4">Add New Project</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST" action="" autocomplete="off" class="needs-validation" novalidate enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-floating position-relative">
                    <input type="text" class="form-control" id="name" name="name" required placeholder="Project Name">
                    <label for="name"><i class="fa fa-building"></i> Project Name *</label>
                    <div class="invalid-feedback">Please enter the project name.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating position-relative">
                    <input type="text" class="form-control" id="city" name="city" required placeholder="City">
                    <label for="city"><i class="fa fa-city"></i> City *</label>
                    <div class="invalid-feedback">Please enter the city.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating position-relative">
                    <input type="text" class="form-control" id="location" name="location" required placeholder="Location">
                    <label for="location"><i class="fa fa-map-marker-alt"></i> Location *</label>
                    <div class="invalid-feedback">Please enter the location.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating position-relative">
                    <textarea class="form-control" id="description" name="description" required placeholder="Description" style="height: 70px"></textarea>
                    <label for="description"><i class="fa fa-align-left"></i> Description *</label>
                    <div class="invalid-feedback">Please enter the description.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating position-relative">
                    <input type="text" class="form-control" id="amenities" name="amenities" placeholder="Amenities">
                    <label for="amenities"><i class="fa fa-list"></i> Amenities</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating position-relative">
                    <input type="text" class="form-control" id="images" name="images" placeholder="Images (comma separated URLs)">
                    <label for="images"><i class="fa fa-images"></i> Images (comma separated URLs)</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating position-relative">
                    <input type="text" class="form-control" id="video" name="video" placeholder="Video URL">
                    <label for="video"><i class="fa fa-video"></i> Video URL</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating position-relative">
                    <input type="text" class="form-control" id="layout_map" name="layout_map" placeholder="Layout Map URL">
                    <label for="layout_map"><i class="fa fa-map"></i> Layout Map URL</label>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Amenities (multiple, icon + label)</label>
                    <input type="file" name="amenity_icons[]" multiple accept="image/*" class="form-control">
                    <input type="text" name="amenity_labels[]" multiple placeholder="Amenity Label(s)" class="form-control mt-2">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Gallery Images (multiple)</label>
                    <input type="file" name="gallery_images[]" multiple accept="image/*" class="form-control">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Brochure (PDF)</label>
                    <input type="file" name="brochure" accept="application/pdf" class="form-control">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>YouTube Video URL</label>
                    <input type="url" name="youtube_url" class="form-control">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="status" id="status" value="1" checked>
                    <label class="form-check-label" for="status">
                        <i class="fa fa-check-circle"></i> Active
                    </label>
                </div>
            </div>
        </div>
        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-plus"></i> Add Project</button>
        </div>
    </form>
</div>
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
