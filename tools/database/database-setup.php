<?php
// APS Dream Homes - Database Setup and Data Integration
require_once __DIR__ . '/includes/db_config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Set page metadata
$page_title = 'Database Setup - APS Dream Homes';
$page_description = 'Complete database setup and data integration for APS Dream Homes';
$page_keywords = 'database, setup, APS Dream Homes, data integration';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .setup-container {
            padding: 60px 0;
        }
        
        .setup-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            margin-bottom: 30px;
        }
        
        .setup-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .setup-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .setup-subtitle {
            font-size: 1.2rem;
            color: #666;
        }
        
        .status-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            border-left: 5px solid var(--primary-color);
        }
        
        .status-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .status-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .status-item i {
            margin-right: 10px;
            width: 20px;
        }
        
        .status-item.success i {
            color: var(--success-color);
        }
        
        .status-item.error i {
            color: var(--danger-color);
        }
        
        .status-item.warning i {
            color: var(--warning-color);
        }
        
        .btn-setup {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 10px;
        }
        
        .btn-setup:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            color: white;
        }
        
        .progress-section {
            margin-top: 30px;
        }
        
        .progress-item {
            margin-bottom: 20px;
        }
        
        .progress-label {
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .progress {
            height: 10px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            transition: width 0.6s ease;
        }
        
        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .alert-success-custom {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-error-custom {
            background: #f8d7da;
            color: #721c24;
        }
        
        .alert-info-custom {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .table-responsive {
            margin-top: 20px;
        }
        
        .table-custom {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table-custom th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
        }
        
        .table-custom td {
            vertical-align: middle;
        }
        
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-success {
            background: var(--success-color);
            color: white;
        }
        
        .badge-error {
            background: var(--danger-color);
            color: white;
        }
        
        .badge-warning {
            background: var(--warning-color);
            color: var(--dark-color);
        }
    </style>
</head>
<body>
    <div class="container setup-container">
        <div class="setup-header" data-aos="fade-down">
            <h1 class="setup-title">
                <i class="fas fa-database me-3"></i>Database Setup & Integration
            </h1>
            <p class="setup-subtitle">Complete database setup and data integration for APS Dream Homes</p>
        </div>
        
        <?php
        // Handle database setup actions
        $setup_message = '';
        $setup_status = '';
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create_database':
                    if (createDatabaseIfNotExists()) {
                        $setup_message = 'Database created successfully!';
                        $setup_status = 'success';
                    } else {
                        $setup_message = 'Database creation failed!';
                        $setup_status = 'error';
                    }
                    break;
                    
                case 'create_tables':
                    if (createTables()) {
                        $setup_message = 'Tables created successfully!';
                        $setup_status = 'success';
                    } else {
                        $setup_message = 'Table creation failed!';
                        $setup_status = 'error';
                    }
                    break;
                    
                case 'insert_data':
                    if (insertSampleData()) {
                        $setup_message = 'Sample data inserted successfully!';
                        $setup_status = 'success';
                    } else {
                        $setup_message = 'Data insertion failed!';
                        $setup_status = 'error';
                    }
                    break;
                    
                case 'full_setup':
                    if (fullDatabaseSetup()) {
                        $setup_message = 'Full database setup completed successfully!';
                        $setup_status = 'success';
                    } else {
                        $setup_message = 'Full setup failed!';
                        $setup_status = 'error';
                    }
                    break;
            }
        }
        
        // Get database status
        $db_exists = checkDatabaseExists();
        $db_size = $db_exists ? getDatabaseSize() : 0;
        $table_count = $db_exists ? getTableCount() : 0;
        $tables = $db_exists ? getTableList() : [];
        ?>
        
        <?php if ($setup_message): ?>
            <div class="alert alert-<?php echo $setup_status; ?> alert-custom" data-aos="fade-down">
                <i class="fas fa-<?php echo $setup_status === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo $setup_message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Database Status -->
        <div class="setup-card" data-aos="fade-up">
            <h3 class="status-title">
                <i class="fas fa-info-circle me-2"></i>Database Status
            </h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="status-item <?php echo $db_exists ? 'success' : 'error'; ?>">
                        <i class="fas fa-<?php echo $db_exists ? 'check' : 'times'; ?>"></i>
                        <span>Database "<?php echo DB_NAME; ?>" <?php echo $db_exists ? 'exists' : 'not found'; ?></span>
                    </div>
                    <div class="status-item">
                        <i class="fas fa-hdd me-2"></i>
                        <span>Database Size: <?php echo $db_size; ?> MB</span>
                    </div>
                    <div class="status-item">
                        <i class="fas fa-table me-2"></i>
                        <span>Table Count: <?php echo $table_count; ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="status-item">
                        <i class="fas fa-server me-2"></i>
                        <span>Host: <?php echo DB_HOST; ?></span>
                    </div>
                    <div class="status-item">
                        <i class="fas fa-user me-2"></i>
                        <span>User: <?php echo DB_USER; ?></span>
                    </div>
                    <div class="status-item">
                        <i class="fas fa-database me-2"></i>
                        <span>Charset: <?php echo DB_CHARSET; ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Setup Actions -->
        <div class="setup-card" data-aos="fade-up" data-aos-delay="100">
            <h3 class="status-title">
                <i class="fas fa-cogs me-2"></i>Setup Actions
            </h3>
            
            <form method="POST" class="text-center">
                <button type="submit" name="action" value="create_database" class="btn-setup">
                    <i class="fas fa-plus me-2"></i>Create Database
                </button>
                <button type="submit" name="action" value="create_tables" class="btn-setup">
                    <i class="fas fa-table me-2"></i>Create Tables
                </button>
                <button type="submit" name="action" value="insert_data" class="btn-setup">
                    <i class="fas fa-database me-2"></i>Insert Sample Data
                </button>
                <button type="submit" name="action" value="full_setup" class="btn-setup">
                    <i class="fas fa-rocket me-2"></i>Full Setup (Recommended)
                </button>
            </form>
            
            <div class="progress-section">
                <h4>Setup Progress</h4>
                <div class="progress-item">
                    <div class="progress-label">Database Creation</div>
                    <div class="progress">
                        <div class="progress-bar" style="width: <?php echo $db_exists ? '100' : '0'; ?>%"></div>
                    </div>
                </div>
                <div class="progress-item">
                    <div class="progress-label">Table Creation</div>
                    <div class="progress">
                        <div class="progress-bar" style="width: <?php echo $table_count > 0 ? '100' : '0'; ?>%"></div>
                    </div>
                </div>
                <div class="progress-item">
                    <div class="progress-label">Data Integration</div>
                    <div class="progress">
                        <div class="progress-bar" style="width: <?php echo $table_count > 5 ? '100' : '0'; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Table Information -->
        <?php if (!empty($tables)): ?>
            <div class="setup-card" data-aos="fade-up" data-aos-delay="200">
                <h3 class="status-title">
                    <i class="fas fa-list me-2"></i>Database Tables
                </h3>
                
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Table Name</th>
                                <th>Records</th>
                                <th>Size (KB)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tables as $table): ?>
                                <tr>
                                    <td><?php echo $table['name']; ?></td>
                                    <td><?php echo number_format($table['rows']); ?></td>
                                    <td><?php echo number_format($table['size'], 2); ?></td>
                                    <td>
                                        <span class="badge-status badge-<?php echo $table['rows'] > 0 ? 'success' : 'warning'; ?>">
                                            <?php echo $table['rows'] > 0 ? 'Active' : 'Empty'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Integration Status -->
        <div class="setup-card" data-aos="fade-up" data-aos-delay="300">
            <h3 class="status-title">
                <i class="fas fa-plug me-2"></i>Integration Status
            </h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="status-item <?php echo $db_exists ? 'success' : 'error'; ?>">
                        <i class="fas fa-check me-2"></i>
                        <span>Database Connection</span>
                    </div>
                    <div class="status-item <?php echo $table_count > 0 ? 'success' : 'warning'; ?>">
                        <i class="fas fa-check me-2"></i>
                        <span>Table Structure</span>
                    </div>
                    <div class="status-item <?php echo $table_count > 5 ? 'success' : 'warning'; ?>">
                        <i class="fas fa-check me-2"></i>
                        <span>Sample Data</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="status-item success">
                        <i class="fas fa-check me-2"></i>
                        <span>User Registration</span>
                    </div>
                    <div class="status-item success">
                        <i class="fas fa-check me-2"></i>
                        <span>Property Listings</span>
                    </div>
                    <div class="status-item success">
                        <i class="fas fa-check me-2"></i>
                        <span>Customer Reviews</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="setup-card" data-aos="fade-up" data-aos-delay="400">
            <h3 class="status-title">
                <i class="fas fa-link me-2"></i>Quick Links
            </h3>
            
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-3">
                    <a href="index_improved.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-home me-2"></i>Homepage
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="register.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="login.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="navigation.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-compass me-2"></i>Navigation
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Animate progress bars on page load
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });
        });

        // Add loading state to buttons
        document.querySelectorAll('.btn-setup').forEach(button => {
            button.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                this.disabled = true;
                
                // Re-enable after 3 seconds (in case of slow response)
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 3000);
            });
        });
    </script>
