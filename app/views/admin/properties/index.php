<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-building me-2"></i>Property Management</h2>
        <a href="<?php echo BASE_URL; ?>/admin/properties/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Property
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
                            <th>Property</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Added On</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($properties)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No properties found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($properties as $property): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo $property['title']; ?></div>
                                        <small class="text-muted"><?php echo $property['property_type']; ?> | <?php echo $property['area']; ?> sq.ft</small>
                                    </td>
                                    <td><?php echo $property['location']; ?></td>
                                    <td><span class="fw-bold text-success">₹<?php echo number_format($property['price']); ?></span></td>
                                    <td>
                                        <?php 
                                            $statusClass = 'bg-info';
                                            if ($property['status'] == 'active') $statusClass = 'bg-success';
                                            if ($property['status'] == 'sold') $statusClass = 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($property['status']); ?></span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($property['created_at'])); ?></td>
                                    <td class="text-end">
                                        <a href="<?php echo BASE_URL; ?>/admin/properties/edit/<?php echo $property['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $property['id']; ?>)">
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
    if (confirm('क्या आप वाकई इस प्रॉपर्टी को डिलीat करना चाहते हैं?')) {
        window.location.href = '<?php echo BASE_URL; ?>/admin/properties/delete/' + id;
    }
}
</script>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
