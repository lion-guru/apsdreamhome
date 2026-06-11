<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">FAQ Details</h5>
                    <div>
                        <a href="<?php echo BASE_URL; ?>/admin/faqs/<?php echo $faq['id']; ?>/edit" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/faqs" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body aps-cp-card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th class="w-25">ID</th>
                            <td><?php echo $faq['id']; ?></td>
                        </tr>
                        <tr>
                            <th>Question</th>
                            <td><?php echo htmlspecialchars($faq['question']); ?></td>
                        </tr>
                        <tr>
                            <th>Answer</th>
                            <td><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td><?php echo htmlspecialchars($faq['category'] ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Sort Order</th>
                            <td><?php echo $faq['display_order']; ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php if ($faq['status'] == 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Created Date</th>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($faq['created_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
