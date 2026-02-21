<?php
/**
 * Modernized Submit Property Page
 * Migrated from Views/submitproperty.php
 */

require_once __DIR__ . '/init.php';

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}

$db = \App\Core\App::database();
$uid = $_SESSION['uid'];
$error = "";
$msg = "";

// Handle form submission
if (isset($_POST['submit_property'])) {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'], 'submit_property')) {
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

        // Handle File Uploads
        $upload_dir = __DIR__ . "/../../../public/uploads/property/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $images = [];
        $image_fields = ['aimage', 'aimage1', 'aimage2', 'aimage3', 'aimage4', 'fimage', 'fimage1', 'fimage2'];
        
        foreach ($image_fields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
                $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                $new_name = uniqid($field . '_') . '.' . $ext;
                if (move_uploaded_file($_FILES[$field]['tmp_name'], $upload_dir . $new_name)) {
                    $images[$field] = $new_name;
                } else {
                    $images[$field] = "";
                }
            } else {
                $images[$field] = "";
            }
        }

        // Insert into database using singleton
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
                'pimage' => $images['aimage'],
                'pimage1' => $images['aimage1'],
                'pimage2' => $images['aimage2'],
                'pimage3' => $images['aimage3'],
                'pimage4' => $images['aimage4'],
                'uid' => $uid,
                'status' => $status,
                'mapimage' => $images['fimage'],
                'topmapimage' => $images['fimage1'],
                'groundmapimage' => $images['fimage2'],
                'totalfloor' => $totalfloor
            ];

            if ($db->insert('property', $data)) {
                $msg = "Property submitted successfully! It will be live after admin approval.";
            } else {
                $error = "Failed to submit property. Database error.";
            }
        } catch (Exception $e) {
            error_log("Property Submission Error: " . $e->getMessage());
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
                    <h2 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2"></i>Submit New Property</h2>
                    <p class="mb-0 opacity-75">Fill in the details to list your property</p>
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
                        <input type="hidden" name="csrf_token" value="<?= CSRFProtection::generateToken('submit_property') ?>">
                        
                        <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Basic Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Property Title</label>
                                <input type="text" name="title" class="form-control bg-light border-0" placeholder="e.g. Luxury 3BHK Apartment" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="content" class="form-control bg-light border-0" rows="4" placeholder="Describe your property in detail..." required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Property Type</label>
                                <select name="ptype" class="form-select bg-light border-0" required>
                                    <option value="">Select Type</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="flat">Flat</option>
                                    <option value="house">House</option>
                                    <option value="villa">Villa</option>
                                    <option value="plot">Plot</option>
                                    <option value="commercial">Commercial</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Listing For</label>
                                <select name="stype" class="form-select bg-light border-0" required>
                                    <option value="sale">Sale</option>
                                    <option value="rent">Rent</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">BHK</label>
                                <select name="bhk" class="form-select bg-light border-0">
                                    <option value="N/A">N/A</option>
                                    <option value="1 BHK">1 BHK</option>
                                    <option value="2 BHK">2 BHK</option>
                                    <option value="3 BHK">3 BHK</option>
                                    <option value="4 BHK">4+ BHK</option>
                                </select>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Property Details</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Bedrooms</label>
                                <input type="number" name="bed" class="form-control bg-light border-0" min="0" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Bathrooms</label>
                                <input type="number" name="bath" class="form-control bg-light border-0" min="0" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Kitchens</label>
                                <input type="number" name="kitc" class="form-control bg-light border-0" min="0" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Balconies</label>
                                <input type="number" name="balc" class="form-control bg-light border-0" min="0" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Area Size (sq.ft)</label>
                                <input type="text" name="asize" class="form-control bg-light border-0" placeholder="e.g. 1200">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Price (₹)</label>
                                <input type="text" name="price" class="form-control bg-light border-0" placeholder="e.g. 4500000" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Floor</label>
                                <input type="text" name="floor" class="form-control bg-light border-0" placeholder="e.g. 2nd Floor">
                            </div>
                        </div>

                        <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Location</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Address / Landmark</label>
                                <input type="text" name="loc" class="form-control bg-light border-0" placeholder="Full address or nearby landmark" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">City</label>
                                <input type="text" name="city" class="form-control bg-light border-0" placeholder="e.g. Lucknow" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">State</label>
                                <input type="text" name="state" class="form-control bg-light border-0" placeholder="e.g. Uttar Pradesh" required>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Property Images</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Featured Image (Main Thumbnail)</label>
                                <input type="file" name="aimage" class="form-control bg-light border-0" accept="image/*" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gallery Image 1</label>
                                <input type="file" name="aimage1" class="form-control bg-light border-0" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gallery Image 2</label>
                                <input type="file" name="aimage2" class="form-control bg-light border-0" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gallery Image 3</label>
                                <input type="file" name="aimage3" class="form-control bg-light border-0" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gallery Image 4</label>
                                <input type="file" name="aimage4" class="form-control bg-light border-0" accept="image/*">
                            </div>
                        </div>

                        <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Features & Amenities</h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <textarea name="feature" class="form-control bg-light border-0" rows="3" placeholder="List features like: Garden, Parking, Security, etc." required></textarea>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="submit_property" class="btn btn-primary btn-lg rounded-pill fw-bold py-3 shadow-sm hover-lift">
                                <i class="fas fa-paper-plane me-2"></i>Submit Property for Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted small">By submitting, you agree to our <a href="legal.php">Terms & Conditions</a>.</p>
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
$page_title = "Submit Property - APS Dream Home";
require_once __DIR__ . '/../layouts/modern.php';
?>

