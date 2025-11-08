<?php
/**
 * APS Dream Home - Sample Booking Creation & Commission Test
 * This script creates a complete sample booking and tests the commission system
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>APS Dream Home - Sample Booking Test</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .test-card { margin-bottom: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; }
    </style>
</head>
<body>
<div class='container mt-4'>
    <h1 class='text-center mb-4'>🏠 APS Dream Home - Sample Booking Test</h1>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='alert alert-success'>✅ Database Connection: SUCCESS</div>";
    
    // Test 1: Create Sample Customer
    echo "<div class='card test-card'>
        <div class='card-header'><h5>📝 Test 1: Create Sample Customer</h5></div>
        <div class='card-body'>";
    
    $customer_data = [
        'name' => 'Rajesh Kumar',
        'email' => 'rajesh.kumar@example.com',
        'phone' => '9876543210',
        'address' => 'A-123, Green Valley, New Delhi',
        'city' => 'New Delhi',
        'state' => 'Delhi',
        'pincode' => '110001',
        'aadhaar_number' => '123456789012',
        'pan_number' => 'ABCDE1234F',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Check if customer already exists
    $check_customer = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
    $check_customer->execute([$customer_data['email']]);
    $existing_customer = $check_customer->fetch();
    
    if (!$existing_customer) {
        $insert_customer = $pdo->prepare("INSERT INTO customers (name, email, phone, address, city, state, pincode, aadhaar_number, pan_number, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_customer->execute(array_values($customer_data));
        $customer_id = $pdo->lastInsertId();
        echo "<p class='success'>✅ Customer created successfully with ID: $customer_id</p>";
    } else {
        $customer_id = $existing_customer['id'];
        echo "<p class='info'>ℹ️ Customer already exists with ID: $customer_id</p>";
    }
    
    echo "<p><strong>Customer Details:</strong><br>";
    echo "Name: {$customer_data['name']}<br>";
    echo "Email: {$customer_data['email']}<br>";
    echo "Phone: {$customer_data['phone']}<br>";
    echo "Location: {$customer_data['city']}, {$customer_data['state']}</p>";
    echo "</div></div>";
    
    // Test 2: Get Available Property & Associate
    echo "<div class='card test-card'>
        <div class='card-header'><h5>🏢 Test 2: Select Property & Associate</h5></div>
        <div class='card-body'>";
    
    // Get a property
    $property_query = $pdo->query("SELECT id, project_name, plot_number, area_sqft, price_per_sqft, total_price FROM properties WHERE status = 'available' LIMIT 1");
    $property = $property_query->fetch(PDO::FETCH_ASSOC);
    
    if (!$property) {
        echo "<p class='error'>❌ No available properties found. Creating sample property...</p>";
        
        $sample_property = [
            'project_name' => 'Green Valley Phase 2',
            'plot_number' => 'GV-101',
            'area_sqft' => 1200,
            'price_per_sqft' => 5000,
            'total_price' => 6000000,
            'status' => 'available',
            'property_type' => 'residential',
            'location' => 'Sector 45, Gurgaon',
            'amenities' => 'Park, Club House, Swimming Pool',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $insert_property = $pdo->prepare("INSERT INTO properties (project_name, plot_number, area_sqft, price_per_sqft, total_price, status, property_type, location, amenities, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_property->execute(array_values($sample_property));
        $property_id = $pdo->lastInsertId();
        
        $property = array_merge($sample_property, ['id' => $property_id]);
        echo "<p class='success'>✅ Sample property created with ID: $property_id</p>";
    } else {
        $property_id = $property['id'];
        echo "<p class='info'>ℹ️ Using existing property with ID: $property_id</p>";
    }
    
    // Get an associate
    $associate_query = $pdo->query("SELECT id, name, email, phone, commission_rate FROM associates WHERE status = 'active' LIMIT 1");
    $associate = $associate_query->fetch(PDO::FETCH_ASSOC);
    
    if (!$associate) {
        echo "<p class='error'>❌ No active associates found. Please add associates first.</p>";
        $associate_id = null;
    } else {
        $associate_id = $associate['id'];
        echo "<p class='info'>ℹ️ Using associate: {$associate['name']} (ID: $associate_id)</p>";
    }
    
    echo "<p><strong>Property Details:</strong><br>";
    echo "Project: {$property['project_name']}<br>";
    echo "Plot: {$property['plot_number']}<br>";
    echo "Area: {$property['area_sqft']} sq ft<br>";
    echo "Price: ₹" . number_format($property['total_price']) . "</p>";
    
    if ($associate) {
        echo "<p><strong>Associate Details:</strong><br>";
        echo "Name: {$associate['name']}<br>";
        echo "Email: {$associate['email']}<br>";
        echo "Commission Rate: {$associate['commission_rate']}%</p>";
    }
    echo "</div></div>";
    
    // Test 3: Create Booking
    echo "<div class='card test-card'>
        <div class='card-header'><h5>📋 Test 3: Create Sample Booking</h5></div>
        <div class='card-body'>";
    
    $booking_data = [
        'customer_id' => $customer_id,
        'property_id' => $property_id,
        'associate_id' => $associate_id,
        'booking_amount' => $property['total_price'],
        'down_payment' => $property['total_price'] * 0.20, // 20% down payment
        'remaining_amount' => $property['total_price'] * 0.80,
        'booking_date' => date('Y-m-d'),
        'status' => 'confirmed',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Check if booking already exists
    $check_booking = $pdo->prepare("SELECT id FROM bookings WHERE customer_id = ? AND property_id = ?");
    $check_booking->execute([$customer_id, $property_id]);
    $existing_booking = $check_booking->fetch();
    
    if (!$existing_booking) {
        $insert_booking = $pdo->prepare("INSERT INTO bookings (customer_id, property_id, associate_id, booking_amount, down_payment, remaining_amount, booking_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_booking->execute(array_values($booking_data));
        $booking_id = $pdo->lastInsertId();
        echo "<p class='success'>✅ Booking created successfully with ID: $booking_id</p>";
        
        // Update property status
        $update_property = $pdo->prepare("UPDATE properties SET status = 'booked' WHERE id = ?");
        $update_property->execute([$property_id]);
        echo "<p class='success'>✅ Property status updated to 'booked'</p>";
        
    } else {
        $booking_id = $existing_booking['id'];
        echo "<p class='info'>ℹ️ Booking already exists with ID: $booking_id</p>";
    }
    
    echo "<p><strong>Booking Details:</strong><br>";
    echo "Booking ID: $booking_id<br>";
    echo "Total Amount: ₹" . number_format($booking_data['booking_amount']) . "<br>";
    echo "Down Payment: ₹" . number_format($booking_data['down_payment']) . "<br>";
    echo "Remaining: ₹" . number_format($booking_data['remaining_amount']) . "<br>";
    echo "Date: {$booking_data['booking_date']}</p>";
    echo "</div></div>";
    
    // Test 4: Calculate and Create Commission
    if ($associate_id) {
        echo "<div class='card test-card'>
            <div class='card-header'><h5>💰 Test 4: Calculate Commission</h5></div>
            <div class='card-body'>";
        
        $commission_rate = $associate['commission_rate'] / 100;
        $commission_amount = $booking_data['booking_amount'] * $commission_rate;
        
        // Check if commission already exists
        $check_commission = $pdo->prepare("SELECT id FROM commission_transactions WHERE booking_id = ? AND associate_id = ?");
        $check_commission->execute([$booking_id, $associate_id]);
        $existing_commission = $check_commission->fetch();
        
        if (!$existing_commission) {
            $commission_data = [
                'associate_id' => $associate_id,
                'booking_id' => $booking_id,
                'commission_amount' => $commission_amount,
                'commission_rate' => $associate['commission_rate'],
                'status' => 'pending',
                'transaction_date' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $insert_commission = $pdo->prepare("INSERT INTO commission_transactions (associate_id, booking_id, commission_amount, commission_rate, status, transaction_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_commission->execute(array_values($commission_data));
            $commission_id = $pdo->lastInsertId();
            echo "<p class='success'>✅ Commission transaction created with ID: $commission_id</p>";
        } else {
            $commission_id = $existing_commission['id'];
            echo "<p class='info'>ℹ️ Commission already exists with ID: $commission_id</p>";
        }
        
        echo "<p><strong>Commission Details:</strong><br>";
        echo "Associate: {$associate['name']}<br>";
        echo "Commission Rate: {$associate['commission_rate']}%<br>";
        echo "Commission Amount: ₹" . number_format($commission_amount) . "<br>";
        echo "Status: Pending</p>";
        echo "</div></div>";
    }
    
    // Test 5: Create EMI Plan
    echo "<div class='card test-card'>
        <div class='card-header'><h5>📊 Test 5: Create EMI Plan</h5></div>
        <div class='card-body'>";
    
    $emi_tenure = 36; // 36 months
    $interest_rate = 12; // 12% annual
    $principal = $booking_data['remaining_amount'];
    
    // Calculate EMI using formula: P * r * (1+r)^n / ((1+r)^n - 1)
    $monthly_rate = $interest_rate / (12 * 100);
    $emi_amount = $principal * $monthly_rate * pow(1 + $monthly_rate, $emi_tenure) / (pow(1 + $monthly_rate, $emi_tenure) - 1);
    
    // Check if EMI plan already exists
    $check_emi = $pdo->prepare("SELECT id FROM emi_plans WHERE booking_id = ?");
    $check_emi->execute([$booking_id]);
    $existing_emi = $check_emi->fetch();
    
    if (!$existing_emi) {
        $emi_data = [
            'booking_id' => $booking_id,
            'customer_id' => $customer_id,
            'principal_amount' => $principal,
            'interest_rate' => $interest_rate,
            'tenure_months' => $emi_tenure,
            'emi_amount' => round($emi_amount, 2),
            'status' => 'active',
            'start_date' => date('Y-m-d', strtotime('+1 month')),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $insert_emi = $pdo->prepare("INSERT INTO emi_plans (booking_id, customer_id, principal_amount, interest_rate, tenure_months, emi_amount, status, start_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_emi->execute(array_values($emi_data));
        $emi_plan_id = $pdo->lastInsertId();
        echo "<p class='success'>✅ EMI Plan created with ID: $emi_plan_id</p>";
        
        // Create first few EMI installments
        for ($i = 1; $i <= 3; $i++) {
            $due_date = date('Y-m-d', strtotime("+$i month"));
            $installment_data = [
                'emi_plan_id' => $emi_plan_id,
                'installment_number' => $i,
                'due_date' => $due_date,
                'amount' => round($emi_amount, 2),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $insert_installment = $pdo->prepare("INSERT INTO emi_installments (emi_plan_id, installment_number, due_date, amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_installment->execute(array_values($installment_data));
        }
        echo "<p class='success'>✅ First 3 EMI installments created</p>";
        
    } else {
        $emi_plan_id = $existing_emi['id'];
        echo "<p class='info'>ℹ️ EMI Plan already exists with ID: $emi_plan_id</p>";
    }
    
    echo "<p><strong>EMI Details:</strong><br>";
    echo "Principal: ₹" . number_format($principal) . "<br>";
    echo "Interest Rate: $interest_rate% p.a.<br>";
    echo "Tenure: $emi_tenure months<br>";
    echo "EMI Amount: ₹" . number_format($emi_amount, 2) . "<br>";
    echo "Start Date: " . date('Y-m-d', strtotime('+1 month')) . "</p>";
    echo "</div></div>";
    
    // Test 6: Summary Report
    echo "<div class='card test-card'>
        <div class='card-header'><h5>📈 Test 6: Booking Summary Report</h5></div>
        <div class='card-body'>";
    
    // Get complete booking details with joins
    $summary_query = $pdo->prepare("
        SELECT 
            b.id as booking_id,
            c.name as customer_name,
            c.email as customer_email,
            c.phone as customer_phone,
            p.project_name,
            p.plot_number,
            p.total_price,
            a.name as associate_name,
            ct.commission_amount,
            ep.emi_amount,
            ep.tenure_months,
            b.status as booking_status,
            b.created_at
        FROM bookings b
        LEFT JOIN customers c ON b.customer_id = c.id
        LEFT JOIN properties p ON b.property_id = p.id
        LEFT JOIN associates a ON b.associate_id = a.id
        LEFT JOIN commission_transactions ct ON b.id = ct.booking_id
        LEFT JOIN emi_plans ep ON b.id = ep.booking_id
        WHERE b.id = ?
    ");
    $summary_query->execute([$booking_id]);
    $summary = $summary_query->fetch(PDO::FETCH_ASSOC);
    
    if ($summary) {
        echo "<div class='row'>";
        echo "<div class='col-md-6'>";
        echo "<h6>Customer Information</h6>";
        echo "<p>Name: {$summary['customer_name']}<br>";
        echo "Email: {$summary['customer_email']}<br>";
        echo "Phone: {$summary['customer_phone']}</p>";
        
        echo "<h6>Property Information</h6>";
        echo "<p>Project: {$summary['project_name']}<br>";
        echo "Plot: {$summary['plot_number']}<br>";
        echo "Price: ₹" . number_format($summary['total_price']) . "</p>";
        echo "</div>";
        
        echo "<div class='col-md-6'>";
        if ($summary['associate_name']) {
            echo "<h6>Associate Information</h6>";
            echo "<p>Name: {$summary['associate_name']}<br>";
            echo "Commission: ₹" . number_format($summary['commission_amount']) . "</p>";
        }
        
        if ($summary['emi_amount']) {
            echo "<h6>EMI Information</h6>";
            echo "<p>Monthly EMI: ₹" . number_format($summary['emi_amount']) . "<br>";
            echo "Tenure: {$summary['tenure_months']} months</p>";
        }
        
        echo "<h6>Booking Status</h6>";
        echo "<p>Status: " . ucfirst($summary['booking_status']) . "<br>";
        echo "Created: {$summary['created_at']}</p>";
        echo "</div>";
        echo "</div>";
        
        echo "<div class='alert alert-success mt-3'>";
        echo "🎉 <strong>Booking Test Completed Successfully!</strong><br>";
        echo "Booking ID: {$summary['booking_id']}<br>";
        echo "Total Value: ₹" . number_format($summary['total_price']) . "<br>";
        echo "All systems working perfectly!";
        echo "</div>";
    }
    
    echo "</div></div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>❌ Database Error: " . $e->getMessage() . "</div>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ General Error: " . $e->getMessage() . "</div>";
}

echo "
    <div class='text-center mt-4'>
        <a href='admin/' class='btn btn-primary'>Access Admin Panel</a>
        <a href='test_final_system.php' class='btn btn-info'>Run System Tests</a>
        <a href='launch_system.php' class='btn btn-success'>Launch System</a>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>