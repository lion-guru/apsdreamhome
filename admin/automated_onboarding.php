<?php
session_start();
include 'config.php';
require_role('Admin');
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Automated Onboarding</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Automated User Onboarding</h2><form method='post'><div class='mb-3'><label>User Email</label><input type='email' name='email' class='form-control'></div><div class='mb-3'><label>Role</label><select name='role' class='form-control'><option>Customer</option><option>Agent</option><option>Admin</option></select></div><button class='btn btn-success'>Send Onboarding Invite</button></form><p class='mt-3'>*Automatically sends onboarding email with secure registration link, role assignment, and welcome documentation.</p></div></body></html>
