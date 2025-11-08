<?php
/**
 * Employee Attendance View
 * Shows employee attendance records and allows check-in/check-out
 */
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-calendar-check me-2"></i>My Attendance</h2>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-2"></i>Filter by Month
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?month=">All Months</a></li>
                    <li><a class="dropdown-item" href="?month=<?= date('Y-m') ?>">This Month</a></li>
                    <li><a class="dropdown-item" href="?month=<?= date('Y-m', strtotime('-1 month')) ?>">Last Month</a></li>
                    <li><a class="dropdown-item" href="?month=<?= date('Y-m', strtotime('-2 months')) ?>">2 Months Ago</a></li>
                </ul>
            </div>
            <button class="btn btn-success" onclick="recordAttendance('check_in')">
                <i class="fas fa-sign-in-alt me-2"></i>Check In
            </button>
            <button class="btn btn-warning" onclick="recordAttendance('check_out')">
                <i class="fas fa-sign-out-alt me-2"></i>Check Out
            </button>
        </div>
    </div>

    <!-- Attendance Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card card text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $stats['total_days'] ?></h4>
                            <small>Total Days</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar fa-2x"></i>
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
                            <h4 class="mb-0"><?= $stats['present_days'] ?></h4>
                            <small>Present</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
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
                            <h4 class="mb-0"><?= $stats['absent_days'] ?></h4>
                            <small>Absent</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
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
                            <h4 class="mb-0 text-dark fw-bold">
                                <?= number_format($stats['attendance_rate'], 1) ?>%
                            </h4>
                            <small class="text-dark">Attendance Rate</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-percentage fa-2x text-dark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Records -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Attendance Records</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($attendance)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No attendance records found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Status</th>
                                        <th>Hours Worked</th>
                                        <th>Location</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendance as $record): ?>
                                        <tr>
                                            <td>
                                                <strong><?= date('M d, Y', strtotime($record['date'])) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?= date('l', strtotime($record['date'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if (!empty($record['check_in'])): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-sign-in-alt me-1"></i>
                                                        <?= date('h:i A', strtotime($record['check_in'])) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($record['check_out'])): ?>
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-sign-out-alt me-1"></i>
                                                        <?= date('h:i A', strtotime($record['check_out'])) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $record['status'] ?? 'absent';
                                                $badgeClass = 'bg-secondary';
                                                $icon = 'fas fa-question';

                                                switch ($status) {
                                                    case 'present':
                                                        $badgeClass = 'bg-success';
                                                        $icon = 'fas fa-check-circle';
                                                        break;
                                                    case 'absent':
                                                        $badgeClass = 'bg-danger';
                                                        $icon = 'fas fa-times-circle';
                                                        break;
                                                    case 'late':
                                                        $badgeClass = 'bg-warning';
                                                        $icon = 'fas fa-clock';
                                                        break;
                                                    case 'half_day':
                                                        $badgeClass = 'bg-info';
                                                        $icon = 'fas fa-adjust';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $badgeClass ?>">
                                                    <i class="<?= $icon ?> me-1"></i>
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                if (!empty($record['check_in']) && !empty($record['check_out'])) {
                                                    $checkIn = new DateTime($record['check_in']);
                                                    $checkOut = new DateTime($record['check_out']);
                                                    $interval = $checkIn->diff($checkOut);
                                                    $hours = $interval->h + ($interval->i / 60);
                                                    echo number_format($hours, 2) . ' hrs';
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($record['location'])): ?>
                                                    <small><?= htmlspecialchars($record['location']) ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($record['notes'])): ?>
                                                    <small><?= htmlspecialchars($record['notes']) ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Calendar View (Optional) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-calendar-alt me-2"></i>Monthly View</h5>
                </div>
                <div class="card-body">
                    <div id="attendance-calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Check In/Out Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceModalTitle">Record Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/employee/record-attendance" method="POST" id="attendanceForm">
                <div class="modal-body">
                    <input type="hidden" name="action" id="attendanceAction">

                    <div class="mb-3">
                        <label for="location" class="form-label">Location (Optional)</label>
                        <input type="text" class="form-control" id="location" name="location"
                               placeholder="Enter your current location">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                  placeholder="Any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="attendanceSubmitBtn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function recordAttendance(action) {
    document.getElementById('attendanceAction').value = action;
    document.getElementById('attendanceModalTitle').textContent =
        action === 'check_in' ? 'Check In' : 'Check Out';
    document.getElementById('attendanceSubmitBtn').textContent =
        action === 'check_in' ? 'Check In' : 'Check Out';
    document.getElementById('attendanceSubmitBtn').className =
        action === 'check_in' ? 'btn btn-success' : 'btn btn-warning';

    $('#attendanceModal').modal('show');
}

// Auto-refresh attendance data every 5 minutes
setInterval(function() {
    if (!document.hidden) {
        location.reload();
    }
}, 300000);

// Get user's location for attendance
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            document.getElementById('location').value = `${lat}, ${lng}`;
        });
    }
}

// Initialize location on page load
document.addEventListener('DOMContentLoaded', function() {
    // Uncomment the next line if you want to auto-get location
    // getLocation();
});
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

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.8em;
}

.task-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}
</style>
