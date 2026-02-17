<?php
/**
 * Script to create missing tables for legal services, team members, and FAQs
 */

// Include database connection
require_once __DIR__ . '/../includes/db_connection.php';

echo "Starting creation of missing tables...\n";

try {
    // Get database connection
    $pdo = getMysqliConnection();
    
    if ($pdo === null) {
        throw new Exception("Failed to connect to the database");
    }
    
    // 1. Create legal_services table
    echo "Creating legal_services table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `legal_services` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT NOT NULL,
        `icon` VARCHAR(100) DEFAULT NULL,
        `price_range` VARCHAR(100) DEFAULT NULL,
        `duration` VARCHAR(50) DEFAULT NULL,
        `features` TEXT DEFAULT NULL,
        `status` ENUM('active', 'inactive') DEFAULT 'active',
        `display_order` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    echo "✓ legal_services table created successfully\n";
    
    // 2. Create team_members table
    echo "Creating team_members table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `team_members` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `position` VARCHAR(255) NOT NULL,
        `bio` TEXT DEFAULT NULL,
        `photo` VARCHAR(255) DEFAULT NULL,
        `email` VARCHAR(255) DEFAULT NULL,
        `phone` VARCHAR(50) DEFAULT NULL,
        `linkedin` VARCHAR(255) DEFAULT NULL,
        `expertise` VARCHAR(255) DEFAULT NULL,
        `experience` VARCHAR(100) DEFAULT NULL,
        `display_order` INT DEFAULT 0,
        `status` ENUM('active', 'inactive') DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    echo "✓ team_members table created successfully\n";
    
    // 3. Create faqs table (if not already created by manage_faqs.php)
    echo "Creating faqs table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `faqs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `question` TEXT NOT NULL,
        `answer` TEXT NOT NULL,
        `category` VARCHAR(100) DEFAULT 'General',
        `display_order` INT DEFAULT 0,
        `status` ENUM('active', 'inactive') DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY `idx_category` (`category`),
        KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    echo "✓ faqs table created successfully\n";
    
    // 4. Insert sample data for testing
    echo "Inserting sample data...\n";
    
    // Sample legal services
    $legalServices = [
        ['Property Documentation', 'Complete property documentation services including title verification, registration, and mutation.', 'document', '₹5,000 - ₹15,000', '3-7 days', 'Title Verification|Registration Assistance|Mutation Services|Document Preparation'],
        ['Legal Consultation', 'Expert legal consultation for property-related matters and dispute resolution.', 'consultation', '₹2,000 - ₹5,000', '1-2 hours', '30-min Consultation|Legal Advice|Document Review|Follow-up Support'],
        ['Agreement Drafting', 'Professional drafting of property agreements, contracts, and legal documents.', 'drafting', '₹3,000 - ₹10,000', '2-5 days', 'Agreement Drafting|Contract Review|Customization|Legal Validation']
    ];
    
    foreach ($legalServices as $service) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO `legal_services` (title, description, icon, price_range, duration, features) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute($service);
    }
    echo "✓ Sample legal services inserted\n";
    
    // Sample team members
    $teamMembers = [
        ['Rajesh Kumar', 'Senior Legal Advisor', '15+ years of experience in property law and real estate documentation.', 'team1.jpg', 'rajesh@apsdreamhome.com', '+91-9876543210', 'linkedin.com/rajeshkumar', 'Property Law, Documentation', '15 years'],
        ['Priya Sharma', 'Legal Documentation Expert', 'Specialized in property registration and title verification processes.', 'team2.jpg', 'priya@apsdreamhome.com', '+91-9876543211', 'linkedin.com/priyasharma', 'Title Verification, Registration', '8 years'],
        ['Amit Singh', 'Legal Consultant', 'Expert in dispute resolution and property agreement drafting.', 'team3.jpg', 'amit@apsdreamhome.com', '+91-9876543212', 'linkedin.com/amitsingh', 'Dispute Resolution, Agreements', '12 years']
    ];
    
    foreach ($teamMembers as $member) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO `team_members` (name, position, bio, photo, email, phone, linkedin, expertise, experience) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($member);
    }
    echo "✓ Sample team members inserted\n";
    
    // Sample FAQs
    $faqs = [
        ['What documents are required for property registration?', 'The documents required typically include sale deed, identity proof, address proof, property tax receipts, and NOC from relevant authorities.'],
        ['How long does the property registration process take?', 'The registration process usually takes 3-7 working days, depending on the completeness of documents and government office workload.'],
        ['What is title verification and why is it important?', 'Title verification is the process of checking the legal ownership history of a property to ensure there are no disputes or encumbrances. It\'s crucial to avoid future legal issues.'],
        ['Can I register property without a lawyer?', 'While it\'s possible, we highly recommend professional legal assistance to ensure all documents are properly prepared and the process is completed correctly.'],
        ['What are the common property disputes?', 'Common disputes include boundary issues, ownership claims, inheritance disputes, and unauthorized construction.'],
        ['How much does legal documentation cost?', 'Costs vary based on property value and services required, typically ranging from ₹5,000 to ₹15,000 for complete documentation services.']
    ];
    
    foreach ($faqs as $faq) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO `faqs` (question, answer) VALUES (?, ?)");
        $stmt->execute($faq);
    }
    echo "✓ Sample FAQs inserted\n";
    
    echo "\n✅ All missing tables created successfully with sample data!\n";
    echo "You can now access the legal services page without database errors.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?>