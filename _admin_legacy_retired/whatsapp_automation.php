<?php
session_start();
include 'config.php';
require_role('Admin');
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>WhatsApp Automation</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>WhatsApp Automation</h2><p>This module is ready for integration with Twilio, Gupshup, or any WhatsApp Business API provider. Add your API key and sender number to automate notifications, campaigns, and two-way messaging.</p><form method='post'><div class='mb-3'><label>Provider</label><select class='form-control'><option>Twilio</option><option>Gupshup</option><option>Custom</option></select></div><div class='mb-3'><label>API Key</label><input class='form-control' type='text' name='// SECURITY: Sensitive information removed'></div><div class='mb-3'><label>Sender Number</label><input class='form-control' type='text' name='sender_number'></div><button class='btn btn-success'>Save Configuration</button></form></div></body></html>

