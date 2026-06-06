<?php
$properties = $properties ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Properties</h1>
        <a href="/list-property" class="btn btn-primary"><i class="fas fa-plus"></i> Add Property</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Property Name</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($properties)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No properties listed yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($properties as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['name'] ?? '') ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($p['type'] ?? '') ?></span></td>
                                    <td><?= htmlspecialchars($p['location'] ?? '') ?></td>
                                    <td>&#8377; <?= htmlspecialchars(number_format((float)($p['price'] ?? 0))) ?></td>
                                    <td>
                                        <?php $st = $p['status'] ?? ''; ?>
                                        <?php if ($st === 'Approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php elseif ($st === 'Pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($st === 'Rejected'): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($st) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="/properties/<?= $p['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="/properties/<?= $p['id'] ?? 0 ?>/edit" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
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
