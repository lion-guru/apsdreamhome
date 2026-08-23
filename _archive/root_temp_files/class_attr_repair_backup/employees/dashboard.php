<?php
$employee = $dashboardData['employee'] ?? [];
$tasks = $dashboardData['tasks'] ?? [];
$performance = $dashboardData['performance'] ?? [];
$attendance = $dashboardData['attendance'] ?? [];
$activities = $dashboardData['activities'] ?? [];
$gamify = $gamify ?? [];

$completedTasks = $performance['completed_tasks'] ?? 0;
$pendingTasks = $performance['pending_tasks'] ?? 0;
$attendanceDays = count($attendance);
$totalActivities = count($activities);

$employeeName = htmlspecialchars($employee['name'] ?? $_SESSION['employee_name'] ?? 'Employee');
$employeeEmail = htmlspecialchars($employee['email'] ?? $_SESSION['employee_email'] ?? '');
$todayCheckedIn = false;
foreach ($attendance as $att) {
    if (date('Y-m-d', strtotime($att['attendance_date'] ?? '')) === date('Y-m-d')) {
        $todayCheckedIn = true;
        break;
    }
}
?>

<style>
    .emp-welcome {
        background: linear-gradient(135deg, #7c2d12 0%, #c2410c 50%, #ea580c 100%);
        color: #fff; border-radius: 16px; padding: 30px; margin-bottom: 24px;
        position: relative; overflow: hidden;
    }
    .emp-welcome::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="g" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23g)"/></svg>');
        opacity: 0.4;
    }
    .emp-welcome * { position: relative; z-index: 1; }
    .emp-stat {
        background: #fff; border-radius: 14px; padding: 22px; text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }
    .emp-stat:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .emp-stat-icon {
        width: 52px; height: 52px; border-radius: 14px; display: flex;
        align-items: center; justify-content: center; font-size: 1.3rem; margin: 0 auto 12px; color: #fff;
    }
    .emp-stat-num { font-size: 2rem; font-weight: 800; color: #1e293b; line-height: 1; }
    .emp-stat-label { font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }

    .emp-card {
        background: #fff; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid #f1f5f9; margin-bottom: 20px; overflow: hidden;
    }
    .emp-card-header {
        padding: 16px 22px; border-bottom: 1px solid #f1f5f9; display: flex;
        justify-content: space-between; align-items: center; background: #fafbfc;
    }
    .emp-card-header h6 { margin: 0; font-weight: 700; color: #1e293b; }
    .emp-card-body { padding: 20px 22px; }

    .emp-action-btn {
        display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px;
        border-radius: 10px; font-weight: 600; font-size: 0.85rem; text-decoration: none;
        transition: all 0.3s ease; border: none; cursor: pointer;
    }
    .emp-action-btn:hover { transform: translateY(-2px); }
    .emp-checkin { background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; }
    .emp-checkin:hover { color: #fff; box-shadow: 0 6px 20px rgba(22,163,74,0.4); }
    .emp-checkout { background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; }
    .emp-checkout:hover { color: #fff; box-shadow: 0 6px 20px rgba(220,38,38,0.4); }
    .emp-quick { background: linear-gradient(135deg, #7c2d12, #c2410c); color: #fff; }
    .emp-quick:hover { color: #fff; box-shadow: 0 6px 20px rgba(124,45,18,0.4); }

    .emp-task-item {
        display: flex; align-items: center; padding: 14px 0; border-bottom: 1px solid #f1f5f9;
    }
    .emp-task-item:last-child { border-bottom: none; }
    .emp-task-badge {
        padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
    }
    .badge-pending { background: #fef3c7; color: #d97706; }
    .badge-in-progress { background: #dbeafe; color: #2563eb; }
    .badge-completed { background: #d1fae5; color: #059669; }

    .emp-activity-item {
        display: flex; align-items: flex-start; padding: 12px 0; border-bottom: 1px solid #f1f5f9;
    }
    .emp-activity-item:last-child { border-bottom: none; }
    .emp-activity-dot {
        width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center;
        justify-content: center; margin-right: 12px; font-size: 0.85rem; color: #fff; flex-shrink: 0;
    }

    .emp-attendance-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 0; border-bottom: 1px solid #f1f5f9;
    }
    .emp-attendance-row:last-child { border-bottom: none; }

    @media (max-width: 768px) {
        .emp-welcome { padding: 20px; }
        .emp-welcome h4 { font-size: 1.2rem; }
        .emp-stat-num { font-size: 1.5rem; }
        .emp-action-btn { width: 100%; justify-content: center; }
    }
</style>

<div class="emp-welcome">
    <div class="row align-items-center">
        <div class="col-md-7">
            <h4 class="fw-bold mb-2">
                <i class="fas fa-user-tie me-2"></i>Welcome, <?= $employeeName ?>!
            </h4>
            <p class="mb-2 opacity-75">
                <?= date('l, F j, Y') ?> &mdash; <?= date('h:i A') ?>
            </p>
            <p class="mb-0 small opacity-60">
                <?php if (!empty($employee['department'])): ?>
                    Department: <?= htmlspecialchars(ucfirst($employee['department'])) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <?php if ($todayCheckedIn): ?>
                <button class="emp-action-btn emp-checkout" onclick="checkOut()">
                    <i class="fas fa-sign-out-alt"></i> Check Out
                </button>
            <?php else: ?>
                <button class="emp-action-btn emp-checkin" onclick="checkIn()">
                    <i class="fas fa-sign-in-alt"></i> Check In
                </button>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/employee/profile" class="emp-action-btn emp-quick mt-2">
                <i class="fas fa-user"></i> My Profile
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="emp-stat">
            <div class="emp-stat-icon" class="style-95160"><i class="fas fa-tasks"></i></div>
            <div class="emp-stat-num"><?= $pendingTasks ?></div>
            <div class="emp-stat-label">Pending Tasks</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="emp-stat">
            <div class="emp-stat-icon" class="style-25636"><i class="fas fa-check-circle"></i></div>
            <div class="emp-stat-num"><?= $completedTasks ?></div>
            <div class="emp-stat-label">Completed</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="emp-stat">
            <div class="emp-stat-icon" class="style-81404"><i class="fas fa-calendar-check"></i></div>
            <div class="emp-stat-num"><?= $attendanceDays ?></div>
            <div class="emp-stat-label">Attendance Days</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="emp-stat">
            <div class="emp-stat-icon" class="style-11227"><i class="fas fa-history"></i></div>
            <div class="emp-stat-num"><?= $totalActivities ?></div>
            <div class="emp-stat-label">Activities</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="emp-card">
            <div class="emp-card-header">
                <h6><i class="fas fa-tasks me-2"></i>My Tasks</h6>
                <a href="<?= BASE_URL ?>/employee/tasks" class="small text-decoration-none" class="style-8314">View All</a>
            </div>
            <div class="emp-card-body">
                <?php if (!empty($tasks)): ?>
                    <?php foreach (array_slice($tasks, 0, 6) as $task): ?>
                        <div class="emp-task-item">
                            <div class="flex-grow-1">
                                <h6 class="mb-1" class="style-51894"><?= htmlspecialchars($task['title'] ?? 'Untitled Task') ?></h6>
                                <small class="text-muted">
                                    <?php if (!empty($task['description'])): ?>
                                        <?= htmlspecialchars(mb_strimwidth($task['description'], 0, 60, '...')) ?>
                                    <?php endif; ?>
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?= date('M d', strtotime($task['created_at'] ?? 'now')) ?>
                                    <?php if (!empty($task['priority'])): ?>
                                        &middot;
                                        <span class="text-uppercase fw-bold" class="style-537">
                                            <?= $task['priority'] ?>
                                        </span>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <?php
                            $statusClass = 'badge-pending';
                            if (($task['status'] ?? '') === 'in_progress') $statusClass = 'badge-in-progress';
                            elseif (($task['status'] ?? '') === 'completed') $statusClass = 'badge-completed';
                            ?>
                            <span class="emp-task-badge <?= $statusClass ?>"><?= htmlspecialchars($task['status'] ?? 'pending') ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-check fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No tasks assigned yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="emp-card">
            <div class="emp-card-header">
                <h6><i class="fas fa-history me-2"></i>Recent Activity</h6>
                <a href="<?= BASE_URL ?>/employee/activities" class="small text-decoration-none" class="style-8314">View All</a>
            </div>
            <div class="emp-card-body">
                <?php if (!empty($activities)): ?>
                    <?php foreach (array_slice($activities, 0, 5) as $activity): ?>
                        <div class="emp-activity-item">
                            <div class="emp-activity-dot" class="style-95160">
                                <i class="fas fa-circle" class="style-96543"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1" class="style-47175"><?= htmlspecialchars($activity['activity'] ?? $activity['description'] ?? 'Activity') ?></p>
                                <small class="text-muted"><?= date('M d, Y h:i A', strtotime($activity['created_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-stream fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No recent activities</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="emp-card">
            <div class="emp-card-header">
                <h6><i class="fas fa-calendar me-2"></i>Quick Links</h6>
            </div>
            <div class="emp-card-body">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/employee/tasks" class="emp-action-btn emp-quick justify-content-center">
                        <i class="fas fa-clipboard-list"></i> My Tasks
                    </a>
                    <a href="<?= BASE_URL ?>/employee/attendance" class="emp-action-btn emp-quick justify-content-center">
                        <i class="fas fa-fingerprint"></i> Attendance
                    </a>
                    <a href="<?= BASE_URL ?>/employee/leaves" class="emp-action-btn emp-quick justify-content-center">
                        <i class="fas fa-umbrella-beach"></i> Apply Leave
                    </a>
                    <a href="<?= BASE_URL ?>/employee/payroll" class="emp-action-btn emp-quick justify-content-center">
                        <i class="fas fa-money-check-alt"></i> Payroll
                    </a>
                    <a href="<?= BASE_URL ?>/employee/documents" class="emp-action-btn emp-quick justify-content-center">
                        <i class="fas fa-file-alt"></i> Documents
                    </a>
                    <a href="<?= BASE_URL ?>/employee/performance" class="emp-action-btn emp-quick justify-content-center">
                        <i class="fas fa-chart-line"></i> Performance
                    </a>
                </div>
            </div>
        </div>

        <div class="emp-card">
            <div class="emp-card-header">
                <h6><i class="fas fa-calendar-check me-2"></i>Attendance This Week</h6>
            </div>
            <div class="emp-card-body">
                <?php
                $weekDays = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-{$i} days"));
                    $dayLabel = date('D', strtotime($date));
                    $checkedIn = false;
                    foreach ($attendance as $att) {
                        if (date('Y-m-d', strtotime($att['attendance_date'] ?? '')) === $date) {
                            $checkedIn = true;
                            break;
                        }
                    }
                    $weekDays[] = ['date' => $date, 'day' => $dayLabel, 'checked' => $checkedIn];
                }
                ?>
                <div class="d-flex justify-content-between">
                    <?php foreach ($weekDays as $wd): ?>
                        <div class="text-center">
                            <div class="style-89970">
                                <?php if ($wd['checked']): ?>
                                    <i class="fas fa-check"></i>
                                <?php else: ?>
                                    <?= strtoupper(substr($wd['day'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted" class="style-56522"><?= $wd['day'] ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="emp-card">
            <div class="emp-card-header">
                <h6><i class="fas fa-chart-pie me-2"></i>Performance</h6>
            </div>
            <div class="emp-card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Tasks Completed</small>
                        <small class="fw-bold"><?= $completedTasks ?></small>
                    </div>
                    <div class="progress" class="style-87912">
                        <div class="progress-bar" role="progressbar" class="style-95451"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Pending Tasks</small>
                        <small class="fw-bold"><?= $pendingTasks ?></small>
                    </div>
                    <div class="progress" class="style-87912">
                        <div class="progress-bar" role="progressbar" class="style-32981"></div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <div class="fw-bold" class="style-22309">
                        <?= ($completedTasks + $pendingTasks) > 0 ? round(($completedTasks / ($completedTasks + $pendingTasks)) * 100) : 0 ?>%
                    </div>
                    <small class="text-muted">Completion Rate</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function checkIn() {
    try {
        const res = await fetch('<?= BASE_URL ?>/employee/checkin', { method: 'POST', headers: {'Content-Type':'application/json'} });
        const data = await res.json();
        if (data.success) { showToast('success', 'Checked in successfully!'); setTimeout(() => location.reload(), 1000); }
        else { showToast('error', data.message || 'Check-in failed'); }
    } catch(e) { showToast('error', 'Network error. Please try again.'); }
}

async function checkOut() {
    try {
        const res = await fetch('<?= BASE_URL ?>/employee/checkout', { method: 'POST', headers: {'Content-Type':'application/json'} });
        const data = await res.json();
        if (data.success) { showToast('success', 'Checked out successfully!'); setTimeout(() => location.reload(), 1000); }
        else { showToast('error', data.message || 'Check-out failed'); }
    } catch(e) { showToast('error', 'Network error. Please try again.'); }
}

function showToast(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const el = document.createElement('div');
    el.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    el.style.cssText = 'top:20px;right:20px;z-index:9999;min-width:300px;';
    el.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(el);
    setTimeout(() => { if (el.parentNode) el.remove(); }, 4000);
}
</script>
