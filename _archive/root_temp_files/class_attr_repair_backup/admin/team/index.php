
    <h4 class="mb-0"><i class="fas fa-users me-2"></i>Team Members</h4>
    <a href="<?php echo BASE_URL; ?>/admin/team/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Add Member
    </a>
</div>

<div class="card aps-cp-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Category</th>
                        <th>Group</th>
                        <th>Expertise</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3" class="style-82835"></i>
                            <h5 class="text-muted">No team members found</h5>
                            <p class="text-muted mb-3">Add your leadership team, advisors, and key staff to showcase them on the website and build trust with customers.</p>
                            <a href="<?= BASE_URL ?>/admin/team/create" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Add Team Member
                            </a>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($members as $i => $m): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td>
                            <?php if (!empty($m['photo'])): ?>
                            <img src="<?= BASE_URL ?>/assets/images/<?php echo htmlspecialchars($m['photo'] ?? ''); ?>" alt="" class="style-20773">
                            <?php else: ?>
                            <div class="style-56261"><i class="fas fa-user"></i></div>
                            <?php endif; ?>
                        </td>
                        <td class="fw-semibold"><?php echo htmlspecialchars($m['name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($m['position'] ?? ''); ?></td>
                        <td><span class="badge bg-info"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $m['category'] ?? 'team'))); ?></span></td>
                        <td><?php echo htmlspecialchars($m['group_name'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($m['expertise'] ?? '-'); ?></td>
                        <td><?php echo (int)($m['sort_order'] ?? 0); ?></td>
                        <td>
                            <?php if (($m['status'] ?? 'active') === 'active'): ?>
                            <span class="badge bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?php echo BASE_URL; ?>/admin/team/edit/<?php echo e($m['id']); ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="<?php echo BASE_URL; ?>/admin/team/destroy/<?php echo e($m['id']); ?>" class="style-71727" data-aps-confirm="Delete this team member?">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
