<?php
require_once '../../../../includes/notification_manager.php';

class EMIForeclosureNotifier {
    private $notificationManager;
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
        $this->notificationManager = new NotificationManager($this->db->getConnection());
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
        
        try {
            return $this->db->fetchOne($query, [$emiPlanId]) ?: [];
        } catch (Exception $e) {
            error_log("EMI Foreclosure prepareCustomerDetails Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Send notifications for EMI foreclosure events
     * 
     * @param int $emiPlanId EMI Plan Identifier
     * @param string $status Foreclosure status (success/failed)
     * @param array $foreclosureDetails Foreclosure details
     */
    public function notifyStakeholders(int $emiPlanId, string $status, array $foreclosureDetails): void {
        try {
            // Prepare customer and loan details
            $customerDetails = $this->prepareCustomerDetails($emiPlanId);
            if (empty($customerDetails)) {
                error_log("EMI Foreclosure Notification Error: Customer details not found for plan ID $emiPlanId");
                return;
            }

            // Prepare common data for templates
            $templateData = [
                'customer_name' => $customerDetails['customer_name'] ?? 'Customer',
                'property_title' => $customerDetails['property_title'] ?? 'Property',
                'total_amount' => number_format($customerDetails['total_amount'] ?? 0, 2),
                'remaining_amount' => number_format($customerDetails['remaining_amount'] ?? 0, 2),
                'foreclosure_amount' => number_format($foreclosureDetails['foreclosure_amount'] ?? 0, 2),
                'status' => ucfirst($status),
                'foreclosure_date' => date('Y-m-d H:i:s')
            ];

            // 1. Customer Notification (In-App + Email)
            $customerTemplate = ($status === 'success') ? 'emi_foreclosure_success' : 'emi_foreclosure_failed';
            $this->notificationManager->send([
                'user_id' => $customerDetails['customer_id'],
                'template' => $customerTemplate,
                'data' => $templateData,
                'type' => 'emi_foreclosure',
                'channels' => ['db', 'email']
            ]);

            // 2. Admin Notification (In-App + Email)
            $adminQuery = "SELECT id, email FROM admin WHERE role IN ('admin', 'manager')";
            $admins = $this->db->fetchAll($adminQuery);
            foreach ($admins as $admin) {
                $this->notificationManager->send([
                    'user_id' => $admin['id'],
                    'template' => 'emi_foreclosure_admin_alert',
                    'data' => $templateData,
                    'type' => 'emi_foreclosure_admin',
                    'channels' => ['db', 'email']
                ]);
            }

        } catch (Exception $e) {
            error_log('EMI Foreclosure Notification Error: ' . $e->getMessage());
        }
    }
}

