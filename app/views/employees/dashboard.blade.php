@extends('layouts.app')

@section('title', 'Employee Dashboard - APS Dream Home')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="text-muted mb-0">Employee Dashboard • {{ date('l, F j, Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="markAttendance()">
                <i class="bi bi-clock me-1"></i>Mark Attendance
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Attendance Stats -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                This Month Attendance
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['present_days'] }}/{{ $stats['total_working_days'] }}
                            </div>
                            <div class="text-xs text-muted">
                                {{ $stats['absent_days'] }} absent • {{ $stats['total_working_days'] - $stats['present_days'] - $stats['absent_days'] }} remaining
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-check fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Monthly Salary
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹{{ number_format($stats['monthly_salary'], 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cash fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks Completed -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tasks This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['completed_tasks'] }}
                            </div>
                            <div class="text-xs text-muted">
                                Completed this month
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Score -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Performance Score
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['performance_score'] }}/5
                            </div>
                            <div class="text-xs text-muted">
                                Latest review rating
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-star fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-8 mb-4">
            <!-- Recent Attendance -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history me-1"></i>Recent Attendance
                    </h6>
                </div>
                <div class="card-body">
                    @if($recentActivities['attendance']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentActivities['attendance'] as $attendance)
                                    <tr>
                                        <td>{{ date('M d, Y', strtotime($attendance->attendance_date)) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'absent' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($attendance->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $attendance->check_in_time ?? '-' }}</td>
                                        <td>{{ $attendance->check_out_time ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('employee.attendance') }}" class="btn btn-sm btn-outline-primary">
                                View All Attendance
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No attendance records found</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Tasks -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="bi bi-list-check me-1"></i>Recent Tasks
                    </h6>
                </div>
                <div class="card-body">
                    @if($recentActivities['tasks']->count() > 0)
                        @foreach($recentActivities['tasks'] as $task)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $task->title }}</h6>
                                <p class="text-muted mb-0 small">{{ Str::limit($task->description, 100) }}</p>
                                <small class="text-muted">
                                    Due: {{ date('M d, Y', strtotime($task->due_date)) }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                        </div>
                        @endforeach
                        <div class="text-center mt-3">
                            <a href="{{ route('employee.tasks') }}" class="btn btn-sm btn-outline-success">
                                View All Tasks
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No tasks assigned</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Upcoming Events -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-calendar-event me-1"></i>Upcoming Events
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Meetings -->
                    @if($upcomingEvents['meetings']->count() > 0)
                        @foreach($upcomingEvents['meetings'] as $meeting)
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-size: 18px;">
                                <i class="bi bi-camera-video"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $meeting->title }}</h6>
                                <p class="text-muted mb-0 small">
                                    {{ date('M d, Y H:i', strtotime($meeting->meeting_date)) }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    @endif

                    <!-- Deadlines -->
                    @if($upcomingEvents['deadlines']->count() > 0)
                        @foreach($upcomingEvents['deadlines'] as $deadline)
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-size: 18px;">
                                <i class="bi bi-alarm"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $deadline->title }}</h6>
                                <p class="text-muted mb-0 small">
                                    Due: {{ date('M d, Y', strtotime($deadline->due_date)) }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    @endif

                    @if($upcomingEvents['meetings']->count() == 0 && $upcomingEvents['deadlines']->count() == 0)
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No upcoming events</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Company Announcements -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="bi bi-megaphone me-1"></i>Announcements
                    </h6>
                </div>
                <div class="card-body">
                    @if($announcements->count() > 0)
                        @foreach($announcements as $announcement)
                        <div class="mb-3 pb-3 border-bottom">
                            <h6 class="mb-2">{{ $announcement->title }}</h6>
                            <p class="text-muted small mb-2">{{ Str::limit($announcement->content, 150) }}</p>
                            <small class="text-muted">
                                {{ date('M d, Y', strtotime($announcement->created_at)) }}
                            </small>
                        </div>
                        @endforeach
                        <div class="text-center">
                            <a href="{{ route('employee.announcements') }}" class="btn btn-sm btn-outline-warning">
                                View All
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-volume-mute text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No announcements</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-lightning me-1"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('employee.attendance') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-clock me-1"></i>View Attendance
                        </a>
                        <a href="{{ route('employee.tasks') }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-list-check me-1"></i>Manage Tasks
                        </a>
                        <a href="{{ route('employee.leaves') }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-calendar-x me-1"></i>Apply Leave
                        </a>
                        <a href="{{ route('employee.payroll') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-cash me-1"></i>View Payroll
                        </a>
                        <a href="{{ route('employee.directory') }}" class="btn btn-outline-dark btn-sm">
                            <i class="bi bi-people me-1"></i>Company Directory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Today's Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employee.mark-attendance') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Attendance Status</label>
                        <select name="status" class="form-select" required>
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="half_day">Half Day</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Check-in Time</label>
                        <input type="time" name="check_in_time" class="form-control" value="{{ date('H:i') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Mark Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function markAttendance() {
    const modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
    modal.show();
}
</script>
@endsection
