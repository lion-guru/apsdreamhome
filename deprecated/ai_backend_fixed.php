<?php
/**
 * APS Dream Home - Fixed AI Backend with Rate Limit Handling
 * Solves HTTP 429 errors with caching and request throttling
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
        'error' => 'Gemini API key configure nahi kiya gaya.',
        'status' => 'not_configured'
    ]);
    exit;
}

// Create cache key based on user input and role
$cache_key = md5($user_text . $user_role . $context);
$cache_file = __DIR__ . '/storage/cache/ai_cache_' . $cache_key . '.json';
$cache_dir = __DIR__ . '/storage/cache';

// Ensure cache directory exists
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

// Check cache first (5-minute cache)
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 300) {
    $cached_response = json_decode(file_get_contents($cache_file), true);
    echo json_encode([
        'reply' => $cached_response['reply'],
        'status' => 'success',
        'cached' => true,
        'role' => $user_role,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Rate limiting - check last request time
$rate_limit_file = __DIR__ . '/storage/cache/last_request.txt';
$last_request_time = 0;
if (file_exists($rate_limit_file)) {
    $last_request_time = (int)file_get_contents($rate_limit_file);
}

// Wait if last request was too recent (minimum 2 seconds between requests)
$time_since_last = time() - $last_request_time;
if ($time_since_last < 2) {
    $sleep_time = 2 - $time_since_last;
    usleep($sleep_time * 1000000); // Convert to microseconds
}

// Update last request time
file_put_contents($rate_limit_file, time());

// Role-based system prompts
$role_prompts = [
    'director' => [
        'persona' => 'You are a Strategic Business Director for APS Dream Home real estate company.',
        'expertise' => 'business strategy, revenue analysis, team management, project oversight',
        'language' => 'Professional Hindi and English mix',
        'focus' => 'strategic decisions, business growth, team performance'
    ],
    'sales' => [
        'persona' => 'You are a Sales Executive for APS Dream Home.',
        'expertise' => 'property sales, customer management, lead generation',
        'language' => 'Friendly Hindi and English mix',
        'focus' => 'sales targets, customer conversion, property features'
    ],
    'developer' => [
        'persona' => 'You are a Senior Developer for APS Dream Home.',
        'expertise' => 'web development, database management, PHP, MySQL',
        'language' => 'Technical English with Hindi explanations',
        'focus' => 'coding help, debugging, system optimization'
    ],
    'bugfixer' => [
        'persona' => 'You are a Quality Assurance and Bug Fixing Specialist.',
        'expertise' => 'error identification, testing procedures, debugging',
        'language' => 'Technical English with Hindi explanations',
        'focus' => 'bug resolution, testing strategies, code quality'
    ],
    'ithead' => [
        'persona' => 'You are an IT Head/Systems Manager for APS Dream Home.',
        'expertise' => 'system administration, security, infrastructure',
        'language' => 'Technical English with Hindi explanations',
        'focus' => 'system security, infrastructure management'
    ],
    'superadmin' => [
        'persona' => 'You are a Super Admin with full system access.',
        'expertise' => 'system configuration, user management, security',
        'language' => 'Technical English with Hindi explanations',
        'focus' => 'system administration, security, user management'
    ],
    'customer' => [
        'persona' => 'You are a Customer Service Representative for APS Dream Home.',
        'expertise' => 'property information, customer support, local market knowledge',
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
    "Be professional, helpful, and provide actionable advice. " .
    "For APS Dream Home properties in Raghunath Nagri, Gorakhpur area. " .
    "Keep responses concise but comprehensive. " .
    "If customer contact details are mentioned, flag them for lead capture.";

// Add context
$context_text = $context ? "Current context: $context. " : '';

// Build complete prompt
$full_prompt = $context_text . "User: $user_text";

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
        "maxOutputTokens" => 800, // Reduced to avoid rate limits
        "topP" => 0.8,
        "topK" => 40
    ]
];

// Make API call with retry logic
$max_retries = 3;
$retry_count = 0;
$response = null;
$http_code = 0;

while ($retry_count < $max_retries && ($http_code !== 200)) {
    if ($retry_count > 0) {
        // Exponential backoff
        $delay = pow(2, $retry_count) * 1000000; // 2^retry_count seconds in microseconds
        usleep($delay);
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20); // Reduced timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    $retry_count++;
}

// Process response
if ($response && $http_code === 200) {
    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $ai_reply = $result['candidates'][0]['content']['parts'][0]['text'];
        
        // Extract potential lead information
        $lead_data = extractLeadInfo($ai_reply, $user_text);
        
        // Cache the response
        $cache_data = [
            'reply' => $ai_reply,
            'lead_data' => $lead_data,
            'timestamp' => time()
        ];
        file_put_contents($cache_file, json_encode($cache_data));
        
        echo json_encode([
            'reply' => $ai_reply,
            'status' => 'success',
            'role' => $user_role,
            'leadData' => $lead_data,
            'cached' => false,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'error' => 'AI se proper response nahi mila.',
            'status' => 'api_error',
            'debug' => substr($response, 0, 500)
        ]);
    }
} elseif ($http_code === 429) {
    // Rate limit hit - return cached response or fallback
    $fallback_response = getCachedFallback($user_text, $user_role);
    echo json_encode([
        'reply' => $fallback_response,
        'status' => 'rate_limited_fallback',
        'message' => 'API rate limit reached. Using cached response.',
        'retry_after' => 60,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} elseif ($http_code === 403) {
    echo json_encode([
        'error' => 'API key invalid or disabled. Check your Gemini API key.',
        'status' => 'api_key_error',
        'http_code' => $http_code
    ]);
} else {
    echo json_encode([
        'error' => 'Google API connection failed. HTTP Code: ' . $http_code,
        'status' => 'connection_error',
        'http_code' => $http_code,
        'curl_error' => $curl_error ?? '',
        'retry_count' => $retry_count
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
    
    // Extract names
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
    
    return !empty($lead_data) ? $lead_data : null;
}

/**
 * Get cached fallback response for rate limit situations
 */
