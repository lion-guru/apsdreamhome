<?php

// TODO: Add proper error handling with try-catch blocks

?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-users mr-2"></i>
                    All Farmers
                </h1>
                <div class="d-flex">
                    <!-- Search Form -->
                    <form class="form-inline mr-3" method="GET" action="<?php echo BASE_URL; ?>/farmers/search">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="input-group">
                            <input type="text"
                                   name="q"
                                   class="form-control"
                                   placeholder="Search Farmers..."
                                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <a href="<?php echo BASE_URL; ?>/farmers/create" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Add New Farmer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Farmers
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $statistics['total_farmers'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                States Covered
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $statistics['unique_states'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Districts Covered
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $statistics['unique_districts'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-city fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Complete Profiles
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $statistics['farmers_with_state'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Farmers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table mr-2"></i>Farmer List
                    </h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-filter mr-1"></i>Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?filter=all">All Farmers</a></li>
                            <li><a class="dropdown-item" href="?filter=active">Active Farmers</a></li>
                            <li><a class="dropdown-item" href="?filter=inactive">Inactive Farmers</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($farmers)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted">No farmers found</h4>
                            <p class="text-muted mb-4">No farmers registered yet.</p>
                            <a href="<?php echo BASE_URL; ?>/farmers/create" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus mr-2"></i>Add First Farmer
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <div class="table-responsive"><table class="table table-bordered table-hover table-responsive" id="farmersTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Farmer</th>
                                        <th>Contact Information</th>
                                        <th>Location</th>
                                        <th>Land Holdings</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($farmers as $farmer): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="farmer-avatar mr-3">
                                                        <?= strtoupper(substr($farmer['name'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($farmer['name']) ?></strong>
                                                        <?php if ($farmer['aadhar_number']): ?>
                                                            <br><small class="text-muted">
                                                                Aadhaar: <?= htmlspecialchars(substr($farmer['aadhar_number'], -4)) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <i class="fas fa-phone mr-1 text-success"></i>
                                                    <a href="tel:<?= htmlspecialchars($farmer['phone']) ?>">
                                                        <?= htmlspecialchars($farmer['phone']) ?>
                                                    </a>
                                                </div>
                                                <?php if ($farmer['email']): ?>
                                                    <div class="mt-1">
                                                        <i class="fas fa-envelope mr-1 text-primary"></i>
                                                        <a href="mailto:<?= htmlspecialchars($farmer['email']) ?>">
                                                            <?= htmlspecialchars($farmer['email']) ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                                        <?= htmlspecialchars($farmer['state_name'] ?? 'N/A') ?>
                                                    </span>
                                                </div>
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        <i class="fas fa-city mr-1"></i>
                                                        <?= htmlspecialchars($farmer['district_name'] ?? 'N/A') ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <span class="badge badge-success badge-lg">
                                                        <i class="fas fa-map mr-1"></i>
                                                        <?= $farmer['total_holdings'] ?? 0 ?>
                                                    </span>
                                                    <?php if ($farmer['total_area']): ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            Total <?= number_format($farmer['total_area'], 2) ?> acres
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $farmer['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                    <?= $farmer['status'] === 'active' ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d M Y', strtotime($farmer['created_at'])) ?>
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    ID: <?= $farmer['id'] ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group-vertical btn-group-sm">
                                                    <a href="<?php echo BASE_URL; ?>/farmers/<?= $farmer['id'] ?>"
                                                       class="btn btn-outline-info btn-sm"
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>View
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>/farmers/<?= $farmer['id'] ?>/edit"
                                                       class="btn btn-outline-warning btn-sm"
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-outline-danger btn-sm"
                                                            onclick="deleteFarmer(<?= $farmer['id'] ?>, '<?= htmlspecialchars($farmer['name']) ?>')"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table></div>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Farmers pagination">
                                <ul class="pagination">
                                    <li class="page-item disabled">
                                        <span class="page-link">Previous</span>
                                    </li>
                                    <li class="page-item active">
                                        <span class="page-link">1</span>
                                    </li>
                                    <li class="page-item disabled">
                                        <span class="page-link">Next</span>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Farmer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="farmerName"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
function deleteFarmer(id, name) {
    document.getElementById('farmerName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = '<?php echo BASE_URL; ?>/farmers/' + id + '/delete';
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Table search functionality
document.addEventListener('DOMContentLoaded', function(){
    var searchInput = document.querySelector('#farmersTable_filter input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            var value = this.value.toLowerCase();
            var rows = document.querySelectorAll('#farmersTable tbody tr');
            rows.forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().indexOf(value) > -1 ? '' : 'none';
            });
        });
    }
});
</script>

<style>
.farmer-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge-lg {
    font-size: 1.1em;
    padding: 0.5em 0.8em;
}

.table td {
    vertical-align: middle;
}

.dropdown-toggle::after {
    margin-left: 0.5em;
}
</style>


