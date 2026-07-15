<?php
/**
 * Wrap all 8 standalone auth files with __() i18n calls
 * Run: php scripts/wrap_standalone_auth_i18n.php
 */

$base = dirname(__DIR__);
$helper = "require_once __DIR__ . '/../../Helpers/TranslationHelper.php';\n";
$filesWrapped = 0;

// ============ 1. universal_login.php ============
$f = "$base/app/views/auth/universal_login.php";
$c = file_get_contents($f);
if (strpos($c, 'TranslationHelper') === false) {
    $c = str_replace(
        "<?php\n\n// Start session",
        "<?php\n" . $helper . "\n// Start session",
        $c
    );
}
$replacements = [
    ["<title>Universal Login - APS Dream Home</title>", "<title><?php echo __('auth_universal_login_title', 'APS Dream Home'); ?></title>"],
    ["<h2 class=\"login-title\">Welcome Back</h2>", "<h2 class=\"login-title\"><?php echo __('auth_welcome_back', 'Welcome Back'); ?></h2>"],
    ["<p class=\"login-subtitle\">Choose your preferred login method</p>", "<p class=\"login-subtitle\"><?php echo __('auth_choose_login_method', 'Choose your preferred login method'); ?></p>"],
    [">Email Login<", "><?php echo __('auth_email_login', 'Email Login'); ?><"],
    [">Mobile Login<", "><?php echo __('auth_mobile_login', 'Mobile Login'); ?><"],
    [">Google Login<", "><?php echo __('auth_google_login', 'Google Login'); ?><"],
    ["Email Address\n                                    </label>", "<?php echo __('auth_email_address', 'Email Address'); ?>\n                                    </label>"],
    ["placeholder=\"Enter your email address\"", "placeholder=\"<?php echo __('auth_enter_email', 'Enter your email address'); ?>\""],
    ["placeholder=\"Enter your password\"", "placeholder=\"<?php echo __('auth_enter_password', 'Enter your password'); ?>\""],
    ["Remember me for 30 days\n                                        </label>", "<?php echo __('auth_remember_me', 'Remember me for 30 days'); ?>\n                                        </label>"],
    [">Login with Email<", "><?php echo __('auth_login_with_email', 'Login with Email'); ?><"],
    [">Try Mobile Login<", "><?php echo __('auth_try_mobile', 'Try Mobile Login'); ?><"],
    ["Mobile Number\n                                    </label>", "<?php echo __('auth_mobile_number', 'Mobile Number'); ?>\n                                    </label>"],
    ["placeholder=\"9876543210\"", "placeholder=\"<?php echo __('auth_phone_placeholder', '9876543210'); ?>\""],
    ["Enter 10-digit mobile number without country code", "<?php echo __('auth_phone_hint', 'Enter 10-digit mobile number without country code'); ?>"],
    ["Password / PIN\n                                    </label>", "<?php echo __('auth_password_or_pin', 'Password / PIN'); ?>\n                                    </label>"],
    ["placeholder=\"Enter password or 4-digit PIN\"", "placeholder=\"<?php echo __('auth_enter_pin', 'Enter password or 4-digit PIN'); ?>\""],
    ["Send OTP instead of password\n                                        </label>", "<?php echo __('auth_send_otp', 'Send OTP instead of password'); ?>\n                                        </label>"],
    [">Login with Mobile<", "><?php echo __('auth_login_with_mobile', 'Login with Mobile'); ?><"],
    [">Request OTP<", "><?php echo __('auth_request_otp', 'Request OTP'); ?><"],
    ["<h4>Google Sign-In</h4>", "<h4><?php echo __('auth_google_signin', 'Google Sign-In'); ?></h4>"],
    ["<p class=\"text-muted\">Quick and secure login with your Google account</p>", "<p class=\"text-muted\"><?php echo __('auth_google_desc', 'Quick and secure login with your Google account'); ?></p>"],
    [">Continue with Google<", "><?php echo __('auth_continue_with_google', 'Continue with Google'); ?><"],
    [">Use Email Instead<", "><?php echo __('auth_use_email_instead', 'Use Email Instead'); ?><"],
    ["Benefits of Google Login:", "<?php echo __('auth_google_benefits', 'Benefits of Google Login:'); ?>"],
    ["No password to remember", "<?php echo __('auth_no_password', 'No password to remember'); ?>"],
    ["Two-factor authentication", "<?php echo __('auth_two_factor', 'Two-factor authentication'); ?>"],
    ["Quick access", "<?php echo __('auth_quick_access', 'Quick access'); ?>"],
    ["Secure connection", "<?php echo __('auth_secure_connection', 'Secure connection'); ?>"],
    [">Forgot Password?<", "><?php echo __('auth_forgot_password', 'Forgot Password?'); ?><"],
    [">Create New Account<", "><?php echo __('auth_create_account', 'Create New Account'); ?><"],
    [">Agent Registration<", "><?php echo __('auth_agent_registration', 'Agent Registration'); ?><"],
    [">Associate Registration<", "><?php echo __('auth_associate_registration', 'Associate Registration'); ?><"],
];
foreach ($replacements as [$s, $r]) {
    if (strpos($c, $s) !== false) {
        $c = str_replace($s, $r, $c);
    }
}
file_put_contents($f, $c);
echo "1. universal_login.php DONE\n";
$filesWrapped++;

