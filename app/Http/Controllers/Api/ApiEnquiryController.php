<?php
namespace App\Http\Controllers\Api;

class ApiEnquiryController extends BaseApiController
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

        // S765: this endpoint used to echo success without persisting anything,
        // so every mobile Contact Us submission vanished silently. Persist it
        // through the same `inquiries` table the website contact form uses.
        $name    = trim((string)($input['name'] ?? ''));
        $email   = trim((string)($input['email'] ?? ''));
        $phone   = trim((string)($input['phone'] ?? ''));
        $subject = trim((string)($input['subject'] ?? '')) ?: 'Mobile Contact Form';
        $message = trim((string)($input['message'] ?? ''));

        $errors = [];
        if ($name === '') $errors[] = 'Name is required';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if ($phone === '') $errors[] = 'Phone is required';
        if ($message === '') $errors[] = 'Message is required';
        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(["success" => false, "error" => implode(' ', $errors)]);
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            // Public mobile enquiry is tenant-1 by design (same as the website
            // contact flow on the default tenant).
            $stmt = $db->prepare(
                "INSERT INTO inquiries (name, email, phone, subject, message, status, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, 'new', 1, NOW())"
            );
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            $id = (int)$db->lastInsertId();
            echo json_encode([
                "success" => true,
                "message" => "Enquiry received successfully",
                "data" => ["id" => $id]
            ]);
        } catch (\Throwable $e) {
            error_log('Mobile enquiry save failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Could not save your enquiry. Please try again."]);
        }
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
