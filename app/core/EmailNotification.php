<?php
/**
 * Enhanced Email Notification System (PHP mail() fallback)
 * Handles sending emails using PHP's built-in mail() function
 */

class EmailNotification {
    private $settings;

    public function __construct() {
        $this->settings = $this->getEmailSettings();
    }

    /**
     * Send inquiry notification email
     */
    public function sendInquiryNotification($inquiry_id) {
        try {
            $inquiry = $this->getInquiryDetails($inquiry_id);
            if (!$inquiry) {
                throw new Exception('Inquiry not found');
            }

            $admin_email = $this->settings['admin_email'];

            // Prepare email content
            $subject = 'New Property Inquiry - ' . $inquiry['property_title'];
            $body = $this->getInquiryEmailTemplate($inquiry);

            // Send to admin
            $this->sendEmail($admin_email, $subject, $body);

            // Send confirmation to user if they provided email
            if (!empty($inquiry['guest_email']) || !empty($inquiry['user_email'])) {
                $user_email = $inquiry['guest_email'] ?? $inquiry['user_email'];
                $confirmation_subject = 'Inquiry Received - APS Dream Home';
                $confirmation_body = $this->getInquiryConfirmationTemplate($inquiry);

                $this->sendEmail($user_email, $confirmation_subject, $confirmation_body);
            }

            return true;

        } catch (Exception $e) {
            error_log('Email notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send user registration notification
     */
    public function sendRegistrationNotification($user_data) {
        try {
            // Send welcome email to user
            $user_subject = 'Welcome to APS Dream Home!';
            $user_body = $this->getRegistrationWelcomeTemplate($user_data);

            $this->sendEmail($user_data['email'], $user_subject, $user_body);

            // Notify admin about new registration
            $admin_email = $this->settings['admin_email'];
            $admin_subject = 'New User Registration - ' . $user_data['name'];
            $admin_body = $this->getRegistrationAdminTemplate($user_data);

            $this->sendEmail($admin_email, $admin_subject, $admin_body);

            return true;

        } catch (Exception $e) {
            error_log('Registration email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send inquiry response notification
     */
    public function sendInquiryResponse($inquiry_id, $response_message) {
        try {
            $inquiry = $this->getInquiryDetails($inquiry_id);
            if (!$inquiry) {
                throw new Exception('Inquiry not found');
            }

            // Send response to user
            $user_email = $inquiry['guest_email'] ?? $inquiry['user_email'];
            if ($user_email) {
                $subject = 'Re: ' . $inquiry['subject'] . ' - APS Dream Home';
                $body = $this->getInquiryResponseTemplate($inquiry, $response_message);

                $this->sendEmail($user_email, $subject, $body);
            }

            return true;

        } catch (Exception $e) {
            error_log('Inquiry response email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email using PHP mail()
     */
    private function sendEmail($to, $subject, $body) {
        try {
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . ($this->settings['from_name'] ?? 'APS Dream Home') . " <" . ($this->settings['from_address'] ?? 'noreply@apsdreamhome.com') . ">\r\n";
            $headers .= "Reply-To: " . ($this->settings['contact_email'] ?? 'info@apsdreamhome.com') . "\r\n";
            $headers .= "X-Mailer: APS Dream Home System\r\n";
            $headers .= "X-Priority: 3\r\n";

            // Send email
            if (!mail($to, $subject, $body, $headers)) {
                throw new Exception('Failed to send email to: ' . $to);
            }

            return true;

        } catch (Exception $e) {
            error_log('Send email error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get email settings from environment configuration
     */
    private function getEmailSettings() {
        return [
            'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@apsdreamhome.com'),
            'from_name' => env('MAIL_FROM_NAME', 'APS Dream Home'),
            'admin_email' => env('ADMIN_EMAIL', 'admin@apsdreamhome.com'),
            'contact_email' => env('CONTACT_EMAIL', 'info@apsdreamhome.com')
        ];
    }

    /**
     * Test email configuration
     */
    public function testEmailConfiguration() {
        try {
            $test_email = $this->settings['admin_email'];
            $subject = 'Email Configuration Test - APS Dream Home';
            $body = "
            <h2>Email Configuration Test</h2>
            <p>This is a test email to verify that your email configuration is working correctly.</p>
            <p><strong>Sent at:</strong> " . date('Y-m-d H:i:s') . "</p>
            <p><strong>Configuration:</strong></p>
            <ul>
                <li>From: {$this->settings['from_name']} &lt;{$this->settings['from_address']}&gt;</li>
                <li>Admin Email: {$this->settings['admin_email']}</li>
                <li>Contact Email: {$this->settings['contact_email']}</li>
            </ul>
            <p>If you received this email, your configuration is working correctly!</p>
            ";

            return $this->sendEmail($test_email, $subject, $body);

        } catch (Exception $e) {
            error_log('Email test error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get inquiry details for email
     */
    private function getInquiryDetails($inquiry_id) {
        try {
            global $pdo;
            if (!$pdo) {
                return null;
            }

            $sql = "
                SELECT
                    pi.*,
                    p.title as property_title,
                    p.city,
                    p.state,
                    u.name as user_name,
                    u.email as user_email,
                    u.phone as user_phone
                FROM property_inquiries pi
                JOIN properties p ON pi.property_id = p.id
                LEFT JOIN users u ON pi.user_id = u.id
                WHERE pi.id = ?
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$inquiry_id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Get inquiry details error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get inquiry email template for admin
     */
    private function getInquiryEmailTemplate($inquiry) {
        $inquiry_type_labels = [
            'general' => 'General Inquiry',
            'viewing' => 'Schedule Viewing',
            'price' => 'Price Information',
            'availability' => 'Availability Check',
            'offer' => 'Make an Offer'
        ];

        $status_labels = [
            'new' => 'New',
            'in_progress' => 'In Progress',
            'responded' => 'Responded',
            'closed' => 'Closed'
        ];

        $priority_labels = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High'
        ];

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>New Property Inquiry</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .footer { background: #343a40; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
                .highlight { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .label { font-weight: bold; color: #007bff; display: inline-block; width: 120px; }
                .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
                .badge-new { background: #dc3545; color: white; }
                .badge-high { background: #ffc107; color: #212529; }
                .button { display: inline-block; background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .button:hover { background: #0056b3; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>&#x1F514; New Property Inquiry</h1>
                    <p>You have received a new inquiry for one of your properties</p>
                </div>

                <div class='content'>
                    <div class='highlight'>
                        <h3>&#x1F3E0; Property Details</h3>
                        <p><span class='label'>Property:</span> {$inquiry['property_title']}</p>
                        <p><span class='label'>Location:</span> {$inquiry['city']}, {$inquiry['state']}</p>
                        <p><span class='label'>Inquiry ID:</span> <strong>#{$inquiry['id']}</strong></p>
                    </div>

                    <div class='highlight'>
                        <h3>&#x1F4DD; Inquiry Details</h3>
                        <p><span class='label'>Subject:</span> {$inquiry['subject']}</p>
                        <p><span class='label'>Type:</span> <span class='badge'>" . ($inquiry_type_labels[$inquiry['inquiry_type']] ?? $inquiry['inquiry_type']) . "</span></p>
                        <p><span class='label'>Priority:</span> <span class='badge badge-high'>" . ($priority_labels[$inquiry['priority']] ?? $inquiry['priority']) . "</span></p>
                        <p><span class='label'>Status:</span> <span class='badge badge-new'>" . ($status_labels[$inquiry['status']] ?? $inquiry['status']) . "</span></p>
                        <p><span class='label'>Submitted:</span> " . date('M d, Y H:i', strtotime($inquiry['created_at'])) . "</p>
                    </div>

                    <div class='highlight'>
                        <h3>&#x1F464; Contact Information</h3>
                        " . (!empty($inquiry['user_name']) ?
                            "<p><span class='label'>Name:</span> {$inquiry['user_name']}</p>
                             <p><span class='label'>Email:</span> {$inquiry['user_email']}</p>
                             <p><span class='label'>Phone:</span> {$inquiry['user_phone']}</p>" :
                            "<p><span class='label'>Name:</span> {$inquiry['guest_name']}</p>
                             <p><span class='label'>Email:</span> {$inquiry['guest_email']}</p>
                             <p><span class='label'>Phone:</span> {$inquiry['guest_phone']}</p>") . "
                    </div>

                    <div class='highlight'>
                        <h3>&#x1F4AC; Message</h3>
                        <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;'>" . nl2br(htmlspecialchars($inquiry['message'])) . "</div>
                    </div>

                    <div class='highlight' style='text-align: center;'>
                        <h3>&#x1F680; Quick Actions</h3>
                        <p><a href='" . BASE_URL . "admin/inquiries/view?id={$inquiry['id']}' class='button'>View in Admin Panel</a></p>
                        <p><a href='" . BASE_URL . "properties/{$inquiry['property_id']}' class='button' style='background: #28a745;'>View Property</a></p>
                    </div>
                </div>

                <div class='footer'>
                    <p>&#x1F4E7; This email was sent from APS Dream Home property management system.</p>
                    <p>Please respond to this inquiry through the admin panel for proper tracking.</p>
                    <p><strong>Admin Email:</strong> {$this->settings['admin_email']} | <strong>Support:</strong> {$this->settings['contact_email']}</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get inquiry confirmation template for user
     */
    private function getInquiryConfirmationTemplate($inquiry) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Inquiry Confirmation</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .footer { background: #343a40; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
                .highlight { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .label { font-weight: bold; color: #28a745; display: inline-block; width: 120px; }
                .button { display: inline-block; background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .button:hover { background: #218838; }
                .steps { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>&#x2705; Inquiry Received</h1>
                    <p>Thank you for your interest in our property!</p>
                </div>

                <div class='content'>
                    <div class='highlight'>
                        <h3>&#x1F642; Thank You!</h3>
                        <p>Dear " . htmlspecialchars($inquiry['guest_name'] ?? $inquiry['user_name'] ?? 'Valued Customer') . ",</p>
                        <p>We have successfully received your inquiry regarding <strong>{$inquiry['property_title']}</strong>.</p>
                        <p>Our expert team will review your inquiry and respond within 24 hours.</p>
                    </div>

                    <div class='highlight'>
                        <h3>&#x1F4DD; Inquiry Summary</h3>
                        <p><span class='label'>Property:</span> {$inquiry['property_title']}</p>
                        <p><span class='label'>Subject:</span> {$inquiry['subject']}</p>
                        <p><span class='label'>Submitted:</span> " . date('M d, Y H:i', strtotime($inquiry['created_at'])) . "</p>
                        <p><span class='label'>Reference ID:</span> <strong>#{$inquiry['id']}</strong></p>
                    </div>

                    <div class='highlight'>
                        <h3>&#x1F554; What Happens Next?</h3>
                        <div class='steps'>
                            <p><strong>Step 1:</strong> Our expert agents will review your inquiry</p>
                            <p><strong>Step 2:</strong> You'll receive a response within 24 hours</p>
                            <p><strong>Step 3:</strong> We may contact you for additional information if needed</p>
                            <p><strong>Step 4:</strong> You can track your inquiry status in your account</p>
                        </div>
                    </div>

                    <div class='highlight' style='text-align: center;'>
                        <h3>&#x1F517; Quick Links</h3>
                        <p><a href='" . BASE_URL . "properties/{$inquiry['property_id']}' class='button'>View Property Again</a></p>
                        <p><a href='" . BASE_URL . "contact' class='button' style='background: #6c757d;'>Contact Us Directly</a></p>
                    </div>
                </div>

                <div class='footer'>
                    <p>&#x1F4E7; This confirmation was sent from APS Dream Home property management system.</p>
                    <p>If you have any questions, please contact us at {$this->settings['contact_email']}</p>
                    <p><strong>Need immediate assistance?</strong> Our support team is here to assist you!</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get inquiry response template
     */
    private function getInquiryResponseTemplate($inquiry, $response_message) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Inquiry Response</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .footer { background: #343a40; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
                .highlight { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .label { font-weight: bold; color: #007bff; display: inline-block; width: 120px; }
                .response-box { background: #e3f2fd; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; margin: 15px 0; }
                .button { display: inline-block; background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .button:hover { background: #0056b3; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>&#x1F4E8; Response to Your Inquiry</h1>
                    <p>Our team has responded to your property inquiry</p>
                </div>

                <div class='content'>
                    <div class='highlight'>
                        <h3>&#x1F3E0; Property Inquiry</h3>
                        <p><span class='label'>Property:</span> {$inquiry['property_title']}</p>
                        <p><span class='label'>Subject:</span> {$inquiry['subject']}</p>
                        <p><span class='label'>Inquiry ID:</span> <strong>#{$inquiry['id']}</strong></p>
                        <p><span class='label'>Submitted:</span> " . date('M d, Y H:i', strtotime($inquiry['created_at'])) . "</p>
                    </div>

                    <div class='highlight'>
                        <h3>&#x1F4AC; Our Response</h3>
                        <div class='response-box'>" . nl2br(htmlspecialchars($response_message)) . "</div>
                    </div>

                    <div class='highlight'>
                        <h3>&#x1F517; Next Steps</h3>
                        <p>If you have any follow-up questions or need more information, please don't hesitate to contact us.</p>
                        <p>You can reply to this email or contact us directly using the information below.</p>
                    </div>

                    <div class='highlight' style='text-align: center;'>
                        <h3>&#x1F4F1; Contact Information</h3>
                        <p><strong>Email:</strong> {$this->settings['contact_email']}</p>
                        <p><strong>Phone:</strong> " . env('CONTACT_PHONE', '+91-9876543210') . "</p>
                        <p><a href='" . BASE_URL . "contact' class='button'>Contact Us Online</a></p>
                    </div>
                </div>

                <div class='footer'>
                    <p>&#x1F4E7; This response was sent from APS Dream Home property management system.</p>
                    <p>Thank you for choosing APS Dream Home for your real estate needs!</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get registration welcome template
     */
    private function getRegistrationWelcomeTemplate($user_data) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Welcome to APS Dream Home</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .footer { background: #343a40; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
                .highlight { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .label { font-weight: bold; color: #28a745; display: inline-block; width: 120px; }
                .button { display: inline-block; background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .button:hover { background: #218838; }
                .features { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>&#x1F389; Welcome to APS Dream Home!</h1>
                    <p>Your account has been created successfully</p>
                </div>

                <div class='content'>
                    <div class='highlight'>
                        <h3>Welcome, " . htmlspecialchars($user_data['name']) . "! &#x1F642;</h3>
                        <p>Thank you for joining APS Dream Home. Your account has been created successfully and you now have access to our complete platform.</p>
                    </div>

                    <div class='highlight'>
                        <h3>&#x1F4DD; Account Details</h3>
                        <p><span class='label'>Name:</span> " . htmlspecialchars($user_data['name']) . "</p>
                        <p><span class='label'>Email:</span> " . htmlspecialchars($user_data['email']) . "</p>
                        <p><span class='label'>Registration Date:</span> " . date('M d, Y') . "</p>
                        <p><span class='label'>Account Status:</span> <span style='color: #28a745; font-weight: bold;'>Active</span></p>
                    </div>

                    <div class='highlight'>
                        <h3>&#x1F680; What You Can Do Now</h3>
                        <div class='features'>
                            <p>&#x1F381; <strong>Browse Properties:</strong> Explore our extensive property listings</p>
                            <p>&#x2764; <strong>Save Favorites:</strong> Save properties you're interested in</p>
                            <p>&#x1F4DD; <strong>Submit Inquiries:</strong> Contact agents about properties</p>
                            <p>&#x1F4CA; <strong>Personal Dashboard:</strong> Track your activity and preferences</p>
                            <p>&#x1F4F1; <strong>Contact Agents:</strong> Direct communication with our experts</p>
                            <p>&#x1F3C6; <strong>Join Associate Program:</strong> Opportunity to earn through our MLM system</p>
                        </div>
                    </div>

                    <div class='highlight' style='text-align: center;'>
                        <h3>&#x1F3C1; Get Started</h3>
                        <p>Start exploring our properties and find your dream home today!</p>
                        <p><a href='" . BASE_URL . "properties' class='button'>Browse Properties</a></p>
                        <p><a href='" . BASE_URL . "contact' class='button' style='background: #6c757d;'>Contact Support</a></p>
                    </div>
                </div>

                <div class='footer'>
                    <p>&#x1F4E7; This welcome email was sent from APS Dream Home property management system.</p>
                    <p>If you have any questions, please contact us at {$this->settings['contact_email']}</p>
                    <p><strong>Need help getting started?</strong> Our support team is here to assist you!</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get registration admin notification template
     */
    private function getRegistrationAdminTemplate($user_data) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>New User Registration</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: #212529; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .footer { background: #343a40; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
                .highlight { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .label { font-weight: bold; color: #ffc107; display: inline-block; width: 120px; }
                .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
                .badge-success { background: #28a745; color: white; }
                .button { display: inline-block; background: #ffc107; color: #212529; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .button:hover { background: #e0a800; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>&#x1F465; New User Registration</h1>
                    <p>A new user has registered on your platform</p>
                </div>

                <div class='content'>
                    <div class='highlight'>
                        <h3>&#x1F4DD; User Details</h3>
                        <p><span class='label'>Name:</span> " . htmlspecialchars($user_data['name']) . "</p>
                        <p><span class='label'>Email:</span> " . htmlspecialchars($user_data['email']) . "</p>
                        <p><span class='label'>Phone:</span> " . htmlspecialchars($user_data['phone'] ?? 'Not provided') . "</p>
                        <p><span class='label'>Registration Date:</span> " . date('M d, Y H:i') . "</p>
                    </div>

                    <div class='highlight'>
                        <h3>&#x1F510; Account Status</h3>
                        <p><span class='label'>Role:</span> <span class='badge badge-success'>" . ucfirst($user_data['role'] ?? 'customer') . "</span></p>
                        <p><span class='label'>Status:</span> <span class='badge badge-success'>" . ucfirst($user_data['status'] ?? 'active') . "</span></p>
                        <p><span class='label'>Email Verified:</span> " . ($user_data['email_verified'] ? '&#x2705; Yes' : '&#x26A0; Pending') . "</p>
                    </div>
                </div>

                <div class='highlight' style='text-align: center;'>
                    <h3>&#x1F680; Quick Actions</h3>
                    <p><a href='" . BASE_URL . "admin/users' class='button'>View All Users</a></p>
                    <p><a href='" . BASE_URL . "admin/users/edit?id=" . ($user_data['id'] ?? '') . "' class='button' style='background: #6c757d;'>Edit User</a></p>
                </div>

                <div class='footer'>
                    <p>&#x1F4E7; This notification was sent from APS Dream Home property management system.</p>
                    <p>New user registrations require admin approval for certain roles.</p>
                    <p><strong>Total Users:</strong> " . ($this->getUserCount() ?? 'N/A') . " | <strong>Admin Email:</strong> {$this->settings['admin_email']}</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get total user count for admin template
     */
    private function getUserCount() {
        try {
            global $pdo;
            if (!$pdo) {
                return null;
            }

            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;

        } catch (Exception $e) {
            return null;
        }
    }
}

?>
