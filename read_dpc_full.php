<?php
$file = file('C:\xampp\htdocs\apsdreamhome\mobile/apsdreamhome_app_v2/lib/presentation/widgets/ai/deal_probability_card.dart');
$lines = $file;
// Read lines 13-260 with line numbers
for ($i = 12; $i <= 260; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i];
    }
}