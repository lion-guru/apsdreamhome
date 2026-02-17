<?php
require_once __DIR__ . '/core/init.php';

$page_title = "Alert Analytics";

require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="index.php"><?= h($mlSupport->translate('Dashboard')) ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?= h($mlSupport->translate('Alert Analytics')) ?></li>
                        </ol>
                    </nav>
                    <h3 class="page-title fw-bold text-primary"><?= h($mlSupport->translate('Alert & System Analytics')) ?></h3>
                    <p class="text-muted small mb-0"><?= h($mlSupport->translate('Monitor system alerts, resolution trends, and performance metrics')) ?></p>
                </div>
                <div class="col-auto">
                    <div class="btn-group shadow-sm border-0">
                        <button type="button" class="btn btn-white btn-sm fw-bold" onclick="refreshAnalytics()">
                            <i class="fas fa-sync-alt me-1"></i> <?= h($mlSupport->translate('Refresh')) ?>
                        </button>
                        <button type="button" class="btn btn-white btn-sm fw-bold" onclick="exportAnalytics()">
                            <i class="fas fa-download me-1"></i> <?= h($mlSupport->translate('Export')) ?>
                        </button>
                    </div>
                    <div class="dropdown d-inline-block ms-2">
                        <button class="btn btn-white btn-sm dropdown-toggle shadow-sm border-0 fw-bold" type="button" data-bs-toggle="dropdown">
                            <i class="far fa-calendar-alt me-1"></i> <?= h($mlSupport->translate('Time Range')) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                            <li><a class="dropdown-item" href="#" onclick="changeTimeRange('24h')"><?= h($mlSupport->translate('Last 24 Hours')) ?></a></li>
                            <li><a class="dropdown-item" href="#" onclick="changeTimeRange('7d')"><?= h($mlSupport->translate('Last 7 Days')) ?></a></li>
                            <li><a class="dropdown-item" href="#" onclick="changeTimeRange('30d')"><?= h($mlSupport->translate('Last 30 Days')) ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Summary Cards -->
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm border-0 h-100 border-start border-danger border-4">
                    <div class="card-body">
                        <h6 class="text-uppercase fw-bold text-muted small mb-3"><?= h($mlSupport->translate('Critical Alerts')) ?></h6>
                        <div class="d-flex align-items-center justify-content-between">
                            <h2 class="fw-bold mb-0" id="criticalCount">0</h2>
                            <div class="text-end">
                                <small class="d-block fw-bold" id="criticalTrend">0%</small>
                                <small class="text-muted small"><?= h($mlSupport->translate('vs last period')) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm border-0 h-100 border-start border-warning border-4">
                    <div class="card-body">
                        <h6 class="text-uppercase fw-bold text-muted small mb-3"><?= h($mlSupport->translate('Warning Alerts')) ?></h6>
                        <div class="d-flex align-items-center justify-content-between">
                            <h2 class="fw-bold mb-0" id="warningCount">0</h2>
                            <div class="text-end">
                                <small class="d-block fw-bold" id="warningTrend">0%</small>
                                <small class="text-muted small"><?= h($mlSupport->translate('vs last period')) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm border-0 h-100 border-start border-primary border-4">
                    <div class="card-body">
                        <h6 class="text-uppercase fw-bold text-muted small mb-3"><?= h($mlSupport->translate('Avg Resolution Time')) ?></h6>
                        <div class="d-flex align-items-center justify-content-between">
                            <h2 class="fw-bold mb-0" id="avgResolutionTime">0m</h2>
                            <div class="text-end">
                                <small class="d-block fw-bold" id="resolutionTrend">0%</small>
                                <small class="text-muted small"><?= h($mlSupport->translate('vs last period')) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm border-0 h-100 border-start border-info border-4">
                    <div class="card-body">
                        <h6 class="text-uppercase fw-bold text-muted small mb-3"><?= h($mlSupport->translate('Alert Rate')) ?></h6>
                        <div class="d-flex align-items-center justify-content-between">
                            <h2 class="fw-bold mb-0" id="alertRate">0/hr</h2>
                            <div class="text-end">
                                <small class="d-block fw-bold" id="alertRateTrend">0%</small>
                                <small class="text-muted small"><?= h($mlSupport->translate('vs last period')) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Trends Chart -->
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?= h($mlSupport->translate('Alert Trends')) ?></h5>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="alertTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?= h($mlSupport->translate('Alert Distribution')) ?></h5>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="alertDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Performance -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?= h($mlSupport->translate('Response Time by System')) ?></h5>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;">
                            <canvas id="responseTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><?= h($mlSupport->translate('Error Rate by System')) ?></h5>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;">
                            <canvas id="errorRateChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Issues Table -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold"><?= h($mlSupport->translate('Top System Issues')) ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0" id="topIssues">
                        <thead>
                            <tr>
                                <th><?= h($mlSupport->translate('System')) ?></th>
                                <th><?= h($mlSupport->translate('Issue')) ?></th>
                                <th><?= h($mlSupport->translate('Occurrences')) ?></th>
                                <th><?= h($mlSupport->translate('Avg Resolution')) ?></th>
                                <th><?= h($mlSupport->translate('Last Seen')) ?></th>
                                <th><?= h($mlSupport->translate('Trend')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Issues will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= get_admin_asset_url('chart.min.js', 'js') ?>"></script>
<script>
const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';

function h(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

let currentTimeRange = '24h';
let alertTrendsChart, alertDistributionChart, responseTimeChart, errorRateChart;

document.addEventListener('DOMContentLoaded', function() {
    initCharts();
    loadAnalytics();
});

function initCharts() {
    const commonOptions = {
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
        }
    };

    // Alert Trends Chart
    const trendsCtx = document.getElementById('alertTrendsChart').getContext('2d');
    alertTrendsChart = new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: '<?= h($mlSupport->translate('Critical')) ?>',
                data: [],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: '<?= h($mlSupport->translate('Warning')) ?>',
                data: [],
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: commonOptions
    });

    // Alert Distribution Chart
    const distributionCtx = document.getElementById('alertDistributionChart').getContext('2d');
    alertDistributionChart = new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: [
                '<?= h($mlSupport->translate('Critical')) ?>', 
                '<?= h($mlSupport->translate('Warning')) ?>', 
                '<?= h($mlSupport->translate('Info')) ?>'
            ],
            datasets: [{
                data: [],
                backgroundColor: [
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(13, 202, 240, 0.8)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            ...commonOptions,
            cutout: '70%'
        }
    });

    // Response Time Chart
    const responseCtx = document.getElementById('responseTimeChart').getContext('2d');
    responseTimeChart = new Chart(responseCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: '<?= h($mlSupport->translate('Avg Response (ms)')) ?>',
                data: [],
                backgroundColor: 'rgba(13, 110, 253, 0.8)',
                borderRadius: 5
            }]
        },
        options: commonOptions
    });

    // Error Rate Chart
    const errorCtx = document.getElementById('errorRateChart').getContext('2d');
    errorRateChart = new Chart(errorCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: '<?= h($mlSupport->translate('Error Rate (%)')) ?>',
                data: [],
                backgroundColor: 'rgba(220, 53, 69, 0.8)',
                borderRadius: 5
            }]
        },
        options: commonOptions
    });
}

