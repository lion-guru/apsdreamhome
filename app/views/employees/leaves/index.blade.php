@extends('layouts.app')

@section('title', 'Leave Management - APS Dream Home')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Leave Management</h1>
            <p class="text-muted mb-0">Apply for leave and track your leave balance</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="applyLeave()">
                <i class="bi bi-plus-circle me-1"></i>Apply for Leave
            </button>
        </div>
    </div>

    <!-- Leave Balance Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Annual Leave
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $leaveBalance['annual'] }}</div>
                            <div class="text-xs text-muted">days remaining</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-check fa-2x text-success"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Sick Leave
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $leaveBalance['sick'] }}</div>
                            <div class="text-xs text-muted">days remaining</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hospital fa-2x text-info"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Casual Leave
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $leaveBalance['casual'] }}</div>
                            <div class="text-xs text-muted">days remaining</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-house-door fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Maternity Leave
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $leaveBalance['maternity'] }}</div>
                            <div class="text-xs text-muted">days remaining</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-heart fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Statistics -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-bar-chart me-1"></i>Leave Applications Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="h4 text-success mb-1">{{ $leaveStats['approved'] }}</div>
                            <small class="text-muted">Approved</small>
                        </div>
                        <div class="col-md-4">
                            <div class="h4 text-warning mb-1">{{ $leaveStats['pending'] }}</div>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-md-4">
                            <div class="h4 text-danger mb-1">{{ $leaveStats['rejected'] }}</div>
                            <small class="text-muted">Rejected</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-lightbulb me-1"></i>Quick Info
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i>Annual leave resets yearly</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i>Sick leave for medical emergencies</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i>Apply leave 7 days in advance</li>
                        <li class="mb-0"><i class="bi bi-check-circle text-success me-1"></i>Check balance before applying</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Applications -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-calendar-x me-1"></i>My Leave Applications
            </h6>
        </div>
        <div class="card-body">
            @if($leaves->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th>Applied On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaves as $leave)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $leave->leave_type === 'annual' ? 'success' : ($leave->leave_type === 'sick' ? 'info' : ($leave->leave_type === 'casual' ? 'warning' : 'danger')) }}">
                                        {{ ucfirst($leave->leave_type) }}
                                    </span>
                                </td>
                                <td>{{ date('M d, Y', strtotime($leave->start_date)) }}</td>
                                <td>{{ date('M d, Y', strtotime($leave->end_date)) }}</td>
                                <td>{{ $leave->leave_days }}</td>
                                <td>
                                    <span class="badge bg-{{ $leave->status === 'approved' ? 'success' : ($leave->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </td>
                                <td>{{ date('M d, Y', strtotime($leave->created_at)) }}</td>
                                <td>
                                    @if($leave->status === 'pending')
                                        <button class="btn btn-sm btn-outline-danger" onclick="cancelLeave({{ $leave->id }})">
                                            <i class="bi bi-x-circle me-1"></i>Cancel
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-outline-info" onclick="viewLeaveDetails({{ $leave->id }})">
                                            <i class="bi bi-eye me-1"></i>View
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $leaves->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No leave applications found</p>
                    <button class="btn btn-primary" onclick="applyLeave()">
                        <i class="bi bi-plus-circle me-1"></i>Apply Your First Leave
                    </button>
                </div>
            @endif
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
            <form action="{{ route('employee.apply-leave') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Leave Type</label>
                            <select name="leave_type" class="form-select" required onchange="updateLeaveBalance()">
                                <option value="">Select leave type</option>
                                <option value="annual">Annual Leave ({{ $leaveBalance['annual'] }} days available)</option>
                                <option value="sick">Sick Leave ({{ $leaveBalance['sick'] }} days available)</option>
                                <option value="casual">Casual Leave ({{ $leaveBalance['casual'] }} days available)</option>
                                <option value="maternity">Maternity Leave ({{ $leaveBalance['maternity'] }} days available)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Leave Period</label>
                            <div class="input-group">
                                <input type="date" name="start_date" class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                <span class="input-group-text">to</span>
                                <input type="date" name="end_date" class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason for Leave</label>
                        <textarea name="reason" class="form-control" rows="4" required placeholder="Please provide a detailed reason for your leave application..."></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> Leave applications should be submitted at least 7 days in advance.
                        Emergency leaves may be approved on a case-by-case basis.
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
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function applyLeave() {
    const modal = new bootstrap.Modal(document.getElementById('applyLeaveModal'));
    modal.show();
}

function updateLeaveBalance() {
    // This would update the leave balance display based on selected type
    // For now, it's handled by the static values in the options
}

function cancelLeave(leaveId) {
    if (!confirm('Are you sure you want to cancel this leave application?')) {
        return;
    }

    // This would typically make an AJAX call to cancel the leave
    alert('Leave cancellation functionality would be implemented here with AJAX call to server.');
}

function viewLeaveDetails(leaveId) {
    // This would fetch leave details via AJAX
    const modal = new bootstrap.Modal(document.getElementById('leaveDetailsModal'));
    document.getElementById('leaveDetailsContent').innerHTML = `
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Leave details functionality would be implemented here with AJAX calls to fetch full leave information.
        </div>
    `;
    modal.show();
}

// Auto-calculate leave days when dates change
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');

    if (startDateInput && endDateInput) {
        [startDateInput, endDateInput].forEach(input => {
            input.addEventListener('change', function() {
                const start = new Date(startDateInput.value);
                const end = new Date(endDateInput.value);

                if (start && end && start <= end) {
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    console.log(`Leave duration: ${diffDays} days`);
                }
            });
        });
    }
});
</script>
@endsection
