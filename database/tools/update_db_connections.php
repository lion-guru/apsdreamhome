<?php
/**
 * Project-wide Database Connection Update Script
 * Helps migrate existing database connections to the new centralized method
 */

function updateDatabaseConnections($directory) {
    $updatedFiles = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            // Check for old database connection patterns
            $patterns = [
                '/mysqli_connect\([^)]+\)/',
                '/new mysqli\([^)]+\)/',
                '/\$conn\s*=\s*[^;]+;/',
                '/require_once\s*\([\'"].*db_config\.php[\'"]\)/'
            ];

            $replacements = [
                'getDbConnection()',
                'getDbConnection()',
                '$conn = getDbConnection();',
                'require_once __DIR__ . "/../includes/db_connection.php";'
            ];

            $modified = false;
            foreach ($patterns as $index => $pattern) {
                $newContent = preg_replace($pattern, $replacements[$index], $content);
                if ($newContent !== $content) {
                    $content = $newContent;
                    $modified = true;
                }
            }

            // Add error handling
            if ($modified) {
                // Wrap database connection in try-catch
                if (strpos($content, 'try {') === false) {
                    $content = preg_replace(
                        '/(\$conn\s*=\s*getDbConnection\(\);)/',
                        "try {\n    $1\n} catch (Exception $e) {\n    handleDatabaseError($e);\n}",
                        $content
                    );
                }

                // Add include for db_connection
                if (strpos($content, 'db_connection.php') === false) {
                    $content = "<?php\nrequire_once __DIR__ . '/../includes/db_connection.php';\n" . $content;
                }

                file_put_contents($filePath, $content);
                $updatedFiles[] = $filePath;
            }
        }
    }

    return $updatedFiles;
}

// Run the update
$directory = __DIR__;
$updatedFiles = updateDatabaseConnections($directory);

echo "Database Connection Update Complete.\n";
echo "Updated Files:\n";
print_r($updatedFiles);
