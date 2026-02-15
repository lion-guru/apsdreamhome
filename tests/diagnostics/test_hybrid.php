<?php
/**
 * Test page for Hybrid Template System
 */

// Include the hybrid template system
require_once 'includes/hybrid_template_system.php';

// Test universal mode
$content = <<<HTML
<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center text-primary">Hybrid Template System Test</h1>
            <p class="lead text-center">This page demonstrates the hybrid template system in action.</p>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h3>Features Tested</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">✓ Universal Template Mode</li>
                        <li class="list-group-item">✓ Automatic Navigation Detection</li>
                        <li class="list-group-item">✓ SEO Meta Tags</li>
                        <li class="list-group-item">✓ CSS/JS Asset Management</li>
                        <li class="list-group-item">✓ Menu System Integration</li>
                    </ul>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="/" class="btn btn-primary me-2">Home</a>
                <a href="/about" class="btn btn-outline-primary me-2">About</a>
                <a href="/contact" class="btn btn-outline-secondary">Contact</a>
            </div>
        </div>
    </div>
</div>
HTML;

// Render the page using universal mode
hybrid_template('universal')
    ->setTitle('Hybrid Template Test - APS Dream Home')
    ->setDescription('Test page for the new hybrid template system combining universal and traditional templates')
    ->addCSS('https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css')
    ->addJS('https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js')
    ->renderPage($content);