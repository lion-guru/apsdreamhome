<?php
/**
 * Property Listing Form Handler - APS Dream Homes
 * Handles property listings from sellers
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once '../includes/db_connection.php';
        $pdo = getMysqliConnection();

        // Get form data
        $owner_name = trim($_POST['owner_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $property_type = trim($_POST['property_type'] ?? '');
        $property_title = trim($_POST['property_title'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $bedrooms = (int)($_POST['bedrooms'] ?? 0);
        $bathrooms = (int)($_POST['bathrooms'] ?? 0);
        $area = (float)($_POST['area'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $amenities = isset($_POST['amenities']) ? implode(', ', $_POST['amenities']) : '';
        $availability = trim($_POST['availability'] ?? '');
        $property_images = $_FILES['property_images'] ?? null;

        // Validation
        $errors = [];

        if (empty($owner_name)) $errors[] = 'Owner name is required';
        if (empty($email)) $errors[] = 'Email is required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($phone)) $errors[] = 'Phone number is required';
        if (empty($property_type)) $errors[] = 'Property type is required';
        if (empty($property_title)) $errors[] = 'Property title is required';
        if (empty($location)) $errors[] = 'Location is required';
        if ($bedrooms <= 0) $errors[] = 'Number of bedrooms must be greater than 0';
        if ($bathrooms <= 0) $errors[] = 'Number of bathrooms must be greater than 0';
        if ($area <= 0) $errors[] = 'Area must be greater than 0';
        if ($price <= 0) $errors[] = 'Price must be greater than 0';
        if (empty($description)) $errors[] = 'Property description is required';

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Handle multiple image uploads
        $image_urls = [];
        $upload_dir = '../uploads/properties/';

        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if ($property_images && is_array($property_images['name'])) {
            foreach ($property_images['name'] as $key => $name) {
                if ($property_images['error'][$key] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                    $file_name = uniqid('property_') . '_' . $key . '.' . $file_extension;
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($property_images['tmp_name'][$key], $file_path)) {
                        $image_urls[] = $file_name;
                    }
                }
            }
        }

        $images_json = !empty($image_urls) ? json_encode($image_urls) : null;

        // Insert property listing request
        $stmt = $pdo->prepare("
            INSERT INTO property_listings
            (owner_name, email, phone, property_type, property_title, location, bedrooms,
             bathrooms, area, price, description, amenities, availability, images, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");

        $stmt->execute([
            $owner_name, $email, $phone, $property_type, $property_title, $location,
            $bedrooms, $bathrooms, $area, $price, $description, $amenities,
            $availability, $images_json
        ]);

        // Send email notification
        $to = 'listings@apsdreamhomes.com';
        $email_subject = 'New Property Listing Request - APS Dream Homes';
        $email_body = "
New property listing request received:

Owner Details:
Name: $owner_name
Email: $email
Phone: $phone

Property Details:
Type: $property_type
Title: $property_title
Location: $location
Bedrooms: $bedrooms
Bathrooms: $bathrooms
Area: $area sq ft
Price: ₹" . number_format($price) . "
Availability: $availability

Description:
$description

Amenities: $amenities

Images: " . (!empty($image_urls) ? count($image_urls) . ' images uploaded' : 'No images uploaded') . "
        ";

        $headers = "From: noreply@apsdreamhomes.com\r\n";
        $headers .= "Reply-To: $email\r\n";

        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your property listing request! Our team will review it and contact you soon.'
        ]);

    } catch (Exception $e) {
        error_log('Property listing error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred. Please try again later.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
