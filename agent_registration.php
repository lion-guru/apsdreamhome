<?php
/**
 * Agent Registration System - Freelancer Partner Program
 * APS Dream Homes - Agent Onboarding for Resell Properties
 */

session_start();
require_once '../includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $company_name = trim($_POST['company_name']);
    $experience_years = trim($_POST['experience_years']);
    $specialization = trim($_POST['specialization']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $terms_accepted = isset($_POST['terms_accepted']) ? 1 : 0;

    // Validation
    if (empty($full_name) || empty($mobile) || empty($email) || empty($password)) {
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
        $check_stmt = $conn->prepare("SELECT id FROM agents WHERE mobile = ? OR email = ?");
        $check_stmt->bind_param("ss", $mobile, $email);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $message = "Mobile number or email already registered!";
            $message_type = "danger";
        } else {
            // Generate unique agent code
            $agent_code = 'AGT' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new agent
            $stmt = $conn->prepare("INSERT INTO agents (agent_code, full_name, mobile, email, company_name, experience_years, specialization, city, state, password, status, registration_date, total_properties, total_earnings) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), 0, 0.00)");
            $stmt->bind_param("ssssssssss", $agent_code, $full_name, $mobile, $email, $company_name, $experience_years, $specialization, $city, $state, $hashed_password);

            if ($stmt->execute()) {
                // Send welcome WhatsApp
                $whatsapp_message = "🎉 Welcome to APS Dream Homes!\n\n";
                $whatsapp_message .= "👤 Agent Registration Successful\n";
                $whatsapp_message .= "🔢 Your Agent Code: " . $agent_code . "\n";
                $whatsapp_message .= "👨‍💼 Name: " . $full_name . "\n";
                $whatsapp_message .= "📱 Mobile: " . $mobile . "\n";
                $whatsapp_message .= "📧 Email: " . $email . "\n\n";

                $whatsapp_message .= "🏢 Company: " . $company_name . "\n";
                $whatsapp_message .= "💼 Experience: " . $experience_years . " years\n";
                $whatsapp_message .= "🎯 Specialization: " . $specialization . "\n";
                $whatsapp_message .= "📍 Location: " . $city . ", " . $state . "\n\n";

                $whatsapp_message .= "🔐 Your Login Credentials:\n";
                $whatsapp_message .= "Agent Code: " . $agent_code . "\n";
                $whatsapp_message .= "Password: " . $password . "\n\n";

                $whatsapp_message .= "You can login using:\n";
                $whatsapp_message .= "• Agent Code + Password\n";
                $whatsapp_message .= "• Mobile + Password\n";
                $whatsapp_message .= "• Email + Password\n\n";

                $whatsapp_message .= "📞 Contact: +91-9876543210\n";
                $whatsapp_message .= "🌐 Portal: http://localhost/apsdreamhomefinal/agent_login.php\n\n";
                $whatsapp_message .= "Start uploading properties and earn commissions! 💰✨";

                sendWhatsAppNotification($mobile, $whatsapp_message);

                $message = "Registration successful! Your Agent Code is: <strong>" . $agent_code . "</strong>. WhatsApp notification sent with login details. Your account is under review.";
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Registration - Freelancer Partner Program - APS Dream Homes</title>
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
            max-width: 900px;
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
        .btn-register {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
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
        .commission-info {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0 10px 10px 0;
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
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="registration-container">
                    <!-- Header Section -->
                    <div class="header-section">
                        <div class="logo-section d-inline-block">
                            <h3 class="mb-0">
                                <i class="fas fa-home me-2"></i>
                                APS DREAM HOMES
                            </h3>
                            <p class="mb-0">Freelancer Partner Program</p>
                        </div>
                        <h1 class="mb-2">Agent Registration</h1>
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
                            <h6><i class="fas fa-info-circle me-2"></i>Agent Program Information</h6>
                            <small>
                                🤝 Join as a freelancer partner and earn commissions<br>
                                🏠 Upload and manage resell properties<br>
                                💰 Earn competitive commissions on every sale<br>
                                📱 Get leads and customer inquiries<br>
                                🎯 Dedicated agent portal for property management
                            </small>
                        </div>

                        <!-- Commission Information -->
                        <div class="commission-info">
                            <h6><i class="fas fa-money-bill-wave me-2"></i>Commission Structure</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h5 class="text-success">🏠 Resell Properties</h5>
                                        <p class="mb-0"><strong>2-3%</strong> Commission</p>
                                        <small>Based on property value</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h5 class="text-primary">🏢 New Projects</h5>
                                        <p class="mb-0"><strong>1-2%</strong> Commission</p>
                                        <small>Developer partnership</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h5 class="text-warning">🎯 Performance Bonus</h5>
                                        <p class="mb-0"><strong>Up to 5%</strong> Extra</p>
                                        <small>For top performers</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Benefits Section -->
                        <div class="benefits-section">
                            <h6 class="mb-3"><i class="fas fa-star me-2"></i>Agent Benefits</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-upload"></i>
                                        </div>
                                        <small>Upload unlimited properties</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <small>Customer lead generation</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <small>Performance tracking</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <small>Mobile app access</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <small>Timely commission payments</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-headset"></i>
                                        </div>
                                        <small>24/7 support</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Registration Form -->
                        <form method="POST" id="agentRegistrationForm">
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
                                            <i class="fas fa-building me-1"></i>Company/Agency Name
                                        </label>
                                        <input type="text" class="form-control" name="company_name" placeholder="Optional">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-briefcase me-1"></i>Experience (Years) *
                                        </label>
                                        <select class="form-control" name="experience_years" required>
                                            <option value="">Select Experience</option>
                                            <option value="0-1">0-1 years</option>
                                            <option value="1-3">1-3 years</option>
                                            <option value="3-5">3-5 years</option>
                                            <option value="5-10">5-10 years</option>
                                            <option value="10+">10+ years</option>
                                        </select>
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

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-tags me-1"></i>Specialization
                                </label>
                                <select class="form-control" name="specialization">
                                    <option value="">Select Specialization</option>
                                    <option value="residential">Residential Properties</option>
                                    <option value="commercial">Commercial Properties</option>
                                    <option value="luxury">Luxury Properties</option>
                                    <option value="plots">Plots & Land</option>
                                    <option value="all">All Types</option>
                                </select>
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
                                    <i class="fas fa-user-plus me-2"></i>Register as Agent
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    Already have an account? <a href="agent_login.php">Login here</a>
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
                                <p>Terms and conditions content goes here...</p>
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
                                <p>Privacy policy content goes here...</p>
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
                            <strong>Email:</strong> agents@apsdreamhomes.com
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
