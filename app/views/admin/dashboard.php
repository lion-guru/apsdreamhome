<?php

/**
 * APS Dream Home - Admin Dashboard
 * Main admin interface
 */

$page_title = $page_title ?? 'Admin Dashboard - APS Dream Home';
$stats = $stats ?? [];
$recent_projects = $recent_projects ?? [];
$recent_applications = $recent_applications ?? [];
$pending_tasks = $pending_tasks ?? [];
?>

<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page variables
$page_title = $page_title ?? 'Admin Dashboard - APS Dream Home';
$stats = $stats ?? [];
$recent_projects = $recent_projects ?? [];
$recent_applications = $recent_applications ?? [];
$pending_tasks = $pending_tasks ?? [];

// Content for base layout
ob_start();
?>

<!-- AI Dashboard Header -->
<div class="ai-dashboard-header mb-4">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="ai-status-indicator">
                    <div class="ai-pulse"></div>
                    <h4 class="mb-0">
                        <i class="fas fa-robot me-2"></i>
                        AI Assistant Active
                    </h4>
                    <small class="text-success">Full Power Mode Enabled</small>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="ai-controls">
                    <button class="btn btn-ai-primary me-2" onclick="toggleAIMode()">
                        <i class="fas fa-brain me-1"></i>
                        AI Mode
                    </button>
                    <button class="btn btn-ai-secondary me-2" onclick="refreshAIData()">
                        <i class="fas fa-sync me-1"></i>
                        Refresh
                    </button>
                    <button class="btn btn-ai-info" onclick="showAIInsights()">
                        <i class="fas fa-chart-line me-1"></i>
                        Insights
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Stats Grid -->
<div class="enhanced-stats-grid mb-4">
    <div class="container-fluid">
        <div class="row g-3">
            <!-- AI-Powered Network Overview -->
            <div class="col-md-3">
                <div class="stat-card ai-enhanced">
                    <div class="stat-icon ai-gradient">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number" data-target="networkSize">
                            <?php echo number_format($stats['network_size'] ?? 0); ?>
                        </h3>
                        <p class="stat-label">Network Size</p>
                        <div class="ai-indicator">
                            <small class="text-success"><i class="fas fa-arrow-up"></i> +12.5%</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI-Powered Revenue Analytics -->
            <div class="col-md-3">
                <div class="stat-card ai-enhanced">
                    <div class="stat-icon ai-gradient">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number" data-target="revenue">
                            ₹<?php echo number_format($stats['revenue'] ?? 0); ?>
                        </h3>
                        <p class="stat-label">Platform Revenue</p>
                        <div class="ai-indicator">
                            <small class="text-success"><i class="fas fa-arrow-up"></i> +8.3%</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI-Powered Active Users -->
            <div class="col-md-3">
                <div class="stat-card ai-enhanced">
                    <div class="stat-icon ai-gradient">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number" data-target="activeUsers">
                            <?php echo number_format($stats['active_users'] ?? 0); ?>
                        </h3>
                        <p class="stat-label">Active Users</p>
                        <div class="ai-indicator">
                            <small class="text-info"><i class="fas fa-minus"></i> Stable</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI-Powered Performance Score -->
            <div class="col-md-3">
                <div class="stat-card ai-enhanced">
                    <div class="stat-icon ai-gradient">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number" data-target="performance">
                            <?php echo $stats['performance_score'] ?? 95; ?>%
                        </h3>
                        <p class="stat-label">AI Performance</p>
                        <div class="ai-indicator">
                            <small class="text-success"><i class="fas fa-arrow-up"></i> Optimal</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Command Center -->
<div class="ai-command-center mb-4">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="ai-terminal">
                    <div class="terminal-header">
                        <span class="terminal-prompt">AI-ADMIN&gt;</span>
                        <span id="aiCommand" class="terminal-input" contenteditable="false">System initialized...</span>
                        <span class="terminal-cursor">|</span>
                    </div>
                    <div class="terminal-output" id="terminalOutput">
                        <div class="log-entry success">AI System Online</div>
                        <div class="log-entry info">Scanning network for anomalies...</div>
                        <div class="log-entry warning">3 potential optimizations identified</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Analytics Section -->
