<?php
/**
 * Enhanced Booking System with WhatsApp Notifications
 * APS Dream Homes - Customer Booking Management
 */

session_start();
require_once '../includes/config.php';

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

$message = '';
$message_type = '';
$property_id = $_GET['id'] ?? 0;

// Get property details if ID is provided
$property = null;
if ($property_id) {
    $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $property = $stmt->get_result()->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "Invalid CSRF token. Please try again.";
        $message_type = "danger";
    } else {
        $full_name = trim($_POST['full_name']);
        $mobile = trim($_POST['mobile']);
        $email = trim($_POST['email']);
        $property_id = trim($_POST['property_id']);
        $booking_type = trim($_POST['booking_type']);
        $visit_date = trim($_POST['visit_date']);
        $visit_time = trim($_POST['visit_time']);
        $budget_range = trim($_POST['budget_range']);
        $financing_needed = isset($_POST['financing_needed']) ? 1 : 0;
        $special_requirements = trim($_POST['special_requirements']);

    // Validation
    if (empty($full_name) || empty($mobile) || empty($email) || empty($property_id)) {
        $message = "Please fill all required fields!";
        $message_type = "danger";
    } elseif (strlen($mobile) != 10) {
        $message = "Mobile number must be 10 digits!";
        $message_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address!";
        $message_type = "danger";
    } else {
        // Generate unique customer ID and password
        $customer_id = 'CUST-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $password = 'cust' . rand(1000, 9999);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if customer already exists
        $check_stmt = $conn->prepare("SELECT id FROM customers WHERE phone = ? OR email = ?");
        $check_stmt->bind_param("ss", $mobile, $email);
        $check_stmt->execute();
        $existing_customer = $check_stmt->get_result()->fetch_assoc();

        $customer_db_id = null;
        if ($existing_customer) {
            $customer_db_id = $existing_customer['id'];
            // Update existing customer
            $update_stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, phone = ? WHERE id = ?");
            $update_stmt->bind_param("sssi", $full_name, $email, $mobile, $customer_db_id);
            $update_stmt->execute();
        } else {
            // Create new customer
            $insert_stmt = $conn->prepare("INSERT INTO customers (id, name, email, phone, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $insert_stmt->bind_param("sssss", $customer_id, $full_name, $email, $mobile, $hashed_password);
            $insert_stmt->execute();
            $customer_db_id = $conn->insert_id;
        }

        // Create booking record
        $booking_status = 'confirmed';
        $booking_notes = "Property visit scheduled for " . date('d M Y', strtotime($visit_date)) . " at " . $visit_time;

        $booking_stmt = $conn->prepare("INSERT INTO bookings (customer_id, property_id, booking_type, visit_date, visit_time, budget_range, financing_needed, special_requirements, status, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $booking_stmt->bind_param("iissssisss", $customer_db_id, $property_id, $booking_type, $visit_date, $visit_time, $budget_range, $financing_needed, $special_requirements, $booking_status, $booking_notes);

        if ($booking_stmt->execute()) {
            $booking_id = $conn->insert_id;

            // Send WhatsApp notification
            $whatsapp_message = "🎉 Booking Confirmed!\n\n";
            $whatsapp_message .= "📋 Booking ID: BK" . str_pad($booking_id, 6, '0', STR_PAD_LEFT) . "\n";
            $whatsapp_message .= "🏠 Property ID: " . $property_id . "\n";
            $whatsapp_message .= "👤 Customer: " . $full_name . "\n";
            $whatsapp_message .= "📱 Mobile: " . $mobile . "\n";
            $whatsapp_message .= "📧 Email: " . $email . "\n";
            $whatsapp_message .= "📅 Visit Date: " . date('d M Y', strtotime($visit_date)) . "\n";
            $whatsapp_message .= "🕐 Visit Time: " . $visit_time . "\n\n";

            $whatsapp_message .= "🔐 Your Login Credentials:\n";
            $whatsapp_message .= "Customer ID: " . $customer_id . "\n";
            $whatsapp_message .= "Password: " . $password . "\n\n";

            $whatsapp_message .= "You can login using:\n";
            $whatsapp_message .= "• Customer ID + Password\n";
            $whatsapp_message .= "• Mobile Number + Password\n";
            $whatsapp_message .= "• Email + Password\n\n";

            $whatsapp_message .= "📞 Need Help? Call: +91-7007444842\n";
            $whatsapp_message .= "🌐 Visit: http://localhost/apsdreamhome/customer_login.php\n\n";
            $whatsapp_message .= "APS Dream Homes - Your Dream Home Awaits! 🏡✨";

            // Send WhatsApp (placeholder - integrate with actual WhatsApp API)
            sendWhatsAppNotification($mobile, $whatsapp_message);

            $message = "Booking confirmed successfully! Your Customer ID is: <strong>" . $customer_id . "</strong> and Password is: <strong>" . $password . "</strong>. WhatsApp notification sent with all details.";
            $message_type = "success";

            // Clear form data
            $_POST = array();
        } else {
            $message = "Booking failed. Please try again!";
            $message_type = "danger";
        }
    }
}
    }

// WhatsApp notification function (placeholder)
function sendWhatsAppNotification($mobile, $message) {
    // This is a placeholder - integrate with actual WhatsApp API
    // You can use services like Twilio, WhatsApp Business API, etc.

    $api_url = "https://api.whatsapp.com/send?phone=91" . $mobile . "&text=" . urlencode($message);

    // For now, just log the message
    error_log("WhatsApp Notification to: " . $mobile . "\nMessage: " . $message);

    // In production, implement actual WhatsApp API integration
    return true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Property Visit - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .booking-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 800px;
        }
        .header-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .property-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            border-left: 5px solid #28a745;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .btn-book {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-book:hover {
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
            <div class="col-lg-10">
                <div class="booking-container">
                    <!-- Header Section -->
                    <div class="header-section">
                        <div class="logo-section d-inline-block">
                            <h3 class="mb-0">
                                <i class="fas fa-home me-2"></i>
                                APS DREAM HOMES
                            </h3>
                            <p class="mb-0">Book Your Property Visit</p>
                        </div>
                        <h1 class="mb-2">Schedule Property Visit</h1>
                        <p class="mb-0">Get instant booking confirmation with WhatsApp notifications</p>
                    </div>

                    <div class="p-4">
                        <!-- Messages -->
                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Property Details -->
                        <?php if ($property): ?>
                        <div class="property-card">
                            <h5 class="mb-3"><i class="fas fa-building me-2"></i>Property Details</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Property ID:</strong> <?php echo htmlspecialchars($property['id']); ?><br>
                                    <strong>Title:</strong> <?php echo htmlspecialchars($property['title']); ?><br>
                                    <strong>Type:</strong> <?php echo htmlspecialchars($property['property_type']); ?><br>
                                    <strong>Price:</strong> ₹<?php echo number_format($property['price']); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Location:</strong> <?php echo htmlspecialchars($property['address']); ?><br>
                                    <strong>Bedrooms:</strong> <?php echo $property['bedrooms']; ?><br>
                                    <strong>Bathrooms:</strong> <?php echo $property['bathrooms']; ?><br>
                                    <strong>Area:</strong> <?php echo number_format($property['area']); ?> sq.ft
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Information Boxes -->
                        <div class="info-box">
                            <h6><i class="fas fa-info-circle me-2"></i>Booking Information</h6>
                            <small>
                                📝 Fill out the form below to schedule your property visit<br>
                                📱 You'll receive instant confirmation via WhatsApp<br>
                                🔐 Auto-generated login credentials will be sent to you<br>
                                📞 Our team will contact you to confirm the visit details
                            </small>
                        </div>

                        <div class="whatsapp-info">
                            <h6><i class="fab fa-whatsapp me-2"></i>WhatsApp Notifications</h6>
                            <small>
                                ✅ Instant booking confirmation<br>
                                ✅ Complete property details<br>
                                ✅ Your login credentials<br>
                                ✅ Visit reminders and updates<br>
                                ✅ 24/7 customer support
                            </small>
                        </div>

                        <!-- Booking Form -->
                        <form method="POST" id="bookingForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                        
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
                                            <i class="fas fa-calendar me-1"></i>Preferred Visit Date *
                                        </label>
                                        <input type="date" class="form-control" name="visit_date"
                                               min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-clock me-1"></i>Preferred Visit Time *
                                        </label>
                                        <select class="form-control" name="visit_time" required>
                                            <option value="">Select Time</option>
                                            <option value="09:00 AM">09:00 AM</option>
                                            <option value="10:00 AM">10:00 AM</option>
                                            <option value="11:00 AM">11:00 AM</option>
                                            <option value="12:00 PM">12:00 PM</option>
                                            <option value="02:00 PM">02:00 PM</option>
                                            <option value="03:00 PM">03:00 PM</option>
                                            <option value="04:00 PM">04:00 PM</option>
                                            <option value="05:00 PM">05:00 PM</option>
                                            <option value="06:00 PM">06:00 PM</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-tag me-1"></i>Booking Type *
                                        </label>
                                        <select class="form-control" name="booking_type" required>
                                            <option value="">Select Type</option>
                                            <option value="site_visit">Site Visit</option>
                                            <option value="virtual_tour">Virtual Tour</option>
                                            <option value="consultation">Consultation</option>
                                            <option value="purchase_intent">Purchase Intent</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-money-bill me-1"></i>Budget Range
                                        </label>
                                        <select class="form-control" name="budget_range">
                                            <option value="">Select Budget</option>
                                            <option value="20-50L">₹20L - ₹50L</option>
                                            <option value="50L-1Cr">₹50L - ₹1Cr</option>
                                            <option value="1Cr-2Cr">₹1Cr - ₹2Cr</option>
                                            <option value="2Cr+">₹2Cr+</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="financing_needed" id="financing_needed">
                                    <label class="form-check-label" for="financing_needed">
                                        <i class="fas fa-percentage me-1"></i>I need financing assistance
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-comments me-1"></i>Special Requirements (Optional)
                                </label>
                                <textarea class="form-control" name="special_requirements" rows="3"
                                          placeholder="Any specific requirements or questions..."></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-book btn-lg">
                                    <i class="fab fa-whatsapp me-2"></i>Book Visit & Get WhatsApp Confirmation
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    By booking, you agree to receive WhatsApp notifications and updates about your visit.
                                </small>
                            </div>
                        </form>
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
                            <strong>Email:</strong> support@apsdreamhomes.com
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="visit_date"]').setAttribute('min', today);
        });
    </script>
</body>
</html>

