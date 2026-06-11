<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Blog Posts</h5>
                    <a href="<?php echo BASE_URL; ?>/admin/blogs/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Post
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
                                    <th>Status</th>
                                    <th>Views</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blogs ?? [] as $blog): ?>
                                <tr>
                                    <td><?php echo $blog['id']; ?></td>
                                    <td><?php echo htmlspecialchars($blog['title']); ?></td>
                                    <td><?php echo htmlspecialchars($blog['category'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($blog['status'] == 'published'): ?>
                                            <span class="badge bg-success">Published</span>
                                        <?php elseif ($blog['status'] == 'draft'): ?>
                                            <span class="badge bg-warning">Draft</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Archived</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $blog['views'] ?? 0; ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($blog['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo BASE_URL; ?>/admin/blogs/<?php echo $blog['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/admin/blogs/<?php echo $blog['id']; ?>/edit" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/admin/blogs/<?php echo $blog['id']; ?>/delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($blogs ?? [])): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No blog posts found.</td>
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