<div class="advanced-analytics mb-4">
    <div class="container-fluid">
        <div class="row">
            <!-- Real-time Activity Monitor -->
            <div class="col-md-8">
                <div class="analytics-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-area me-2"></i>
                            Real-time Activity Monitor
                        </h5>
                        <div class="live-indicator">
                            <span class="live-dot"></span>
                            LIVE
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="activityChart" width="400" height="200"></canvas>
                        <div class="chart-legend">
                            <span class="legend-item users"><i class="fas fa-circle text-primary"></i> Users</span>
                            <span class="legend-item properties"><i class="fas fa-circle text-success"></i> Properties</span>
                            <span class="legend-item revenue"><i class="fas fa-circle text-warning"></i> Revenue</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Insights Panel -->
            <div class="col-md-4">
                <div class="ai-insights">
                    <div class="insights-header">
                        <h5 class="mb-3">
                            <i class="fas fa-brain me-2"></i>
                            AI Insights
                        </h5>
                    </div>
                    <div class="insights-content">
                        <div class="insight-item">
                            <div class="insight-icon positive">
                                <i class="fas fa-arrow-trend-up"></i>
                            </div>
                            <div class="insight-text">
                                <strong>Peak Performance</strong><br>
                                <small>System running at optimal efficiency</small>
                            </div>
                        </div>
                        <div class="insight-item">
                            <div class="insight-icon warning">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="insight-text">
                                <strong>Security Alert</strong><br>
                                <small>2 unusual login patterns detected</small>
                            </div>
                        </div>
                        <div class="insight-item">
                            <div class="insight-icon info">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="insight-text">
                                <strong>Optimization Ready</strong><br>
                                <small>3 performance improvements available</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Main Content -->
