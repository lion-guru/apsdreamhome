<?php
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="admin-header py-4 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="mb-0 text-white">
                    <i class="fas fa-calendar-check me-2"></i>
                    Visit Management
                </h1>
                <p class="mb-0 opacity-75">Manage property site visits and schedules.</p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <a href="<?php echo BASE_URL; ?>admin/visits/create" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i>Schedule Visit
                </a>
            </div>
        </div>
    </div>
</div>

<section class="dashboard-stats py-5">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-list fa-2x"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($stats['total'] ?? 0); ?></div>
                        <div class="stat-label">Total Visits</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($stats['upcoming'] ?? 0); ?></div>
                        <div class="stat-label">Upcoming Visits</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($stats['completed'] ?? 0); ?></div>
                        <div class="stat-label">Completed Visits</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0">Upcoming Visits</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Visit Date</th>
                                <th>Customer</th>
                                <th>Property</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($visits) && !empty($visits)): ?>
                                <?php foreach ($visits as $visit): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo date('d M Y', strtotime($visit['visit_date'])); ?></div>
                                            <small class="text-muted"><?php echo date('h:i A', strtotime($visit['visit_date'])); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($visit['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($visit['property_title']); ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo ucfirst($visit['visit_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php
                                                                    echo match ($visit['status']) {
                                                                        'scheduled' => 'warning',
                                                                        'completed' => 'success',
                                                                        'cancelled' => 'danger',
                                                                        default => 'secondary'
                                                                    };
                                                                    ?>">
                                                <?php echo ucfirst($visit['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Manage
                                                </button>
                                                <ul class="dropdown-menu border-0 shadow-sm">
                                                    <li><a class="dropdown-item update-status" href="#" data-id="<?php echo $visit['id']; ?>" data-status="completed">Mark Completed</a></li>
                                                    <li><a class="dropdown-item update-status" href="#" data-id="<?php echo $visit['id']; ?>" data-status="cancelled">Cancel Visit</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No upcoming visits found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.querySelectorAll('.update-status').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const status = this.dataset.status;

            if (confirm(`Are you sure you want to mark this visit as ${status}?`)) {
                fetch(`<?php echo BASE_URL; ?>admin/visits/${id}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `status=${status}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error updating status');
                        }
                    });
            }
        });
    });
</script>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
