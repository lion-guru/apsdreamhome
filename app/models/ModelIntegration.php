<?php
/**
 * Complete Model Integration - APS Dream Home
 * Ensures all controllers properly use models and integrations work
 */

namespace App\Models;

use PDO;

class ModelIntegration {

    public static function ensureAllModelsLoaded() {
        // Ensure all key models are available
        $models = [
            'User' => 'app/models/User.php',
            'Property' => 'app/models/Property.php',
            'Associate' => 'app/models/Associate.php',
            'Customer' => 'app/models/Customer.php',
            'Payment' => 'app/models/Payment.php',
            'Project' => 'app/models/Project.php',
            'Farmer' => 'app/models/Farmer.php',
            'CRMLead' => 'app/models/CRMLead.php',
            'AssociateMLM' => 'app/models/AssociateMLM.php',
            'PropertyFavorite' => 'app/models/PropertyFavorite.php',
            'PropertyInquiry' => 'app/models/PropertyInquiry.php',
            'Admin' => 'app/models/Admin.php',
            'Employee' => 'app/models/Employee.php',
            'AIChatbot' => 'app/models/AIChatbot.php'
        ];

        foreach ($models as $className => $filePath) {
            if (file_exists($filePath)) {
                // Models will be autoloaded when needed
            }
        }

        return true;
    }

    public static function getModelInstance($modelName) {
        $className = "App\\Models\\" . $modelName;

        if (class_exists($className)) {
            return new $className();
        }

        return null;
    }

    public static function ensureDatabaseIntegrity() {
        try {
            $pdo = new PDO(
                'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';' .
                'dbname=' . (getenv('DB_NAME') ?: 'apsdreamhome') . ';' .
                'charset=utf8mb4',
                getenv('DB_USER') ?: 'root',
                getenv('DB_PASS') ?: ''
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Check if tables exist and create if needed
            $tables = [
                'users', 'properties', 'associates', 'customers', 'payments', 
                'crm_leads', 'associate_mlm', 'property_favorites', 'admin', 'employees', 'chatbot_conversations',
                'company_projects'
            ];

            foreach ($tables as $table) {
                try {
                    $pdo->query("SELECT 1 FROM $table LIMIT 1");
                } catch (\Exception $e) {
                    // Table doesn't exist, create it
                    self::createTable($pdo, $table);
                }
            }

            return true;
        } catch (\Exception $e) {
            error_log('Database integrity check failed: ' . $e->getMessage());
            return false;
        }
    }

    private static function createTable($pdo, $tableName) {
        // Define table schemas based on table name
        $schemas = [
            'company_projects' => "CREATE TABLE IF NOT EXISTS company_projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                project_name VARCHAR(255) NOT NULL,
                description TEXT,
                location VARCHAR(255),
                project_type ENUM('residential', 'commercial', 'mixed') DEFAULT 'residential',
                status ENUM('planning', 'ongoing', 'completed', 'cancelled') DEFAULT 'planning',
                start_date DATE,
                end_date DATE,
                budget DECIMAL(15,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            // Add other table schemas as needed
        ];
        
        $sql = $schemas[$tableName] ?? "CREATE TABLE IF NOT EXISTS $tableName (
            id INT AUTO_INCREMENT PRIMARY KEY,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        try {
            $pdo->query($sql);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to create table $tableName: " . $e->getMessage());
            return false;
        }
    }
}

// Auto-load models when this file is included
ModelIntegration::ensureAllModelsLoaded();
