<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Knowledge Base</h5>
                    <a href="<?php echo BASE_URL; ?>/admin/knowledge-base/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Article
                    </a>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Views</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($articles ?? [] as $article): ?>
                                <tr>
                                    <td><?php echo $article['id']; ?></td>
                                    <td><?php echo htmlspecialchars($article['title'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($article['category'] ?? '-'); ?></td>
                                    <td><?php echo $article['views'] ?? 0; ?></td>
                                    <td>
                                        <?php if ($article['status'] == 'published'): ?>
                                            <span class="badge bg-success">Published</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($article['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo BASE_URL; ?>/admin/knowledge-base/<?php echo $article['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/admin/knowledge-base/<?php echo $article['id']; ?>/edit" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/admin/knowledge-base/<?php echo $article['id']; ?>/delete" class="btn btn-sm btn-danger" data-aps-confirm="Are you sure?">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($articles ?? [])): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-book fa-3x text-muted mb-3" class="style-82835"></i>
                                        <h5 class="text-muted">No knowledge base articles found</h5>
                                        <p class="text-muted mb-3">Build a knowledge base to help customers find answers to common questions about properties, payments, and processes.</p>
                                        <a href="<?= BASE_URL ?>/admin/knowledge-base/create" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i> Create Article
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