// ============ 2. reset_password.php (underscore) ============
$f = "$base/app/views/auth/reset_password.php";
$c = file_get_contents($f);
if (strpos($c, 'TranslationHelper') === false) {
    $c = str_replace(
        "<?php\n\n\n\$page_title",
        "<?php\n" . $helper . "\n\$page_title",
        $c
    );
}
$replacements = [
    ["<title>Reset Password - APS Dream Home</title>", "<title><?php echo __('auth_reset_password_title', 'Reset Password'); ?></title>"],
    ["<h2 class=\"reset-title\">Reset Password</h2>", "<h2 class=\"reset-title\"><?php echo __('auth_reset_password', 'Reset Password'); ?></h2>"],
    ["<p class=\"reset-subtitle\">Enter your new password below</p>", "<p class=\"reset-subtitle\"><?php echo __('auth_enter_new_password', 'Enter your new password below'); ?></p>"],
    ["New Password\n                            </label>", "<?php echo __('auth_new_password', 'New Password'); ?>\n                            </label>"],
    ["placeholder=\"Enter your new password\"", "placeholder=\"<?php echo __('auth_enter_new_password_ph', 'Enter your new password'); ?>\""],
    ["At least 6 characters with letters, numbers, and symbols", "<?php echo __('auth_password_rules', 'At least 6 characters with letters, numbers, and symbols'); ?>"],
    ["Confirm Password\n                            </label>", "<?php echo __('auth_confirm_password', 'Confirm Password'); ?>\n                            </label>"],
    ["placeholder=\"Confirm your new password\"", "placeholder=\"<?php echo __('auth_confirm_password_ph', 'Confirm your new password'); ?>\""],
    ["Passwords do not match\n                            </div>", "<?php echo __('auth_passwords_no_match', 'Passwords do not match'); ?>\n                            </div>"],
    ["Password Strength", "<?php echo __('auth_password_strength', 'Password Strength'); ?>"],
    ["Enter a password", "<?php echo __('auth_enter_password_strength', 'Enter a password'); ?>"],
    ["Reset Password\n                            </button>", "<?php echo __('auth_reset_password_btn', 'Reset Password'); ?>\n                            </button>"],
    ["Back to Login\n                            </a>", "<?php echo __('auth_back_to_login', 'Back to Login'); ?>\n                            </a>"],
    ["Your password is encrypted and secure", "<?php echo __('auth_password_encrypted', 'Your password is encrypted and secure'); ?>"],
    ["Need Help?", "<?php echo __('auth_need_help', 'Need Help?'); ?>"],
    ["Request Another Reset", "<?php echo __('auth_request_another_reset', 'Request Another Reset'); ?>"],
];
foreach ($replacements as [$s, $r]) {
    if (strpos($c, $s) !== false) {
        $c = str_replace($s, $r, $c);
    }
}
file_put_contents($f, $c);
echo "2. reset_password.php DONE\n";
$filesWrapped++;

