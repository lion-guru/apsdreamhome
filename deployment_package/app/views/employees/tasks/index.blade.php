@extends('layouts.app')

@section('title', 'Task Management - APS Dream Home')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Task Management</h1>
            <p class="text-muted mb-0">Track and manage your assigned tasks</p>
        </div>
    </div>

    <!-- Task Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Tasks
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $taskStats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-list-task fa-2x text-primary"></i>
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
                                Pending
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $taskStats['pending'] }}</div>
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
                                In Progress
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $taskStats['in_progress'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-play-circle fa-2x text-info"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Completed
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $taskStats['completed'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('employee.tasks') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-list-check me-1"></i>My Tasks
            </h6>
        </div>
        <div class="card-body">
            @if($tasks->count() > 0)
                <div class="row">
                    @foreach($tasks as $task)
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 border-left-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'secondary')) }}">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">{{ $task->title }}</h6>
                                <div>
                                    <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'secondary')) }} me-2">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                    <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'info' : 'warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="card-text">{{ $task->description }}</p>

                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>
                                            Due: {{ date('M d, Y', strtotime($task->due_date)) }}
                                        </small>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            Assigned: {{ date('M d, Y', strtotime($task->created_at)) }}
                                        </small>
                                    </div>
                                </div>

                                @if($task->status !== 'completed')
                                    @if($task->due_date < now() && $task->status !== 'completed')
                                        <div class="alert alert-danger py-2 mb-3">
                                            <small><i class="bi bi-exclamation-triangle me-1"></i>This task is overdue!</small>
                                        </div>
                                    @endif

                                    <div class="d-flex gap-2">
                                        @if($task->status === 'pending')
                                            <button class="btn btn-sm btn-outline-info task-status-btn" data-task-id="<?php echo $task->id; ?>" data-status="in_progress">
                                                <i class="bi bi-play me-1"></i>Start
                                            </button>
                                        @elseif($task->status === 'in_progress')
                                            <button class="btn btn-sm btn-success task-status-btn" data-task-id="<?php echo $task->id; ?>" data-status="completed">
                                                <i class="bi bi-check me-1"></i>Complete
                                            </button>
                                        @endif
                                        <button class="btn btn-sm btn-outline-secondary task-view-btn" data-task-id="<?php echo $task->id; ?>">
                                            <i class="bi bi-eye me-1"></i>View
                                        </button>
                                    </div>
                                @else
                                    <div class="text-success">
                                        <small><i class="bi bi-check-circle me-1"></i>Completed on {{ date('M d, Y', strtotime($task->completed_at ?? $task->updated_at)) }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $tasks->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-clipboard-x text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No tasks found matching your criteria</p>
                    <a href="{{ route('employee.tasks') }}" class="btn btn-outline-primary">Clear Filters</a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="taskDetailsContent">
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
document.addEventListener('DOMContentLoaded', function() {
    // Handle task status updates
    document.querySelectorAll('.task-status-btn').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-task-id');
            const status = this.getAttribute('data-status');

            if (!confirm('Are you sure you want to update this task status?')) {
                return;
            }

            updateTaskStatus(taskId, status);
        });
    });

    // Handle task view buttons
    document.querySelectorAll('.task-view-btn').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-task-id');
            viewTaskDetails(taskId);
        });
    });
});

function updateTaskStatus(taskId, status) {
    fetch(`{{ route('employee.tasks') }}/${taskId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating task status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating task status');
    });
}

function viewTaskDetails(taskId) {
    // This would typically fetch task details via AJAX
    // For now, just show a placeholder
    const modal = new bootstrap.Modal(document.getElementById('taskDetailsModal'));
    document.getElementById('taskDetailsContent').innerHTML = `
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Task details functionality would be implemented here with AJAX calls to fetch full task information.
        </div>
    `;
    modal.show();
}
</script>
@endsection
