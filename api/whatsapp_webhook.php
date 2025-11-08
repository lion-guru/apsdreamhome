<?php
/**
 * APS Dream Home - WhatsApp Business API Webhook
 * Handles incoming WhatsApp messages and verification
 */

require_once '../includes/config.php';
require_once '../includes/whatsapp_integration.php';

header('Content-Type: application/json');

// Handle webhook verification (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $verify_token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';

    if ($verify_token === $config['whatsapp']['webhook_verify_token']) {
        echo $challenge;
        exit;
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid verification token']);
        exit;
    }
}

// Handle incoming messages (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');

    if (empty($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'No input data']);
        exit;
    }

    $data = json_decode($input, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        exit;
    }

    try {
        processWhatsAppWebhook($data);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        error_log('WhatsApp webhook error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
}

/**
 * Process incoming WhatsApp webhook data
 */
function processWhatsAppWebhook($data) {
    global $config;

    // Log incoming webhook
    logWhatsAppWebhook($data);

    // Process different types of webhook entries
    if (isset($data['entry'])) {
        foreach ($data['entry'] as $entry) {
            if (isset($entry['changes'])) {
                foreach ($entry['changes'] as $change) {
                    if ($change['field'] === 'messages') {
                        processWhatsAppMessage($change['value']);
                    }
                }
            }
        }
    }
}

/**
 * Process incoming WhatsApp message
 */
function processWhatsAppMessage($message_data) {
    global $config;

    if (!isset($message_data['messages'])) {
        return;
    }

    foreach ($message_data['messages'] as $message) {
        $message_type = $message['type'] ?? 'text';

        switch ($message_type) {
            case 'text':
                handleTextMessage($message);
                break;
            case 'image':
                handleImageMessage($message);
                break;
            case 'document':
                handleDocumentMessage($message);
                break;
            default:
                logWhatsAppActivity('UNKNOWN_MESSAGE_TYPE', 'Unsupported message type: ' . $message_type);
        }
    }
}

/**
 * Handle text message
 */
function handleTextMessage($message) {
    global $config;

    $from = $message['from'] ?? '';
    $text = $message['text']['body'] ?? '';
    $timestamp = $message['timestamp'] ?? time();

    // Log incoming message
    logWhatsAppActivity('INCOMING_TEXT', "From: {$from}, Message: {$text}");

    // Check if it's a command or regular message
    if (isCommand($text)) {
        handleCommand($from, $text);
    } else {
        handleRegularMessage($from, $text);
    }
}

/**
 * Handle image message
 */
function handleImageMessage($message) {
    global $config;

    $from = $message['from'] ?? '';
    $image_id = $message['image']['id'] ?? '';
    $caption = $message['image']['caption'] ?? '';

    logWhatsAppActivity('INCOMING_IMAGE', "From: {$from}, Image ID: {$image_id}, Caption: {$caption}");

    // Send acknowledgment
    sendWhatsAppMessage($from, "Thank you for sharing the image! Our team will review it and get back to you soon. 📸");
}

/**
 * Handle document message
 */
function handleDocumentMessage($message) {
    global $config;

    $from = $message['from'] ?? '';
    $document_id = $message['document']['id'] ?? '';
    $filename = $message['document']['filename'] ?? '';
    $caption = $message['document']['caption'] ?? '';

    logWhatsAppActivity('INCOMING_DOCUMENT', "From: {$from}, Document: {$filename}, Caption: {$caption}");

    // Send acknowledgment
    sendWhatsAppMessage($from, "Thank you for sharing the document! Our team will review it and get back to you soon. 📄");
}

/**
 * Check if message is a command
 */
function isCommand($text) {
    $commands = ['HELP', 'INFO', 'PROPERTIES', 'CONTACT', 'STATUS', 'MENU'];
    $text_upper = strtoupper(trim($text));
    return in_array($text_upper, $commands);
}

/**
 * Handle bot commands
 */
function handleCommand($phone, $command) {
    global $config;

    $command = strtoupper(trim($command));

    switch ($command) {
        case 'HELP':
            $response = "🤖 *APS Dream Home Bot Commands:*\n\n" .
                       "📋 *INFO* - Company information\n" .
                       "🏠 *PROPERTIES* - Available properties\n" .
                       "📞 *CONTACT* - Contact details\n" .
                       "📊 *STATUS* - System status\n" .
                       "🍽️ *MENU* - Main menu\n" .
                       "❓ *HELP* - Show this help\n\n" .
                       "Just type your message and our team will respond!";
            break;

        case 'INFO':
            $response = "🏢 *APS Dream Home*\n\n" .
                       "Your trusted partner in real estate!\n" .
                       "🌟 Specializing in residential & commercial properties\n" .
                       "📍 Serving across India\n" .
                       "💼 Professional service since 2020\n\n" .
                       "Contact us for all your property needs!";
            break;

        case 'PROPERTIES':
            $response = "🏠 *Available Properties*\n\n" .
                       "We have a wide range of properties:\n" .
                       "• Residential Plots\n" .
                       "• Commercial Spaces\n" .
                       "• Luxury Villas\n" .
                       "• Apartments\n" .
                       "• Farm Houses\n\n" .
                       "Send us your requirements for personalized recommendations!";
            break;

        case 'CONTACT':
            $response = "📞 *Contact Information*\n\n" .
                       "📱 Phone: " . $config['whatsapp']['phone_number'] . "\n" .
                       "📧 Email: " . ($config['email']['from_email'] ?? 'info@apsdreamhome.com') . "\n" .
                       "🌐 Website: " . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'www.apsdreamhome.com') . "\n" .
                       "⏰ Business Hours: " . ($config['whatsapp']['auto_responses']['business_hours'] ?? 'Mon-Fri 9AM-6PM') . "\n\n" .
                       "Our team is here to help!";
            break;

        case 'STATUS':
            $response = "✅ *System Status*\n\n" .
                       "🤖 AI Assistant: Online\n" .
                       "📱 WhatsApp: Connected\n" .
                       "📧 Email: Active\n" .
                       "🗄️ Database: Connected\n\n" .
                       "All systems operational! 🚀";
            break;

        case 'MENU':
            $response = "🍽️ *APS Dream Home Menu*\n\n" .
                       "1️⃣ Property Search\n" .
                       "2️⃣ Booking Inquiry\n" .
                       "3️⃣ Price Calculator\n" .
                       "4️⃣ Contact Support\n" .
                       "5️⃣ Business Info\n\n" .
                       "Reply with the number or just type your message!";
            break;

        default:
            $response = "❓ I didn't understand that command.\n\nType *HELP* for available commands.";
    }

    sendWhatsAppMessage($phone, $response);
}

