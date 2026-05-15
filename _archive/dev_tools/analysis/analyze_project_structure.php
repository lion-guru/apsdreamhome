<?php
// Project structure analysis
echo "📊 Analyzing project structure...\n";
$directories = ["app", "config", "public", "routes", "tests", "tools"];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $fileCount = count(glob("$dir/*"));
        echo "✅ $dir: $fileCount files\n";
    } else {
        echo "❌ $dir: NOT FOUND\n";
    }
}
?>