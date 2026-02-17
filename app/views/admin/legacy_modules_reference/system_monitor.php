<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';

$page_title = "System Monitor";
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<link rel="stylesheet" href="<?= get_admin_asset_url('chart.min.css', 'css') ?>">

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">System Monitor</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">System Monitor</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshStats()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportStats()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="far fa-calendar-alt me-1"></i> Time Range
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="changeTimeRange('24h')">Last 24 Hours</a></li>
                            <li><a class="dropdown-item" href="#" onclick="changeTimeRange('7d')">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="#" onclick="changeTimeRange('30d')">Last 30 Days</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3 small fw-bold">Follow-up System</h6>
                        <div class="d-flex align-items-center">
                            <div class="system-status" id="followupStatus">
                                <i class="fas fa-circle text-success"></i>
                            </div>
                            <div class="ms-3">
                                <div class="h3 mb-0 fw-bold" id="followupCount">0</div>
                                <small class="text-muted">Processed Today</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3 small fw-bold">Visit Scheduling</h6>
                        <div class="d-flex align-items-center">
                            <div class="system-status" id="visitStatus">
                                <i class="fas fa-circle text-success"></i>
                            </div>
                            <div class="ms-3">
                                <div class="h3 mb-0 fw-bold" id="visitCount">0</div>
                                <small class="text-muted">Scheduled Today</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3 small fw-bold">AI Recommendations</h6>
                        <div class="d-flex align-items-center">
                            <div class="system-status" id="aiStatus">
                                <i class="fas fa-circle text-success"></i>
                            </div>
                            <div class="ms-3">
                                <div class="h3 mb-0 fw-bold" id="aiCount">0</div>
                                <small class="text-muted">Generated Today</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3 small fw-bold">Lead Processing</h6>
                        <div class="d-flex align-items-center">
                            <div class="system-status" id="leadStatus">
                                <i class="fas fa-circle text-success"></i>
                            </div>
                            <div class="ms-3">
                                <div class="h3 mb-0 fw-bold" id="leadCount">0</div>
                                <small class="text-muted">Active Leads</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title h6 fw-bold mb-3">System Activity</h5>
                        <div style="height: 300px;">
                            <canvas id="activityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title h6 fw-bold mb-3">Response Times</h5>
                        <div style="height: 300px;">
                            <canvas id="responseChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 fw-bold mb-0">System Logs</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="systemLogs">
                        <thead class="table-light">
                            <tr>
                                <th class="small fw-bold">Timestamp</th>
                                <th class="small fw-bold">System</th>
                                <th class="small fw-bold">Event</th>
                                <th class="small fw-bold">Status</th>
                                <th class="small fw-bold">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Logs will be loaded here -->
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span class="ms-2 text-muted">Loading logs...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

<script src="<?= get_admin_asset_url('chart.min.js', 'js') ?>"></script>
<script>
    let activityChart, responseChart;
    let currentTimeRange = '24h';

    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
        loadStats();
        loadLogs();
    });

    function initCharts() {
        // Activity Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        activityChart = new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Follow-ups',
                    data: [],
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Visits',
                    data: [],
                    borderColor: '#6f42c1',
                    backgroundColor: 'rgba(111, 66, 193, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'AI Recommendations',
                    data: [],
                    borderColor: '#fd7e14',
                    backgroundColor: 'rgba(253, 126, 20, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Response Time Chart
        const responseCtx = document.getElementById('responseChart').getContext('2d');
        responseChart = new Chart(responseCtx, {
            type: 'bar',
            data: {
                labels: ['Follow-ups', 'Visit Scheduling', 'AI Recommendations', 'Lead Processing'],
                datasets: [{
                    label: 'Average Response Time (ms)',
                    data: [],
                    backgroundColor: [
                        'rgba(13, 110, 253, 0.7)',
                        'rgba(111, 66, 193, 0.7)',
                        'rgba(253, 126, 20, 0.7)',
                        'rgba(220, 53, 69, 0.7)'
                    ],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function loadStats() {
        fetch(`../api/system_stats.php?range=${currentTimeRange}`)
            .then(response => response.json())
            .then(data => {
                updateDashboard(data);
                updateCharts(data);
            })
            .catch(error => console.error('Error loading stats:', error));
    }

    function loadLogs() {
        fetch(`../api/system_logs.php?range=${currentTimeRange}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#systemLogs tbody');
                if (!data.logs || data.logs.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No logs found for this period.</td></tr>';
                    return;
                }
                tbody.innerHTML = data.logs.map(log => `
                    <tr>
                        <td class="small text-muted">${log.timestamp}</td>
                        <td class="small fw-bold">${log.system}</td>
                        <td class="small">${log.event}</td>
                        <td>
                            <span class="badge rounded-pill bg-${log.status === 'success' ? 'success' : 'danger'} small">
                                ${log.status}
                            </span>
                        </td>
                        <td class="small text-muted">${log.details}</td>
                    </tr>
                `).join('');
            })
            .catch(error => {
                console.error('Error loading logs:', error);
                document.querySelector('#systemLogs tbody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error loading logs.</td></tr>';
            });
    }

    function updateDashboard(data) {
        // Update counters
        document.getElementById('followupCount').textContent = data.followups?.count || 0;
        document.getElementById('visitCount').textContent = data.visits?.count || 0;
        document.getElementById('aiCount').textContent = data.ai?.count || 0;
        document.getElementById('leadCount').textContent = data.leads?.count || 0;

        // Update status indicators
        updateStatus('followupStatus', data.followups?.status);
        updateStatus('visitStatus', data.visits?.status);
        updateStatus('aiStatus', data.ai?.status);
        updateStatus('leadStatus', data.leads?.status);
    }

    function updateStatus(elementId, status) {
        const element = document.getElementById(elementId).querySelector('i');
        element.className = `fas fa-circle text-${status === 'healthy' ? 'success' : 'danger'}`;
    }

    function updateCharts(data) {
        if (!data.timeline) return;
        
        // Update Activity Chart
        activityChart.data.labels = data.timeline.labels || [];
        activityChart.data.datasets[0].data = data.timeline.followups || [];
        activityChart.data.datasets[1].data = data.timeline.visits || [];
        activityChart.data.datasets[2].data = data.timeline.ai || [];
        activityChart.update();

        // Update Response Time Chart
        if (data.performance) {
            responseChart.data.datasets[0].data = [
                data.performance.followups || 0,
                data.performance.visits || 0,
                data.performance.ai || 0,
                data.performance.leads || 0
            ];
            responseChart.update();
        }
    }

    function changeTimeRange(range) {
        currentTimeRange = range;
        refreshStats();
    }

    function refreshStats() {
        loadStats();
        loadLogs();
    }

    function exportStats() {
        window.location.href = `../api/export_stats.php?range=${currentTimeRange}`;
    }

    // Refresh every 5 minutes
    setInterval(refreshStats, 300000);
</script>