// ============ 3. google_role_selection.php ============
$f = "$base/app/views/auth/google_role_selection.php";
$c = file_get_contents($f);
if (strpos($c, 'TranslationHelper') === false) {
    $c = "<?php require_once __DIR__ . '/../../Helpers/TranslationHelper.php'; ?>\n" . $c;
}
$replacements = [
    ["<title>Complete Your Registration - APS Dream Home</title>", "<title><?php echo __('auth_complete_registration_title', 'Complete Registration'); ?></title>"],
    ["I want to join as:</h5>", "<?php echo __('auth_join_as', 'I want to join as:'); ?></h5>"],
    ["<h5>Customer</h5>", "<h5><?php echo __('auth_role_customer', 'Customer'); ?></h5>"],
    ["<small>Search properties, buy/rent, get 5% discount with referral</small>", "<small><?php echo __('auth_role_customer_desc', 'Search properties, buy/rent, get 5% discount with referral'); ?></small>"],
    ["<h5>Associate</h5>", "<h5><?php echo __('auth_role_associate', 'Associate'); ?></h5>"],
    ["<small>Earn commissions, build network, mandatory referral code</small>", "<small><?php echo __('auth_role_associate_desc', 'Earn commissions, build network, mandatory referral code'); ?></small>"],
    ["<h5>Agent</h5>", "<h5><?php echo __('auth_role_agent', 'Agent'); ?></h5>"],
    ["<small>Sell properties, earn higher commissions, mandatory referral</small>", "<small><?php echo __('auth_role_agent_desc', 'Sell properties, earn higher commissions, mandatory referral'); ?></small>"],
    [">Referral Code<", "><?php echo __('auth_referral_code', 'Referral Code'); ?><"],
    ["placeholder=\"Enter referral code\"", "placeholder=\"<?php echo __('auth_enter_referral', 'Enter referral code'); ?>\""],
    [">Use Company Code<", "><?php echo __('auth_use_company_code', 'Use Company Code'); ?><"],
    ["Associate/Agent require referral code. Use company code to join directly.", "<?php echo __('auth_referral_note', 'Associate/Agent require referral code. Use company code to join directly.'); ?>"],
    ["Phone Number *</label>", "<?php echo __('auth_phone_number_required', 'Phone Number *'); ?></label>"],
    ["placeholder=\"Enter your phone number\"", "placeholder=\"<?php echo __('auth_enter_phone', 'Enter your phone number'); ?>\""],
    [">Complete Registration<", "><?php echo __('auth_complete_registration', 'Complete Registration'); ?><"],
    ["Creating your account...</p>", "<?php echo __('auth_creating_account', 'Creating your account...'); ?></p>"],
    ["Back to Login\n            </a>", "<?php echo __('auth_back_to_login', 'Back to Login'); ?>\n            </a>"],
];
foreach ($replacements as [$s, $r]) {
    if (strpos($c, $s) !== false) {
        $c = str_replace($s, $r, $c);
    }
}
file_put_contents($f, $c);
echo "3. google_role_selection.php DONE\n";
$filesWrapped++;

