<?php
$file = file('C:\xampp\htdocs\apsdreamhome\mobile/apsdreamhome_app_v2/lib/presentation/widgets/ai/deal_probability_card.dart');
for ($i = 240; $i <= 265; $i++) {
    if (isset($file[$i])) {
        echo ($i + 1) . ": [" . trim($file[$i]) . "]\n";
    }
}