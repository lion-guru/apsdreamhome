<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associate MLM Dashboard - APS Dream Home</title>
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
        .associate-header {
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
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow-hover);
        }

        .stat-card.team { border-left-color: var(--secondary-color); }
        .stat-card.earnings { border-left-color: var(--success-color); }
        .stat-card.business { border-left-color: var(--warning-color); }
        .stat-card.rank { border-left-color: var(--info-color); }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 1rem;
        }

        .stat-icon.primary { background: rgba(41, 98, 255, 0.1); color: var(--primary-color); }
        .stat-icon.success { background: rgba(76, 175, 80, 0.1); color: var(--secondary-color); }
        .stat-icon.warning { background: rgba(255, 152, 0, 0.1); color: var(--warning-color); }
        .stat-icon.info { background: rgba(33, 150, 243, 0.1); color: var(--info-color); }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-change {
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }

        .stat-change.positive { color: var(--secondary-color); }
        .stat-change.negative { color: var(--danger-color); }

        /* Main Content */
        .dashboard-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

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
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .activity-icon.commission { background: rgba(76, 175, 80, 0.1); color: var(--secondary-color); }
        .activity-icon.referral { background: rgba(41, 98, 255, 0.1); color: var(--primary-color); }
        .activity-icon.training { background: rgba(255, 152, 0, 0.1); color: var(--warning-color); }

        .activity-content h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .activity-content p {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.25rem;
        }

        .activity-time {
            font-size: 0.7rem;
            color: #999;
        }

        /* Rank Progress */
        .rank-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .rank-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .current-rank {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .next-rank {
            font-size: 1rem;
            color: #666;
        }

        .progress {
            height: 12px;
            border-radius: 6px;
            background: #eee;
            margin-bottom: 0.5rem;
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 6px;
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

            .dashboard-content {
                padding: 1rem;
            }

            .stat-value {
                font-size: 1.5rem;
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
    <header class="associate-header">
        <div class="header-content">
            <div class="welcome-section">
                <h1>Welcome back, {{ $associate->name ?? 'Associate' }}! 👋</h1>
                <p>Here's your MLM dashboard overview</p>
            </div>
            <div class="user-profile">
                <img src="/public/assets/images/user/{{ $associate->profile_image ?? 'default-avatar.jpg' }}"
                     alt="Profile" class="user-avatar">
                <div>
                    <div style="font-weight: 600;">{{ $associate->name }}</div>
                    <div style="font-size: 0.8rem; opacity: 0.8;">ID: {{ $associate->associate_id ?? $associate->id }}</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Dashboard Content -->
    <main class="dashboard-content">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <!-- Team Statistics -->
            <div class="stat-card team">
                <div class="stat-icon success">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-value">{{ number_format($teamStats['total_team']) }}</div>
                <div class="stat-label">Total Team Members</div>
                <div class="stat-change positive">
                    <i class="bi bi-arrow-up"></i> {{ $teamStats['active_members'] }} active
                </div>
            </div>

            <!-- Earnings -->
            <div class="stat-card earnings">
                <div class="stat-icon warning">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stat-value">₹{{ number_format($earningsData['total_earnings'], 0) }}</div>
                <div class="stat-label">Total Earnings</div>
                <div class="stat-change positive">
                    <i class="bi bi-plus-circle"></i> ₹{{ number_format($earningsData['pending_commissions'], 0) }} pending
                </div>
            </div>

            <!-- Business Volume -->
            <div class="stat-card business">
                <div class="stat-icon info">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="stat-value">₹{{ number_format($businessReports['total_business'], 0) }}</div>
                <div class="stat-label">Business Volume</div>
                <div class="stat-change positive">
                    <i class="bi bi-bar-chart"></i> {{ count($businessReports['monthly_business']) }} months active
                </div>
            </div>

            <!-- Rank Progress -->
            <div class="stat-card rank">
                <div class="stat-icon primary">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div class="stat-value">{{ $performanceMetrics['rank_progress']['current_rank'] }}</div>
                <div class="stat-label">Current Rank</div>
                <div class="stat-change positive">
                    <i class="bi bi-chevron-double-up"></i> {{ $performanceMetrics['rank_progress']['progress_percentage'] }}% to {{ $performanceMetrics['rank_progress']['next_rank'] }}
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-grid">
            <!-- Earnings Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Monthly Earnings</h3>
                    <span class="badge bg-success">+{{ $performanceMetrics['earnings_growth'] }}%</span>
                </div>
                <canvas id="earningsChart" width="400" height="200"></canvas>
            </div>

            <!-- Team Growth Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Team Distribution</h3>
                    <span class="badge bg-primary">{{ $teamStats['total_levels'] }} levels</span>
                </div>
                <canvas id="teamChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Rank Progress Section -->
        <div class="rank-section">
            <div class="rank-header">
                <div>
                    <h3 class="current-rank">{{ $performanceMetrics['rank_progress']['current_rank'] }}</h3>
                    <p class="next-rank">Next: {{ $performanceMetrics['rank_progress']['next_rank'] }}</p>
                </div>
                <div class="text-end">
                    <div class="h5 mb-0">{{ $performanceMetrics['rank_progress']['progress_percentage'] }}%</div>
                    <small class="text-muted">Progress</small>
                </div>
            </div>
            <div class="progress">
                <div class="progress-bar" role="progressbar"
                     style="width: {!! $performanceMetrics['rank_progress']['progress_percentage'] !!}%"
                     aria-valuenow="{{ $performanceMetrics['rank_progress']['progress_percentage'] }}"
                     aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="mt-2">
                <small class="text-muted">
                    ₹{{ number_format($performanceMetrics['rank_progress']['total_earnings'], 0) }} earned
                    • {{ $performanceMetrics['active_referrals'] }} active referrals
                </small>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="activities-section">
            <h3 class="chart-title mb-4">Recent Activities</h3>
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

        <!-- Quick Actions -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3 class="chart-title mb-3">Quick Actions</h3>
                <div class="d-grid gap-2">
                    <a href="{{ route('associate.team') }}" class="btn btn-outline-primary">
                        <i class="bi bi-people me-2"></i>View Team
                    </a>
                    <a href="{{ route('associate.earnings') }}" class="btn btn-outline-success">
                        <i class="bi bi-cash me-2"></i>View Earnings
                    </a>
                    <a href="{{ route('associate.training') }}" class="btn btn-outline-warning">
                        <i class="bi bi-book me-2"></i>Training Center
                    </a>
                    <a href="{{ route('associate.referrals') }}" class="btn btn-outline-info">
                        <i class="bi bi-person-plus me-2"></i>Referral Program
                    </a>
                </div>
            </div>

            <div class="chart-card">
                <h3 class="chart-title mb-3">Training Progress</h3>
                @if($trainingProgress['total_courses'] > 0)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Overall Progress</small>
                            <small>{{ $trainingProgress['average_progress'] }}%</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: {!! $trainingProgress['average_progress'] !!}%"></div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 text-primary">{{ $trainingProgress['total_courses'] }}</div>
                            <small class="text-muted">Enrolled</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 text-success">{{ $trainingProgress['completed_courses'] }}</div>
                            <small class="text-muted">Completed</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 text-warning">{{ $trainingProgress['in_progress_courses'] }}</div>
                            <small class="text-muted">In Progress</small>
                        </div>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-book text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">No training enrolled</p>
                        <a href="{{ route('associate.training') }}" class="btn btn-sm btn-primary">Browse Courses</a>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        @php
            $earningsJson = json_encode($earningsData['monthly_earnings'] ?? []);
            $teamJson = json_encode($teamStats['level_breakdown'] ?? []);
        @endphp

        // Earnings Chart
        const earningsCtx = document.getElementById('earningsChart').getContext('2d');
        const earningsData = {!! $earningsJson !!};

        new Chart(earningsCtx, {
            type: 'line',
            data: {
                labels: Object.keys(earningsData),
                datasets: [{
                    label: 'Monthly Earnings (₹)',
                    data: Object.values(earningsData),
                    borderColor: '#2962ff',
                    backgroundColor: 'rgba(41, 98, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Team Distribution Chart
        const teamCtx = document.getElementById('teamChart').getContext('2d');
        const teamData = {!! $teamJson !!};

        new Chart(teamCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(teamData).map(level => `Level ${level}`),
                datasets: [{
                    data: Object.values(teamData),
                    backgroundColor: [
                        '#2962ff', '#4CAF50', '#ff9800', '#e91e63',
                        '#9c27b0', '#00bcd4', '#795548', '#607d8b'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Auto-refresh data every 5 minutes
        setInterval(function() {
            // Could add real-time updates here
            console.log('Dashboard data refresh check...');
        }, 300000);
    </script>
</body>
</html>
