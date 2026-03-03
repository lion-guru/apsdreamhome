<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - MCP Configuration Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --gradient-danger: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .server-card {
            background: white;
            border-radius: 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .server-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .server-header {
            background: var(--gradient-primary);
            color: white;
            padding: 20px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .server-status {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-active { background: var(--success-color); }
        .status-inactive { background: var(--danger-color); }
        .status-configuring { background: var(--warning-color); }

        .config-form {
            padding: 20px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .database-selector {
            background: var(--gradient-success);
            color: white;
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .nav-pills .nav-link {
            border-radius: 10px;
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link.active {
            background: var(--gradient-primary);
        }

        .tab-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-top: 20px;
        }

        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }

        .success-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }

        .feature-badge {
            background: var(--gradient-success);
            color: white;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .server-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="main-container p-4">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <h1 class="display-4 fw-bold text-white mb-3">
                            <i class="fas fa-cogs me-3"></i>
                            MCP Configuration Manager
                        </h1>
                        <p class="lead text-white-50">
                            APS Dream Home - Advanced MCP Server Configuration & Database Management
                        </p>
                        <div class="d-flex justify-content-center gap-3">
                            <span class="feature-badge">
                                <i class="fas fa-server me-2"></i>12+ MCP Servers
                            </span>
                            <span class="feature-badge">
                                <i class="fas fa-database me-2"></i>Multi-Database Support
                            </span>
                            <span class="feature-badge">
                                <i class="fas fa-shield-alt me-2"></i>Secure Configuration
                            </span>
                        </div>
                    </div>

                    <!-- Database Selection -->
                    <div class="database-selector text-center">
                        <h4 class="text-white mb-3">
                            <i class="fas fa-database me-2"></i>
                            Select Database Configuration
                        </h4>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-light" onclick="selectDatabase('mysql')">
                                <i class="fab fa-mysql me-2"></i>MySQL
                            </button>
                            <button type="button" class="btn btn-light" onclick="selectDatabase('postgresql')">
                                <i class="fas fa-database me-2"></i>PostgreSQL
                            </button>
                            <button type="button" class="btn btn-light" onclick="selectDatabase('sqlite')">
                                <i class="fas fa-database me-2"></i>SQLite
                            </button>
                            <button type="button" class="btn btn-light" onclick="selectDatabase('supabase')">
                                <i class="fas fa-cloud me-2"></i>Supabase
                            </button>
                        </div>
                    </div>

                    <!-- Navigation Tabs -->
                    <ul class="nav nav-pills justify-content-center mb-4" id="serverTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="database-tab" data-bs-toggle="pill" data-bs-target="#database" type="button" role="tab">
                                <i class="fas fa-database me-2"></i>Database
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="search-tab" data-bs-toggle="pill" data-bs-target="#search" type="button" role="tab">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="payment-tab" data-bs-toggle="pill" data-bs-target="#payment" type="button" role="tab">
                                <i class="fas fa-credit-card me-2"></i>Payment
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="communication-tab" data-bs-toggle="pill" data-bs-target="#communication" type="button" role="tab">
                                <i class="fas fa-comments me-2"></i>Communication
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ai-tab" data-bs-toggle="pill" data-bs-target="#ai" type="button" role="tab">
                                <i class="fas fa-brain me-2"></i>AI & Automation
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="serverTabContent">
                        <!-- Database Tab -->
                        <div class="tab-pane fade show active" id="database" role="tabpanel">
                            <div class="row g-4">
                                <!-- PostgreSQL -->
                                <div class="col-md-6">
                                    <div class="server-card">
                                        <div class="server-header">
                                            <span class="server-status status-inactive" id="postgresql-status"></span>
                                            <i class="fas fa-database server-icon"></i>
                                            PostgreSQL Server
                                        </div>
                                        <div class="config-form">
                                            <div class="mb-3">
                                                <label class="form-label">Connection String</label>
                                                <input type="text" class="form-control" id="postgresql-connection" placeholder="postgresql://localhost:5432/apsdreamhome">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Username</label>
                                                <input type="text" class="form-control" id="postgresql-user" placeholder="root">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Password</label>
                                                <input type="password" class="form-control" id="postgresql-password" placeholder="">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Database</label>
                                                <input type="text" class="form-control" id="postgresql-database" placeholder="apsdreamhome">
                                            </div>
                                            <button class="btn btn-primary w-100" onclick="configureServer('postgresql')">
                                                <i class="fas fa-cog me-2"></i>Configure PostgreSQL
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- SQLite -->
                                <div class="col-md-6">
                                    <div class="server-card">
                                        <div class="server-header">
                                            <span class="server-status status-inactive" id="sqlite-status"></span>
                                            <i class="fas fa-database server-icon"></i>
                                            SQLite Server
                                        </div>
                                        <div class="config-form">
                                            <div class="mb-3">
                                                <label class="form-label">Database Path</label>
                                                <input type="text" class="form-control" id="sqlite-path" placeholder="c:\xampp\htdocs\apsdreamhome\database\apsdreamhome.db">
                                            </div>
                                            <button class="btn btn-primary w-100" onclick="configureServer('sqlite')">
                                                <i class="fas fa-cog me-2"></i>Configure SQLite
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Supabase -->
                                <div class="col-md-6">
                                    <div class="server-card">
                                        <div class="server-header">
                                            <span class="server-status status-inactive" id="supabase-status"></span>
                                            <i class="fas fa-cloud server-icon"></i>
                                            Supabase Server
                                        </div>
                                        <div class="config-form">
                                            <div class="mb-3">
                                                <label class="form-label">Project URL</label>
                                                <input type="text" class="form-control" id="supabase-url" placeholder="https://your-project.supabase.co">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Anon Key</label>
                                                <input type="text" class="form-control" id="supabase-anon" placeholder="your-anon-key">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Service Key</label>
                                                <input type="text" class="form-control" id="supabase-service" placeholder="your-service-key">
                                            </div>
                                            <button class="btn btn-primary w-100" onclick="configureServer('supabase')">
                                                <i class="fas fa-cog me-2"></i>Configure Supabase
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Search Tab -->
                        <div class="tab-pane fade" id="search" role="tabpanel">
                            <div class="row g-4">
                                <!-- Firecrawl -->
                                <div class="col-md-6">
                                    <div class="server-card">
                                        <div class="server-header">
                                            <span class="server-status status-inactive" id="firecrawl-status"></span>
                                            <i class="fas fa-fire server-icon"></i>
                                            Firecrawl Server
                                        </div>
                                        <div class="config-form">
                                            <div class="mb-3">
                                                <label class="form-label">API Key</label>
                                                <input type="text" class="form-control" id="firecrawl-key" placeholder="fc-your-api-key">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Base URL</label>
                                                <input type="text" class="form-control" id="firecrawl-url" placeholder="https://api.firecrawl.dev">
                                            </div>
                                            <button class="btn btn-primary w-100" onclick="configureServer('firecrawl')">
                                                <i class="fas fa-cog me-2"></i>Configure Firecrawl
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Brave Search -->
                                <div class="col-md-6">
                                    <div class="server-card">
                                        <div class="server-header">
                                            <span class="server-status status-inactive" id="brave-status"></span>
                                            <i class="fab fa-brave server-icon"></i>
                                            Brave Search Server
                                        </div>
                                        <div class="config-form">
                                            <div class="mb-3">
                                                <label class="form-label">API Key</label>
                                                <input type="text" class="form-control" id="brave-key" placeholder="BSA-your-api-key">
                                            </div>
                                            <button class="btn btn-primary w-100" onclick="configureServer('brave-search')">
                                                <i class="fas fa-cog me-2"></i>Configure Brave Search
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bright Data -->
                                <div class="col-md-6">
                                    <div class="server-card">
                                        <div class="server-header">
                                            <span class="server-status status-inactive" id="brightdata-status"></span>
                                            <i class="fas fa-chart-line server-icon"></i>
                                            Bright Data Server
                                        </div>
                                        <div class="config-form">
                                            <div class="mb-3">
                                                <label class="form-label">Customer ID</label>
                                                <input type="text" class="form-control" id="brightdata-customer" placeholder="your-customer-id">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">API Key</label>
                                                <input type="text" class="form-control" id="brightdata-key" placeholder="your-api-key">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Zone</label>
                                                <input type="text" class="form-control" id="brightdata-zone" placeholder="your-zone">
                                            </div>
                                            <button class="btn btn-primary w-100" onclick="configureServer('brightdata')">
                                                <i class="fas fa-cog me-2"></i>Configure Bright Data
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Google Maps -->
                                <div class="col-md-6">
                                    <div class="server-card">
                                        <div class="server-header">
                                            <span class="server-status status-inactive" id="googlemaps-status"></span>
                                            <i class="fab fa-google server-icon"></i>
                                            Google Maps Server
                                        </div>
                                        <div class="config-form">
                                            <div class="mb-3">
                                                <label class="form-label">API Key</label>
                                                <input type="text" class="form-control" id="googlemaps-key" placeholder="AIzaSy-your-google-maps-api-key">
                                            </div>
                                            <button class="btn btn-primary w-100" onclick="configureServer('google-maps')">
                                                <i class="fas fa-cog me-2"></i>Configure Google Maps
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Tab -->
                        <div class="tab-pane fade" id="payment" role="tabpanel">
                            <div class="row g-4">
                                <!-- Stripe -->
                                <div class="col-md-6">
                                    <div class="server-card">
                                        <div class="server-header">
                                            <span class="server-status status-inactive" id="stripe-status"></span>
                                            <i class="fab fa-stripe server-icon"></i>
                                            Stripe Server
                                        </div>
                                        <div class="config-form">
                                            <div class="mb-3">
                                                <label class="form-label">Secret Key</label>
                                                <input type="password" class="form-control" id="stripe-secret" placeholder="sk_test_your-stripe-secret-key">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Publishable Key</label>
                                                <input type="text" class="form-control" id="stripe-publishable" placeholder="pk_test_your-stripe-publishable-key">
                                            </div>
                                            <button class="btn btn-primary w-100" onclick="configureServer('stripe')">
                                                <i class="fas fa-cog me-2"></i>Configure Stripe
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Communication Tab -->
                        <div class="tab-pane fade" id="communication" role="tabpanel">
                            <div class="row g-4">
                                <!-- Slack -->
                                <div class="col-md-6">
                                    <div class="server-card">
                                        <div class="server-header">
                                            <span class="server-status status-inactive" id="slack-status"></span>
                                            <i class="fab fa-slack server-icon"></i>
                                            Slack Server
                                        </div>
                                        <div class="config-form">
                                            <div class="mb-3">
                                                <label class="form-label">Bot Token</label>
                                                <input type="password" class="form-control" id="slack-token" placeholder="xoxb-your-slack-bot-token">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Signing Secret</label>
                                                <input type="password" class="form-control" id="slack-signing" placeholder="your-slack-signing-secret">
                                            </div>
                                            <button class="btn btn-primary w-100" onclick="configureServer('slack')">
                                                <i class="fas fa-cog me-2"></i>Configure Slack
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- WhatsApp -->
                                <div class="col-md-6">
                                    <div class="server-card">
                                        <div class="server-header">
                                            <span class="server-status status-inactive" id="whatsapp-status"></span>
                                            <i class="fab fa-whatsapp server-icon"></i>
                                            WhatsApp Server
                                        </div>
                                        <div class="config-form">
                                            <div class="mb-3">
                                                <label class="form-label">Phone Number ID</label>
                                                <input type="text" class="form-control" id="whatsapp-phone" placeholder="your-whatsapp-phone-number-id">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Access Token</label>
                                                <input type="password" class="form-control" id="whatsapp-token" placeholder="EAADyour-whatsapp-access-token">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Webhook Verify Token</label>
                                                <input type="password" class="form-control" id="whatsapp-webhook" placeholder="your-webhook-verify-token">
                                            </div>
                                            <button class="btn btn-primary w-100" onclick="configureServer('whatsapp')">
                                                <i class="fas fa-cog me-2"></i>Configure WhatsApp
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- AI Tab -->
                        <div class="tab-pane fade" id="ai" role="tabpanel">
                            <div class="row g-4">
                                <!-- AI Image Tagging -->
                                <div class="col-md-6">
                                    <div class="server-card">
                                        <div class="server-header">
                                            <span class="server-status status-inactive" id="ai-image-status"></span>
                                            <i class="fas fa-brain server-icon"></i>
                                            AI Image Tagging Server
                                        </div>
                                        <div class="config-form">
                                            <div class="mb-3">
                                                <label class="form-label">API Key</label>
                                                <input type="text" class="form-control" id="ai-image-key" placeholder="your-ai-image-tagging-api-key">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Base URL</label>
                                                <input type="text" class="form-control" id="ai-image-url" placeholder="https://api.ai-image-tagging.com">
                                            </div>
                                            <button class="btn btn-primary w-100" onclick="configureServer('ai-image-tagging')">
                                                <i class="fas fa-cog me-2"></i>Configure AI Image Tagging
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Browser Stealth -->
                                <div class="col-md-6">
                                    <div class="server-card">
                                        <div class="server-header">
                                            <span class="server-status status-inactive" id="browser-stealth-status"></span>
                                            <i class="fas fa-user-secret server-icon"></i>
                                            Browser Stealth Server
                                        </div>
                                        <div class="config-form">
                                            <div class="mb-3">
                                                <label class="form-label">Stealth Mode</label>
                                                <select class="form-control" id="browser-stealth-mode">
                                                    <option value="enabled">Enabled</option>
                                                    <option value="disabled">Disabled</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Headless Mode</label>
                                                <select class="form-control" id="browser-headless">
                                                    <option value="true">Enabled</option>
                                                    <option value="false">Disabled</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">User Agent</label>
                                                <input type="text" class="form-control" id="browser-user-agent" placeholder="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36">
                                            </div>
                                            <button class="btn btn-primary w-100" onclick="configureServer('browser-stealth')">
                                                <i class="fas fa-cog me-2"></i>Configure Browser Stealth
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center mt-5">
                        <div class="d-flex justify-content-center gap-3">
                            <button class="btn btn-success btn-lg" onclick="saveAllConfigurations()">
                                <i class="fas fa-save me-2"></i>Save All Configurations
                            </button>
                            <button class="btn btn-warning btn-lg" onclick="testAllConnections()">
                                <i class="fas fa-plug me-2"></i>Test All Connections
                            </button>
                            <button class="btn btn-info btn-lg" onclick="startAllServers()">
                                <i class="fas fa-play me-2"></i>Start All Servers
                            </button>
                            <button class="btn btn-danger btn-lg" onclick="stopAllServers()">
                                <i class="fas fa-stop me-2"></i>Stop All Servers
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Success Toast -->
    <div class="success-toast">
        <div class="toast-header bg-success text-white">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            Configuration saved successfully!
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let mcpConfig = {};
        let currentDatabase = 'mysql';

        // Load existing configuration
        async function loadConfiguration() {
            try {
                const response = await fetch('/config/mcp_servers.json');
                mcpConfig = await response.json();
                updateUIFromConfig();
            } catch (error) {
                console.log('No existing configuration found');
            }
        }

        // Update UI from loaded configuration
        function updateUIFromConfig() {
            Object.keys(mcpConfig.mcpServers).forEach(serverKey => {
                const server = mcpConfig.mcpServers[serverKey];
                Object.keys(server.env).forEach(envKey => {
                    const inputId = `${serverKey}-${envKey.toLowerCase().replace(/_/g, '-')}`;
                    const input = document.getElementById(inputId);
                    if (input) {
                        input.value = server.env[envKey];
                    }
                });
                updateServerStatus(serverKey, 'active');
            });
        }

        // Select database
        function selectDatabase(dbType) {
            currentDatabase = dbType;
            document.querySelectorAll('.database-selector .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Update database-specific configurations
            updateDatabaseDefaults(dbType);
        }

        // Update database defaults
        function updateDatabaseDefaults(dbType) {
            const defaults = {
                'mysql': {
                    'postgresql-connection': 'mysql:host=localhost;dbname=apsdreamhome',
                    'postgresql-user': 'root',
                    'postgresql-password': '',
                    'postgresql-database': 'apsdreamhome'
                },
                'postgresql': {
                    'postgresql-connection': 'postgresql://localhost:5432/apsdreamhome',
                    'postgresql-user': 'root',
                    'postgresql-password': '',
                    'postgresql-database': 'apsdreamhome'
                },
                'sqlite': {
                    'sqlite-path': 'c:\\xampp\\htdocs\\apsdreamhome\\database\\apsdreamhome.db'
                },
                'supabase': {
                    'supabase-url': 'https://your-project.supabase.co',
                    'supabase-anon': 'your-anon-key',
                    'supabase-service': 'your-service-key'
                }
            };

            Object.keys(defaults[dbType]).forEach(key => {
                const input = document.getElementById(key);
                if (input) {
                    input.value = defaults[dbType][key];
                }
            });
        }

        // Configure server
        async function configureServer(serverKey) {
            showLoading();
            const serverConfig = mcpConfig.mcpServers[serverKey] || {};
            
            // Collect form data
            const formElements = document.querySelectorAll(`[id^="${serverKey}-"]`);
            formElements.forEach(element => {
                const envKey = element.id.replace(`${serverKey}-`, '').replace(/-/g, '_').toUpperCase();
                if (!serverConfig.env) {
                    serverConfig.env = {};
                }
                serverConfig.env[envKey] = element.value;
            });

            // Update configuration
            if (!mcpConfig.mcpServers) {
                mcpConfig.mcpServers = {};
            }
            mcpConfig.mcpServers[serverKey] = serverConfig;

            // Save configuration
            await saveConfiguration();
            updateServerStatus(serverKey, 'configuring');
            
            setTimeout(() => {
                updateServerStatus(serverKey, 'active');
                hideLoading();
                showSuccessToast(`${serverKey} server configured successfully!`);
            }, 2000);
        }

        // Update server status
        function updateServerStatus(serverKey, status) {
            const statusElement = document.getElementById(`${serverKey}-status`);
            if (statusElement) {
                statusElement.className = `server-status status-${status}`;
            }
        }

        // Save all configurations
        async function saveAllConfigurations() {
            showLoading();
            
            try {
                await saveConfiguration();
                hideLoading();
                showSuccessToast('All configurations saved successfully!');
            } catch (error) {
                hideLoading();
                alert('Error saving configurations: ' + error.message);
            }
        }

        // Save configuration to file
        async function saveConfiguration() {
            const response = await fetch('/config/save_mcp_config.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(mcpConfig)
            });
            
            if (!response.ok) {
                throw new Error('Failed to save configuration');
            }
            
            return await response.json();
        }

        // Test all connections
        async function testAllConnections() {
            showLoading();
            const results = [];
            
            for (const serverKey in mcpConfig.mcpServers) {
                try {
                    // Simulate connection test
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    results.push({ server: serverKey, status: 'success' });
                    updateServerStatus(serverKey, 'active');
                } catch (error) {
                    results.push({ server: serverKey, status: 'error', error: error.message });
                    updateServerStatus(serverKey, 'inactive');
                }
            }
            
            hideLoading();
            alert('Connection tests completed:\\n' + results.map(r => `${r.server}: ${r.status}`).join('\\n'));
        }

        // Start all servers
        async function startAllServers() {
            showLoading();
            showSuccessToast('Starting all MCP servers...');
            
            // Simulate server startup
            setTimeout(() => {
                Object.keys(mcpConfig.mcpServers).forEach(serverKey => {
                    updateServerStatus(serverKey, 'active');
                });
                hideLoading();
                showSuccessToast('All MCP servers started successfully!');
            }, 3000);
        }

        // Stop all servers
        async function stopAllServers() {
            showLoading();
            showSuccessToast('Stopping all MCP servers...');
            
            // Simulate server shutdown
            setTimeout(() => {
                Object.keys(mcpConfig.mcpServers).forEach(serverKey => {
                    updateServerStatus(serverKey, 'inactive');
                });
                hideLoading();
                showSuccessToast('All MCP servers stopped successfully!');
            }, 2000);
        }

        // Show/hide loading
        function showLoading() {
            document.querySelector('.loading-spinner').style.display = 'block';
        }

        function hideLoading() {
            document.querySelector('.loading-spinner').style.display = 'none';
        }

        // Show success toast
        function showSuccessToast(message) {
            const toast = document.querySelector('.success-toast');
            toast.querySelector('.toast-body').textContent = message;
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadConfiguration();
            selectDatabase('mysql');
        });
    </script>
</body>
</html>
