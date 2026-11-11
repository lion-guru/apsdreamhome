<?php
$file = file('C:\xampp\htdocs\apsdreamhome\mobile/apsdreamhome_app_v2/lib/presentation/widgets/ai/deal_probability_card.dart');
$total = count($file);
echo "Total lines: $total\n";
for ($i = max(0, $total - 20); $i < $total; $i++) {
    echo ($i + 1) . ": " . $file[$i];
}