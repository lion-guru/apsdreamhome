<?php
/**
 * Employee Activities View
 * Shows employee activity logs and history
 */
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-history me-2"></i>My Activities</h2>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-2"></i>Filter by Type
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?activity_type=">All Activities</a></li>
                    <li><a class="dropdown-item" href="?activity_type=login">Login</a></li>
                    <li><a class="dropdown-item" href="?activity_type=task">Task Updates</a></li>
                    <li><a class="dropdown-item" href="?activity_type=attendance">Attendance</a></li>
                    <li><a class="dropdown-item" href="?activity_type=leave">Leave</a></li>
                    <li><a class="dropdown-item" href="?activity_type=document">Documents</a></li>
                </ul>
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-calendar me-2"></i>Date Range
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?date_range=today">Today</a></li>
                    <li><a class="dropdown-item" href="?date_range=yesterday">Yesterday</a></li>
                    <li><a class="dropdown-item" href="?date_range=week">This Week</a></li>
                    <li><a class="dropdown-item" href="?date_range=month">This Month</a></li>
                    <li><a class="dropdown-item" href="?date_range=3months">Last 3 Months</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Activity Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $totalActivities = count($activities);
                                echo $totalActivities;
                                ?>
                            </h4>
                            <small>Total Activities</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $todayActivities = count(array_filter($activities, function($a) {
                                    return date('Y-m-d') === date('Y-m-d', strtotime($a['created_at']));
                                }));
                                echo $todayActivities;
                                ?>
                            </h4>
                            <small>Today</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $weekActivities = count(array_filter($activities, function($a) {
                                    $activityDate = date('Y-m-d', strtotime($a['created_at']));
                                    $weekStart = date('Y-m-d', strtotime('monday this week'));
                                    $weekEnd = date('Y-m-d', strtotime('sunday this week'));
                                    return $activityDate >= $weekStart && $activityDate <= $weekEnd;
                                }));
                                echo $weekActivities;
                                ?>
                            </h4>
                            <small>This Week</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-week fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $uniqueTypes = count(array_unique(array_column($activities, 'activity_type')));
                                echo $uniqueTypes;
                                ?>
                            </h4>
                            <small>Activity Types</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tags fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities Timeline -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-stream me-2"></i>Activity Timeline</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($activities)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No activities found.
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($activities as $activity): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-<?= $this->getActivityColor($activity['activity_type'] ?? 'general') ?>">
                                        <i class="fas fa-<?= $this->getActivityIcon($activity['activity_type'] ?? 'general') ?>"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="timeline-title">
                                                    <?= htmlspecialchars($activity['activity_type'] ?? 'Activity') ?>
                                                </h6>
                                                <p class="timeline-text mb-2">
                                                    <?= htmlspecialchars($activity['description'] ?? 'No description') ?>
                                                </p>
                                            </div>
                                            <span class="badge bg-<?= $this->getActivityBadgeClass($activity['activity_type'] ?? 'general') ?>">
                                                <?= htmlspecialchars($activity['activity_type'] ?? 'General') ?>
                                            </span>
                                        </div>

                                        <!-- Additional Details -->
                                        <?php if (!empty($activity['metadata'])): ?>
                                            <div class="activity-metadata mb-2">
                                                <?php
                                                $metadata = json_decode($activity['metadata'], true);
                                                if ($metadata && is_array($metadata)):
                                                    foreach ($metadata as $key => $value):
                                                ?>
                                                    <small class="text-muted">
                                                        <strong><?= htmlspecialchars($key) ?>:</strong>
                                                        <?= htmlspecialchars($value) ?>
                                                    </small>
                                                    <br>
                                                <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Activity Footer -->
                                        <div class="activity-footer">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= date('M d, Y h:i A', strtotime($activity['created_at'])) ?>
                                            </small>
                                            <?php if (!empty($activity['ip_address'])): ?>
                                                <small class="text-muted ms-3">
                                                    <i class="fas fa-globe me-1"></i>
                                                    IP: <?= htmlspecialchars($activity['ip_address']) ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php if (!empty($activity['user_agent'])): ?>
                                                <small class="text-muted ms-3">
                                                    <i class="fas fa-browser me-1"></i>
                                                    <?= htmlspecialchars(substr($activity['user_agent'], 0, 50)) ?>...
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Types Breakdown -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie me-2"></i>Activity Types Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        $activityTypes = [];
                        foreach ($activities as $activity) {
                            $type = $activity['activity_type'] ?? 'general';
                            $activityTypes[$type] = ($activityTypes[$type] ?? 0) + 1;
                        }

                        foreach ($activityTypes as $type => $count):
                        ?>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="activity-type-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-<?= $this->getActivityIcon($type) ?> me-2"></i>
                                                <?= ucfirst($type) ?>
                                            </h6>
                                            <p class="mb-0 text-muted">
                                                <?= $count ?> activities
                                            </p>
                                        </div>
                                        <div class="activity-count">
                                            <?= $count ?>
                                        </div>
                                    </div>
                                    <div class="progress mt-2">
                                        <div class="progress-bar bg-<?= $this->getActivityColor($type) ?>"
                                             style="width: <?= ($count / max($activityTypes)) * 100 ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh activities every 2 minutes
