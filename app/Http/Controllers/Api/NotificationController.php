<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Security;
use Exception;
use PDO;

class NotificationController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->db) {
            error_log('NotificationController: Failed to initialize database connection');
        }
    }

    /**
     * Creates a new notification.
     */
    public function create()
    {
        $this->setCorsHeaders();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            return $this->errorResponse('Invalid JSON input.', 400);
        }

        // 1. Validate input
        $requiredFields = ['title', 'message', 'target_audience'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->errorResponse("Missing required field: {$field}", 400);
            }
        }

        // Sanitize all inputs
        $title = Security::sanitize($data['title']);
        $message = Security::sanitize($data['message']);
        $targetAudience = Security::sanitize($data['target_audience']);
        $userId = isset($data['user_id']) ? (int)Security::sanitize($data['user_id']) : null;
        $type = isset($data['type']) ? Security::sanitize($data['type']) : 'info';
        $campaignId = isset($data['campaign_id']) ? (int)Security::sanitize($data['campaign_id']) : null;

        // 2. Insert into database
        try {
            $sql = "INSERT INTO notifications (user_id, title, message, type, target_audience, campaign_id, created_at) 
                    VALUES (:user_id, :title, :message, :type, :target_audience, :campaign_id, NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindValue(':user_id', $userId, $userId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':message', $message);
            $stmt->bindValue(':type', $type);
            $stmt->bindValue(':target_audience', $targetAudience);
            $stmt->bindValue(':campaign_id', $campaignId, $campaignId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            
            $stmt->execute();
            
            $notificationId = $this->db->lastInsertId();

            return $this->successResponse([
                'notification_id' => $notificationId,
                'status' => 'created'
            ], 'Notification created successfully.');

        } catch (Exception $e) {
            return $this->errorResponse('Failed to create notification: ' . $e->getMessage(), 500);
        }
    }

    private function setCorsHeaders()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }

    protected function successResponse($data, $message = 'Success')
    {
        http_response_code(201); // 201 Created for successful resource creation
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
        exit();
    }

    protected function errorResponse($message, $code = 400)
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit();
    }
}
