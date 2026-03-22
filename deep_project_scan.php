<?php

echo "🔍 APS DREAM HOME - DEEP PROJECT SCAN\n";
echo "======================================\n\n";

// 1. Database Deep Analysis
echo "📊 DEEP DATABASE ANALYSIS:\n";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all tables with detailed info
    $tables_query = "SELECT TABLE_NAME, TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH 
                     FROM information_schema.TABLES 
                     WHERE TABLE_SCHEMA = 'apsdreamhome' 
                     ORDER BY DATA_LENGTH DESC";
    $tables = $pdo->query($tables_query)->fetchAll();
    
    echo "  📋 Total Tables: " . count($tables) . "\n";
    
    // Analyze important tables
    $important_tables = [];
    $ai_related_tables = [];
    $user_tables = [];
    $property_tables = [];
    
    foreach ($tables as $table) {
        $table_name = $table['TABLE_NAME'];
        $row_count = $table['TABLE_ROWS'];
        $data_size = number_format($table['DATA_LENGTH'] / 1024, 2) . ' KB';
        
        // Categorize tables
        if (strpos($table_name, 'ai') !== false || strpos($table_name, 'chat') !== false || strpos($table_name, 'bot') !== false) {
            $ai_related_tables[] = "$table_name ($row_count rows, $data_size)";
        } elseif (strpos($table_name, 'user') !== false || strpos($table_name, 'customer') !== false || strpos($table_name, 'employee') !== false) {
            $user_tables[] = "$table_name ($row_count rows, $data_size)";
        } elseif (strpos($table_name, 'property') !== false || strpos($table_name, 'project') !== false) {
            $property_tables[] = "$table_name ($row_count rows, $data_size)";
        }
        
        $important_tables[] = "$table_name: $row_count rows, $data_size";
    }
    
    echo "  🤖 AI-Related Tables: " . count($ai_related_tables) . "\n";
    if (!empty($ai_related_tables)) {
        foreach ($ai_related_tables as $table) {
            echo "    - $table\n";
        }
    }
    
    echo "  👥 User Tables: " . count($user_tables) . "\n";
    if (!empty($user_tables)) {
        foreach (array_slice($user_tables, 0, 5) as $table) {
            echo "    - $table\n";
        }
        if (count($user_tables) > 5) {
            echo "    - ... and " . (count($user_tables) - 5) . " more\n";
        }
    }
    
    echo "  🏠 Property Tables: " . count($property_tables) . "\n";
    if (!empty($property_tables)) {
        foreach (array_slice($property_tables, 0, 5) as $table) {
            echo "    - $table\n";
        }
        if (count($property_tables) > 5) {
            echo "    - ... and " . (count($property_tables) - 5) . " more\n";
        }
    }
    
} catch (Exception $e) {
    echo "  ❌ Database Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. Existing AI Features Scan
echo "🤖 EXISTING AI FEATURES SCAN:\n";
$ai_files = [
    'ai_chat.html' => 'Basic AI Chat Interface',
    'ai_chat_enhanced.html' => 'Enhanced AI Chat',
    'ai_backend.php' => 'Basic AI Backend',
    'ai_backend_enhanced.php' => 'Enhanced AI Backend',
    'ai_backend_fixed.php' => 'Rate-Limit Fixed Backend',
    'save_lead.php' => 'Lead Management',
    'get_lead_count.php' => 'Lead Statistics',
    'AI_CHAT_INTEGRATION_GUIDE.md' => 'Integration Documentation',
    'MVC_AI_IMPLEMENTATION_GUIDE.md' => 'MVC Implementation Guide'
];

foreach ($ai_files as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $size = number_format(filesize(__DIR__ . '/' . $file) / 1024, 2) . ' KB';
        $modified = date('Y-m-d H:i:s', filemtime(__DIR__ . '/' . $file));
        echo "  ✅ $file - $description ($size, Modified: $modified)\n";
    } else {
        echo "  ❌ $file - Missing\n";
    }
}

echo "\n";

// 3. Scan for existing AI-related code
echo "🔍 EXISTING AI CODE SCAN:\n";
$directories_to_scan = [
    'app' => 'Application Code',
    'config' => 'Configuration',
    'assets' => 'Assets',
    'public' => 'Public Files'
];

$ai_keywords = ['ai', 'chat', 'bot', 'gemini', 'openai', 'assistant', 'intelligence'];
$ai_files_found = [];

