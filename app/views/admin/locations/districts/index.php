

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-city"></i> Districts Management</h2>
                <div>
                    <a href="/admin/locations/districts/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add District
                    </a>
                    <a href="/admin/locations/states" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> States
                    </a>
                    <a href="/admin/locations/colonies" class="btn btn-success">
                        <i class="fas fa-home"></i> Colonies
                    </a>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/admin/locations/districts">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="state_id" class="form-label">Filter by State</label>
                                <select class="form-select" id="state_id" name="state_id" onchange="this.form.submit()">
                                    <option value="">All States</option>
                                    <?php foreach ($states as $state): ?>
                                        <option value="<?php echo $state['id']; ?>" <?php echo (isset($_GET['state_id']) && $_GET['state_id'] == $state['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($state['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Districts Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Districts</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>State</th>
                                    <th>Colonies</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($districts as $district): ?>
                                <tr>
                                    <td><?php echo $district['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($district['name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($district['code']); ?></span>
                                    </td>
                                    <td>
                                        <a href="/admin/locations/districts?state_id=<?php echo $district['state_id']; ?>" class="btn btn-sm btn-outline-info">
                                            <?php echo htmlspecialchars($district['state_name']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="/admin/locations/colonies?district_id=<?php echo $district['id']; ?>" class="btn btn-sm btn-outline-success">
                                            <?php echo $district['colony_count']; ?> Colonies
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $district['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $district['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/locations/colonies?district_id=<?php echo $district['id']; ?>" class="btn btn-outline-success" title="View Colonies">
                                                <i class="fas fa-home"></i>
                                            </a>
                                            <a href="/admin/locations/districts/edit/<?php echo $district['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/admin/locations/districts/delete/<?php echo $district['id']; ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure? This will also delete all associated colonies.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


