<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';
require_once 'app/Services/Localization/LocalizationService.php';
require_once 'app/Models/User.php';

echo "Testing LocalizationService...\n";

$doc = new \App\Services\Localization\LocalizationService(null, null, 'en_US');
echo "Created successfully!\n";