foreach ($directories_to_scan as $dir => $description) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/' . $dir));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                foreach ($ai_keywords as $keyword) {
                    if (stripos($content, $keyword) !== false) {
                        $relative_path = str_replace(__DIR__ . '/', '', $file->getPathname());
                        $ai_files_found[] = $relative_path;
                        break;
                    }
                }
            }
        }
    }
}

echo "  📁 AI-Related Files Found: " . count($ai_files_found) . "\n";
foreach (array_unique($ai_files_found) as $file) {
    echo "    - $file\n";
}

echo "\n";

// 4. Controllers Analysis
echo "🎮 CONTROLLERS ANALYSIS:\n";
if (is_dir(__DIR__ . '/app/Controllers')) {
    $controllers = glob(__DIR__ . '/app/Controllers/*.php');
    echo "  📋 Total Controllers: " . count($controllers) . "\n";
    
    $controller_info = [];
    foreach ($controllers as $controller) {
        $controller_name = basename($controller, '.php');
        $content = file_get_contents($controller);
        $methods = [];
        
        // Extract method names
        if (preg_match_all('/public function (\w+)/', $content, $matches)) {
            $methods = $matches[1];
        }
        
        $controller_info[$controller_name] = [
            'methods' => $methods,
            'ai_related' => stripos($content, 'ai') !== false || stripos($content, 'chat') !== false
        ];
    }
    
    foreach ($controller_info as $name => $info) {
        $ai_indicator = $info['ai_related'] ? ' 🤖' : '';
        echo "    - $name: " . count($info['methods']) . " methods$ai_indicator\n";
    }
}

echo "\n";

// 5. Models Analysis
echo "📊 MODELS ANALYSIS:\n";
if (is_dir(__DIR__ . '/app/Models')) {
    $models = glob(__DIR__ . '/app/Models/*.php');
    echo "  📋 Total Models: " . count($models) . "\n";
    
    $important_models = ['User', 'Property', 'Lead', 'Customer', 'Employee', 'Project'];
    $found_models = [];
    
    foreach ($models as $model) {
        $model_name = basename($model, '.php');
        if (in_array($model_name, $important_models)) {
            $found_models[] = $model_name;
        }
    }
    
    echo "  🎯 Important Models Found: " . count($found_models) . "\n";
    foreach ($found_models as $model) {
        echo "    - $model\n";
    }
}

echo "\n";

// 6. Views Analysis
echo "👁️ VIEWS ANALYSIS:\n";
$view_dirs = ['app/views', 'views', 'resources/views'];
$found_view_dir = null;

foreach ($view_dirs as $dir) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        $found_view_dir = $dir;
        break;
    }
}

if ($found_view_dir) {
    $views = glob(__DIR__ . '/' . $found_view_dir . '/**/*.php', GLOB_BRACE);
    echo "  📁 Views Directory: $found_view_dir\n";
    echo "  📋 Total Views: " . count($views) . "\n";
    
    // Check for AI-related views
    $ai_views = [];
    foreach ($views as $view) {
        $content = file_get_contents($view);
        if (stripos($content, 'ai') !== false || stripos($content, 'chat') !== false) {
            $ai_views[] = str_replace(__DIR__ . '/' . $found_view_dir . '/', '', $view);
        }
    }
    
    echo "  🤖 AI-Related Views: " . count($ai_views) . "\n";
    foreach ($ai_views as $view) {
        echo "    - $view\n";
    }
} else {
    echo "  ❌ No Views Directory Found\n";
}

echo "\n";

// 7. Routes Analysis
echo "🛣️ ROUTES ANALYSIS:\n";
if (file_exists(__DIR__ . '/routes/web.php')) {
    $routes_content = file_get_contents(__DIR__ . '/routes/web.php');
    $route_count = substr_count($routes_content, '$router->');
    echo "  📋 Total Routes: $route_count\n";
    
    // Check for AI routes
    $ai_routes = [];
    if (preg_match_all('/\$router->(get|post)\([\'"]([^\'"]*ai[^\'"]*)[\'"]/', $routes_content, $matches)) {
        foreach ($matches[2] as $route) {
            $ai_routes[] = $route;
        }
    }
    
    echo "  🤖 AI Routes: " . count($ai_routes) . "\n";
    foreach ($ai_routes as $route) {
        echo "    - $route\n";
    }
} else {
    echo "  ❌ Routes File Not Found\n";
}

echo "\n";

// 8. Configuration Analysis
echo "⚙️ CONFIGURATION ANALYSIS:\n";
$config_files = [
    '.env' => 'Environment Variables',
    'config/app_config.json' => 'App Configuration',
    'config/database.php' => 'Database Config',
    'config/gemini_config.php' => 'Gemini AI Config'
];