function loadAnalytics() {
    fetch(`../api/alert_analytics.php?range=${currentTimeRange}&csrf_token=${CSRF_TOKEN}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' || data.summary) {
                updateDashboard(data);
                updateCharts(data);
                updateTopIssues(data.topIssues || []);
            }
        })
        .catch(error => console.error('Error loading analytics:', error));
}

function updateDashboard(data) {
    document.getElementById('criticalCount').textContent = data.summary.critical;
    document.getElementById('warningCount').textContent = data.summary.warning;
    document.getElementById('avgResolutionTime').textContent = data.summary.avgResolutionTime;
    document.getElementById('alertRate').textContent = data.summary.alertRate;

    updateTrend('criticalTrend', data.trends.critical);
    updateTrend('warningTrend', data.trends.warning);
    updateTrend('resolutionTrend', data.trends.resolution);
    updateTrend('alertRateTrend', data.trends.alertRate);
}

function updateTrend(elementId, trend) {
    const element = document.getElementById(elementId);
    const trendValue = parseFloat(trend);
    const absValue = Math.abs(trendValue);
    
    element.innerHTML = `${absValue}% <i class="fas fa-arrow-${trendValue >= 0 ? 'up' : 'down'}"></i>`;
    element.className = `d-block fw-bold text-${trendValue > 0 ? 'danger' : 'success'}`;
}

function updateCharts(data) {
    if (data.charts.trends) {
        alertTrendsChart.data.labels = data.charts.trends.labels;
        alertTrendsChart.data.datasets[0].data = data.charts.trends.critical;
        alertTrendsChart.data.datasets[1].data = data.charts.trends.warning;
        alertTrendsChart.update();
    }

    if (data.charts.distribution) {
        alertDistributionChart.data.datasets[0].data = [
            data.charts.distribution.critical,
            data.charts.distribution.warning,
            data.charts.distribution.info
        ];
        alertDistributionChart.update();
    }

    if (data.charts.responseTimes) {
        responseTimeChart.data.labels = data.charts.responseTimes.labels;
        responseTimeChart.data.datasets[0].data = data.charts.responseTimes.values;
        responseTimeChart.update();
    }

    if (data.charts.errorRates) {
        errorRateChart.data.labels = data.charts.errorRates.labels;
        errorRateChart.data.datasets[0].data = data.charts.errorRates.values;
        errorRateChart.update();
    }
}

function updateTopIssues(issues) {
    const tbody = document.querySelector('#topIssues tbody');
    if (issues.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted"><?= h($mlSupport->translate('No major issues recorded')) ?></td></tr>`;
        return;
    }

    tbody.innerHTML = issues.map(issue => `
        <tr>
            <td class="fw-bold text-primary">${h(issue.system)}</td>
            <td>${h(issue.issue)}</td>
            <td class="text-center">${h(issue.occurrences)}</td>
            <td>${h(issue.avgResolutionTime)}</td>
            <td class="small text-muted">${h(issue.lastOccurrence)}</td>
            <td>
                <span class="badge bg-${issue.trend > 0 ? 'danger-light text-danger' : 'success-light text-success'}">
                    ${Math.abs(issue.trend)}%
                    <i class="fas fa-arrow-${issue.trend > 0 ? 'up' : 'down'} ms-1"></i>
                </span>
            </td>
        </tr>
    `).join('');
}

function changeTimeRange(range) {
    currentTimeRange = range;
    refreshAnalytics();
}

function refreshAnalytics() {
    loadAnalytics();
}

function exportAnalytics() {
    window.location.href = `../api/export_alert_analytics.php?range=${currentTimeRange}&csrf_token=${CSRF_TOKEN}`;
}

// Refresh every 5 minutes
setInterval(refreshAnalytics, 300000);
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
    <script src="<?= get_admin_asset_url('chart.min.js', 'js') ?>"></script>

