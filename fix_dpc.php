<?php
$file = 'C:\xampp\htdocs\apsdreamhome\mobile/apsdreamhome_app_v2/lib/presentation/widgets/ai/deal_probability_card.dart';
$content = file_get_contents($file);
// Remove standalone empty list lines that are not spread operators
$content = preg_replace("/^  \[\],?\n/m", "", $content);
file_put_contents($file, $content);
echo "Done\n";