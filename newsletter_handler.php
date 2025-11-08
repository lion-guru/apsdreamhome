<?php
/**
 * Newsletter Subscription Handler - APS Dream Homes
 * Handles newsletter subscriptions and email preferences
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once '../includes/db_connection.php';
        $pdo = getDbConnection();

        // Get form data
        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subscription_type = trim($_POST['subscription_type'] ?? 'general');
        $interests = isset($_POST['interests']) ? $_POST['interests'] : [];
        $frequency = trim($_POST['frequency'] ?? 'weekly');
        $source = trim($_POST['source'] ?? 'website');

        // Validation
        $errors = [];

        if (empty($email)) $errors[] = 'Email is required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($name)) $errors[] = 'Name is required';

        // Check if email already exists
        $existing_stmt = $pdo->prepare("SELECT id FROM newsletter_subscriptions WHERE email = ?");
        $existing_stmt->execute([$email]);
        if ($existing_stmt->fetch()) {
            echo json_encode([
                'success' => true,
                'message' => 'You are already subscribed to our newsletter!'
            ]);
            exit;
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        $interests_json = !empty($interests) ? json_encode($interests) : null;

        // Insert subscription
        $stmt = $pdo->prepare("
            INSERT INTO newsletter_subscriptions
            (name, email, phone, subscription_type, interests, frequency, source, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ");

        $stmt->execute([
            $name, $email, $phone, $subscription_type, $interests_json,
            $frequency, $source
        ]);

        // Send welcome email
        $welcome_subject = 'Welcome to APS Dream Homes Newsletter!';
        $welcome_body = "
Dear $name,

Thank you for subscribing to the APS Dream Homes newsletter!

You have successfully subscribed to our $subscription_type updates.

What you'll receive:
- Latest property listings and market trends
- Real estate investment tips and insights
- Exclusive offers and promotions
- Industry news and updates

Subscription Details:
- Email: $email
- Frequency: $frequency
- Interests: " . (!empty($interests) ? implode(', ', $interests) : 'General real estate updates') . "

You can manage your subscription preferences anytime by clicking the unsubscribe link in any of our emails.

Best regards,
APS Dream Homes Team
        ";

        $headers = "From: newsletter@apsdreamhomes.com\r\n";
        $headers .= "Reply-To: info@apsdreamhomes.com\r\n";

        // Uncomment to enable email sending
        // mail($email, $welcome_subject, $welcome_body, $headers);

        echo json_encode([
            'success' => true,
            'message' => 'Thank you for subscribing! You will receive a confirmation email shortly.'
        ]);

    } catch (Exception $e) {
        error_log('Newsletter subscription error: ' . $e->getMessage());
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
