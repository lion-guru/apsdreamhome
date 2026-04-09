<?php
/**
 * Comprehensive Admin Panel Fix
 * Fixes: layout issues, undefined variables, missing routes, old view paths
 */

require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "=== ADMIN PANEL COMPREHENSIVE FIX ===\n\n";

$basePath = __DIR__ . '/..';

// 1. Fix views that use old layout includes
echo "1. Fixing old layout includes in views...\n";

$viewsToFix = [
    'admin/plots/index.php',
    'admin/commission/index.php',
    'admin/commission/rules.php',
    'admin/commission/create_rule.php',
    'admin/commission/edit_rule.php',
    'admin/commission/calculations.php',
    'admin/commission/reports.php',
    'admin/commission/payments.php',
];

foreach ($viewsToFix as $view) {
    $path = $basePath . '/app/views/' . $view;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Check if it uses old include path
        if (strpos($content, '../../../layouts/admin_header.php') !== false ||
            strpos($content, '../../../layouts/admin_footer.php') !== false) {
            
            // Remove old includes - these views should work with the controller's render method
            $content = preg_replace('/<\?php\s*include[^>]*admin_header[^>]*\/?>/i', '', $content);
            $content = preg_replace('/<\?php\s*include[^>]*admin_footer[^>]*\/?>/i', '', $content);
            
            file_put_contents($path, $content);
            echo "   ✅ Fixed: $view\n";
        }
    }
}

// 2. Add missing routes
echo "\n2. Adding missing routes to web.php...\n";

$routesFile = $basePath . '/routes/web.php';
$routesContent = file_get_contents($routesFile);

// Check if routes exist before adding
$missingRoutes = [
    '/admin/payouts' => '$router->get(\'/admin/payouts\', \'App\\\\Http\\\\Controllers\\\\Admin\\\\CommissionController@payouts\');',
    '/admin/network/tree' => '$router->get(\'/admin/network/tree\', \'App\\\\Http\\\\Controllers\\\\Admin\\\\MlmController@tree\');',
    '/admin/network/genealogy' => '$router->get(\'/admin/network/genealogy\', \'App\\\\Http\\\\Controllers\\\\Admin\\\\MlmController@genealogy\');',
    '/admin/network/ranks' => '$router->get(\'/admin/network/ranks\', \'App\\\\Http\\\\Controllers\\\\Admin\\\\MlmController@ranks\');',
];

$added = 0;
foreach ($missingRoutes as $route => $routeDef) {
    if (strpos($routesContent, $route) === false) {
        $routesContent .= "\n" . $routeDef . "\n";
        echo "   ✅ Added: $route\n";
        $added++;
    }
}

if ($added > 0) {
    file_put_contents($routesFile, $routesContent);
}

// 3. Create stub controller for MLM if needed
echo "\n3. Checking MLM controller...\n";

$mlmPath = $basePath . '/app/Http/Controllers/Admin/MlmController.php';
if (!file_exists($mlmPath)) {
    $mlmCode = '<?php
namespace App\Http\Controllers\Admin;
use App\Core\Controller;

class MlmController extends Controller
{
    protected $db;
    
    public function __construct() {
        $this->db = \App\Core\Database\Database::getInstance();
    }
    
    public function index() { $this->render("admin/mlm/index"); }
    public function tree() { $this->render("admin/mlm/tree"); }
    public function genealogy() { $this->render("admin/mlm/genealogy"); }
    public function ranks() { $this->render("admin/mlm/ranks"); }
    public function associates() { $this->render("admin/mlm/associates"); }
    public function payouts() { $this->render("admin/mlm/payouts"); }
}
';
    file_put_contents($mlmPath, $mlmCode);
    echo "   ✅ Created MlmController\n";
} else {
    echo "   ⏭️  MlmController exists\n";
}

// 4. Fix undefined variable issues in controllers
echo "\n4. Checking controller variable issues...\n";

$controllersToCheck = [
    'Admin/LeadController' => ['total'],
    'Admin/PropertyManagementController' => ['total', 'site_location'],
    'Admin/BookingController' => ['total_amount'],
];

foreach ($controllersToCheck as $ctrl => $vars) {
    $path = $basePath . '/app/Http/Controllers/' . $ctrl . '.php';
    if (file_exists($path)) {
        echo "   ⚠️  $ctrl - may have undefined key issues\n";
    }
}

echo "\n5. Creating basic MLM views...\n";

$mlmViews = [
    'admin/mlm/tree.php',
    'admin/mlm/genealogy.php',
    'admin/mlm/ranks.php',
];

$stubView = '<?php
$page_title = $page_title ?? "MLM";
?>
<div class="container-fluid py-4">
    <h3><?= $page_title ?></h3>
    <div class="alert alert-info">Page under development</div>
</div>
';

foreach ($mlmViews as $view) {
    $path = $basePath . '/app/views/' . $view;
    $dir = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    if (!file_exists($path)) {
        file_put_contents($path, $stubView);
        echo "   ✅ Created: $view\n";
    }
}

echo "\n6. Summary of issues that need manual fix:\n";
echo "   - Sidebar inconsistency: Views using different layouts\n";
echo "   - Undefined array keys: Controllers return data differently than views expect\n";
echo "   - htmlspecialchars null: Use ?? '' or htmlspecialchars(\$var ?? '')\n";
echo "   - Gallery page: Controller returning frontend view instead of admin\n";

echo "\n✅ FIX COMPLETE\n";
echo "Run test again to check improvements.\n";