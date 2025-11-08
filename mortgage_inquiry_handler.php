<?php
/**
 * Mortgage Inquiry Handler - APS Dream Homes
 * Handles mortgage and loan inquiries
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once '../includes/db_connection.php';
        $pdo = getDbConnection();

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $property_value = (float)($_POST['property_value'] ?? 0);
        $down_payment = (float)($_POST['down_payment'] ?? 0);
        $loan_amount = (float)($_POST['loan_amount'] ?? 0);
        $loan_tenure = (int)($_POST['loan_tenure'] ?? 0);
        $employment_type = trim($_POST['employment_type'] ?? '');
        $monthly_income = (float)($_POST['monthly_income'] ?? 0);
        $existing_loans = (float)($_POST['existing_loans'] ?? 0);
        $property_location = trim($_POST['property_location'] ?? '');
        $urgency_level = trim($_POST['urgency_level'] ?? '');
        $additional_info = trim($_POST['additional_info'] ?? '');

        // Validation
        $errors = [];

        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email)) $errors[] = 'Email is required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($phone)) $errors[] = 'Phone number is required';
        if ($property_value <= 0) $errors[] = 'Property value must be greater than 0';
        if ($down_payment < 0) $errors[] = 'Down payment cannot be negative';
        if ($loan_amount <= 0) $errors[] = 'Loan amount must be greater than 0';
        if ($loan_tenure <= 0) $errors[] = 'Loan tenure must be greater than 0';
        if (empty($employment_type)) $errors[] = 'Employment type is required';
        if ($monthly_income <= 0) $errors[] = 'Monthly income must be greater than 0';
        if (empty($property_location)) $errors[] = 'Property location is required';

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Calculate loan-to-value ratio
        $loan_to_value_ratio = ($loan_amount / $property_value) * 100;

        // Insert mortgage inquiry
        $stmt = $pdo->prepare("
            INSERT INTO mortgage_inquiries
            (name, email, phone, property_value, down_payment, loan_amount, loan_tenure,
             employment_type, monthly_income, existing_loans, property_location, urgency_level,
             additional_info, loan_to_value_ratio, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");

        $stmt->execute([
            $name, $email, $phone, $property_value, $down_payment, $loan_amount,
            $loan_tenure, $employment_type, $monthly_income, $existing_loans,
            $property_location, $urgency_level, $additional_info, $loan_to_value_ratio
        ]);

        // Send email notification
        $to = 'mortgage@apsdreamhomes.com';
        $email_subject = 'New Mortgage Inquiry - APS Dream Homes';
        $email_body = "
New mortgage inquiry received:

Client Details:
Name: $name
Email: $email
Phone: $phone
Employment Type: $employment_type
Monthly Income: ₹" . number_format($monthly_income) . "
Existing Loans: ₹" . number_format($existing_loans) . "

Loan Request:
Property Value: ₹" . number_format($property_value) . "
Down Payment: ₹" . number_format($down_payment) . "
Loan Amount: ₹" . number_format($loan_amount) . "
Loan Tenure: $loan_tenure years
Property Location: $property_location
Urgency Level: $urgency_level
Loan-to-Value Ratio: " . number_format($loan_to_value_ratio, 2) . "%

Additional Information:
" . ($additional_info ?: 'None provided') . "
        ";

        $headers = "From: noreply@apsdreamhomes.com\r\n";
        $headers .= "Reply-To: $email\r\n";

        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your mortgage inquiry! Our financial advisor will contact you within 24 hours with the best loan options.'
        ]);

    } catch (Exception $e) {
        error_log('Mortgage inquiry error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred. Please try again later.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
