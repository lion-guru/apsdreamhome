<?php

namespace App\Http\Controllers\Api;

use \Exception;
use App\Http\Middleware\RateLimitMiddleware;

class CommunicationController extends BaseApiController
{
    private $rateLimiter;
    private $automationService;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth', ['except' => ['whatsappWebhook', 'telegramWebhook', 'smsWebhook']]);
        $this->middleware('role:admin', ['except' => ['whatsappWebhook', 'telegramWebhook', 'smsWebhook']]);
        $this->rateLimiter = new RateLimitMiddleware();
        $this->automationService = new \App\Services\Communication\CommunicationAutomationService();
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
            $this->rateLimiter->handle('email', function ($req) { return true; }, 'email');

            $to = $this->validateInput($this->inputWithJson('to', ''), 'email');
            $subject = $this->validateInput($this->inputWithJson('subject', ''), 'subject', 200);
            $message = $this->validateInput($this->inputWithJson('message', ''), 'message', 10000);

            if (!$to) return $this->jsonError('Invalid email address format', 400);
            if (!$subject) return $this->jsonError('Invalid subject format or too long', 400);
            if (!$message) return $this->jsonError('Invalid message format or too long', 400);

            if ($this->hasSuspiciousPatterns(\json_encode($this->getJsonInput()))) {
                $this->logSecurityEvent('Suspicious Input Pattern Detected in Email API', [
                    'ip_address' => $this->request()->getClientIp()
                ]);
                return $this->jsonError('Suspicious content detected', 400);
            }

            $result = $this->automationService->sendMessage('email', $to, $message);

            if ($result['success']) {
                return $this->jsonSuccess(null, 'Email sent successfully');
            } else {
                return $this->jsonError('Failed to send email: ' . ($result['error'] ?? 'Unknown error'), 500);
            }
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Send WhatsApp message
     */
    public function sendWhatsApp()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $this->rateLimiter->handle('whatsapp', function ($req) { return true; }, 'whatsapp');

            $to = $this->validateInput($this->inputWithJson('to', ''), 'phone');
            $message = $this->validateInput($this->inputWithJson('message', ''), 'message', 1000);

            if (!$to) return $this->jsonError('Invalid phone number format. Must be 10-15 digits.', 400);
            if (!$message) return $this->jsonError('Invalid message format or too long', 400);

            if ($this->hasSuspiciousPatterns(\json_encode($this->getJsonInput()))) {
                $this->logSecurityEvent('Suspicious Input Pattern Detected in WhatsApp API', [
                    'ip_address' => $this->request()->getClientIp()
                ]);
                return $this->jsonError('Suspicious content detected', 400);
            }

            $result = $this->automationService->sendMessage('whatsapp', $to, $message);

            if ($result['success']) {
                return $this->jsonSuccess(['message_id' => $result['message_id'] ?? null], 'WhatsApp message sent successfully');
            } else {
                return $this->jsonError('Failed to send WhatsApp message: ' . ($result['error'] ?? 'Unknown error'), 500);
            }
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Send Telegram message
     */
    public function sendTelegram()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $this->rateLimiter->handle('telegram', function ($req) { return true; }, 'telegram');

            $chatId = $this->inputWithJson('chat_id', '');
            $message = $this->validateInput($this->inputWithJson('message', ''), 'message', 4000);

            if (!$chatId) return $this->jsonError('Chat ID is required', 400);
            if (!$message) return $this->jsonError('Message is required', 400);

            $result = $this->automationService->sendMessage('telegram', $chatId, $message);

            if ($result['success']) {
                return $this->jsonSuccess(['message_id' => $result['message_id'] ?? null], 'Telegram message sent successfully');
            } else {
                return $this->jsonError('Failed to send Telegram message: ' . ($result['error'] ?? 'Unknown error'), 500);
            }
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Send SMS message
     */
    public function sendSMS()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $this->rateLimiter->handle('sms', function ($req) { return true; }, 'sms');

            $to = $this->validateInput($this->inputWithJson('to', ''), 'phone');
            $message = $this->validateInput($this->inputWithJson('message', ''), 'message', 160);

            if (!$to) return $this->jsonError('Invalid phone number format', 400);
            if (!$message) return $this->jsonError('Message is required', 400);

            $result = $this->automationService->sendMessage('sms', $to, $message);

            if ($result['success']) {
                return $this->jsonSuccess(null, 'SMS sent successfully');
            } else {
                return $this->jsonError('Failed to send SMS: ' . ($result['error'] ?? 'Unknown error'), 500);
            }
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Validate and sanitize input
     */
    protected function validateInput($input, $type = 'string', $max_length = null)
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

