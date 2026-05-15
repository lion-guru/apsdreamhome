<?php
/**
 * AI Monitoring Script
 * Monitors AI performance and usage
 */

// Check AI API status
function checkAIStatus() {
    $config_file = __DIR__ . "/config/gemini_config.php";
    if (file_exists($config_file)) {
        include $config_file;
        if (!empty($config["api_key"])) {
            echo "✅ AI Status: CONFIGURED\n";
        } else {
            echo "❌ AI Status: NOT CONFIGURED\n";
        }
    }
}

// Check AI backend
function checkAIBackend() {
    $backend_files = [
        "ai_backend.php",
        "ai_backend_enhanced.php", 
        "ai_backend_fixed.php"
    ];
    
    foreach ($backend_files as $file) {
        if (file_exists(__DIR__ . "/" . $file)) {
            echo "✅ Found: $file\n";
        }
    }
}

// Check AI frontend
function checkAIFrontend() {
    $frontend_files = [
        "ai_chat.html",
        "ai_chat_enhanced.html"
    ];
    
    foreach ($frontend_files as $file) {
        if (file_exists(__DIR__ . "/" . $file)) {
            echo "✅ Found: $file\n";
        }
    }
}

// Check AI routes
function checkAIRoutes() {
    $routes_file = __DIR__ . "/routes/web.php";
    if (file_exists($routes_file)) {
        $content = file_get_contents($routes_file);
        if (strpos($content, "ai-chat") !== false) {
            echo "✅ AI Routes: CONFIGURED\n";
        }
    }
}

echo "🤖 AI System Status Check:\n";
echo "========================\n";
checkAIStatus();
echo "\n";
checkAIBackend();
echo "\n";
checkAIFrontend();
echo "\n";
checkAIRoutes();
echo "\n";
echo "📊 AI Monitoring Active\n";
?>