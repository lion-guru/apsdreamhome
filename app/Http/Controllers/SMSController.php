<?php

/**
 * SMS Controller
 * Handles SMS management and OTP verification
 */

namespace App\Http\Controllers;

use App\Core\Database\Database;
use App\Services\Communication\SMSService;

class SMSController extends BaseController
{
    private $smsService;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->smsService = new SMSService();
    }

    /**
     * Send OTP via AJAX
     */
    public function sendOTP()
    {
        header('Content-Type: application/json');

        $mobile = $_POST['mobile'] ?? '';
        $purpose = $_POST['purpose'] ?? 'verification';

        if (empty($mobile) || !preg_match('/^[0-9]{10}$/', $mobile)) {
            echo json_encode(['success' => false, 'error' => 'Invalid mobile number']);
            exit;
        }

        $result = $this->smsService->sendOTP($mobile);

        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'OTP sent successfully',
                'expires_in' => 600 // 10 minutes
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
        exit;
    }

    /**
     * Verify OTP via AJAX
     */
    public function verifyOTP()
    {
        header('Content-Type: application/json');

        $mobile = $_POST['mobile'] ?? '';
        $otp = $_POST['otp'] ?? '';

        if (empty($mobile) || empty($otp)) {
            echo json_encode(['success' => false, 'error' => 'Mobile and OTP required']);
            exit;
        }

        $result = $this->smsService->verifyOTP($mobile, $otp);

        echo json_encode($result);
        exit;
    }

    /**
     * Admin SMS Dashboard
     */
    public function adminDashboard()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        $stats = $this->smsService->getStats(30);
        $recentLogs = $this->db->fetchAll(
            "SELECT * FROM sms_logs ORDER BY created_at DESC LIMIT 50"
        );

        $base = BASE_URL;
        include __DIR__ . '/../views/admin/sms/dashboard.php';
    }

    /**
     * Admin send custom SMS
     */
    public function adminSend()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['admin_id'])) {
            $_SESSION['error'] = "Unauthorized";
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        $mobile = $_POST['mobile'] ?? '';
        $message = $_POST['message'] ?? '';

        if (empty($mobile) || empty($message)) {
            $_SESSION['error'] = "Mobile and message required";
            header('Location: ' . BASE_URL . '/admin/sms');
            exit;
        }

        $result = $this->smsService->sendSMS($mobile, $message, 'ADMIN');

        if ($result['success']) {
            $_SESSION['success'] = "SMS sent successfully!";
        } else {
            $_SESSION['error'] = "Failed to send SMS: " . $result['error'];
        }

        header('Location: ' . BASE_URL . '/admin/sms');
        exit;
    }

    /**
     * Get SMS logs API
     */
    public function getLogs()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['admin_id'])) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $type = $_GET['type'] ?? 'all';
        $limit = min($_GET['limit'] ?? 50, 100);

        $sql = "SELECT * FROM sms_logs";
        $params = [];

        if ($type !== 'all') {
            $sql .= " WHERE type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        $logs = $this->db->fetchAll($sql, $params);

        echo json_encode($logs);
        exit;
    }
}
