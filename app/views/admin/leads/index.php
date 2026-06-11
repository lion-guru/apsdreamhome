<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-bullseye"></i> Lead Management</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/leads/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Lead
            </a>
            <a href="<?= BASE_URL ?>/admin/leads/import" class="btn btn-success">
                <i class="fas fa-upload"></i> Import
            </a>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body aps-cp-card-body">
                    <h5>Total Leads</h5>
                    <h3><?= count($leads ?? []) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body aps-cp-card-body">
                    <h5>Converted</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body aps-cp-card-body">
                    <h5>Follow-up</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body aps-cp-card-body">
                    <h5>Lost</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header">
            <h5 class="mb-0">All Leads</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($leads)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No leads found!</p>
                    <a href="<?= BASE_URL ?>/admin/leads/create" class="btn btn-primary">Add Lead</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($lead['name'] ?? 'N/A') ?></strong></td>
                                <td><?= htmlspecialchars($lead['phone'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($lead['email'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($lead['source'] ?? 'Direct') ?></td>
                                <td>
                                    <span class="badge bg-<?= ($lead['status'] ?? '') === 'new' ? 'primary' : (($lead['status'] ?? '') === 'converted' ? 'success' : 'secondary') ?>">
                                        <?= ucfirst($lead['status'] ?? 'new') ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y', strtotime($lead['created_at'] ?? 'now')) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/leads/<?= $lead['id'] ?>" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>