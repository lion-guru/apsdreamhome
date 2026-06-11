<?php $colony = $colony ?? []; $plots = $plots ?? []; ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-eye text-info me-2"></i><?php echo htmlspecialchars($colony['name'] ?? 'Colony Details'); ?></h4>
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/colonies/<?php echo $colony['id'] ?? 0; ?>/edit" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
            <a href="<?php echo BASE_URL; ?>/colony/<?php echo htmlspecialchars($colony['slug'] ?? ''); ?>" class="btn btn-success btn-sm" target="_blank"><i class="fas fa-external-link-alt me-1"></i>View Public Page</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm"><div class="card-header bg-white"><h6 class="mb-0">Description</h6></div>
            <div class="card-body aps-cp-card-body"><?php echo nl2br(htmlspecialchars($colony['description'] ?? 'No description')); ?></div></div>

            <div class="card border-0 shadow-sm mt-4"><div class="card-header bg-white"><h6 class="mb-0">Available Plots (<?php echo count($plots); ?>)</h6></div>
            <div class="card-body p-0">
                <?php if (empty($plots)): ?>
                <div class="text-center py-4 text-muted">No plots assigned to this colony yet.</div>
                <?php else: ?>
                <div class="table-responsive"><table class="table table-hover mb-0">
                    <thead class="bg-light"><tr><th>Plot #</th><th>Block</th><th>Area (sqft)</th><th>Price</th><th>Status</th></tr></thead>
                    <tbody><?php foreach ($plots as $p): ?>
                        <tr><td><?php echo htmlspecialchars($p['plot_number'] ?? 'N/A'); ?></td><td><?php echo htmlspecialchars($p['block'] ?? '-'); ?></td>
                        <td><?php echo $p['area_sqft'] ?? 0; ?></td><td>₹<?php echo number_format($p['total_price'] ?? 0); ?></td>
                        <td><span class="badge bg-<?php echo ($p['status'] ?? '') === 'available' ? 'success' : (($p['status'] ?? '') === 'booked' ? 'warning' : 'secondary'); ?>"><?php echo ucfirst($p['status'] ?? 'N/A'); ?></span></td></tr>
                    <?php endforeach; ?></tbody></table></div>
                <?php endif; ?>
            </div></div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm"><div class="card-header bg-white"><h6 class="mb-0">Quick Info</h6></div>
            <div class="card-body aps-cp-card-body">
                <table class="table table-sm">
                    <tr><td><strong>Slug</strong></td><td><code><?php echo htmlspecialchars($colony['slug'] ?? ''); ?></code></td></tr>
                    <tr><td><strong>District</strong></td><td><?php echo htmlspecialchars($colony['district_name'] ?? ''); ?></td></tr>
                    <tr><td><strong>State</strong></td><td><?php echo htmlspecialchars($colony['state_name'] ?? ''); ?></td></tr>
                    <tr><td><strong>Total Plots</strong></td><td><?php echo $colony['total_plots'] ?? 0; ?></td></tr>
                    <tr><td><strong>Available</strong></td><td><?php echo $colony['available_plots'] ?? 0; ?></td></tr>
                    <tr><td><strong>Starting Price</strong></td><td>₹<?php echo number_format($colony['starting_price'] ?? 0); ?></td></tr>
                    <tr><td><strong>Active</strong></td><td><?php echo ($colony['is_active'] ?? 0) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?></td></tr>
                    <tr><td><strong>Featured</strong></td><td><?php echo ($colony['is_featured'] ?? 0) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?></td></tr>
                    <tr><td><strong>Public Plots</strong></td><td><?php echo ($colony['show_plots_publicly'] ?? 0) ? '<span class="badge bg-success">Visible</span>' : '<span class="badge bg-secondary">Hidden</span>'; ?></td></tr>
                </table>
            </div></div>

            <div class="card border-0 shadow-sm mt-4"><div class="card-header bg-white"><h6 class="mb-0">Amenities</h6></div>
            <div class="card-body aps-cp-card-body">
                <?php $amenities = array_filter(array_map('trim', explode("\n", $colony['amenities'] ?? ''))); ?>
                <?php if (empty($amenities)): ?><p class="text-muted mb-0">None listed</p>
                <?php else: ?><ul class="mb-0"><?php foreach ($amenities as $a): ?><li><?php echo htmlspecialchars($a); ?></li><?php endforeach; ?></ul><?php endif; ?>
            </div></div>

            <?php if ($colony['map_link'] ?? ''): ?>
            <div class="card border-0 shadow-sm mt-4"><div class="card-header bg-white"><h6 class="mb-0">Location</h6></div>
            <div class="card-body p-0">
                <iframe src="<?php echo htmlspecialchars($colony['map_link']); ?>" width="100%" height="250" style="border:0;display:block" allowfullscreen loading="lazy"></iframe>
            </div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
