<?php
/**
 * Modernized Edit Property Page
 * Migrated from Views/submitpropertyupdate.php
 */

require_once __DIR__ . '/init.php';

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}

$db = \App\Core\App::database();
$uid = $_SESSION['uid'];
$pid = intval($_GET['id'] ?? 0);
$error = "";
$msg = "";

// Verify property ownership
$property = null;
if ($pid > 0) {
    $property = $db->fetch("SELECT * FROM property WHERE pid = ? AND uid = ?", [$pid, $uid]);
}

if (!$property) {
    header("Location: dashboards/user_dashboard.php");
    exit;
}

// Handle form submission
if (isset($_POST['update_property'])) {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'], 'edit_property')) {
        $error = "Security error: Invalid CSRF token.";
    } else {
        // Collect data
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $ptype = $_POST['ptype'] ?? '';
        $bhk = $_POST['bhk'] ?? '';
        $stype = $_POST['stype'] ?? '';
        $bed = intval($_POST['bed'] ?? 0);
        $bath = intval($_POST['bath'] ?? 0);
        $balc = intval($_POST['balc'] ?? 0);
        $kitc = intval($_POST['kitc'] ?? 0);
        $hall = intval($_POST['hall'] ?? 0);
        $floor = $_POST['floor'] ?? '';
        $asize = $_POST['asize'] ?? '';
        $price = $_POST['price'] ?? '';
        $loc = $_POST['loc'] ?? '';
        $city = $_POST['city'] ?? '';
        $state = $_POST['state'] ?? '';
        $feature = $_POST['feature'] ?? '';
        $status = $_POST['status'] ?? 'available';
        $totalfloor = $_POST['totalfl'] ?? '';

        // Handle File Uploads (only if new files are provided)
        $upload_dir = __DIR__ . "/../../../public/uploads/property/";
        $images = [
            'pimage' => $property['pimage'],
            'pimage1' => $property['pimage1'],
            'pimage2' => $property['pimage2'],
            'pimage3' => $property['pimage3'],
            'pimage4' => $property['pimage4'],
            'mapimage' => $property['mapimage'],
            'topmapimage' => $property['topmapimage'],
            'groundmapimage' => $property['groundmapimage']
        ];
        
        $image_map = [
            'aimage' => 'pimage',
            'aimage1' => 'pimage1',
            'aimage2' => 'pimage2',
            'aimage3' => 'pimage3',
            'aimage4' => 'pimage4',
            'fimage' => 'mapimage',
            'fimage1' => 'topmapimage',
            'fimage2' => 'groundmapimage'
        ];

        foreach ($image_map as $form_field => $db_field) {
            if (isset($_FILES[$form_field]) && $_FILES[$form_field]['error'] == 0) {
                $ext = pathinfo($_FILES[$form_field]['name'], PATHINFO_EXTENSION);
                $new_name = uniqid($form_field . '_') . '.' . $ext;
                if (move_uploaded_file($_FILES[$form_field]['tmp_name'], $upload_dir . $new_name)) {
                    // Delete old image if it exists
                    if (!empty($property[$db_field]) && file_exists($upload_dir . $property[$db_field])) {
                        unlink($upload_dir . $property[$db_field]);
                    }
                    $images[$db_field] = $new_name;
                }
            }
        }

        // Update database using singleton
        try {
            $data = [
                'title' => $title,
                'pcontent' => $content,
                'type' => $ptype,
                'bhk' => $bhk,
                'stype' => $stype,
                'bedroom' => $bed,
                'bathroom' => $bath,
                'balcony' => $balc,
                'kitchen' => $kitc,
                'hall' => $hall,
                'floor' => $floor,
                'size' => $asize,
                'price' => $price,
                'location' => $loc,
                'city' => $city,
                'state' => $state,
                'feature' => $feature,
                'status' => $status,
                'totalfloor' => $totalfloor,
                'pimage' => $images['pimage'],
                'pimage1' => $images['pimage1'],
                'pimage2' => $images['pimage2'],
                'pimage3' => $images['pimage3'],
                'pimage4' => $images['pimage4'],
                'mapimage' => $images['mapimage'],
                'topmapimage' => $images['topmapimage'],
                'groundmapimage' => $images['groundmapimage']
            ];

            if ($db->update('property', $data, ['pid' => $pid, 'uid' => $uid])) {
                $msg = "Property updated successfully!";
                // Refresh property data
                $property = $db->fetch("SELECT * FROM property WHERE pid = ? AND uid = ?", [$pid, $uid]);
            } else {
                $error = "Failed to update property. Database error.";
            }
        } catch (Exception $e) {
            error_log("Property Update Error: " . $e->getMessage());
            $error = "An error occurred. Please try again later.";
        }
    }
}