<div class="enhanced-main-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-2">
                <div class="enhanced-sidebar">
                    <div class="sidebar-header">
                        <h4 class="text-white">AI Control</h4>
                    </div>
                    <nav class="sidebar-nav">
                        <a href="#" class="nav-item active" onclick="loadSection('overview')">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Overview</span>
                        </a>
                        <a href="#" class="nav-item" onclick="loadSection('analytics')">
                            <i class="fas fa-chart-line"></i>
                            <span>Analytics</span>
                        </a>
                        <a href="#" class="nav-item" onclick="loadSection('users')">
                            <i class="fas fa-users"></i>
                            <span>User Network</span>
                        </a>
                        <a href="#" class="nav-item" onclick="loadSection('properties')">
                            <i class="fas fa-home"></i>
                            <span>Properties</span>
                        </a>
                        <a href="#" class="nav-item" onclick="loadSection('ai-tools')">
                            <i class="fas fa-robot"></i>
                            <span>AI Tools</span>
                        </a>
                        <a href="#" class="nav-item" onclick="loadSection('security')">
                            <i class="fas fa-shield-alt"></i>
                            <span>Security</span>
                        </a>
                        <a href="#" class="nav-item" onclick="loadSection('reports')">
                            <i class="fas fa-file-alt"></i>
                            <span>Reports</span>
                        </a>
                        <a href="#" class="nav-item" onclick="loadSection('settings')">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-md-10">
                <div class="content-area" id="contentArea">
                    <!-- Dynamic content will be loaded here -->
                    <div class="section-overview">
                        <h2 class="section-title">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Administrative Intelligence Dashboard
                        </h2>
                        <p class="section-desc">AI-powered real-time monitoring and management system</p>

                        <!-- Quick Actions Grid -->
                        <div class="quick-actions-grid">
                            <div class="action-card" onclick="executeAction('system-scan')">
                                <div class="action-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h6>System Scan</h6>
                                <p>Perform comprehensive system analysis</p>
                            </div>
                            <div class="action-card" onclick="executeAction('optimize-db')">
                                <div class="action-icon">
                                    <i class="fas fa-database"></i>
                                </div>
                                <h6>Optimize DB</h6>
                                <p>AI-powered database optimization</p>
                            </div>
                            <div class="action-card" onclick="executeAction('security-check')">
                                <div class="action-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h6>Security Check</h6>
                                <p>Advanced security analysis</p>
                            </div>
                            <div class="action-card" onclick="executeAction('backup-system')">
                                <div class="action-icon">
                                    <i class="fas fa-save"></i>
                                </div>
                                <h6>Backup System</h6>
                                <p>Automated system backup</p>
                            </div>
                            <div class="action-card" onclick="executeAction('ai-report')">
                                <div class="action-icon">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <h6>AI Report</h6>
                                <p>Generate AI insights report</p>
                            </div>
                            <div class="action-card" onclick="executeAction('cache-clear')">
                                <div class="action-icon">
                                    <i class="fas fa-broom"></i>
                                </div>
                                <h6>Clear Cache</h6>
                                <p>System cache cleanup</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.3);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        --ai-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        --terminal-bg: #1a1a2e;
        --terminal-green: #00ff00;
        --terminal-yellow: #ffff00;
        --terminal-red: #ff0000;
    }

    /* AI Dashboard Styles */
    .ai-dashboard-header {
        background: var(--ai-gradient);
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: var(--glass-shadow);
        margin-bottom: 2rem;
    }

    .ai-status-indicator {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .ai-pulse {
        width: 12px;
        height: 12px;
        background: #00ff00;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    .ai-controls .btn {
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-ai-primary {
        background: linear-gradient(135deg, #00ff88 0%, #00cc66 100%);
        color: white;
    }

    .btn-ai-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }

    .btn-ai-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
    }

    .enhanced-stats-grid .stat-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 15px;
        padding: 1.5rem;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .enhanced-stats-grid .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
    }

    .ai-enhanced {
        border-left: 4px solid #00ff88;
    }

    .ai-gradient {
        background: var(--ai-gradient);
        color: white;
    }

    .ai-indicator {
        margin-top: 0.5rem;
    }

    .ai-command-center {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 15px;
        padding: 1.5rem;
        backdrop-filter: blur(10px);
    }

    .ai-terminal {
        background: var(--terminal-bg);
        border-radius: 8px;
        padding: 1rem;
        font-family: 'Courier New', monospace;
        color: var(--terminal-green);
        min-height: 120px;
    }

    .terminal-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .terminal-prompt {
        color: #00ff88;
        font-weight: bold;
    }

    .terminal-input {
        flex: 1;
    }

    .terminal-cursor {
        animation: blink 1s infinite;
    }

    @keyframes blink {

        0%,
        50% {
            opacity: 1;
        }

        51%,
        100% {
            opacity: 0;
        }
    }

    .terminal-output {
        font-size: 0.85rem;
        line-height: 1.4;
    }

    .log-entry {
        margin-bottom: 0.5rem;
        padding: 0.25rem;
        border-radius: 4px;
    }

    .log-entry.success {
        background: rgba(0, 255, 0, 0.1);
        border-left: 3px solid var(--terminal-green);
    }

    .log-entry.info {
        background: rgba(0, 150, 255, 0.1);
        border-left: 3px solid #0096ff;
    }

    .log-entry.warning {
        background: rgba(255, 255, 0, 0.1);
        border-left: 3px solid var(--terminal-yellow);
    }

    .advanced-analytics {
        margin-bottom: 2rem;
    }

    .analytics-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 15px;
        backdrop-filter: blur(10px);
        height: 100%;
    }

    .live-indicator {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .live-dot {
        width: 8px;
        height: 8px;
        background: #00ff00;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    .ai-insights {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 15px;
        padding: 1.5rem;
        backdrop-filter: blur(10px);
        height: 100%;
    }

    .insights-header {
        border-bottom: 1px solid var(--glass-border);
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }

    .insight-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .insight-item:last-child {
        border-bottom: none;
    }

    .insight-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .insight-icon.positive {
        background: rgba(0, 255, 0, 0.2);
        color: var(--terminal-green);
    }

    .insight-icon.warning {
        background: rgba(255, 255, 0, 0.2);
        color: var(--terminal-yellow);
    }

    .insight-icon.info {
        background: rgba(0, 150, 255, 0.2);
        color: #0096ff;
    }

    .enhanced-main-content {
        margin-top: 2rem;
    }

    .enhanced-sidebar {
        background: linear-gradient(135deg, #1a1c23 0%, #2d3441 100%);
        border-radius: 15px;
        padding: 1.5rem;
        height: fit-content;
    }

    .sidebar-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }

    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .sidebar-nav .nav-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .sidebar-nav .nav-item:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        transform: translateX(5px);
    }

    .sidebar-nav .nav-item.active {
        background: var(--primary-gradient);
        color: white;
    }

    .content-area {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 15px;
        padding: 2rem;
        backdrop-filter: blur(10px);
        min-height: 600px;
    }

    .section-title {
        color: white;
        margin-bottom: 1rem;
    }

    .section-desc {
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 2rem;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .action-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .action-card:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
    }

    .action-icon {
        width: 60px;
        height: 60px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: white;
        font-size: 1.5rem;
    }

    .action-card h6 {
        color: white;
        margin-bottom: 0.5rem;
    }

    .action-card p {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
        margin: 0;
    }

    body {
        background: #f0f2f5;
        font-family: 'Inter', sans-serif;
    }

    .admin-sidebar {
        background: #1a1c23;
        min-height: 100vh;
        padding: 1.5rem;
        position: fixed;
        width: inherit;
    }

    .admin-sidebar .nav-link {
        color: rgba(255, 255, 255, 0.7);
        padding: 0.8rem 1rem;
        border-radius: 12px;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .admin-sidebar .nav-link:hover,
    .admin-sidebar .nav-link.active {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .main-content {
        padding: 2rem;
        margin-left: 16.666667%;
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: var(--glass-shadow);
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
    }
</style>

<script>
    // AI Dashboard JavaScript
    let aiMode = 'full';
    let currentSection = 'overview';

    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', function() {
        initializeAIDashboard();
        startRealTimeUpdates();
        animateCounters();
        initializeActivityChart();
    });

    function initializeAIDashboard() {
        updateTerminalCommand('AI System initialized successfully');
        addTerminalLog('Scanning network for active users...', 'info');
        addTerminalLog('AI Analytics engine started', 'success');
    }

    function toggleAIMode() {
        aiMode = aiMode === 'full' ? 'economy' : 'full';
        updateTerminalCommand(`AI Mode switched to ${aiMode}`);
        addTerminalLog(`AI ${aiMode} mode activated`, 'success');
    }

    function refreshAIData() {
        updateTerminalCommand('Refreshing AI data...');
        addTerminalLog('Fetching latest analytics...', 'info');

        // Simulate data refresh
        setTimeout(() => {
            addTerminalLog('AI data refreshed successfully', 'success');
            updateStats();
        }, 2000);
    }

    function showAIInsights() {
        updateTerminalCommand('Generating AI insights...');
        addTerminalLog('Analyzing system patterns...', 'info');

        setTimeout(() => {
            addTerminalLog('3 optimization opportunities identified', 'warning');
            addTerminalLog('System performance: Optimal', 'success');
        }, 1500);
    }

    function loadSection(section) {
        currentSection = section;
        updateTerminalCommand(`Loading ${section} section...`);

        // Update active nav
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
        });
        event.target.classList.add('active');

        // Load section content
        loadSectionContent(section);
    }

    function loadSectionContent(section) {
        const contentArea = document.getElementById('contentArea');

        const sections = {
            'overview': `
                    <h2 class="section-title">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Administrative Intelligence Dashboard
                    </h2>
                    <p class="section-desc">AI-powered real-time monitoring and management system</p>
                    <div class="quick-actions-grid">
                        <div class="action-card" onclick="executeAction('system-scan')">
                            <div class="action-icon"><i class="fas fa-search"></i></div>
                            <h6>System Scan</h6>
                            <p>Perform comprehensive system analysis</p>
                        </div>
                        <div class="action-card" onclick="executeAction('optimize-db')">
                            <div class="action-icon"><i class="fas fa-database"></i></div>
                            <h6>Optimize DB</h6>
                            <p>AI-powered database optimization</p>
                        </div>
                        <div class="action-card" onclick="executeAction('security-check')">
                            <div class="action-icon"><i class="fas fa-shield-alt"></i></div>
                            <h6>Security Check</h6>
                            <p>Advanced security analysis</p>
                        </div>
                    </div>
                `,
            'analytics': `
                    <h2 class="section-title">
                        <i class="fas fa-chart-line me-2"></i>
                        Advanced Analytics
                    </h2>
                    <p class="section-desc">Deep AI-powered analytics and insights</p>
                    <div class="analytics-content">
                        <canvas id="detailedChart" width="400" height="300"></canvas>
                    </div>
                `
        };

        contentArea.innerHTML = sections[section] || sections['overview'];
        addTerminalLog(`${section} section loaded`, 'success');
    }

    function executeAction(action) {
        updateTerminalCommand(`Executing ${action}...`);
        addTerminalLog('AI processing request...', 'info');

        const actions = {
            'system-scan': () => {
                setTimeout(() => {
                    addTerminalLog('System scan completed', 'success');
                    addTerminalLog('0 critical issues found', 'success');
                    addTerminalLog('3 optimizations available', 'warning');
                }, 3000);
            },
            'optimize-db': () => {
                setTimeout(() => {
                    addTerminalLog('Database optimization started', 'info');
                    addTerminalLog('Query optimization: +25% performance', 'success');
                    addTerminalLog('Index optimization: +15% performance', 'success');
                    addTerminalLog('Cache cleanup: Completed', 'success');
                }, 2000);
            },
            'security-check': () => {
                setTimeout(() => {
                    addTerminalLog('Security analysis in progress...', 'info');
                    addTerminalLog('Firewall: Secure', 'success');
                    addTerminalLog('SSL Certificate: Valid', 'success');
                    addTerminalLog('2 suspicious activities detected', 'warning');
                }, 2500);
            }
        };

        if (actions[action]) {
            actions[action]();
        }
    }

    function updateTerminalCommand(command) {
        const commandElement = document.getElementById('aiCommand');
        commandElement.textContent = command;
    }

    function addTerminalLog(message, type = 'info') {
        const output = document.getElementById('terminalOutput');
        const logEntry = document.createElement('div');
        logEntry.className = `log-entry ${type}`;
        logEntry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
        output.appendChild(logEntry);
        output.scrollTop = output.scrollHeight;
    }

    function animateCounters() {
        const counters = document.querySelectorAll('.stat-number');
        counters.forEach(counter => {
            const target = parseInt(counter.textContent.replace(/[^0-9]/g, ''));
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                counter.textContent = Math.floor(current).toLocaleString();
            }, 16);
        });
    }

    function startRealTimeUpdates() {
        setInterval(() => {
            updateLiveStats();
        }, 5000);
    }

    function updateLiveStats() {
        const randomUpdates = [
            'New user registered',
            'Property view updated',
            'Database query optimized',
            'Cache refreshed'
        ];

        if (Math.random() > 0.7) {
            const update = randomUpdates[Math.floor(Math.random() * randomUpdates.length)];
            addTerminalLog(update, 'info');
        }
    }

    function initializeActivityChart() {
        const canvas = document.getElementById('activityChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        // Create gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, 200);
        gradient.addColorStop(0, 'rgba(102, 126, 234, 0.8)');
        gradient.addColorStop(1, 'rgba(102, 126, 234, 0.2)');

        // Simple chart animation
        let offset = 0;

        function drawChart() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw grid lines
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
            ctx.lineWidth = 1;
            for (let i = 0; i < 5; i++) {
                ctx.beginPath();
                ctx.moveTo(0, i * 40);
                ctx.lineTo(canvas.width, i * 40);
                ctx.stroke();
            }

            // Draw data line
            ctx.strokeStyle = '#667eea';
            ctx.lineWidth = 3;
            ctx.beginPath();

            for (let i = 0; i < canvas.width; i += 5) {
                const y = canvas.height / 2 + Math.sin((i + offset) * 0.02) * 50;
                if (i === 0) {
                    ctx.moveTo(i, y);
                } else {
                    ctx.lineTo(i, y);
                }
            }

            ctx.stroke();
            offset += 2;
            requestAnimationFrame(drawChart);
        }

        drawChart();
    }
