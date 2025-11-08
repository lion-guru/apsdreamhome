<?php
/**
 * SMS Notification Handler
 * Handles SMS notifications using configurable SMS gateways
 */

class SmsNotifier {
    private $config;
    private $gateway;

    public function __construct() {
        $this->loadConfig();
        $this->initializeGateway();
    }

    /**
     * Load SMS configuration
     */
    private function loadConfig() {
        $this->config = [
            'gateway' => defined('SMS_GATEWAY') ? SMS_GATEWAY : 'twilio',
            'api_key' => defined('SMS_API_KEY') ? SMS_API_KEY : '',
            'api_secret' => defined('SMS_API_SECRET') ? SMS_API_SECRET : '',
            'from_number' => defined('SMS_FROM_NUMBER') ? SMS_FROM_NUMBER : '',
            'retry_attempts' => 3,
            'retry_delay' => 60 // seconds
        ];
    }

    /**
     * Initialize SMS gateway
     */
    private function initializeGateway() {
        switch ($this->config['gateway']) {
            case 'twilio':
                if (class_exists('Twilio\Rest\Client')) {
                    $this->gateway = new \Twilio\Rest\Client(
                        $this->config['api_key'],
                        $this->config['api_secret']
                    );
                }
                break;
            
            // Add more gateway implementations here
            default:
                throw new Exception("Unsupported SMS gateway: {$this->config['gateway']}");
        }
    }

    /**
     * Send SMS notification
     */
    public function send($to, $message, $options = []) {
        if (empty($this->config['api_key'])) {
            throw new Exception('SMS gateway not configured');
        }

        $attempt = 1;
        $success = false;
        $lastError = null;

        while ($attempt <= $this->config['retry_attempts'] && !$success) {
            try {
                switch ($this->config['gateway']) {
                    case 'twilio':
                        $success = $this->sendViaTwilio($to, $message, $options);
                        break;
                    
                    // Add more gateway implementations here
                }

                if ($success) {
                    $this->logSuccess($to, $message);
                    return true;
                }

            } catch (Exception $e) {
                $lastError = $e;
                $this->logError($to, $message, $e->getMessage(), $attempt);
                
                if ($attempt < $this->config['retry_attempts']) {
                    sleep($this->config['retry_delay']);
                }
            }

            $attempt++;
        }

        if (!$success && $lastError) {
            throw $lastError;
        }

        return false;
    }

    /**
     * Send SMS via Twilio
     */
    private function sendViaTwilio($to, $message, $options) {
        if (!$this->gateway) {
            throw new Exception('Twilio client not initialized');
        }

        $params = [
            'from' => $this->config['from_number'],
            'body' => $message
        ];

        // Add optional parameters
        if (!empty($options['status_callback'])) {
            $params['statusCallback'] = $options['status_callback'];
        }

        $response = $this->gateway->messages->create($to, $params);
        return $response->sid ? true : false;
    }

    /**
     * Log successful SMS
     */
    private function logSuccess($to, $message) {
        $query = "INSERT INTO sms_logs (
                     phone_number,
                     message,
                     status,
                     created_at
                 ) VALUES (?, ?, 'success', NOW())";

        try {
            $conn = get_db_connection();
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $to, $message);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("SMS log error: " . $e->getMessage());
        }
    }

    /**
     * Log SMS error
     */
    private function logError($to, $message, $error, $attempt) {
        $query = "INSERT INTO sms_logs (
                     phone_number,
                     message,
                     status,
                     error_message,
                     attempt,
                     created_at
                 ) VALUES (?, ?, 'error', ?, ?, NOW())";

        try {
            $conn = get_db_connection();
            $stmt = $conn->prepare($query);
            $stmt->bind_param('sssi', $to, $message, $error, $attempt);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("SMS log error: " . $e->getMessage());
        }
    }

    /**
     * Validate phone number format
     */
    public static function validatePhoneNumber($phone) {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Check if it's a valid format (adjust regex based on your requirements)
        if (!preg_match('/^[1-9][0-9]{9,14}$/', $phone)) {
            return false;
        }

        return $phone;
    }
}
?>