// ============ 4. associate_register.php ============
$f = "$base/app/views/auth/associate_register.php";
$c = file_get_contents($f);
if (strpos($c, 'TranslationHelper') === false) {
    $c = str_replace(
        "<?php\nif (!defined('BASE_URL'))",
        "<?php\n" . $helper . "\nif (!defined('BASE_URL'))",
        $c
    );
}
$replacements = [
    ["<div class=\"brand-name\">APS Dream Home</div>", "<div class=\"brand-name\"><?php echo __('auth_aps_dream_home', 'APS Dream Home'); ?></div>"],
    ["<div class=\"brand-subtitle\">Associate Partner Portal</div>", "<div class=\"brand-subtitle\"><?php echo __('auth_associate_portal', 'Associate Partner Portal'); ?></div>"],
    ["Join our associate network and earn commissions by referring users", "<?php echo __('auth_associate_tagline', 'Join our associate network and earn commissions by referring users'); ?>"],
    ["Full Name <span class=\"required\">*</span></label>", "<?php echo __('auth_full_name', 'Full Name'); ?> <span class=\"required\">*</span></label>"],
    ["placeholder=\"Enter your full name\"", "placeholder=\"<?php echo __('auth_enter_full_name', 'Enter your full name'); ?>\""],
    ["Email Address <span class=\"required\">*</span></label>", "<?php echo __('auth_email_address', 'Email Address'); ?> <span class=\"required\">*</span></label>"],
    ["placeholder=\"you@example.com\"", "placeholder=\"<?php echo __('auth_email_ph', 'you@example.com'); ?>\""],
    ["Phone Number <span class=\"required\">*</span></label>", "<?php echo __('auth_phone_number', 'Phone Number'); ?> <span class=\"required\">*</span></label>"],
    ["placeholder=\"10-digit mobile number\"", "placeholder=\"<?php echo __('auth_10digit_phone', '10-digit mobile number'); ?>\""],
    ["Password <span class=\"required\">*</span></label>", "<?php echo __('auth_password', 'Password'); ?> <span class=\"required\">*</span></label>"],
    ["placeholder=\"Create password\"", "placeholder=\"<?php echo __('auth_create_password', 'Create password'); ?>\""],
    ["Confirm Password <span class=\"required\">*</span></label>", "<?php echo __('auth_confirm_password', 'Confirm Password'); ?> <span class=\"required\">*</span></label>"],
    ["placeholder=\"Re-enter password\"", "placeholder=\"<?php echo __('auth_reenter_password', 'Re-enter password'); ?>\""],
    ["Sponsor / Referral Code", "<?php echo __('auth_sponsor_referral_code', 'Sponsor / Referral Code'); ?>"],
    ["(optional if you have a referrer link)", "<?php echo __('auth_referral_optional', '(optional if you have a referrer link)'); ?>"],
    ["Enter sponsor code or use referral link", "<?php echo __('auth_sponsor_ph', 'Enter sponsor code or use referral link'); ?>"],
    ["If you were referred by an associate, enter their sponsor code here.", "<?php echo __('auth_referral_info', 'If you were referred by an associate, enter their sponsor code here.'); ?>"],
    [">Create Associate Account<", "><?php echo __('auth_create_associate', 'Create Associate Account'); ?><"],
    ["Already registered?", "<?php echo __('auth_already_registered', 'Already registered?'); ?>"],
    ["Sign in to your Associate Account", "<?php echo __('auth_sign_in_associate', 'Sign in to your Associate Account'); ?>"],
    ["Back to Main Site", "<?php echo __('auth_back_to_main', 'Back to Main Site'); ?>"],
    ["Please enter a valid 10-digit phone number.", "<?php echo __('auth_valid_phone', 'Please enter a valid 10-digit phone number.'); ?>"],
    ["Passwords do not match.", "<?php echo __('auth_passwords_dont_match', 'Passwords do not match.'); ?>"],
];
foreach ($replacements as [$s, $r]) {
    if (strpos($c, $s) !== false) {
        $c = str_replace($s, $r, $c);
    }
}
file_put_contents($f, $c);
echo "4. associate_register.php DONE\n";
$filesWrapped++;

