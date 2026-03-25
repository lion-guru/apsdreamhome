<?php
$$page_title = 'Analytics Dashboard - APS Dream Home';
$page_description = 'Comprehensive analytics and performance monitoring';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card analytics-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body text-center">
                    <h1><i class="fas fa-chart-line me-3"></i>Advanced Analytics Dashboard</h1>
                    <p class="mb-0">Real-time performance monitoring and business intelligence</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-value">2,847</div>
                <div class="metric-label">Total Users</div>
                <div class="metric-change text-success">↑ 12.5%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-value">59</div>
                <div class="metric-label">Active Properties</div>
                <div class="metric-change text-success">↑ 8.3%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-value">147</div>
                <div class="metric-label">Bookings This Month</div>
                <div class="metric-change text-warning">↓ 2.1%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-value">₹2.4M</div>
                <div class="metric-label">Revenue</div>
                <div class="metric-change text-success">↑ 18.7%</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="chart-container">
                <h5><i class="fas fa-chart-area me-2"></i>Property Views & Bookings Trend</h5>
                <canvas id="trendChart" height="100"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-container">
                <h5><i class="fas fa-chart-pie me-2"></i>Property Types Distribution</h5>
                <canvas id="propertyTypeChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="chart-container">
                <h5><i class="fas fa-tachometer-alt me-2"></i>System Performance</h5>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="performance-gauge">
                            <svg width="120" height="120">
                                <circle cx="60" cy="60" r="50" class="gauge-ring"></circle>
                                <circle cx="60" cy="60" r="50" class="gauge-progress" style="stroke: #28a745; stroke-dasharray: 314 314; stroke-dashoffset: 31;"></circle>
                                <text x="60" y="65" text-anchor="middle" class="gauge-text">90%</text>
                            </svg>
                            <div class="mt-2">Server Uptime</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="performance-gauge">
                            <svg width="120" height="120">
                                <circle cx="60" cy="60" r="50" class="gauge-ring"></circle>
                                <circle cx="60" cy="60" r="50" class="gauge-progress" style="stroke: #ffc107; stroke-dasharray: 314 314; stroke-dashoffset: 94;"></circle>
                                <text x="60" y="65" text-anchor="middle" class="gauge-text">70%</text>
                            </svg>
                            <div class="mt-2">Memory Usage</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="performance-gauge">
                            <svg width="120" height="120">
                                <circle cx="60" cy="60" r="50" class="gauge-ring"></circle>
                                <circle cx="60" cy="60" r="50" class="gauge-progress" style="stroke: #dc3545; stroke-dasharray: 314 314; stroke-dashoffset: 157;"></circle>
                                <text x="60" y="65" text-anchor="middle" class="gauge-text">50%</text>
                            </svg>
                            <div class="mt-2">CPU Usage</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-container">
                <h5><i class="fas fa-chart-bar me-2"></i>Monthly Revenue</h5>
                <canvas id="revenueChart" height="150"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity Timeline -->
    <div class="row">
        <div class="col-md-8">
            <div class="chart-container">
                <h5><i class="fas fa-history me-2"></i>Recent Activity Timeline</h5>
                <div class="activity-timeline">
                    <div class="activity-item">
                        <div class="d-flex align-items-start">
                            <div class="activity-icon activity-whatsapp">
                                <i class="fas fa-whatsapp"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6>WhatsApp Campaign Sent</h6>
                                <p class="mb-1">Bulk WhatsApp campaign sent to 500+ users for new property listings</p>
                                <small class="text-muted">10 minutes ago</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="d-flex align-items-start">
                            <div class="activity-icon activity-email">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6>Email Newsletter</h6>
                                <p class="mb-1">Monthly newsletter with property updates sent to 2,847 subscribers</p>
                                <small class="text-muted">2 hours ago</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="d-flex align-items-start">
                            <div class="activity-icon activity-ai">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6>AI Agent Training</h6>
                                <p class="mb-1">AI agent completed training on 15 new property listings</p>
                                <small class="text-muted">4 hours ago</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="d-flex align-items-start">
                            <div class="activity-icon activity-system">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6>System Update</h6>
                                <p class="mb-1">Database optimization completed - 23% performance improvement</p>
                                <small class="text-muted">6 hours ago</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="chart-container">
                <h5><i class="fas fa-users me-2"></i>User Engagement</h5>
                <div class="engagement-stats">
                    <div class="stat-row">
                        <span>Daily Active Users</span>
                        <strong>342</strong>
                    </div>
                    <div class="stat-row">
                        <span>Page Views</span>
                        <strong>8,947</strong>
                    </div>
                    <div class="stat-row">
                        <span>Avg. Session Time</span>
                        <strong>4m 32s</strong>
                    </div>
                    <div class="stat-row">
                        <span>Bounce Rate</span>
                        <strong>32.4%</strong>
                    </div>
                    <div class="stat-row">
                        <span>Conversion Rate</span>
                        <strong>5.8%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.analytics-card { margin: 15px 0; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
.metric-card { text-align: center; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; margin: 10px 0; }
.metric-value { font-size: 2.5em; font-weight: bold; }
.metric-label { font-size: 0.9em; opacity: 0.9; }
.metric-change { font-size: 0.8em; margin-top: 5px; }
.chart-container { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; }
.activity-timeline { position: relative; padding-left: 30px; }
.activity-timeline::before { content: ''; position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background: #dee2e6; }
.activity-item { position: relative; margin: 15px 0; padding: 15px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.activity-item::before { content: ''; position: absolute; left: -23px; top: 20px; width: 12px; height: 12px; background: #007bff; border-radius: 50%; border: 3px solid white; }
.activity-icon { width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 15px; }
.activity-whatsapp { background: #25d366; color: white; }
.activity-email { background: #007bff; color: white; }
.activity-ai { background: #6f42c1; color: white; }
.activity-system { background: #ffc107; color: #212529; }
.performance-gauge { position: relative; width: 120px; height: 120px; margin: 0 auto; }
.gauge-ring { fill: none; stroke: #e9ecef; stroke-width: 8; }
.gauge-progress { fill: none; stroke-width: 8; stroke-linecap: round; transform-origin: center; transform: rotate(-90deg); }
.gauge-text { font-size: 1.5em; font-weight: bold; fill: #495057; }
.engagement-stats .stat-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
.engagement-stats .stat-row:last-child { border-bottom: none; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Trend Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Property Views',
            data: [1200, 1900, 3000, 5000, 4000, 6000],
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4
        }, {
            label: 'Bookings',
            data: [100, 150, 200, 350, 280, 420],
            borderColor: '#764ba2',
            backgroundColor: 'rgba(118, 75, 162, 0.1)',
            tension: 0.4
        }]
    }
});

// Property Type Chart
const propertyCtx = document.getElementById('propertyTypeChart').getContext('2d');
new Chart(propertyCtx, {
    type: 'doughnut',
    data: {
        labels: ['Apartments', 'Villas', 'Commercial', 'Land'],
        datasets: [{
            data: [35, 25, 20, 20],
            backgroundColor: ['#667eea', '#764ba2', '#ffc107', '#28a745']
        }]
    }
});

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Revenue (₹Lakhs)',
            data: [18, 22, 28, 35, 42, 48],
            backgroundColor: '#667eea'
        }]
    }
});
</script>
