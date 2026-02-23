@extends('layouts.app')

@section('title', 'Attendance Management - APS Dream Home')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Attendance Management</h1>
            <p class="text-muted mb-0">Track and manage your attendance records</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="markAttendance()">
                <i class="bi bi-plus-circle me-1"></i>Mark Attendance
            </button>
        </div>
    </div>

    <!-- Monthly Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Present Days
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $monthlySummary['present'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-success"></i>
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
                                Absent Days
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $monthlySummary['absent'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-x-circle fa-2x text-danger"></i>
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
                                Late Days
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $monthlySummary['late'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock fa-2x text-warning"></i>
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
                                Half Days
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $monthlySummary['half_day'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-dash-circle fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Records</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        @for($y = date('Y') - 2; $y <= date('Y'); $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('employee.attendance') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Records -->
    @include('components.mobile-table', [
        'tableClass' => 'table-bordered table-hover',
        'headerClass' => 'table-light',
        'headers' => [
            ['title' => 'Date'],
            ['title' => 'Day'],
            ['title' => 'Status'],
            ['title' => 'Check In'],
            ['title' => 'Check Out'],
            ['title' => 'Working Hours'],
            ['title' => 'Notes']
        ]
    ])
        {{-- Table Rows --}}
        @section('table-rows')
            @foreach($attendance as $record)
            <tr>
                <td>{{ date('M d, Y', strtotime($record->attendance_date)) }}</td>
                <td>{{ date('l', strtotime($record->attendance_date)) }}</td>
                <td>
                    <span class="badge bg-{{ $record->status === 'present' ? 'success' : ($record->status === 'absent' ? 'danger' : ($record->status === 'late' ? 'warning' : 'info')) }}">
                        {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                    </span>
                </td>
                <td>{{ $record->check_in_time ?? '-' }}</td>
                <td>{{ $record->check_out_time ?? '-' }}</td>
                <td>
                    @if($record->check_in_time && $record->check_out_time)
                        @php
                            $checkIn = strtotime($record->check_in_time);
                            $checkOut = strtotime($record->check_out_time);
                            $hours = ($checkOut - $checkIn) / 3600;
                            echo number_format($hours, 1) . ' hrs';
                        @endphp
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($record->notes)
                        <span title="{{ $record->notes }}">
                            {{ Str::limit($record->notes, 30) }}
                        </span>
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        @endsection

        {{-- Mobile Cards --}}
        @section('mobile-cards')
            @foreach($attendance as $record)
            <div class="mobile-card">
                <div class="mobile-card-header">
                    <h6 class="mobile-card-title">
                        {{ date('M d, Y', strtotime($record->attendance_date)) }}
                        <small class="text-muted d-block">{{ date('l', strtotime($record->attendance_date)) }}</small>
                    </h6>
                    <span class="badge mobile-card-status {{ $record->status }}">
                        {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                    </span>
                </div>

                <div class="mobile-card-body mobile-card-grid-2">
                    <div class="mobile-card-field">
                        <span class="mobile-card-label">Check In</span>
                        <span class="mobile-card-value">{{ $record->check_in_time ?? '-' }}</span>
                    </div>
                    <div class="mobile-card-field">
                        <span class="mobile-card-label">Check Out</span>
                        <span class="mobile-card-value">{{ $record->check_out_time ?? '-' }}</span>
                    </div>
                    <div class="mobile-card-field">
                        <span class="mobile-card-label">Working Hours</span>
                        <span class="mobile-card-value">
                            @if($record->check_in_time && $record->check_out_time)
                                @php
                                    $checkIn = strtotime($record->check_in_time);
                                    $checkOut = strtotime($record->check_out_time);
                                    $hours = ($checkOut - $checkIn) / 3600;
                                    echo number_format($hours, 1) . ' hrs';
                                @endphp
                            @else
                                -
                            @endif
                        </span>
                    </div>
                    @if($record->notes)
                    <div class="mobile-card-field">
                        <span class="mobile-card-label">Notes</span>
                        <span class="mobile-card-value text-truncate" title="{{ $record->notes }}">
                            {{ Str::limit($record->notes, 50) }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        @endsection
    @endinclude

    {{-- Pagination --}}
    @if($attendance->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $attendance->appends(request()->query())->links() }}
        </div>
    @endif
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
                        <input type="time" name="check_in_time" class="form-control" value="{{ date('H:i') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Check-out Time (Optional)</label>
                        <input type="time" name="check_out_time" class="form-control">
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
