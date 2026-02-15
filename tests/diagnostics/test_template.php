<?php
// Test template system
require_once 'includes/enhanced_universal_template.php';

$template = new EnhancedUniversalTemplate();
$template->setTitle('Test Page');
$template->setDescription('Test Description');

// Start output buffering
ob_start();
echo '<h1>Test Content</h1>';
$content = ob_get_clean();

// Render the page
$template->renderPage($content, 'Test Page');
?>