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
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
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
                                        <div class="fw-bold"><?php echo $lead['first_name'] . ' ' . $lead['last_name']; ?></div>
                                        <small class="text-muted"><?php echo $lead['email']; ?></small>
                                    </td>
                                    <td><?php echo $lead['phone']; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $lead['source']; ?></span></td>
                                    <td>
                                        <?php 
                                            $statusClass = 'bg-info';
                                            if ($lead['status'] == 'Hot') $statusClass = 'bg-danger';
                                            if ($lead['status'] == 'Closed') $statusClass = 'bg-success';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo $lead['status']; ?></span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($lead['created_at'])); ?></td>
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
