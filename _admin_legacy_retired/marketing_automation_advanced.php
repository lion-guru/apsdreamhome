<?php
session_start();
include 'config.php';
require_role('Admin');
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Hyper-Personalized Marketing Automation</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Hyper-Personalized Marketing Automation</h2><form method='post'><div class='mb-3'><label>Segment Users By</label><select name='segment_by' class='form-control'><option value='behavior'>Behavior</option><option value='purchase_history'>Purchase History</option><option value='location'>Location</option><option value='custom'>Custom</option></select></div><div class='mb-3'><label>Channel</label><select name='channel' class='form-control'><option value='email'>Email</option><option value='sms'>SMS</option><option value='whatsapp'>WhatsApp</option></select></div><div class='mb-3'><label>Campaign Message</label><textarea name='message' class='form-control'></textarea></div><button class='btn btn-success'>Send Campaign</button></form><p class='mt-3'>*AI-driven segmentation and campaign triggers. Integrates with user analytics and prediction modules.</p></div></body></html>
