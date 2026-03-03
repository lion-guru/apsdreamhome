@extends('layouts.admin')
@section('title', $title ?? 'Users Management - APS Dream Home')
@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Users Management</h4>
                <div>
                    <a href="{{ url('/admin/users/create') }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Add User
                    </a>
                    <button class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-download me-2"></i>Export
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search users..." value="{{ $_GET['search'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <option value="">All Roles</option>
                                <option value="user" {{ ($_GET['role'] ?? '') == 'user' ? 'selected' : '' }}>Users</option>
                                <option value="agent" {{ ($_GET['role'] ?? '') == 'agent' ? 'selected' : '' }}>Agents</option>
                                <option value="admin" {{ ($_GET['role'] ?? '') == 'admin' ? 'selected' : '' }}>Admins</option>
                                <option value="employee" {{ ($_GET['role'] ?? '') == 'employee' ? 'selected' : '' }}>Employees</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" {{ ($_GET['status'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ ($_GET['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ ($_GET['status'] ?? '') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Registered</label>
                            <select name="date_range" class="form-select">
                                <option value="">All Time</option>
                                <option value="today" {{ ($_GET['date_range'] ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ ($_GET['date_range'] ?? '') == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ ($_GET['date_range'] ?? '') == 'month' ? 'selected' : '' }}>This Month</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-search me-1"></i>Search
                                </button>
                                <a href="{{ url('/admin/users') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Contact</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Properties</th>
                                    <th>Joined</th>
                                    <th>Last Active</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users ?? [] as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-3" style="width: 40px; height: 40px; font-size: 14px;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                                <small class="text-muted">ID: #USR{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div><i class="bi bi-envelope me-1"></i>{{ $user->email }}</div>
                                            <div><i class="bi bi-telephone me-1"></i>{{ $user->phone }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $roleClass = match($user->role) {
                                                'admin' => 'danger',
                                                'agent' => 'warning',
                                                'employee' => 'info',
                                                'user' => 'secondary',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $roleClass }}">{{ ucfirst($user->role) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($user->status) {
                                                'active' => 'success',
                                                'inactive' => 'secondary',
                                                'suspended' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ ucfirst($user->status) }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $user->properties_count ?? 0 }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $user->created_at }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $user->last_active ?? 'Never' }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ url('/admin/users/' . $user->id) }}" class="btn btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ url('/admin/users/' . $user->id . '/edit') }}" class="btn btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if($user->status == 'active')
                                                <button class="btn btn-outline-warning" title="Suspend" onclick="updateStatus('{{ $user->id }}', 'suspended');"
                                                    <i class="bi bi-pause"></i>
                                                </button>
                                            @else
                                                <button class="btn btn-outline-success" title="Activate" onclick="updateStatus('{{ $user->id }}', 'active');"
                                                    <i class="bi bi-play"></i>
                                                </button>
                                            @endif
                                            <button class="btn btn-outline-danger" title="Delete" onclick="confirmDelete('{{ $user->id }}');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Users pagination">
                        <ul class="pagination justify-content-center mt-4">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to update this user's status?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="statusForm" method="POST" style="display: inline;">
                    <input type="hidden" name="status" id="statusValue">
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(userId) {
    document.getElementById('deleteForm').action = '/admin/users/' + userId + '/delete';
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function updateStatus(userId, status) {
    document.getElementById('statusValue').value = status;
    document.getElementById('statusForm').action = '/admin/users/' + userId + '/status';
    var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    statusModal.show();
}
</script>

@endsection
