<?php
/**
 * Unified Registration System with MLM Referral
 * APS Dream Homes - Multi-Role Registration
 * Supports: customer, agent, associate, builder, investor
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

$message = '';
$message_type = '';
$referrer_info = null;
$referral_code = $_GET['ref'] ?? '';

// Get referrer details
if ($referral_code) {
    $stmt = $conn->prepare("SELECT u.id, u.name, mp.referral_code, mp.user_type, mp.current_level FROM users u JOIN mlm_profiles mp ON u.id = mp.user_id WHERE mp.referral_code = ? AND mp.status = 'active'");
    $stmt->bind_param("s", $referral_code);
    $stmt->execute();
    $referrer_info = $stmt->get_result()->fetch_assoc();
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'] ?? 'customer';
    $referrer_code = trim($_POST['referrer_code']) ?? null;
    
    // Validation
    $errors = [];
    
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($mobile) || strlen($mobile) != 10) $errors[] = "Valid 10-digit mobile is required";
    if (empty($password) || strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    if (empty($user_type)) $errors[] = "User type is required";
    
    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $errors[] = "Email already registered";
    }
    
    // Check if mobile already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE mobile = ?");
    $check_stmt->bind_param("s", $mobile);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $errors[] = "Mobile already registered";
    }
    
    // Verify referrer code if provided
    $sponsor_id = null;
    if ($referrer_code) {
        $stmt = $conn->prepare("SELECT user_id FROM mlm_profiles WHERE referral_code = ? AND status = 'active'");
        $stmt->bind_param("s", $referrer_code);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if (!$result) {
            $errors[] = "Invalid referrer code";
        } else {
            $sponsor_id = $result['user_id'];
        }
    }
    
    if (empty($errors)) {
        // Generate unique referral code
        $referral_code = generateReferralCode($full_name, $email);
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (name, email, mobile, password, type, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");
        $stmt->bind_param("sssss", $full_name, $email, $mobile, $hashed_password, $user_type);
        
        if ($stmt->execute()) {
            $new_user_id = $conn->insert_id;
            
            // Create MLM profile
            $stmt = $conn->prepare("INSERT INTO mlm_profiles (user_id, referral_code, sponsor_user_id, sponsor_code, user_type, verification_status, status) VALUES (?, ?, ?, ?, ?, 'verified', 'active')");
            $stmt->bind_param("isis", $new_user_id, $referral_code, $sponsor_id, $referrer_code);
            $stmt->execute();
            
            // Create referral record
            if ($sponsor_id) {
                $stmt = $conn->prepare("INSERT INTO mlm_referrals (referrer_user_id, referred_user_id, referral_type, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iis", $sponsor_id, $new_user_id, $user_type);
                $stmt->execute();
                
                // Update sponsor's direct referrals
                $conn->query("UPDATE mlm_profiles SET direct_referrals = direct_referrals + 1 WHERE user_id = {$sponsor_id}");
                
                // Build network tree
                buildNetworkTree($conn, $new_user_id, $sponsor_id);
            }
            
            // Auto-login
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $user_type;
            
            // Redirect based on user type
            $redirect_url = BASE_URL;
            switch ($user_type) {
                case 'admin':
                    $redirect_url .= 'admin/';
                    break;
                case 'agent':
                    $redirect_url .= 'agent/dashboard';
                    break;
                case 'associate':
                    $redirect_url .= 'associate/dashboard';
                    break;
                case 'builder':
                    $redirect_url .= 'builder/dashboard';
                    break;
                default:
                    $redirect_url .= 'dashboard';
            }
            
            header("Location: {$redirect_url}");
            exit();
        } else {
            $errors[] = "Registration failed";
        }
    }
}

// Helper function to generate referral codes
function generateReferralCode($name, $email) {
    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
    $suffix = strtoupper(substr(md5($email . time()), 0, 4));
    return $prefix . $suffix;
}

// Helper function to build network tree
function buildNetworkTree($conn, $user_id, $sponsor_id) {
    $level = 1;
    $current = $sponsor_id;
    
    while ($current) {
        $stmt = $conn->prepare("INSERT INTO mlm_network_tree (ancestor_user_id, descendant_user_id, level, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iii", $current, $user_id, $level);
        $stmt->execute();
        
        // Get next ancestor
        $stmt = $conn->prepare("SELECT sponsor_user_id FROM mlm_profiles WHERE user_id = ?");
        $stmt->bind_param("i", $current);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result && $result['sponsor_user_id']) {
            $current = $result['sponsor_user_id'];
            $level++;
        } else {
            break;
        }
    }
}

// Get Indian states for dropdown
$indian_states = [
    'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat', 
    'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 
    'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 
    'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 
    'Uttarakhand', 'West Bengal', 'Delhi', 'Jammu and Kashmir', 'Ladakh'
];

// Get user types for dropdown
$user_types = [
    'customer' => 'Customer (Property Buyer)',
    'agent' => 'Real Estate Agent',
    'associate' => 'MLM Associate',
    'builder' => 'Property Builder',
    'investor' => 'Property Investor'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - APS Dream Homes | MLM Referral System</title>
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
        }
        .header-section {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        .logo-section {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .referrer-info {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .role-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
            cursor: pointer;
        }
        .role-card:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }
        .role-card.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }
        .dynamic-fields {
            display: none;
        }
        .dynamic-fields.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="registration-container">
                    <!-- Header Section -->
                    <div class="header-section">
                        <div class="logo-section d-inline-block">
                            <h2 class="mb-0">
                                <i class="fas fa-home me-2"></i>
                                APS DREAM HOMES
                            </h2>
                            <p class="mb-0">Join Our MLM Network</p>
                        </div>
                        <h3 class="mt-3">Unified Registration</h3>
                        <p class="mb-0">Register once, refer forever</p>
                    </div>

                    <div class="p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($referrer_info): ?>
                            <div class="referrer-info">
                                <h5><i class="fas fa-user-friends me-2"></i>Referred by</h5>
                                <p class="mb-1"><strong><?= htmlspecialchars($referrer_info['name']) ?></strong></p>
                                <p class="mb-0">Code: <?= htmlspecialchars($referrer_info['referral_code']) ?></p>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="registrationForm" novalidate>
                            <!-- Basic Information -->
                            <h5 class="mb-3 text-primary">Basic Information</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mobile Number *</label>
                                    <input type="tel" class="form-control" name="mobile" value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>" pattern="[0-9]{10}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Referral Code</label>
                                    <input type="text" class="form-control" name="referrer_code" value="<?= htmlspecialchars($referral_code) ?>" placeholder="Optional">
                                </div>
                            </div>

                            <!-- User Type Selection -->
                            <h5 class="mb-3 text-primary">Register As</h5>
                            
                            <div class="row">
                                <?php foreach ($user_types as $type => $label): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="role-card" data-role="<?= $type ?>">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="fas fa-<?= getRoleIcon($type) ?> fa-2x text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1"><?= $label ?></h6>
                                                    <small class="text-muted"><?= getRoleDescription($type) ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <input type="hidden" name="user_type" id="user_type" value="customer">

                            <!-- Dynamic Fields Based on Role -->
                            <div id="dynamicFields">
                                <!-- Customer Fields -->
                                <div class="dynamic-fields" id="customer_fields">
                                    <h5 class="mb-3 text-primary">Customer Preferences</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Budget Range</label>
                                            <select class="form-control" name="budget_range">
                                                <option value="">Select Budget</option>
                                                <option value="0-10">₹0-10 Lakh</option>
                                                <option value="10-25">₹10-25 Lakh</option>
                                                <option value="25-50">₹25-50 Lakh</option>
                                                <option value="50-100">₹50-100 Lakh</option>
                                                <option value="100+">₹1 Crore+</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Property Type</label>
                                            <select class="form-control" name="property_type">
                                                <option value="">Select Type</option>
                                                <option value="apartment">Apartment</option>
                                                <option value="house">House</option>
                                                <option value="plot">Plot</option>
                                                <option value="commercial">Commercial</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Agent Fields -->
                                <div class="dynamic-fields" id="agent_fields">
                                    <h5 class="mb-3 text-primary">Agent Information</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">License Number</label>
                                            <input type="text" class="form-control" name="license_number" placeholder="Real Estate License">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Experience (Years)</label>
                                            <input type="number" class="form-control" name="experience" min="0" max="50">
                                        </div>
                                    </div>
                                </div>

                                <!-- Associate Fields -->
                                <div class="dynamic-fields" id="associate_fields">
                                    <h5 class="mb-3 text-primary">Associate Details</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">PAN Number</label>
                                            <input type="text" class="form-control" name="pan_number" style="text-transform: uppercase;">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Aadhar Number</label>
                                            <input type="text" class="form-control" name="aadhar_number" pattern="[0-9]{12}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Builder Fields -->
                                <div class="dynamic-fields" id="builder_fields">
                                    <h5 class="mb-3 text-primary">Builder Information</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Company Name</label>
                                            <input type="text" class="form-control" name="company_name" placeholder="Your Company Name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">RERA Registration</label>
                                            <input type="text" class="form-control" name="rera_registration" placeholder="RERA Registration Number">
                                        </div>
                                    </div>
                                </div>

                                <!-- Investor Fields -->
                                <div class="dynamic-fields" id="investor_fields">
                                    <h5 class="mb-3 text-primary">Investment Preferences</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Investment Range</label>
                                            <select class="form-control" name="investment_range">
                                                <option value="">Select Range</option>
                                                <option value="10-50">₹10-50 Lakh</option>
                                                <option value="50-100">₹50-100 Lakh</option>
                                                <option value="100-500">₹1-5 Crore</option>
                                                <option value="500+">₹5 Crore+</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Investment Type</label>
                                            <select class="form-control" name="investment_type">
                                                <option value="">Select Type</option>
                                                <option value="residential">Residential</option>
                                                <option value="commercial">Commercial</option>
                                                <option value="mixed">Mixed Portfolio</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Security -->
                            <h5 class="mb-3 text-primary">Account Security</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password *</label>
                                    <input type="password" class="form-control" name="password" minlength="6" required>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" name="confirm_password" minlength="6" required>
                                </div>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms & Conditions</a> and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-register btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Register Now
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <p class="text-muted">
                                Already have an account? 
                                <a href="<?= BASE_URL ?>login" class="text-primary text-decoration-none">Login here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
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
                    <h6>MLM Network Agreement</h6>
                    <ul>
                        <li>All referrals must be genuine and legitimate</li>
                        <li>Commission payments are subject to successful property sales</li>
                        <li>Users must maintain professional conduct</li>
                        <li>Company reserves right to modify commission structure</li>
                        <li>All data is protected under privacy policy</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Role selection
        document.querySelectorAll('.role-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                
                const role = this.dataset.role;
                document.getElementById('user_type').value = role;
                
                // Show/hide dynamic fields
                document.querySelectorAll('.dynamic-fields').forEach(field => field.classList.remove('active'));
                document.getElementById(role + '_fields').classList.add('active');
            });
        });

        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            const mobile = document.querySelector('input[name="mobile"]').value;
            if (mobile.length !== 10 || !/^\d+$/.test(mobile)) {
                e.preventDefault();
                alert('Please enter a valid 10-digit mobile number!');
                return false;
            }
        });
    </script>

    <?php
    function getRoleIcon($type) {
        $icons = [
            'customer' => 'user',
            'agent' => 'user-tie',
            'associate' => 'users',
            'builder' => 'building',
            'investor' => 'chart-line'
        ];
        return $icons[$type] ?? 'user';
    }

    function getRoleDescription($type) {
        $descriptions = [
            'customer' => 'Buy properties and earn referral rewards',
            'agent' => 'Sell properties and earn commissions',
            'associate' => 'Build network and earn multi-level commissions',
            'builder' => 'List properties and manage sales',
            'investor' => 'Invest in properties and earn returns'
        ];
        return $descriptions[$type] ?? 'Standard user';
    }
    ?>
</body>
</html>