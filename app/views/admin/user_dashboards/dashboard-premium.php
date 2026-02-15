<?php

/**
 * Premium Employee Dashboard - APS Dream Homes
 * Ultra-modern dashboard with advanced features and stunning design
 */

require_once __DIR__ . '/../core/init.php';

$db = \App\Core\App::database();

// Enhanced session security and timeout check
if (!isAuthenticated() || (getAuthRole() !== 'employee' && getAuthRole() !== 'admin')) {
    header('Location: ../../employee_login_enhanced.php');
    exit();
}

$page_title = "Employee Dashboard - APS Dream Homes";
$employee_name = getAuthFullName() ?? 'Employee';
$employee_id = getAuthUserId() ?? 0;
$employee_email = getAuthUserEmail() ?? '';
$employee_department = $_SESSION['employee_department'] ?? '';

// Get employee details
$employee_details = [];
try {
    $employee_details = $db->fetchOne("SELECT * FROM employees WHERE id = :id AND status = 'active'", ['id' => $employee_id]) ?: [];
} catch (Exception $e) {
    error_log("Employee details error: " . $e->getMessage());
}

// Get employee tasks/assignments
$tasks = [];
try {
    $tasks = $db->fetchAll("SELECT * FROM employee_tasks WHERE employee_id = :id ORDER BY due_date ASC LIMIT 10", ['id' => $employee_id]);
} catch (Exception $e) {
    error_log("Employee tasks error: " . $e->getMessage());
}

// Get recent activities
$activities = [];
try {
    $activities = $db->fetchAll("SELECT * FROM employee_activities WHERE employee_id = :id ORDER BY created_at DESC LIMIT 8", ['id' => $employee_id]);
} catch (Exception $e) {
    error_log("Employee activities error: " . $e->getMessage());
}

// Get comprehensive statistics
$stats = [
    'total_tasks' => 0,
    'pending_tasks' => 0,
    'in_progress_tasks' => 0,
    'completed_today' => 0,
    'overdue_tasks' => 0,
    'completed_this_week' => 0,
    'upcoming_deadlines' => 0
];

foreach ($tasks as $task) {
    $stats['total_tasks']++;
    if ($task['status'] === 'pending') $stats['pending_tasks']++;
    if ($task['status'] === 'in_progress') $stats['in_progress_tasks']++;
    if ($task['status'] === 'completed') $stats['completed_today']++;

    // Check overdue tasks
    if ($task['due_date'] && strtotime($task['due_date']) < strtotime('today') && $task['status'] !== 'completed') {
        $stats['overdue_tasks']++;
    }

    // Check upcoming deadlines (next 7 days)
    if ($task['due_date'] && strtotime($task['due_date']) <= strtotime('+7 days') && $task['status'] !== 'completed') {
        $stats['upcoming_deadlines']++;
    }
}

