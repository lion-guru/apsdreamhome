<?php
session_start();
include 'config.php';
require_role('Admin');
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Automated Documentation Generator</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Automated Documentation Generator</h2><form method='post'><div class='mb-3'><label>Documentation Type</label><select name='doc_type' class='form-control'><option value='user_guide'>User Guide</option><option value='api_docs'>API Docs</option><option value='faq'>FAQ</option></select></div><div class='mb-3'><label>Audience</label><select name='audience' class='form-control'><option value='customer'>Customer</option><option value='agent'>Agent</option><option value='admin'>Admin</option></select></div><button class='btn btn-success'>Generate Documentation</button></form><p class='mt-3'>*Automatically generates and downloads user guides, API documentation, or FAQs tailored to the selected audience.</p></div></body></html>
