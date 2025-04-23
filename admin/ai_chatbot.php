<?php
session_start();
include 'config.php';
require_role('Admin');
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>AI Chatbot Integration</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI Chatbot Integration</h2><p>This module is ready for integration with Dialogflow, OpenAI, or any chatbot provider. Add your API key and webhook URL to enable automated chat support for customers and leads (web or WhatsApp).</p><form method='post'><div class='mb-3'><label>Provider</label><select class='form-control'><option>Dialogflow</option><option>OpenAI</option><option>Custom</option></select></div><div class='mb-3'><label>API Key</label><input class='form-control' type='text' name='api_key'></div><div class='mb-3'><label>Webhook URL</label><input class='form-control' type='text' name='webhook_url'></div><button class='btn btn-success'>Save Configuration</button></form></div></body></html>
