<?php
$file = 'C:\xampp\htdocs\apsdreamhome\mobile/apsdreamhome_app_v2/lib/presentation/widgets/ai/deal_probability_card.dart';
$lines = file($file);
$newLines = [];
$removed = 0;
foreach ($lines as $line) {
    $trimmed = trim($line);
    if ($trimmed === '[],' || $trimmed === '[]') {
        $removed++;
        continue;
    }
    $newLines[] = $line;
}
file_put_contents($file, implode('', $newLines));
echo "Removed $removed empty list lines\n";