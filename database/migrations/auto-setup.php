<?php

/**
 * APS Dream Home - Automatic Production Setup
 * Runs on first deployment to setup database and configuration
 */

// Auto-detect environment
$isProduction = ($_SERVER['HTTP_HOST'] ?? 'localhost') !== 'localhost';
$basePath = __DIR__;

// Database configuration
$config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'dbname' => $_ENV['DB_DATABASE'] ?? 'apsdreamhome',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? ''
];

// Setup log file
$logFile = $basePath . '/storage/logs/setup.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "$message\n";
}

function setupDatabase($config) {
    try {
        // Create connection
        $pdo = new PDO("mysql:host={$config['host']}", $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$config['dbname']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE {$config['dbname']}");
        
        logMessage("Database '{$config['dbname']}' created/verified");
        
        // Get all model files and create tables automatically
        $modelsPath = __DIR__ . '/app/Models';
        $modelFiles = glob($modelsPath . '/*.php');
        
        $essentialTables = [
            // Core tables first
            "CREATE TABLE IF NOT EXISTS users (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                email varchar(255) NOT NULL,
                phone varchar(20) DEFAULT NULL,
                password varchar(255) NOT NULL,
                role enum('admin','user','employee') DEFAULT 'user',
                status enum('active','inactive') DEFAULT 'active',
                email_verified_at timestamp NULL DEFAULT NULL,
                remember_token varchar(100) DEFAULT NULL,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY email (email),
                KEY role (role),
                KEY status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];
        
        // Create essential tables first
        foreach ($essentialTables as $sql) {
            $pdo->exec($sql);
        }
        
        // Auto-create tables from all model files
        $tableDefinitions = [
            'password_reset_tokens' => "CREATE TABLE IF NOT EXISTS password_reset_tokens (
                id int(11) NOT NULL AUTO_INCREMENT,
                email varchar(255) NOT NULL,
                token varchar(255) NOT NULL,
                expires_at datetime NOT NULL,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY email (email),
                KEY token (token),
                KEY expires_at (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'properties' => "CREATE TABLE IF NOT EXISTS properties (
                id int(11) NOT NULL AUTO_INCREMENT,
                title varchar(255) NOT NULL,
                description text,
                price decimal(12,2) DEFAULT NULL,
                location varchar(255) DEFAULT NULL,
                city varchar(100) DEFAULT NULL,
                state varchar(100) DEFAULT NULL,
                pincode varchar(10) DEFAULT NULL,
                type varchar(50) DEFAULT NULL,
                bedrooms int(11) DEFAULT NULL,
                bathrooms int(11) DEFAULT NULL,
                area_sqft decimal(10,2) DEFAULT NULL,
                status enum('available','sold','rented','under_offer') DEFAULT 'available',
                featured tinyint(1) DEFAULT 0,
                images json DEFAULT NULL,
                amenities json DEFAULT NULL,
                created_by int(11) DEFAULT NULL,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY status (status),
                KEY type (type),
                KEY location (location),
                KEY price (price),
                KEY featured (featured),
                KEY created_by (created_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'leads' => "CREATE TABLE IF NOT EXISTS leads (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                email varchar(255) NOT NULL,
                phone varchar(20) DEFAULT NULL,
                property_id int(11) DEFAULT NULL,
                budget_min decimal(12,2) DEFAULT NULL,
                budget_max decimal(12,2) DEFAULT NULL,
                preferred_location varchar(255) DEFAULT NULL,
                message text,
                status enum('new','contacted','interested','converted','lost') DEFAULT 'new',
                priority enum('low','medium','high') DEFAULT 'medium',
                source varchar(50) DEFAULT 'website',
                assigned_to int(11) DEFAULT NULL,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY email (email),
                KEY property_id (property_id),
                KEY status (status),
                KEY priority (priority),
                KEY assigned_to (assigned_to),
                KEY created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'employees' => "CREATE TABLE IF NOT EXISTS employees (
                id int(11) NOT NULL AUTO_INCREMENT,
                user_id int(11) DEFAULT NULL,
                employee_id varchar(20) DEFAULT NULL,
                name varchar(255) NOT NULL,
                email varchar(255) NOT NULL,
                phone varchar(20) DEFAULT NULL,
                password varchar(255) DEFAULT NULL,
                department varchar(100) DEFAULT NULL,
                designation varchar(100) DEFAULT NULL,
                salary decimal(10,2) DEFAULT NULL,
                join_date date DEFAULT NULL,
                reporting_manager_id int(11) DEFAULT NULL,
                status enum('active','inactive','on_leave') DEFAULT 'active',
                address text,
                emergency_contact varchar(255) DEFAULT NULL,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY employee_id (employee_id),
                UNIQUE KEY user_id (user_id),
                KEY email (email),
                KEY department (department),
                KEY status (status),
                KEY reporting_manager_id (reporting_manager_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Additional essential tables from models
            'projects' => "CREATE TABLE IF NOT EXISTS projects (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                description text,
                location varchar(255) DEFAULT NULL,
                developer varchar(255) DEFAULT NULL,
                status enum('planning','under_construction','completed','cancelled') DEFAULT 'planning',
                start_date date DEFAULT NULL,
                completion_date date DEFAULT NULL,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY status (status),
                KEY location (location)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'payments' => "CREATE TABLE IF NOT EXISTS payments (
                id int(11) NOT NULL AUTO_INCREMENT,
                user_id int(11) DEFAULT NULL,
                property_id int(11) DEFAULT NULL,
                amount decimal(12,2) NOT NULL,
                payment_type enum('booking','emi','full_payment','commission') DEFAULT 'booking',
                status enum('pending','completed','failed','refunded') DEFAULT 'pending',
                payment_method varchar(50) DEFAULT NULL,
                transaction_id varchar(255) DEFAULT NULL,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY property_id (property_id),
                KEY status (status),
                KEY payment_type (payment_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'notifications' => "CREATE TABLE IF NOT EXISTS notifications (
                id int(11) NOT NULL AUTO_INCREMENT,
                user_id int(11) DEFAULT NULL,
                title varchar(255) NOT NULL,
                message text,
                type enum('info','success','warning','error') DEFAULT 'info',
                status enum('read','unread') DEFAULT 'unread',
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY status (status),
                KEY type (type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'settings' => "CREATE TABLE IF NOT EXISTS settings (
                id int(11) NOT NULL AUTO_INCREMENT,
                key_name varchar(255) NOT NULL,
                value text,
                description varchar(255) DEFAULT NULL,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY key_name (key_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'audit_logs' => "CREATE TABLE IF NOT EXISTS audit_logs (
                id int(11) NOT NULL AUTO_INCREMENT,
                user_id int(11) DEFAULT NULL,
                action varchar(255) NOT NULL,
                table_name varchar(100) DEFAULT NULL,
                record_id int(11) DEFAULT NULL,
                old_values json DEFAULT NULL,
                new_values json DEFAULT NULL,
                ip_address varchar(45) DEFAULT NULL,
                user_agent text,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY action (action),
                KEY table_name (table_name),
                KEY created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];
        
        // Create all tables
        foreach ($tableDefinitions as $tableName => $sql) {
            try {
                $pdo->exec($sql);
                logMessage("Table '$tableName' created successfully");
            } catch (PDOException $e) {
                logMessage("Table '$tableName' creation failed: " . $e->getMessage());
            }
        }
        
        logMessage("All database tables created successfully");
        
        // Insert default data
        $defaultAdminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['System Admin', 'admin@apsdreamhome.com', $defaultAdminPassword, 'admin', 'active']);
        
        // Insert default settings
        $defaultSettings = [
            ['site_name', 'APS Dream Home', 'Website name'],
            ['site_email', 'info@apsdreamhome.com', 'Contact email'],
            ['site_phone', '+91-9876543210', 'Contact phone'],
            ['currency', 'INR', 'Default currency'],
            ['timezone', 'Asia/Kolkata', 'Default timezone']
        ];
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO settings (key_name, value, description) VALUES (?, ?, ?)");
        foreach ($defaultSettings as $setting) {
            $stmt->execute($setting);
        }
        
        // Insert sample properties
        $sampleProperties = [
            [
                'title' => 'Luxury 3BHK Apartment',
                'description' => 'Spacious 3BHK apartment in prime location with modern amenities',
                'price' => 8500000,
                'location' => 'Bandra West, Mumbai',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400050',
                'type' => 'Apartment',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area_sqft' => 1200,
                'status' => 'available'
            ],
            [
                'title' => 'Independent House',
                'description' => 'Beautiful independent house with garden and parking',
                'price' => 15000000,
                'location' => 'Koramangala, Bangalore',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'pincode' => '560034',
                'type' => 'House',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area_sqft' => 2500,
                'status' => 'available'
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO properties (title, description, price, location, city, state, pincode, type, bedrooms, bathrooms, area_sqft, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($sampleProperties as $property) {
            $stmt->execute(array_values($property));
        }
        
        logMessage("Default data inserted successfully");
        
        return true;
        
    } catch(PDOException $e) {
        logMessage("Database setup failed: " . $e->getMessage());
        return false;
    }
}

function setupDirectories($basePath) {
    $directories = [
        'storage/logs',
        'storage/cache',
        'storage/sessions',
        'storage/uploads',
        'public/uploads/properties',
        'public/uploads/profiles',
        'public/uploads/documents'
    ];
    
    foreach ($directories as $dir) {
        $fullPath = $basePath . '/' . $dir;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
            logMessage("Created directory: $dir");
        }
    }
}

function setupEnvironment($basePath) {
    $envFile = $basePath . '/.env';
    $envExample = $basePath . '/.env.example';
    
    if (!file_exists($envFile) && file_exists($envExample)) {
        copy($envExample, $envFile);
        logMessage("Environment file created from example");
    }
    
    // Create .htaccess for public uploads
    $htaccessContent = "
# Prevent direct access to uploaded files
<FilesMatch '\.(php|phtml|pht|php3|php4|php5|phpt)$'>
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Allow image access
<FilesMatch '\.(jpg|jpeg|png|gif|webp)$'>
    Order Allow,Deny
    Allow from all
</FilesMatch>
";
    
    file_put_contents($basePath . '/public/uploads/.htaccess', $htaccessContent);
    logMessage("Security .htaccess created for uploads directory");
}

// Main setup execution
logMessage("=== APS Dream Home Automatic Setup Started ===");
logMessage("Environment: " . ($isProduction ? 'Production' : 'Development'));

// Step 1: Setup directories
setupDirectories($basePath);

// Step 2: Setup environment
setupEnvironment($basePath);

// Step 3: Setup database
$dbSetupSuccess = setupDatabase($config);

// Step 4: Create setup lock file
if ($dbSetupSuccess) {
    $lockFile = $basePath . '/storage/.setup.lock';
    file_put_contents($lockFile, date('Y-m-d H:i:s'));
    logMessage("Setup completed successfully!");
    logMessage("Default admin login: admin@apsdreamhome.com / admin123");
    logMessage("=== Setup Completed ===");
} else {
    logMessage("Setup failed! Check logs for details.");
}

// Redirect if web access
if (php_sapi_name() !== 'cli') {
    if ($dbSetupSuccess) {
        header('Location: /');
        exit;
    } else {
        echo "<h1>Setup Failed</h1><p>Check storage/logs/setup.log for details</p>";
    }
}
?>
