<?php include '../app/views/includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="admin-sidebar">
                <div class="sidebar-header">
                    <h5><i class="fas fa-tachometer-alt me-2"></i>Admin Panel</h5>
                </div>
                <nav class="nav nav-pills flex-column">
                    <a href="/admin" class="nav-link">Dashboard</a>
                    <a href="/admin/properties" class="nav-link">Properties</a>
                    <a href="/admin/leads" class="nav-link active">Leads</a>
                    <a href="/admin/users" class="nav-link">Users</a>
                    <a href="/admin/reports" class="nav-link">Reports</a>
                    <a href="/admin/settings" class="nav-link">Settings</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="admin-content">
                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2>Lead Management</h2>
                                <p class="text-muted">Manage customer leads and inquiries</p>
                            </div>
                            <div>
                                <a href="/admin/leads/create" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add New Lead
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="filters-card">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="search"
                                           placeholder="Search leads..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="new" <?php echo ($filters['status'] ?? '') === 'new' ? 'selected' : ''; ?>>New</option>
                                        <option value="contacted" <?php echo ($filters['status'] ?? '') === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                        <option value="qualified" <?php echo ($filters['status'] ?? '') === 'qualified' ? 'selected' : ''; ?>>Qualified</option>
                                        <option value="converted" <?php echo ($filters['status'] ?? '') === 'converted' ? 'selected' : ''; ?>>Converted</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="source">
                                        <option value="">All Sources</option>
                                        <option value="website" <?php echo ($filters['source'] ?? '') === 'website' ? 'selected' : ''; ?>>Website</option>
                                        <option value="phone" <?php echo ($filters['source'] ?? '') === 'phone' ? 'selected' : ''; ?>>Phone</option>
                                        <option value="email" <?php echo ($filters['source'] ?? '') === 'email' ? 'selected' : ''; ?>>Email</option>
                                        <option value="referral" <?php echo ($filters['source'] ?? '') === 'referral' ? 'selected' : ''; ?>>Referral</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="assigned_to">
                                        <option value="">All Agents</option>
                                        <!-- Add agent options here -->
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-2"></i>Filter
                                    </button>
                                    <a href="/admin/leads" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Clear
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $leadStats['by_status'][0]['count'] ?? 0; ?></h3>
                                <p>Total Leads</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo count(array_filter($leadStats['by_status'] ?? [], fn($s) => $s['status'] === 'new')) ?? 0; ?></h3>
                                <p>New Leads</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo count(array_filter($leadStats['by_status'] ?? [], fn($s) => $s['status'] === 'converted')) ?? 0; ?></h3>
                                <p>Converted</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $leadStats['by_source'][0]['count'] ?? 0; ?></h3>
                                <p>This Month</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leads Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-card">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Source</th>
                                            <th>Budget</th>
                                            <th>Assigned To</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($leads)): ?>
                                            <?php foreach ($leads as $lead): ?>
                                            <tr>
                                                <td><?php echo $lead['id']; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar me-2">
                                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($lead['name']); ?>" alt="Lead">
                                                        </div>
                                                        <span><?php echo htmlspecialchars($lead['name']); ?></span>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($lead['email']); ?></td>
                                                <td><?php echo htmlspecialchars($lead['phone']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusColor($lead['status']); ?>">
                                                        <?php echo ucfirst($lead['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo ucfirst($lead['source']); ?></td>
                                                <td>₹<?php echo number_format($lead['budget'] ?? 0); ?></td>
                                                <td><?php echo htmlspecialchars($lead['assigned_user_name'] ?? 'Unassigned'); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($lead['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="/admin/leads/<?php echo $lead['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="/admin/leads/<?php echo $lead['id']; ?>/edit" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteLead(<?php echo $lead['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="10" class="text-center py-4">
                                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                    <h5>No leads found</h5>
                                                    <p class="text-muted">No leads match your current filters.</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if (!empty($leads)): ?>
                <div class="row">
                    <div class="col-12">
                        <nav aria-label="Lead pagination">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <span class="page-link">Previous</span>
                                </li>
                                <li class="page-item active">
                                    <span class="page-link">1</span>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Delete lead confirmation
function deleteLead(leadId) {
    if (confirm('Are you sure you want to delete this lead?')) {
        // Implement delete functionality
        fetch(`/admin/leads/${leadId}/delete`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to delete lead: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete lead. Please try again.');
        });
    }
}

// Helper function for status colors
function getStatusColor(status) {
    const colors = {
        'new': 'primary',
        'contacted': 'info',
        'qualified': 'warning',
        'converted': 'success',
        'closed': 'secondary'
    };
    return colors[status] || 'secondary';
}
</script>

<style>
.admin-sidebar {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.admin-content {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 30px;
}

.filters-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 20px;
}

.table-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

@media (max-width: 768px) {
    .admin-sidebar {
        margin-bottom: 20px;
    }
}
</style>

<?php include '../app/views/includes/footer.php'; ?>
