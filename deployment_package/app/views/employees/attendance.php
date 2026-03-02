<?php
/**
 * Employee Attendance View
 * Shows employee attendance records with location-based check-in/out
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Employee Attendance'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .stats-card { border-radius: 12px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .check-in-btn { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; }
        .check-out-btn { background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%); border: none; }
        .location-indicator { font-size: 0.8em; color: #6c757d; }
        .attendance-status-present { color: #28a745; }
        .attendance-status-late { color: #ffc107; }
        .attendance-status-absent { color: #dc3545; }
        .attendance-status-half-day { color: #fd7e14; }
        .attendance-status-early-leave { color: #6f42c1; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-calendar-check me-2 text-primary"></i>My Attendance</h2>
                <p class="text-muted mb-0">Track your daily attendance with location verification</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn check-in-btn text-white" id="checkInBtn" onclick="handleCheckIn()">
                    <i class="fas fa-sign-in-alt me-2"></i>Check In
                </button>
                <button class="btn check-out-btn text-white" id="checkOutBtn" onclick="handleCheckOut()" disabled>
                    <i class="fas fa-sign-out-alt me-2"></i>Check Out
                </button>
            </div>
        </div>

        <!-- Current Status -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Today's Status</h5>
                        <div class="row" id="todayStatus">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-clock fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Check-in Time</h6>
                                        <p class="mb-0 text-muted" id="checkInTime">Not checked in yet</p>
                                        <small class="location-indicator" id="checkInLocation"></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-clock fa-2x text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Check-out Time</h6>
                                        <p class="mb-0 text-muted" id="checkOutTime">Not checked out yet</p>
                                        <small class="location-indicator" id="checkOutLocation"></small>
                                    </div>
                                </div>
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
