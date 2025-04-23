<?php
session_start();
include 'config.php';
require_role('Admin');
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Voice Assistant Integration</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Voice Assistant Integration</h2><p>This module is ready for integration with Alexa, Google Assistant, or custom voice services. Add your API key to enable voice commands for property search, booking, and support.</p><form method='post'><div class='mb-3'><label>Provider</label><select class='form-control'><option>Alexa</option><option>Google Assistant</option><option>Custom</option></select></div><div class='mb-3'><label>API Key</label><input class='form-control' type='text' name='api_key'></div><button class='btn btn-success'>Save Configuration</button></form></div></body></html>
