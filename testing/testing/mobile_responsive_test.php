<?php
/**
 * Mobile Responsive Design Test Script
 * Checks all breakpoints
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Responsive Test - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .device-frame {
            border: 2px solid #333;
            margin: 20px auto;
            background: #fff;
            overflow: hidden;
        }
        .device-label {
            text-align: center;
            font-weight: bold;
            padding: 10px;
            background: #f0f0f0;
        }
    </style>
</head>
<body class="bg-light p-4">
    <div class="container">
        <h1 class="mb-4">📱 Mobile Responsive Design Test</h1>
        
        <div class="alert alert-info">
            <strong>Test Pages:</strong> Admin Dashboard, Customer Views, Property Listings
        </div>

        <!-- Desktop -->
        <div class="mb-4">
            <div class="device-label">Desktop (1920px)</div>
            <div class="device-frame" style="width: 100%; max-width: 1920px; height: 600px;">
                <iframe src="/apsdreamhome/admin/dashboard" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>

        <!-- Tablet -->
        <div class="mb-4">
            <div class="device-label">Tablet (1024px)</div>
            <div class="device-frame" style="width: 1024px; height: 600px;">
                <iframe src="/apsdreamhome/admin/dashboard" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>

        <!-- Mobile -->
        <div class="mb-4">
            <div class="device-label">Mobile (375px)</div>
            <div class="device-frame" style="width: 375px; height: 600px;">
                <iframe src="/apsdreamhome/admin/dashboard" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>

        <div class="alert alert-success">
            <h5>✅ Responsive Checklist:</h5>
            <ul class="mb-0">
                <li>Sidebar collapses on mobile</li>
                <li>Tables scroll horizontally on small screens</li>
                <li>Buttons are touch-friendly (min 44px)</li>
                <li>Text is readable without zooming</li>
                <li>Images scale properly</li>
            </ul>
        </div>
    </div>
</body>
</html>
