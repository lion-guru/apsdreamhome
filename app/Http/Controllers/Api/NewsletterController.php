<?php
namespace App\Http\Controllers\Api;

use PDO;

class NewsletterController extends BaseApiController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = new PDO(
            "mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function subscribe() 
    {
        header("Content-Type: application/json");

        $email = $this->inputWithJson('email') ?? ($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "success" => false,
                "message" => "Invalid email address"
            ]);
            return;
        }

        try {
            $existing = \App\Models\NewsletterSubscriber::findByEmail($email);
            
            if ($existing) {
                echo json_encode([
                    "success" => true,
                    "message" => "You are already subscribed!"
                ]);
                return;
            }

            \App\Models\NewsletterSubscriber::subscribe($email);
            
            echo json_encode([
                "success" => true,
                "message" => "Thank you for subscribing!"
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Subscription failed. Please try again."
            ]);
        }
    }
}
