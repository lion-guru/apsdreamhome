<?php $colonies = $colonies ?? []; ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-city text-primary me-2"></i>Colonies / Projects</h4>
        <a href="<?php echo BASE_URL; ?>/admin/colonies/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>New Colony
        </a>
    </div>
    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::flash('error')): ?>
        <div class="alert alert-danger"><?php echo $msg; ?></div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>District</th>
                    <th>State</th>
                    <th>Total Plots</th>
                    <th>Available</th>
                    <th>Starting Price</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($colonies as $c): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($c['name'] ?? ''); ?></strong></td>
                    <td><code><?php echo htmlspecialchars($c['slug'] ?? ''); ?></code></td>
                    <td><?php echo htmlspecialchars($c['district_name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($c['state_name'] ?? ''); ?></td>
                    <td><?php echo $c['total_plots'] ?? 0; ?></td>
                    <td><?php echo $c['available_plots'] ?? 0; ?></td>
                    <td>₹<?php echo number_format($c['starting_price'] ?? 0); ?></td>
                    <td><?php echo ($c['is_active'] ?? 0) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?></td>
                    <td class="text-nowrap">
                        <a href="<?php echo BASE_URL; ?>/admin/colonies/<?php echo $c['id']; ?>" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                        <a href="<?php echo BASE_URL; ?>/admin/colonies/<?php echo $c['id']; ?>/edit" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="<?php echo BASE_URL; ?>/admin/colonies/<?php echo $c['id']; ?>/plots" class="btn btn-sm btn-success" title="Plots"><i class="fas fa-map"></i></a>
                        <a href="<?php echo BASE_URL; ?>/colony/<?php echo htmlspecialchars($c['slug'] ?? ''); ?>" class="btn btn-sm btn-secondary" target="_blank" title="View Public Page"><i class="fas fa-external-link-alt"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($colonies)): ?>
                <tr><td colspan="9" class="text-center text-muted py-4">No colonies found. <a href="<?php echo BASE_URL; ?>/admin/colonies/create">Create one</a>.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
