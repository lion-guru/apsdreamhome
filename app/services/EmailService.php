<?php

namespace App\Services;

use Exception;

class EmailService
{
    private $config;

    public function __construct()
    {
        $this->config = [
            'host' => getenv('MAIL_HOST') ?: 'localhost',
            'username' => getenv('MAIL_USERNAME') ?: '',
            'password' => getenv('MAIL_PASSWORD') ?: '',
            'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@apsdreamhome.com',
            'from_name' => getenv('MAIL_FROM_NAME') ?: 'APS Dream Home'
        ];
    }

    /**
     * General email sending method
     */
    public function send($to, $subject, $body, $fromName = null)
    {
        try {
            $headers = [
                'From: ' . ($fromName ? "$fromName <{$this->config['from_address']}>" : "{$this->config['from_name']} <{$this->config['from_address']}>"),
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8'
            ];

            return mail($to, $subject, $body, implode("\r\n", $headers));
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send verification email
     */
    public function sendVerificationEmail($to, $name, $token)
    {
        try {
            $subject = 'Verify Your Email Address';

            // Email body
            $verificationUrl = "http://localhost/apsdreamhome/verify-email?token=" . $token;
            $message = file_get_contents(__DIR__ . '/../../resources/emails/verification.html');
            $message = str_replace(
                ['{{name}}', '{{verification_url}}'],
                [$name, $verificationUrl],
                $message
            );

            $headers = [
                'From: ' . "{$this->config['from_name']} <{$this->config['from_address']}>",
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8'
            ];

            $result = mail($to, $subject, $message, implode("\r\n", $headers));
            return $result;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail($to, $name)
    {
        try {
            $subject = 'Welcome to APS Dream Home - Your Account is Ready!';
            $message = $this->getWelcomeEmailTemplate($name);

            $headers = [
                'From: ' . "{$this->config['from_name']} <{$this->config['from_address']}>",
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8'
            ];

            $result = mail($to, $subject, $message, implode("\r\n", $headers));
            return ['success' => $result, 'message' => $result ? 'Welcome email sent successfully' : 'Email sending failed'];
        } catch (Exception $e) {
            error_log("Welcome email failed: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send property inquiry notification to admin
     */
    public function sendPropertyInquiryNotification($inquiryData)
    {
        try {
            $adminEmail = getenv('ADMIN_EMAIL') ?: 'admin@apsdreamhome.com';
            $subject = 'New Property Inquiry - ' . $inquiryData['property_title'];

            $message = $this->getInquiryNotificationTemplate($inquiryData);

            $headers = [
                'From: ' . "{$this->config['from_name']} <{$this->config['from_address']}>",
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8'
            ];

            $result = mail($adminEmail, $subject, $message, implode("\r\n", $headers));
            return ['success' => $result, 'message' => $result ? 'Inquiry notification sent to admin' : 'Email sending failed'];
        } catch (Exception $e) {
            error_log("Inquiry notification failed: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send inquiry response to user
     */
    public function sendInquiryResponse($userEmail, $userName, $propertyTitle, $response)
    {
        try {
            $subject = 'Response to Your Property Inquiry - ' . $propertyTitle;

            $message = $this->getInquiryResponseTemplate($userName, $propertyTitle, $response);

            $headers = [
                'From: ' . "{$this->config['from_name']} <{$this->config['from_address']}>",
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8'
            ];

            $result = mail($userEmail, $subject, $message, implode("\r\n", $headers));
            return ['success' => $result, 'message' => $result ? 'Inquiry response sent to user' : 'Email sending failed'];
        } catch (Exception $e) {
            error_log("Inquiry response failed: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send newsletter subscription confirmation
     */
    public function sendNewsletterConfirmation($userEmail, $userName)
    {
        try {
            $subject = 'Welcome to APS Dream Home Newsletter';

            $message = $this->getNewsletterConfirmationTemplate($userName);

            $headers = [
                'From: ' . "{$this->config['from_name']} <{$this->config['from_address']}>",
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8'
            ];

            $result = mail($userEmail, $subject, $message, implode("\r\n", $headers));
            return ['success' => $result, 'message' => $result ? 'Newsletter confirmation sent' : 'Email sending failed'];
        } catch (Exception $e) {
            error_log("Newsletter confirmation failed: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get welcome email template
     */
    private function getWelcomeEmailTemplate($userName)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Welcome to APS Dream Home</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #0d6efd, #6610f2); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { display: inline-block; background: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to APS Dream Home! 🏠</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$userName}!</h2>
                    <p>Thank you for joining APS Dream Home! Your account has been successfully created and you're now part of our real estate community.</p>

                    <p>Here's what you can do with your new account:</p>
                    <ul>
                        <li>🔍 <strong>Browse Properties:</strong> Explore our extensive collection of residential, commercial, and land properties</li>
                        <li>❤️ <strong>Save Favorites:</strong> Bookmark properties you love and track price changes</li>
                        <li>📧 <strong>Get Alerts:</strong> Receive notifications for new properties matching your preferences</li>
                        <li>📞 <strong>Contact Agents:</strong> Directly inquire about properties you're interested in</li>
                        <li>👤 <strong>Manage Profile:</strong> Update your preferences and account information</li>
                    </ul>

                    <a href='http://localhost:8000/dashboard' class='button'>Explore Your Dashboard</a>

                    <p>If you have any questions or need assistance, feel free to contact our support team.</p>

                    <p>Happy property hunting!</p>
                    <p><strong>The APS Dream Home Team</strong></p>
                </div>
                <div class='footer'>
                    <p>This email was sent to you because you created an account with APS Dream Home.</p>
                    <p>If you didn't create this account, please ignore this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get inquiry notification template for admin
     */
    private function getInquiryNotificationTemplate($inquiryData)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>New Property Inquiry</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #dc3545, #fd7e14); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .info-box { background: white; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #0d6efd; }
                .button { display: inline-block; background: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📧 New Property Inquiry</h1>
                </div>
                <div class='content'>
                    <h2>You have received a new property inquiry!</h2>

                    <div class='info-box'>
                        <h4>Property Details</h4>
                        <p><strong>Property:</strong> {$inquiryData['property_title']}</p>
                        <p><strong>Location:</strong> {$inquiryData['location']}</p>
                        <p><strong>Price:</strong> ₹" . number_format($inquiryData['price']) . "</p>
                    </div>

                    <div class='info-box'>
                        <h4>Customer Details</h4>
                        <p><strong>Name:</strong> {$inquiryData['name']}</p>
                        <p><strong>Email:</strong> {$inquiryData['email']}</p>
                        <p><strong>Phone:</strong> {$inquiryData['phone']}</p>
                    </div>

                    <div class='info-box'>
                        <h4>Message</h4>
                        <p>" . nl2br(htmlspecialchars($inquiryData['message'])) . "</p>
                    </div>

                    <div class='info-box'>
                        <h4>Action Required</h4>
                        <p>Please respond to this inquiry within 24 hours to maintain good customer service.</p>
                        <a href='http://localhost:8000/admin/inquiries' class='button'>View All Inquiries</a>
                    </div>

                    <p><strong>Inquiry Date:</strong> " . date('M j, Y \a\t g:i A', strtotime($inquiryData['created_at'])) . "</p>

                    <p>Best regards,<br><strong>APS Dream Home System</strong></p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get inquiry response template for user
     */
    private function getInquiryResponseTemplate($userName, $propertyTitle, $response)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Inquiry Response</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #198754, #20c997); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .response-box { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #198754; }
                .button { display: inline-block; background: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📬 Response to Your Inquiry</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$userName}!</h2>
                    <p>Thank you for your interest in <strong>{$propertyTitle}</strong>. We've received your inquiry and here's our response:</p>

                    <div class='response-box'>
                        " . nl2br(htmlspecialchars($response)) . "
                    </div>

                    <p>If you have any further questions or need additional information, please don't hesitate to contact us.</p>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost:8000/properties' class='button'>Browse More Properties</a>
                        <a href='http://localhost:8000/dashboard/inquiries' class='button' style='background: #6c757d; margin-left: 10px;'>View All Inquiries</a>
                    </div>

                    <p>We hope to assist you in finding your dream property!</p>
                    <p>Best regards,<br><strong>The APS Dream Home Team</strong></p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get newsletter confirmation template
     */
    private function getNewsletterConfirmationTemplate($userName)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Newsletter Subscription Confirmed</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { display: inline-block; background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📧 Welcome to Our Newsletter!</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$userName}!</h2>
                    <p>Thank you for subscribing to APS Dream Home newsletter! You're now part of our community and will receive regular updates about:</p>

                    <ul>
                        <li>🏠 <strong>New Property Listings:</strong> Be the first to know about new properties in your preferred locations</li>
                        <li>📈 <strong>Market Insights:</strong> Get valuable information about real estate trends and market analysis</li>
                        <li>💡 <strong>Investment Tips:</strong> Expert advice on property investment opportunities</li>
                        <li>🏆 <strong>Exclusive Offers:</strong> Special deals and discounts for our newsletter subscribers</li>
                        <li>📊 <strong>Success Stories:</strong> Real stories from our satisfied customers</li>
                    </ul>

                    <p>We'll only send you valuable content and never spam your inbox. You can unsubscribe at any time.</p>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost:8000/properties' class='button'>Explore Properties Now</a>
                    </div>

                    <p>Thank you for choosing APS Dream Home for your real estate needs!</p>
                    <p>Best regards,<br><strong>The APS Dream Home Team</strong></p>
                </div>
                <div class='footer'>
                    <p>You received this email because you subscribed to our newsletter.</p>
                    <p><a href='http://localhost:8000/unsubscribe'>Unsubscribe</a> if you no longer wish to receive these emails.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
