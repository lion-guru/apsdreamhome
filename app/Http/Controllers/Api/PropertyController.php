<?php
namespace App\Http\Controllers\Api;

use PDO;
use Exception;

class PropertyController 
{
    public function index() 
    {
        header("Content-Type: application/json");
        
        try {
            $dbHost = getenv('DB_HOST') ?: '127.0.0.1';
            $dbPort = getenv('DB_PORT') ?: '3307';
            $dbName = getenv('DB_DATABASE') ?: 'apsdreamhome';
            $dbUser = getenv('DB_USERNAME') ?: 'root';
            $dbPass = getenv('DB_PASSWORD') ?: '';
            
            $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
            $db = new PDO($dsn, $dbUser, $dbPass);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $db->query("SELECT p.*, c.name as colony_name, d.name as district_name, s.name as state_name 
                                FROM plots p 
                                LEFT JOIN colonies c ON p.colony_id = c.id 
                                LEFT JOIN districts d ON c.district_id = d.id 
                                LEFT JOIN states s ON d.state_id = s.id 
                                LIMIT 50");
            
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                "success" => true,
                "data" => $properties,
                "total" => count($properties)
            ], JSON_PRETTY_PRINT);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ], JSON_PRETTY_PRINT);
        }
    }
}
?>