try {
    // Get completed today
    $res = $db->fetchOne("SELECT COUNT(*) as count FROM employee_tasks WHERE employee_id = :id AND status = 'completed' AND DATE(updated_at) = CURDATE()", ['id' => $employee_id]);
    $stats['completed_today'] = $res['count'] ?? 0;

    // Get completed this week
    $res = $db->fetchOne("SELECT COUNT(*) as count FROM employee_tasks WHERE employee_id = :id AND status = 'completed' AND YEARWEEK(updated_at) = YEARWEEK(CURDATE())", ['id' => $employee_id]);
    $stats['completed_this_week'] = $res['count'] ?? 0;
} catch (Exception $e) {
    error_log("Stats error: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../../assets/css/dashboard.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Overlay for Mobile Sidebar -->
    <div class="overlay" id="overlay"></div>

    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
    </div>

    <!-- Top Header -->
    <header class="top-header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3>APS Dream Homes</h3>
            </div>
            <div class="user-section">
                <div class="notification-bell">
                    <i class="fas fa-bell fa-lg"></i>
                    <div class="notification-badge"></div>
                </div>
                <div class="user-info">
                    <h6><?php echo h($employee_name); ?></h6>
                    <small><?php echo h($employee_department); ?> Department</small>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($employee_name, 0, 2)); ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar">
        <nav class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-title">Main Menu</div>
                <a href="#" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-tasks"></i>
                    My Tasks
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-calendar-alt"></i>
                    Calendar
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-chart-line"></i>
                    Reports
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-title">Personal</div>
                <a href="#" class="menu-item">
                    <i class="fas fa-user"></i>
                    Profile
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-bell"></i>
                    Notifications
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-envelope"></i>
                    Messages
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-title">Resources</div>
                <a href="#" class="menu-item">
                    <i class="fas fa-book"></i>
                    Documentation
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-question-circle"></i>
                    Help & Support
                </a>
                <a href="../../employee_logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Welcome Section -->
        <div class="welcome-section fade-in-up">
            <div class="welcome-content">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="welcome-title">Welcome back, <?php echo h($employee_name); ?>! 👋</h1>
                        <p class="welcome-subtitle">Here's what's happening with your work today. You're doing great!</p>
                        <div class="welcome-stats">
                            <div class="welcome-stat">
                                <div class="welcome-stat-number"><?php echo h($stats['completed_this_week']); ?></div>
                                <div class="welcome-stat-label">Completed This Week</div>
                            </div>
                            <div class="welcome-stat">
                                <div class="welcome-stat-number"><?php echo h($stats['pending_tasks']); ?></div>
                                <div class="welcome-stat-label">Pending Tasks</div>
                            </div>
                            <div class="welcome-stat">
                                <div class="welcome-stat-number"><?php echo h($stats['upcoming_deadlines']); ?></div>
                                <div class="welcome-stat-label">Upcoming Deadlines</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 text-end">
                        <div class="text-white-50 small">
                            <i class="fas fa-clock me-1"></i>
                            Last login: <?php echo date('M j, Y g:i A', strtotime($employee_details['last_login'] ?? 'now')); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="premium-card stat-card fade-in-up">
                    <i class="fas fa-tasks stat-icon"></i>
                    <div class="stat-number"><?php echo h($stats['total_tasks']); ?></div>
                    <div class="stat-label">Total Tasks</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="premium-card stat-card fade-in-up" style="animation-delay: 0.1s;">
                    <i class="fas fa-hourglass-half stat-icon" style="background: var(--warning-gradient); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;"></i>
                    <div class="stat-number" style="background: var(--warning-gradient); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;"><?php echo h($stats['pending_tasks']); ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="premium-card stat-card fade-in-up" style="animation-delay: 0.2s;">
                    <i class="fas fa-spinner stat-icon" style="background: var(--success-gradient); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;"></i>
                    <div class="stat-number" style="background: var(--success-gradient); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;"><?php echo h($stats['in_progress_tasks']); ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="premium-card stat-card fade-in-up" style="animation-delay: 0.3s;">
                    <i class="fas fa-check-circle stat-icon" style="background: var(--secondary-gradient); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;"></i>
                    <div class="stat-number" style="background: var(--secondary-gradient); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;"><?php echo h($stats['completed_today']); ?></div>
                    <div class="stat-label">Completed Today</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tasks Section -->
            <div class="col-lg-8 mb-4">
                <div class="premium-card fade-in-up" style="animation-delay: 0.4s;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4><i class="fas fa-tasks me-2"></i>My Tasks</h4>
                        <button class="btn btn-primary" style="background: var(--primary-gradient); border: none;">
                            <i class="fas fa-plus me-1"></i>New Task
                        </button>
                    </div>

                    <?php if (empty($tasks)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No tasks assigned</h5>
                            <p class="text-muted">You don't have any active tasks right now. Enjoy your free time!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($tasks as $index => $task): ?>
                            <div class="task-card fade-in-up" style="animation-delay: <?php echo (0.5 + $index * 0.1); ?>s;">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-2 fw-bold"><?php echo h($task['title'] ?? 'Untitled Task'); ?></h6>
                                        <p class="text-muted mb-3"><?php echo h($task['description'] ?? 'No description available'); ?></p>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="task-priority priority-<?php echo h($task['priority'] ?? 'medium'); ?>">
                                                <i class="fas fa-flag me-1"></i><?php echo h(ucfirst($task['priority'] ?? 'medium')); ?>
                                            </span>
                                            <span class="task-status status-<?php echo h(str_replace('_', '-', $task['status'] ?? 'pending')); ?>">
                                                <i class="fas fa-info-circle me-1"></i><?php echo h(ucfirst(str_replace('_', ' ', $task['status'] ?? 'pending'))); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="text-muted small mb-3">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Due: <?php echo date('M j, Y', strtotime($task['due_date'] ?? 'now')); ?>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Profile & Activity Section -->
            <div class="col-lg-4">
                <!-- Profile Card -->
                <div class="premium-card profile-card fade-in-up" style="animation-delay: 0.5s;">
                    <div class="profile-avatar pulse">
                        <?php echo strtoupper(substr($employee_name, 0, 2)); ?>
                    </div>
                    <h5 class="profile-name"><?php echo h($employee_name); ?></h5>
                    <p class="profile-role"><?php echo h($employee_details['role'] ?? 'Employee'); ?></p>
                    <div class="profile-details">
                        <div class="profile-detail-item">
                            <div class="profile-detail-label">Department</div>
                            <div class="profile-detail-value"><?php echo h($employee_department); ?></div>
                        </div>
                        <div class="profile-detail-item">
                            <div class="profile-detail-label">Email</div>
                            <div class="profile-detail-value"><?php echo h($employee_email); ?></div>
                        </div>
                        <div class="profile-detail-item">
                            <div class="profile-detail-label">Phone</div>
                            <div class="profile-detail-value"><?php echo h($employee_details['phone'] ?? 'Not provided'); ?></div>
                        </div>
                        <div class="profile-detail-item">
                            <div class="profile-detail-label">Join Date</div>
                            <div class="profile-detail-value"><?php echo date('M j, Y', strtotime($employee_details['join_date'] ?? 'now')); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="premium-card fade-in-up" style="animation-delay: 0.6s;">
                    <h5 class="mb-4"><i class="fas fa-history me-2"></i>Recent Activity</h5>

                    <?php if (empty($activities)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent activity</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activities as $index => $activity): ?>
                            <div class="activity-item fade-in-up" style="animation-delay: <?php echo (0.7 + $index * 0.1); ?>s;">
                                <div class="activity-icon">
                                    <i class="fas fa-<?php echo $activity['icon'] ?? 'circle'; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title"><?php echo h($activity['activity'] ?? 'Activity'); ?></div>
                                    <div class="activity-time"><?php echo time_ago(strtotime($activity['created_at'])); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced animations on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in-up').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        // Time ago helper function
        function time_ago(timestamp) {
            const seconds = Math.floor(Date.now() / 1000) - timestamp;
            const intervals = {
                year: 31536000,
                month: 2592000,
                week: 604800,
                day: 86400,
                hour: 3600,
                minute: 60
            };

            for (const [unit, secondsInUnit] of Object.entries(intervals)) {
                const interval = Math.floor(seconds / secondsInUnit);
                if (interval >= 1) {
                    return interval + ' ' + unit + (interval > 1 ? 's' : '') + ' ago';
                }
            }
            return 'Just now';
        }

        // Interactive hover effects
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px)';
            });

            item.addEventListener('mouseleave', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'translateX(0)';
                }
            });
        });

        // Notification bell interaction
        document.querySelector('.notification-bell')?.addEventListener('click', function() {
            this.classList.toggle('pulse');
            setTimeout(() => {
                this.classList.toggle('pulse');
            }, 1000);
        });

        // Smooth scroll for sidebar links
        document.querySelectorAll('.menu-item[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

    <?php
    // Helper function for time ago (PHP version)
    function time_ago($timestamp)
    {
        $seconds = time() - $timestamp;
        $intervals = array(
            'year' => 31536000,
            'month' => 2592000,
            'week' => 604800,
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60
        );

        foreach ($intervals as $unit => $secondsInUnit) {
            $interval = floor($seconds / $secondsInUnit);
            if ($interval >= 1) {
                return $interval . ' ' . $unit . ($interval > 1 ? 's' : '') . ' ago';
            }
        }
        return 'Just now';
    }
    ?>

    <script src="assets/js/dashboard.js?v=<?php echo time(); ?>"></script>
</body>

</html>