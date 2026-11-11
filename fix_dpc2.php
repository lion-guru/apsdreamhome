<?php
$file = 'C:\xampp\htdocs\apsdreamhome\mobile/apsdreamhome_app_v2/lib/presentation/widgets/ai/deal_probability_card.dart';
$lines = file($file);
$newLines = [];
foreach ($lines as $line) {
    $trimmed = trim($line);
    if ($trimmed === '[]' || $trimmed === '[],') {
        continue; // Skip standalone empty lists
    }
    $newLines[] = $line;
}
file_put_contents($file, implode('', $newLines));
echo "Removed " . (count($lines) - count($newLines)) . " empty list lines\n";