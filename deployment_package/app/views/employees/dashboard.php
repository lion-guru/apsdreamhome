<?php require_once 'app/views/layouts/employee_header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-2">स्वागत है, <?= htmlspecialchars($employee['name']) ?>!</h4>
                            <p class="card-text mb-2">
                                <i class="fas fa-id-badge mr-2"></i>
                                <?= htmlspecialchars($employee['employee_code']) ?> •
                                <?= htmlspecialchars($employee['role_name']) ?> •
                                <?= htmlspecialchars($employee['department_name']) ?>
                            </p>
                            <p class="card-text">
                                <i class="fas fa-calendar mr-2"></i>
                                जॉइनिंग डेट: <?= date('d M Y', strtotime($employee['joining_date'])) ?>
                                <?php if ($employee['salary']): ?>
                                    | सैलरी: ₹<?= number_format($employee['salary']) ?>/माह
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="employee-level">
                                <?php
                                $performanceScore = $weekly_performance['overall_score'] ?? 0;
                                $level = 'Bronze';
                                $levelColor = 'bronze';

                                if ($performanceScore >= 90) {
                                    $level = 'Diamond';
                                    $levelColor = 'info';
                                } elseif ($performanceScore >= 80) {
                                    $level = 'Gold';
                                    $levelColor = 'warning';
                                } elseif ($performanceScore >= 70) {
                                    $level = 'Silver';
                                    $levelColor = 'silver';
                                }
                                ?>
                                <span class="badge badge-<?= $levelColor ?> p-2">
                                    <i class="fas fa-star mr-1"></i><?= $level ?> Performer
                                </span>
                                <br>
                                <small class="text-white-50 mt-1 d-block">
                                    परफॉर्मेंस स्कोर: <?= $performanceScore ?>/100
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">टुडे टास्क्स</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $today_tasks_count ?>
                            </div>
                            <small class="text-success">
                                <i class="fas fa-tasks"></i> पेंडिंग वर्क
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">परफॉर्मेंस स्कोर</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $weekly_performance['overall_score'] ?? 0 ?>/100
                            </div>
                            <small class="text-info">
                                <i class="fas fa-chart-line"></i> इस सप्ताह का
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">टोटल एक्टिविटीज</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $employee['total_activities'] ?? 0 ?>
                            </div>
                            <small class="text-warning">
                                <i class="fas fa-history"></i> कुल एक्टिविटीज
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-history fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">टुडे अटेंडेंस</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $todayStatus = $employee['today_attendance'] ?? 'not_marked';
                                $statusText = [
                                    'present' => 'प्रेजेंट',
                                    'absent' => 'एब्सेंट',
                                    'late' => 'लेट',
                                    'not_marked' => 'नहीं मार्क्ड'
                                ];
                                echo $statusText[$todayStatus] ?? 'नहीं मार्क्ड';
                                ?>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> स्टेटस चेक करें
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Today's Tasks -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks mr-2"></i>टुडे टास्क्स
                    </h6>
                    <a href="/employee/tasks" class="btn btn-sm btn-outline-primary">सभी देखें</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($dashboard_data['today_tasks'])): ?>
                        <?php foreach ($dashboard_data['today_tasks'] as $task): ?>
                            <div class="task-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <?php
                                        $priorityColors = [
                                            'high' => 'danger',
                                            'medium' => 'warning',
                                            'low' => 'info'
                                        ];
                                        $priorityColor = $priorityColors[$task['priority']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $priorityColor ?> badge-pill">
                                            <?= ucfirst($task['priority']) ?>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="font-weight-bold small">
                                            <?= htmlspecialchars($task['title']) ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?= htmlspecialchars(substr($task['description'], 0, 50)) ?>...
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'in_progress' => 'info',
                                            'completed' => 'success'
                                        ];
                                        $statusColor = $statusColors[$task['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $statusColor ?> badge-sm">
                                            <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">सभी टास्क्स कंप्लीट!</h6>
                            <p class="text-muted">कोई पेंडिंग टास्क नहीं है</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt mr-2"></i>क्विक एक्शन
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="recordAttendance('check_in')">
                            <i class="fas fa-sign-in-alt mr-2"></i>चेक इन
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="recordAttendance('check_out')">
                            <i class="fas fa-sign-out-alt mr-2"></i>चेक आउट
                        </button>
                        <a href="/employee/tasks" class="btn btn-outline-info">
                            <i class="fas fa-tasks mr-2"></i>मेरे टास्क्स
                        </a>
                        <a href="/employee/leaves" class="btn btn-outline-warning">
                            <i class="fas fa-calendar mr-2"></i>लीव अप्लाई करें
                        </a>
                        <a href="/employee/profile" class="btn btn-outline-secondary">
                            <i class="fas fa-user mr-2"></i>मेरा प्रोफाइल
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Overview -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line mr-2"></i>परफॉर्मेंस ओवरव्यू
                    </h6>
                    <a href="/employee/performance" class="btn btn-sm btn-outline-primary">डिटेल्स देखें</a>
                </div>
                <div class="card-body">
                    <?php
                    $tasksCompleted = $weekly_performance['tasks']['tasks_completed'] ?? 0;
                    $onTimeCompletions = $weekly_performance['tasks']['on_time_completions'] ?? 0;
                    $attendanceRate = $weekly_performance['attendance']['attendance_rate'] ?? 0;
                    $avgRating = $weekly_performance['satisfaction']['avg_rating'] ?? 0;

                    $taskScore = $tasksCompleted > 0 ? min(($onTimeCompletions / $tasksCompleted) * 100, 100) : 0;
                    ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="performance-item text-center">
                                <div class="h4 mb-1 text-success font-weight-bold">
                                    <?= round($taskScore) ?>%
                                </div>
                                <small class="text-muted">टास्क कंप्लीशन रेट</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="performance-item text-center">
                                <div class="h4 mb-1 text-info font-weight-bold">
                                    <?= round($attendanceRate) ?>%
                                </div>
                                <small class="text-muted">अटेंडेंस रेट</small>
                            </div>
                        </div>
                    </div>

                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: <?= $taskScore ?>%"></div>
                    </div>

                    <div class="progress mb-3">
                        <div class="progress-bar bg-info" role="progressbar"
                             style="width: <?= $attendanceRate ?>%"></div>
                    </div>

                    <?php if ($avgRating > 0): ?>
                    <div class="text-center">
                        <div class="rating-display">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $avgRating ? 'text-warning' : 'text-muted' ?>"></i>
                            <?php endfor; ?>
                            <span class="ml-2 font-weight-bold">
                                <?= number_format($avgRating, 1) ?>/5
                            </span>
                        </div>
                        <small class="text-muted">ऐवरेज रेटिंग</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-2"></i>रिसेंट एक्टिविटीज
                    </h6>
                    <a href="/employee/activities" class="btn btn-sm btn-outline-primary">सभी देखें</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($dashboard_data['recent_activities'])): ?>
                        <?php foreach ($dashboard_data['recent_activities'] as $activity): ?>
                            <div class="activity-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <?php
                                        $activityIcon = 'circle';
                                        $activityColor = 'secondary';

                                        switch ($activity['activity_type']) {
                                            case 'task_completed':
                                                $activityIcon = 'check-circle';
                                                $activityColor = 'success';
                                                break;
                                            case 'task_assigned':
                                                $activityIcon = 'tasks';
                                                $activityColor = 'info';
                                                break;
                                            case 'attendance_marked':
                                                $activityIcon = 'clock';
                                                $activityColor = 'warning';
                                                break;
                                            case 'profile_updated':
                                                $activityIcon = 'user-edit';
                                                $activityColor = 'primary';
                                                break;
                                        }
                                        ?>
                                        <div class="icon-circle bg-<?= $activityColor ?>">
                                            <i class="fas fa-<?= $activityIcon ?> text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="small font-weight-bold text-gray-900">
                                            <?= htmlspecialchars($activity['description']) ?>
                                        </div>
                                        <div class="small text-gray-500">
                                            <?= ucfirst(str_replace('_', ' ', $activity['activity_type'])) ?>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= date('M d, h:i A', strtotime($activity['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-history fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0 small">कोई रिसेंट एक्टिविटी नहीं</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Leaves & Notifications -->
    <?php if (!empty($dashboard_data['pending_leaves'])): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-times mr-2"></i>पेंडिंग लीव रिक्वेस्ट्स
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>लीव टाइप</th>
                                    <th>स्टार्ट डेट</th>
                                    <th>एंड डेट</th>
                                    <th>टोटल डेज</th>
                                    <th>रिजन</th>
                                    <th>स्टेटस</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dashboard_data['pending_leaves'] as $leave): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($leave['leave_type_name']) ?></td>
                                        <td><?= date('d M Y', strtotime($leave['start_date'])) ?></td>
                                        <td><?= date('d M Y', strtotime($leave['end_date'])) ?></td>
                                        <td><span class="badge badge-info"><?=$leave['total_days']?> दिन</span></td>
                                        <td><?= htmlspecialchars(substr($leave['reason'], 0, 30)) ?>...</td>
                                        <td>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock mr-1"></i>पेंडिंग
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Performance Insights -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-lightbulb mr-2"></i>परफॉर्मेंस इनसाइट्स
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="insight-item">
                                <h6 class="text-success">
                                    <i class="fas fa-arrow-up mr-2"></i>स्ट्रेंथ्स
                                </h6>
                                <ul class="list-unstyled">
                                    <?php if ($taskScore >= 80): ?>
                                        <li><i class="fas fa-check text-success mr-2"></i>एक्सीलेंट टास्क कंप्लीशन रेट</li>
                                    <?php endif; ?>
                                    <?php if ($attendanceRate >= 90): ?>
                                        <li><i class="fas fa-check text-success mr-2"></i>रिगुलर अटेंडेंस</li>
                                    <?php endif; ?>
                                    <?php if (($weekly_performance['tasks']['tasks_completed'] ?? 0) >= 5): ?>
                                        <li><i class="fas fa-check text-success mr-2"></i>हाई प्रोडक्टिविटी</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="insight-item">
                                <h6 class="text-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>इंप्रूवमेंट एरियाज
                                </h6>
                                <ul class="list-unstyled">
                                    <?php if ($taskScore < 70): ?>
                                        <li><i class="fas fa-circle text-warning mr-2"></i>टास्क कंप्लीशन रेट इंप्रूव करें</li>
                                    <?php endif; ?>
                                    <?php if ($attendanceRate < 80): ?>
                                        <li><i class="fas fa-circle text-warning mr-2"></i>अटेंडेंस में सुधार की जरूरत</li>
                                    <?php endif; ?>
                                    <?php if (($weekly_performance['tasks']['tasks_completed'] ?? 0) < 3): ?>
                                        <li><i class="fas fa-circle text-warning mr-2"></i>ज्यादा टास्क्स कंप्लीट करें</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-target mr-2"></i>नेक्स्ट वीक टार्गेट्स</h6>
                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <strong>टास्क कंप्लीशन:</strong> 90%
                                    </div>
                                    <div class="col-md-3">
                                        <strong>अटेंडेंस:</strong> 95%
                                    </div>
                                    <div class="col-md-3">
                                        <strong>न्यू टास्क्स:</strong> 8+
                                    </div>
                                    <div class="col-md-3">
                                        <strong>परफॉर्मेंस स्कोर:</strong> 85+
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function recordAttendance(action) {
    if (confirm(`क्या आप ${action === 'check_in' ? 'चेक इन' : 'चेक आउट'} करना चाहते हैं?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/employee/record-attendance';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;
        form.appendChild(actionInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.employee-level .badge {
    font-size: 1.1em;
    padding: 0.5em 1em;
}

.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.task-item {
    padding: 10px;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.task-item:hover {
    border-color: #667eea;
    background-color: #f8f9fa;
}

.performance-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 15px;
}

.rating-display {
    font-size: 1.2em;
    margin: 10px 0;
}

.activity-item {
    padding: 10px;
    border-left: 4px solid #667eea;
    background: #f8f9fa;
    border-radius: 0 8px 8px 0;
    margin-bottom: 10px;
}

.insight-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 15px;
}

.insight-item ul li {
    margin-bottom: 8px;
}

.progress {
    height: 10px;
    border-radius: 5px;
}

.progress-bar {
    border-radius: 5px;
}

.alert {
    border-radius: 10px;
}

.text-gray-900 {
    color: #212529 !important;
}

.badge-bronze {
    background-color: #cd7f32;
    color: white;
}

.badge-silver {
    background-color: #c0c0c0;
    color: #333;
}

.d-grid .btn {
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .d-grid {
        display: block !important;
    }

    .d-grid .btn {
        display: block;
        width: 100%;
    }
}
</style>

<?php require_once 'app/views/layouts/employee_footer.php'; ?>
