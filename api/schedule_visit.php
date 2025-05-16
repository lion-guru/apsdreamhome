<?php
/**
 * Automated Property Visit Scheduling API
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/middleware/rate_limit_middleware.php';
require_once __DIR__ . '/../includes/input_validation.php';

// Apply rate limiting
$rateLimitMiddleware->handle('schedule_visit');

// Get database connection
$con = getDbConnection();
if (!$con) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Initialize input validator
$validator = new InputValidator($con);

// Validate input
$property_id = $validator->validateInt($_POST['property_id'] ?? 0);
$name = $validator->sanitizeString($_POST['name'] ?? '');
$email = $validator->validateEmail($_POST['email'] ?? '');
$phone = $validator->sanitizeString($_POST['phone'] ?? '');
$preferred_date = $validator->validateDate($_POST['preferred_date'] ?? '');
$preferred_time = $validator->sanitizeString($_POST['preferred_time'] ?? '');
if (!in_array($preferred_time, ['10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid time slot']);
    exit;
}
$notes = $validator->sanitizeString($_POST['notes'] ?? '');

if (!$property_id || !$name || !$email || !$phone || !$preferred_date || !$preferred_time) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {
    // Begin transaction
    $con->begin_transaction();

    // Get property details and agent availability
    $property_query = "SELECT p.*, u.id as agent_id, u.email as agent_email,
                             (SELECT COUNT(*) FROM bookings b 
                              WHERE b.property_id = p.id 
                                AND b.visit_date = ? 
                                AND b.visit_time = ?) as existing_bookings
                      FROM properties p
                      LEFT JOIN users u ON p.owner_id = u.id
                      WHERE p.id = ?";
    
    $stmt = $con->prepare($property_query);
    $stmt->bind_param('ssi', $preferred_date, $preferred_time, $property_id);
    $stmt->execute();
    $property = $stmt->get_result()->fetch_assoc();

    // Check if timeslot is available (max 2 bookings per slot)
    if ($property['existing_bookings'] >= 2) {
        // Find next available slot
        $next_slot_query = "SELECT visit_date, visit_time 
                           FROM bookings 
                           WHERE property_id = ? 
                             AND visit_date >= ?
                           GROUP BY visit_date, visit_time
                           HAVING COUNT(*) < 2
                           ORDER BY visit_date ASC, visit_time ASC
                           LIMIT 1";
        
        $stmt = $con->prepare($next_slot_query);
        $stmt->bind_param('is', $property_id, $preferred_date);
        $stmt->execute();
        $next_slot = $stmt->get_result()->fetch_assoc();

        if ($next_slot) {
            $preferred_date = $next_slot['visit_date'];
            $preferred_time = $next_slot['visit_time'];
        } else {
            // No slots available in next few days
            throw new Exception('No available slots');
        }
    }

    // Create or get customer
    $customer_query = "INSERT INTO customers (name, email, phone, created_at) 
                      VALUES (?, ?, ?, NOW())
                      ON DUPLICATE KEY UPDATE 
                      id=LAST_INSERT_ID(id), 
                      updated_at=NOW()";
    
    $stmt = $con->prepare($customer_query);
    $stmt->bind_param('sss', $name, $email, $phone);
    $stmt->execute();
    $customer_id = $stmt->insert_id;

    // Create booking
    $booking_query = "INSERT INTO bookings (property_id, customer_id, visit_date, visit_time, notes, status)
                     VALUES (?, ?, ?, ?, ?, 'confirmed')";
    
    $stmt = $con->prepare($booking_query);
    $stmt->bind_param('iisss', $property_id, $customer_id, $preferred_date, $preferred_time, $notes);
    $stmt->execute();
    $booking_id = $stmt->insert_id;

    // Create lead
    $lead_query = "INSERT INTO leads (name, email, phone, source, status, notes)
                   VALUES (?, ?, ?, 'property_visit', 'scheduled', ?)";
    
    $stmt = $con->prepare($lead_query);
    $stmt->bind_param('ssss', $name, $email, $phone, $notes);
    $stmt->execute();
    $lead_id = $stmt->insert_id;

    // Link lead to customer journey
    $journey_query = "INSERT INTO customer_journeys (customer_id, lead_id, property_id, interaction_type, notes)
                     VALUES (?, ?, ?, 'visit_scheduled', ?)";
    
    $stmt = $con->prepare($journey_query);
    $stmt->bind_param('iiis', $customer_id, $lead_id, $property_id, $notes);
    $stmt->execute();

    // Notify agent
    if ($property['agent_id']) {
        $notification_query = "INSERT INTO notifications (user_id, type, message, link)
                             VALUES (?, 'visit_scheduled', ?, ?)";
        
        $message = "New property visit scheduled for {$preferred_date} at {$preferred_time}";
        $link = "/admin/bookings.php?id={$booking_id}";
        
        $stmt = $con->prepare($notification_query);
        $stmt->bind_param('iss', $property['agent_id'], $message, $link);
        $stmt->execute();

        // Send email notification
        $to = $property['agent_email'];
        $subject = "New Property Visit Scheduled";
        $message = "A new visit has been scheduled for your property.\n\n";
        $message .= "Details:\n";
        $message .= "Date: {$preferred_date}\n";
        $message .= "Time: {$preferred_time}\n";
        $message .= "Customer: {$name}\n";
        $message .= "Phone: {$phone}\n";
        $message .= "Email: {$email}\n";
        $message .= "Notes: {$notes}\n\n";
        $message .= "View booking: " . SITE_URL . "/admin/bookings.php?id={$booking_id}";
        
        mail($to, $subject, $message);
    }

    // Send confirmation to customer
    $to = $email;
    $subject = "Property Visit Confirmation";
    $message = "Your property visit has been scheduled.\n\n";
    $message .= "Details:\n";
    $message .= "Property: {$property['title']}\n";
    $message .= "Date: {$preferred_date}\n";
    $message .= "Time: {$preferred_time}\n\n";
    $message .= "Location: {$property['location']}\n\n";
    $message .= "Notes: Please arrive 5 minutes before your scheduled time.\n";
    $message .= "If you need to reschedule, please contact us at " . SUPPORT_EMAIL;
    
    mail($to, $subject, $message);

    // Commit transaction
    $con->commit();

    // Return success
    echo json_encode([
        'status' => 'success',
        'booking_id' => $booking_id,
        'visit_date' => $preferred_date,
        'visit_time' => $preferred_time,
        'message' => 'Visit scheduled successfully'
    ]);

} catch (Exception $e) {
    // Rollback on error
    $con->rollback();
    error_log("Visit scheduling error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage() == 'No available slots' 
            ? 'No available slots for this property. Please try a different date.'
            : 'Failed to schedule visit. Please try again.'
    ]);
}
?>
