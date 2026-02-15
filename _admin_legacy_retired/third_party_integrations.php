<?php
session_start();
include 'config.php';
require_role('Admin');
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Third-Party Integrations</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Third-Party Integrations</h2><ul><li>CRM/ERP (Zoho, Salesforce) - API sync ready</li><li>WhatsApp/SMS Gateway - Configure provider and keys</li><li>Cloud Backup (Google Drive/Dropbox) - Upload/download backup files</li></ul><form method='post'><div class='mb-3'><label>Integration Type</label><select class='form-control'><option>CRM/ERP</option><option>WhatsApp/SMS</option><option>Cloud Backup</option></select></div><div class='mb-3'><label>API Key/Token</label><input class='form-control' type='text' name='api_token'></div><button class='btn btn-success'>Save Integration</button></form></div></body></html>
