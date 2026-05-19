<?php
$page_title = $page_title ?? 'My Leads - APS Dream Home';
$leads = $leads ?? [];
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-users text-success me-2"></i>My Leads</h4>
        <a href="<?php echo BASE_URL; ?>/associate/leads/add" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Lead</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($leads)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No leads yet. Start adding leads to grow your business.</p>
                    <a href="<?php echo BASE_URL; ?>/associate/leads/add" class="btn btn-primary">Add Your First Lead</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                        <thead class="bg-light">
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leads as $lead): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($lead['name'] ?? ''); ?></strong></td>
                                    <td><?php echo htmlspecialchars($lead['phone'] ?? ''); ?><br><small class="text-muted"><?php echo htmlspecialchars($lead['email'] ?? ''); ?></small></td>
                                    <td><?php echo htmlspecialchars($lead['type'] ?? 'General'); ?></td>
                                    <td><span class="badge bg-<?php echo ($lead['status'] ?? 'new') === 'hot' ? 'danger' : (($lead['status'] ?? 'new') === 'warm' ? 'warning' : 'info'); ?>"><?php echo ucfirst($lead['status'] ?? 'New'); ?></span></td>
                                    <td><?php echo htmlspecialchars($lead['date'] ?? ''); ?></td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
