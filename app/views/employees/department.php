<?php
/**
 * Employee Department Page — shared view for 16 department routes
 * Data: $dept_title, $dept_icon, $dept_desc, $dept_color, $dept_slug, $employee_name
 */
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($dept_title) ?> — APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f0f2f5; }
        .dept-hero {
            background: linear-gradient(135deg, <?= $dept_color ?>22 0%, <?= $dept_color ?>11 100%);
            border-bottom: 1px solid <?= $dept_color ?>30;
            padding: 32px 0 28px;
        }
        .dept-icon-wrap {
            width: 64px; height: 64px; border-radius: 18px;
            background: <?= $dept_color ?>18; color: <?= $dept_color ?>;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; flex-shrink: 0;
        }
        .stat-card {
            background: #fff; border-radius: 14px; border: 1px solid #e9ecef;
            padding: 20px; transition: .2s; height: 100%;
        }
        .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); transform: translateY(-2px); }
        .stat-card .stat-icon {
            width: 42px; height: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .stat-card .stat-value { font-size: 26px; font-weight: 700; color: #1e293b; }
        .stat-card .stat-label { font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }
        .section-card {
            background: #fff; border-radius: 14px; border: 1px solid #e9ecef;
            padding: 24px; margin-bottom: 20px;
        }
        .section-card h6 { font-weight: 600; color: #1e293b; margin-bottom: 16px; }
        .quick-action {
            display: flex; align-items: center; gap: 12px; padding: 12px 16px;
            background: #f8fafc; border-radius: 10px; border: 1px solid #e9ecef;
            cursor: pointer; transition: .2s; text-decoration: none; color: inherit;
        }
        .quick-action:hover { background: #f0f4ff; border-color: #c7d2fe; color: inherit; text-decoration: none; }
        .quick-action i { font-size: 18px; width: 20px; text-align: center; }
        .empty-state {
            text-align: center; padding: 48px 20px; color: #94a3b8;
        }
        .empty-state i { font-size: 48px; margin-bottom: 16px; color: <?= $dept_color ?>40; }
        .empty-state h5 { color: #475569; font-weight: 600; margin-bottom: 8px; }
    </style>
</head>
<body>

<!-- Hero -->
<div class="dept-hero">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center gap-4">
            <div class="dept-icon-wrap">
                <i class="<?= $dept_icon ?>"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="color: #1e293b;"><?= htmlspecialchars($dept_title) ?></h3>
                <p class="mb-0 mt-1" style="color: #64748b; font-size: 14px;"><?= htmlspecialchars($dept_desc) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4 py-4">

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Items</div>
                        <div class="stat-value">—</div>
                    </div>
                    <div class="stat-icon" style="background: <?= $dept_color ?>12; color: <?= $dept_color ?>;">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Active</div>
                        <div class="stat-value">—</div>
                    </div>
                    <div class="stat-icon" style="background: #10b98112; color: #10b981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Pending</div>
                        <div class="stat-value">—</div>
                    </div>
                    <div class="stat-icon" style="background: #f59e0b12; color: #f59e0b;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Completed</div>
                        <div class="stat-value">—</div>
                    </div>
                    <div class="stat-icon" style="background: #3b82f612; color: #3b82f6;">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="section-card">
                <h6><i class="fas fa-list me-2" style="color: <?= $dept_color ?>;"></i>Recent Activity</h6>
                <div class="empty-state">
                    <i class="<?= $dept_icon ?>"></i>
                    <h5><?= htmlspecialchars($dept_title) ?></h5>
                    <p>This module is ready for data integration. Connect your department data sources to see activity here.</p>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="section-card mb-0">
                <h6><i class="fas fa-bolt me-2" style="color: <?= $dept_color ?>;"></i>Quick Actions</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="<?= $base ?>/employee/dashboard" class="quick-action">
                        <i class="fas fa-tachometer-alt" style="color: #6366f1;"></i>
                        <span>Back to Dashboard</span>
                    </a>
                    <a href="<?= $base ?>/employee/tasks" class="quick-action">
                        <i class="fas fa-tasks" style="color: #3b82f6;"></i>
                        <span>My Tasks</span>
                    </a>
                    <a href="<?= $base ?>/employee/attendance" class="quick-action">
                        <i class="fas fa-calendar-check" style="color: #10b981;"></i>
                        <span>Attendance</span>
                    </a>
                    <a href="<?= $base ?>/employee/leaves" class="quick-action">
                        <i class="fas fa-calendar-times" style="color: #f59e0b;"></i>
                        <span>Apply for Leave</span>
                    </a>
                    <a href="<?= $base ?>/employee/profile" class="quick-action">
                        <i class="fas fa-user-circle" style="color: #8b5cf6;"></i>
                        <span>My Profile</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
