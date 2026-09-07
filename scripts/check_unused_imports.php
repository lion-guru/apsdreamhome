<?php
$dir = 'C:\xampp\htdocs\apsdreamhome\app\Http\Controllers';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$unusedImports = [];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        
        // Extract use statements
        if (preg_match_all('/^use\s+([^;]+);/m', $content, $matches)) {
            foreach ($matches[1] as $useStmt) {
                $useStmt = trim($useStmt);
                // Get the class name (last part after \)
                $parts = explode('\\', $useStmt);
                $className = end($parts);
                
                // Check if class is used in the file (not in use statements, not in comments)
                // Remove use statements and comments for checking
                $checkContent = preg_replace('/^use\s+[^;]+;/m', '', $content);
                $checkContent = preg_replace('/\/\/.*$/m', '', $checkContent);
                $checkContent = preg_replace('/\/\*.*?\*\//s', '', $checkContent);
                
                // Check if className is used (as type hint, instantiation, static call, etc.)
                $patterns = [
                    '/\b' . preg_quote($className, '/') . '\b/',  // class name
                    '/new\s+' . preg_quote($className, '/') . '\b/',  // new ClassName
                    '/\b' . preg_quote($className, '/') . '::/',  // ClassName::
                    '/:' . preg_quote($className, '/') . '\b/',  // type hint : ClassName
                    '/\?' . preg_quote($className, '/') . '\b/',  // nullable type hint
                    '/@var\s+' . preg_quote($className, '/') . '\b/',  // @var ClassName
                    '/@return\s+' . preg_quote($className, '/') . '\b/',  // @return ClassName
                ];
                
                $used = false;
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $checkContent)) {
                        $used = true;
                        break;
                    }
                }
                
                if (!$used) {
                    $relativePath = str_replace('C:\xampp\htdocs\apsdreamhome\\', '', $file->getPathname());
                    $unusedImports[] = "$relativePath: $useStmt (class: $className)";
                }
            }
        }
    }
}

echo "Unused imports found: " . count($unusedImports) . "\n\n";
foreach ($unusedImports as $imp) {
    echo $imp . "\n";
}