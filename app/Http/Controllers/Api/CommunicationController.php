<?php

namespace App\Http\Controllers\Api;

use \Exception;
use App\Common\Middleware\RateLimitMiddleware;

class CommunicationController extends BaseApiController
{
    private $rateLimiter;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth', ['except' => ['whatsappWebhook']]);
        $this->middleware('role:admin', ['except' => ['whatsappWebhook']]);
        $this->rateLimiter = new RateLimitMiddleware();

        // Load legacy integrations if needed
        require_once \dirname(\dirname(\dirname(\dirname(__DIR__)))) . '/includes/whatsapp_integration.php';
        require_once \dirname(\dirname(\dirname(\dirname(__DIR__)))) . '/includes/email_service.php';
        require_once \dirname(\dirname(\dirname(\dirname(__DIR__)))) . '/includes/sms_service.php';
    }

    /**
     * Send email
     */
    public function sendEmail()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            // Apply rate limiting
            $this->rateLimiter->handle('email');

            $to = $this->validateInput($this->request()->input('to', ''), 'email');
            $subject = $this->validateInput($this->request()->input('subject', ''), 'subject', 200);
            $message = $this->validateInput($this->request()->input('message', ''), 'message', 10000);

            if (!$to) return $this->jsonError('Invalid email address format', 400);
            if (!$subject) return $this->jsonError('Invalid subject format or too long', 400);
            if (!$message) return $this->jsonError('Invalid message format or too long', 400);

            // Check for suspicious patterns
            if ($this->hasSuspiciousPatterns(\json_encode($this->request()->all()))) {
                $this->logSecurityEvent('Suspicious Input Pattern Detected in Email API', [
                    'ip_address' => $this->request()->getClientIp()
                ]);
                return $this->jsonError('Suspicious content detected', 400);
            }

            // Send email
            $emailService = new \EmailService(null, $this->db);
            $sent = $emailService->send($to, $subject, $message);

            if ($sent) {
                return $this->jsonSuccess(null, 'Email sent successfully');
            } else {
                return $this->jsonError('Failed to send email. Please try again.', 500);
            }
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Send SMS
     */
    public function sendSms()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            // Apply rate limiting
            $this->rateLimiter->handle('sms');

            $to = $this->validateInput($this->request()->input('to', ''), 'phone');
            $message = $this->validateInput($this->request()->input('message', ''), 'message', 160);

            if (!$to) return $this->jsonError('Invalid phone number format. Must be 10-15 digits.', 400);
            if (!$message) return $this->jsonError('Invalid message format or too long', 400);

            // Check for suspicious patterns
            if ($this->hasSuspiciousPatterns(\json_encode($this->request()->all()))) {
                $this->logSecurityEvent('Suspicious Input Pattern Detected in SMS API', [
                    'ip_address' => $this->request()->getClientIp()
                ]);
                return $this->jsonError('Suspicious content detected', 400);
            }

            // Send SMS
            $smsService = new \SMSService(null, $this->db);
            $sent = $smsService->send($to, $message);

            if ($sent) {
                return $this->jsonSuccess(null, 'SMS sent successfully');
            } else {
                return $this->jsonError('Failed to send SMS. Please try again.', 500);
            }
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Validate and sanitize input
     */
    private function validateInput($input, $type = 'string', $max_length = null)
    {
        $input = \trim($input);
        if (empty($input)) return false;

        switch ($type) {
            case 'email':
                $input = \filter_var($input, \FILTER_SANITIZE_EMAIL);
                if (!\filter_var($input, \FILTER_VALIDATE_EMAIL)) return false;
                break;
            case 'phone':
                $input = \preg_replace('/[^\d+\s]/', '', $input);
                if (\strlen($input) < 10 || \strlen($input) > 15) return false;
                break;
            case 'subject':
                if (!\preg_match('/^[a-zA-Z0-9\s\-_.,!?()]+$/', $input)) return false;
                $input = h($input);
                break;
            default:
                $input = h($input);
                break;
        }

        if ($max_length && \strlen($input) > $max_length) return false;
        return $input;
    }

    /**
     * Check for suspicious patterns
     */
    private function hasSuspiciousPatterns($input)
    {
        $suspicious_patterns = ['<script', 'javascript:', 'onload=', 'onerror=', 'eval(', 'alert(', 'document.cookie', 'iframe', 'embed'];
        foreach ($suspicious_patterns as $pattern) {
            if (\stripos($input, $pattern) !== false) return true;
        }
        return false;
    }

    /**
     * Log security events
     */
    private function logSecurityEvent($event, $context = [])
    {
        $logMessage = \sprintf("[%s] %s | Context: %s", \date('Y-m-d H:i:s'), $event, \json_encode($context));
        \error_log($logMessage);

        // Also log to DB if table exists
        try {
            $this->db->execute(
                "INSERT INTO security_logs (event, context, ip_address, created_at) VALUES (?, ?, ?, NOW())",
                [$event, \json_encode($context), $this->request()->getClientIp()]
            );
        } catch (\Exception $e) {
            // Silently fail if table doesn't exist
        }
    }

    /**
     * WhatsApp Webhook handler
     */
    public function whatsappWebhook()
    {
        $method = $this->request()->getMethod();

        // Handle verification (GET)
        if ($method === 'GET') {
            $verifyToken = $this->request()->input('hub_verify_token', '');
            $challenge = $this->request()->input('hub_challenge', '');

            // In a real app, this would come from a config/env
            $expectedToken = $_ENV['WHATSAPP_VERIFY_TOKEN'] ?? 'aps_dream_home_verify';

            if ($verifyToken === $expectedToken) {
                if (!\headers_sent()) {
                    \header('Content-Type: text/plain');
                }
                echo $challenge;
                exit;
            } else {
                return $this->jsonError('Invalid verification token', 403);
            }
        }

        // Handle incoming messages (POST)
        if ($method === 'POST') {
            try {
                $data = $this->request()->all();

                if (!$data) {
                    return $this->jsonError('Invalid JSON data', 400);
                }

                // Process via WhatsAppIntegration class
                $whatsapp = new \WhatsAppIntegration();
                if (method_exists($whatsapp, 'handleIncomingWebhook')) {
                    $whatsapp->handleIncomingWebhook($data);
                } else {
                    $this->logWebhook($data);
                }

                return $this->jsonSuccess(['status' => 'success']);

            } catch (Exception $e) {
                error_log('WhatsApp webhook error: ' . $e->getMessage());
                return $this->jsonError('Internal server error', 500);
            }
        }

        return $this->jsonError('Method not allowed', 405);
    }

    /**
     * Log webhook data for debugging
     */
    private function logWebhook($data)
    {
        $this->db->execute(
            "INSERT INTO webhook_logs (source, payload, created_at) VALUES (?, ?, NOW())",
            ['whatsapp', json_encode($data)]
        );
    }
}
