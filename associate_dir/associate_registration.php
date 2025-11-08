<?php
/**
 * Enhanced Associate Registration System
 * APS Dream Homes - MLM Associate Onboarding
 * Based on Company Payout Structure and Team Building
 */

session_start();
require_once '../includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

$message = '';
$message_type = '';
$referrer_info = null;

// Check if referrer code is provided
if (isset($_GET['ref'])) {
    $referrer_code = trim($_GET['ref']);
    $stmt = $conn->prepare("SELECT id, full_name, mobile, current_level, total_team_size FROM mlm_agents WHERE referral_code = ? AND status = 'active'");
    $stmt->bind_param("s", $referrer_code);
    $stmt->execute();
    $referrer_info = $stmt->get_result()->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $aadhar_number = trim($_POST['aadhar_number']);
    $pan_number = trim($_POST['pan_number']);
    $address = trim($_POST['address']);
    $state = trim($_POST['state']);
    $district = trim($_POST['district']);
    $pin_code = trim($_POST['pin_code']);
    $ifsc_code = trim($_POST['ifsc_code']);
    $referrer_code = trim($_POST['referrer_code']) ?: null;
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validation
    if (empty($full_name) || empty($mobile) || empty($email) || empty($password)) {
        $message = "Please fill all required fields!";
        $message_type = "danger";
    } elseif (empty($referrer_code)) {
        $message = "Referral code is mandatory for associate registration!";
        $message_type = "danger";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match!";
        $message_type = "danger";
    } elseif (strlen($mobile) != 10) {
        $message = "Mobile number must be 10 digits!";
        $message_type = "danger";
    } elseif (!preg_match('/^[A-Za-z0-9]{4,8}$/', $referrer_code)) {
        $message = "Referral code must be alphanumeric and between 4-8 characters!";
        $message_type = "danger";
    } else {
        // Check if mobile or email already exists
        $check_stmt = $conn->prepare("SELECT id FROM mlm_agents WHERE mobile = ? OR email = ?");
        $check_stmt->bind_param("ss", $mobile, $email);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $message = "Mobile number or email already registered!";
            $message_type = "danger";
        } else {
            // Generate unique referral code
            $referral_code = 'APS' . strtoupper(substr($full_name, 0, 2)) . rand(1000, 9999);
            
            // Verify referral code exists if provided
            $referrer_id = null;
            if ($referrer_code) {
                $ref_stmt = $conn->prepare("SELECT id FROM mlm_agents WHERE referral_code = ? AND status = 'active'");
                $ref_stmt->bind_param("s", $referrer_code);
                $ref_stmt->execute();
                $ref_result = $ref_stmt->get_result()->fetch_assoc();
                if (!$ref_result) {
                    $message = "Invalid referrer code!";
                    $message_type = "danger";
                } else {
                    $referrer_id = $ref_result['id'];
                }
            }
            
            if (!$message) {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new associate
                $stmt = $conn->prepare("INSERT INTO mlm_agents (full_name, mobile, email, aadhar_number, pan_number, address, state, district, pin_code, bank_account, ifsc_code, referral_code, sponsor_id, password, current_level, status, registration_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Associate', 'pending', NOW())");
                $stmt->bind_param("ssssssssssssis", $full_name, $mobile, $email, $aadhar_number, $pan_number, $address, $state, $district, $pin_code, $bank_account, $ifsc_code, $referral_code, $referrer_id, $hashed_password);
                
                if ($stmt->execute()) {
                    $new_agent_id = $conn->insert_id;
                    
                    // Update referrer's team count if exists
                    if ($referrer_id) {
                        $update_stmt = $conn->prepare("UPDATE mlm_agents SET total_team_size = total_team_size + 1, direct_referrals = direct_referrals + 1 WHERE id = ?");
                        $update_stmt->bind_param("i", $referrer_id);
                        $update_stmt->execute();
                        
                        // Create commission record for referrer (if applicable)
                        $commission_stmt = $conn->prepare("INSERT INTO mlm_commissions (agent_id, earned_from_agent_id, commission_type, amount, level, transaction_date, status) VALUES (?, ?, 'referral_bonus', 500.00, 1, NOW(), 'pending')");
                        $commission_stmt->bind_param("ii", $referrer_id, $new_agent_id);
                        $commission_stmt->execute();
                    }
                    
                    // Send welcome SMS/Email (placeholder)
                    // sendWelcomeSMS($mobile, $full_name, $referral_code);
                    
                    $message = "Registration successful! Your referral code is: <strong>$referral_code</strong>. Please save it for future reference. Your account is under review and will be activated within 24 hours.";
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
}

// Get Indian states for dropdown
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
    <title>Associate Registration - APS Dream Homes</title>
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
        .payout-structure {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .level-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 0.25rem;
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin: 0 auto 1rem;
        }
        .benefits-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
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
                            <h2 class="mb-0">
                                <i class="fas fa-home me-2"></i>
                                APS DREAM HOMES
                            </h2>
                            <p class="mb-0">आपका साथी</p>
                        </div>
                        <h1 class="mb-2">Associate Registration</h1>
                        <p class="mb-0">Join India's Premier Real Estate Network</p>
                        <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Singhariya Chauraha, Near Ganpati Lawn, Gorakhpur</p>
                    </div>

                    <div class="p-4">
                        <!-- Messages -->
                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Referrer Information -->
                        <?php if ($referrer_info): ?>
                        <div class="referrer-info">
                            <h5 class="mb-3">
                                <i class="fas fa-user-friends me-2"></i>
                                Referred by Sponsor
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Name:</strong> <?php echo htmlspecialchars($referrer_info['full_name']); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Level:</strong> <?php echo htmlspecialchars($referrer_info['current_level']); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Mobile:</strong> <?php echo htmlspecialchars($referrer_info['mobile']); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Team Size:</strong> <?php echo $referrer_info['total_team_size']; ?> Members
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Payout Structure -->
                        <div class="payout-structure">
                            <h4 class="text-center mb-4">
                                <i class="fas fa-trophy me-2"></i>
                                APS Associate Payout System
                            </h4>
                            <div class="row text-center">
                                <div class="col-md-2 mb-3">
                                    <div class="level-badge">Associate</div>
                                    <div><strong>0-10L</strong></div>
                                    <div>5% Commission</div>
                                    <div><i class="fas fa-mobile-alt"></i> Mobile</div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="level-badge">Sr. Associate</div>
                                    <div><strong>10-35L</strong></div>
                                    <div>7% Commission</div>
                                    <div><i class="fas fa-tablet-alt"></i> Tablet</div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="level-badge">BDM</div>
                                    <div><strong>35-70L</strong></div>
                                    <div>10% Commission</div>
                                    <div><i class="fas fa-laptop"></i> Laptop</div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="level-badge">Sr. BDM</div>
                                    <div><strong>70L-1.5Cr</strong></div>
                                    <div>12% Commission</div>
                                    <div><i class="fas fa-plane"></i> Tour</div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="level-badge">VP</div>
                                    <div><strong>1.5-3Cr</strong></div>
                                    <div>15% Commission</div>
                                    <div><i class="fas fa-motorcycle"></i> Bike</div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="level-badge">President</div>
                                    <div><strong>3-5Cr</strong></div>
                                    <div>18% Commission</div>
                                    <div><i class="fas fa-car"></i> Car</div>
                                </div>
                            </div>
                        </div>

                        <!-- Benefits Section -->
                        <div class="benefits-section">
                            <h4 class="text-center mb-4">Why Join APS Dream Homes?</h4>
                            <div class="row">
                                <div class="col-md-3 text-center mb-4">
                                    <div class="feature-icon">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <h6>High Commissions</h6>
                                    <p class="text-muted">Up to 20% commission on sales</p>
                                </div>
                                <div class="col-md-3 text-center mb-4">
                                    <div class="feature-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h6>Team Building</h6>
                                    <p class="text-muted">Build your own sales network</p>
                                </div>
                                <div class="col-md-3 text-center mb-4">
                                    <div class="feature-icon">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                    <h6>Amazing Rewards</h6>
                                    <p class="text-muted">Mobile, Laptop, Car & more</p>
                                </div>
                                <div class="col-md-3 text-center mb-4">
                                    <div class="feature-icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <h6>Career Growth</h6>
                                    <p class="text-muted">Unlimited growth opportunities</p>
                                </div>
                            </div>
                        </div>

                        <!-- Registration Form -->
                        <form method="POST" class="row g-3">
                            <div class="col-12">
                                <h4 class="text-primary mb-3">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Registration Details
                                </h4>
                            </div>

                            <!-- Personal Information -->
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile Number *</label>
                                <input type="tel" class="form-control" name="mobile" value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>" pattern="[0-9]{10}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Aadhar Number</label>
                                <input type="text" class="form-control" name="aadhar_number" value="<?php echo htmlspecialchars($_POST['aadhar_number'] ?? ''); ?>" pattern="[0-9]{12}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">PAN Number</label>
                                <input type="text" class="form-control" name="pan_number" value="<?php echo htmlspecialchars($_POST['pan_number'] ?? ''); ?>" style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Referrer Code</label>
                                <input type="text" class="form-control" name="referrer_code" value="<?php echo htmlspecialchars($_GET['ref'] ?? $_POST['referrer_code'] ?? ''); ?>" placeholder="Optional">
                            </div>

                            <!-- Address Information -->
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <select class="form-control" name="state">
                                    <option value="">Select State</option>
                                    <?php foreach ($indian_states as $state): ?>
                                    <option value="<?php echo $state; ?>" <?php echo (($_POST['state'] ?? '') == $state) ? 'selected' : ''; ?>>
                                        <?php echo $state; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">District</label>
                                <input type="text" class="form-control" name="district" value="<?php echo htmlspecialchars($_POST['district'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">PIN Code</label>
                                <input type="text" class="form-control" name="pin_code" value="<?php echo htmlspecialchars($_POST['pin_code'] ?? ''); ?>" pattern="[0-9]{6}">
                            </div>

                            <!-- Bank Information -->
                            <div class="col-md-6">
                                <label class="form-label">Bank Account Number</label>
                                <input type="text" class="form-control" name="bank_account" value="<?php echo htmlspecialchars($_POST['bank_account'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IFSC Code</label>
                                <input type="text" class="form-control" name="ifsc_code" value="<?php echo htmlspecialchars($_POST['ifsc_code'] ?? ''); ?>" style="text-transform: uppercase;">
                            </div>

                            <!-- Password Information -->
                            <div class="col-md-6">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password" required minlength="6">
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" name="confirm_password" required minlength="6">
                            </div>

                            <!-- Terms and Submit -->
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms & Conditions</a> and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-register btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Register as Associate
                                </button>
                            </div>

                            <div class="col-12 text-center mt-3">
                                <p class="text-muted">
                                    Already have an account? 
                                    <a href="associate_login.php" class="text-primary text-decoration-none">Login here</a>
                                </p>
                            </div>
                        </form>
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
                    <h6>Associate Agreement</h6>
                    <ul>
                        <li>Associates must maintain professional conduct while representing APS Dream Homes</li>
                        <li>All commission payments are subject to successful property sales and documentation</li>
                        <li>Associates are responsible for following all legal and regulatory requirements</li>
                        <li>Misrepresentation of facts or fraudulent activities will result in immediate termination</li>
                        <li>Commission structure may be revised based on performance and company policies</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
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

        // Auto-format inputs
        document.querySelector('input[name="pan_number"]').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        document.querySelector('input[name="ifsc_code"]').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    </script>
</body>
</html>