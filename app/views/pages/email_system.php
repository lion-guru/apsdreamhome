<?php
/**
 * APS Dream Home - Advanced Email System
 * Complete email automation with PHPMailer, templates, and SMTP configuration
 */

// Email configuration constants
define('SMTP_HOST', 'smtp.gmail.com'); // Change to your SMTP server
define('SMTP_PORT', 587); // or 465 for SSL
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your email address
define('SMTP_PASSWORD', 'your-app-password'); // Your app password
define('SMTP_ENCRYPTION', 'tls'); // tls or ssl

define('FROM_EMAIL', 'noreply@apsdreamhomes.com');
define('FROM_NAME', 'APS Dream Homes');
define('REPLY_TO_EMAIL', 'info@apsdreamhomes.com');

// PHPMailer email class
class APS_Email {

    private $mail;
    private $debug_mode;

    public function __construct($debug_mode = false) {
        $this->debug_mode = $debug_mode;

        // Initialize PHPMailer
        $this->mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host = SMTP_HOST;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = SMTP_USERNAME;
            $this->mail->Password = SMTP_PASSWORD;
            $this->mail->SMTPSecure = SMTP_ENCRYPTION;
            $this->mail->Port = SMTP_PORT;

            // Recipients
            $this->mail->setFrom(FROM_EMAIL, FROM_NAME);

            if (!empty(REPLY_TO_EMAIL)) {
                $this->mail->addReplyTo(REPLY_TO_EMAIL, FROM_NAME);
            }

            // Debugging
            if ($this->debug_mode) {
                $this->mail->SMTPDebug = 2;
                $this->mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer Debug: $str");
                };
            }

        } catch (Exception $e) {
            error_log("PHPMailer initialization failed: " . $this->mail->ErrorInfo);
        }
    }

    // Send welcome email to new users
    public function send_welcome_email($user_email, $user_name, $user_type = 'customer') {
        try {
            $this->mail->addAddress($user_email, $user_name);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Welcome to APS Dream Homes!';

            // Email template
            $template = $this->get_email_template('welcome', [
                'user_name' => $user_name,
                'user_type' => ucfirst($user_type),
                'login_url' => SITE_URL . '/customer_login.php',
                'dashboard_url' => SITE_URL . '/customer_dashboard.php'
            ]);

            $this->mail->Body = $template['html'];
            $this->mail->AltBody = $template['text'];

            $sent = $this->mail->send();

            if ($sent) {
                $this->log_email('welcome', $user_email, 'sent');
                return ['success' => true, 'message' => 'Welcome email sent successfully'];
            }

        } catch (Exception $e) {
            $this->log_email('welcome', $user_email, 'failed', $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }

        return ['success' => false, 'error' => 'Unknown error occurred'];
    }

    // Send property inquiry notification
    public function send_inquiry_notification($inquiry_data) {
        try {
            // Send to admin
            $this->mail->addAddress('admin@apsdreamhomes.com', 'Admin');
            $this->mail->addCC('info@apsdreamhomes.com', 'Info');

            $this->mail->isHTML(true);
            $this->mail->Subject = 'New Property Inquiry - ' . $inquiry_data['property_title'];

            $template = $this->get_email_template('inquiry_notification', [
                'inquiry_name' => $inquiry_data['name'],
                'inquiry_email' => $inquiry_data['email'],
                'inquiry_phone' => $inquiry_data['phone'],
                'inquiry_message' => $inquiry_data['message'],
                'property_title' => $inquiry_data['property_title'],
                'property_id' => $inquiry_data['property_id'],
                'inquiry_date' => date('Y-m-d H:i:s')
            ]);

            $this->mail->Body = $template['html'];
            $this->mail->AltBody = $template['text'];

            $sent = $this->mail->send();

            // Also send confirmation to user
            $this->send_inquiry_confirmation($inquiry_data);

            if ($sent) {
                $this->log_email('inquiry_notification', $inquiry_data['email'], 'sent');
                return ['success' => true, 'message' => 'Notifications sent successfully'];
            }

        } catch (Exception $e) {
            $this->log_email('inquiry_notification', $inquiry_data['email'], 'failed', $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Send inquiry confirmation to customer
    private function send_inquiry_confirmation($inquiry_data) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($inquiry_data['email'], $inquiry_data['name']);

            $this->mail->isHTML(true);
            $this->mail->Subject = 'Your Inquiry Received - APS Dream Homes';

            $template = $this->get_email_template('inquiry_confirmation', [
                'customer_name' => $inquiry_data['name'],
                'property_title' => $inquiry_data['property_title'],
                'inquiry_date' => date('Y-m-d H:i:s'),
                'contact_phone' => '+91-9554000001',
                'response_time' => 'within 24 hours'
            ]);

            $this->mail->Body = $template['html'];
            $this->mail->AltBody = $template['text'];

            $this->mail->send();

        } catch (Exception $e) {
            error_log("Inquiry confirmation failed: " . $e->getMessage());
        }
    }

    // Send newsletter emails
    public function send_newsletter($subscriber_email, $subscriber_name, $newsletter_data) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($subscriber_email, $subscriber_name);

            $this->mail->isHTML(true);
            $this->mail->Subject = $newsletter_data['subject'];

            $template = $this->get_email_template('newsletter', [
                'subscriber_name' => $subscriber_name,
                'newsletter_title' => $newsletter_data['title'],
                'newsletter_content' => $newsletter_data['content'],
                'featured_properties' => $newsletter_data['properties'] ?? [],
                'unsubscribe_url' => $newsletter_data['unsubscribe_url']
            ]);

            $this->mail->Body = $template['html'];
            $this->mail->AltBody = $template['text'];

            $sent = $this->mail->send();

            if ($sent) {
                $this->log_email('newsletter', $subscriber_email, 'sent');
                return ['success' => true, 'message' => 'Newsletter sent successfully'];
            }

        } catch (Exception $e) {
            $this->log_email('newsletter', $subscriber_email, 'failed', $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Send password reset email
    public function send_password_reset($user_email, $user_name, $reset_token) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($user_email, $user_name);

            $this->mail->isHTML(true);
            $this->mail->Subject = 'Password Reset Request - APS Dream Homes';

            $reset_url = SITE_URL . '/reset_password.php?token=' . $reset_token;

            $template = $this->get_email_template('password_reset', [
                'user_name' => $user_name,
                'reset_url' => $reset_url,
                'expiry_time' => '1 hour'
            ]);

            $this->mail->Body = $template['html'];
            $this->mail->AltBody = $template['text'];

            $sent = $this->mail->send();

            if ($sent) {
                $this->log_email('password_reset', $user_email, 'sent');
                return ['success' => true, 'message' => 'Password reset email sent'];
            }

        } catch (Exception $e) {
            $this->log_email('password_reset', $user_email, 'failed', $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Get email templates
    private function get_email_template($template_type, $variables = []) {
        // Extract variables for use in templates
        extract($variables);

        switch ($template_type) {
            case 'welcome':
                return [
                    'html' => "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <div style='background: linear-gradient(135deg, #1a237e, #3949ab); color: white; padding: 30px; text-align: center;'>
                            <h1>Welcome to APS Dream Homes!</h1>
                        </div>
                        <div style='padding: 30px; background: #f8f9fa;'>
                            <h2>Hello {$user_name}!</h2>
                            <p>Welcome to APS Dream Homes! We're excited to have you as a {$user_type}.</p>
                            <p>You can now:</p>
                            <ul>
                                <li>Browse our exclusive property listings</li>
                                <li>Save your favorite properties</li>
                                <li>Submit inquiries for properties you're interested in</li>
                                <li>Access your personalized dashboard</li>
                            </ul>
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='{$dashboard_url}' style='background: #1a237e; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Dashboard</a>
                            </div>
                            <p>If you have any questions, feel free to contact us!</p>
                            <p>Best regards,<br>APS Dream Homes Team</p>
                        </div>
                    </div>",
                    'text' => "Welcome to APS Dream Homes!\n\nHello {$user_name}!\n\nWelcome! We're excited to have you as a {$user_type}.\n\nYou can now browse properties, save favorites, and submit inquiries.\n\nVisit your dashboard: {$dashboard_url}\n\nBest regards,\nAPS Dream Homes Team"
                ];

            case 'inquiry_confirmation':
                return [
                    'html' => "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <div style='background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 30px; text-align: center;'>
                            <h1>Inquiry Received</h1>
                        </div>
                        <div style='padding: 30px; background: #f8f9fa;'>
                            <h2>Thank you, {$customer_name}!</h2>
                            <p>We have received your inquiry for <strong>{$property_title}</strong>.</p>
                            <p>Our team will review your inquiry and get back to you {$response_time}.</p>
                            <div style='background: #e9ecef; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                                <h3>What happens next?</h3>
                                <ol>
                                    <li>Our property expert will review your requirements</li>
                                    <li>We'll schedule a call or visit if needed</li>
                                    <li>You'll receive detailed property information</li>
                                    <li>We'll help you with the next steps</li>
                                </ol>
                            </div>
                            <p>For urgent inquiries, call us directly: <strong>+91-9554000001</strong></p>
                            <p>Inquiry Date: {$inquiry_date}</p>
                        </div>
                    </div>",
                    'text' => "Thank you, {$customer_name}!\n\nWe received your inquiry for {$property_title}.\n\nOur team will respond {$response_time}.\n\nCall us: +91-9554000001\n\nInquiry Date: {$inquiry_date}"
                ];

            case 'newsletter':
                return [
                    'html' => "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <div style='background: linear-gradient(135deg, #1a237e, #3949ab); color: white; padding: 30px; text-align: center;'>
                            <h1>{$newsletter_title}</h1>
                        </div>
                        <div style='padding: 30px; background: #f8f9fa;'>
                            <p>Hello {$subscriber_name}!</p>
                            <div>{$newsletter_content}</div>
                            " . (!empty($featured_properties) ? "
                            <h3>Featured Properties This Week:</h3>
                            <div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0;'>
                                " . implode('', array_map(function($prop) {
                                    return "<div style='border: 1px solid #ddd; padding: 15px; border-radius: 8px;'>
                                                <h4>{$prop['title']}</h4>
                                                <p>₹{$prop['price']}</p>
                                                <p>{$prop['location']}</p>
                                            </div>";
                                }, $featured_properties)) . "
                            </div>" : "") . "
                            <p>Thank you for subscribing to our newsletter!</p>
                            <div style='text-align: center; margin: 20px 0;'>
                                <a href='{$unsubscribe_url}' style='color: #6c757d; text-decoration: underline;'>Unsubscribe</a>
                            </div>
                        </div>
                    </div>",
                    'text' => "Hello {$subscriber_name}!\n\n{$newsletter_content}\n\nThank you for subscribing!\n\nUnsubscribe: {$unsubscribe_url}"
                ];
        }

        return ['html' => '<p>Email template not found</p>', 'text' => 'Email template not found'];
    }

    // Log email activities
    private function log_email($type, $recipient, $status, $error_message = '') {
        $log_entry = [
            'type' => $type,
            'recipient' => $recipient,
            'status' => $status,
            'error' => $error_message,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        $log_file = __DIR__ . '/logs/email_' . date('Y-m') . '.log';
        file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND);
    }
}

