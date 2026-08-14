<?php
/**
 * Image Optimizer CLI
 * Run: php app/Tools/optimize_images.php --dir=public/uploads
 */
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Helpers/ImageOptimizer.php';

$dir = isset($argv[1]) ? str_replace('--dir=', '', $argv[1]) : 'public/uploads';
$results = ImageOptimizer::optimizeDirectory($dir, true);
print_r($results);
?>
