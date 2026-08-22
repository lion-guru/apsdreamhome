<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Testimonials</h5>
                    <a href="<?php echo BASE_URL; ?>/admin/testimonials/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer Name</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($testimonials ?? [] as $testimonial): ?>
                                    <tr>
                                        <td><?php echo e($testimonial['id']); ?></td>
                                        <td><?php echo htmlspecialchars($testimonial['customer_name'] ?? ''); ?></td>
                                        <td>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </td>
                                        <td>
                                            <?php if ($testimonial['status'] == 'approved'): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php elseif ($testimonial['status'] == 'pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($testimonial['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?php echo BASE_URL; ?>/admin/testimonials/<?php echo e($testimonial['id']); ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>/admin/testimonials/<?php echo e($testimonial['id']); ?>/edit" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>/admin/testimonials/<?php echo e($testimonial['id']); ?>/delete" class="btn btn-sm btn-danger" data-aps-confirm="Are you sure?">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($testimonials ?? [])): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="fas fa-quote-left fa-3x text-muted mb-3" class="style-82835"></i>
                                            <h5 class="text-muted">No testimonials found</h5>
                                            <p class="text-muted mb-3">Collect customer testimonials to build social proof and trust with potential buyers.</p>
                                            <a href="<?= BASE_URL ?>/admin/testimonials/create" class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i> Add Testimonial
                                            </a>
                                        </td>
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