<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin">Admin</a></li>
                    <li class="breadcrumb-item active">Virtual Tour Management</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-vr-cardboard me-3 text-primary"></i><?= ($page_title ?? 'Virtual Tour Management') ?></h1>
        </div>
    </div>

    <?php $properties = $properties ?? []; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>Properties with Virtual Tours</h5>
            <span class="badge bg-primary"><?= count($properties) ?> properties</span>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($properties)): ?>
            <div class="text-center py-5">
                <i class="fas fa-vr-cardboard fa-5x text-muted mb-3"></i>
                <h5>No Virtual Tours Available</h5>
                <p class="text-muted">Properties with virtual tours will appear here.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle table-responsive">
                    <thead class="table-light">
                        <tr>
                            <th>Property</th>
                            <th>Location</th>
                            <th>Panoramas</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($properties as $prop): ?>
                        <tr>
                            <td><strong><?= ($prop['title'] ?? 'Untitled') ?></strong></td>
                            <td><?= ($prop['city'] ?? 'N/A') ?>, <?= ($prop['state'] ?? 'N/A') ?></td>
                            <td><span class="badge bg-info"><?= ($prop['panorama_count'] ?? 0) ?></span></td>
                            <td><small class="text-muted"><?= ($prop['last_updated'] ?? 'Never') ?></small></td>
                            <td>
                                <a href="<?= $base ?? BASE_URL ?>virtual-tour/<?= ($prop['id'] ?? '') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                <a href="<?= $base ?? BASE_URL ?>admin/properties/<?= (int)($prop['id'] ?? 0) ?>" class="btn btn-sm btn-outline-success" title="Edit Property (upload panoramas)"><i class="fas fa-upload"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