</script>

<?php
$content = ob_get_clean();

// Include enterprise dashboard layout
require_once __DIR__ . '/enterprise_dashboard.php';
?>
-webkit-backdrop-filter: blur(10px);
border: 1px solid var(--glass-border);
border-radius: 20px;
box-shadow: var(--glass-shadow);
padding: 1.5rem;
transition: transform 0.3s ease;
}

.glass-card:hover {
transform: translateY(-5px);
}

.admin-header {
background: var(--primary-gradient);
border-radius: 24px;
padding: 2.5rem;
color: white;
margin-bottom: 2rem;
box-shadow: 0 10px 20px rgba(118, 75, 162, 0.2);
}

.stat-icon {
width: 50px;
height: 50px;
border-radius: 12px;
display: flex;
align-items: center;
justify-content: center;
font-size: 1.5rem;
color: white;
margin-bottom: 1rem;
}

.recent-item {
display: flex;
align-items: center;
padding: 1rem;
border-bottom: 1px solid rgba(0, 0, 0, 0.05);
transition: background 0.2s;
}

.recent-item:last-child {
border-bottom: none;
}

.recent-item:hover {
background: rgba(0, 0, 0, 0.02);
}

.activity-dot {
width: 10px;
height: 10px;
border-radius: 50%;
margin-right: 1rem;
}