setInterval(function() {
    if (!document.hidden) {
        location.reload();
    }
}, 120000);

// Expandable activity details
function toggleActivityDetails(activityId) {
    const details = document.getElementById(`activity-details-${activityId}`);
    if (details) {
        details.style.display = details.style.display === 'none' ? 'block' : 'none';
    }
}

// Filter activities by date range
document.querySelectorAll('[href*="date_range"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const range = this.href.split('date_range=')[1];
        applyDateFilter(range);
    });
});

function applyDateFilter(range) {
    const now = new Date();
    let startDate, endDate;

    switch (range) {
        case 'today':
            startDate = endDate = new Date().toISOString().split('T')[0];
            break;
        case 'yesterday':
            const yesterday = new Date(now);
            yesterday.setDate(yesterday.getDate() - 1);
            startDate = endDate = yesterday.toISOString().split('T')[0];
            break;
        case 'week':
            const weekStart = new Date(now);
            weekStart.setDate(now.getDate() - now.getDay());
            startDate = weekStart.toISOString().split('T')[0];
            endDate = new Date().toISOString().split('T')[0];
            break;
        case 'month':
            startDate = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
            endDate = new Date().toISOString().split('T')[0];
            break;
        case '3months':
            const threeMonthsAgo = new Date(now);
            threeMonthsAgo.setMonth(threeMonthsAgo.getMonth() - 3);
            startDate = threeMonthsAgo.toISOString().split('T')[0];
            endDate = new Date().toISOString().split('T')[0];
            break;
    }

    // Apply the filter (in a real implementation, you would submit a form or make an AJAX call)
    console.log(`Filtering activities from ${startDate} to ${endDate}`);
}
</script>

<style>
.stats-card {
    border: none;
    border-radius: 10px;
    transition: transform 0.2s;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.timeline {
    position: relative;
    padding-left: 40px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -28px;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-left: 10px;
}

.timeline-title {
    margin-bottom: 8px;
    font-weight: 600;
    color: #495057;
}

.timeline-text {
    margin-bottom: 10px;
    color: #6c757d;
}

.activity-footer {
    border-top: 1px solid #e9ecef;
    padding-top: 8px;
    margin-top: 10px;
}

.activity-metadata {
    background: #e9ecef;
    border-radius: 4px;
    padding: 8px;
    margin: 10px 0;
    font-size: 0.875rem;
}

.activity-type-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    transition: box-shadow 0.2s;
}

.activity-type-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.activity-count {
    background: #007bff;
    color: white;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}

.badge {
    font-size: 0.75em;
}
</style>
