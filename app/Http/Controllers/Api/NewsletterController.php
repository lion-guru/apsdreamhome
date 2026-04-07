<?php
namespace App\Http\Controllers\Api;

use PDO;

class NewsletterController 
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

    public function subscribe() 
    {
        header("Content-Type: application/json");
        
        $email = $_POST['email'] ?? '';
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "success" => false,
                "message" => "Invalid email address"
            ]);
            return;
        }

        try {
            // Check if already subscribed
            $stmt = $this->db->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                echo json_encode([
                    "success" => true,
                    "message" => "You are already subscribed!"
                ]);
                return;
            }

            // Insert new subscriber
            $stmt = $this->db->prepare("
                INSERT INTO newsletter_subscribers (email, is_active, created_at) 
                VALUES (?, 1, NOW())
            ");
            $stmt->execute([$email]);
            
            echo json_encode([
                "success" => true,
                "message" => "Thank you for subscribing!"
            ]);
        } catch (\Exception $e) {
            // If table doesn't exist, create it
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $this->createTable();
                // Retry insert
                $stmt = $this->db->prepare("
                    INSERT INTO newsletter_subscribers (email, is_active, created_at) 
                    VALUES (?, 1, NOW())
                ");
                $stmt->execute([$email]);
                
                echo json_encode([
                    "success" => true,
                    "message" => "Thank you for subscribing!"
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Subscription failed. Please try again."
                ]);
            }
        }
    }

    private function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            name VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->db->exec($sql);
    }
}
?>
