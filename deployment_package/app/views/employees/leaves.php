<?php
/**
 * Employee Leaves View
 * Shows employee leave records and allows leave applications
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Employee Leaves'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <style>
        .stats-card { border-radius: 12px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .leave-balance-card { transition: transform 0.2s; }
        .leave-balance-card:hover { transform: translateY(-2px); }
        .status-approved { color: #28a745; }
        .status-pending { color: #ffc107; }
        .status-rejected { color: #dc3545; }
        .status-cancelled { color: #6c757d; }
        .calendar-container { max-height: 600px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-calendar-alt me-2 text-primary"></i>My Leaves</h2>
                <p class="text-muted mb-0">Manage your leave requests and track your leave balance</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="loadLeaveCalendar()">
                    <i class="fas fa-calendar me-2"></i>Calendar View
                </button>
                <button class="btn btn-primary" onclick="showApplyLeaveModal()">
                    <i class="fas fa-plus me-2"></i>Apply for Leave
                </button>
            </div>
        </div>

    <!-- Leave Records -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Leave Records</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($leaves)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No leave records found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>From Date</th>
                                        <th>To Date</th>
                                        <th>Total Days</th>
                                        <th>Status</th>
                                        <th>Reason</th>
                                        <th>Applied Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leaves as $leave): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= htmlspecialchars($leave['leave_type_name'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= date('M d, Y', strtotime($leave['start_date'])) ?></strong>
                                            </td>
                                            <td>
                                                <strong><?= date('M d, Y', strtotime($leave['end_date'])) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?= htmlspecialchars($leave['total_days']) ?> day<?= $leave['total_days'] > 1 ? 's' : '' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $leave['status'] ?? 'pending';
                                                $badgeClass = 'bg-secondary';
                                                $icon = 'fas fa-question';

                                                switch ($status) {
                                                    case 'approved':
                                                        $badgeClass = 'bg-success';
                                                        $icon = 'fas fa-check-circle';
                                                        break;
                                                    case 'rejected':
                                                        $badgeClass = 'bg-danger';
                                                        $icon = 'fas fa-times-circle';
                                                        break;
                                                    case 'pending':
                                                        $badgeClass = 'bg-warning';
                                                        $icon = 'fas fa-clock';
                                                        break;
                                                    case 'cancelled':
                                                        $badgeClass = 'bg-info';
                                                        $icon = 'fas fa-ban';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $badgeClass ?>">
                                                    <i class="<?= $icon ?> me-1"></i>
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($leave['reason'])): ?>
                                                    <span title="<?= htmlspecialchars($leave['reason']) ?>">
                                                        <?= htmlspecialchars(substr($leave['reason'], 0, 50)) ?>
                                                        <?php if (strlen($leave['reason']) > 50): ?>...<?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">No reason provided</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('M d, Y', strtotime($leave['applied_date'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($status === 'pending'): ?>
                                                    <button class="btn btn-sm btn-warning" onclick="cancelLeave(<?= $leave['leave_id'] ?>)">
                                                        <i class="fas fa-times me-1"></i>Cancel
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-info" onclick="viewLeaveDetails(<?= $leave['leave_id'] ?>)">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </button>
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
</div>

<!-- Apply Leave Modal -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Apply for Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/employee/apply-leave" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="leave_type_id" class="form-label">Leave Type *</label>
                                <select class="form-select" id="leave_type_id" name="leave_type_id" required>
                                    <option value="">Select Leave Type</option>
                                    <?php foreach ($leave_types as $type): ?>
                                        <option value="<?= $type['leave_type_id'] ?>">
                                            <?= htmlspecialchars($type['leave_type_name']) ?>
                                            (<?= $type['max_days'] ?> days max)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Duration</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="duration_type" id="single_day" value="single" checked onchange="toggleDateFields()">
                                    <label class="form-check-label" for="single_day">
                                        Single Day
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="duration_type" id="multiple_days" value="multiple" onchange="toggleDateFields()">
                                    <label class="form-check-label" for="multiple_days">
                                        Multiple Days
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="singleDayFields">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="single_date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="single_date" name="start_date" required>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="multipleDayFields" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">From Date *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">To Date *</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason *</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3"
                                  placeholder="Please provide a reason for your leave request..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Leave Details Modal -->
<div class="modal fade" id="leaveDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Leave Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="leaveDetailsContent">
                <!-- Leave details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function showApplyLeaveModal() {
    $('#applyLeaveModal').modal('show');
}

function toggleDateFields() {
    const singleDayFields = document.getElementById('singleDayFields');
    const multipleDayFields = document.getElementById('multipleDayFields');
    const singleRadio = document.getElementById('single_day');
    const multipleRadio = document.getElementById('multiple_days');

    if (singleRadio.checked) {
        singleDayFields.style.display = 'block';
        multipleDayFields.style.display = 'none';
        document.getElementById('end_date').removeAttribute('required');
        document.getElementById('single_date').setAttribute('name', 'start_date');
    } else {
        singleDayFields.style.display = 'none';
        multipleDayFields.style.display = 'block';
        document.getElementById('end_date').setAttribute('required', 'required');
        document.getElementById('single_date').removeAttribute('name');
    }
}

function cancelLeave(leaveId) {
    if (confirm('Are you sure you want to cancel this leave application?')) {
        // In a real implementation, you would submit a form to cancel the leave
        alert('Leave cancellation feature would be implemented here.');
    }
}

function viewLeaveDetails(leaveId) {
    // In a real implementation, you would make an AJAX call to fetch leave details
    $('#leaveDetailsModal').modal('show');
    $('#leaveDetailsContent').html('<p>Loading leave details...</p>');
}

// Set minimum date for leave applications
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('single_date').setAttribute('min', today);
    document.getElementById('start_date').setAttribute('min', today);
    document.getElementById('end_date').setAttribute('min', today);
});

// Update end date minimum when start date changes
document.getElementById('start_date')?.addEventListener('change', function() {
    document.getElementById('end_date').setAttribute('min', this.value);
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

.leave-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.leave-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.leave-meta {
    border-top: 1px solid #eee;
    padding-top: 10px;
    margin-top: 10px;
}

.leave-actions {
    margin-top: 15px;
}

.leave-actions .btn {
    margin-right: 5px;
    margin-bottom: 5px;
}
</style>