// ============ 5. agent_register.php ============
$f = "$base/app/views/auth/agent_register.php";
$c = file_get_contents($f);
if (strpos($c, 'TranslationHelper') === false) {
    $c = str_replace(
        "<?php\nif (!defined('BASE_URL'))",
        "<?php\n" . $helper . "\nif (!defined('BASE_URL'))",
        $c
    );
}
$replacements = [
    ["<p class=\"brand-subtitle\">Agent Registration</p>", "<p class=\"brand-subtitle\"><?php echo __('auth_agent_registration_title', 'Agent Registration'); ?></p>"],
    ["<i class=\"fa-solid fa-arrow-left\"></i> Back to Home", "<i class=\"fa-solid fa-arrow-left\"></i> <?php echo __('auth_back_to_home', 'Back to Home'); ?>"],
    ["<h2 class=\"brand-title\">APS Dream Home</h2>", "<h2 class=\"brand-title\"><?php echo __('auth_aps_dream_home', 'APS Dream Home'); ?></h2>"],
    ["Please fix the following errors:", "<?php echo __('auth_fix_errors', 'Please fix the following errors:'); ?>"],
    ["Personal Details</div>", "<?php echo __('auth_personal_details', 'Personal Details'); ?></div>"],
    ["Full Name\n                </label>", "<?php echo __('auth_full_name', 'Full Name'); ?>\n                </label>"],
    ["placeholder=\"Enter your full name\"", "placeholder=\"<?php echo __('auth_enter_full_name', 'Enter your full name'); ?>\""],
    ["Email Address\n                </label>", "<?php echo __('auth_email_address', 'Email Address'); ?>\n                </label>"],
    ["placeholder=\"you@example.com\"", "placeholder=\"<?php echo __('auth_email_ph', 'you@example.com'); ?>\""],
    ["Phone Number\n                </label>", "<?php echo __('auth_phone_number', 'Phone Number'); ?>\n                </label>"],
    ["placeholder=\"10-digit phone number\"", "placeholder=\"<?php echo __('auth_10digit_phone', '10-digit phone number'); ?>\""],
    ["Security</div>", "<?php echo __('auth_security', 'Security'); ?></div>"],
    ["Password\n                </label>", "<?php echo __('auth_password', 'Password'); ?>\n                </label>"],
    ["placeholder=\"Create a password\"", "placeholder=\"<?php echo __('auth_create_a_password', 'Create a password'); ?>\""],
    ["Confirm Password\n                </label>", "<?php echo __('auth_confirm_password', 'Confirm Password'); ?>\n                </label>"],
    ["placeholder=\"Re-enter password\"", "placeholder=\"<?php echo __('auth_reenter_password', 'Re-enter password'); ?>\""],
    ["Professional Info</div>", "<?php echo __('auth_professional_info', 'Professional Info'); ?></div>"],
    ["Experience\n                </label>", "<?php echo __('auth_experience', 'Experience'); ?>\n                </label>"],
    ["Select your experience", "<?php echo __('auth_select_experience', 'Select your experience'); ?>"],
    [">Fresher<", "><?php echo __('auth_fresher', 'Fresher'); ?><"],
    [">1-2 years<", "><?php echo __('auth_exp_1_2', '1-2 years'); ?><"],
    [">3-5 years<", "><?php echo __('auth_exp_3_5', '3-5 years'); ?><"],
    [">5+ years<", "><?php echo __('auth_exp_5_plus', '5+ years'); ?><"],
    ["Referral Code\n                </label>", "<?php echo __('auth_referral_code', 'Referral Code'); ?>\n                </label>"],
    ["placeholder=\"Enter referral code\"", "placeholder=\"<?php echo __('auth_enter_referral', 'Enter referral code'); ?>\""],
    ["By registering, you agree to our", "<?php echo __('auth_terms_prefix', 'By registering, you agree to our'); ?>"],
    ["Terms of Service", "<?php echo __('auth_terms', 'Terms of Service'); ?>"],
    ["Privacy Policy", "<?php echo __('auth_privacy_policy', 'Privacy Policy'); ?>"],
    [">Create Account<", "><?php echo __('auth_create_account', 'Create Account'); ?><"],
    ["Already have an account?", "<?php echo __('auth_already_have_account', 'Already have an account?'); ?>"],
    ["Login here", "<?php echo __('auth_login_here', 'Login here'); ?>"],
];
foreach ($replacements as [$s, $r]) {
    if (strpos($c, $s) !== false) {
        $c = str_replace($s, $r, $c);
    }
}
file_put_contents($f, $c);
echo "5. agent_register.php DONE\n";
$filesWrapped++;

echo "\n=== DONE: $filesWrapped files wrapped ===\n";
