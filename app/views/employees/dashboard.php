<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if employee is logged in
if (!isset($_SESSION['employee_id'])) {
    header('Location: ' . BASE_URL . '/employee/login');
    exit;
}

// Set page variables
$$page_title = 'Employee Dashboard - APS Dream Home';
$page_description = 'Employee portal dashboard for APS Dream Home';
$active_page = 'dashboard';

// Get employee data from controller
$employee = $dashboardData['employee'] ?? [];
$tasks = $dashboardData['tasks'] ?? [];
$performance = $dashboardData['performance'] ?? [];
$attendance = $dashboardData['attendance'] ?? [];
$activities = $dashboardData['activities'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .dashboard-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            margin: 20px auto;
            max-width: 1400px;
            padding: 30px;
        }

        .welcome-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .welcome-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(102, 126, 234, 0.1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: white;
        }

        .stat-icon.tasks { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.performance { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .stat-icon.attendance { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .stat-icon.activities { background: linear-gradient(135deg, #43e97b, #38f9d7); }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .action-btn:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .task-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .task-item:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .task-priority {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-high { background: #ffe4e6; color: #dc2626; }
        .priority-medium { background: #fef3c7; color: #d97706; }
        .priority-low { background: #dbeafe; color: #2563eb; }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background: white;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1rem;
            color: white;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 15px 25px;
            margin-bottom: 30px;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .navbar-text {
            color: white !important;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                margin: 10px;
                padding: 20px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .welcome-header {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar">
            <div class="container-fluid">
                <span class="navbar-brand">
                    <i class="fas fa-building me-2"></i>
                    APS Dream Home
                </span>
                <span class="navbar-text">
                    <i class="fas fa-user-tie me-2"></i>
                    Welcome, <?php echo htmlspecialchars($_SESSION['employee_name'] ?? 'Employee'); ?>
                </span>
            </div>
        </nav>

        <div class="dashboard-container">
            <!-- Welcome Header -->
            <div class="welcome-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-4 fw-bold mb-3">
                            <i class="fas fa-user-tie me-3"></i>
                            Employee Dashboard
                        </h1>
                        <p class="lead mb-0">
                            Welcome back, <?php echo htmlspecialchars($employee['name'] ?? 'Employee'); ?>! 
                            Here's your workspace overview.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex flex-column align-items-end">
                            <div class="text-white-50 small">Current Time</div>
                            <div class="h4 mb-0" id="currentTime"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon tasks">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-number"><?php echo count($tasks); ?></div>
                        <div class="stat-label">Active Tasks</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon performance">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-number"><?php echo $performance['completed_tasks'] ?? 0; ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon attendance">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-number"><?php echo count($attendance); ?></div>
                        <div class="stat-label">Attendance Days</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon activities">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="stat-number"><?php echo count($activities); ?></div>
                        <div class="stat-label">Activities</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h4 class="mb-3">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h4>
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <button class="action-btn w-100" onclick="checkIn()">
                            <i class="fas fa-sign-in-alt"></i>
                            Check In
                        </button>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <button class="action-btn w-100" onclick="checkOut()">
                            <i class="fas fa-sign-out-alt"></i>
                            Check Out
                        </button>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?php echo BASE_URL; ?>/employee/profile" class="action-btn w-100">
                            <i class="fas fa-user"></i>
                            Profile
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?php echo BASE_URL; ?>/employee/logout" class="action-btn w-100">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row">
                <!-- Tasks Section -->
                <div class="col-md-6 mb-4">
                    <div class="chart-container">
                        <h4 class="mb-3">
                            <i class="fas fa-tasks me-2"></i>
                            Recent Tasks
                        </h4>
                        <?php if (!empty($tasks)): ?>
                            <?php foreach (array_slice($tasks, 0, 5) as $task): ?>
                                <div class="task-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($task['title'] ?? 'Untitled Task'); ?></h6>
                                            <p class="text-muted small mb-2"><?php echo htmlspecialchars($task['description'] ?? 'No description'); ?></p>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date('M d, Y', strtotime($task['created_at'] ?? 'now')); ?>
                                            </small>
                                        </div>
                                        <span class="task-priority priority-medium">
                                            <?php echo htmlspecialchars($task['status'] ?? 'pending'); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No tasks assigned yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Activities Section -->
                <div class="col-md-6 mb-4">
                    <div class="chart-container">
                        <h4 class="mb-3">
                            <i class="fas fa-history me-2"></i>
                            Recent Activities
                        </h4>
                        <?php if (!empty($activities)): ?>
                            <?php foreach (array_slice($activities, 0, 5) as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($activity['activity'] ?? 'Activity'); ?></h6>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y H:i', strtotime($activity['created_at'] ?? 'now')); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No recent activities</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Performance Chart -->
            <div class="row">
                <div class="col-12">
                    <div class="chart-container">
                        <h4 class="mb-3">
                            <i class="fas fa-chart-bar me-2"></i>
                            Performance Overview
                        </h4>
                        <canvas id="performanceChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            });
            document.getElementById('currentTime').textContent = timeString;
        }
        
        updateTime();
        setInterval(updateTime, 1000);

        // Check In function
        async function checkIn() {
            try {
                const response = await fetch('<?php echo BASE_URL; ?>/employee/checkin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('success', 'Checked in successfully!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('error', result.message || 'Check in failed');
                }
            } catch (error) {
                showNotification('error', 'Network error. Please try again.');
            }
        }

        // Check Out function
        async function checkOut() {
            try {
                const response = await fetch('<?php echo BASE_URL; ?>/employee/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('success', 'Checked out successfully!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('error', result.message || 'Check out failed');
                }
            } catch (error) {
                showNotification('error', 'Network error. Please try again.');
            }
        }

        // Show notification
        function showNotification(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alert = document.createElement('div');
            alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        }

        // Performance Chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Completed Tasks', 'Pending Tasks', 'Attendance', 'Activities'],
                datasets: [{
                    label: 'Performance Metrics',
                    data: [
                        <?php echo $performance['completed_tasks'] ?? 0; ?>,
                        <?php echo $performance['pending_tasks'] ?? 0; ?>,
                        <?php echo count($attendance); ?>,
                        <?php echo count($activities); ?>
                    ],
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(118, 75, 162, 0.8)',
                        'rgba(79, 172, 254, 0.8)',
                        'rgba(67, 233, 123, 0.8)'
                    ],
                    borderColor: [
                        'rgba(102, 126, 234, 1)',
                        'rgba(118, 75, 162, 1)',
                        'rgba(79, 172, 254, 1)',
                        'rgba(67, 233, 123, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 10
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
                            borderDash: [5, 5]
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

        // Auto-refresh dashboard every 30 seconds
        setInterval(() => {
            // Optional: Add auto-refresh functionality
        }, 30000);
    </script>
</body>
</html>