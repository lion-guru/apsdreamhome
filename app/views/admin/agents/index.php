<?php
$page_title = $page_title ?? 'Agent Management - APS Dream Home';
$agents = $agents ?? [];
$totalAgents = $totalAgents ?? 0;
$activeAgents = $activeAgents ?? 0;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="fas fa-user-tie me-2 text-primary"></i>Agent Management</h2>
        <a href="<?= BASE_URL ?>/admin/users?role=agent" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Agent</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid #3b82f6;border-radius:10px">
                <div style="font-size:1.4rem;font-weight:700;color:#3b82f6"><?= $totalAgents ?></div>
                <div class="small text-muted">Total Agents</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid #16a34a;border-radius:10px">
                <div style="font-size:1.4rem;font-weight:700;color:#16a34a"><?= $activeAgents ?></div>
                <div class="small text-muted">Active</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid #f59e0b;border-radius:10px">
                <div style="font-size:1.4rem;font-weight:700;color:#f59e0b"><?= array_sum(array_column($agents, 'deals_count')) ?></div>
                <div class="small text-muted">Total Deals</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid #8b5cf6;border-radius:10px">
                <div style="font-size:1.4rem;font-weight:700;color:#8b5cf6">₹<?= number_format(array_sum(array_column($agents, 'total_commission')) / 100000, 1) ?>L</div>
                <div class="small text-muted">Total Commission</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Deals</th>
                            <th>Commission</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($agents)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-user-tie fa-3x mb-3" style="opacity:0.15"></i>
                            <h5 class="text-muted">No agents found</h5>
                            <p class="text-muted mb-0">Add your first agent to start managing agent activities.</p>
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($agents as $agent): ?>
                        <tr>
                            <td><?= $agent['id'] ?></td>
                            <td><strong><?= htmlspecialchars($agent['name'] ?? '') ?></strong></td>
                            <td><?= htmlspecialchars($agent['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($agent['phone'] ?? '') ?></td>
                            <td>
                                <?php
                                $status = $agent['status'] ?? 'active';
                                $badgeClass = $status === 'active' ? 'bg-success' : ($status === 'inactive' ? 'bg-secondary' : 'bg-warning text-dark');
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                            </td>
                            <td><span class="badge bg-info"><?= $agent['deals_count'] ?? 0 ?></span></td>
                            <td>₹<?= number_format($agent['total_commission'] ?? 0) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/users/<?= $agent['id'] ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                <a href="<?= BASE_URL ?>/admin/users/<?= $agent['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-pen"></i></a>
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
