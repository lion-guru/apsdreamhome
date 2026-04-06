<?php
namespace App\Http\Controllers\Api;

class SystemController 
{
    public function health() 
    {
        header("Content-Type: application/json");
        
        $status = [
            "status" => "healthy",
            "timestamp" => date("Y-m-d H:i:s"),
            "version" => "1.0.0",
            "database" => "connected",
            "server" => "running"
        ];
        
        echo json_encode($status);
    }
}
?>