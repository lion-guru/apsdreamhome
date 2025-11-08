<?php
/**
 * Resell Properties System - User Property Upload
 * APS Dream Homes - Property Listing for Individual Sellers
 */

session_start();
require_once '../includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

$message = '';
$message_type = '';
$property_id = $_GET['id'] ?? 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $property_title = trim($_POST['property_title']);
    $property_type = trim($_POST['property_type']);
    $price = trim($_POST['price']);
    $bedrooms = trim($_POST['bedrooms']);
    $bathrooms = trim($_POST['bathrooms']);
    $area = trim($_POST['area']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $description = trim($_POST['description']);
    $features = isset($_POST['features']) ? $_POST['features'] : [];
    $terms_accepted = isset($_POST['terms_accepted']) ? 1 : 0;

    // Validation
    if (empty($full_name) || empty($mobile) || empty($email) || empty($property_title) || empty($price)) {
        $message = "Please fill all required fields!";
        $message_type = "danger";
    } elseif (strlen($mobile) != 10) {
        $message = "Mobile number must be 10 digits!";
        $message_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address!";
        $message_type = "danger";
    } elseif (!$terms_accepted) {
        $message = "Please accept the terms and conditions!";
        $message_type = "danger";
    } else {
        // Check if user already exists
        $check_stmt = $conn->prepare("SELECT id FROM resell_users WHERE mobile = ? OR email = ?");
        $check_stmt->bind_param("ss", $mobile, $email);
        $check_stmt->execute();
        $existing_user = $check_stmt->get_result()->fetch_assoc();

        $user_id = null;
        if ($existing_user) {
            $user_id = $existing_user['id'];
            // Update existing user
            $update_stmt = $conn->prepare("UPDATE resell_users SET full_name = ?, email = ?, mobile = ? WHERE id = ?");
            $update_stmt->bind_param("sssi", $full_name, $email, $mobile, $user_id);
            $update_stmt->execute();
        } else {
            // Create new user
            $insert_stmt = $conn->prepare("INSERT INTO resell_users (full_name, mobile, email, registration_date) VALUES (?, ?, ?, NOW())");
            $insert_stmt->bind_param("sss", $full_name, $mobile, $email);
            $insert_stmt->execute();
            $user_id = $conn->insert_id;
        }

        // Create property listing
        $features_json = json_encode($features);
        $status = 'pending'; // Properties need approval before going live

        $property_stmt = $conn->prepare("INSERT INTO resell_properties (user_id, title, property_type, price, bedrooms, bathrooms, area, address, city, state, description, features, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $property_stmt->bind_param("issdiiissssss", $user_id, $property_title, $property_type, $price, $bedrooms, $bathrooms, $area, $address, $city, $state, $description, $features_json, $status);

        if ($property_stmt->execute()) {
            $property_id = $conn->insert_id;

            // Handle image uploads
            $upload_dir = '../uploads/resell_properties/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $uploaded_images = [];
            $image_fields = ['featured_image', 'image1', 'image2', 'image3', 'image4', 'image5'];
            
            foreach ($image_fields as $field) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES[$field]['name'];
                    $file_tmp = $_FILES[$field]['tmp_name'];
                    $file_size = $_FILES[$field]['size'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    $allowed_ext = ['jpg', 'jpeg', 'png'];
                    
                    if (in_array($file_ext, $allowed_ext)) {
                        if ($file_size <= 2097152) { // 2MB max
                            $new_file_name = 'property_' . $property_id . '_' . time() . '_' . uniqid() . '.' . $file_ext;
                            $file_path = $upload_dir . $new_file_name;
                            
                            if (move_uploaded_file($file_tmp, $file_path)) {
                                $uploaded_images[] = $new_file_name;
                                
                                // Insert image record into database
                                $is_featured = ($field === 'featured_image') ? 1 : 0;
                                $image_stmt = $conn->prepare("INSERT INTO resell_property_images (property_id, image_name, is_featured, uploaded_at) VALUES (?, ?, ?, NOW())");
                                $image_stmt->bind_param("isi", $property_id, $new_file_name, $is_featured);
                                $image_stmt->execute();
                            }
                        }
                    }
                }
            }

            // Send WhatsApp notification
            $whatsapp_message = "🏠 Property Listing Submitted Successfully!\n\n";
            $whatsapp_message .= "👤 Seller: " . $full_name . "\n";
            $whatsapp_message .= "📱 Mobile: " . $mobile . "\n";
            $whatsapp_message .= "📧 Email: " . $email . "\n\n";

            $whatsapp_message .= "🏡 Property Details:\n";
            $whatsapp_message .= "📋 Title: " . $property_title . "\n";
            $whatsapp_message .= "🏷️ Type: " . ucfirst($property_type) . "\n";
            $whatsapp_message .= "💰 Price: ₹" . number_format($price) . "\n";
            $whatsapp_message .= "📍 Location: " . $address . ", " . $city . ", " . $state . "\n\n";

            if (!empty($uploaded_images)) {
                $whatsapp_message .= "📸 Images Uploaded: " . count($uploaded_images) . "\n";
            }

            $whatsapp_message .= "✅ Your property is under review\n";
            $whatsapp_message .= "⏰ It will be live within 24 hours\n";
            $whatsapp_message .= "📞 Our team will contact you soon\n\n";

            $whatsapp_message .= "🔗 Track Status: http://localhost/apsdreamhome/resell-status.php\n";
            $whatsapp_message .= "📱 Support: +91-9876543210\n\n";
            $whatsapp_message .= "APS Dream Homes - Your Property, Our Priority! 🏠✨";

            sendWhatsAppNotification($mobile, $whatsapp_message);

            $message = "Property listing submitted successfully! Your property is under review and will be live within 24 hours. WhatsApp notification sent with details.";
            $message_type = "success";

            // Clear form data
            $_POST = array();
        } else {
            $message = "Property listing failed. Please try again!";
            $message_type = "danger";
        }
    }
}

