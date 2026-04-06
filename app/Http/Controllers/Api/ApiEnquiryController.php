<?php
namespace App\Http\Controllers\Api;

class ApiEnquiryController 
{
    public function store() 
    {
        header("Content-Type: application/json");
        
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "Invalid JSON"]);
            return;
        }
        
        // Process enquiry (simplified)
        echo json_encode([
            "success" => true,
            "message" => "Enquiry received successfully",
            "data" => $input
        ]);
    }
    
    public function propertyInquiry() 
    {
        header("Content-Type: application/json");
        
        $input = json_decode(file_get_contents("php://input"), true);
        
        echo json_encode([
            "success" => true,
            "message" => "Property inquiry submitted",
            "data" => $input
        ]);
    }
}
?>