// Start buffering for modern layout
ob_start();
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden animate-fade-up">
                <div class="card-header bg-primary text-white p-4">
                    <h2 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Edit Property</h2>
                    <p class="mb-0 opacity-75">Update your property details below</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($msg): ?>
                        <div class="alert alert-success rounded-3 border-0 shadow-sm mb-4">
                            <i class="fas fa-check-circle me-2"></i> <?= $msg ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= CSRFProtection::generateToken('edit_property') ?>">
                        
                        <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Basic Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Property Title</label>
                                <input type="text" name="title" class="form-control bg-light border-0" value="<?= h($property['title']) ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="content" class="form-control bg-light border-0" rows="4" required><?= h($property['pcontent']) ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Property Type</label>
                                <select name="ptype" class="form-select bg-light border-0" required>
                                    <option value="apartment" <?= $property['type'] == 'apartment' ? 'selected' : '' ?>>Apartment</option>
                                    <option value="flat" <?= $property['type'] == 'flat' ? 'selected' : '' ?>>Flat</option>
                                    <option value="house" <?= $property['type'] == 'house' ? 'selected' : '' ?>>House</option>
                                    <option value="villa" <?= $property['type'] == 'villa' ? 'selected' : '' ?>>Villa</option>
                                    <option value="plot" <?= $property['type'] == 'plot' ? 'selected' : '' ?>>Plot</option>
                                    <option value="commercial" <?= $property['type'] == 'commercial' ? 'selected' : '' ?>>Commercial</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Listing For</label>
                                <select name="stype" class="form-select bg-light border-0" required>
                                    <option value="sale" <?= $property['stype'] == 'sale' ? 'selected' : '' ?>>Sale</option>
                                    <option value="rent" <?= $property['stype'] == 'rent' ? 'selected' : '' ?>>Rent</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">BHK</label>
                                <select name="bhk" class="form-select bg-light border-0">
                                    <option value="N/A" <?= $property['bhk'] == 'N/A' ? 'selected' : '' ?>>N/A</option>
                                    <option value="1 BHK" <?= $property['bhk'] == '1 BHK' ? 'selected' : '' ?>>1 BHK</option>
                                    <option value="2 BHK" <?= $property['bhk'] == '2 BHK' ? 'selected' : '' ?>>2 BHK</option>
                                    <option value="3 BHK" <?= $property['bhk'] == '3 BHK' ? 'selected' : '' ?>>3 BHK</option>
                                    <option value="4 BHK" <?= $property['bhk'] == '4 BHK' ? 'selected' : '' ?>>4+ BHK</option>
                                </select>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Property Details</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Bedrooms</label>
                                <input type="number" name="bed" class="form-control bg-light border-0" min="0" value="<?= $property['bedroom'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Bathrooms</label>
                                <input type="number" name="bath" class="form-control bg-light border-0" min="0" value="<?= $property['bathroom'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Kitchens</label>
                                <input type="number" name="kitc" class="form-control bg-light border-0" min="0" value="<?= $property['kitchen'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Balconies</label>
                                <input type="number" name="balc" class="form-control bg-light border-0" min="0" value="<?= $property['balcony'] ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Area Size (sq.ft)</label>
                                <input type="text" name="asize" class="form-control bg-light border-0" value="<?= h($property['size']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Price (₹)</label>
                                <input type="text" name="price" class="form-control bg-light border-0" value="<?= h($property['price']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Floor</label>
                                <input type="text" name="floor" class="form-control bg-light border-0" value="<?= h($property['floor']) ?>">
                            </div>
                        </div>

                        <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Location</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Address / Landmark</label>
                                <input type="text" name="loc" class="form-control bg-light border-0" value="<?= h($property['location']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">City</label>
                                <input type="text" name="city" class="form-control bg-light border-0" value="<?= h($property['city']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">State</label>
                                <input type="text" name="state" class="form-control bg-light border-0" value="<?= h($property['state']) ?>" required>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Property Images</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Featured Image (Leave empty to keep current)</label>
                                <input type="file" name="aimage" class="form-control bg-light border-0" accept="image/*">
                                <?php if($property['pimage']): ?>
                                    <div class="mt-2"><img src="<?= BASE_URL ?>public/uploads/property/<?= $property['pimage'] ?>" height="60" class="rounded shadow-sm"></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gallery Image 1</label>
                                <input type="file" name="aimage1" class="form-control bg-light border-0" accept="image/*">
                                <?php if($property['pimage1']): ?>
                                    <div class="mt-2"><img src="<?= BASE_URL ?>public/uploads/property/<?= $property['pimage1'] ?>" height="50" class="rounded shadow-sm"></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gallery Image 2</label>
                                <input type="file" name="aimage2" class="form-control bg-light border-0" accept="image/*">
                                <?php if($property['pimage2']): ?>
                                    <div class="mt-2"><img src="<?= BASE_URL ?>public/uploads/property/<?= $property['pimage2'] ?>" height="50" class="rounded shadow-sm"></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Status & Features</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Listing Status</label>
                                <select name="status" class="form-select bg-light border-0" required>
                                    <option value="available" <?= $property['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="sold out" <?= $property['status'] == 'sold out' ? 'selected' : '' ?>>Sold Out</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Features & Amenities</label>
                                <textarea name="feature" class="form-control bg-light border-0" rows="3" required><?= h($property['feature']) ?></textarea>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="update_property" class="btn btn-primary btn-lg rounded-pill fw-bold py-3 shadow-sm hover-lift">
                                <i class="fas fa-save me-2"></i>Update Property
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.form-control:focus, .form-select:focus {
    background-color: #fff !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    border: 1px solid #0d6efd !important;
}
</style>

<script>
// Form validation
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>

<?php
$content = ob_get_clean();
$page_title = "Edit Property - APS Dream Home";
require_once __DIR__ . '/../layouts/modern.php';
?>

