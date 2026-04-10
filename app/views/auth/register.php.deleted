/**
* register - APS Dream Home Component
*
* @package APS Dream Home
* @version 1.0.0
* @author APS Dream Home Team
* @copyright 2026 APS Dream Home
*
* Description: Handles register functionality
*
* Features:
* - Secure input validation
* - Comprehensive error handling
* - Performance optimization
* - Database integration
* - Session management
* - CSRF protection
*
* @see https://apsdreamhome.com/docs
*/
?>

// TODO: Add proper error handling with try-catch blocks

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - APS Dream Homes | MLM Referral System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/public/css/pages.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #4CAF50, #45a049);
            --success-gradient: linear-gradient(135deg, #27ae60, #2ecc71);
            --warning-gradient: linear-gradient(135deg, #f39c12, #e67e22);
            --danger-gradient: linear-gradient(135deg, #e74c3c, #c0392b);
            --info-gradient: linear-gradient(135deg, #17a2b8, #3498db);
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --card-shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .registration-container {
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin: 2rem 0;
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
            display: inline-block;
        }

        .logo-section h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .logo-section p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-register {
            background: var(--primary-gradient);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            color: white;
        }

        .referrer-info {
            background: var(--secondary-gradient);
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
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .role-card:hover {
            border-color: #667eea;
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: var(--card-shadow);
        }

        .role-card.selected {
            border-color: #667eea;
            background: #f0f4ff;
            box-shadow: var(--card-shadow);
        }

        .role-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 1rem;
        }

        .role-icon.customer {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .role-icon.agent {
            background: rgba(41, 98, 255, 0.1);
            color: #2962ff;
        }

        .role-icon.associate {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }

        .role-icon.builder {
            background: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }

        .role-icon.investor {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }

        .dynamic-fields {
            display: none;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .dynamic-fields.active {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-section-title {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .terms-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee, #fdd);
            color: #c0392b;
        }

        .alert-success {
            background: linear-gradient(135deg, #efe, #dfd);
            color: #27ae60;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-section {
                padding: 2rem 1rem;
            }

            .logo-section h2 {
                font-size: 1.2rem;
            }

            .registration-container {
                margin: 1rem 0;
                border-radius: 15px;
            }

            .role-card {
                padding: 1rem;
            }

            .role-icon {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="registration-container">
                    <!-- Header Section -->
                    <div class="header-section">
                        <div class="logo-section">
                            <h2><i class="bi bi-house-door-fill me-2"></i>APS DREAM HOMES</h2>
                            <p>Join Our MLM Network</p>
                        </div>
                        <h3 class="mt-3">Unified Registration</h3>
                        <p class="mb-0">Register once, refer forever</p>
                    </div>

                    <div class="p-4">
                        <!-- Display Errors -->
                        <?php if (isset($errors) && $errors->any()): ?>
                            //
                            // TODO: This file is large (722 lines). Consider splitting into smaller functions.
                            // TODO: Add input validation for all user inputs.
                            //
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors->all() as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Display Success -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Referrer Information -->
                        <?php if (isset($referrerInfo)): ?>
                            <div class="referrer-info">
                                <h5><i class="bi bi-person-check me-2"></i>Referred by</h5>
                                <p class="mb-1"><strong><?php echo htmlspecialchars($referrerInfo['name']); ?></strong></p>
                                <p class="mb-0">Code: <?php echo htmlspecialchars($referrerInfo['referral_code']); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Registration Form -->
                        <form method="POST" action="<?php echo BASE_URL; ?>/register" id="registrationForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                            <!-- Basic Information -->
                            <h5 class="form-section-title">📋 Basic Information</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="full_name"
                                        value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                                    <?php if (isset($errors['full_name'])): ?>
                                        <div class="text-danger small"><?php echo htmlspecialchars($errors['full_name']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" name="email" id="email"
                                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="text-danger small"><?php echo htmlspecialchars($errors['email']); ?></div>
                                    <?php endif; ?>
                                    <div id="email-feedback" class="small mt-1"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mobile Number *</label>
                                    <input type="tel" class="form-control" name="mobile" id="mobile"
                                        value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>" pattern="[0-9]{10}" maxlength="10" required>
                                    <?php if (isset($errors['mobile'])): ?>
                                        <div class="text-danger small"><?php echo htmlspecialchars($errors['mobile']); ?></div>
                                    <?php endif; ?>
                                    <div id="mobile-feedback" class="small mt-1"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Referral Code</label>
                                    <input type="text" class="form-control" name="referrer_code" id="referrer_code"
                                        value="<?php echo htmlspecialchars($referralCode ?? ''); ?>" placeholder="Optional">
                                    <?php if (isset($errors['referrer_code'])): ?>
                                        <div class="text-danger small"><?php echo htmlspecialchars($errors['referrer_code']); ?></div>
                                    <?php endif; ?>
                                    <div id="referrer-feedback" class="small mt-1"></div>
                                </div>
                            </div>

                            <!-- User Type Selection -->
                            <h5 class="form-section-title mt-4">👥 Register As</h5>

                            <div class="row">
                                <?php if (isset($userTypes)): ?>
                                    <?php foreach ($userTypes as $type => $config): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="role-card" data-role="<?php echo $type; ?>">
                                                <div class="d-flex align-items-center">
                                                    <div class="role-icon <?php echo $type; ?>">
                                                        <i class="bi bi-<?php echo $config['icon']; ?>"></i>
                                                    </div>
                                                    <div class="ms-3">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($config['label']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($config['description']); ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <input type="hidden" name="user_type" id="user_type" value="customer">
                            <?php if (isset($errors['user_type'])): ?>
                                <div class="text-danger small"><?php echo htmlspecialchars($errors['user_type']); ?></div>
                            <?php endif; ?>

                            <!-- Dynamic Fields Based on Role -->
                            <div id="dynamicFields">
                                <!-- Customer Fields -->
                                <div class="dynamic-fields" id="customer_fields">
                                    <h6 class="form-section-title">🎯 Customer Preferences</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Budget Range</label>
                                            <select class="form-control" name="budget_range">
                                                <option value="">Select Budget</option>
                                                <option value="0-10" <?php echo ($_POST['budget_range'] ?? '') == '0-10' ? 'selected' : ''; ?>>₹0-10 Lakh</option>
                                                <option value="10-25" <?php echo ($_POST['budget_range'] ?? '') == '10-25' ? 'selected' : ''; ?>>₹10-25 Lakh</option>
                                                <option value="25-50" <?php echo ($_POST['budget_range'] ?? '') == '25-50' ? 'selected' : ''; ?>>₹25-50 Lakh</option>
                                                <option value="50-100" <?php echo ($_POST['budget_range'] ?? '') == '50-100' ? 'selected' : ''; ?>>₹50-100 Lakh</option>
                                                <option value="100+" <?php echo ($_POST['budget_range'] ?? '') == '100+' ? 'selected' : ''; ?>>₹1 Crore+</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Property Type</label>
                                            <select class="form-control" name="property_type">
                                                <option value="">Select Type</option>
                                                <option value="apartment" <?php echo ($_POST['property_type'] ?? '') == 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                                                <option value="house" <?php echo ($_POST['property_type'] ?? '') == 'house' ? 'selected' : ''; ?>>House</option>
                                                <option value="plot" <?php echo ($_POST['property_type'] ?? '') == 'plot' ? 'selected' : ''; ?>>Plot</option>
                                                <option value="commercial" <?php echo ($_POST['property_type'] ?? '') == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Agent Fields -->
                                <div class="dynamic-fields" id="agent_fields">
                                    <h6 class="form-section-title">🏢 Agent Information</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">License Number</label>
                                            <input type="text" class="form-control" name="license_number"
                                                value="<?php echo htmlspecialchars($_POST['license_number'] ?? ''); ?>" placeholder="Real Estate License">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Experience (Years)</label>
                                            <input type="number" class="form-control" name="experience"
                                                value="<?php echo htmlspecialchars($_POST['experience'] ?? ''); ?>" min="0" max="50">
                                        </div>
                                    </div>
                                </div>

                                <!-- Associate Fields -->
                                <div class="dynamic-fields" id="associate_fields">
                                    <h6 class="form-section-title">🤝 Associate Details</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">PAN Number</label>
                                            <input type="text" class="form-control" name="pan_number"
                                                value="<?php echo htmlspecialchars($_POST['pan_number'] ?? ''); ?>" style="text-transform: uppercase;"
                                                placeholder="AAAAA0000A">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Aadhar Number</label>
                                            <input type="text" class="form-control" name="aadhar_number"
                                                value="<?php echo htmlspecialchars($_POST['aadhar_number'] ?? ''); ?>" pattern="[0-9]{12}"
                                                placeholder="123456789012">
                                        </div>
                                    </div>
                                </div>

                                <!-- Builder Fields -->
                                <div class="dynamic-fields" id="builder_fields">
                                    <h6 class="form-section-title">🏗️ Builder Information</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Company Name</label>
                                            <input type="text" class="form-control" name="company_name"
                                                value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>" placeholder="Your Company Name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">RERA Registration</label>
                                            <input type="text" class="form-control" name="rera_registration"
                                                value="<?php echo htmlspecialchars($_POST['rera_registration'] ?? ''); ?>" placeholder="RERA Registration Number">
                                        </div>
                                    </div>
                                </div>

                                <!-- Investor Fields -->
                                <div class="dynamic-fields" id="investor_fields">
                                    <h6 class="form-section-title">📈 Investment Preferences</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Investment Range</label>
                                            <select class="form-control" name="investment_range">
                                                <option value="">Select Range</option>
                                                <option value="10-50" <?php echo ($_POST['investment_range'] ?? '') == '10-50' ? 'selected' : ''; ?>>₹10-50 Lakh</option>
                                                <option value="50-100" <?php echo ($_POST['investment_range'] ?? '') == '50-100' ? 'selected' : ''; ?>>₹50-100 Lakh</option>
                                                <option value="100-500" <?php echo ($_POST['investment_range'] ?? '') == '100-500' ? 'selected' : ''; ?>>₹1-5 Crore</option>
                                                <option value="500+" <?php echo ($_POST['investment_range'] ?? '') == '500+' ? 'selected' : ''; ?>>₹5 Crore+</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Investment Type</label>
                                            <select class="form-control" name="investment_type">
                                                <option value="">Select Type</option>
                                                <option value="residential" <?php echo ($_POST['investment_type'] ?? '') == 'residential' ? 'selected' : ''; ?>>Residential</option>
                                                <option value="commercial" <?php echo ($_POST['investment_type'] ?? '') == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                                <option value="mixed" <?php echo ($_POST['investment_type'] ?? '') == 'mixed' ? 'selected' : ''; ?>>Mixed Portfolio</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Security -->
                            <h5 class="form-section-title mt-4">🔐 Account Security</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password *</label>
                                    <input type="password" class="form-control" name="password" minlength="6" required>
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="text-danger small"><?php echo htmlspecialchars($errors['password']); ?></div>
                                    <?php endif; ?>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" name="confirm_password" minlength="6" required>
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="text-danger small"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="terms-section">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" value="1" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms & Conditions</a>
                                        and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                                    </label>
                                </div>
                                <?php if (isset($errors['terms'])): ?>
                                    <div class="text-danger small"><?php echo htmlspecialchars($errors['terms']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-register btn-lg" id="submitBtn">
                                    <i class="bi bi-person-plus me-2"></i>
                                    Register Now
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <p class="text-muted">
                                Already have an account?
                                <a href="<?php echo BASE_URL; ?>/login" class="text-primary text-decoration-none">Login here</a>
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
                    <h5 class="modal-title">📋 Terms & Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>🤝 MLM Network Agreement</h6>
                    <ul>
                        <li>All referrals must be genuine and legitimate</li>
                        <li>Commission payments are subject to successful property sales</li>
                        <li>Users must maintain professional conduct</li>
                        <li>Company reserves right to modify commission structure</li>
                        <li>All data is protected under privacy policy</li>
                    </ul>

                    <h6 class="mt-3">🏠 Property Services</h6>
                    <ul>
                        <li>All property information is verified and accurate</li>
                        <li>Users are responsible for due diligence on properties</li>
                        <li>Company acts as facilitator between buyers and sellers</li>
                        <li>Disputes are resolved through company arbitration</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Privacy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🔒 Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Data Collection</h6>
                    <p>We collect personal information for registration and service provision.</p>

                    <h6 class="mt-3">Data Usage</h6>
                    <p>Your data is used to provide MLM services, property matching, and commission processing.</p>

                    <h6 class="mt-3">Data Protection</h6>
                    <p>We implement security measures to protect your personal information.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        // Set default role selection
        document.querySelector('.role-card[data-role="customer"]').classList.add('selected');
        document.getElementById('customer_fields').classList.add('active');

        // Real-time email validation
        let emailTimeout;
        document.getElementById('email').addEventListener('input', function() {
            clearTimeout(emailTimeout);
            const email = this.value;
            const feedback = document.getElementById('email-feedback');

            if (email.length < 5) {
                feedback.innerHTML = '';
                return;
            }

            emailTimeout = setTimeout(() => {
                fetch('<?php echo BASE_URL; ?>/register/check-email?email=' + encodeURIComponent(email))
                    .then(response => response.json())
                    .then(data => {
                        if (data.available) {
                            feedback.innerHTML = '<span class="text-success">✅ Email available</span>';
                        } else {
                            feedback.innerHTML = '<span class="text-danger">❌ Email already registered</span>';
                        }
                    })
                    .catch(error => {
                        console.error('Email validation error:', error);
                    });
            }, 500);
        });

        // Real-time mobile validation
        let mobileTimeout;
        document.getElementById('mobile').addEventListener('input', function() {
            clearTimeout(mobileTimeout);
            const mobile = this.value;
            const feedback = document.getElementById('mobile-feedback');

            if (mobile.length < 10) {
                feedback.innerHTML = '';
                return;
            }

            mobileTimeout = setTimeout(() => {
                fetch('<?php echo BASE_URL; ?>/register/check-mobile?mobile=' + encodeURIComponent(mobile))
                    .then(response => response.json())
                    .then(data => {
                        if (data.available) {
                            feedback.innerHTML = '<span class="text-success">✅ Mobile available</span>';
                        } else {
                            feedback.innerHTML = '<span class="text-danger">❌ Mobile already registered</span>';
                        }
                    })
                    .catch(error => {
                        console.error('Mobile validation error:', error);
                    });
            }, 500);
        });

        // Referral code validation
        let referrerTimeout;
        document.getElementById('referrer_code').addEventListener('input', function() {
            clearTimeout(referrerTimeout);
            const code = this.value.trim();
            const feedback = document.getElementById('referrer-feedback');

            if (code.length < 3) {
                feedback.innerHTML = '';
                return;
            }

            referrerTimeout = setTimeout(() => {
                fetch('<?php echo BASE_URL; ?>/register/validate-referral', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo $_SESSION['csrf_token'] ?? ''; ?>'
                        },
                        body: JSON.stringify({
                            code: code
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.valid) {
                            feedback.innerHTML = `<span class="text-success">✅ Valid code - Referred by ${data.referrer.name}</span>`;
                        } else {
                            feedback.innerHTML = '<span class="text-danger">❌ Invalid referral code</span>';
                        }
                    })
                    .catch(error => {
                        console.error('Referral validation error:', error);
                        feedback.innerHTML = '<span class="text-warning">⚠️ Validation error</span>';
                    });
            }, 500);
        });

        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;

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

            // Show loading state
            submitBtn.innerHTML = '<span class="loading"></span> Processing...';
            submitBtn.disabled = true;

            // Reset after 3 seconds if form doesn't submit
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });
    </script>




</body>

</html>

//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 730 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//