<?php
/**
 * Email Manager Class
 * Handles all email operations for APS Dream Home
 */

class EmailManager {
    private $config;
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $smtp_encryption;
    private $from_email;
    private $from_name;
    
    public function __construct($config = null) {
        if ($config === null) {
            $this->loadDefaultConfig();
        } else {
            $this->config = $config;
            $this->initializeFromConfig();
        }
    }
    
    private function loadDefaultConfig() {
        $this->smtp_host = 'smtp.gmail.com';
        $this->smtp_port = 587;
        $this->smtp_username = 'apsdreamhomes44@gmail.com';
        $this->smtp_password = 'Aps@1601';
        $this->smtp_encryption = 'tls';
        $this->from_email = 'apsdreamhomes44@gmail.com';
        $this->from_name = 'APS Dream Home';
    }
    
    private function initializeFromConfig() {
        $this->smtp_host = $this->config['smtp_host'] ?? 'smtp.gmail.com';
        $this->smtp_port = $this->config['smtp_port'] ?? 587;
        $this->smtp_username = $this->config['smtp_username'] ?? '';
        $this->smtp_password = $this->config['smtp_password'] ?? '';
        $this->smtp_encryption = $this->config['smtp_encryption'] ?? 'tls';
        $this->from_email = $this->config['from_email'] ?? '';
        $this->from_name = $this->config['from_name'] ?? 'APS Dream Home';
    }
    
    /**
     * Send email using PHP's mail() function
     */
    public function sendEmail($to, $subject, $message, $headers = []) {
        $default_headers = [
            'From' => $this->from_name . ' <' . $this->from_email . '>',
            'Reply-To' => $this->from_email,
            'X-Mailer' => 'APS Dream Home Mailer',
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
        
        $headers = array_merge($default_headers, $headers);
        $header_string = '';
        foreach ($headers as $key => $value) {
            $header_string .= $key . ': ' . $value . "\r\n";
        }
        
        return mail($to, $subject, $message, $header_string);
    }
    
    /**
     * Send contact form email
     */
    public function sendContactForm($data) {
        $subject = 'New Contact Form Submission - ' . $data['name'];
        $message = $this->buildContactEmailTemplate($data);
        
        $to = $this->from_email;
        $headers = [
            'Reply-To' => $data['email']
        ];
        
        return $this->sendEmail($to, $subject, $message, $headers);
    }
    
    /**
     * Send property inquiry email
     */
    public function sendPropertyInquiry($data) {
        $subject = 'Property Inquiry - ' . $data['property_title'];
        $message = $this->buildPropertyInquiryTemplate($data);
        
        $to = $this->from_email;
        $headers = [
            'Reply-To' => $data['email']
        ];
        
        return $this->sendEmail($to, $subject, $message, $headers);
    }
    
    /**
     * Send job application email
     */
    public function sendJobApplication($data) {
        $subject = 'Job Application - ' . $data['position'];
        $message = $this->buildJobApplicationTemplate($data);
        
        $to = $this->from_email;
        $headers = [
            'Reply-To' => $data['email']
        ];
        
        return $this->sendEmail($to, $subject, $message, $headers);
    }
    
    /**
     * Build contact form email template
     */
    private function buildContactEmailTemplate($data) {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .field { margin: 10px 0; }
                .label { font-weight: bold; color: #2c3e50; }
                .value { margin-left: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>New Contact Form Submission</h2>
                </div>
                <div class='content'>
                    <div class='field'>
                        <span class='label'>Name:</span>
                        <span class='value'>{$data['name']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Email:</span>
                        <span class='value'>{$data['email']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Phone:</span>
                        <span class='value'>{$data['phone']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Subject:</span>
                        <span class='value'>{$data['subject']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Message:</span>
                        <div class='value'>{$data['message']}</div>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Build property inquiry email template
     */
    private function buildPropertyInquiryTemplate($data) {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #27ae60; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .field { margin: 10px 0; }
                .label { font-weight: bold; color: #27ae60; }
                .value { margin-left: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Property Inquiry</h2>
                </div>
                <div class='content'>
                    <div class='field'>
                        <span class='label'>Property:</span>
                        <span class='value'>{$data['property_title']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Name:</span>
                        <span class='value'>{$data['name']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Email:</span>
                        <span class='value'>{$data['email']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Phone:</span>
                        <span class='value'>{$data['phone']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Message:</span>
                        <div class='value'>{$data['message']}</div>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Build job application email template
     */
    private function buildJobApplicationTemplate($data) {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #8e44ad; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .field { margin: 10px 0; }
                .label { font-weight: bold; color: #8e44ad; }
                .value { margin-left: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Job Application</h2>
                </div>
                <div class='content'>
                    <div class='field'>
                        <span class='label'>Position:</span>
                        <span class='value'>{$data['position']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Name:</span>
                        <span class='value'>{$data['name']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Email:</span>
                        <span class='value'>{$data['email']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Phone:</span>
                        <span class='value'>{$data['phone']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Experience:</span>
                        <span class='value'>{$data['experience']}</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Message:</span>
                        <div class='value'>{$data['message']}</div>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Test email configuration
     */
    public function testEmailConfig() {
        $test_data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'subject' => 'Test Email',
            'message' => 'This is a test email to verify email configuration.'
        ];
        
        return $this->sendContactForm($test_data);
    }
}
?>