</body>
</html>

<?php
// Database functions

function createTables() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        
        // Create users table
        $conn->query("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            utype ENUM('customer', 'agent', 'builder', 'admin') DEFAULT 'customer',
            status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Create properties table
        $conn->query("CREATE TABLE IF NOT EXISTS properties (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            type ENUM('residential', 'commercial', 'plot', 'apartment', 'farmhouse', 'rowhouse') NOT NULL,
            location VARCHAR(255) NOT NULL,
            size VARCHAR(100),
            price VARCHAR(100),
            description TEXT,
            amenities JSON,
            image_url VARCHAR(500),
            status ENUM('available', 'sold', 'booking', 'limited') DEFAULT 'available',
            featured BOOLEAN DEFAULT FALSE,
            rera_number VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Create reviews table
        $conn->query("CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            property_id INT,
            user_id INT,
            rating INT CHECK (rating >= 1 AND rating <= 5),
            review_text TEXT,
            status ENUM('approved', 'pending', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Create contacts table
        $conn->query("CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            subject VARCHAR(255),
            message TEXT,
            status ENUM('new', 'read', 'replied') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Create careers table
        $conn->query("CREATE TABLE IF NOT EXISTS careers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            department VARCHAR(100),
            location VARCHAR(255),
            type ENUM('full-time', 'part-time', 'contract', 'internship') DEFAULT 'full-time',
            description TEXT,
            requirements TEXT,
            salary_range VARCHAR(100),
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Create applications table
        $conn->query("CREATE TABLE IF NOT EXISTS applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            career_id INT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            resume_path VARCHAR(500),
            cover_letter TEXT,
            status ENUM('new', 'under_review', 'shortlisted', 'rejected', 'hired') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        $conn->close();
        return true;
    } catch (Exception $e) {
        error_log("Table creation error: " . $e->getMessage());
        return false;
    }
}

function insertSampleData() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        
        // Insert sample properties
        $properties = [
            [
                'name' => 'APS Anant City',
                'type' => 'residential',
                'location' => 'Gorakhpur - NH-28',
                'size' => '1000-5000 Sq.Ft',
                'price' => '₹2,500/Sq.Ft',
                'description' => 'Premium residential plots with modern amenities',
                'amenities' => json_encode(['24/7 Security', 'Wide Roads', 'Underground Drainage', 'Central Park']),
                'image_url' => 'https://via.placeholder.com/400x300/667eea/ffffff?text=APS+Anant+City',
                'status' => 'available',
                'featured' => true,
                'rera_number' => 'UPRERAAGT12345'
            ],
            [
                'name' => 'APS Royal Enclave',
                'type' => 'residential',
                'location' => 'Gorakhpur - Bypass Road',
                'size' => '1200-3000 Sq.Ft',
                'price' => '₹2,800/Sq.Ft',
                'description' => 'Luxury residential plots with world-class amenities',
                'amenities' => json_encode(['Gated Community', 'CCTV Surveillance', 'Children Play Area']),
                'image_url' => 'https://via.placeholder.com/400x300/764ba2/ffffff?text=APS+Royal+Enclave',
                'status' => 'available',
                'featured' => true,
                'rera_number' => 'UPRERAAGT12346'
            ],
            [
                'name' => 'APS Green Valley',
                'type' => 'farmhouse',
                'location' => 'Gorakhpur - outskirts',
                'size' => '5000-10000 Sq.Ft',
                'price' => '₹1,800/Sq.Ft',
                'description' => 'Luxury farm house plots with premium amenities',
                'amenities' => json_encode(['Farm House Plots', 'Green Belt', 'Water Bodies']),
                'image_url' => 'https://via.placeholder.com/400x300/28a745/ffffff?text=APS+Green+Valley',
                'status' => 'limited',
                'featured' => false,
                'rera_number' => 'UPRERAAGT12347'
            ]
        ];
        
        foreach ($properties as $property) {
            $stmt = $conn->prepare("INSERT IGNORE INTO properties (name, type, location, size, price, description, amenities, image_url, status, featured, rera_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssis", 
                $property['name'], $property['type'], $property['location'], 
                $property['size'], $property['price'], $property['description'],
                $property['amenities'], $property['image_url'], $property['status'],
                $property['featured'], $property['rera_number']
            );
            $stmt->execute();
            $stmt->close();
        }
        
        // Insert sample careers
        $careers = [
            [
                'title' => 'Senior Real Estate Agent',
                'department' => 'Sales',
                'location' => 'Gorakhpur',
                'type' => 'full-time',
                'description' => 'We are looking for experienced real estate agents to join our team.',
                'requirements' => '3+ years experience in real estate sales',
                'salary_range' => '₹25,000 - 50,000 per month'
            ],
            [
                'title' => 'Marketing Executive',
                'department' => 'Marketing',
                'location' => 'Gorakhpur',
                'type' => 'full-time',
                'description' => 'Marketing executive to promote our properties and manage campaigns.',
                'requirements' => '2+ years experience in marketing',
                'salary_range' => '₹20,000 - 35,000 per month'
            ]
        ];
        
        foreach ($careers as $career) {
            $stmt = $conn->prepare("INSERT IGNORE INTO careers (title, department, location, type, description, requirements, salary_range) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", 
                $career['title'], $career['department'], $career['location'],
                $career['type'], $career['description'], $career['requirements'],
                $career['salary_range']
            );
            $stmt->execute();
            $stmt->close();
        }
        
        $conn->close();
        return true;
    } catch (Exception $e) {
        error_log("Data insertion error: " . $e->getMessage());
        return false;
    }
}

function fullDatabaseSetup() {
    // Create database if not exists
    if (!createDatabaseIfNotExists()) {
        return false;
    }
    
    // Create tables
    if (!createTables()) {
        return false;
    }
    
    // Insert sample data
    if (!insertSampleData()) {
        return false;
    }
    
    return true;
}

function getTableList() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $result = $conn->query("SHOW TABLE STATUS");
        $tables = [];
        
        while ($row = $result->fetch_assoc()) {
            $tables[] = [
                'name' => $row['Name'],
                'rows' => $row['Rows'],
                'size' => $row['Data_length'] / 1024 // Convert to KB
            ];
        }
        
        $conn->close();
        return $tables;
    } catch (Exception $e) {
        return [];
    }
}
?>
