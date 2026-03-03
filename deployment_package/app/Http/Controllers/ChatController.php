<?php
namespace App\Http\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ChatController extends Controller {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getMessages($roomId) {
        $query = "SELECT * FROM chat_messages WHERE room_id = ? ORDER BY created_at ASC LIMIT 50";
        $messages = $this->db->query($query, [$roomId])->fetchAll();
        
        return json_encode([
            "success" => true,
            "messages" => $messages
        ]);
    }
    
    public function saveMessage($roomId, $userId, $message) {
        $query = "INSERT INTO chat_messages (room_id, user_id, message, created_at) VALUES (?, ?, ?, NOW())";
        $result = $this->db->query($query, [$roomId, $userId, $message]);
        
        if ($result) {
            return json_encode([
                "success" => true,
                "message_id" => $this->db->lastInsertId()
            ]);
        }
        
        return json_encode([
            "success" => false,
            "error" => "Failed to save message"
        ]);
    }
    
    public function getOnlineUsers($roomId) {
        $query = "SELECT DISTINCT user_id FROM chat_sessions WHERE room_id = ? AND last_active > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        $users = $this->db->query($query, [$roomId])->fetchAll();
        
        return json_encode([
            "success" => true,
            "users" => $users
        ]);
    }
    
    public function updateUserPresence($roomId, $userId) {
        $query = "INSERT INTO chat_sessions (room_id, user_id, last_active) 
                  VALUES (?, ?, NOW())
                  ON DUPLICATE KEY UPDATE last_active = NOW()";
        $result = $this->db->query($query, [$roomId, $userId]);
        
        return json_encode([
            "success" => $result
        ]);
    }
}
