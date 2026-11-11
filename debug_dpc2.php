<?php
$file = 'C:\xampp\htdocs\apsdreamhome\mobile/apsdreamhome_app_v2/lib/presentation/widgets/ai/deal_probability_card.dart';
$lines = file($file);
for ($i = 237; $i <= 258; $i++) {
    if (isset($lines[$i])) {
        $hex = bin2hex(trim($lines[$i]));
        echo ($i + 1) . ": hex=[" . $hex . "] text=[" . trim($lines[$i]) . "]\n";
    }
}