<?php
// Check what methods are called on AIService/AIChatbotService in controllers
$dir = 'C:/xampp/htdocs/apsdreamhome/app/Http/Controllers';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$methods = [];
foreach($it as $file) {
    if($file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    // Find method calls on chatbotService, aiService, etc.
    preg_match_all('/(?:chatbotService|aiService|gateway|ai)\s*->\s*(\w+)\s*\(/', $content, $m, PREG_SET_ORDER);
    foreach($m as $match) {
        $methods[$match[1]] = true;
    }
}

echo "=== Methods called on AI-related objects in controllers ===\n";
ksort($methods);
foreach(array_keys($methods) as $method) {
    echo "  - $method()\n";
}

// Check what methods AIService has
echo "\n=== Methods defined in AIService ===\n";
$aiService = file_get_contents('C:/xampp/htdocs/apsdreamhome/app/Services/AI/AIService.php');
preg_match_all('/public function (\w+)/', $aiService, $m, PREG_SET_ORDER);
foreach($m as $match) {
    echo "  - {$match[1]}()\n";
}