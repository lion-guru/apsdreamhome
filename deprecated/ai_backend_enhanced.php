<?php
/**
 * APS Dream Home - Enhanced AI Backend
 * Role-based AI Assistant with Lead Capture and File Processing
 */

header('Content-Type: application/json');

// Load configuration
require_once __DIR__ . '/config/gemini_config.php';
$config = require __DIR__ . '/config/gemini_config.php';

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$user_text = $data['message'] ?? '';
$user_role = $data['role'] ?? 'customer';
$context = $data['context'] ?? '';
$files = $data['files'] ?? [];

if (empty($user_text)) {
    echo json_encode(['error' => 'Sawal khali hai!']);
    exit;
}

// Check API key
if (empty($config['api_key']) || $config['api_key'] === 'YOUR_REAL_GEMINI_API_KEY_HERE') {
    echo json_encode([
        'error' => 'Gemini API key configure nahi kiya gaya. .env file mein real API key dalein.',
        'status' => 'not_configured'
    ]);
    exit;
}

// Role-based system prompts
$role_prompts = [
    'director' => [
        'persona' => 'You are a Strategic Business Director for APS Dream Home real estate company.',
        'expertise' => 'business strategy, revenue analysis, team management, project oversight, financial planning',
        'language' => 'Hindi and English mix',
        'focus' => 'strategic decisions, business growth, team performance, market analysis'
    ],
    'sales' => [
        'persona' => 'You are a Sales Executive for APS Dream Home.',
        'expertise' => 'property sales, customer management, lead generation, market trends',
        'language' => 'Hindi and English mix',
        'focus' => 'sales targets, customer conversion, property features, closing deals'
    ],
    'developer' => [
        'persona' => 'You are a Senior Developer for APS Dream Home.',
        'expertise' => 'web development, database management, API integration, system architecture',
        'language' => 'Technical English with Hindi explanations',
        'focus' => 'coding help, debugging, system optimization, technical solutions'
    ],
    'bugfixer' => [
        'persona' => 'You are a Quality Assurance and Bug Fixing Specialist.',
        'expertise' => 'error identification, testing procedures, debugging, quality assurance',
        'language' => 'Technical English with Hindi explanations',
        'focus' => 'bug resolution, testing strategies, code quality, system health'
    ],
    'ithead' => [
        'persona' => 'You are an IT Head/Systems Manager for APS Dream Home.',
        'expertise' => 'system administration, security, infrastructure, network management',
        'language' => 'Technical English with Hindi explanations',
        'focus' => 'system security, infrastructure management, technical leadership'
    ],
    'superadmin' => [
        'persona' => 'You are a Super Admin with full system access.',
        'expertise' => 'system configuration, user management, security oversight, database administration',
        'language' => 'Technical English with Hindi explanations',
        'focus' => 'system administration, security, user management, full system control'
    ],
    'customer' => [
        'persona' => 'You are a Customer Service Representative for APS Dream Home.',
        'expertise' => 'property information, customer support, sales guidance, local market knowledge',
        'language' => 'Friendly Hindi and English mix',
        'focus' => 'customer assistance, property information, purchase guidance'
    ]
];

// Get role configuration
$role_config = $role_prompts[$user_role] ?? $role_prompts['customer'];

// Build system instruction
$system_instruction = $role_config['persona'] . " " . 
    "Your expertise includes: " . $role_config['expertise'] . ". " .
    "Focus on: " . $role_config['focus'] . ". " .
    "Communicate in " . $role_config['language'] . ". " .
    "Always be professional, helpful, and provide actionable advice. " .
    "For APS Dream Home properties in Raghunath Nagri, Gorakhpur area. " .
    "If customer contact details are mentioned, flag them for lead capture.";

// Add file context if any
$file_context = '';
if (!empty($files)) {
    $file_context = "User has uploaded " . count($files) . " file(s): " . 
        implode(', ', array_column($files, 'name')) . ". ";
}

// Add role context
$role_context = $context ? "Current context: $context. " : '';

// Build complete prompt
$full_prompt = $file_context . $role_context . "User: $user_text";

// Prepare API request
$url = $config['api_url'] . '?key=' . $config['api_key'];

$payload = [
    "system_instruction" => [
        "parts" => [
            ["text" => $system_instruction]
        ]
    ],
    "contents" => [
        [
            "role" => "user",
            "parts" => [["text" => $full_prompt]]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 1024,
        "topP" => 0.8,
        "topK" => 40
    ]
];

// Make API call
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Process response
if ($response && $http_code === 200) {
    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $ai_reply = $result['candidates'][0]['content']['parts'][0]['text'];
        
        // Extract potential lead information
        $lead_data = extractLeadInfo($ai_reply, $user_text);
        
        echo json_encode([
            'reply' => $ai_reply,
            'status' => 'success',
            'role' => $user_role,
            'leadData' => $lead_data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'error' => 'AI se proper response nahi mila.',
            'status' => 'api_error',
            'debug' => $response
        ]);
    }
} else {
    echo json_encode([
        'error' => 'Google API connection failed. HTTP Code: ' . $http_code,
        'status' => 'connection_error',
        'http_code' => $http_code
    ]);
}

/**
 * Extract lead information from text
 */
function extractLeadInfo($ai_reply, $user_text) {
    $lead_data = [];
    
    // Combine both texts for better extraction
    $combined_text = strtolower($ai_reply . ' ' . $user_text);
    
    // Extract phone numbers (Indian format)
    if (preg_match('/(\+91[-\s]?|0)?[6-9]\d{9}/', $combined_text, $phone_matches)) {
        $lead_data['phone'] = preg_replace('/[^0-9]/', '', $phone_matches[0]);
    }
    
    // Extract email addresses
    if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $combined_text, $email_matches)) {
        $lead_data['email'] = $email_matches[0];
    }
    
    // Extract names (basic pattern)
    if (preg_match('/(?:mera naam|my name is|i am|mujhe|name)[\s:]+([a-zA-Z\s]+)/i', $combined_text, $name_matches)) {
        $lead_data['name'] = trim($name_matches[1]);
    }
    
    // Extract property interests
    $property_types = ['plot', 'villa', 'apartment', 'flat', 'commercial', 'residential', 'house'];
    foreach ($property_types as $type) {
        if (strpos($combined_text, $type) !== false) {
            $lead_data['property_interest'] = $type;
            break;
        }
    }
    
    // Extract budget information
    if (preg_match('/(?:budget|price|rate|cost)[\s:]+(?:₹|rs\.?|rupees?)\s*(\d+\s*(?:lakh|crore|thousand|lack))/i', $combined_text, $budget_matches)) {
        $lead_data['budget'] = $budget_matches[1];
    }
    
    // Extract locations
    $locations = ['raghunath nagri', 'gorakhpur', 'lucknow', 'kushinagar', 'deoria'];
    foreach ($locations as $location) {
        if (strpos($combined_text, $location) !== false) {
            $lead_data['location'] = $location;
            break;
        }
    }
    
    // Return lead data if we have meaningful information
    return !empty($lead_data) ? $lead_data : null;
}
?>
