<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Testimonial Details</h5>
                    <div>
                        <a href="<?php echo BASE_URL; ?>/admin/testimonials/<?php echo $testimonial['id']; ?>/edit" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/testimonials" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="table-responsive"><table class="table table-bordered">
                                <tr>
                                    <th class="w-25">ID</th>
                                    <td><?php echo $testimonial['id']; ?></td>
                                </tr>
                                <tr>
                                    <th>Customer Name</th>
                                    <td><?php echo htmlspecialchars($testimonial['customer_name'] ?? ''); ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?php echo htmlspecialchars($testimonial['customer_email'] ?? '-'); ?></td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td><?php echo htmlspecialchars($testimonial['customer_phone'] ?? '-'); ?></td>
                                </tr>
                                <tr>
                                    <th>Rating</th>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                        (<?php echo $testimonial['rating']; ?>/5)
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <?php if ($testimonial['status'] == 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php elseif ($testimonial['status'] == 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Featured</th>
                                    <td>
                                        <?php if (!empty($testimonial['is_featured'])): ?>
                                            <span class="badge bg-info">Featured</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created Date</th>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($testimonial['created_at'])); ?></td>
                                </tr>
                            </table></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header aps-cp-card-header">
                                    <h6 class="mb-0">Testimonial Content</h6>
                                </div>
                                <div class="card-body aps-cp-card-body">
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($testimonial['content'] ?? '')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