// WhatsApp notification function
function sendWhatsAppNotification($mobile, $message) {
    error_log("WhatsApp Notification to: " . $mobile . "\nMessage: " . $message);
    return true;
}

// Get states for dropdown
$indian_states = [
    'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat',
    'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh',
    'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
    'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh',
    'Uttarakhand', 'West Bengal', 'Delhi', 'Jammu and Kashmir', 'Ladakh'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Your Property - Resell Properties - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .listing-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 1000px;
        }
        .header-section {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            padding: 3rem 2rem 2rem;
            text-align: center;
        }
        .logo-section {
            background: linear-gradient(45deg, #17a2b8, #007bff);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .form-control:focus {
            border-color: #ff6b35;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        }
        .btn-submit {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            color: white;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0 10px 10px 0;
        }
        .benefits-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-right: 1rem;
        }
        .whatsapp-info {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0 10px 10px 0;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="listing-container">
                    <!-- Header Section -->
                    <div class="header-section">
                        <div class="logo-section d-inline-block">
                            <h3 class="mb-0">
                                <i class="fas fa-home me-2"></i>
                                APS DREAM HOMES
                            </h3>
                            <p class="mb-0">Resell Property Portal</p>
                        </div>
                        <h1 class="mb-2">List Your Property</h1>
                        <p class="mb-0">Sell your property quickly and easily</p>
                    </div>

                    <div class="p-4">
                        <!-- Messages -->
                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Information Box -->
                        <div class="info-box">
                            <h6><i class="fas fa-info-circle me-2"></i>Listing Information</h6>
                            <small>
                                🏠 List your property for free on our platform<br>
                                📱 Get direct inquiries from verified buyers<br>
                                💰 Sell faster with our extensive network<br>
                                📊 Track views and inquiries in real-time<br>
                                ✅ Properties are reviewed before going live
                            </small>
                        </div>

                        <!-- WhatsApp Info -->
                        <div class="whatsapp-info">
                            <h6><i class="fab fa-whatsapp me-2"></i>WhatsApp Support</h6>
                            <small>
                                ✅ Instant listing confirmation<br>
                                ✅ Property status updates<br>
                                ✅ Buyer inquiry notifications<br>
                                ✅ 24/7 customer support<br>
                                📞 Call: +91-9876543210
                            </small>
                        </div>

                        <!-- Benefits Section -->
                        <div class="benefits-section">
                            <h6 class="mb-3"><i class="fas fa-star me-2"></i>Selling Benefits</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <small>Reach thousands of buyers</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                        <small>Verified buyer inquiries</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <small>Real-time analytics</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <small>WhatsApp notifications</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-headset"></i>
                                        </div>
                                        <small>Dedicated support</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-percentage"></i>
                                        </div>
                                        <small>Best market rates</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Listing Form -->
                        <form method="POST" id="propertyListingForm" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-user me-1"></i>Full Name *
                                        </label>
                                        <input type="text" class="form-control" name="full_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-mobile-alt me-1"></i>Mobile Number *
                                        </label>
                                        <input type="tel" class="form-control" name="mobile" pattern="[0-9]{10}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address *
                                </label>
                                <input type="email" class="form-control" name="email" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-tag me-1"></i>Property Title *
                                        </label>
                                        <input type="text" class="form-control" name="property_title"
                                               placeholder="e.g., 3 BHK Apartment in Prime Location" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-home me-1"></i>Property Type *
                                        </label>
                                        <select class="form-control" name="property_type" required>
                                            <option value="">Select Type</option>
                                            <option value="apartment">Apartment</option>
                                            <option value="villa">Villa</option>
                                            <option value="house">Independent House</option>
                                            <option value="plot">Plot</option>
                                            <option value="commercial">Commercial</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-money-bill me-1"></i>Price (₹) *
                                        </label>
                                        <input type="number" class="form-control" name="price" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-bed me-1"></i>Bedrooms
                                        </label>
                                        <select class="form-control" name="bedrooms">
                                            <option value="">Select</option>
                                            <option value="1">1 BHK</option>
                                            <option value="2">2 BHK</option>
                                            <option value="3">3 BHK</option>
                                            <option value="4">4 BHK</option>
                                            <option value="5">5+ BHK</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-bath me-1"></i>Bathrooms
                                        </label>
                                        <select class="form-control" name="bathrooms">
                                            <option value="">Select</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4+</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-ruler-combined me-1"></i>Area (sq.ft)
                                        </label>
                                        <input type="number" class="form-control" name="area" min="1">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Full Address *
                                </label>
                                <input type="text" class="form-control" name="address" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-city me-1"></i>City *
                                        </label>
                                        <input type="text" class="form-control" name="city" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-map me-1"></i>State *
                                        </label>
                                        <select class="form-control" name="state" required>
                                            <option value="">Select State</option>
                                            <?php foreach ($indian_states as $state): ?>
                                                <option value="<?php echo $state; ?>"><?php echo $state; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-align-left me-1"></i>Description
                                </label>
                                <textarea class="form-control" name="description" rows="4"
                                          placeholder="Describe your property, nearby amenities, etc."></textarea>
                            </div>

                            <!-- Property Images Upload -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-images me-1"></i>Property Images *
                                </label>
                                <div class="alert alert-info">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        Upload at least one image of your property. First image will be used as featured image.
                                        Supported formats: JPG, PNG, JPEG. Max size: 2MB per image.
                                    </small>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Featured Image *</label>
                                            <input type="file" class="form-control" name="featured_image" accept="image/*" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Additional Image 1</label>
                                            <input type="file" class="form-control" name="image1" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Additional Image 2</label>
                                            <input type="file" class="form-control" name="image2" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Additional Image 3</label>
                                            <input type="file" class="form-control" name="image3" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Additional Image 4</label>
                                            <input type="file" class="form-control" name="image4" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Additional Image 5</label>
                                            <input type="file" class="form-control" name="image5" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-list me-1"></i>Property Features (Optional)
                                </label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="features[]" value="parking" id="parking">
                                            <label class="form-check-label" for="parking">Parking</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="features[]" value="gym" id="gym">
                                            <label class="form-check-label" for="gym">Gym</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="features[]" value="garden" id="garden">
                                            <label class="form-check-label" for="garden">Garden</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="features[]" value="security" id="security">
                                            <label class="form-check-label" for="security">Security</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="features[]" value="pool" id="pool">
                                            <label class="form-check-label" for="pool">Swimming Pool</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="features[]" value="balcony" id="balcony">
                                            <label class="form-check-label" for="balcony">Balcony</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="features[]" value="furnished" id="furnished">
                                            <label class="form-check-label" for="furnished">Furnished</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="features[]" value="maintenance" id="maintenance">
                                            <label class="form-check-label" for="maintenance">Maintenance</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="features[]" value="other" id="other">
                                            <label class="form-check-label" for="other">Other</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="terms_accepted" id="terms_accepted" required>
                                    <label class="form-check-label" for="terms_accepted">
                                        I accept the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms & Conditions</a> and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a> *
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-submit btn-lg">
                                    <i class="fas fa-upload me-2"></i>List My Property
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    By listing, you agree to receive WhatsApp notifications and buyer inquiries.
                                </small>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Terms Modal -->
                <div class="modal fade" id="termsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Terms & Conditions</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Terms and conditions for property listing...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Privacy Modal -->
                <div class="modal fade" id="privacyModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Privacy Policy</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Privacy policy for property sellers...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="mt-4 text-center">
                    <div class="bg-white p-3 rounded shadow-sm">
                        <h6 class="text-muted">Need Help?</h6>
                        <p class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            <strong>Support:</strong> +91-9876543210
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-envelope me-2"></i>
                            <strong>Email:</strong> resell@apsdreamhomes.com
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