        try {
            $this->db->execute(
                "INSERT INTO security_logs (action, details, ip_address, created_at) VALUES (?, ?, ?, NOW())",
                [$event, \json_encode($context), $this->request()->getClientIp()]
            );
        } catch (\Exception $e) {
            error_log("CommunicationController.php: " . $e->getMessage());
        }
    }

    /**
     * WhatsApp Webhook handler
     */
    public function whatsappWebhook()
    {
        $method = $this->request()->getMethod();

        if ($method === 'GET') {
            $mode = $this->request()->input('hub_mode', '');
            $token = $this->request()->input('hub_verify_token', '');
            $challenge = $this->request()->input('hub_challenge', '');

            $whatsappService = new \App\Services\Communication\WhatsAppService();
            $verified = $whatsappService->verifyWebhook($mode, $token);

            if ($verified) {
                if (!\headers_sent()) {
                    \header('Content-Type: text/plain');
                }
                echo $verified;
                exit;
            } else {
                return $this->jsonError('Invalid verification token', 403);
            }
        }

        if ($method === 'POST') {
            try {
                $data = $this->getJsonInput();

                if (!$data) {
                    return $this->jsonError('Invalid JSON data', 400);
                }

                // Process via CommunicationAutomationService
                $result = $this->automationService->processIncomingMessage('whatsapp', $data);

                if ($result['success']) {
                    return $this->jsonSuccess([
                        'processed' => $result['processed'] ?? 0,
                        'reply_sent' => $result['reply_sent'] ?? false,
                        'lead_created' => $result['lead_created'] ?? false,
                        'lead_id' => $result['lead_id'] ?? null
                    ], 'Webhook processed successfully');
                } else {
                    return $this->jsonError('Failed to process webhook: ' . ($result['error'] ?? 'Unknown error'), 500);
                }

            } catch (\Exception $e) {
                logger('WhatsApp webhook error: ' . $e->getMessage());
                return $this->jsonError('Internal server error', 500);
            }
        }

        return $this->jsonError('Method not allowed', 405);
    }

    /**
     * Telegram Webhook handler
     */
    public function telegramWebhook()
    {
        $method = $this->request()->getMethod();

        if ($method === 'GET') {
            // Telegram webhook verification (optional)
            return $this->jsonSuccess(['status' => 'ok'], 'Telegram webhook endpoint active');
        }

        if ($method === 'POST') {
            try {
                $data = $this->getJsonInput();

                if (!$data) {
                    return $this->jsonError('Invalid JSON data', 400);
                }

                // Process via CommunicationAutomationService
                $result = $this->automationService->processIncomingMessage('telegram', $data);

                if ($result['success']) {
                    return $this->jsonSuccess([
                        'processed' => $result['processed'] ?? 0,
                        'reply_sent' => $result['reply_sent'] ?? false,
                        'lead_created' => $result['lead_created'] ?? false,
                        'lead_id' => $result['lead_id'] ?? null
                    ], 'Webhook processed successfully');
                } else {
                    return $this->jsonError('Failed to process webhook: ' . ($result['error'] ?? 'Unknown error'), 500);
                }

            } catch (\Exception $e) {
                logger('Telegram webhook error: ' . $e->getMessage());
                return $this->jsonError('Internal server error', 500);
            }
        }

        return $this->jsonError('Method not allowed', 405);
    }

    /**
     * SMS Webhook handler (MSG91 incoming)
     */
    public function smsWebhook()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            // MSG91 sends form data for incoming SMS
            $data = array_merge($this->request()->all(), $this->getJsonInput() ?? []);

            if (!$data) {
                return $this->jsonError('Invalid data', 400);
            }

            $result = $this->automationService->processIncomingMessage('sms', $data);

            if ($result['success']) {
                return $this->jsonSuccess([
                    'processed' => $result['processed'] ?? 0,
                    'reply_sent' => $result['reply_sent'] ?? false,
                    'lead_created' => $result['lead_created'] ?? false,
                    'lead_id' => $result['lead_id'] ?? null
                ], 'SMS webhook processed successfully');
            } else {
                return $this->jsonError('Failed to process SMS webhook: ' . ($result['error'] ?? 'Unknown error'), 500);
            }

        } catch (\Exception $e) {
            logger('SMS webhook error: ' . $e->getMessage());
            return $this->jsonError('Internal server error', 500);
        }
    }

    /**
     * Get channel status
     */
    public function channelStatus()
    {
        try {
            $channels = [
                'whatsapp' => $this->automationService->isChannelEnabled('whatsapp'),
                'telegram' => $this->automationService->isChannelEnabled('telegram'),
                'sms' => $this->automationService->isChannelEnabled('sms'),
                'email' => $this->automationService->isChannelEnabled('email'),
            ];

            return $this->jsonSuccess($channels, 'Channel status retrieved');
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
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