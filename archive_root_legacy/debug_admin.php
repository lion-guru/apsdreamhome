<?php
/**
 * APS Dream Home - Blank Page Debugger
 */

// Enable all error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "=== APS DREAM HOME - BLANK PAGE DEBUGGER ===\n\n";

define('INCLUDED_FROM_MAIN', true);

echo "Testing admin.php with full error reporting...\n\n";

try {
    // Clear any previous output
    ob_clean();

    // Simulate browser request
    $_SERVER['REQUEST_URI'] = '/apsdreamhome/admin.php';
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['REQUEST_METHOD'] = 'GET';

    // Clear session
    unset($_SESSION);

    echo "Loading admin.php...\n";

    // Include admin file
    require_once 'admin.php';

    $output = ob_get_contents();

    if (empty($output)) {
        echo "❌ BLANK OUTPUT - No content generated\n";
        echo "This could be due to:\n";
        echo "• Fatal error stopping execution\n";
        echo "• Output buffering issues\n";
        echo "• Headers already sent\n";
        echo "• Exit() called without output\n";
    } else {
        echo "✅ Content generated: " . strlen($output) . " characters\n";
        echo "First 500 characters:\n";
        echo substr($output, 0, 500) . "...\n";
    }

} catch (Exception $e) {
    echo "❌ CAUGHT EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "❌ CAUGHT FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n=== DETAILED DEBUG ===\n";
echo "Checking each component individually...\n";
?>
