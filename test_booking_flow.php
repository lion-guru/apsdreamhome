<?php
require_once 'app/core/Autoloader.php';

use App\Core\App;

try {
    $db = App::database();
    echo "Starting end-to-end booking flow test...\n";

    // 1. Simulate Public Booking Submission
    // We'll use a random email to ensure it's a new "customer" or update an existing one
    $testEmail = 'test_' . time() . '@example.com';
    $testPhone = '9' . rand(100000000, 999999999);
    $testName = 'Test User ' . time();

    echo "Step 1: Simulating public booking submission for $testEmail...\n";

    // Get a random available property
    $property = $db->fetch("SELECT id FROM properties WHERE status = 'available' LIMIT 1");
    if (!$property) {
        throw new Exception("No available properties found for testing.");
    }
    $propertyId = $property['id'];

    // This logic mimics PageController::submitBooking
    $existing_customer = $db->fetch("SELECT id FROM customers WHERE phone = ? OR email = ?", [$testPhone, $testEmail]);

    $customer_db_id = null;
    if ($existing_customer) {
        $customer_db_id = $existing_customer['id'];
        $db->update('customers', [
            'name' => $testName,
            'email' => $testEmail,
            'phone' => $testPhone
        ], "id = ?", [$customer_db_id]);
        echo "Updated existing customer ID: $customer_db_id\n";
    } else {
        $customer_id = 'CUST-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $password = 'cust' . rand(1000, 9999);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $db->insert('customers', [
            'id' => $customer_id,
            'name' => $testName,
            'email' => $testEmail,
            'phone' => $testPhone,
            'password' => $hashed_password,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $customer_db_id = $customer_id;
        echo "Created new customer ID: $customer_db_id\n";
    }

    $booking_data = [
        'customer_id' => $customer_db_id,
        'property_id' => $propertyId,
        'booking_type' => 'site_visit',
        'visit_date' => date('Y-m-d', strtotime('+1 day')),
        'visit_time' => '10:00:00',
        'budget_range' => '50L - 75L',
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];

    if ($db->insert('bookings', $booking_data)) {
        $bookingId = $db->lastInsertId();
        echo "Booking created successfully. ID: $bookingId\n";

        // 2. Verify in Admin List (Simulate the query in BookingController::index)
        echo "Step 2: Verifying booking appears in admin list...\n";
        $sql = "SELECT b.*, p.title as property_title,
                       COALESCE(u.uname, c.name) as customer_name
                FROM bookings b
                LEFT JOIN properties p ON b.property_id = p.id
                LEFT JOIN customers c ON b.customer_id = c.id
                LEFT JOIN user u ON c.user_id = u.uid
                WHERE b.id = ?";
        $booking = $db->fetch($sql, [$bookingId]);

        if ($booking) {
            echo "Verification Success!\n";
            echo "Booking ID: " . $booking['id'] . "\n";
            echo "Customer Name: " . $booking['customer_name'] . " (Should be $testName)\n";
            echo "Property Title: " . $booking['property_title'] . "\n";
            echo "Status: " . $booking['status'] . "\n";
        } else {
            echo "Verification Failed: Booking not found via admin query.\n";
        }

    } else {
        echo "Failed to insert booking.\n";
    }

} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
}
