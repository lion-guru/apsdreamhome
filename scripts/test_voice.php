<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\Voice\VoiceCallService;

$service = new VoiceCallService();
echo "VoiceCallService loaded successfully\n";
echo "Provider: " . $service->getVoiceProvider()->getProvider() . "\n";
echo "Configured: " . ($service->getVoiceProvider()->isConfigured() ? 'Yes' : 'No') . "\n";