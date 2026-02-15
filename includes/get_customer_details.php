<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['associate_id'])) {
    echo '<div class="alert alert-danger">Unauthorized access. Please log in.</div>';
    exit();
}

if (isset($_GET['customer_id'])) {
    $customer_id = $_GET['customer_id'];

    // Fetch customer details
    try {
        $db = \App\Core\App::database();
        $query = "
            SELECT 
                c.customer_name, 
                c.customer_email, 
                c.customer_phone, 
                c.customer_address, 
                c.customer_city, 
                c.customer_state, 
                c.customer_zip, 
                b.booking_id, 
                b.total_amount, 
                b.paid_amount, 
                b.remaining_amount 
            FROM customers c 
            JOIN bookings b ON c.customer_id = b.customer_id 
            WHERE c.customer_id = :customer_id
        ";
        $customer_details = $db->fetch($query, ['customer_id' => $customer_id]);

        if ($customer_details) {
            ?>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Customer Information</h6>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($customer_details['customer_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($customer_details['customer_email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer_details['customer_phone']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($customer_details['customer_address']); ?></p>
                        <p><strong>City:</strong> <?php echo htmlspecialchars($customer_details['customer_city']); ?></p>
                        <p><strong>State:</strong> <?php echo htmlspecialchars($customer_details['customer_state']); ?></p>
                        <p><strong>Zip:</strong> <?php echo htmlspecialchars($customer_details['customer_zip']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Booking Information</h6>
                        <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($customer_details['booking_id']); ?></p>

                        <p><strong>Total Amount:</strong> ₹<?php echo htmlspecialchars(number_format($customer_details['total_amount'], 2)); ?></p>
                        <p><strong>Paid Amount:</strong> ₹<?php echo htmlspecialchars(number_format($customer_details['paid_amount'], 2)); ?></p>
                        <p><strong>Remaining Amount:</strong> ₹<?php echo htmlspecialchars(number_format($customer_details['remaining_amount'], 2)); ?></p>
                    </div>
                </div>
            </div>
            <?php
        } else {
            echo '<div class="alert alert-warning">Customer details not found.</div>';
        }
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request. Customer ID not provided.</div>';
}
?>