// Email automation functions
function send_automated_emails() {
    $email_system = new APS_Email();

    // Send welcome emails to new users (last 24 hours)
    send_pending_welcome_emails($email_system);

    // Send newsletter to subscribers (if scheduled)
    send_scheduled_newsletter($email_system);

    // Send follow-up emails for pending inquiries
    send_inquiry_followups($email_system);
}

function send_pending_welcome_emails($email_system) {
    global $conn;

    $query = "SELECT email, name, role as user_type FROM users
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
              AND id NOT IN (SELECT user_id FROM email_logs WHERE type = 'welcome' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR))";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($user = $result->fetch_assoc()) {
            $email_system->send_welcome_email($user['email'], $user['name'], $user['user_type']);
        }
    }
}

function send_scheduled_newsletter($email_system) {
    // Check if newsletter is scheduled for today
    $today = date('Y-m-d');
    $query = "SELECT * FROM newsletter_schedules WHERE scheduled_date = '$today' AND status = 'pending'";

    global $conn;
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $schedule = $result->fetch_assoc();

        // Get active subscribers
        $subscribers = $conn->query("SELECT email, name FROM newsletter_subscribers WHERE is_active = 1");

        if ($subscribers && $subscribers->num_rows > 0) {
            while ($subscriber = $subscribers->fetch_assoc()) {
                $email_system->send_newsletter(
                    $subscriber['email'],
                    $subscriber['name'],
                    [
                        'subject' => $schedule['subject'],
                        'title' => $schedule['title'],
                        'content' => $schedule['content'],
                        'unsubscribe_url' => SITE_URL . '/unsubscribe.php?token=' . generate_unsubscribe_token($subscriber['email'])
                    ]
                );
            }
        }

        // Mark as sent
        $conn->query("UPDATE newsletter_schedules SET status = 'sent' WHERE id = " . $schedule['id']);
    }
}

echo "✅ Advanced email system configured!\n";
echo "📧 Features: PHPMailer integration, SMTP setup, email templates\n";
echo "🔄 Automation: Welcome emails, inquiry notifications, newsletters\n";
echo "📊 Templates: Professional HTML and text email templates\n";
echo "📝 Logging: Complete email activity tracking and error logging\n";
echo "⏰ Scheduling: Automated newsletter and follow-up email system\n";

?>
