<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .lead-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .lead-card:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 12px;
        }
        .priority-badge {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
        }
        .priority-high { background-color: #dc3545; color: white; }
        .priority-medium { background-color: #ffc107; color: #212529; }
        .priority-low { background-color: #28a745; color: white; }
        .filters-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-tie me-2"></i>Lead Management</h2>
            <a href="/leads/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Lead
            </a>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search leads..."
                           value="<?php echo htmlspecialchars($data['filters']['search'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <?php foreach ($data['statuses'] as $status): ?>
                            <option value="<?php echo $status['status']; ?>"
                                <?php echo ($data['filters']['status'] ?? '') == $status['status'] ? 'selected' : ''; ?>>
                                <?php echo ucfirst($status['status']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="source">
                        <option value="">All Sources</option>
                        <?php foreach ($data['sources'] as $source): ?>
                            <option value="<?php echo $source['source']; ?>"
                                <?php echo ($data['filters']['source'] ?? '') == $source['source'] ? 'selected' : ''; ?>>
                                <?php echo ucfirst($source['source']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="priority">
                        <option value="">All Priorities</option>
                        <option value="low" <?php echo ($data['filters']['priority'] ?? '') == 'low' ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?php echo ($data['filters']['priority'] ?? '') == 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo ($data['filters']['priority'] ?? '') == 'high' ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="/leads" class="btn btn-outline-secondary">
                        <i class="fas fa-undo me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h4><?php echo array_sum(array_column($data['leadStats']['by_status'], 'count')); ?></h4>
                        <p class="text-muted mb-0">Total Leads</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-user-plus fa-2x text-success mb-2"></i>
                        <h4><?php echo array_sum(array_column($data['leadStats']['by_status'], 'count')); ?></h4>
                        <p class="text-muted mb-0">New This Month</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-info mb-2"></i>
                        <h4><?php echo array_sum(array_column($data['leadStats']['by_status'], 'count')); ?></h4>
                        <p class="text-muted mb-0">Qualified</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-handshake fa-2x text-warning mb-2"></i>
                        <h4><?php echo array_sum(array_column($data['leadStats']['by_status'], 'count')); ?></h4>
                        <p class="text-muted mb-0">Converted</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leads Grid -->
        <div class="row">
            <?php if (!empty($data['leads'])): ?>
                <?php foreach ($data['leads'] as $lead): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card lead-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($lead['name']); ?></h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="/leads/<?php echo $lead['id']; ?>">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </a></li>
                                            <li><a class="dropdown-item" href="/leads/<?php echo $lead['id']; ?>/edit">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteLead(<?php echo $lead['id']; ?>)">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <span class="status-badge badge bg-<?php echo getStatusColor($lead['status']); ?>">
                                        <?php echo ucfirst($lead['status']); ?>
                                    </span>
                                    <span class="priority-badge priority-<?php echo $lead['priority']; ?>">
                                        <?php echo ucfirst($lead['priority']); ?> Priority
                                    </span>
                                </div>

                                <p class="card-text text-muted mb-2">
                                    <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($lead['email']); ?>
                                </p>
                                <p class="card-text text-muted mb-2">
                                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($lead['phone']); ?>
                                </p>
                                <p class="card-text text-muted mb-3">
                                    <i class="fas fa-tag me-1"></i><?php echo ucfirst($lead['source']); ?>
                                </p>

                                <?php if (!empty($lead['property_type'])): ?>
                                    <p class="card-text text-muted mb-2">
                                        <i class="fas fa-home me-1"></i>Interested in: <?php echo ucfirst($lead['property_type']); ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($lead['budget'])): ?>
                                    <p class="card-text text-muted mb-2">
                                        <i class="fas fa-dollar-sign me-1"></i>Budget: ₹<?php echo number_format($lead['budget']); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('M j, Y', strtotime($lead['created_at'])); ?>
                                    </small>
                                    <?php if (!empty($lead['assigned_user_name'])): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($lead['assigned_user_name']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No leads found</h4>
                        <p class="text-muted">Get started by adding your first lead.</p>
                        <a href="/leads/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Lead
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteLead(leadId) {
            if (confirm('Are you sure you want to delete this lead? This action cannot be undone.')) {
                // Here you would make an AJAX call to delete the lead
                alert('Delete functionality would be implemented here');
            }
        }

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
</body>
</html>

<?php
// Helper function to get status color
function getStatusColor($status) {
    $colors = [
        'new' => 'primary',
        'contacted' => 'info',
        'qualified' => 'warning',
        'converted' => 'success',
        'closed' => 'secondary'
    ];
    return $colors[$status] ?? 'secondary';
}
?>
