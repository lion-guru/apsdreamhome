<?php
$page_title = $page_title ?? 'Blog Comments';
$active_page = 'blog-comments';

$statusLabels = [
    'pending' => ['label' => 'Pending', 'class' => 'warning'],
    'approved' => ['label' => 'Approved', 'class' => 'success'],
    'spam' => ['label' => 'Spam', 'class' => 'danger'],
];
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-comments"></i> Blog Comments</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/admin/blogs" class="btn btn-outline-primary"><i class="fas fa-arrow-left"></i> Back to Blogs</a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-secondary text-white h-100">
            <div class="card-body text-center py-3">
                <h3><?= $stats['total'] ?? 0 ?></h3>
                <p class="mb-0 small">Total Comments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body text-center py-3">
                <h3><?= $stats['pending'] ?? 0 ?></h3>
                <p class="mb-0 small">Pending Review</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body text-center py-3">
                <h3><?= $stats['approved'] ?? 0 ?></h3>
                <p class="mb-0 small">Approved</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white h-100">
            <div class="card-body text-center py-3">
                <h3><?= $stats['spam'] ?? 0 ?></h3>
                <p class="mb-0 small">Spam</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/admin/blogs/comments">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search name, email, content..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= ($status ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="spam" <?= ($status ?? '') === 'spam' ? 'selected' : '' ?>>Spam</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="<?= BASE_URL ?>/admin/blogs/comments" class="btn btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Comments Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Author</th>
                        <th>Comment</th>
                        <th>Post</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($comments)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-comments fa-2x mb-2"></i><br>No comments found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($comments as $c): ?>
                            <?php $sl = $statusLabels[$c['status']] ?? ['label' => $c['status'], 'class' => 'secondary']; ?>
                            <tr class="<?= $c['status'] === 'pending' ? 'table-warning' : '' ?>">
                                <td><?= $c['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($c['author_name'] ?? 'Guest') ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($c['author_email'] ?? '') ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars(mb_substr($c['comment'] ?? '', 0, 120)) ?>
                                    <?php if (mb_strlen($c['comment'] ?? '') > 120): ?>...<?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars($c['post_title'] ?? 'N/A') ?></small></td>
                                <td><span class="badge bg-<?= $sl['class'] ?>"><?= $sl['label'] ?></span></td>
                                <td><small><?= date('d M Y H:i', strtotime($c['created_at'])) ?></small></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($c['status'] !== 'approved'): ?>
                                            <a href="<?= BASE_URL ?>/admin/blogs/comment/<?= $c['id'] ?>/approve" class="btn btn-outline-success" title="Approve"><i class="fas fa-check"></i></a>
                                        <?php endif; ?>
                                        <?php if ($c['status'] !== 'spam'): ?>
                                            <a href="<?= BASE_URL ?>/admin/blogs/comment/<?= $c['id'] ?>/reject" class="btn btn-outline-warning" title="Mark Spam"><i class="fas fa-ban"></i></a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/admin/blogs/comment/<?= $c['id'] ?>/delete" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this comment permanently?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (($totalPages ?? 1) > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= $status ?? '' ?>&search=<?= $search ?? '' ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 3); $i <= min($totalPages, $page + 3); $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&status=<?= $status ?? '' ?>&search=<?= $search ?? '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= $status ?? '' ?>&search=<?= $search ?? '' ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
