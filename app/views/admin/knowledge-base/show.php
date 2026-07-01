<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Article Details</h5>
                    <div>
                        <a href="<?php echo BASE_URL; ?>/admin/knowledge-base/<?php echo $article['id']; ?>/edit" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/knowledge-base" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive"><table class="table table-bordered">
                                <tr>
                                    <th class="w-25">ID</th>
                                    <td><?php echo $article['id']; ?></td>
                                </tr>
                                <tr>
                                    <th>Title</th>
                                    <td><?php echo htmlspecialchars($article['title']); ?></td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td><?php echo htmlspecialchars($article['category'] ?? '-'); ?></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <?php if ($article['status'] == 'published'): ?>
                                            <span class="badge bg-success">Published</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Views</th>
                                    <td><?php echo $article['views'] ?? 0; ?></td>
                                </tr>
                                <tr>
                                    <th>Created Date</th>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($article['created_at'])); ?></td>
                                </tr>
                            </table></div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card aps-cp-card">
                                <div class="card-header aps-cp-card-header">
                                    <h6 class="mb-0">Content</h6>
                                </div>
                                <div class="card-body aps-cp-card-body">
                                    <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
