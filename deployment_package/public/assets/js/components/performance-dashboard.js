// Performance Dashboard Component
class PerformanceDashboard {
    constructor(container, options = {}) {
        this.container = container;
        this.options = {
            refreshInterval: options.refreshInterval || 5000,
            maxDataPoints: options.maxDataPoints || 100,
            ...options
        };
        
        this.metrics = {
            responseTime: [],
            throughput: [],
            errorRate: [],
            cpuUsage: [],
            memoryUsage: [],
            diskUsage: [],
            networkIO: [],
            activeUsers: [],
            databaseConnections: [],
            cacheHitRate: [],
            queueSize: []
        };
        
        this.charts = {};
        this.alerts = [];
        this.thresholds = {
            responseTime: 1000,
            errorRate: 5,
            cpuUsage: 80,
            memoryUsage: 85,
            diskUsage: 90,
            databaseConnections: 100,
            cacheHitRate: 70
        };
        
        this.init();
    }
    
    init() {
        this.createLayout();
        this.createCharts();
        this.startMonitoring();
        this.setupEventListeners();
    }
    
    createLayout() {
        this.container.innerHTML = `
            <div class="performance-dashboard">
                <div class="dashboard-header">
                    <h2>Performance Monitoring Dashboard</h2>
                    <div class="dashboard-controls">
                        <button id="refresh-btn" class="btn btn-primary">Refresh</button>
                        <button id="pause-btn" class="btn btn-secondary">Pause</button>
                        <select id="time-range" class="form-select">
                            <option value="1h">Last Hour</option>
                            <option value="6h">Last 6 Hours</option>
                            <option value="24h">Last 24 Hours</option>
                            <option value="7d">Last 7 Days</option>
                        </select>
                    </div>
                </div>
                
                <div class="dashboard-metrics">
                    <div class="metric-cards">
                        <div class="metric-card">
                            <div class="metric-title">Response Time</div>
                            <div class="metric-value" id="response-time-value">0ms</div>
                            <div class="metric-change" id="response-time-change">0%</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-title">Throughput</div>
                            <div class="metric-value" id="throughput-value">0 req/s</div>
                            <div class="metric-change" id="throughput-change">0%</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-title">Error Rate</div>
                            <div class="metric-value" id="error-rate-value">0%</div>
                            <div class="metric-change" id="error-rate-change">0%</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-title">Active Users</div>
                            <div class="metric-value" id="active-users-value">0</div>
                            <div class="metric-change" id="active-users-change">0%</div>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-charts">
                    <div class="chart-container">
                        <h3>Response Time Trend</h3>
                        <canvas id="response-time-chart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3>System Resources</h3>
                        <canvas id="system-resources-chart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3>Database Performance</h3>
                        <canvas id="database-performance-chart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3>Cache Performance</h3>
                        <canvas id="cache-performance-chart"></canvas>
                    </div>
                </div>
                
                <div class="dashboard-alerts">
                    <h3>Active Alerts</h3>
                    <div id="alerts-container"></div>
                </div>
                
                <div class="dashboard-details">
                    <h3>Performance Details</h3>
                    <div class="details-grid">
                        <div class="detail-section">
                            <h4>Application Metrics</h4>
                            <div class="detail-item">
                                <label>Average Response Time:</label>
                                <span id="avg-response-time">0ms</span>
                            </div>
                            <div class="detail-item">
                                <label>95th Percentile:</label>
                                <span id="p95-response-time">0ms</span>
                            </div>
                            <div class="detail-item">
                                <label>99th Percentile:</label>
                                <span id="p99-response-time">0ms</span>
                            </div>
                            <div class="detail-item">
                                <label>Requests per Second:</label>
                                <span id="rps">0</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4>System Resources</h4>
                            <div class="detail-item">
                                <label>CPU Usage:</label>
                                <span id="cpu-usage">0%</span>
                            </div>
                            <div class="detail-item">
                                <label>Memory Usage:</label>
                                <span id="memory-usage">0%</span>
                            </div>
                            <div class="detail-item">
                                <label>Disk Usage:</label>
                                <span id="disk-usage">0%</span>
                            </div>
                            <div class="detail-item">
                                <label>Network I/O:</label>
                                <span id="network-io">0 MB/s</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4>Database Metrics</h4>
                            <div class="detail-item">
                                <label>Active Connections:</label>
                                <span id="db-connections">0</span>
                            </div>
                            <div class="detail-item">
                                <label>Query Time:</label>
                                <span id="query-time">0ms</span>
                            </div>
                            <div class="detail-item">
                                <label>Slow Queries:</label>
                                <span id="slow-queries">0</span>
                            </div>
                            <div class="detail-item">
                                <label>Cache Hit Rate:</label>
                                <span id="cache-hit-rate">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        this.addStyles();
    }
    
    createCharts() {
        // Response Time Chart
        const responseTimeCtx = document.getElementById('response-time-chart').getContext('2d');
        this.charts.responseTime = new Chart(responseTimeCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Response Time (ms)',
                    data: [],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Time'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Response Time (ms)'
                        }
                    }
                }
            }
        });
        
        // System Resources Chart
        const systemResourcesCtx = document.getElementById('system-resources-chart').getContext('2d');
        this.charts.systemResources = new Chart(systemResourcesCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'CPU Usage (%)',
                        data: [],
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1
                    },
                    {
                        label: 'Memory Usage (%)',
                        data: [],
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        tension: 0.1
                    },
                    {
                        label: 'Disk Usage (%)',
                        data: [],
                        borderColor: 'rgb(255, 205, 86)',
                        backgroundColor: 'rgba(255, 205, 86, 0.2)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Time'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Usage (%)'
                        },
                        min: 0,
                        max: 100
                    }
                }
            }
        });
        
        // Database Performance Chart
        const databasePerformanceCtx = document.getElementById('database-performance-chart').getContext('2d');
        this.charts.databasePerformance = new Chart(databasePerformanceCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Database Connections',
                        data: [],
                        borderColor: 'rgb(153, 102, 255)',
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        tension: 0.1
                    },
                    {
                        label: 'Query Time (ms)',
                        data: [],
                        borderColor: 'rgb(255, 159, 64)',
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Time'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Value'
                        }
                    }
                }
            }
        });
        
        // Cache Performance Chart
        const cachePerformanceCtx = document.getElementById('cache-performance-chart').getContext('2d');
        this.charts.cachePerformance = new Chart(cachePerformanceCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Cache Hit Rate (%)',
                        data: [],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    },
                    {
                        label: 'Queue Size',
                        data: [],
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Time'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Value'
                        }
                    }
                }
            }
        });
    }
    
    startMonitoring() {
        this.isMonitoring = true;
        this.fetchMetrics();
        this.monitoringInterval = setInterval(() => {
            if (this.isMonitoring) {
                this.fetchMetrics();
            }
        }, this.options.refreshInterval);
    }
    
    async fetchMetrics() {
        try {
            const response = await fetch('/api/v2.0/monitoring/metrics');
            const data = await response.json();
            
            this.updateMetrics(data);
            this.updateCharts(data);
            this.checkAlerts(data);
        } catch (error) {
            console.error('Error fetching metrics:', error);
            this.showError('Failed to fetch metrics');
        }
    }
    
    updateMetrics(data) {
        const timestamp = new Date().toLocaleTimeString();
        
        // Update metric values
        document.getElementById('response-time-value').textContent = `${data.responseTime}ms`;
        document.getElementById('throughput-value').textContent = `${data.throughput} req/s`;
        document.getElementById('error-rate-value').textContent = `${data.errorRate}%`;
        document.getElementById('active-users-value').textContent = data.activeUsers;
        
        // Update detail values
        document.getElementById('avg-response-time').textContent = `${data.avgResponseTime}ms`;
        document.getElementById('p95-response-time').textContent = `${data.p95ResponseTime}ms`;
        document.getElementById('p99-response-time').textContent = `${data.p99ResponseTime}ms`;
        document.getElementById('rps').textContent = data.rps;
        
        document.getElementById('cpu-usage').textContent = `${data.cpuUsage}%`;
        document.getElementById('memory-usage').textContent = `${data.memoryUsage}%`;
        document.getElementById('disk-usage').textContent = `${data.diskUsage}%`;
        document.getElementById('network-io').textContent = `${data.networkIO} MB/s`;
        
        document.getElementById('db-connections').textContent = data.dbConnections;
        document.getElementById('query-time').textContent = `${data.queryTime}ms`;
        document.getElementById('slow-queries').textContent = data.slowQueries;
        document.getElementById('cache-hit-rate').textContent = `${data.cacheHitRate}%`;
        
        // Store metrics for charts
        this.metrics.responseTime.push({ time: timestamp, value: data.responseTime });
        this.metrics.throughput.push({ time: timestamp, value: data.throughput });
        this.metrics.errorRate.push({ time: timestamp, value: data.errorRate });
        this.metrics.cpuUsage.push({ time: timestamp, value: data.cpuUsage });
        this.metrics.memoryUsage.push({ time: timestamp, value: data.memoryUsage });
        this.metrics.diskUsage.push({ time: timestamp, value: data.diskUsage });
        this.metrics.networkIO.push({ time: timestamp, value: data.networkIO });
        this.metrics.activeUsers.push({ time: timestamp, value: data.activeUsers });
        this.metrics.databaseConnections.push({ time: timestamp, value: data.dbConnections });
        this.metrics.cacheHitRate.push({ time: timestamp, value: data.cacheHitRate });
        this.metrics.queueSize.push({ time: timestamp, value: data.queueSize });
        
        // Limit data points
        Object.keys(this.metrics).forEach(key => {
            if (this.metrics[key].length > this.options.maxDataPoints) {
                this.metrics[key] = this.metrics[key].slice(-this.options.maxDataPoints);
            }
        });
    }
    
    updateCharts(data) {
        const timestamp = new Date().toLocaleTimeString();
        
        // Update Response Time Chart
        this.charts.responseTime.data.labels.push(timestamp);
        this.charts.responseTime.data.datasets[0].data.push(data.responseTime);
        
        // Update System Resources Chart
        this.charts.systemResources.data.labels.push(timestamp);
        this.charts.systemResources.data.datasets[0].data.push(data.cpuUsage);
        this.charts.systemResources.data.datasets[1].data.push(data.memoryUsage);
        this.charts.systemResources.data.datasets[2].data.push(data.diskUsage);
        
        // Update Database Performance Chart
        this.charts.databasePerformance.data.labels.push(timestamp);
        this.charts.databasePerformance.data.datasets[0].data.push(data.dbConnections);
        this.charts.databasePerformance.data.datasets[1].data.push(data.queryTime);
        
        // Update Cache Performance Chart
        this.charts.cachePerformance.data.labels.push(timestamp);
        this.charts.cachePerformance.data.datasets[0].data.push(data.cacheHitRate);
        this.charts.cachePerformance.data.datasets[1].data.push(data.queueSize);
        
        // Limit data points in charts
        Object.values(this.charts).forEach(chart => {
            if (chart.data.labels.length > this.options.maxDataPoints) {
                chart.data.labels = chart.data.labels.slice(-this.options.maxDataPoints);
                chart.data.datasets.forEach(dataset => {
                    dataset.data = dataset.data.slice(-this.options.maxDataPoints);
                });
            }
            chart.update();
        });
    }
    
    checkAlerts(data) {
        const newAlerts = [];
        
        // Check response time
        if (data.responseTime > this.thresholds.responseTime) {
            newAlerts.push({
                type: 'warning',
                message: `Response time (${data.responseTime}ms) exceeds threshold (${this.thresholds.responseTime}ms)`,
                timestamp: new Date().toISOString()
            });
        }
        
        // Check error rate
        if (data.errorRate > this.thresholds.errorRate) {
            newAlerts.push({
                type: 'critical',
                message: `Error rate (${data.errorRate}%) exceeds threshold (${this.thresholds.errorRate}%)`,
                timestamp: new Date().toISOString()
            });
        }
        
        // Check CPU usage
        if (data.cpuUsage > this.thresholds.cpuUsage) {
            newAlerts.push({
                type: 'warning',
                message: `CPU usage (${data.cpuUsage}%) exceeds threshold (${this.thresholds.cpuUsage}%)`,
                timestamp: new Date().toISOString()
            });
        }
        
        // Check memory usage
        if (data.memoryUsage > this.thresholds.memoryUsage) {
            newAlerts.push({
                type: 'critical',
                message: `Memory usage (${data.memoryUsage}%) exceeds threshold (${this.thresholds.memoryUsage}%)`,
                timestamp: new Date().toISOString()
            });
        }
        
        // Check disk usage
        if (data.diskUsage > this.thresholds.diskUsage) {
            newAlerts.push({
                type: 'critical',
                message: `Disk usage (${data.diskUsage}%) exceeds threshold (${this.thresholds.diskUsage}%)`,
                timestamp: new Date().toISOString()
            });
        }
        
        // Update alerts
        this.updateAlerts(newAlerts);
    }
    
    updateAlerts(newAlerts) {
        const alertsContainer = document.getElementById('alerts-container');
        
        if (newAlerts.length === 0) {
            alertsContainer.innerHTML = '<p>No active alerts</p>';
            return;
        }
        
        alertsContainer.innerHTML = newAlerts.map(alert => `
            <div class="alert alert-${alert.type}">
                <div class="alert-message">${alert.message}</div>
                <div class="alert-time">${new Date(alert.timestamp).toLocaleString()}</div>
            </div>
        `).join('');
    }
    
    setupEventListeners() {
        // Refresh button
        document.getElementById('refresh-btn').addEventListener('click', () => {
            this.fetchMetrics();
        });
        
        // Pause/Resume button
        document.getElementById('pause-btn').addEventListener('click', () => {
            this.isMonitoring = !this.isMonitoring;
            document.getElementById('pause-btn').textContent = this.isMonitoring ? 'Pause' : 'Resume';
        });
        
        // Time range selector
        document.getElementById('time-range').addEventListener('change', (e) => {
            this.changeTimeRange(e.target.value);
        });
    }
    
    changeTimeRange(range) {
        // This would fetch historical data based on the selected time range
        console.log('Changing time range to:', range);
        // Implementation would depend on your API endpoints
    }
    
    showError(message) {
        const alertsContainer = document.getElementById('alerts-container');
        alertsContainer.innerHTML = `
            <div class="alert alert-danger">
                <div class="alert-message">${message}</div>
            </div>
        `;
    }
    
    pause() {
        this.isMonitoring = false;
    }
    
    resume() {
        this.isMonitoring = true;
    }
    
    destroy() {
        if (this.monitoringInterval) {
            clearInterval(this.monitoringInterval);
        }
        
        Object.values(this.charts).forEach(chart => {
            chart.destroy();
        });
    }
    
    addStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .performance-dashboard {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                padding: 20px;
                background: #f8f9fa;
            }
            
            .dashboard-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding: 20px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .dashboard-controls {
                display: flex;
                gap: 10px;
                align-items: center;
            }
            
            .metric-cards {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 20px;
            }
            
            .metric-card {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                text-align: center;
            }
            
            .metric-title {
                font-size: 14px;
                color: #666;
                margin-bottom: 8px;
            }
            
            .metric-value {
                font-size: 24px;
                font-weight: bold;
                color: #333;
                margin-bottom: 4px;
            }
            
            .metric-change {
                font-size: 12px;
                color: #666;
            }
            
            .dashboard-charts {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
                gap: 20px;
                margin-bottom: 20px;
            }
            
            .chart-container {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .chart-container h3 {
                margin-top: 0;
                margin-bottom: 20px;
                color: #333;
            }
            
            .dashboard-alerts {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                margin-bottom: 20px;
            }
            
            .dashboard-alerts h3 {
                margin-top: 0;
                margin-bottom: 20px;
                color: #333;
            }
            
            .alert {
                padding: 12px;
                border-radius: 4px;
                margin-bottom: 10px;
            }
            
            .alert-warning {
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                color: #856404;
            }
            
            .alert-critical {
                background: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
            }
            
            .alert-message {
                font-weight: bold;
                margin-bottom: 4px;
            }
            
            .alert-time {
                font-size: 12px;
                color: #666;
            }
            
            .dashboard-details {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .dashboard-details h3 {
                margin-top: 0;
                margin-bottom: 20px;
                color: #333;
            }
            
            .details-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
            }
            
            .detail-section h4 {
                margin-top: 0;
                margin-bottom: 15px;
                color: #333;
            }
            
            .detail-item {
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
                padding: 8px;
                background: #f8f9fa;
                border-radius: 4px;
            }
            
            .detail-item label {
                font-weight: bold;
                color: #666;
            }
            
            .btn {
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
            }
            
            .btn-primary {
                background: #007bff;
                color: white;
            }
            
            .btn-secondary {
                background: #6c757d;
                color: white;
            }
            
            .form-select {
                padding: 8px 12px;
                border: 1px solid #ccc;
                border-radius: 4px;
                font-size: 14px;
            }
        `;
        document.head.appendChild(style);
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PerformanceDashboard;
}
