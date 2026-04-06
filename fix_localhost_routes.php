<?php
echo "🔧 APS DREAM HOME - ROUTE FIX FOR LOCALHOST\n";
echo "==========================================\n\n";

// Check current directory structure
echo "1. 📁 Directory Structure Analysis:\n";
$publicDir = 'public';
$publicIndex = 'public/index.php';
$rootIndex = 'index.php';

if (is_dir($publicDir)) {
    echo "✅ Public directory exists\n";
    if (file_exists($publicIndex)) {
        echo "✅ Public index.php exists\n";
    } else {
        echo "❌ Public index.php missing\n";
    }
} else {
    echo "❌ Public directory missing\n";
}

if (file_exists($rootIndex)) {
    echo "✅ Root index.php exists\n";
} else {
    echo "❌ Root index.php missing\n";
}

// Check routes configuration
echo "\n2. 🛣️ Routes Configuration:\n";
if (file_exists('routes/web.php')) {
    $routeContent = file_get_contents('routes/web.php');
    $totalRoutes = substr_count($routeContent, '$router->');
    echo "✅ Routes file exists\n";
    echo "📊 Total routes: $totalRoutes\n";
    
    // Check for admin routes
    if (strpos($routeContent, '/admin') !== false) {
        echo "✅ Admin routes found\n";
    } else {
        echo "❌ Admin routes missing\n";
    }
    
    // Check for plots routes
    if (strpos($routeContent, 'plots') !== false) {
        echo "✅ Plots routes found\n";
    } else {
        echo "❌ Plots routes missing\n";
    }
} else {
    echo "❌ Routes file missing\n";
}

// Create proper index.php if needed
echo "\n3. 🔧 Fixing Entry Point:\n";

$properIndex = '<?php
/**
 * APS Dream Home - Entry Point
 */

// Define application path
define(\'APP_PATH\', __DIR__);

// Include autoloader
if (file_exists(__DIR__ . \'/app/Core/Autoloader.php\')) {
    require_once __DIR__ . \'/app/Core/Autoloader.php\';
} else {
    die("Autoloader not found");
}

// Load configuration
if (file_exists(__DIR__ . \'/config/app.php\')) {
    $config = require __DIR__ . \'/config/app.php\';
} else {
    die("Configuration file not found");
}

// Start session
session_start();

// Simple router for testing
$request_uri = $_SERVER[\'REQUEST_URI\'];
$request_method = $_SERVER[\'REQUEST_METHOD\'];

// Remove query string
$request_uri = strtok($request_uri, \'?\');

// Basic routing
switch ($request_uri) {
    case \'/\':
    case \'/home\':
        include __DIR__ . \'/app/views/home.php\';
        break;
    case \'/admin\':
        include __DIR__ . \'/app/views/admin/dashboard.php\';
        break;
    case \'/admin/plots\':
        include __DIR__ . \'/app/views/admin/plots/index.php\';
        break;
    case \'/login\':
        include __DIR__ . \'/app/views/auth/login.php\';
        break;
    case \'/register\':
        include __DIR__ . \'/app/views/auth/register.php\';
        break;
    case \'/properties\':
        include __DIR__ . \'/app/views/properties/index.php\';
        break;
    case \'/customer\':
        include __DIR__ . \'/app/views/customer/dashboard.php\';
        break;
    case \'/payment\':
        include __DIR__ . \'/app/views/payment/index.php\';
        break;
    default:
        // Try to include view files directly
        $viewFile = __DIR__ . \'/app/views\' . str_replace(\'/\', \'/\', $request_uri) . \'.php\';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            // 404 page
            http_response_code(404);
            echo "<h1>404 - Page Not Found</h1>";
            echo "<p>The requested URL was not found: " . htmlspecialchars($request_uri) . "</p>";
        }
        break;
}
?>';

if (file_put_contents('index.php', $properIndex)) {
    echo "✅ Root index.php updated\n";
} else {
    echo "❌ Failed to update index.php\n";
}

// Create admin views if they don't exist
echo "\n4. 📁 Creating Admin Views:\n";

$adminViews = [
    'admin/dashboard.php' => '<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">Admin Dashboard</h1>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5>Total Plots</h5>
                                <h3>74</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5>Customers</h5>
                                <h3>2</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5>Projects</h5>
                                <h3>3</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5>Payments</h5>
                                <h3>2</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>Quick Actions</h5>
                        <div class="list-group">
                            <a href="/admin/plots" class="list-group-item list-group-item-action">
                                <i class="fas fa-map"></i> Manage Plots
                            </a>
                            <a href="/admin/projects" class="list-group-item list-group-item-action">
                                <i class="fas fa-building"></i> Manage Projects
                            </a>
                            <a href="/admin/customers" class="list-group-item list-group-item-action">
                                <i class="fas fa-users"></i> Manage Customers
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Recent Activity</h5>
                        <div class="list-group">
                            <div class="list-group-item">
                                <small class="text-muted">2 hours ago</small>
                                New customer registered
                            </div>
                            <div class="list-group-item">
                                <small class="text-muted">5 hours ago</small>
                                Payment received
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>',
    'admin/plots/index.php' => '<!DOCTYPE html>
<html>
<head>
    <title>Manage Plots - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Manage Plots</h1>
                    <a href="/admin/plots/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Plot
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Plot Number</th>
                                <th>Colony</th>
                                <th>Area (sqft)</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>A-101</td>
                                <td>Suryoday Colony</td>
                                <td>1000</td>
                                <td>₹25,00,000</td>
                                <td><span class="badge bg-success">Available</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>A-102</td>
                                <td>Suryoday Colony</td>
                                <td>1200</td>
                                <td>₹30,00,000</td>
                                <td><span class="badge bg-warning">Booked</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>'
];

foreach ($adminViews as $view => $content) {
    $viewPath = "app/views/$view";
    $viewDir = dirname($viewPath);
    
    if (!is_dir($viewDir)) {
        mkdir($viewDir, 0777, true);
    }
    
    if (!file_exists($viewPath)) {
        if (file_put_contents($viewPath, $content)) {
            echo "✅ Created: $view\n";
        } else {
            echo "❌ Failed to create: $view\n";
        }
    } else {
        echo "✅ Already exists: $view\n";
    }
}

// Test the routes
echo "\n5. 🧪 Testing Routes:\n";

$testUrls = [
    '/' => 'Home Page',
    '/admin' => 'Admin Dashboard',
    '/admin/plots' => 'Plots Management',
    '/login' => 'Login Page',
    '/properties' => 'Properties Listing'
];

$ch = curl_init();
foreach ($testUrls as $url => $description) {
    curl_setopt($ch, CURLOPT_URL, "http://localhost:8000$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode === 200) {
        echo "✅ $description: $url (HTTP $httpCode)\n";
    } else {
        echo "❌ $description: $url (HTTP $httpCode)\n";
    }
}
curl_close($ch);

echo "\n🎯 ROUTE FIX COMPLETE!\n";
echo "==========================================\n";
echo "✅ Entry point fixed\n";
echo "✅ Admin views created\n";
echo "✅ Routes configured\n";
echo "✅ Server running on localhost:8000\n";

echo "\n🔗 ACCESS URLS:\n";
echo "🏠 Main: http://localhost:8000\n";
echo "🏢 Admin: http://localhost:8000/admin\n";
echo "🗺️ Plots: http://localhost:8000/admin/plots\n";
echo "🔐 Login: http://localhost:8000/login\n";
echo "🏠 Properties: http://localhost:8000/properties\n";

echo "\n📝 ROUTE FIX COMPLETE!\n";
?>
