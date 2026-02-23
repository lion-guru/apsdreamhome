<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/public/css/pages.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2962ff;
            --secondary-color: #4CAF50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --card-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --card-shadow-hover: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: #333;
        }

        /* Header */
        .agent-header {
            background: linear-gradient(135deg, var(--primary-color), #1976d2);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .welcome-section h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            opacity: 0.9;
            font-size: 1rem;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.3);
            position: relative;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .online-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 16px;
            height: 16px;
            background: var(--secondary-color);
            border: 3px solid white;
            border-radius: 50%;
        }

        .user-details h6 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .user-details p {
            opacity: 0.8;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .performance-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow-hover);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }

        .stat-card.sales { border-left-color: var(--primary-color); }
        .stat-card.commission { border-left-color: var(--secondary-color); }
        .stat-card.customers { border-left-color: var(--info-color); }
        .stat-card.leads { border-left-color: var(--warning-color); }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }

        .stat-icon.primary { background: rgba(41, 98, 255, 0.1); color: var(--primary-color); }
        .stat-icon.success { background: rgba(76, 175, 80, 0.1); color: var(--secondary-color); }
        .stat-icon.info { background: rgba(23, 162, 184, 0.1); color: var(--info-color); }
        .stat-icon.warning { background: rgba(255, 152, 0, 0.1); color: var(--warning-color); }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 2;
        }

        .stat-change {
            font-size: 0.8rem;
            margin-top: 0.5rem;
            position: relative;
            z-index: 2;
        }

        .stat-change.positive { color: var(--secondary-color); }
        .stat-change.negative { color: var(--danger-color); }

        /* Charts Section */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        /* Activities Section */
        .activities-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background: rgba(41, 98, 255, 0.02);
            border-radius: 8px;
            margin: 0 -1rem;
            padding: 1rem;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .activity-icon.lead { background: rgba(41, 98, 255, 0.1); color: var(--primary-color); }
        .activity-icon.sale { background: rgba(76, 175, 80, 0.1); color: var(--secondary-color); }
        .activity-icon.commission { background: rgba(255, 152, 0, 0.1); color: var(--warning-color); }

        .activity-content h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .activity-content p {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.25rem;
        }

        .activity-time {
            font-size: 0.75rem;
            color: #999;
        }

        /* Quick Actions */
        .actions-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            border-radius: 12px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .action-btn.primary {
            background: linear-gradient(135deg, rgba(41, 98, 255, 0.1), rgba(25, 118, 210, 0.1));
            border-color: var(--primary-color);
        }

        .action-btn.primary:hover {
            background: linear-gradient(135deg, var(--primary-color), #1976d2);
            color: white;
        }

        .action-btn.success {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(46, 125, 50, 0.1));
            border-color: var(--secondary-color);
        }

        .action-btn.success:hover {
            background: linear-gradient(135deg, var(--secondary-color), #2e7d32);
            color: white;
        }

        .action-btn.info {
            background: linear-gradient(135deg, rgba(23, 162, 184, 0.1), rgba(0, 131, 143, 0.1));
            border-color: var(--info-color);
        }

        .action-btn.info:hover {
            background: linear-gradient(135deg, var(--info-color), #008391);
            color: white;
        }

        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .action-content h5 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .action-content p {
            font-size: 0.8rem;
            color: #666;
            margin: 0;
        }

        /* Performance Section */
        .performance-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .performance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .performance-item {
            text-align: center;
            padding: 1rem;
            border-radius: 12px;
            background: var(--light-bg);
        }

        .performance-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .performance-label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Main Content */
        .dashboard-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }

            .dashboard-content {
                padding: 1rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .performance-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card {
            animation: fadeInUp 0.6s ease-out;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

        /* Loading States */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="agent-header">
        <div class="header-content">
            <div class="welcome-section">
                <h1>Welcome back, {{ $agent->name ?? 'Agent' }}! 👋</h1>
                <p>Here's your real estate dashboard overview</p>
            </div>
            <div class="user-profile">
                <div class="user-avatar">
                    <img src="{{ $agent->profile_image ?? 'https://ui-avatars.com/api/?name=' . urlencode($agent->name) . '&size=60&background=2962ff&color=fff' }}"
                         alt="Profile">
                    <div class="online-indicator"></div>
                </div>
                <div class="user-details">
                    <h6>{{ $agent->name }}</h6>
                    <p><span class="badge bg-primary performance-badge">{{ $performanceMetrics['rank'] }}</span></p>
                    <p>ID: {{ $agent->agent_id ?? $agent->id }}</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Dashboard Content -->
    <main class="dashboard-content">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <!-- Total Sales -->
            <div class="stat-card sales">
                <div class="stat-icon primary">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stat-value">₹{{ number_format($stats['total_sales'], 0) }}</div>
                <div class="stat-label">Total Sales</div>
                <div class="stat-change positive">
                    <i class="bi bi-graph-up"></i> {{ $performanceMetrics['sales_growth'] }}% this month
                </div>
            </div>

            <!-- Commission Earned -->
            <div class="stat-card commission">
                <div class="stat-icon success">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-value">₹{{ number_format($stats['commission_earned'], 0) }}</div>
                <div class="stat-label">Commission Earned</div>
                <div class="stat-change positive">
                    <i class="bi bi-plus-circle"></i> From {{ $stats['total_customers'] }} customers
                </div>
            </div>

            <!-- Total Customers -->
            <div class="stat-card customers">
                <div class="stat-icon info">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-value">{{ number_format($stats['total_customers']) }}</div>
                <div class="stat-label">Total Customers</div>
                <div class="stat-change positive">
                    <i class="bi bi-person-check"></i> {{ $stats['conversion_rate'] }}% conversion rate
                </div>
            </div>

            <!-- Pending Leads -->
            <div class="stat-card leads">
                <div class="stat-icon warning">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="stat-value">{{ number_format($stats['pending_leads']) }}</div>
                <div class="stat-label">Pending Leads</div>
                <div class="stat-change {{ $stats['pending_leads'] > 10 ? 'negative' : 'positive' }}">
                    <i class="bi bi-exclamation-triangle"></i> {{ $stats['active_properties'] }} active properties
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-grid">
            <!-- Monthly Performance Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Monthly Performance</h3>
                    <span class="badge bg-success">{{ $stats['target_progress'] }}% of target</span>
                </div>
                <canvas id="monthlyChart" width="400" height="200"></canvas>
            </div>

            <!-- Target Progress -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Monthly Target Progress</h3>
                    <span class="badge bg-primary">{{ $stats['target_progress'] }}%</span>
                </div>
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>₹{{ number_format($stats['monthly_sales'], 0) }}</span>
                        <span>₹{{ number_format($stats['monthly_target'], 0) }}</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-gradient-primary" role="progressbar"
                             style="width: {!! $stats['target_progress'] !!}%"
                             aria-valuenow="{{ $stats['target_progress'] }}"
                             aria-valuemin="0" aria-valuemax="100">
                            {{ $stats['target_progress'] }}%
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            ₹{{ number_format($stats['monthly_target'] - $stats['monthly_sales'], 0) }} remaining to reach target
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions-section">
            <h3 class="chart-title mb-4"><i class="bi bi-lightning me-2 text-warning"></i>Quick Actions</h3>
            <div class="action-buttons">
                <a href="{{ route('agent.leads') }}" class="action-btn primary">
                    <div class="action-icon">
                        <i class="bi bi-list-ul"></i>
                    </div>
                    <div class="action-content">
                        <h5>My Leads</h5>
                        <p>Manage assigned leads</p>
                    </div>
                </a>

                <a href="{{ route('agent.customers.add') }}" class="action-btn success">
                    <div class="action-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="action-content">
                        <h5>Add Customer</h5>
                        <p>Register new customer</p>
                    </div>
                </a>

                <a href="{{ route('agent.properties') }}" class="action-btn info">
                    <div class="action-icon">
                        <i class="bi bi-house"></i>
                    </div>
                    <div class="action-content">
                        <h5>My Properties</h5>
                        <p>Manage listings</p>
                    </div>
                </a>

                <a href="{{ route('agent.marketing') }}" class="action-btn primary">
                    <div class="action-icon">
                        <i class="bi bi-share"></i>
                    </div>
                    <div class="action-content">
                        <h5>Marketing Tools</h5>
                        <p>Share links & materials</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="performance-section">
            <h3 class="chart-title mb-4"><i class="bi bi-graph-up me-2 text-primary"></i>Performance Metrics</h3>
            <div class="performance-grid">
                <div class="performance-item">
                    <div class="performance-value">{{ $performanceMetrics['avg_response_time'] }}h</div>
                    <div class="performance-label">Avg Response Time</div>
                </div>
                <div class="performance-item">
                    <div class="performance-value">{{ $performanceMetrics['customer_satisfaction'] }}★</div>
                    <div class="performance-label">Customer Rating</div>
                </div>
                <div class="performance-item">
                    <div class="performance-value">{{ $performanceMetrics['performance_score'] }}%</div>
                    <div class="performance-label">Performance Score</div>
                </div>
                <div class="performance-item">
                    <div class="performance-value">₹{{ number_format($stats['avg_property_price'], 0) }}</div>
                    <div class="performance-label">Avg Property Price</div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="activities-section">
            <h3 class="chart-title mb-4"><i class="bi bi-activity me-2 text-info"></i>Recent Activities</h3>
            @if(count($recentActivities) > 0)
                @foreach($recentActivities as $activity)
                <div class="activity-item">
                    <div class="activity-icon {{ $activity['type'] }}">
                        <i class="bi bi-{{ $activity['icon'] }}"></i>
                    </div>
                    <div class="activity-content">
                        <h4>{{ $activity['title'] }}</h4>
                        <p>{{ $activity['description'] }}</p>
                        <small class="activity-time">{{ \Carbon\Carbon::parse($activity['date'])->diffForHumans() }}</small>
                    </div>
                </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <i class="bi bi-activity text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No recent activities</p>
                </div>
            @endif
        </div>

        <!-- Upcoming Tasks -->
        @if($upcomingTasks['total_upcoming'] > 0)
        <div class="activities-section">
            <h3 class="chart-title mb-4"><i class="bi bi-calendar-check me-2 text-success"></i>Upcoming Tasks ({{ $upcomingTasks['total_upcoming'] }})</h3>

            @if(count($upcomingTasks['site_visits']) > 0)
                <h5 class="mb-3"><i class="bi bi-geo-alt me-2"></i>Site Visits</h5>
                @foreach($upcomingTasks['site_visits'] as $visit)
                <div class="activity-item">
                    <div class="activity-icon" style="background: rgba(76, 175, 80, 0.1); color: var(--secondary-color);">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div class="activity-content">
                        <h4>Site Visit Scheduled</h4>
                        <p>{{ $visit->name ?? 'Unknown' }} - {{ $visit->phone ?? 'N/A' }}</p>
                        <small class="activity-time">{{ \Carbon\Carbon::parse($visit->visit_date)->format('M j, Y g:i A') }}</small>
                    </div>
                </div>
                @endforeach
            @endif

            @if(count($upcomingTasks['follow_ups']) > 0)
                <h5 class="mb-3 mt-4"><i class="bi bi-telephone me-2"></i>Follow-up Calls</h5>
                @foreach($upcomingTasks['follow_ups'] as $followup)
                <div class="activity-item">
                    <div class="activity-icon" style="background: rgba(255, 152, 0, 0.1); color: var(--warning-color);">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div class="activity-content">
                        <h4>Follow-up Call</h4>
                        <p>{{ $followup->name ?? 'Unknown' }} - {{ $followup->phone ?? 'N/A' }}</p>
                        <small class="activity-time">{{ \Carbon\Carbon::parse($followup->next_followup_date)->format('M j, Y') }}</small>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
        @endif
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        @php
            $monthlyJson = json_encode($monthlyData);
        @endphp

        // Monthly Performance Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyData = {!! $monthlyJson !!};

        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => item.month),
                datasets: [{
                    label: 'Sales (₹)',
                    data: monthlyData.map(item => item.sales),
                    borderColor: '#2962ff',
                    backgroundColor: 'rgba(41, 98, 255, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                }, {
                    label: 'Leads',
                    data: monthlyData.map(item => item.leads),
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Sales (₹)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Leads'
                        },
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                }
            }
        });

        // Auto-refresh data every 5 minutes
        setInterval(function() {
            // Could add real-time updates here
            console.log('Agent dashboard data refresh check...');
        }, 300000);

        // Add loading states for actions
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const icon = this.querySelector('.action-icon i');
                const originalIcon = icon.className;

                // Add loading state
                icon.className = 'bi bi-arrow-repeat loading';

                // Reset after 2 seconds (in case of redirect)
                setTimeout(() => {
                    icon.className = originalIcon;
                }, 2000);
            });
        });
    </script>
</body>
</html>
