<?php
namespace App\Http\Controllers;

class SupportController extends Controller
{
    public function createSupportTicket()
    {
        return $this->json([
            "success" => true,
            "data" => [
                "ticket_id" => "TKT_" . uniqid(),
                "subject" => $_POST["subject"] ?? "General Inquiry",
                "message" => $_POST["message"] ?? "Need assistance",
                "priority" => $_POST["priority"] ?? "medium",
                "user_id" => 1,
                "status" => "open",
                "created_at" => date("Y-m-d H:i:s")
            ]
        ]);
    }
}
?>