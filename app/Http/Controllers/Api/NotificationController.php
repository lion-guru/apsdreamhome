<?php
namespace App\Http\Controllers\Api;

use PDO;

class NotificationController 
{
    private $db;

    public function __construct()
    {
        $this->db = new PDO(
            "mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function create() 
    {
        header("Content-Type: application/json");
        
        $input = json_decode(file_get_contents("php://input"), true);
        
        $userId = $input['user_id'] ?? null;
        $title = $input['title'] ?? '';
        $message = $input['message'] ?? '';
        $type = $input['type'] ?? 'info';

        if (!$userId || empty($title)) {
            echo json_encode([
                "success" => false,
                "message" => "Missing required fields: user_id, title"
            ]);
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO notification_feed (notification_id, user_id, type, title, message, is_read) VALUES (?, ?, ?, ?, ?, 0)"
            );
            $stmt->execute([uniqid('notif_'), $userId, $type, $title, $message]);
            
            echo json_encode([
                "success" => true,
                "message" => "Notification created successfully",
                "data" => [
                    "user_id" => $userId,
                    "title" => $title,
                    "message" => $message,
                    "type" => $type
                ]
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Database error: " . $e->getMessage()
            ]);
        }
    }
}