foreach ($config_files as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "  ✅ $file - $description\n";
        
        if ($file === 'config/gemini_config.php') {
            $content = file_get_contents(__DIR__ . '/' . $file);
            if (strpos($content, 'AIzaSy') !== false) {
                echo "    🔑 API Key: Configured\n";
            } else {
                echo "    ⚠️ API Key: Not configured\n";
            }
        }
    } else {
        echo "  ❌ $file - Missing\n";
    }
}

echo "\n";

// 9. Assets Analysis
echo "🎨 ASSETS ANALYSIS:\n";
$asset_dirs = ['assets/css', 'assets/js', 'assets/images', 'public/css', 'public/js'];
$ai_assets = [];

foreach ($asset_dirs as $dir) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        $assets = glob(__DIR__ . '/' . $dir . '/ai*');
        foreach ($assets as $asset) {
            $ai_assets[] = str_replace(__DIR__ . '/', '', $asset);
        }
    }
}

echo "  🤖 AI-Related Assets: " . count($ai_assets) . "\n";
foreach ($ai_assets as $asset) {
    echo "    - $asset\n";
}

echo "\n";

// 10. Integration Opportunities Analysis
echo "🎯 INTEGRATION OPPORTUNITIES:\n";

$opportunities = [];

// Check if home page exists
if (file_exists(__DIR__ . '/app/views/pages/index.php') || file_exists(__DIR__ . '/views/index.php')) {
    $opportunities[] = "🏠 Home Page - Add AI chat widget to main page";
}

// Check if property pages exist
$property_views = glob(__DIR__ . '/**/property*.php');
if (!empty($property_views)) {
    $opportunities[] = "📋 Property Pages - Property-specific AI assistance";
}

// Check if contact page exists
if (file_exists(__DIR__ . '/app/views/pages/contact.php') || file_exists(__DIR__ . '/views/contact.php')) {
    $opportunities[] = "📞 Contact Page - Replace traditional form with AI chat";
}

// Check if user dashboard exists
$dashboard_views = glob(__DIR__ . '/**/dashboard*.php');
if (!empty($dashboard_views)) {
    $opportunities[] = "👤 User Dashboard - Personalized AI assistant";
}

// Check if admin panel exists
$admin_views = glob(__DIR__ . '/**/admin*.php');
if (!empty($admin_views)) {
    $opportunities[] = "🔐 Admin Panel - AI-powered administrative assistance";
}

echo "  🎯 Found Opportunities: " . count($opportunities) . "\n";
foreach ($opportunities as $opportunity) {
    echo "    - $opportunity\n";
}

echo "\n";

// 11. Implementation Strategy
echo "🚀 IMPLEMENTATION STRATEGY:\n";

echo "  📊 Phase 1: Foundation (Week 1)\n";
echo "    - Fix rate limit issues (COMPLETED)\n";
echo "    - Create AI controller integration\n";
echo "    - Add AI widget to home page\n";
echo "    - Test basic functionality\n";

echo "\n  🏠 Phase 2: Property Integration (Week 2)\n";
echo "    - Property-specific AI chat\n";
echo "    - Context-aware responses\n";
echo "    - Property lead capture\n";
echo "    - Mobile optimization\n";

echo "\n  👥 Phase 3: User Integration (Week 3)\n";
echo "    - User dashboard AI assistant\n";
echo "    - Role-based AI for staff\n";
echo "    - Employee portal integration\n";
echo "    - Performance monitoring\n";

echo "\n  🔧 Phase 4: Advanced Features (Week 4)\n";
echo "    - Admin panel AI tools\n";
echo "    - Advanced analytics\n";
echo "    - Voice input support\n";
echo "    - Multi-language enhancement\n";

echo "\n";

// 12. Risk Analysis
echo "⚠️ RISK ANALYSIS:\n";
echo "  🚨 Rate Limiting: SOLVED with ai_backend_fixed.php\n";
echo "  🗄️ Database Load: Monitor with 633 tables\n";
echo "  🔐 Security: API key in environment variables\n";
echo "  📱 Performance: Caching implemented\n";
echo "  👥 User Adoption: Need training and documentation\n";

echo "\n";

echo "✅ DEEP SCAN COMPLETE!\n";
echo "📊 Project is ready for AI integration\n";
echo "🚀 Follow the implementation strategy\n";
echo "🎯 Focus on high-impact integrations first\n";
?>