/**
 * Handle regular message (forward to admin)
 */
function handleRegularMessage($phone, $message) {
    global $config;

    // Log customer message
    logWhatsAppActivity('CUSTOMER_MESSAGE', "From: {$phone}, Message: {$message}");

    // Send auto-response if outside business hours
    $current_hour = date('H');
    $business_hours = explode('-', $config['whatsapp']['auto_responses']['greeting_hours']);

    if ($current_hour < (int)$business_hours[0] || $current_hour > (int)$business_hours[1]) {
        $away_message = $config['whatsapp']['auto_responses']['away_message'];
        sendWhatsAppMessage($phone, $away_message);
    }

    // Forward message to admin
    $admin_phone = $config['whatsapp']['admin_phone'] ?? $config['whatsapp']['phone_number'];
    $forward_message = "📨 *New Customer Message*\n\n" .
                      "From: {$phone}\n" .
                      "Time: " . date('Y-m-d H:i:s') . "\n" .
                      "Message: {$message}\n\n" .
                      "Reply to this message to respond to the customer.";

    sendWhatsAppMessage($admin_phone, $forward_message);

    // Send acknowledgment to customer
    $welcome_message = $config['whatsapp']['auto_responses']['welcome_message'];
    sendWhatsAppMessage($phone, $welcome_message);
}

/**
 * Log WhatsApp webhook activity
 */
function logWhatsAppWebhook($data) {
    $log_file = '../logs/whatsapp_webhook.log';
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $data,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];

    file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Log WhatsApp activity
 */
function logWhatsAppActivity($type, $message) {
    $log_file = '../logs/whatsapp_activity.log';
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'message' => $message,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];

    file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
}
