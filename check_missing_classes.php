<?php
/**
 * Check for missing classes and dependencies
 */

echo "Checking for missing classes and dependencies...\n\n";

// Check required classes
$requiredClasses = [
    'App\Core\App',
    'App\Core\Contracts\Arrayable',
    'App\Core\Database\Relations\HasRelationships',
    'App\Core\Support\Str',
    'App\Core\Support\Collection'
];

echo "🔍 Checking required classes:\n";
foreach ($requiredClasses as $class) {
    if (class_exists($class) || trait_exists($class) || interface_exists($class)) {
        echo "✅ $class - Found\n";
    } else {
        echo "❌ $class - Missing\n";
    }
}

echo "\n🔍 Checking file structure:\n";

$baseDir = __DIR__ . '/app/Core';
$requiredFiles = [
    'App.php',
    'Contracts/Arrayable.php',
    'Database/Relations/HasRelationships.php',
    'Support/Str.php',
    'Support/Collection.php'
];

foreach ($requiredFiles as $file) {
    $filePath = $baseDir . '/' . $file;
    if (file_exists($filePath)) {
        echo "✅ $file - Exists\n";
    } else {
        echo "❌ $file - Missing\n";
    }
}

echo "\n🔍 Checking Model.php syntax:\n";

// Read Model.php content
$modelFile = __DIR__ . '/app/Core/Database/Model.php';
if (file_exists($modelFile)) {
    $content = file_get_contents($modelFile);
    
    // Check for common syntax issues
    if (strpos($content, 'use HasRelationships;') !== false) {
        echo "✅ HasRelationships trait usage found\n";
    } else {
        echo "❌ HasRelationships trait usage not found\n";
    }
    
    if (strpos($content, 'abstract class Model') !== false) {
        echo "✅ Model class declaration found\n";
    } else {
        echo "❌ Model class declaration not found\n";
    }
    
    if (strpos($content, 'implements \\ArrayAccess') !== false) {
        echo "✅ ArrayAccess implementation found\n";
    } else {
        echo "❌ ArrayAccess implementation not found\n";
    }
    
    // Check for missing closing braces
    $openBraces = substr_count($content, '{');
    $closeBraces = substr_count($content, '}');
    echo "📊 Brace count: Open=$openBraces, Close=$closeBraces\n";
    
    if ($openBraces === $closeBraces) {
        echo "✅ Braces are balanced\n";
    } else {
        echo "❌ Braces are not balanced\n";
    }
} else {
    echo "❌ Model.php file not found\n";
}

echo "\n🎯 Summary:\n";
echo "If any classes or files are missing, they need to be created.\n";
echo "If braces are not balanced, there's a syntax error.\n";
echo "If HasRelationships trait is missing, it needs to be created.\n";
?>
