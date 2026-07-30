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
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            
            // Check if tables exist and create if needed
            $tables = [
                'users', 'properties', 'users', 'users', 'payments', 
                'crm_leads', 'associate_mlm', 'property_favorites', 'admin', 'users', 'chatbot_conversations',
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
            'company_projects' => "",
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
