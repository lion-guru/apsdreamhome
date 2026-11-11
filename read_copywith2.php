<?php
$file = file('C:\xampp\htdocs\apsdreamhome\mobile/apsdreamhome_app_v2/lib/data/models/lead_model_extended.dart');
for ($i = 120; $i <= 170; $i++) {
    if (isset($file[$i])) {
        echo ($i + 1) . ": " . $file[$i];
    }
}