function getCachedFallback($user_text, $user_role) {
    $fallbacks = [
        'customer' => [
            'default' => "🙏 Namaste! Main APS Dream Home ki AI hoon. Currently high traffic ke wajah se slow response aa rahi hai. Kripa thodi der baad try karein ya humse contact karein: +91-9277121112. Aapka property requirement hume WhatsApp par bhej sakte hain.",
            'property' => "🏠 APS Dream Home mein aapka swagat hai! Gorakhpur mein premium properties available hain. Rate limit ke wajah se main abhi detailed response nahi de sakti. Please call: +91-9277121112 ya visit karein: apsdreamhome.com",
            'price' => "💰 Price information ke liye humse directly contact karein. Rate limit ke wajah se main abhi detailed pricing nahi de sakti. Call: +91-9277121112 for latest prices.",
        ],
        'sales' => [
            'default' => "💼 Sales team member! Due to high API traffic, using fallback response. Please check our CRM for latest leads or call customer: +91-9277121112. System will recover shortly.",
            'lead' => "🎯 Lead information temporarily unavailable. Please check customer database or call directly. API rate limit active - will recover in few minutes.",
        ],
        'developer' => [
            'default' => "👨‍💻 Developer! API rate limit active. For technical help: check documentation at /docs or contact IT team. System recovering shortly.",
            'bug' => "🐛 Bug fixing help temporarily limited. Please check error logs and existing tickets. API will recover soon.",
        ]
    ];
    
    $role_fallbacks = $fallbacks[$user_role] ?? $fallbacks['customer'];
    
    // Check for specific keywords
    foreach ($role_fallbacks as $keyword => $response) {
        if ($keyword !== 'default' && strpos(strtolower($user_text), $keyword) !== false) {
            return $response;
        }
    }
    
    return $role_fallbacks['default'];
}
?>
