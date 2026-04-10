

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-map-marked-alt"></i> Location Management</h2>
                <div>
                    <a href="/admin/locations/states/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add State
                    </a>
                    <a href="/admin/locations/districts" class="btn btn-info">
                        <i class="fas fa-city"></i> Districts
                    </a>
                    <a href="/admin/locations/colonies" class="btn btn-success">
                        <i class="fas fa-home"></i> Colonies
                    </a>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total States</h5>
                            <h3><?php echo count($states); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Districts</h5>
                            <h3><?php echo array_sum(array_column($states, 'district_count')); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Colonies</h5>
                            <h3>
                                <?php 
                                $totalColonies = 0;
                                foreach ($states as $state) {
                                    $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM colonies c LEFT JOIN districts d ON c.district_id = d.id WHERE d.state_id = ? AND c.is_active = 1");
                                    $stmt->execute([$state['id']]);
                                    $totalColonies += $stmt->fetch()['count'];
                                }
                                echo $totalColonies;
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- States Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">States</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Districts</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($states as $state): ?>
                                <tr>
                                    <td><?php echo $state['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($state['name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($state['code']); ?></span>
                                    </td>
                                    <td>
                                        <a href="/admin/locations/districts?state_id=<?php echo $state['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <?php echo $state['district_count']; ?> Districts
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $state['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $state['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/locations/districts?state_id=<?php echo $state['id']; ?>" class="btn btn-outline-info" title="View Districts">
                                                <i class="fas fa-city"></i>
                                            </a>
                                            <a href="/admin/locations/states/edit/<?php echo $state['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/admin/locations/states/delete/<?php echo $state['id']; ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure?')">
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


