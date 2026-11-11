<?php

namespace App\Services;

use App\Services\Communication\EmailService as CanonicalEmailService;
use App\Core\Database\Database;
use Exception;

use \App\Traits\ServiceTenantTrait;

/**
 * EmailService - Legacy wrapper for backward compatibility
 * 
 * @deprecated Use App\Services\Communication\EmailService instead
 * This class delegates to the canonical EmailService
 */
class EmailService
{
    use \App\Traits\ServiceTenantTrait;

    private $unifiedService;

    public function __construct()
    {
        $this->unifiedService = new \App\Services\Communication\EmailService();
    }

    /**
     * Create and configure a PHPMailer instance - delegates to unified service
     */
    private function createMailer()
    {
        // Delegate to unified service's internal mailer
        return $this->unifiedService->createMailerForLegacy();
    }

    /**
     * General email sending method - delegates to unified service
     */
    public function send($to, $subject, $body, $fromName = null)
    {
        try {
            return $this->unifiedService->send($to, $subject, $bodyHtml, '');
        } catch (Exception $e) {
            error_log("Email sending failed (legacy): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send verification email - delegates to unified service
     */
    public function sendVerificationEmail($to, $name, $token)
    {
        try {
            $subject = 'Verify Your Email Address';
            $appUrl = rtrim(BASE_URL, '/');
            $verificationUrl = $appUrl . "/verify-email?token=" . $token;
            
            $message = $this->getVerificationEmailTemplate($name, $verificationUrl);
            
            return $this->unifiedService->send($to, $subject, $message, '');
        } catch (Exception $e) {
            error_log("Email sending failed (legacy): " . $e->getMessage());
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
            $result = $this->unifiedService->send($to, $subject, $message, '');
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
            $result = $this->unifiedService->send($adminEmail, $subject, $message, '');
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
            $result = $this->unifiedService->send($userEmail, $subject, $message, '');
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
            $result = $this->unifiedService->send($userEmail, $subject, $message, '');
            return ['success' => $result, 'message' => $result ? 'Newsletter confirmation sent' : 'Email sending failed'];
        } catch (Exception $e) {
            error_log("Newsletter confirmation failed: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Legacy template methods for backward compatibility
     */
    private function getVerificationEmailTemplate($name, $verificationUrl)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Verify Your Email Address</title>
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
                    <h1>Verify Your Email Address</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$name}!</h2>
                    <p>Thank you for registering with APS Dream Home. Please click the button below to verify your email address:</p>
                    <p style='text-align: center;'>
                        <a href='{$verificationUrl}' class='button'>Verify Email Address</a>
                    </p>
                    <p>If you didn't create an account, please ignore this email.</p>
                    <p>The link will expire in 24 hours.</p>
                </div>
                <div class='footer'>
                    <p>APS Dream Home - Your Dream Home Partner</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private function getWelcomeEmailTemplate($userName)
    {
        $baseUrl = rtrim(BASE_URL, '/');
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
                        <li>📞 <strong>Contact users:</strong> Directly inquire about properties you're interested in</li>
                        <li>👤 <strong>Manage Profile:</strong> Update your preferences and account information</li>
                    </ul>

                    <a href='{$baseUrl}/user/dashboard' class='button'>Explore Your Dashboard</a>

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
        $baseUrl = rtrim(BASE_URL, '/');
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
                        <a href='{$baseUrl}/admin/inquiries' class='button'>View All Inquiries</a>
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
        $baseUrl = rtrim(BASE_URL, '/');
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
                        <a href='{$baseUrl}/properties' class='button'>Browse More Properties</a>
                        <a href='{$baseUrl}/dashboard/inquiries' class='button' style='background: #6c757d; margin-left: 10px;'>View All Inquiries</a>
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
        $baseUrl = rtrim(BASE_URL, '/');
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
                        <li>📊 <strong>Success Stories:</strong> Real stories from our satisfied users</li>
                    </ul>

                    <p>We'll only send you valuable content and never spam your inbox. You can unsubscribe at any time.</p>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$baseUrl}/properties' class='button'>Explore Properties Now</a>
                    </div>

                    <p>Thank you for choosing APS Dream Home for your real estate needs!</p>
                    <p>Best regards,<br><strong>The APS Dream Home Team</strong></p>
                </div>
                <div class='footer'>
                    <p>You received this email because you subscribed to our newsletter.</p>
                    <p><a href='{$baseUrl}/unsubscribe'>Unsubscribe</a> if you no longer wish to receive these emails.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Send lead welcome email
     */
    public function sendLeadWelcomeEmail($to, $name)
    {
        try {
            $subject = 'Special Property Offers for You - APS Dream Home';
            $message = "
                <h2>Hello {$name}!</h2>
                <p>Thank you for your interest in APS Dream Home. We have received your inquiry and our team will contact you shortly.</p>
                <p>In the meantime, feel free to browse our latest properties.</p>
                <br>
                <p>Best regards,<br>Modern Real Estate Team</p>
            ";

            return $this->unifiedService->send($to, $subject, $message, '');
        } catch (Exception $e) {
            error_log("Lead welcome email failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email with attachment
     */
    public function sendWithAttachment($to, $subject, $body, $attachmentPath, $attachmentName)
    {
        try {
            return $this->unifiedService->sendWithAttachment($to, $subject, $body, $attachmentPath, $attachmentName);
        } catch (\Throwable $e) {
            error_log("Email with attachment failed: " . $e->getMessage());
            return false;
        }
    }
}