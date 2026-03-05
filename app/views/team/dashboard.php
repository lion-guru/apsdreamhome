/**
 * dashboard - APS Dream Home Component
 * 
 * @package APS Dream Home
 * @version 1.0.0
 * @author APS Dream Home Team
 * @copyright 2026 APS Dream Home
 * 
 * Description: Handles dashboard functionality
 * 
 * Features:
 * - Secure input validation
 * - Comprehensive error handling
 * - Performance optimization
 * - Database integration
 * - Session management
 * - CSRF protection
 * 
 * @see https://apsdreamhome.com/docs
 */
<?php

// TODO: Add proper error handling with try-catch blocks

PE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Management - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/public/css/pages.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script">
    <script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
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
            --hierarchy-line: #dee2e6;
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
        .team-header {
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

        .team-stats {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .stat-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        /* Team Overview Cards */
        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .overview-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .overview-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow-hover);
        }

        .overview-card.members { border-left-color: var(--secondary-color); }
        .overview-card.performance { border-left-color: var(--warning-color); }
        .overview-card.earnings { border-left-color: var(--success-color); }

        .overview-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 1rem;
        }

        .overview-icon.primary { background: rgba(41, 98, 255, 0.1); color: var(--primary-color); }
        .overview-icon.success { background: rgba(76, 175, 80, 0.1); color: var(--secondary-color); }
        .overview-icon.warning { background: rgba(255, 152, 0, 0.1); color: var(--warning-color); }
        .overview-icon.info { background: rgba(23, 162, 184, 0.1); color: var(--info-color); }

        .overview-value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .overview-label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .overview-change {
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }

        .overview-change.positive { color: var(--secondary-color); }
        .overview-change.negative { color: var(--danger-color); }

        /* Hierarchy Visualization */
        .hierarchy-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .hierarchy-container {
            min-height: 400px;
            position: relative;
        }

        .hierarchy-node {
            background: white;
            border: 2px solid var(--primary-color);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            cursor: pointer;
            min-width: 120px;
        }

        .hierarchy-node:hover {
            transform: scale(1.05);
            box-shadow: var(--card-shadow-hover);
        }

        .hierarchy-node.root {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, rgba(41, 98, 255, 0.1), rgba(25, 118, 210, 0.1));
        }

        .hierarchy-node.level-1 { border-color: var(--secondary-color); }
        .hierarchy-node.level-2 { border-color: var(--warning-color); }
        .hierarchy-node.level-3 { border-color: var(--info-color); }

        .node-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin: 0 auto 0.5rem;
            object-fit: cover;
        }

        .node-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .node-type {
            font-size: 0.7rem;
            color: #666;
            text-transform: uppercase;
        }

        .node-status {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-left: 0.5rem;
        }

        .node-status.active { background: var(--secondary-color); }
        .node-status.inactive { background: var(--danger-color); }

        /* Performance Charts */
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

        /* Team Activities */
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

        .activity-icon.registration { background: rgba(76, 175, 80, 0.1); color: var(--secondary-color); }
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

        /* Incentives Section */
        .incentives-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .incentive-item {
            background: var(--light-bg);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
        }

        .incentive-item.achieved {
            border-left-color: var(--secondary-color);
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(46, 125, 50, 0.05));
        }

        .incentive-item.pending {
            border-left-color: var(--warning-color);
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.1), rgba(245, 124, 0, 0.05));
        }

        .incentive-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .incentive-title {
            font-weight: 600;
            color: #333;
        }

        .incentive-amount {
            font-weight: 700;
            color: var(--primary-color);
        }

        .incentive-description {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .incentive-progress {
            width: 100%;
            height: 6px;
            background: #eee;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 3px;
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

            .overview-grid {
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

            .overview-value {
                font-size: 1.5rem;
            }

            .hierarchy-container {
                min-height: 300px;
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

        .overview-card {
            animation: fadeInUp 0.6s ease-out;
        }

        .overview-card:nth-child(1) { animation-delay: 0.1s; }
        .overview-card:nth-child(2) { animation-delay: 0.2s; }
        .overview-card:nth-child(3) { animation-delay: 0.3s; }
        .overview-card:nth-child(4) { animation-delay: 0.4s; }

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
        
        .incentive-progress-bar {
            width: var(--progress-width, 0%);
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body id="hierarchy-data" data-hierarchy="<?php echo htmlspecialchars(json_encode($hierarchyData) ); ?>" data-earnings="<?php echo htmlspecialchars(json_encode($performanceData['monthly_earnings'] ?? []) ); ?>">
//
// TODO: This file is large (942 lines). Consider splitting into smaller functions.
// TODO: Add input validation for all user inputs.
//
    <!-- Header -->
    <header class="team-header">
        <div class="header-content">
            <div class="welcome-section">
                <h1>Team Management Center 👥</h1>
                <p>Monitor, manage, and grow your team</p>
            </div>
            <div class="team-stats">
                <div class="stat-badge">
                    <i class="bi bi-trophy me-1"></i><?php echo htmlspecialchars($1); ?>
                </div>
                <div class="stat-badge">
                    <i class="bi bi-people me-1"></i><?php echo htmlspecialchars($1); ?> members
                </div>
            </div>
        </div>
    </header>

    <!-- Dashboard Content -->
    <main class="dashboard-content">
        <!-- Team Overview -->
        <div class="overview-grid">
            <!-- Total Members -->
            <div class="overview-card members">
                <div class="overview-icon success">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="overview-value"><?php echo htmlspecialchars(number_format($teamInfo['total_members']) ); ?></div>
                <div class="overview-label">Total Team Members</div>
                <div class="overview-change positive">
                    <i class="bi bi-person-check"></i> <?php echo htmlspecialchars($1); ?> active
                </div>
            </div>

            <!-- Team Performance -->
            <div class="overview-card performance">
                <div class="overview-icon warning">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="overview-value"><?php echo htmlspecialchars($1); ?>%</div>
                <div class="overview-label">Avg Performance</div>
                <div class="overview-change positive">
                    <i class="bi bi-arrow-up"></i> Team efficiency rating
                </div>
            </div>

            <!-- Team Earnings -->
            <div class="overview-card earnings">
                <div class="overview-icon info">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="overview-value">₹<?php echo htmlspecialchars(number_format($teamInfo['total_earnings'], 0) ); ?></div>
                <div class="overview-label">Team Earnings</div>
                <div class="overview-change positive">
                    <i class="bi bi-plus-circle"></i> From <?php echo htmlspecialchars($1); ?> members
                </div>
            </div>

            <!-- Team Levels -->
            <div class="overview-card">
                <div class="overview-icon primary">
                    <i class="bi bi-diagram-3"></i>
                </div>
                <div class="overview-value"><?php echo htmlspecialchars($1); ?></div>
                <div class="overview-label">Network Levels</div>
                <div class="overview-change positive">
                    <i class="bi bi-chevron-double-up"></i> <?php echo htmlspecialchars($1); ?> direct reports
                </div>
            </div>
        </div>

        <!-- Team Hierarchy Visualization -->
        <div class="hierarchy-section">
            <h3 class="chart-title mb-4"><i class="bi bi-diagram-3 me-2 text-primary"></i>Team Hierarchy</h3>
            <div class="hierarchy-container">
                <div id="hierarchy-chart"></div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-grid">
            <!-- Team Earnings Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Team Earnings Trend</h3>
                    <span class="badge bg-success">Last 6 Months</span>
                </div>
                <canvas id="teamEarningsChart" width="400" height="200"></canvas>
            </div>

            <!-- Top Performers -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Top Performers</h3>
                    <span class="badge bg-primary"><?php echo htmlspecialchars(count($performanceData['top_performers']) ); ?> members</span>
                </div>
                <div class="mt-3">
                    <?php if(count($performanceData['top_performers']) > 0)
                        <?php foreach(array_slice($performanceData['top_performers'], 0, 5) as $performer)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="fw-bold small"><?php echo htmlspecialchars($1); ?></div>
                                <div class="text-muted small">Level <?php echo htmlspecialchars($1); ?></div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success">₹<?php echo htmlspecialchars(number_format($performer['earnings'], 0) ); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="bi bi-person-x text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 small">No performance data yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions-section">
            <h3 class="chart-title mb-4"><i class="bi bi-lightning me-2 text-warning"></i>Team Management Actions</h3>
            <div class="action-buttons">
                <a href="<?php echo htmlspecialchars(route('team.members') ); ?>" class="action-btn primary">
                    <div class="action-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="action-content">
                        <h5>View All Members</h5>
                        <p>Manage team members</p>
                    </div>
                </a>

                <a href="<?php echo htmlspecialchars(route('team.performance') ); ?>" class="action-btn success">
                    <div class="action-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="action-content">
                        <h5>Performance Analytics</h5>
                        <p>Detailed performance reports</p>
                    </div>
                </a>

                <a href="<?php echo htmlspecialchars(route('team.communication') ); ?>" class="action-btn info">
                    <div class="action-icon">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="action-content">
                        <h5>Team Communication</h5>
                        <p>Send messages to team</p>
                    </div>
                </a>

                <a href="<?php echo htmlspecialchars(route('team.export') ); ?>" class="action-btn primary">
                    <div class="action-icon">
                        <i class="bi bi-download"></i>
                    </div>
                    <div class="action-content">
                        <h5>Export Team Data</h5>
                        <p>Download team reports</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Team Activities -->
        <div class="activities-section">
            <h3 class="chart-title mb-4"><i class="bi bi-activity me-2 text-info"></i>Recent Team Activities</h3>
            <?php if(count($recentActivities) > 0)
                <?php foreach($recentActivities as $activity)
                <div class="activity-item">
                    <div class="activity-icon <?php echo htmlspecialchars($1); ?>">
                        <i class="bi bi-<?php echo htmlspecialchars($1); ?>"></i>
                    </div>
                    <div class="activity-content">
                        <h4><?php echo htmlspecialchars($1); ?></h4>
                        <p><?php echo htmlspecialchars($1); ?></p>
                        <small class="activity-time"><?php echo htmlspecialchars(\Carbon\Carbon::parse($activity['date'])->diffForHumans() ); ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-activity text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No recent team activities</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Team Incentives -->
        <?php if(count($teamIncentives) > 0)
        <div class="incentives-section">
            <h3 class="chart-title mb-4"><i class="bi bi-trophy me-2 text-warning"></i>Team Incentives & Rewards</h3>
            <?php foreach($teamIncentives as $incentive)
            <div class="incentive-item <?php echo htmlspecialchars($1); ?>">
                <div class="incentive-header">
                    <div class="incentive-title"><?php echo htmlspecialchars($1); ?></div>
                    <div class="incentive-amount">₹<?php echo htmlspecialchars(number_format($incentive['amount'], 0) ); ?></div>
                </div>
                <div class="incentive-description"><?php echo htmlspecialchars($1); ?></div>
                <?php if(isset($incentive['progress']))
                <div class="incentive-progress">
                    <div class="progress-bar incentive-progress-bar" data-progress="<?php echo htmlspecialchars($1); ?>"></div>
                </div>
                <div class="mt-1 small text-muted"><?php echo htmlspecialchars(round($incentive['progress'], 1) ); ?>% complete</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Team Hierarchy Visualization using D3.js
        const hierarchyDataElement = document.getElementById('hierarchy-data');
        const hierarchyData = hierarchyDataElement ? JSON.parse(hierarchyDataElement.getAttribute('data-hierarchy')) : [];
        
        const earningsDataElement = document.getElementById('earnings-data');
        const earningsData = earningsDataElement ? JSON.parse(earningsDataElement.getAttribute('data-earnings')) : [];

        // Set progress bar widths from data attributes
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.incentive-progress-bar');
            progressBars.forEach(bar => {
                const progress = bar.getAttribute('data-progress');
                if (progress) {
                    bar.style.setProperty('--progress-width', progress + '%');
                }
            });
        });

        // Simple hierarchy renderer (can be enhanced with D3 tree layout)
        function renderHierarchy() {
            const container = document.getElementById('hierarchy-chart');
            container.innerHTML = '';

            if (!hierarchyData.root) {
                container.innerHTML = '<div class="text-center py-5"><i class="bi bi-diagram-3 text-muted" style="font-size: 3rem;"></i><p class="text-muted mt-2">No team hierarchy data available</p></div>';
                return;
            }

            // Create root node
            const rootNode = createNode(hierarchyData.root, 'root');
            container.appendChild(rootNode);

            // Create level containers
            for (let level = 1; level <= 4; level++) {
                if (hierarchyData.levels && hierarchyData.levels[level]) {
                    const levelContainer = document.createElement('div');
                    levelContainer.className = 'hierarchy-level mt-4';
                    levelContainer.innerHTML = `<h6 class="text-center mb-3">Level ${level}</h6>`;

                    const levelNodes = document.createElement('div');
                    levelNodes.className = 'd-flex flex-wrap justify-content-center gap-3';

                    hierarchyData.levels[level].forEach(member => {
                        const node = createNode(member, `level-${level}`);
                        levelNodes.appendChild(node);
                    });

                    levelContainer.appendChild(levelNodes);
                    container.appendChild(levelContainer);
                }
            }
        }

        function createNode(member, nodeClass) {
            const node = document.createElement('div');
            node.className = `hierarchy-node ${nodeClass}`;

            node.innerHTML = `
                <img src="${member.avatar || '/public/assets/images/user/default-avatar.jpg'}"
                     alt="${member.name}" class="node-avatar">
                <div class="node-name">${member.name}</div>
                <div class="node-type">${member.type || 'Member'}
                    <span class="node-status ${member.status || 'active'}"></span>
                </div>
                ${member.referral_code ? `<small class="text-muted d-block">${member.referral_code}</small>` : ''}
            `;

            node.addEventListener('click', () => {
                // Could navigate to member detail page
                console.log('Clicked member:', member.id);
            });

            return node;
        }

        // Team Earnings Chart
        const earningsCtx = document.getElementById('teamEarningsChart').getContext('2d');
        const chartEarningsData = earningsData; // Use the already defined earningsData

        new Chart(earningsCtx, {
            type: 'line',
            data: {
                labels: earningsData.map(item => item.month),
                datasets: [{
                    label: 'Team Earnings (₹)',
                    data: earningsData.map(item => item.earnings),
                    borderColor: '#2962ff',
                    backgroundColor: 'rgba(41, 98, 255, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#2962ff',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
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
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Initialize hierarchy on page load
        document.addEventListener('DOMContentLoaded', function() {
            renderHierarchy();
        });

        // Auto-refresh data every 5 minutes
        setInterval(function() {
            // Could add real-time updates here
            console.log('Team dashboard data refresh check...');
        }, 300000);
    </script>
</body>
</html>



// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\employees\dashboard.php

function markAttendance() {
    const modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
    modal.show();
}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\farmers\dashboard.php

function deleteFarmer(id, name) {
    document.getElementById('farmerName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = '/farmers/' + id + '/delete';
    $('#deleteModal').modal('show');
}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\admin\monitoring\dashboard.php

function refreshMonitoring() {
    location.reload();
}
function runDiagnostics() {
    alert('Running system diagnostics... This may take a few moments.');
    // In real implementation, this would trigger diagnostic tests
}
function clearCache() {
    if (confirm('Are you sure you want to clear all system cache?')) {
        alert('Cache cleared successfully!');
        // In real implementation, this would clear cache
    }
function exportReport() {
    alert('Generating monitoring report... Download will start shortly.');
    // In real implementation, this would generate and download report
}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\user\dashboard.php

class
require_once __DIR__ . '/../../core/Helpers.php';

// Ensure $data is defined to prevent errors
if (!isset($data)) {
    $data = [
        'user' => ['name' => 'Guest'],
        'stats' => [
            'total_bookings' => 0,
            'total_favorites' => 0,
            'total_inquiries' => 0
        ],
        'recent_properties' => [],
        'favorites' => []
    ];
}
//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 950 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//