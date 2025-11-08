<?php
// Test CSS file accessibility
$cssFile = '/assets/css/style.css';
$fullPath = __DIR__ . $cssFile;
$webPath = 'http://' . $_SERVER['HTTP_HOST'] . '/apsdreamhome' . $cssFile;

// Check if file exists
$fileExists = file_exists($fullPath);
$isReadable = is_readable($fullPath);

// Try to get file contents
$fileContents = @file_get_contents($fullPath);
$fileAccessible = ($fileContents !== false);
?>
<!DOCTYPE html>
<html>
<head>
    <title>CSS File Test</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>CSS File Access Test</h1>
    
    <h2>File Information</h2>
    <p><strong>File Path:</strong> <?php echo htmlspecialchars($fullPath); ?></p>
    <p><strong>Web URL:</strong> <a href="<?php echo htmlspecialchars($webPath); ?>" target="_blank"><?php echo htmlspecialchars($webPath); ?></a></p>
    
    <h2>Access Test Results</h2>
    <p><strong>File Exists:</strong> 
        <?php echo $fileExists ? '<span class="success">✓ Yes</span>' : '<span class="error">✗ No</span>'; ?>
    </p>
    <p><strong>Is Readable:</strong> 
        <?php echo $isReadable ? '<span class="success">✓ Yes</span>' : '<span class="error">✗ No</span>'; ?>
    </p>
    <p><strong>Accessible via file_get_contents:</strong> 
        <?php echo $fileAccessible ? '<span class="success">✓ Yes</span>' : '<span class="error">✗ No - ' . error_get_last()['message'] . '</span>'; ?>
    </p>
    
    <?php if ($fileAccessible): ?>
    <h2>File Contents (First 20 lines):</h2>
    <pre><?php echo htmlspecialchars(implode("\n", array_slice(explode("\n", $fileContents), 0, 20))); ?>...</pre>
    <?php endif; ?>
    
    <h2>PHP Error Log</h2>
    <pre><?php echo htmlspecialchars(print_r(error_get_last(), true)); ?></pre>
    
    <h2>Server Information</h2>
    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
    <p><strong>Web Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></p>
    <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'N/A'; ?></p>
</body>
</html>
