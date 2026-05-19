<?php
namespace App\Http\Controllers\Api;

class NotificationController 
{
    public function create() 
    {
        header("Content-Type: application/json");
        
        $input = json_decode(file_get_contents("php://input"), true);
        
        echo json_encode([
            "success" => true,
            "message" => "Notification created successfully",
            "data" => $input
        ]);
    }
}
?>