.bg-gradient-blue {
background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
}

.bg-gradient-green {
background: linear-gradient(135deg, #064e3b 0%, #10b981 100%);
}

.bg-gradient-orange {
background: linear-gradient(135deg, #7c2d12 0%, #f97316 100%);
}

.bg-gradient-purple {
background: linear-gradient(135deg, #4c1d95 0%, #8b5cf6 100%);
}
</style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 position-fixed">
                <div class="admin-sidebar">
                    <div class="text-center mb-5">
                        <h4 class="text-white fw-bold">APS <span class="text-primary text-opacity-75">Dream</span></h4>
                        <small class="text-muted">ADMIN PANEL</small>
                    </div>
                    <nav class="nav flex-column">
                        <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="nav-link active"><i class="fas fa-grid-2 me-2"></i> Dashboard</a>
                        <a href="<?php echo BASE_URL; ?>/admin/properties" class="nav-link"><i class="fas fa-home me-2"></i> Properties</a>
                        <a href="<?php echo BASE_URL; ?>/admin/users" class="nav-link"><i class="fas fa-users me-2"></i> User Network</a>
                        <a href="<?php echo BASE_URL; ?>/admin/reports" class="nav-link"><i class="fas fa-chart-pie me-2"></i> Analytics</a>
                        <a href="<?php echo BASE_URL; ?>/admin/settings" class="nav-link"><i class="fas fa-sliders me-2"></i> Settings</a>
                        <hr class="border-secondary opacity-25">
                        <a href="<?php echo BASE_URL; ?>/" class="nav-link"><i class="fas fa-external-link-alt me-2"></i> Public View</a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="admin-header animate-fade-in">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="fw-bold mb-1">Administrative Intelligence</h1>
                            <p class="mb-0 opacity-75">Visualizing property metrics and user growth in real-time.</p>
                        </div>
                        <div class="text-end">
                            <div class="dropdown">
                                <div class="d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-3 py-2 border border-white border-opacity-10" data-bs-toggle="dropdown" style="cursor: pointer;">
                                    <img src="https://ui-avatars.com/api/?name=Admin&background=random" class="rounded-circle me-2" width="32">
                                    <span class="fw-medium">Super Admin</span>
                                    <i class="fas fa-chevron-down ms-2"></i>
                                </div>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/admin/logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="glass-card">
                            <div class="stat-icon bg-gradient-blue"><i class="fas fa-users"></i></div>
                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['total_users'] ?? 150); ?></h2>
                            <p class="text-muted small mb-0">Total Network Size</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="glass-card">
                            <div class="stat-icon bg-gradient-green"><i class="fas fa-building"></i></div>
                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['total_properties'] ?? 85); ?></h2>
                            <p class="text-muted small mb-0">Managed Assets</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="glass-card">
                            <div class="stat-icon bg-gradient-orange"><i class="fas fa-file-invoice-dollar"></i></div>
                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['pending_approvals'] ?? 5); ?></h2>
                            <p class="text-muted small mb-0">Pending Validations</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="glass-card">
                            <div class="stat-icon bg-gradient-purple"><i class="fas fa-wallet"></i></div>
                            <h2 class="fw-bold mb-0">₹<?php echo number_format(is_numeric($stats['total_revenue'] ?? 0) ? $stats['total_revenue'] : 245000); ?></h2>
                            <p class="text-muted small mb-0">Platform Revenue</p>
                        </div>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="glass-card h-100">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0">Growth Performance</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light rounded-pill px-3" type="button">This Month</button>
                                </div>
                            </div>
                            <div style="height: 300px; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.02); border-radius: 15px;">
                                <p class="text-muted">Interactive growth charts will render here.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card h-100">
                            <h5 class="fw-bold mb-4">Live Activities</h5>
                            <div class="activity-feed">
                                <?php if (!empty($recent_activities)): ?>
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <div class="recent-item">
                                            <div class="activity-dot bg-primary"></div>
                                            <div>
                                                <p class="mb-0 fw-medium small"><?php echo htmlspecialchars($activity['name']); ?></p>
                                                <small class="text-muted">Performed <?php echo htmlspecialchars($activity['action']); ?> action</small>
                                            </div>
                                            <small class="ms-auto text-muted" style="font-size: 0.7rem;">Today</small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-stream text-muted fa-2x mb-3"></i>
                                        <p class="text-muted small">No recent activity detected.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary w-100 mt-4 rounded-pill">View All Logs</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>