<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';

// Syntax check
exec("php -l C:/xampp/htdocs/apsdreamhome/app/Http/Controllers/AssociateController.php 2>&1", $out);
echo "Syntax: " . implode(" ", $out) . "\n";

// Check for 'Coming Soon' dead ends in views
exec("grep -rn 'Coming Soon' C:/xampp/htdocs/apsdreamhome/app/views/associate/ 2>&1", $out);
echo "\nComing Soon in associate views:\n";
foreach ($out as $line) echo "  $line\n";
if (empty($out)) echo "  None found!\n";

// Check associate/book_plot.php loads correctly
$content = file_get_contents('C:/xampp/htdocs/apsdreamhome/app/views/associate/book_plot.php');
echo "\nbook_plot.php has render call: " . (strpos($content, 'render(') !== false ? 'YES' : 'NO') . "\n";
echo "book_plot.php has colonyFilter JS: " . (strpos($content, 'colonyFilter') !== false ? 'YES' : 'NO') . "\n";
echo "book_plot.php has terms_consent: " . (strpos($content, 'terms_consent') !== false ? 'YES' : 'NO') . "\n";
echo "book_plot.php has legal links: " . (strpos($content, '/terms-conditions') !== false ? 'YES' : 'NO') . "\n";
