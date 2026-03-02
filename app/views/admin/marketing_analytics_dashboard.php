<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketing Analytics Dashboard - APS Dream Home</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #333;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        .sidebar {
            background: #2c3e50;
            color: white;
            padding: 20px;
        }
        
        .sidebar h2 {
            margin-bottom: 30px;
            font-size: 24px;
        }
        
        .nav-item {
            padding: 12px 0;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .nav-item:hover {
            background: #34495e;
        }
        
        .nav-item.active {
            background: #3498db;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 32px;
            color: #2c3e50;
        }
        
        .date-range {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .date-range select, .date-range input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .metric-card:hover {
            transform: translateY(-5px);
        }
        
        .metric-card h3 {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .metric-value {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .metric-change {
            font-size: 14px;
            font-weight: bold;
        }
        
        .metric-change.positive {
            color: #27ae60;
        }
        
        .metric-change.negative {
            color: #e74c3c;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .chart-card h3 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .campaigns-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .campaigns-table h3 {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            margin: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-draft {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>📊 Marketing</h2>
            <div class="nav-item active" onclick="showSection('overview')">📈 Overview</div>
            <div class="nav-item" onclick="showSection('campaigns')">🎯 Campaigns</div>
            <div class="nav-item" onclick="showSection('analytics')">📊 Analytics</div>
            <div class="nav-item" onclick="showSection('reports')">📋 Reports</div>
            <div class="nav-item" onclick="showSection('settings')">⚙️ Settings</div>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Marketing Analytics Dashboard</h1>
                <div class="date-range">
                    <select id="dateRange">
                        <option value="7">Last 7 days</option>
                        <option value="30" selected>Last 30 days</option>
                        <option value="90">Last 90 days</option>
                        <option value="365">Last year</option>
                    </select>
                    <input type="date" id="startDate">
                    <input type="date" id="endDate">
                    <button class="btn btn-primary" onclick="refreshData()">Refresh</button>
                </div>
            </div>
            
            <div id="overview-section">
                <div class="metrics-grid">
                    <div class="metric-card">
                        <h3>Total Campaigns</h3>
                        <div class="metric-value" id="totalCampaigns">-</div>
                        <div class="metric-change positive">+12.5%</div>
                    </div>
                    <div class="metric-card">
                        <h3>Active Campaigns</h3>
                        <div class="metric-value" id="activeCampaigns">-</div>
                        <div class="metric-change positive">+8.3%</div>
                    </div>
                    <div class="metric-card">
                        <h3>Total Reach</h3>
                        <div class="metric-value" id="totalReach">-</div>
                        <div class="metric-change positive">+25.7%</div>
                    </div>
                    <div class="metric-card">
                        <h3>Conversions</h3>
                        <div class="metric-value" id="conversions">-</div>
                        <div class="metric-change positive">+18.2%</div>
                    </div>
                </div>
                
                <div class="charts-container">
                    <div class="chart-card">
                        <h3>Campaign Performance</h3>
                        <canvas id="campaignChart"></canvas>
                    </div>
                    <div class="chart-card">
                        <h3>Conversion Funnel</h3>
                        <canvas id="funnelChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div id="campaigns-section" style="display: none;">
                <div class="campaigns-table">
                    <h3>📋 Marketing Campaigns</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Budget</th>
                                <th>Reach</th>
                                <th>Conversions</th>
                                <th>CTR</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="campaignsTableBody">
                            <tr>
                                <td colspan="8" class="loading">Loading campaigns...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initializeDashboard();
            loadDashboardData();
        });
        
        function initializeDashboard() {
            // Set default dates
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - 30);
            
            document.getElementById('endDate').value = endDate.toISOString().split('T')[0];
            document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
            
            // Initialize charts
            initializeCharts();
        }
        
        function initializeCharts() {
            // Campaign Performance Chart
            const campaignCtx = document.getElementById('campaignChart').getContext('2d');
            window.campaignChart = new Chart(campaignCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Reach',
                        data: [],
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Conversions',
                        data: [],
                        borderColor: '#27ae60',
                        backgroundColor: 'rgba(39, 174, 96, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Conversion Funnel Chart
            const funnelCtx = document.getElementById('funnelChart').getContext('2d');
            window.funnelChart = new Chart(funnelCtx, {
                type: 'bar',
                data: {
                    labels: ['Impressions', 'Clicks', 'Leads', 'Conversions'],
                    datasets: [{
                        label: 'Users',
                        data: [],
                        backgroundColor: [
                            '#3498db',
                            '#9b59b6',
                            '#f39c12',
                            '#27ae60'
                        ]
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
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        function loadDashboardData() {
            // Simulate API call to get dashboard data
            setTimeout(() => {
                updateMetrics();
                updateCharts();
                loadCampaigns();
            }, 1000);
        }
        
        function updateMetrics() {
            // Simulate metrics data
            document.getElementById('totalCampaigns').textContent = '24';
            document.getElementById('activeCampaigns').textContent = '8';
            document.getElementById('totalReach').textContent = '125.4K';
            document.getElementById('conversions').textContent = '3,847';
        }
        
        function updateCharts() {
            // Update campaign chart
            const dates = [];
            const reachData = [];
            const conversionData = [];
            
            for (let i = 29; i >= 0; i--) {
                const date = new Date();
                date.setDate(date.getDate() - i);
                dates.push(date.toLocaleDateString());
                reachData.push(Math.floor(Math.random() * 5000) + 2000);
                conversionData.push(Math.floor(Math.random() * 200) + 50);
            }
            
            window.campaignChart.data.labels = dates;
            window.campaignChart.data.datasets[0].data = reachData;
            window.campaignChart.data.datasets[1].data = conversionData;
            window.campaignChart.update();
            
            // Update funnel chart
            window.funnelChart.data.datasets[0].data = [45000, 12000, 4800, 3847];
            window.funnelChart.update();
        }
        
        function loadCampaigns() {
            // Simulate campaigns data
            const campaigns = [
                {
                    name: 'Spring Sale 2024',
                    type: 'Email',
                    status: 'active',
                    budget: '$5,000',
                    reach: '45.2K',
                    conversions: '1,234',
                    ctr: '2.73%'
                },
                {
                    name: 'New Property Listings',
                    type: 'Social',
                    status: 'active',
                    budget: '$3,000',
                    reach: '32.1K',
                    conversions: '892',
                    ctr: '2.78%'
                },
                {
                    name: 'Summer Promotion',
                    type: 'Display',
                    status: 'draft',
                    budget: '$2,500',
                    reach: '0',
                    conversions: '0',
                    ctr: '0%'
                }
            ];
            
            const tbody = document.getElementById('campaignsTableBody');
            tbody.innerHTML = '';
            
            campaigns.forEach(campaign => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${campaign.name}</td>
                    <td>${campaign.type}</td>
                    <td><span class="status-badge status-${campaign.status}">${campaign.status}</span></td>
                    <td>${campaign.budget}</td>
                    <td>${campaign.reach}</td>
                    <td>${campaign.conversions}</td>
                    <td>${campaign.ctr}</td>
                    <td>
                        <button class="btn btn-primary" onclick="viewCampaign(${campaign.id})">View</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        function showSection(section) {
            // Hide all sections
            document.getElementById('overview-section').style.display = 'none';
            document.getElementById('campaigns-section').style.display = 'none';
            
            // Show selected section
            document.getElementById(section + '-section').style.display = 'block';
            
            // Update nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            event.target.classList.add('active');
        }
        
        function refreshData() {
            loadDashboardData();
        }
        
        function viewCampaign(campaignId) {
            // Navigate to campaign details
            console.log('Viewing campaign:', campaignId);
        }
    </script>
</body>
</html>