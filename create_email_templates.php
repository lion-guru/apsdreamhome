<?php
/**
 * Email Templates for APS Dream Home
 * Professional email templates for various notifications
 */

// Define BASE_URL if not already defined
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome/');
}

// Welcome Email Template
$welcome_template = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to APS Dream Home</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
        .highlight { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 Welcome to APS Dream Home!</h1>
            <p>Your trusted partner in finding the perfect property</p>
        </div>

        <div class="content">
            <h2>Hi ' . ($user_name ?? 'Valued Customer') . '!</h2>

            <p>Welcome to APS Dream Home! We\'re excited to have you join our community of property seekers and real estate enthusiasts.</p>

            <div class="highlight">
                <h3>🎯 What you can do now:</h3>
                <ul>
                    <li>Browse thousands of verified properties</li>
                    <li>Use advanced search filters</li>
                    <li>Save favorite properties</li>
                    <li>Contact verified agents</li>
                    <li>Get personalized recommendations</li>
                </ul>
            </div>

            <p><strong>Ready to explore?</strong> <a href="' . ($login_url ?? BASE_URL . 'login') . '" class="button">Login to Your Account</a></p>

            <p>If you have any questions, our support team is here to help:</p>
            <p>📧 Email: ' . ($support_email ?? 'support@apsdreamhome.com') . '<br>
               📞 Phone: +91-9876543210</p>
        </div>

        <div class="footer">
            <p>This email was sent to you because you recently created an account with APS Dream Home.</p>
            <p>If you didn\'t create this account, please ignore this email.</p>
            <p>&copy; ' . date('Y') . ' APS Dream Home. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';

// Property Inquiry Email Template
$property_inquiry_template = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Property Inquiry</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .property-card { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .inquiry-details { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .button { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📧 New Property Inquiry</h2>
            <p>Action required for property: ' . ($property_title ?? 'Unknown Property') . '</p>
        </div>

        <div class="content">
            <div class="property-card">
                <h3>' . ($property_title ?? 'Property Title') . '</h3>
                <p><strong>Property URL:</strong> <a href="' . ($property_url ?? BASE_URL . 'properties') . '">' . ($property_url ?? BASE_URL . 'properties') . '</a></p>
            </div>

            <div class="inquiry-details">
                <h4>👤 Customer Details:</h4>
                <p><strong>Name:</strong> ' . ($inquiry_name ?? 'Unknown') . '</p>
                <p><strong>Email:</strong> ' . ($inquiry_email ?? 'unknown@example.com') . '</p>
                <p><strong>Phone:</strong> ' . ($inquiry_phone ?? 'N/A') . '</p>
                <p><strong>Message:</strong></p>
                <p>' . nl2br(htmlspecialchars($inquiry_message ?? 'No message provided')) . '</p>
            </div>

            <p><strong>Next Steps:</strong></p>
            <ol>
                <li>Contact the customer within 24 hours</li>
                <li>Schedule a property viewing if requested</li>
                <li>Update inquiry status in admin panel</li>
                <li>Follow up after property visit</li>
            </ol>

            <div style="text-align: center; margin: 30px 0;">
                <a href="' . ($admin_panel_url ?? BASE_URL . 'admin') . '" class="button">View in Admin Panel</a>
                <a href="mailto:' . ($inquiry_email ?? 'unknown@example.com') . '" class="button" style="background: #28a745;">Reply to Customer</a>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated notification from APS Dream Home CRM system.</p>
            <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
        </div>
    </div>
</body>
</html>';

// Payment Success Email Template
$payment_success_template = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .payment-card { background: white; padding: 25px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 15px rgba(0,0,0,0.1); border-left: 5px solid #28a745; }
        .amount { font-size: 2rem; font-weight: bold; color: #28a745; }
        .button { display: inline-block; background: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 25px; margin: 15px 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Payment Successful!</h1>
            <p>Your payment has been processed successfully</p>
        </div>

        <div class="content">
            <div class="payment-card">
                <h3>Payment Details</h3>
                <p><strong>Order ID:</strong> #' . ($order_id ?? 'N/A') . '</p>
                <p><strong>Amount Paid:</strong> <span class="amount">₹' . number_format($amount ?? 0) . '</span></p>
                <p><strong>Payment Method:</strong> ' . ucfirst($payment_method ?? 'Unknown') . '</p>
                <p><strong>Transaction ID:</strong> ' . ($transaction_id ?? 'N/A') . '</p>
                <p><strong>Payment Date:</strong> ' . ($payment_date ?? date('d M Y, h:i A')) . '</p>
            </div>

            <h3>Hi ' . ($user_name ?? 'Valued Customer') . '!</h3>

            <p>Thank you for your payment! Your transaction has been successfully processed.</p>

            <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <p><strong>What happens next?</strong></p>
                <ul>
                    <li>You will receive a confirmation SMS shortly</li>
                    <li>Your booking/agent will contact you within 24 hours</li>
                    <li>You can download your receipt anytime</li>
                    <li>All transaction details are saved in your account</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="' . ($download_receipt_url ?? BASE_URL . 'payment/receipt') . '" class="button">Download Receipt</a>
                <a href="' . BASE_URL . 'dashboard" class="button" style="background: #6c757d;">View Dashboard</a>
            </div>

            <p>If you have any questions about your payment, please contact our support team:</p>
            <p>📧 Email: support@apsdreamhome.com<br>
               📞 Phone: +91-9876543210</p>
        </div>

        <div class="footer">
            <p>Payment processed by APS Dream Home Payment Gateway</p>
            <p>Transaction ID: ' . ($transaction_id ?? 'N/A') . '</p>
            <p>&copy; ' . date('Y') . ' APS Dream Home. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';

// Password Reset Email Template
$password_reset_template = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ffc107, #fd7e14); color: #212529; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .reset-button { display: inline-block; background: #ffc107; color: #212529; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; margin: 20px 0; border: 2px solid #ffc107; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Password Reset Request</h1>
            <p>Reset your APS Dream Home account password</p>
        </div>

        <div class="content">
            <h2>Hi ' . ($user_name ?? 'Valued Customer') . '!</h2>

            <p>We received a request to reset your password for your APS Dream Home account.</p>

            <div class="warning">
                <p><strong>Security Notice:</strong> This reset link will expire in 24 hours for your security.</p>
            </div>

            <p><strong>Reset your password:</strong></p>
            <p style="text-align: center;">
                <a href="' . BASE_URL . 'reset-password?token=RESET_TOKEN_HERE" class="reset-button">Reset Password Now</a>
            </p>

            <p><strong>Didn\'t request this reset?</strong></p>
            <p>If you didn\'t request a password reset, please ignore this email. Your password will remain unchanged.</p>

            <p>For security reasons, this link can only be used once and will expire automatically.</p>

            <p>If you continue to have trouble accessing your account, please contact our support team:</p>
            <p>📧 Email: ' . ($support_email ?? 'support@apsdreamhome.com') . '<br>
               📞 Phone: +91-9876543210</p>
        </div>

        <div class="footer">
            <p>This password reset request was made from IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . '</p>
            <p>Request time: ' . date('Y-m-d H:i:s') . '</p>
            <p>If you did not request this reset, your account is still secure.</p>
            <p>&copy; ' . date('Y') . ' APS Dream Home. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';

// Admin Notification Template
$admin_notification_template = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #dc3545, #e83e8c); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .notification-card { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 5px solid #dc3545; }
        .button { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
        .priority-high { border-left-color: #dc3545; }
        .priority-medium { border-left-color: #ffc107; }
        .priority-low { border-left-color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🚨 Admin Notification</h2>
            <p>' . ucfirst(str_replace('_', ' ', $type ?? 'system')) . ' - Action Required</p>
        </div>

        <div class="content">
            <div class="notification-card priority-high">
                <h3>📋 Notification Details</h3>
                <p><strong>Type:</strong> ' . ucfirst(str_replace('_', ' ', $type ?? 'system')) . '</p>
                <p><strong>Timestamp:</strong> ' . ($timestamp ?? date('Y-m-d H:i:s')) . '</p>';

if (!empty($data ?? [])) {
    $admin_notification_template .= '
                <h4>Additional Information:</h4>
                <ul>';
    foreach ($data ?? [] as $key => $value) {
        $admin_notification_template .= '<li><strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong> ' . htmlspecialchars($value) . '</li>';
    }
    $admin_notification_template .= '</ul>';
}

$admin_notification_template .= '
            </div>

            <p><strong>Recommended Actions:</strong></p>
            <ol>
                <li>Review the notification details above</li>
                <li>Take appropriate action based on the type</li>
                <li>Update status if applicable</li>
                <li>Monitor for similar notifications</li>
            </ol>

            <div style="text-align: center; margin: 30px 0;">
                <a href="' . BASE_URL . 'admin" class="button">Open Admin Panel</a>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated notification from APS Dream Home admin system.</p>
            <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
        </div>
    </div>
</body>
</html>';
?>

<!-- Save these templates to app/views/emails/ directory -->
<?php
// Create email templates directory
$email_templates_dir = __DIR__ . '/app/views/emails/';
if (!is_dir($email_templates_dir)) {
    mkdir($email_templates_dir, 0755, true);
}

// Save welcome email template
file_put_contents($email_templates_dir . 'welcome.php', $welcome_template);

// Save property inquiry template
file_put_contents($email_templates_dir . 'property_inquiry.php', $property_inquiry_template);

// Save payment success template
file_put_contents($email_templates_dir . 'payment_success.php', $payment_success_template);

// Save password reset template
file_put_contents($email_templates_dir . 'password_reset.php', $password_reset_template);

// Save admin notification template
file_put_contents($email_templates_dir . 'admin_notification.php', $admin_notification_template);

echo "✅ Email templates created successfully!\n";
?>
