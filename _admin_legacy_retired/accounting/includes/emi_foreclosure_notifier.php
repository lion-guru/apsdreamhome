<?php
require_once '../../../../includes/notification_manager.php';

class EMIForeclosureNotifier {
    private $notificationManager;
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->notificationManager = new NotificationManager($dbConnection);
    }

    /**
     * Prepare customer details for notification
     * 
     * @param int $emiPlanId EMI Plan Identifier
     * @return array Customer and loan details
     */
    private function prepareCustomerDetails(int $emiPlanId): array {
        $query = "SELECT 
            c.id AS customer_id, 
            c.name AS customer_name, 
            c.email AS customer_email,
            c.phone AS customer_phone,
            p.title AS property_title,
            ep.total_amount,
            ep.remaining_amount,
            ep.status AS loan_status
        FROM emi_plans ep
        JOIN customers c ON ep.customer_id = c.id
        JOIN properties p ON ep.property_id = p.id
        WHERE ep.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $emiPlanId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc() ?? [];
    }

    /**
     * Send notifications for EMI foreclosure events
     * 
     * @param int $emiPlanId EMI Plan Identifier
     * @param string $status Foreclosure status
     * @param array $foreclosureDetails Foreclosure details
     */
    public function notifyStakeholders(int $emiPlanId, string $status, array $foreclosureDetails): void {
        try {
            // Prepare customer and loan details
            $customerDetails = $this->prepareCustomerDetails($emiPlanId);

            // Prepare notification data
            $notificationData = [
                'customer_id' => $customerDetails['customer_id'] ?? 0,
                'customer_name' => $customerDetails['customer_name'] ?? 'Customer',
                'property_title' => $customerDetails['property_title'] ?? 'Property',
                'total_amount' => $customerDetails['total_amount'] ?? 0,
                'remaining_amount' => $customerDetails['remaining_amount'] ?? 0,
                'foreclosure_amount' => $foreclosureDetails['foreclosure_amount'] ?? 0,
                'error_message' => $foreclosureDetails['error_message'] ?? 'Unknown error',
                'status' => $status,
                'foreclosure_date' => date('Y-m-d H:i:s')
            ];

            // Customer Notification
            $this->sendCustomerNotification($customerDetails, $notificationData);

            // Admin Notification
            $this->sendAdminNotification($customerDetails, $notificationData);

            // Optional: SMS Notification
            $this->sendSMSNotification($customerDetails, $notificationData);
        } catch (Exception $e) {
            error_log('EMI Foreclosure Notification Error: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to customer
     * 
     * @param array $customerDetails Customer information
     * @param array $notificationData Foreclosure details
     */
    private function sendCustomerNotification(array $customerDetails, array $notificationData): void {
        // Determine template type based on status
        $templateType = $notificationData['status'] === 'success' 
            ? 'emi_foreclosure_success' 
            : 'emi_foreclosure_failed';

        // Send templated email
        $this->notificationManager->sendTemplatedEmail(
            $customerDetails['customer_email'] ?? '',
            $templateType,
            $notificationData
        );

        // Create templated in-app notification
        $this->notificationManager->createTemplatedNotification(
            $customerDetails['customer_id'] ?? 0,
            $templateType,
            $notificationData,
            'in_app'
        );
    }

    /**
     * Send notification to admin users
     * 
     * @param array $customerDetails Customer information
     * @param array $notificationData Foreclosure details
     */
    private function sendAdminNotification(array $customerDetails, array $notificationData): void {
        // Fetch admin users
        $adminQuery = "SELECT id, email FROM users WHERE role IN ('admin', 'manager')";
        $adminResult = $this->conn->query($adminQuery);

        while ($admin = $adminResult->fetch_assoc()) {
            // Create templated admin notification
            $this->notificationManager->createTemplatedNotification(
                $admin['id'],
                'emi_foreclosure_admin_alert',
                $notificationData,
                'in_app'
            );

            // Send templated email to admin
            $this->notificationManager->sendTemplatedEmail(
                $admin['email'],
                'emi_foreclosure_admin_alert',
                $notificationData
            );
        }
    }

    /**
     * Send SMS notification
     * 
     * @param array $customerDetails Customer information
     * @param array $notificationData Foreclosure details
     */
    private function sendSMSNotification(array $customerDetails, array $notificationData): void {
        // Implement SMS gateway integration
        // This is a placeholder and would require actual SMS gateway configuration
        if (!empty($customerDetails['customer_phone'])) {
            $smsMessage = $this->formatSMSMessage($customerDetails, $notificationData);
            
            // Example SMS sending (replace with actual SMS gateway)
            error_log('SMS Notification: ' . $smsMessage . ' to ' . $customerDetails['customer_phone']);
        }
    }

    /**
     * Get email template based on foreclosure status
     * 
     * @param string $status Foreclosure status
     * @return array Email template
     */
    private function getEmailTemplate(string $status): array {
        $templates = [
            'success' => [
                'subject' => 'EMI Foreclosure Completed - APS Dream Home',
                'body' => 'Dear {customer_name},

Your EMI plan for the property "{property_title}" has been successfully foreclosed.

Total Loan Amount: ₹{total_amount}
Foreclosure Amount: ₹{foreclosure_amount}

Thank you for your cooperation.'
            ],
            'failed' => [
                'subject' => 'EMI Foreclosure Attempt Failed - APS Dream Home',
                'body' => 'Dear {customer_name},

We regret to inform you that the foreclosure attempt for your property "{property_title}" was unsuccessful.

Please contact our support team for further assistance.

Remaining Loan Amount: ₹{remaining_amount}'
            ]
        ];

        return $templates[$status] ?? $templates['failed'];
    }

    /**
     * Format email body with customer and foreclosure details
     * 
     * @param string $template Email template
     * @param array $customerDetails Customer information
     * @param array $notificationData Foreclosure details
     * @return string Formatted email body
     */
    private function formatEmailBody(string $template, array $customerDetails, array $notificationData): string {
        $replacements = [
            '{customer_name}' => $customerDetails['customer_name'],
            '{property_title}' => $notificationData['property_title'],
            '{total_amount}' => number_format($notificationData['total_amount'], 2),
            '{remaining_amount}' => number_format($notificationData['remaining_amount'], 2),
            '{foreclosure_amount}' => number_format($notificationData['foreclosure_amount'], 2)
        ];

        return strtr($template, $replacements);
    }

    /**
     * Format in-app notification message
     * 
     * @param string $emailBody Email template body
     * @param array $customerDetails Customer information
     * @param array $notificationData Foreclosure details
     * @return string Formatted in-app message
     */
    private function formatInAppMessage(string $emailBody, array $customerDetails, array $notificationData): string {
        return substr($emailBody, 0, 250) . '...'; // Truncate for in-app notification
    }

    /**
     * Format admin notification message
     * 
     * @param array $customerDetails Customer information
     * @param array $notificationData Foreclosure details
     * @return string Formatted admin message
     */
    private function formatAdminMessage(array $customerDetails, array $notificationData): string {
        return "EMI Foreclosure {$notificationData['status']} 
Customer: {$customerDetails['customer_name']}
Property: {$notificationData['property_title']}
Total Amount: ₹" . number_format($notificationData['total_amount'], 2) . "
Foreclosure Amount: ₹" . number_format($notificationData['foreclosure_amount'], 2);
    }

    /**
     * Format SMS message
     * 
     * @param array $customerDetails Customer information
     * @param array $notificationData Foreclosure details
     * @return string Formatted SMS message
     */
    private function formatSMSMessage(array $customerDetails, array $notificationData): string {
        return "APS Dream Home: EMI Foreclosure {$notificationData['status']} for {$customerDetails['customer_name']}. " .
               "Property: {$notificationData['property_title']}. " .
               "Amount: ₹" . number_format($notificationData['foreclosure_amount'], 2);
    }
}
