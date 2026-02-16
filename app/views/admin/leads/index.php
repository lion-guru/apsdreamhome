<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users-cog me-2"></i>Lead Management</h2>
        <a href="<?php echo BASE_URL; ?>/admin/leads/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Lead
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leads)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No leads found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($leads as $lead): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($lead['name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($lead['email'] ?? ''); ?></small>
                                        <?php if (!empty($lead['company'])): ?>
                                            <br><small class="text-info"><i class="fas fa-building me-1"></i><?php echo htmlspecialchars($lead['company']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($lead['phone'] ?? ''); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($lead['source_name'] ?? $lead['source'] ?? 'Unknown'); ?></span></td>
                                    <td>
                                        <?php
                                        $statusLabel = $lead['status_label'] ?? $lead['status'] ?? 'New';
                                        $statusClass = 'bg-info';
                                        $statusLower = strtolower($statusLabel);

                                        if (strpos($statusLower, 'hot') !== false || $statusLower == 'high') $statusClass = 'bg-danger';
                                        elseif (strpos($statusLower, 'closed') !== false || $statusLower == 'won') $statusClass = 'bg-success';
                                        elseif (strpos($statusLower, 'new') !== false) $statusClass = 'bg-primary';
                                        elseif (strpos($statusLower, 'lost') !== false) $statusClass = 'bg-secondary';
                                        elseif (strpos($statusLower, 'qualified') !== false) $statusClass = 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                                    </td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($lead['created_at'])); ?>
                                        <?php if (!empty($lead['assigned_to_name'])): ?>
                                            <br><small class="text-muted"><i class="fas fa-user-tag me-1"></i><?php echo htmlspecialchars($lead['assigned_to_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?php echo BASE_URL; ?>/admin/leads/edit/<?php echo $lead['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $lead['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this lead?')) {
            window.location.href = '<?php echo BASE_URL; ?>/admin/leads/delete/' + id;
        }
    }
</script>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>