<?php

/**
 * Seed Script: Add Test Associates
 * 
 * This script adds test associate data to the database for testing purposes.
 */

// Database configuration
$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create connection
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Starting associate seed...\n";
    
    // Check if associates table has data
    $stmt = $conn->query("SELECT COUNT(*) FROM associates");
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "✓ Associates table already has $count records. Skipping seed.\n";
        exit(0);
    }
    
    // Generate referral codes
    function generateReferralCode($name) {
        return strtoupper(substr($name, 0, 3)) . rand(1000, 9999);
    }
    
    // Test associates data
    $associates = [
        [
            'name' => 'Rajesh Kumar',
            'email' => 'rajesh.associate@apsdreamhome.com',
            'phone' => '9876543210',
            'password' => password_hash('associate123', PASSWORD_DEFAULT),
            'referral_code' => 'RAJ1234',
            'commission_rate' => 2.50,
            'experience_years' => 5,
            'total_properties' => 15,
            'sold_properties' => 8,
            'total_commission' => 125000.00,
            'wallet_balance' => 45000.00,
            'rating' => 4.5,
            'total_reviews' => 12,
            'address' => '123 Main Street, Civil Lines',
            'city' => 'Gorakhpur',
            'state' => 'Uttar Pradesh',
            'pincode' => '273001',
            'pan_number' => 'ABCDE1234F',
            'aadhar_number' => '1234-5678-9012',
            'bank_name' => 'State Bank of India',
            'account_number' => '1234567890123456',
            'ifsc_code' => 'SBIN0001234'
        ],
        [
            'name' => 'Priya Sharma',
            'email' => 'priya.associate@apsdreamhome.com',
            'phone' => '9876543211',
            'password' => password_hash('associate123', PASSWORD_DEFAULT),
            'referral_code' => 'PRI5678',
            'commission_rate' => 3.00,
            'experience_years' => 8,
            'total_properties' => 22,
            'sold_properties' => 15,
            'total_commission' => 185000.00,
            'wallet_balance' => 75000.00,
            'rating' => 4.8,
            'total_reviews' => 18,
            'address' => '456 Park Road, Mahanagar',
            'city' => 'Lucknow',
            'state' => 'Uttar Pradesh',
            'pincode' => '226003',
            'pan_number' => 'BCDEF5678G',
            'aadhar_number' => '2345-6789-0123',
            'bank_name' => 'HDFC Bank',
            'account_number' => '2345678901234567',
            'ifsc_code' => 'HDFC0002345'
        ],
        [
            'name' => 'Amit Verma',
            'email' => 'amit.associate@apsdreamhome.com',
            'phone' => '9876543212',
            'password' => password_hash('associate123', PASSWORD_DEFAULT),
            'referral_code' => 'AMI9012',
            'commission_rate' => 2.75,
            'experience_years' => 3,
            'total_properties' => 10,
            'sold_properties' => 5,
            'total_commission' => 65000.00,
            'wallet_balance' => 25000.00,
            'rating' => 4.2,
            'total_reviews' => 8,
            'address' => '789 Colony Road, Kasya',
            'city' => 'Kushinagar',
            'state' => 'Uttar Pradesh',
            'pincode' => '274401',
            'pan_number' => 'CDEFG9012H',
            'aadhar_number' => '3456-7890-1234',
            'bank_name' => 'ICICI Bank',
            'account_number' => '3456789012345678',
            'ifsc_code' => 'ICIC0003456'
        ]
    ];
    
    // Insert associates
    $stmt = $conn->prepare("INSERT INTO associates (name, email, phone, password, referral_code, commission_rate, experience_years, total_properties, sold_properties, total_commission, wallet_balance, rating, total_reviews, address, city, state, pincode, pan_number, aadhar_number, bank_name, account_number, ifsc_code, status, joined_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
    
    foreach ($associates as $associate) {
        $stmt->execute([
            $associate['name'],
            $associate['email'],
            $associate['phone'],
            $associate['password'],
            $associate['referral_code'],
            $associate['commission_rate'],
            $associate['experience_years'],
            $associate['total_properties'],
            $associate['sold_properties'],
            $associate['total_commission'],
            $associate['wallet_balance'],
            $associate['rating'],
            $associate['total_reviews'],
            $associate['address'],
            $associate['city'],
            $associate['state'],
            $associate['pincode'],
            $associate['pan_number'],
            $associate['aadhar_number'],
            $associate['bank_name'],
            $associate['account_number'],
            $associate['ifsc_code']
        ]);
        echo "✓ Added associate: {$associate['name']}\n";
    }
    
    // Add sample activities for first associate
    $activities = [
        ['associate_id' => 1, 'activity_type' => 'property_listed', 'description' => 'Listed new property: Luxury Apartment in Gomti Nagar'],
        ['associate_id' => 1, 'activity_type' => 'lead_converted', 'description' => 'Converted lead to client: Ramesh Kumar'],
        ['associate_id' => 1, 'activity_type' => 'deal_closed', 'description' => 'Closed deal: Modern Villa in Hazratganj'],
        ['associate_id' => 2, 'activity_type' => 'property_listed', 'description' => 'Listed new property: Commercial Space in Alambagh'],
        ['associate_id' => 2, 'activity_type' => 'commission_earned', 'description' => 'Commission earned: ₹25,000 from property sale']
    ];
    
    $stmt = $conn->prepare("INSERT INTO associate_activities (associate_id, activity_type, description, created_at) VALUES (?, ?, ?, NOW())");
    foreach ($activities as $activity) {
        $stmt->execute([$activity['associate_id'], $activity['activity_type'], $activity['description']]);
        echo "✓ Added activity for associate {$activity['associate_id']}\n";
    }
    
    // Add sample commissions
    $commissions = [
        ['associate_id' => 1, 'commission_amount' => 25000.00, 'commission_rate' => 2.50, 'property_value' => 1000000.00, 'status' => 'paid', 'payment_date' => date('Y-m-d H:i:s', strtotime('-1 week'))],
        ['associate_id' => 1, 'commission_amount' => 37500.00, 'commission_rate' => 2.50, 'property_value' => 1500000.00, 'status' => 'approved'],
        ['associate_id' => 2, 'commission_amount' => 45000.00, 'commission_rate' => 3.00, 'property_value' => 1500000.00, 'status' => 'pending'],
        ['associate_id' => 3, 'commission_amount' => 15000.00, 'commission_rate' => 2.75, 'property_value' => 545454.00, 'status' => 'approved']
    ];
    
    $stmt = $conn->prepare("INSERT INTO associate_commissions (associate_id, commission_amount, commission_rate, property_value, status, payment_date, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    foreach ($commissions as $commission) {
        $stmt->execute([
            $commission['associate_id'],
            $commission['commission_amount'],
            $commission['commission_rate'],
            $commission['property_value'],
            $commission['status'],
            $commission['payment_date'] ?? null
        ]);
        echo "✓ Added commission for associate {$commission['associate_id']}\n";
    }
    
    echo "\n✓ Associate seed completed successfully!\n";
    echo "✓ Added 3 test associates\n";
    echo "✓ Added 5 activities\n";
    echo "✓ Added 4 commissions\n";
    
} catch (PDOException $e) {
    echo "✗ Error seeding associates: " . $e->getMessage() . "\n";
    exit(1);
}
