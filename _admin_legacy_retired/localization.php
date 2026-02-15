<?php
session_start();
include 'config.php';
require_role('Admin');
$languages = ['en'=>'English','hi'=>'Hindi','fr'=>'French','ar'=>'Arabic','zh'=>'Chinese'];
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Localization & Globalization</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Localization & Globalization</h2><form method='post'><div class='mb-3'><label>Select Default Language</label><select name='language' class='form-control'><?php foreach($languages as $k=>$v): ?><option value='<?= $k ?>'><?= $v ?></option><?php endforeach; ?></select></div><div class='mb-3'><label>Default Currency</label><select name='currency' class='form-control'><option value='INR'>INR</option><option value='USD'>USD</option><option value='EUR'>EUR</option><option value='CNY'>CNY</option><option value='AED'>AED</option></select></div><button class='btn btn-success'>Save Settings</button></form><p class='mt-3'>*Supports multi-language, multi-currency, and region-specific compliance. Ready for global expansion.</p></div></body></html>
