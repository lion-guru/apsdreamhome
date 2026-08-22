<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">FAQs</h5>
                    <a href="<?php echo BASE_URL; ?>/admin/faqs/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New FAQ
                    </a>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Question</th>
                                    <th>Category</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($faqs ?? [] as $faq): ?>
                                <tr>
                                    <td><?php echo e($faq['id']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($faq['question'], 0, 50)) . (strlen($faq['question']) > 50 ? '...' : ''); ?></td>
                                    <td><?php echo htmlspecialchars($faq['category'] ?? '-'); ?></td>
                                    <td><?php echo e($faq['display_order']); ?></td>
                                    <td>
                                        <?php if ($faq['status'] == 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo BASE_URL; ?>/admin/faqs/<?php echo e($faq['id']); ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/admin/faqs/<?php echo e($faq['id']); ?>/edit" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/admin/faqs/<?php echo e($faq['id']); ?>/delete" class="btn btn-sm btn-danger" data-aps-confirm="Are you sure?">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($faqs ?? [])): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No FAQs found.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
