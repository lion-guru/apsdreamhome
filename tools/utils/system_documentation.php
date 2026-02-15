<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Documentation - APS Dream Home</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }

        .nav {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .nav ul {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .nav a {
            color: #667eea;
            text-decoration: none;
            padding: 10px 20px;
            border: 2px solid #667eea;
            border-radius: 25px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .nav a:hover {
            background: #667eea;
            color: white;
        }

        .section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .section h2 {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .section h3 {
            color: #333;
            margin: 25px 0 15px 0;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .feature-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .feature-card h4 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }

        .api-endpoint {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #007bff;
        }

        .method {
            font-weight: bold;
            color: #007bff;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            margin: 5px;
        }

        .status-implemented {
            background: #d4edda;
            color: #155724;
        }

        .status-testing {
            background: #fff3cd;
            color: #856404;
        }

        .status-planned {
            background: #d1ecf1;
            color: #0c5460;
        }

        .security-level {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin: 5px;
        }

        .security-high {
            background: #d4edda;
            color: #155724;
        }

        .security-medium {
            background: #fff3cd;
            color: #856404;
        }

        .security-low {
            background: #f8d7da;
            color: #721c24;
        }

        .ai-capability {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }

        .performance-metric {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin: 5px 0;
        }

        .toc {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .toc ul {
            list-style: none;
        }

        .toc li {
            margin: 10px 0;
        }

        .toc a {
            color: #667eea;
            text-decoration: none;
            padding: 5px 0;
            display: block;
        }

        .toc a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .architecture-diagram {
            text-align: center;
            margin: 30px 0;
        }

        .architecture-diagram img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .quick-start {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin: 30px 0;
        }

        .quick-start h3 {
            margin-bottom: 15px;
        }

        .quick-start-code {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
        }

        .troubleshooting {
            background: #fff3cd;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid #ffc107;
        }

        .troubleshooting h4 {
            color: #856404;
            margin-top: 15px;
        }

        .faq {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px 0;
        }

        .faq-item {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }

        .faq-question {
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }

        .support-info {
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin: 30px 0;
        }

        .support-info h3 {
            margin-bottom: 15px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .contact-card {
            background: rgba(255,255,255,0.2);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .contact-card h4 {
            margin-bottom: 10px;
        }

        .footer {
            background: #2d3748;
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-top: 50px;
        }

        .footer p {
            margin: 10px 0;
        }

        @media (max-width: 768px) {
            .nav ul {
                flex-direction: column;
                align-items: center;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 System Documentation</h1>
            <p>Complete Guide to APS Dream Home Security & AI Implementation</p>
        </div>

        <div class="nav">
            <ul>
                <li><a href="#overview">Overview</a></li>
                <li><a href="#security">Security Features</a></li>
                <li><a href="#ai-system">AI System</a></li>
                <li><a href="#architecture">Architecture</a></li>
                <li><a href="#api-reference">API Reference</a></li>
                <li><a href="#testing">Testing</a></li>
                <li><a href="#deployment">Deployment</a></li>
                <li><a href="#troubleshooting">Troubleshooting</a></li>
                <li><a href="#faq">FAQ</a></li>
                <li><a href="#support">Support</a></li>
            </ul>
        </div>

        <div class="toc">
            <h3>📋 Table of Contents</h3>
            <ul>
                <li><a href="#overview">1. System Overview</a></li>
                <li><a href="#security-features">2. Security Features</a></li>
                <li><a href="#ai-capabilities">3. AI Capabilities</a></li>
                <li><a href="#architecture">4. System Architecture</a></li>
                <li><a href="#api-endpoints">5. API Endpoints</a></li>
                <li><a href="#testing-validation">6. Testing & Validation</a></li>
                <li><a href="#performance">7. Performance Optimization</a></li>
                <li><a href="#deployment-guide">8. Deployment Guide</a></li>
                <li><a href="#monitoring">9. Monitoring & Maintenance</a></li>
                <li><a href="#troubleshooting">10. Troubleshooting</a></li>
                <li><a href="#faq">11. FAQ</a></li>
                <li><a href="#support">12. Support Information</a></li>
            </ul>
        </div>

        <div class="section" id="overview">
            <h2>1. System Overview</h2>
            <p>The APS Dream Home application is a comprehensive real estate platform featuring advanced security measures and AI-powered capabilities. This documentation covers the complete implementation including security hardening, AI systems, and operational guidelines.</p>

            <h3>Key Features</h3>
            <div class="feature-grid">
                <div class="feature-card">
                    <h4>🔒 Enterprise Security</h4>
                    <p>Comprehensive security implementation with authentication, authorization, input validation, and protection against common web vulnerabilities.</p>
                </div>
                <div class="feature-card">
                    <h4>🤖 AI-Powered System</h4>
                    <p>Advanced AI chatbot, property recommendations, market analysis, and predictive analytics for enhanced user experience.</p>
                </div>
                <div class="feature-card">
                    <h4>📊 Real-time Analytics</h4>
                    <p>Comprehensive analytics and reporting system for monitoring system performance and user interactions.</p>
                </div>
                <div class="feature-card">
                    <h4>⚡ High Performance</h4>
                    <p>Optimized database queries, caching systems, and efficient code architecture for maximum performance.</p>
                </div>
            </div>

            <h3>System Status</h3>
            <div class="feature-grid">
                <div class="feature-card">
                    <h4>Security Level</h4>
                    <span class="security-level security-high">ENTERPRISE-GRADE</span>
                </div>
                <div class="feature-card">
                    <h4>AI Capabilities</h4>
                    <span class="status-badge status-implemented">FULLY IMPLEMENTED</span>
                </div>
                <div class="feature-card">
                    <h4>System Testing</h4>
                    <span class="status-badge status-testing">COMPREHENSIVE</span>
                </div>
                <div class="feature-card">
                    <h4>Documentation</h4>
                    <span class="status-badge status-implemented">COMPLETE</span>
                </div>
            </div>
        </div>

        <div class="section" id="security">
            <h2>2. Security Features</h2>

            <h3>Authentication & Authorization</h3>
            <div class="ai-capability">
                <h4>🔐 Multi-Layer Security Implementation</h4>
                <ul>
                    <li><strong>Password Security:</strong> Argon2ID hashing with secure configuration</li>
                    <li><strong>Session Management:</strong> Secure cookies with HTTPOnly and SameSite flags</li>
                    <li><strong>Rate Limiting:</strong> Progressive lockout system (5min → 1hr)</li>
                    <li><strong>CSRF Protection:</strong> Token-based request validation</li>
                    <li><strong>HTTPS Enforcement:</strong> Automatic HTTP to HTTPS redirection</li>
                </ul>
            </div>

            <h3>Input Validation & Sanitization</h3>
            <div class="code-block">
// Example of secure input handling
$username = sanitizeInput($_POST['username']);
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$password = password_hash($_POST['password'], PASSWORD_ARGON2ID);
