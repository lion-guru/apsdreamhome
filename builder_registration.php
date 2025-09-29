<?php
/**
 * Builder Registration System - Developer Partner Program
 * APS Dream Homes - Builder Onboarding for New Projects
 */

session_start();
require_once '../includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = trim($_POST['company_name']);
    $contact_person = trim($_POST['contact_person']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $company_type = trim($_POST['company_type']);
    $established_year = trim($_POST['established_year']);
    $total_projects = trim($_POST['total_projects']);
    $ongoing_projects = trim($_POST['ongoing_projects']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $terms_accepted = isset($_POST['terms_accepted']) ? 1 : 0;

    // Validation
    if (empty($company_name) || empty($contact_person) || empty($mobile) || empty($email) || empty($password)) {
        $message = "Please fill all required fields!";
        $message_type = "danger";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match!";
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
        // Check if mobile or email already exists
        $check_stmt = $conn->prepare("SELECT id FROM builders WHERE mobile = ? OR email = ?");
        $check_stmt->bind_param("ss", $mobile, $email);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $message = "Mobile number or email already registered!";
            $message_type = "danger";
        } else {
            // Generate unique builder code
            $builder_code = 'BLD' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new builder
            $stmt = $conn->prepare("INSERT INTO builders (builder_code, company_name, contact_person, mobile, email, company_type, established_year, total_projects, ongoing_projects, city, state, password, status, registration_date, total_units_sold, total_revenue) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), 0, 0.00)");
            $stmt->bind_param("ssssssssssss", $builder_code, $company_name, $contact_person, $mobile, $email, $company_type, $established_year, $total_projects, $ongoing_projects, $city, $state, $hashed_password);

            if ($stmt->execute()) {
                // Send welcome WhatsApp
                $whatsapp_message = "🎉 Welcome to APS Dream Homes!\n\n";
                $whatsapp_message .= "🏗️ Builder Registration Successful\n";
                $whatsapp_message .= "🔢 Your Builder Code: " . $builder_code . "\n";
                $whatsapp_message .= "🏢 Company: " . $company_name . "\n";
                $whatsapp_message .= "👨‍💼 Contact Person: " . $contact_person . "\n";
                $whatsapp_message .= "📱 Mobile: " . $mobile . "\n";
                $whatsapp_message .= "📧 Email: " . $email . "\n\n";

                $whatsapp_message .= "📊 Company Details:\n";
                $whatsapp_message .= "🏪 Type: " . $company_type . "\n";
                $whatsapp_message .= "📅 Established: " . $established_year . "\n";
                $whatsapp_message .= "🏠 Total Projects: " . $total_projects . "\n";
                $whatsapp_message .= "🚧 Ongoing: " . $ongoing_projects . "\n";
                $whatsapp_message .= "📍 Location: " . $city . ", " . $state . "\n\n";

                $whatsapp_message .= "🔐 Your Login Credentials:\n";
                $whatsapp_message .= "Builder Code: " . $builder_code . "\n";
                $whatsapp_message .= "Password: " . $password . "\n\n";

                $whatsapp_message .= "You can login using:\n";
                $whatsapp_message .= "• Builder Code + Password\n";
                $whatsapp_message .= "• Mobile + Password\n";
                $whatsapp_message .= "• Email + Password\n\n";

                $whatsapp_message .= "📞 Contact: +91-9876543210\n";
                $whatsapp_message .= "🌐 Portal: http://localhost/apsdreamhomefinal/builder_login.php\n\n";
                $whatsapp_message .= "List your projects and reach thousands of buyers! 🏠✨";

                sendWhatsAppNotification($mobile, $whatsapp_message);

                $message = "Registration successful! Your Builder Code is: <strong>" . $builder_code . "</strong>. WhatsApp notification sent with login details. Your account is under review.";
                $message_type = "success";

                // Clear form data
                $_POST = array();
            } else {
                $message = "Registration failed. Please try again!";
                $message_type = "danger";
            }
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

// Get current year for established year dropdown
$current_year = date('Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Builder Registration - Developer Partner Program - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .registration-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 1000px;
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
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-register:hover {
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
        .partnership-info {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0 10px 10px 0;
        }
        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-right: 1rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="registration-container">
                    <!-- Header Section -->
                    <div class="header-section">
                        <div class="logo-section d-inline-block">
                            <h3 class="mb-0">
                                <i class="fas fa-home me-2"></i>
                                APS DREAM HOMES
                            </h3>
                            <p class="mb-0">Developer Partner Program</p>
                        </div>
                        <h1 class="mb-2">Builder Registration</h1>
                        <p class="mb-0">Join India's Premier Real Estate Network</p>
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
                            <h6><i class="fas fa-info-circle me-2"></i>Developer Program Information</h6>
                            <small>
                                🤝 Partner with us to showcase your projects<br>
                                🏗️ List new developments and under-construction properties<br>
                                🏠 Reach thousands of potential buyers<br>
                                💰 Competitive commission structure<br>
                                📊 Advanced analytics and lead management
                            </small>
                        </div>

                        <!-- Partnership Information -->
                        <div class="partnership-info">
                            <h6><i class="fas fa-handshake me-2"></i>Partnership Benefits</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h5 class="text-success">🎯 Premium Visibility</h5>
                                        <p class="mb-0">Featured listings</p>
                                        <small>Priority placement</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h5 class="text-primary">📈 Lead Generation</h5>
                                        <p class="mb-0">Quality leads</p>
                                        <small>Verified buyers</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h5 class="text-warning">💼 Marketing Support</h5>
                                        <p class="mb-0">Digital marketing</p>
                                        <small>Social media promotion</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Benefits Section -->
                        <div class="benefits-section">
                            <h6 class="mb-3"><i class="fas fa-star me-2"></i>Builder Benefits</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <small>Unlimited project listings</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <small>Direct customer interaction</small>
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
                                        <small>Mobile dashboard</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <small>Commission tracking</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-headset"></i>
                                        </div>
                                        <small>Dedicated support</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Registration Form -->
                        <form method="POST" id="builderRegistrationForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-building me-1"></i>Company Name *
                                        </label>
                                        <input type="text" class="form-control" name="company_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-user me-1"></i>Contact Person *
                                        </label>
                                        <input type="text" class="form-control" name="contact_person" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-mobile-alt me-1"></i>Mobile Number *
                                        </label>
                                        <input type="tel" class="form-control" name="mobile" pattern="[0-9]{10}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-envelope me-1"></i>Email Address *
                                        </label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-industry me-1"></i>Company Type *
                                        </label>
                                        <select class="form-control" name="company_type" required>
                                            <option value="">Select Type</option>
                                            <option value="private_limited">Private Limited</option>
                                            <option value="limited_liability">LLP</option>
                                            <option value="partnership">Partnership</option>
                                            <option value="proprietorship">Proprietorship</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-calendar me-1"></i>Established Year *
                                        </label>
                                        <select class="form-control" name="established_year" required>
                                            <option value="">Select Year</option>
                                            <?php for ($year = $current_year; $year >= 1950; $year--): ?>
                                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-home me-1"></i>Total Projects Completed
                                        </label>
                                        <input type="number" class="form-control" name="total_projects" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-tools me-1"></i>Ongoing Projects
                                        </label>
                                        <input type="number" class="form-control" name="ongoing_projects" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-map-marker-alt me-1"></i>City *
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

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-lock me-1"></i>Password *
                                        </label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-lock me-1"></i>Confirm Password *
                                        </label>
                                        <input type="password" class="form-control" name="confirm_password" required>
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
                                <button type="submit" class="btn btn-register btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Register as Builder
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    Already have an account? <a href="builder_login.php">Login here</a>
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
                                <p>Terms and conditions for builders...</p>
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
                                <p>Privacy policy for builders...</p>
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
                            <strong>Email:</strong> builders@apsdreamhomes.com
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
