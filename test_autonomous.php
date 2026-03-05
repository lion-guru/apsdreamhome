<?php
/**
 * Test file for autonomous system demonstration
 * This file contains security vulnerabilities that will be auto-fixed
 */

// This should be auto-fixed by the Sentinel system
$userInput = Security::sanitize($_POST['username']);  // Vulnerable to XSS
$email = Security::sanitize($_GET['email']);         // Vulnerable to XSS
$data = Security::sanitize($_REQUEST['data']);       // Vulnerable to XSS

echo "Welcome, $userInput!";     // Direct echo without sanitization

// SQL injection vulnerable query
$sql = "SELECT * FROM users WHERE username = '$userInput'";  // Should be fixed

// This should trigger auto-conversion if saved as .blade.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Autonomous System Test</title>
</head>
<body>
    <h1>Testing Autonomous Features</h1>
    
    <?php if ($userInput): ?>
        <p>Hello, <?= htmlspecialchars($userInput) ?>!</p>
    <?php endif; ?>
    
    <?php if ($email): ?>
        <p>Email: <?= htmlspecialchars($email) ?></p>
    <?php endif; ?>
    
    <?php if ($data): ?>
        <p>Data: <?= htmlspecialchars($data) ?></p>
    <?php endif; ?>
    
    <!-- This should be auto-converted if saved as .blade.php -->
    @if(isset($userInput))
        <p>Hello, {{ $userInput }}!</p>
    @endif
    
    @foreach($data as $item)
        <p>{{ $item }}</p>
    @endforeach
    
    <script>
        // This should trigger security fixes
        var userInput = '<?= $userInput ?>';
        console.log('User input:', userInput);
    </script>
</body>
</html>
