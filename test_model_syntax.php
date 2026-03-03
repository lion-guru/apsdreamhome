<?php
/**
 * Test Model.php Syntax
 */

echo "Testing Model.php syntax...\n";

// Try to include the Model file
try {
    include_once 'app/Core/Database/Model.php';
    echo "✅ Model.php included successfully\n";
} catch (ParseError $e) {
    echo "❌ Parse Error: " . $e->getMessage() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}

echo "Test complete.\n";
?>
