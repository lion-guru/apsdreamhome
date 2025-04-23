<?php
session_start();
include 'config.php';
require_role('Admin');
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Big Data Export</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Big Data Analytics & Export</h2><form method='post'><div class='mb-3'><label>Export Data</label><select name='data_type' class='form-control'><option value='bookings'>Bookings</option><option value='customers'>Customers</option><option value='leads'>Leads</option><option value='all'>All Data</option></select></div><div class='mb-3'><label>Export To</label><select name='export_to' class='form-control'><option value='csv'>CSV</option><option value='s3'>AWS S3</option><option value='bigquery'>Google BigQuery</option></select></div><button class='btn btn-success'>Export</button></form><p class='mt-3'>*Export your data for advanced analytics, BI, and reporting. Integrates with cloud data lakes.</p></div></body></html>
