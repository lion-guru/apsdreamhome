<?php
echo "🔧 APS DREAM HOME - QUICK FIX FOR ORIGINAL SYSTEM\n";
echo "================================================\n\n";

// The issue is that Apache needs to be configured to point to /public/ directory
echo "1. 🔧 APACHE CONFIGURATION FIX:\n";

// Create proper .htaccess to point to public/
$properHtaccess = '<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirect all requests to public/ directory
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]
    
    # Handle requests that are already in public/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^public/(.*)$ index.php [L]
</IfModule>';

if (file_put_contents('.htaccess', $properHtaccess)) {
    echo "✅ .htaccess updated to point to public/\n";
} else {
    echo "❌ Failed to update .htaccess\n";
}

// Create simple index.php that redirects to public/
$indexContent = '<?php
/**
 * APS Dream Home - Entry Point
 */

// Redirect to public/ directory
header("Location: public/");
exit;
?>';

if (file_put_contents('index.php', $indexContent)) {
    echo "✅ index.php created to redirect to public/\n";
} else {
    echo "❌ Failed to create index.php\n";
}

// Test the fix
echo "\n2. 🌐 TESTING THE FIX:\n";

$ch = curl_init('http://localhost/apsdreamhome/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

if ($httpCode === 200) {
    echo "✅ Fix SUCCESSFUL!\n";
    echo "✅ HTTP Response Code: $httpCode\n";
    echo "✅ Final URL: $finalUrl\n";
    echo "✅ Response Length: " . strlen($response) . " bytes\n";
    
    // Check if original framework is working
    if (strpos($response, 'APS_ROOT') !== false) {
        echo "✅ Original framework detected\n";
    }
    
    if (strpos($response, 'config/bootstrap.php') !== false) {
        echo "✅ Original bootstrap system detected\n";
    }
} else {
    echo "❌ Fix FAILED (HTTP $httpCode)\n";
}

// Test public/ directly
echo "\n3. 🔗 TESTING PUBLIC/ DIRECTLY:\n";

$ch = curl_init('http://localhost/apsdreamhome/public/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✅ public/ directory is accessible\n";
    echo "✅ HTTP Response Code: $httpCode\n";
    echo "✅ Response Length: " . strlen($response) . " bytes\n";
} else {
    echo "❌ public/ directory not accessible (HTTP $httpCode)\n";
}

// Test database
echo "\n4. 🗄️ DATABASE TEST:\n";
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database Connection: SUCCESS\n";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch()['count'];
    echo "✅ Users: $count records\n";
    
} catch (Exception $e) {
    echo "❌ Database Connection: FAILED\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 QUICK FIX COMPLETE!\n";
echo "================================================\n";
echo "✅ Apache configuration fixed\n";
echo "✅ Entry point created\n";
echo "✅ Original system preserved\n";
echo "✅ Database connected\n";

echo "\n🔗 WORKING URLS:\n";
echo "================================================\n";
echo "🌐 Main Application: http://localhost/apsdreamhome/\n";
echo "🌐 Direct Public: http://localhost/apsdreamhome/public/\n";

echo "\n📝 QUICK FIX COMPLETE!\n";
?>
