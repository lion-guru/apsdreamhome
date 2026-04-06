<?php
namespace App\Http\Controllers\Api;

class NewsletterController 
{
    public function subscribe() 
    {
        header("Content-Type: application/json");
        
        $input = json_decode(file_get_contents("php://input"), true);
        
        echo json_encode([
            "success" => true,
            "message" => "Subscribed to newsletter successfully",
            "email" => $input["email"] ?? ""
        ]);
    }
}
?>